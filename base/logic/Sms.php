<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 短信验证码服务
 */
namespace base\logic;
use think\Request;
use think\facade\Session;
use think\api\Client;
use base\model\SystemTenant;
use base\model\SystemTenantBill;

class Sms
{

    protected $sms_session_name  = 'sapixx#com/smscode';  //SESSION值
    protected $request;
    protected $expire  = 120;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->sms_session_name = uuid(5,false,$this->request->host(true)."sapixx#com/smscode");
    }

    /**
     * 短信验证码发送
     * @param integer $phone_id  接受手机号
     * @param integer $tenant_id 用户ID
     * @param string  $tpl_name 签名
     * @return void
     */
    public function getCode(int $phone_id, int $is_buy = 0)
    {

        $data['phone_id'] = $phone_id;
        $data['code']     = getcode(4);
        if (IS_DEBUG) { 
            try {
                $config = config('config.topthink_api');
                if ($is_buy && $this->request->tenant) {
                    if (money($this->request->tenant->money+$this->request->tenant->lock_money) < money($config['price'])) {
                        abort(403, '帐号余额不足,请联系应用服务商');
                    }
                    SystemTenantBill::create(['money' =>-$config['price'],'state' => 1,'tenant_id' => $this->request->tenant->id,'order_sn'=>uuid(),'message' => '发送短信']);
                    SystemTenant::moneyDec($this->request->tenant->id,$config['price']);
                }
                //短信发送请求
                $client = new Client($config['appcode']);
                $result = $client->smsSend()->withSignId($config['signId'])->withTemplateId($config['templateId'])->withPhone($phone_id)->withParams(json_encode(['code'=>$data['code']]))->request();
                if (isset($result) && $result['code'] != 0) {
                    abort(403,$result['message']);
                }
            }catch (\Exception $e) {
                exitjson(403,$e->getMessage());
            } 
        }
        self::setCode($data);
        return IS_DEBUG?$data:[];
    }

    /**
     * 设置验证码
     * @param  string $phone_id 验证手机号
     * @param  string $sms_code 验证码验证码
     */
    public function setCode(array $data)
    {
        Session::set($this->sms_session_name,$data);
        Session::save();
    }

    /**
     * 判断验证码
     * @param  string $phone_id 验证手机号
     * @param  string $sms_code 验证码验证码
     */
    public function isCode($phone_id,$sms_code)
    {
        if (!Session::has($this->sms_session_name)) {
            return false;
        }
        $smscode = Session::pull($this->sms_session_name);
        if ($smscode['phone_id'] != $phone_id || $smscode['code'] != $sms_code) {
            return false;
        }
        return true;
    }
}