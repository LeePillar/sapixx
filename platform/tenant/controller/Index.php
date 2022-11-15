<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户端
 */
namespace platform\tenant\controller;
use base\model\SystemTenant;
use base\model\SystemApps;
use base\model\SystemTenantGroup;
use base\model\SystemPlugin;
use think\facade\Config;
use think\facade\Cookie;
use util\Ary;

class Index extends Common
{

    /**
     * 租户首页
     */
    public function index()
    {
        $view['is_siderbar'] = false;
        if($this->request->apps){
            if(empty(app('configs')->siderbar($this->request->app->appname))){
                $plugin = SystemPlugin::whereFindInSet('app_ids',$this->request->app->id)->find(); //检查插件
                if($plugin){
                    $view['is_siderbar'] = true;
                }
            }else{
                $view['is_siderbar'] = true;
            }
        }
        return view()->assign($view);
    }

    /**
     * 应用控制台跳转
     */
    public function welcome()
    {
        if($this->request->apps){
            return redirect((string)url($this->request->app->appname.'/tenant/index'));
        }else{
            return redirect((string)url('tenant/store/index'));
        }
    }

   /**
     * 管理菜单
     * @return json
     */
    public function menu($menu = 'tenant')
    {
        try {
            if($this->request->apps){
                //判断SiderBar的菜单;
                $menu = (empty($menu) || $menu == 'tenant')?'tenant':'tenant_'.$menu; 
                //判断是否自己的应用
                if($this->request->tenant->parent_apps_id == $this->request->apps->id){
                    $menus = $this->app->configs->tenant($this->request->app->appname,$menu);   //全部菜单
                    $info = SystemTenantGroup::where(['id' => $this->request->tenant->group_id])->field('menu')->apps()->find(); //读取数据库中权限组权限
                    $access_menu = empty($info)?[]:array_flip($info->menu);
                    //仅显示有选择的菜单
                    $data = [];
                    foreach ($menus as $key => $value) {
                        $data[$key]['name'] = $value['name'];
                        if(!empty($value['menu'])){
                            foreach ($value['menu'] as $k => $val) {
                                if(isset($val['url']) && isset($access_menu[md5($val['url'])])){
                                    $data[$key]['menu'][$k]['name'] = $val['name'];
                                    $data[$key]['menu'][$k]['url']  = $val['url'];
                                }
                            }
                        }
                        if(empty($data[$key]['menu'])){
                            unset($data[$key]);
                        }
                    }
                    $menus = Ary::reform_keys($data);
                }else{
                    $menus = $this->app->configs->tenant($this->request->app->appname,$menu); //菜单
                    if($menu == 'tenant_plugin' && empty($menus)){
                        $tenant_plugin = config('tenant_plugin');
                        $menus = array_merge($menus,$tenant_plugin);
                    }else{
                        $about_menus = ($menu=='tenant'?($this->request->tenant->lock_config?[]:config('tenant')):[]); //关于应用
                        $menus = array_merge($menus,$about_menus);
                    }
                }
            }else{
                $menus = config('other');
            }
            //  //插件
            //  $plugin = SystemPlugin::whereFindInSet('app_ids',$this->request->app->id)->count(); //检查插件
            //  if($plugin && !empty($siderbar)){
            //      $siderbar = array_merge($siderbar,[['name' =>'扩展','icon' =>'x-diamond','anchor' =>'plugin']]);
            //  }
            return enjson(204,$menus);
        } catch (\Exception $e){
            return enjson(204);
        }
    }

   /**
     * siderBar
     * @return json
     */
    public function siderbar()
    {
        try {
            $siderbar = [];
            if($this->request->apps){
                $siderbar = $this->app->configs->siderbar($this->request->app->appname);
                $info = SystemTenantGroup::where(['id' => $this->request->tenant->group_id])->apps()->find();
                $access_menu = $info?array_flip($info->menu):[];
                if($this->request->tenant->parent_apps_id == $this->request->apps->id){
                    foreach ($siderbar as $key => $var) {
                        $siderbar[$key]['menu'] = 0;
                        $menu = app('configs')->tenant($this->request->app->appname,$var['anchor']=='tenant'?'tenant':'tenant_'.$var['anchor']);
                        foreach ($menu as $value) {
                            if(isset($value['menu'])){
                                foreach ($value['menu'] as $val) {
                                    if(isset($val['url']) && isset($access_menu[md5($val['url'])])){
                                        ++$siderbar[$key]['menu'];
                                    }
                                }
                            }
                        }
                        if($siderbar[$key]['menu'] == 0){
                            unset($siderbar[$key]);
                        }
                    }
                    $siderbar = Ary::reform_keys($siderbar);
                }
                //插件
                $plugin = SystemPlugin::whereFindInSet('app_ids',$this->request->app->id)->count(); //检查插件
                if($plugin){
                    $siderbar = array_merge($siderbar,[['name' =>'扩展','icon' =>'x-diamond','anchor' =>'plugin']]);
                }
            }
            return enjson(204,$siderbar);
        } catch (\Exception $e){
            return enjson(204);
        }
    }
    
