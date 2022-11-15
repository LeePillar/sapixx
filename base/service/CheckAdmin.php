<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 后台管理切换Session事件
 */
declare(strict_types=1);

namespace base\service;
use think\facade\Session;
use think\facade\Request;
use base\model\SystemApp;
use code\Code;

class CheckAdmin
{

    /**
     * 管理员Session名称
     * @var string
     */
    protected $admin_session = 'sapixx_admin';
    protected $admin_key     = 'sapixx_admin_key';

    /**
     * 管理员当前管理的APPID名称
     * @var string
     */
    protected $app_session     = 'sapixx_apps';
    protected $app_session_key = 'sapixx_apps_key';

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        $this->admin_session   = uuid(5,false,Request::host(true) . "sapixx_admin");
        $this->admin_key       = uuid(5,false,Request::host(true) . "sapixx_admin_key");
        $this->app_session     = uuid(5,false,Request::host(true) . "sapixx_app");
        $this->app_session_key = uuid(5,false,Request::host(true) . "sapixx_app_key");
    }

    /**
     * 判断管理员是否登录
     * @access public
     * @return boolean
     */
    public function getLogin()
    {
        if (Session::has($this->admin_session)) {
            $admin = Code::de(Session::get($this->admin_session),$this->admin_key);
            if ($admin) {
                return json_decode($admin);
            }
        }
        return false;
    }


    /**
     * 登录后台管理Session
     * @access public
     */
    public function setLogin($param)
    {
        $data = [
            'username'        => $param->username,
            'last_login_time' => $param->last_login_time,
            'last_login_ip'   => $param->last_login_ip,
            'id'              => $param->id
        ];
        Session::set($this->admin_session,Code::en(json_encode($data),$this->admin_key));
        Session::save();
    }

    /**
     * 退出后台管理Session
     * @access public
     */
    public function clearLogin()
    {
        Session::delete($this->admin_session);
        Session::delete($this->app_session);
        Session::save();
    }

    /**
     * 以下是当前管理小程序
     * ########################################
     * 获取管理应用信息
     * @return void
     */
    public function getApp($param = '')
    {
        if (Session::has($this->app_session)) {
            $app = Code::de(Session::get($this->app_session),$this->app_session_key);
            if ($app) {
                $app  = json_decode($app);
                $rel = SystemApp::where(['id' => $app->appid])->find();
                if($rel && $param){
                    return $rel->getAttr($param);
                }
                return $rel;
            }
            return false;
        }
    }

    /**
     * 设置管理应用信息
     * @access public
     */
    public function setApp($param)
    {
        Session::set($this->app_session,Code::en(json_encode($param),$this->app_session_key));
        Session::save();
    }

    /**
     * 退出后台管理Session
     * @access public
     */
    public function clearApp()
    {
        Session::delete($this->app_session);
        Session::save();
    }
}
