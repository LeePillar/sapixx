<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 微信公众号和小程序服务
 */
namespace base\logic;

use base\model\SystemAppsClient;
use think\Request;
use think\Service;
use think\facade\Config;
use EasyWeChat\OpenPlatform\AuthorizerAccessToken;
use EasyWeChat\OpenPlatform\Application as OpenPlatform;
use EasyWeChat\OfficialAccount\Application as Official;
use EasyWeChat\MiniApp\Application as MiniApp;

class Wechat extends Service
{
    protected $request;
    protected $api;
    protected $config; //开发平台配置
    protected $client; //租户应用配置
    protected $openPlatform = false;
    protected $type         = 'api';
    protected $data         = [];

    public function __construct(Request $request)
    {
        $this->request  = $request;
    }

    /**
     * 开放平台实例
     * @return object
     */
    public function openPlatform(){
        try {
            $wechat_open = Config::get('config.wechat_open');
            $config = [
                'app_id'  => $wechat_open['app_id'],
                'secret'  => $wechat_open['secret'],
                'token'   => $wechat_open['token'],
                'aes_key' => $wechat_open['aes_key'],
            ]; 
            $config['http']['timeout'] = 5;
            $config['http']['retry']   = true;
            $config['http']['throw']   = false;
            return new OpenPlatform($config);
        } catch (\Exception $e) {
            abort(11003,$e->getMessage());
        }
    }
    
    /**
     * 开放平台接入测试实例
     * @param boolean $is_officialAccess
     * @param string|null $appid
     * @return void
     */
    public function testOpenPlatform($is_officialAccess = false,string $appid = null){
        try {
            $app = $this->openPlatform();
            $authorizerAccessToken =  new AuthorizerAccessToken($appid,$app->getComponentAccessToken()->getToken());
            if($is_officialAccess){
                return $app->getOfficialAccountWithAccessToken($appid, $authorizerAccessToken)->getServer();
            }else{
                return $app->getMiniAppWithAccessToken($appid, $authorizerAccessToken)->getServer();
            }
        } catch (\Exception $e) {
            abort(11003,$e->getMessage());
        }
    }

     /**
     * 读取配置
     * @return void
     */
    private function getClient(){
        try {
            switch ($this->type) {
                case 'wechatapp':
                    $this->client = SystemAppsClient::where(['title' => 'wechatapp'])->apps()->find();
                    break;
                case 'wechatmp':
                    $this->client = SystemAppsClient::where(['title' => 'wechatmp'])->apps()->find();
                    break;
                case 'admin':
                    $this->client = (object)Config::get('config.wechat_account');
                    $this->client->title = 'admin';
                    break;
                default:
                    $this->client = $this->request->client;
                    break;
            }
            if(empty($this->client) || empty($this->client->appid)){
                abort(11001,'API微信接入配置有误');
            }
            $this->openPlatform = $this->client->app->config['is_open_wechat']??false;
            if($this->openPlatform){
                if(empty($this->client->config)){
                    abort(11001,'应用未授权,请先授权');
                }
                //判断是否更新authorizer_access_token
                if(time()-$this->client->update_time >= $this->client->config['expires_in']){
                    $response = $this->openPlatform()->refreshAuthorizerToken($this->client->appid,$this->client->config['authorizer_refresh_token']);
                    $authorizer = $this->client->config;
                    $authorizer['expires_in']               = intval($response['expires_in'] ?? 7200)-500;
                    $authorizer['authorizer_access_token']  = $response['authorizer_access_token'];
                    $authorizer['authorizer_refresh_token'] = $response['authorizer_refresh_token'];
                    $this->client->update_time  = time();
                    $this->client->config       = $authorizer;
                    $this->client->save();
                }
            }else{
                if(empty($this->client->secret)){
                    abort(11001,'app_secret未配置');
                }
            }
            return $this;
        } catch (\Exception $e) {
            exitjson(10001,$e->getMessage());
        }
    }

    /**
     * 实例化对象并写入配置
     * @return voidf
     */
    private function setConfig(){
        try {
            if($this->openPlatform){
                $app = $this->openPlatform();
                if($this->client->title == 'wechatapp'){
                    $this->api = $app->getMiniAppWithAccessToken($this->client->appid,$this->client->config['authorizer_access_token']);
                }else{
                    $this->api = $app->getOfficialAccountWithAccessToken($this->client->appid,$this->client->config['authorizer_access_token']);
                }
            }else{
                $config = [
                    'app_id'  => $this->client->appid,
                    'secret'  => $this->client->secret,
                    'token'   => $this->client->token,
                    'aes_key' => $this->client->aes_key,
                ];
                $config['http']['timeout'] = 5;
                $config['http']['retry']   = true;
                $config['http']['throw']   = false;
                if($this->client->title == 'wechatapp'){
                    $this->api = new MiniApp($config);
                }else{
                    $this->api = new Official($config);
                }
            }
            return $this;
        } catch (\Exception $e) {
            exitjson(10001,$e->getMessage());
        } 
    }
    
    /**
     *  支付终端
     * @return void
     */
    public function client($type = 'admin')
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * 返回实例
     * @return void
     */
    public function new(){
        $this->getClient();
        $this->setConfig();
        return $this->api;
    }

  /**
     * 小程序服务
     * @return void
     */
    public function data(array $data = [],bool $is_secret = false)
    {
        $this->getClient();
        $this->data = $data;
        if($is_secret){
            $this->data['appid']  = $this->client->appid;
            $this->data['secret'] = $this->client->secret;
        }
        return $this;
    }

    /**
     * POST接口Body快捷入口
     * @param string $uri
     * @return void
     */
    public function postBody(string $uri){
        $this->data = ['body' => $this->data];
        return $this->post($uri);
    }

    /**
     * POST接口Json快捷入口
     * @param string $uri
     * @return void
     */
    public function postJson(string $uri){
        $this->data = ['json' => $this->data];
        return $this->post($uri);
    }

    /**
     * POST接口xml快捷入口
     * @param string $uri
     * @return void
     */
    public function postXml(string $uri){
        $this->data = ['xml' => $this->data];
        return $this->post($uri);
    }

    /**
     * POST接口
     * @return void
     */
    public function post(string $uri)
    {
        try {
            $this->setConfig();
            $response = $this->api->getClient()->post($uri,$this->data);
            if ($response->isFailed()) {
                abort(10001,$response['errmsg']);
            }
            return $response;
        } catch (\Exception $e) {
            exitjson(10002,$e->getMessage());
        }
    }

    /**
     * Get接口
     * @return void
     */
    public function get(string $uri)
    {
        try {
            $this->setConfig();
            $response = $this->api->getClient()->get($uri,['query'=>$this->data]);
            if ($response->isFailed()) {
                abort(10001,$response['errmsg']);
            }
            return $response;
        } catch (\Exception $e) {
            exitjson(10002,$e->getMessage());
        }
    }
}