    /**
     * 会员首页
     */
    public function login()
    {
        if (IS_POST) {
            $param = [
                'phone_id'       => $this->request->param('phone_id/d'),
                'login_password' => $this->request->param('password/s'),
                'captcha'        => $this->request->param('captcha/s'),
                'settemp'        => $this->request->param('settemp/d',0)
            ];
            $this->validate($param, 'Account.login');
            $rel = SystemTenant::where(['phone_id' => $param['phone_id']])->find();
            if (!$rel || !password_verify(md5($param['login_password']),$rel->password)) {
                return enjson(403,'登录失败或密码错误');
            }
            if($rel->is_lock){
                return enjson(403,'帐号已被锁定,请联系管理员解锁');
            }
            $rel->login_time = time();
            $rel->login_ip   = request()->ip();
            $rel->save();
            if ($rel) {
                $this->app->tenant->clearLogin();
                //判断是否子帐号
                if($rel->parent_id){
                    $parent_apps = SystemApps::where(['id' => $rel->parent_apps_id,'is_lock' => 0])->find();
                    if(empty($parent_apps)){
                        return enjson(403,'管理应用已到期或锁定,请联系主管理续费');
                    }
                    $this->app->tenant->setApps($rel->parent_apps_id);
                }else{
                    //锁定过期应用
                    $apps_ids = SystemApps::where(['tenant_id' => $rel->id,'is_lock' => 0])->whereTime('end_time','<=',time())->column('id');
                    if(!empty($apps_ids)){
                        SystemApps::where(['id' => $apps_ids])->update(['is_lock' => 1]);
                    }
                    $weapp =  SystemApps::where(['tenant_id' => $rel->id,'is_lock' => 0])->field('id')->order('is_default desc,id desc')->find();
                    if($weapp){
                        $this->app->tenant->setApps($weapp->id);
                    }
                }
                $this->app->tenant->setLogin($rel->id);
                if($param['settemp'] == 1){
                    $this->app->tenant->setTempPassword(['user' => $param['phone_id'],'password' => $param['login_password']]);
                }else{
                    $this->app->tenant->clearTempPassword();
                }
                return enjson(302,['url' => (string)url('tenant/index/index')]);
            }
        } else {
            $view['qrcode'] = Config::get('config.we_account.account');
            $view['login']  = $this->app->tenant->getTempPassword();
            return view()->assign($view);
        }
    }

