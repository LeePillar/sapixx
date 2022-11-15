<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 微信支付服务
 */
namespace base\logic;
use think\Request;
use think\Service;
use think\facade\Config;
use base\model\SystemAppsConfig;
use EasyWeChat\Pay\Application;

class Wepay extends Service{

    protected $request;
    protected $api;
    protected $wechat_sp; //服务商配置
    protected $config; //商户号配置
    protected $type       = 'tencent'; //支付终端(admin是平台充值,不走服务商)
    protected $data       = [];

    public const V3_URI_PREFIXES = [
        '/v3/',
        '/sandbox/v3/',
        '/hk/v3/',
        '/global/v3/',
    ];

    public function __construct(Request $request)
    {
        $this->request  = $request;
    }

     /**
     * 读取配置
     * @return void
     */
    private function getClient(){
        try {
            $this->wechat_sp = false;
            if($this->type  == 'admin'){
                $this->config = (object)Config::get('config.wechat_pay');
                $this->config->is_platform_psp = 0;
                $this->config->is_psp = 0;
            }else{
                //读取支付配置
                $result  = SystemAppsConfig::where(['title' => 'wechatpay'])->field('config,title')->apps()->find();
                if(empty($result)){
                    abort(11001,'API微信接入配置有误');
                }
                $this->config = (object)$result->config;
                //微信支付OPENID读取
                if($this->request->user){
                    //默认赋值,V3不需要后面用unset删除
                    $this->data['openid'] = $this->request->user->client->login_id;  
                    //判断直连还是服务商
                    if($this->config->is_psp == 0 && $this->config->is_platform_psp == 0){
                        $this->data['payer']['openid'] = $this->data['openid'];  //直链
                    }else{
                        $this->data['payer']['sub_openid'] = $this->data['openid'];   //服务商
                    }
                }
                //平台服务商
                if($this->config->is_platform_psp == 1){
                    $this->wechat_sp = (object)Config::get('config.wechat_sp');
                }
                //自助服务商
                if($this->config->is_psp == 1){ 
                    $wechatsp = SystemAppsConfig::where(['title' => 'wechatsp'])->field('config')->apps()->find();
                    if(empty($wechatsp)){
                        abort(11001,'微信支付服务商配置错误');
                    }
                    $this->wechat_sp = (object)$wechatsp->config;
                }
            }
            return $this;
        } catch (\Exception $e) {
            exitjson(10001,$e->getMessage());
        }
    }

