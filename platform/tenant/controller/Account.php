<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 帐号管理
 */
namespace platform\tenant\controller;
use base\model\SystemTenant;
use base\model\SystemApps;
use base\model\SystemTenantBill;
use think\facade\Cookie;
use think\exception\ValidateException;
use time\Time;

class Account extends Common{

    /**
     * 个人信息
     * @return \think\response\View
     */
    public function index(int $types = 0,int $money = 0){
        if($this->request->tenant->parent_id){
            $apps = SystemApps::where(['id'=>$this->request->tenant->parent_apps_id,'is_lock'=> $types])->order('id desc')->select();
        }else{
            $apps = SystemApps::where(['tenant_id'=>$this->request->tenant->id,'is_lock'=> $types])->order('is_default desc,id desc')->select();
        }
        $apps->filter(function($value){
            $value->logo = $value->logo ?: $value->app->logo;
            return $value;
        });
        $view['apps'] = $apps;
        //统计
        $view['apps_count'] = SystemApps::where(['tenant_id' => $this->request->tenant->id])->count();
        $view['consumes']   = SystemTenantBill::where([['state','=',1],['money','<',0],['tenant_id','=',$this->request->tenant->id]])->sum('money');
        //账单
        $condition[] = ['tenant_id','=',$this->request->tenant->id];
        $condition[] = ['state','=',1];
        if($money){
            $condition[] = ['money',$money == 1?'>':'<',0];
        }
        $view['bill']       = SystemTenantBill::where($condition)->order('id desc')->paginate($this->pages);
        $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')]];
        return view()->assign($view);
    }

    /**
     * 修改登录手机号
     */
    public function edit(){
        if(IS_POST){
            $param['username'] = $this->request->param('username/s');
            $this->validate($param,'Account.edit');
            $data['username'] = $param['username'];
            $data['id']       = $this->request->tenant->id;
            SystemTenant::update($data);
            return enjson(200,['url' => (string)url('account/index')]);
        }else{
            $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')],['name' =>'基本信息']];
            return view()->assign($view);
        }
    }

   /**
     *修改绑定手机号
     */
    public function phone()
    {
        if (IS_AJAX) {
            $param = [
                'sms_code' => $this->request->param('sms_code/d'),
                'phone_id' => $this->request->param('phone_id/d'),
            ];
            $this->validate($param,'Account.phone');
            if(!app('sms')->isCode($param['phone_id'],$param['sms_code'])){
                return enjson(403,'验证码错误');
            }
            $rel = SystemTenant::where(['phone_id' => $param['phone_id']])->find();
            if($rel){
                return enjson(403,'手机号重复');
            }
            $data['phone_id'] = $param['phone_id'];
            $data['id']       = $this->request->tenant->id;
            SystemTenant::update($data);
            return enjson(200,['url' => (string)url('account/index')]);
        } else {
            $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')],['name' =>'绑定手机']];
            return view()->assign($view);
        }
    }

    /**
     * 绑定微信扫码登录
     * @return void
     */
    public function bindWechat()
    {
        if (IS_AJAX) {
            $param = [
                'sms_code' => $this->request->param('sms_code/d'),
                'phone_id' => $this->request->param('phone_id/d'),
            ];
            $this->validate($param,'Account.phone');
            if(!app('sms')->isCode($param['phone_id'],$param['sms_code'])){
                return enjson(403,'验证码错误');
            }
            $rel = SystemTenant::where(['phone_id' => $param['phone_id']])->find();
            if($rel){
                return enjson(403,'手机号重复');
            }
            $data['phone_id'] = $param['phone_id'];
            $data['id']       = $this->request->tenant->id;
            SystemTenant::update($data);
            return enjson(200,['url' => (string)url('account/index')]);
        } else {
            $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')],['name' =>'绑定微信']];
            return view()->assign($view);
        }
    }


    /**
     * 修改密码
     */
    public function password()
    {
        if (IS_AJAX) {
            $param = [
                'login_password' => $this->request->param('login_password/s'),
                'password'       => $this->request->param('password/s'),
                'repassword'     => $this->request->param('repassword/s'),
            ];
            $this->validate($param,'Account.password');
            $data['password'] = password_hash(md5($param['password']), PASSWORD_DEFAULT);
            $data['id']       = $this->request->tenant->id;
            SystemTenant::update($data);
            return enjson(200,['is_parent' => true,'url' => (string)url('tenant/index/logout')]);
        } else {
            $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')],['name' =>'修改密码']];
            return view()->assign($view);
        }
    }