    /**
     * 生成登录二维码
     * @return void
     */
    public  function wechatQrcode(){
        if(IS_POST){
            $access_code = app('code')->en(uuid(0),'','ENCODE',60);
            $response = app('wechat')->client('admin')->data([
                'expire_seconds' => 60,
                'action_name' => 'QR_STR_SCENE',
                'action_info' => [
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
     *检查绑定是否成功
     */
    public  function checkWechatQrcode(){
        if(IS_POST){
            if(Cookie::has('login_ticket')){
                $ticket = Cookie::get('login_ticket');
                $access_code = app('code')->de($ticket);
                if(!$access_code){
                    return enjson(403,'二维码已过期');
                }
                $tenant = SystemTenant::where(['ticket' => $access_code,'is_lock' => 0])->hidden(['safe_password,password,phone_id'])->find();
                if ($tenant) {
                    $tenant->login_time = time();
                    $tenant->login_ip   = request()->ip();
                    $tenant->ticket     = null;
                    $tenant->save();
                    $this->app->tenant->clearLogin();
                    //批量处理锁定应用
                    $apps_ids = SystemApps::where(['tenant_id' => $tenant->id,'is_lock' => 0])->whereTime('end_time','<=',time())->column('id');
                    if(!empty($apps_ids)){
                        SystemApps::where(['id' => $apps_ids])->update(['is_lock' => 1]);
                    }
                    //读取默认应用
                    $weapp = SystemApps::where(['tenant_id' => $tenant->id,'is_lock' => 0])->field('id')->order('is_default desc,id desc')->limit(2)->select();
                    if(!$weapp->isEmpty()){
                        $this->app->tenant->setApps($weapp->toArray()[0]['id']);
                    }
                    $this->app->tenant->setLogin($tenant->id);
                    return enjson(302,['url' => (string)url('tenant/index/index')]);
                }
            }
        }
        return enjson(204,'检测中'); 
    }

    /**
     * 会员注册
     */
    public function register()
    {
        if (request()->isPost()) {
            $param = [
                'phone_id'   => $this->request->param('phone_id/s'),
                'password'   => $this->request->param('password/s'),
                'repassword' => $this->request->param('repassword/s'),
                'sms_code'   => $this->request->param('sms_code/s'),
            ];
            $this->validate($param, 'Account.register');
            if (!app('sms')->isCode($param['phone_id'],$param['sms_code'])) {
                return enjson(403, '验证码错误');
            }
            //判断手机号是否重复
            $info = SystemTenant::where(['phone_id' => $param['phone_id']])->count();
            if ($info) {
                return enjson(403, '手机号重复');
            }
            //验证码通过
            $data['username']   = '用户_'.getcode(6);
            $data['phone_id']   = $param['phone_id'];
            $data['login_time'] = time();
            $data['login_ip']   = request()->ip();
            $data['parent_id']  = 0;
            if(empty($data['password'])){
                $data['password'] = password_hash(md5($param['password']), PASSWORD_DEFAULT);
            }
            if(empty($data['safe_password'])){
                $data['safe_password'] = password_hash(md5(substr($param['phone_id'],-6)), PASSWORD_DEFAULT);
            }
            $rel = SystemTenant::create($data);
            if ($rel) {
                $this->app->tenant->clearLogin();
                $this->app->tenant->setLogin($rel->id);
                return enjson(302,['url' => (string)url('tenant/index/index')]);
            }
        } else {
            return view();
        }
    }

    /**
     * 忘记密码
     */
    public function forgot()
    {
        if (request()->isPost()) {
            $param = [
                'phone_id'   => $this->request->param('phone_id/s'),
                'password'   => $this->request->param('password/s'),
                'repassword' => $this->request->param('repassword/s'),
                'sms_code'   => $this->request->param('sms_code/s'),
            ];
            $this->validate($param, 'Account.register');
            if (!app('sms')->isCode($param['phone_id'],$param['sms_code'])) {
                return enjson(403, '验证码错误');
            }
            $rel = SystemTenant::where(['phone_id' => $param['phone_id']])->find();
            if (!$rel) {
                return enjson(403, '找回密码失败');
            }
            $rel->login_time = time();
            $rel->login_ip   = request()->ip();
            $rel->password   = password_hash(md5($param['password']), PASSWORD_DEFAULT);
            $rel->save();
            if ($rel) {
                $this->app->tenant->clearLogin();
                $this->app->tenant->setLogin($rel->id);
                return enjson(302,['url' => (string)url('tenant/index/index')]);
            }
        } else {
            return view();
        }
    }

    /**
     * 会员退出
     */
    public function logout()
    {
        $this->app->tenant->clearLogin();
        return redirect((string)url('tenant/index/login'));
    }

    /**
     * 获取指定手机短信
     * @return void
     */
    public function getPhoneSms(){
        $phone_id = $this->request->param('phone_id/s');
        $this->validate(['phone_id' => $phone_id],'Account.getsms');
        $rel = app('sms')->getCode($phone_id);
        if(IS_DEBUG){
            return enjson(202,'Debug验证码:'.$rel['code']);
        }
        return enjson(204);
    }

    /**
     * 获取已绑定短信
     * @return void
     */
    public function getSms(){
        $phone_id = $this->request->param('phone_id/s');
        $this->validate(['phone_id' => $phone_id],'Account.getsms');
        $info = SystemTenant::where(['phone_id' => $phone_id])->find();
        if (!$info) {
            return enjson(403,'手机号未注册');
        }
        $rel = app('sms')->getCode($info->phone_id);
        if(IS_DEBUG){
            return enjson(202,'Debug验证码:'.$rel['code']);
        }
        return enjson(204);
    }
}