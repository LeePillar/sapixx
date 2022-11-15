<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 租户端通知
 */
namespace platform\apis\controller\service;
use base\ApiController;
use base\model\SystemTenantBill;
use base\model\SystemTenant;
use EasyWeChat\Pay\Message;

class Tenant extends ApiController{

    /**
     * 租户充值-微信通知
     * @return json
     */
    public function recharge(){
        try {
            $app = app('wepay')->client('admin')->new();
            $server = $app->getServer();
            $server->with(function(Message $message) use ($app){
                $api = $app->getClient();
                $transaction_id = $message['transaction_id'];
                $out_trade_no   = $message['out_trade_no'];
                $response = $api->get('v3/pay/transactions/id/'.$transaction_id.'?mchid='.$app->getMerchant()->getMerchantId());
                if ($response->isSuccessful()) {
                    $bill = SystemTenantBill::where(['order_sn' => $out_trade_no,'state' => 0])->find();
                    if($bill){
                        $bill->message        = '微信充值,openid:'.$message['payer']['openid'];
                        $bill->state          = 1;
                        $bill->transaction_id = $transaction_id;
                        $bill->money          = $message['amount']['total']/100;
                        $bill->save();
                        SystemTenant::moneyInc($bill->tenant_id,$bill->money);
                        return 'SUCCESS';
                    }
                }
                return 'FAIL';
            });
            $response = $server->serve();
            return $response->getBody();
        } catch (\Exception $e) {
            return abort(404,'请求方式不正确:'.$e->getMessage());
        }
    }

    /**
     * 公众号/小程序-授权回调
     * @return void
     */
    public function redirect(){
        $type = $this->request->param('type/d',3);
        if($type != 1 && $type != 2){
            return abort(404,'未找到要授权的应用类型');
        }
        $auth_code = $this->request->param('auth_code/s');
        if(empty($auth_code)){
            return abort(404,'授权码获取失败,请重新授权!');
        }
        $url = (string)url('tenant/wechat/getAuth',['type' => $type,'auth_code' => $auth_code]);
        $this->jump('正在同步授权信息,请稍后',urldecode($url));
    }
}