    /**
     *安全密码
     */
    public function safePassword()
    {
        if (IS_AJAX) {
            $param = [
                'sms_code'              => $this->request->param('sms_code/d'),
                'safe_password'         => $this->request->param('safe_password/d'),
                'safe_password_confirm' => $this->request->param('safe_password_confirm/d'),
            ];
            $this->validate($param,'Account.safepassword');
            //判断验证码
            if(!app('sms')->isCode($this->request->tenant->phone_id,$param['sms_code'])){
                return enjson(403,'验证码错误');
            }
            $data['safe_password'] = password_hash(md5($param['safe_password']),PASSWORD_DEFAULT);
            $data['id']            = $this->request->tenant->id;
            SystemTenant::update($data);
            return enjson(200,['url' => (string)url('account/index')]);
        } else {
            $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')],['name' =>'安全密码']];
            return view()->assign($view);
        }
    }

    /**
     * 账户充值
     * @return \think\response\View
     */
    public function recharge(){
        if(IS_POST){
            $param = [
                'money' => $this->request->param('money/d'),
                'agree' => $this->request->param('agree/d',0)
            ];
            $this->validate($param,'Account.recharge');
            $data['description']  = '租户微信充值';
            $data['out_trade_no'] = uuid();
            $rel = app('wepay')->client('admin')->data($data)->fee($param['money'])->notify(apis('service/tenant/recharge'))->postJson('v3/pay/transactions/native');
            $data['code_url'] = $rel['code_url'];
            SystemTenantBill::create(['tenant_id' => $this->request->tenant->id,'state' => 0,'money' => $param['money'],'order_sn' => $data['out_trade_no']]);
            return enjson(204,$data);
        }else{
            $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')],['name' =>'帐号充值']];
            return view()->assign($view);
        }
    } 
    
    /**
     * 应用续期
     * @return \think\response\View
     */
    public function appsRenewal(){
        if(IS_POST){
            $data = [
                'apps_id'      => $this->request->param('apps_id/d'),
                'safepassword' => $this->request->param('safepassword/s'),
                'agree'        => $this->request->param('agree/d',0)
            ];
            $this->validate($data,'Apps.renewal');
            if(!password_verify(md5($data['safepassword']),$this->request->tenant->safe_password)) {
                return enjson(403,'你输入的安全密码不正确');
            }
            //读取应用
            $apps = SystemApps::where(['id' => $data['apps_id']])->find();
            if (empty($apps)) {
                return enjson(403,'未找到你开通的应用');
            }
            //判断帐号额度,如果价格<=0,就不在查询数据库
            if ($apps->app->price > 0) {
                $price = $apps->app->price;
                if(!empty($this->request->tenant->agent)){
                    $price = money($apps->app->price*$this->request->tenant->agent->discount/10);
                }
                if (!SystemTenant::moneyDec($this->request->tenant->id,$price)) {
                    return enjson(403,'续期余额不足,请先充值');
                }
                //增加账单
                SystemTenantBill::create(['tenant_id' => $this->request->tenant->id,'state' => 1,'money' => -$price,'message' => '续期'.$apps->title.'应用','order_sn' => uuid()]);
            }
            $apps->end_time = Time::daysAfter(365,$apps->getData('end_time'));
            $apps->is_lock  = 0;
            $apps->save();
            return enjson(200,'成功续期一年',['url' => (string)url('account/index')]);
        }else{
            if($this->request->tenant->parent_id){
                $apps = SystemApps::where(['id'=>$this->request->tenant->parent_apps_id])->find();
            }else{
                $id = $this->request->param('id/d');
                $apps = SystemApps::where(['tenant_id'=>$this->request->tenant->id,'id' => $id])->find();
            }
            if(empty($apps)){
                $this->error('未找到续期应用');
            }
            $apps->logo   = $apps->logo ?: DAMAIN."static/{$apps->app->appname}/logo.png";
            $view['info'] = $apps;
            $view['breadcrumb'] = [['name' =>'控制台','icon' => 'house'],['name' =>'我的帐号','url' => (string)url('account/index')],['name' =>'应用续期']];
            return view()->assign($view);
        }
    } 

