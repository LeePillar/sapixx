<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 应用设置
 */
namespace platform\tenant\controller;
use base\model\SystemApps;
use base\model\SystemAppsClient;
use base\model\SystemAppsConfig;
use EasyWeChat\Pay\Application;
use util\Dir;
use util\Sign;
use Hashids\Hashids;

class Apps extends Common{

    //初始化
    protected function initialize()
    {
        $tenant = app('tenant')->getLogin();
        if(empty($tenant)){
            $this->jump('没有登录,禁止访问',(string)url('tenant/index/logout'));
        }
        if($tenant->lock_config || $tenant->parent_id){
            $this->jump('没有权限访问应用配置功能');
        }
        $apps = app('tenant')->getApps();
        if(empty($apps)){
            header('Location:'.(string)url('tenant/store/index'));
            exit();
        }
    }

    /**
     * 关于应用
     */
    public function index()
    {
        $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')]]);
        $view['lists']     = SystemAppsClient::order('id desc')->apps()->select();
        $view['configs']   = $this->app->configs->version();
        $view['wechatpay'] = SystemAppsConfig::configs('wechatpay',true);
        return view()->assign($view);
    }

    /**
     * 修改信息信息
     */
    public function edit(){
        if(IS_AJAX){
            $data = [
                'id'    => $this->request->apps->id,
                'logo'  => $this->request->param('logo/s'),
                'title' => $this->request->param('title/s'),
                'about' => $this->request->param('about/s')
            ];
            $this->validate($data,'Apps.edit');
            if(empty($data['logo'])){
                unset($data['logo']);
            }
            SystemApps::update($data);
            return enjson(200,['url' => (string)url('apps/index')]);
        }else{
            return view();
        }
    } 

    /**
     * 接入终端配置
     */
    public function clientConfig(){
        $title = $this->request->param('type/s','web');
        if(!isset($this->request->app->config['types'][$title])){
            $this->error('当前应用,不支持接入终端类型');
        }
        $is_open_wechat = $this->request->app->config['is_open_wechat']?:0;
        if(IS_POST){
            $param = [
                'appid'   => $this->request->param('appid/s'),
                'token'   => $this->request->param('token/s','TOKEN'),
                'aes_key' => $this->request->param('aes_key/s'),
                'secret'  => $this->request->param('secret/s'),
                'title'   => $title,
                'app_id'  => $this->request->apps->app_id,
                'apps_id' => $this->request->apps->id,
            ];
            $this->validate($param,'Apps.config');
            SystemAppsClient::edit($param);
            return enjson(200,['url' => (string)url('apps/index')]);
        }else{
            $info = SystemAppsClient::configs($title);
            if($title == 'wechatmp' || $title == 'wechatapp'){
                if($is_open_wechat && empty($info->config)){
                    return redirect((string)url('tenant/wechat/'.$title));
                }
            }
            $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')],['name' =>'微信公众号']]);
            $view['info']   = $info;
            $view['logo']   = SystemAppsClient::getTypes($title);
            $view['domain'] = '应用未提供Web服务';
            $view['is_web'] = false;
            if(is_dir(PATH_APP.$this->request->app->appname.DS.'web'.DS.$title)){
                if(empty($info)){
                    $view['domain'] = 'Web服务保存配置后开启';
                }else{
                    $view['is_web'] = true;
                    $view['domain'] = $info->domain?'//'.$info->domain:(DAMAIN.'web/'.$info->id_code);
                }
            }
            return view()->assign($view);
        }
    }

     /**
     * 微信支付配置
     */
    public function configWechatPay(){
        if(IS_POST){
            $param = [
                'is_platform_psp' => $this->request->param('is_platform_psp/d',0),
                'is_psp'          => $this->request->param('is_psp/d',0),
                'appid'           => $this->request->param('appid/s'),
                'mchid'           => $this->request->param('mchid/s'),
                'secret'          => $this->request->param('secret/s'),
                'cert'            => $this->request->param('cert/s'),
                'certkey'         => $this->request->param('certkey/s'),
                'secretv3'        => $this->request->param('secretv3/s'),
                'serial_no'       => $this->request->param('serial_no/s'),
                'platform_certs'  => $this->request->param('platform_certs/s')
            ];
            $this->validate($param,'Apps.wechatpay');
            if($param['is_platform_psp'] && $param['is_psp'] ){
                return enjson(403,'服务商模式仅能启用一个');
            }
            $rel = SystemAppsConfig::configs('wechatpay');
            if($rel){
                $data['id']     = $rel['id'];
                $data['config'] = $param;
                SystemAppsConfig::update($data);
            }else{
                $data['apps_id'] = $this->request->apps->id;
                $data['title']   = 'wechatpay';
                $data['config']  = $param;
                SystemAppsConfig::create($data);
            }
            return enjson(200);
        }else{
            $view['type'] = 'wechatpay';
            $view['info'] = SystemAppsConfig::configs('wechatpay',true);
            $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')],['name' =>'微信支付']]);
            return view()->assign($view);
        }
    }

