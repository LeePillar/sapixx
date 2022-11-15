<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 后台首页
 */
namespace platform\admin\controller;
use base\model\SystemAdmin;
use base\model\SystemApps;
use base\model\SystemTenantBill;
use base\model\SystemTenant;
use time\Time;

class Index extends Common
{

    /**
     * 后台主框架
     */
    public function index()
    {
        return view();
    }

    /**
     * 后台控制台
     */
    public function console(int $money = 0)
    {
        $month = Time::month();
        $view['money']    = SystemTenantBill::where([['create_time','>=',$month[0]],['state','=',1],['money','>',0]])->sum('money');
        $view['consume']  = SystemTenantBill::where([['create_time','>=',$month[0]],['state','=',1],['money','<',0]])->sum('money');
        $view['tenant']   = SystemTenant::where([['create_time','>=',$month[0]]])->count();
        $view['apps']     = SystemApps::where([['create_time','>=',$month[0]]])->count();
        $view['moneys']   = SystemTenant::sum('money');
        $view['consumes'] = SystemTenantBill::where([['state','=',1],['money','<',0]])->sum('money');
        $condition = [];
        if($money){
            $condition[] = ['money',$money == 1?'>':'<',0];
        }
        $view['bill']       = SystemTenantBill::where($condition)->order('id desc')->paginate($this->pages);
        $view['breadcrumb'] = [['name' =>'控制台','icon' =>'window'],['name' =>'数据统计']];
        return view()->assign($view);
    }

    /**
     * 管理菜单
     * @return json
     */
    public function menu()
    {
        $app = $this->request->param('app/d',0);
        $menu = ($app && $this->request->app)?$this->app->configs->admin($this->request->app->appname):config('admin');
        return enjson(204,$menu);
    }

    /**
     * 后台登录
     */
    public function login()
    {
        if (IS_AJAX) {
            $data = [
                'captcha'        => $this->request->param('captcha/s'),
                'login_id'       => $this->request->param('login_id/s'),
                'login_password' => $this->request->param('login_password/s')
            ];
            $this->validate($data,'Admin.login');
            $result  = SystemAdmin::login($data);
            if ($result) {
                $this->app->admin->setlogin($result);
                return enjson(302,['url' => (string)url('admin/index/index')]);
            }
            return enjson(11105);
        } else {
            return view();
        }
    }

    
    /**
     * 修改密码
     */
    public function password()
    {
        if (IS_AJAX) {
            $param = [
                'password'         => $this->request->param('password/s'),
                'password_confirm' => $this->request->param('repassword/s'),
            ];
            $this->validate($param, 'Admin.password');
            $data['password'] = password_hash(md5($param['password']), PASSWORD_DEFAULT);
            $data['id']       = $this->request->admin->id;
            SystemAdmin::update($data);
            return enjson(200,['is_parent' => true,'url' => (string)url('admin/index/logout')]);
        } else {
            $view['info'] = SystemAdmin::where(['id' => $this->request->admin->id])->find();
            $view['breadcrumb'] = [['name' =>'控制台'],['name' =>'修改密码']];
            return view()->assign($view);
        }
    }

    /**
     * 退出
     */
    public function logout()
    {
        $this->app->admin->clearLogin();
        return redirect((string)url('admin/index/login'));
    }
}