    /**
     * 设置为默认应用
     * @param integer $id
     * @return void
     */
    public function setDefault(int $id){
        SystemApps::where(['tenant_id' =>$this->request->tenant->id])->update(['is_default' => 0]);
        SystemApps::where(['id' => $id,'tenant_id' =>$this->request->tenant->id])->update(['is_default' => 1]);
        return enjson(200);
    }

    /**
     * 检测手机号是否重复
     */
    public function isphone(){
        $phone_id  = $this->request->param('phone_id');
        try {
            validate(['phone_id' => 'require|mobile'])->check(['phone_id'  => $phone_id]);
        } catch (ValidateException $e) {
            return json(['error'=>'手机号不正确']);
        }
        $result = SystemTenant::where(['phone_id' => $phone_id])->find();
        if($result){
            return json(['error'=>'手机号已绑定其它帐号']);
        }else{
            return json(['ok'=>'手机号可用']);
        }
    }
    
    /**
     * 获取指定手机短信
     * @return void
     */
    public function getPhoneSms(){
        $phone_id = $this->request->param('phone_id/s');
        $this->validate(['phone_id' => $phone_id],'Account.getsms');
        $sms = app('sms')->getCode($phone_id);
        return enjson(204,$sms);
    }

    /**
     * 获取已绑定短信
     * @return void
     */
    public function getSms(){
        $sms = app('sms')->getCode($this->request->tenant->phone_id);
        return enjson(204,$sms);
    }

    /**
     * 切换管理已开通的应用
     * @param integer $id 用户ID
     */
    public function manage(int $id = 0){
        if($this->request->tenant->parent_id && $id == $this->request->tenant->parent_apps_id){
            $rel = SystemApps::where(['is_lock' => 0,'id' => $id])->find();
        }else{
            $rel = SystemApps::where(['tenant_id' => $this->request->tenant->id,'is_lock' => 0,'id' => $id])->find();
        }
        if(empty($rel)){
            return enjson(403,'此应用已被禁用');
        }
        $this->app->tenant->clearApps();
        $this->app->tenant->setApps($rel->id);
        return enjson(200);
    }

    /**
     * 生成绑定二维码
     * @return void
     */
    public function bindWechatQrcode(){
        if(IS_POST){
            $access_code = app('code')->en($this->request->tenant->id,'','ENCODE',60);
            $response = app('wechat')->client('admin')->data([
                'expire_seconds' => 60,
                'action_name'    => 'QR_STR_SCENE',
                'action_info'    => [
                    'scene' => [
                        'scene_str' => $access_code
                    ]
                ]
            ])->postJson('cgi-bin/qrcode/create');
            Cookie::set('login_ticket',$access_code,$response['expire_seconds']);
            return enjson(204,['url' => $response['url']]);
        }
        return enjson(404);
    }

    /**
     *检查充值是否成功
     */
    public function checkRecharge(){
        if(IS_POST){
            $ordersn = $this->request->param('ordersn/s');
            if(empty($ordersn)){
                return enjson(403,'订单号不能为空');
            }
            $rel = SystemTenantBill::where(['order_sn' => $ordersn,'tenant_id' => $this->request->tenant->id,'state' => 1])->find();
            if(!empty($rel)){
                return enjson(200,['url' => (string)url('tenant/account/index')]); 
            }
        }
        return enjson(204);
    }

    /**
     *检查绑定是否成功
     */
    public function checkWechatQrcode(){
        if(IS_POST){
            if(Cookie::has('login_ticket')){
                $ticket = Cookie::get('login_ticket');
                $access_code = app('code')->de($ticket);
                if(!$access_code){
                    return enjson(403,'二维码已过期');
                }
                $tenant = $this->request->tenant;
                if(strcmp($ticket,$tenant->ticket) == 0){
                    $tenant->ticket = null;
                    $tenant->save();
                    Cookie::delete('login_ticket');
                    return enjson(200,['url' => (string)url('tenant/account/index')]); 
                }
            }
        }
        return enjson(204);
    }

    /**
     * 读取租户并返回JSON
     * @param integer $title 用户ID
     */
    public function apps(){
        $apps = SystemApps::with(['app'	=> function($query) {
            $query->field('logo,id,appname');
        }])->where(['tenant_id' => $this->request->tenant->id,'is_lock' => 0])->field('id,app_id,title,logo')->order('is_default desc,id desc')->select();
        $apps->filter(function($value){
            $value->logo = $value->logo ?: $value->app->logo;
            return $value;
        });
        return enjson(204,$apps);
    }
}