    /**
     * 微信服务商配置
     */
    public function configWechatSp(){
        if(IS_POST){
            $param = [
                'appid'          => $this->request->param('appid/s'),
                'mchid'          => $this->request->param('mchid/s'),
                'secret'         => $this->request->param('secret/s'),
                'cert'           => $this->request->param('cert/s'),
                'certkey'        => $this->request->param('certkey/s'),
                'secretv3'       => $this->request->param('secretv3/s'),
                'serial_no'      => $this->request->param('serial_no/s'),
                'platform_certs' => $this->request->param('platform_certs/s')
            ];
            $this->validate($param,'Apps.wechatpay');
            $rel = SystemAppsConfig::configs('wechatsp');
            if($rel){
                $data['id']     = $rel['id'];
                $data['config'] = $param;
                SystemAppsConfig::update($data);
            }else{
                $data['apps_id'] = $this->request->apps->id;
                $data['title']   = 'wechatsp';
                $data['config']  = $param;
                SystemAppsConfig::create($data);
            }
            return enjson(200);
        }else{
            $view['info'] = SystemAppsConfig::configs('wechatsp',true);
            $view['type'] = 'wechatsp';
            $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')],['name' =>'微信服务商']]);
            return view()->assign($view);
        }
    }

    /**
     * 支付宝配置
     */
    public function configAlipay(){
        if(IS_POST){
            $param = [
                'appid'          => $this->request->param('appid/s'),
                'cert'           => $this->request->param('cert/s'),
                'certkey'        => $this->request->param('certkey/s'),
                'alipaycert'     => $this->request->param('alipaycert/s'),
                'alipayrootcert' => $this->request->param('alipayrootcert/s')
            ];
            $this->validate($param,'Apps.alipay');
            $rel = SystemAppsConfig::configs('alipay');
            if($rel){
                $data['id']     = $rel['id'];
                $data['config'] = $param;
                SystemAppsConfig::update($data);
            }else{
                $data['apps_id'] = $this->request->apps->id;
                $data['title']   = 'alipay';
                $data['config']  = $param;
                SystemAppsConfig::create($data);
            }
            return enjson(200);
        }else{
            $view['info'] = SystemAppsConfig::configs('alipay',true);
            $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')],['name' =>'支付宝']]);
            return view()->assign($view);
        }
    }

    /**
     * 对象储存
     */
    public function configStorage(){
        if(IS_POST){
            $param = [
                'driver'   => $this->request->param('driver/s'),
                'endpoint' => $this->request->param('endpoint/s'),
                'appid'    => $this->request->param('appid/s'),
                'aes_key'  => $this->request->param('aes_key/s'),
                'secret'   => $this->request->param('secret/s'),
                'bucket'   => $this->request->param('bucket/s'),
                'url'      => $this->request->param('url/s')
            ];
            $this->validate($param,'Apps.storage');
            $rel = SystemAppsConfig::configs('storage');
            if($rel){
                $data['id']     = $rel['id'];
                $data['config'] = $param;
                SystemAppsConfig::update($data);
            }else{
                $data['apps_id'] = $this->request->apps->id;
                $data['title']   = 'storage';
                $data['config']  = $param;
                SystemAppsConfig::create($data);
            }
            return enjson(200);
        }else{
            $view['info'] = SystemAppsConfig::configs('storage',true);
            $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')],['name' =>'对象储存']]);
            return view()->assign($view);
        }
    }

    /**
     * 一键同步证书
     */
    public function getCart(){
        $title = $this->request->param('type/s');
        if($title !='wechatsp' && $title != 'wechatpay'){
            return enjson(0,'仅支持微信支付同步证书');
        }
        $rel = SystemAppsConfig::configs($title);
        if(empty($rel)|| empty($rel->config)){
            return enjson(0,'未保存配置禁止同步证书');
        }
        $config = $rel->config;
        if (strlen($config['secretv3']??null) != 32) {
            return enjson(0,'无效的APIv3密钥,长度应为32个字节');
        }
        if(!is_file($config['certkey']??null)){
            return enjson(0,'支付证书必须上传');
        }
        if(!is_file($config['cert']??null)){
            return enjson(0,'支付证书秘钥必须上传');
        }
        if(!Dir::isDirs(PATH_RUNTIME)){
            return enjson(0,'目录Runtime无写权限,请联系你的服务商');
        }
        //保存目录(如果没有权限自动创建)
        $hashids = new Hashids(config('api.jwt_salt'),6,config('api.safeid_meta'));
        $storage = PATH_STORAGE.DS.$this->request->app->appname.DS.$hashids->encode($this->request->apps->id).DS;
        if(!Dir::isDirs($storage)){
            Dir::mkDirs($storage);
        }
        //支付配置
        $setting['mch_id']        = $config['mchid'];
        $setting['secret_key']    = $config['secretv3'];
        $setting['private_key']   = $config['certkey'];
        $setting['certificate']   = $config['cert'];
        $setting['http']['throw'] = false;
        $app = new Application($setting);
        $response = $app->getClient()->get('v3/certificates');
        if ($response->isFailed()) {
            return enjson(0,$response['message']);
        }
        if(empty($response['data'][0])){
            return enjson(0,'平台证书下载失败');
        }
        $data = $response['data'][0];
        $ciphertext = Sign::decryptCiphertext($data,$setting['secret_key']);
        $cert = $storage.$data['serial_no'].'.pem';
        if(!Dir::fileBody($cert,$ciphertext)){
            return enjson(0,'平台正式保存失败,请查看runtime是否有写权限');
        }
        $config['platform_certs'] = str_replace('\\','/',substr($cert,strlen(PATH_STORAGE)));
        $config['serial_no']      = $data['serial_no'];
        $rel->config = $config;
        $rel->save();
        return enjson(200,'微信支付3.0平台证书同步成功');
    }
}