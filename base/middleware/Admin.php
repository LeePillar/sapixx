<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 后台中间件
 */

namespace base\middleware;

use think\App;
use think\facade\Request;
use think\facade\View;

class Admin
{

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app  = $app;
    }

    public function handle($request, \Closure $next)
    {
        //读取站点信息
        $request->config  = (object)$this->app->config->get('config');
        $request->configs = (object)$this->app->config->get('version');
        $request->web     = $request->config->site;
        //IP访问后台的安全权限
        if (!IS_DEBUG && !empty($request->web['safe_ip'])) {
            if ($request->ip() != '127.0.0.1' && !strpos($request->web['safe_ip'], $request->ip())) {
                return redirect($request->domain());
            }
        }
        //当前请求方法
        $module     = strtolower(trim($this->app->http->getName()));
        $controller = strtolower(trim($request->controller()));
        $action     = strtolower(trim($request->action()));
        $noLogin    = ['admin' => ['index' => ['login','logout']]];  //不需要登录验证的页面
        //如果当前访问是无需登录验证则直接返回  
        if (isset($noLogin[$module])) {
            if (isset($noLogin[$module][$controller]) && in_array($action, $noLogin[$module][$controller])) {
                View::assign(['web' => $request->web,'configs' => (array)$request->configs]);
                return $next($request);
            }
        }
        //登录用户信息
        $request->admin = $this->app->admin->getLogin() ?: false;
        //判断是否登录,没有登录直接退出
        if (empty($request->admin)) {
            $this->app->admin->clearLogin();
            return response('<script type="text/javascript">if(top.location.href!=self.location.href){top.location="'.(string)url('admin/index/logout').'"}else{window.location.href="'.(string)url('admin/index/logout').'"}</script>');
        }
        $request->app = $this->app->admin->getApp() ?: false; //管理应用信息
        //未进入应用管理.禁止进入任何应用管理界面
        if($request->import != 'plugin'){
            if(!$request->app && !isset(array_flip(SYSAPP)[$module])){
                abort(403,'请先进入应用管理中心');
            }
            if($request->app && $request->app->config['is_admin'] == 0){
                abort(403,'应用未开启管理中台');
            }
            //进入应用管理界面的,禁止跨应用管理
            if($request->app && !isset(array_flip(SYSAPP)[$module]) && $module != $request->app->appname){
                abort(403,'禁止直接跨应用管理,请先切换应用.');
            }
        }
        View::assign([
            'admin'      => $request->admin,
            'plugin'     => $request->plugin,
            'app'        => $request->app,
            'web'        => $request->web,
            'configs'    => (array)$request->configs,
            'breadcrumb' => $this->breadcrumb()
        ]);
        return $next($request);
    }

    /**
     * 面包屑
     * @return void
     */
    public function breadcrumb()
    {
        $menu = $this->app->configs->admin($this->app->http->getName());
        $breadcrumb = [];
        foreach ($menu as $value) {
            if (isset($value['menu'])) {
                foreach ($value['menu'] as $v) {
                    if (!empty($v['url']) && $v['url'] == Request::baseUrl()) {
                        $breadcrumb[] = ['name' => $value['name']];
                        $breadcrumb[] = $v;
                    }
                }
            }
        }
        return $breadcrumb;
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