    /**
     * 实例化对象并写入配置
     * @return void
     */
    private function setConfig(){
        //判断是否服务商
        if($this->wechat_sp){
            $wechatpay = $this->wechat_sp;
        }else{
            $wechatpay = $this->config;
        }
        $config = [
            'mch_id'         => $wechatpay->mchid,
            'v2_secret_key'  => $wechatpay->secret,  //V2
            'secret_key'     => $wechatpay->secretv3 //V3
        ];
        //在非系统下
        $this->config->certkey        = $wechatpay->certkey?PATH_STORAGE.$wechatpay->certkey:'';
        $this->config->cert           = $wechatpay->cert?PATH_STORAGE.$wechatpay->cert:'';
        $this->config->platform_certs = $wechatpay->platform_certs?PATH_STORAGE.$wechatpay->platform_certs:'';
        if(file_exists($this->config->certkey)){
            $config['private_key'] = $this->config->certkey;
        }
        if(file_exists($this->config->cert)){
            $config['certificate'] = $this->config->cert;
        }
        if(file_exists($this->config->platform_certs)){
            $config['platform_certs'][] = $this->config->platform_certs;
        }
        $config['http']['throw'] = false;
        $this->api = new Application($config);
        return $this;
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
     * 支付的数据
     * @return void
     */
    public function data(array $data = [])
    {
        $this->data = array_merge($this->data,$data);
        return $this;
    }

    /**
     *  H5支付
     * @return void
     */
    public function fee(float $fee)
    {
        $this->data['total_fee'] = $fee*100;
        return $this;
    }

    /**
     *  产品描述
     * @return void
     */
    public function name(string $title)
    {
        $this->data['description'] = $title;
        return $this;
    }

    /**
     *  订单号
     * @return void
     */
    public function orderid(string $orderid)
    {
        $this->data['out_trade_no'] = (string)$orderid;
        return $this;
    }

    /**
     * 公众号小程序
       * @return void
     */
    public function jsapi()
    {
        $this->data['trade_type'] = 'JSAPI';
        return $this;
    }

    /**
     * APP支付
     */
    public function app()
    {
        $this->data['trade_type'] = 'APP';
        return $this;
    }

    /**
     * 扫一扫
     */
    public function scan()
    {
        $this->data['trade_type'] = 'NATIVE';
        return $this;
    }

    /**
     *  H5支付
     */
    public function h5()
    {
        $this->data['trade_type'] = 'MWEB';
        return $this;
    }

    /**
     * 通知的网址
     * @return void
     */
    public function notify(string $notify_url)
    {
        $this->data['notify_url'] = $notify_url;
        return $this;
    }

    /**
     * POST接口xml快捷入口(v2)
     * @param string $uri
     * @return void
     */
    public function postXml(string $uri){
        $this->getClient();
        $this->setConfig();
        if($this->wechat_sp){
            $this->data['appid']      = $this->wechat_sp->appid;
            $this->data['mch_id']     = $this->wechat_sp->mchid;
            $this->data['sub_appid']  = $this->config->appid;
            $this->data['sub_mch_id'] = $this->config->mchid;
        }else{
            $this->data['appid']  = $this->config->appid;
            $this->data['mch_id'] = $this->config->mchid;
        }
        $this->data = ['xml' => $this->data];
        return $this->post($uri);
    }

    /**
     * POST接口Json快捷入口(v3)
     * @param string $uri
     * @return void
     */
    public function postJson(string $uri){
        $this->getClient();
        $this->setConfig();
        if($this->wechat_sp){
            $this->data['sp_appid']  = (string)$this->wechat_sp->appid;
            $this->data['sp_mchid']  = (string)$this->wechat_sp->mchid;
            $this->data['sub_appid'] = (string)$this->config->appid;
            $this->data['sub_mchid'] = (string)$this->config->mchid;
        }else{
            $this->data['appid']  = (string)$this->config->appid;
            $this->data['mchid']  = (string)$this->config->mchid;
        }
        //兼容APIV3
        if($this->isV3Request($uri)){
            if(isset($this->data['total_fee'])){
                $this->data['amount']['total'] = (int)$this->data['total_fee'];
                unset($this->data['total_fee']);
            }
            if($this->request->user){ //v3不需要这个参数
                unset($this->data['openid']);
            }
        }else{
            if($this->request->user){ //V2不需要这个参数
                unset($this->data['payer']);
            }
        }
        $this->data = ['json' => $this->data];
        return $this->post($uri);
    }

    /**
     * POST接口快捷入口
     * @param string $uri
     * @return void
     */
    public function post(string $uri){
        $response = $this->api->getClient()->post($uri,$this->data);
        if($this->isV3Request($uri)){
            if ($response->isFailed() || isset($response['code'])) {
                exitjson(10002,$response['message']);
            }
        }else{
            if ($response->isFailed() || ($response['return_code']??'FAIL') != 'SUCCESS') {
                exitjson(10002,$response['return_msg']);
            }
        }
        return $response;
    }

    /**
     * 判断是否V3接口
     *
     * @param string $url
     * @return boolean
     */
    protected function isV3Request(string $url): bool
    {
        foreach (self::V3_URI_PREFIXES as $prefix) {
            if (\str_starts_with('/'.ltrim($url,'/'), $prefix)) {
                return true;
            }
        }
        return false;
    }

    
    /**
     * 编译WeixinJSBridge
     * @param boolean $prepayId
     * @param boolean $signType  默认RSA，v2要传MD5
     * @return void
     */
    public function buildWeixinJs(string $prepayId, string $signType = 'RSA')
    {
        $this->getClient();
        $this->setConfig();
        $utils = $this->api->getUtils();
        return $utils->buildBridgeConfig($prepayId,(string)$this->config->appid,$signType);
    } 

    /**
     * 编译JSSDK
     * @param boolean $prepayId
     * @param boolean $signType  默认RSA，v2要传MD5
     * @return void
     */
    public function buildjsdk(string $prepayId, string $signType = 'RSA')
    {
        $this->getClient();
        $this->setConfig();
        $utils = $this->api->getUtils();
        return $utils->buildSdkConfig($prepayId,(string)$this->config->appid,$signType);
    }

    /**
     * 编译小程序
     * @param boolean $prepayId
     * @param boolean $signType  默认RSA，v2要传MD5
     * @return void
     */
    public function buildWeapp(string $prepayId, string $signType = 'RSA')
    {
        $this->getClient();
        $this->setConfig();
        $utils = $this->api->getUtils();
        return $utils->buildMiniAppConfig($prepayId,(string)$this->config->appid,$signType);
    }

    /**
     * 编译小程序
     * @param boolean $prepayId
     * @param boolean $signType  默认RSA，v2要传MD5
     * @return void
     */
    public function buildApp(string $prepayId, string $signType = 'RSA')
    {
        $this->getClient();
        $this->setConfig();
        $utils = $this->api->getUtils();
        return $utils->buildAppConfig($prepayId,(string)$this->config->appid,$signType);
    }
}