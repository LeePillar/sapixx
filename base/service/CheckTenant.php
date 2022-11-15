<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户登录管理
 */
declare(strict_types=1);

namespace base\service;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Request;
use base\model\SystemTenant;
use base\model\SystemApps;
use code\Code;

class CheckTenant
{

    /**
     * 租户Session名称
     * @var string
     */
    protected $tenant_session = 'sapixx_tenant';
    protected $tenant_key    = 'sapixx_tenant_key';
    
    /**
     * 租户当前管理的应用
     * @var string
     */
    protected $apps_session  = 'sapixx_tenant_apps';
    protected $apps_key      = 'sapixx_tenant_apps_key';

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        $this->tenant_session = uuid(4,false,Request::host(true)."sapixx_tenant");
        $this->tenant_key     = uuid(4,false,Request::host(true)."sapixx_tenant_key");
        $this->apps_session   = uuid(4,false,Request::host(true)."sapixx_tenant_apps");
        $this->apps_key       = uuid(4,false,Request::host(true)."sapixx_tenant_apps_key");
    }

    /**
     * ##########################################
     * 保存登录密码
     * @access public
     */
    public function setTempPassword(array $param)
    {
        Cookie::forever('temp_login_id',Code::en(json_encode($param)));
        Cookie::save();
    }

     /*** 
      * 获取登录密码
     */
    public function getTempPassword()
    {
        if (Cookie::has('temp_login_id')) {
            return json_decode(Code::de(Cookie::get('temp_login_id')),true);
        }
    } 

     /*** 
      * 清楚记住密码
     */
    public function clearTempPassword()
    {
        Cookie::delete('temp_login_id');
    } 

    /**
     * ##########################################
     * 判断是否登录
     * @access public
     * @return boolean
     */
    public function getLogin()
    {
        if (Session::has($this->tenant_session)) {
            $tenant_id = Code::de(Session::get($this->tenant_session),$this->tenant_key);
            if ($tenant_id) {
                return SystemTenant::where(['id' => $tenant_id, 'is_lock' => 0])->hidden(['password','safe_password'])->find();
            }
        }
        return false;
    }

    /**
     * 登录后台管理Session
     * @access public
     */
    public function setLogin($id)
    {
        Session::set($this->tenant_session,Code::en($id,$this->tenant_key));
        Session::save();
    }

    /**
     * 退出后台管理Session
     * @access public
     */
    public function clearLogin()
    {
        Session::delete($this->tenant_session);
        Session::delete($this->apps_session);
        Session::save();
    }

    /**
     * 管理的应用
     * @param string $param //字段名
     * @return void
     */
    public function getApps($param = '')
    {
        if (Session::has($this->apps_session)) {
            $weapp_id = Code::de(Session::get($this->apps_session),$this->apps_key);
            if ($weapp_id) {
                $apps = SystemApps::where(['id' => $weapp_id, 'is_lock' => 0])->find();
                if($apps){
                    $apps->logo = $apps->logo ?: DAMAIN."static/{$apps->app->appname}/logo.png";
                    if($param){
                        return $apps[$param];
                    }
                    return $apps;
                }
            }
        }
        return false;
    }

    /**
     * 设置管理应用信息
     * @access public
     */
    public function setApps($id)
    {
        Session::set($this->apps_session,Code::en($id,$this->apps_key));
        Session::save();
    }

    /**
     * 退出管理应用
     * @access public
     */
    public function clearApps()
    {
        Session::delete($this->apps_session);
        Session::save();
    }
}
