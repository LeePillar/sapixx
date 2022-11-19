<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * API中间件
 */
namespace base\middleware;

use think\App;
use think\facade\Request;
use base\model\SystemAppsClient;
use think\facade\Config;

class Api
{

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app  = $app;
    }

    public function handle($request, \Closure $next)
    {
        //判断免绑定应用的公共路由
        $noSign = Config('route.controller_layer');
        $client = self::getApps($noSign);
        //必须读取到应用信息
        if ($noSign != 'controller\service' && (empty($client) || !empty($client->apps->is_lock))){
            return enjson(11005,'API请求应用未找到'); 
        }
        //应用信息,赋值给请求器并传值给控制器
        if (!empty($client)){
            $request->client  = $client;
            $request->apps    = $client->apps;
            $request->app     = $client->app;
            $module = strtolower(trim($this->app->http->getName()));
            if($module != $request->app->appname && $module != 'apis'){
                return enjson(404,'请求API资源被拒绝'); 
            }
        }
        //API签名验证
        if ($noSign != 'api\service' && $noSign != 'controller\service'){
            $this->checkSign($client);
        }
        $request->config  = (object)$this->app->config->get('config');
        $request->configs = (object)$this->app->config->get('version');
        $request->user    = $this->app->user->getLogin($request->param('token/s',''));
        return $next($request);
    }

    /**
     * 接口验证
     * @return bool|array
     */
    public static function checkSign($client)
    {
        if(IS_DEBUG || !Config::get('api.sign_auth_on')){
            return true;
        }
        $sign      = Request::param('sign/s'); 
        $timestamp = Request::param('timestamp/d');
        $nonce_str = Request::param('nonce_str/s');
        if (empty($sign) || empty($timestamp) || empty($nonce_str)) {
            exitjson(11003);
        }
        if (time()-$timestamp > Config::get('api.api_sige_time')) {
            exitjson(11004);
        }
        $param['api_id']    = $client->api_id;
        $param['timestamp'] = $timestamp;
        $param['nonce_str'] = $nonce_str;
        if (strcmp(app('jwt')->makeSign($param,$client->api_secret,'md5'),$sign) === 0) {
            return true;
        }
        exitjson(11004);
    }

    /**
     * 读取应用信息
     * @return bool|object
     */
    protected function getApps($noSign)
    {
        $client_id = Request::param('get/s');
        if(empty($client_id)){
            $header = Request::header();
            if (empty($header['sapixx-apiid'])) {
                return false;
            }
            $api_id = $header['sapixx-apiid'];
            if (strlen($api_id) != 18 || !ctype_digit((string)$api_id)) {
                return false;
            }
            return SystemAppsClient::where(['api_id' => $api_id])->cache(true)->find();
        }else{
            if (!preg_match('/^([a-zA-Z0-9]){6}$/',$client_id)) {
                return false;
            }
            if(IS_DEBUG || ($noSign == 'api\service' || $noSign == 'controller\service')){
                return SystemAppsClient::where(['id' => idcode($client_id)])->cache(true)->find();
            }
        }
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
