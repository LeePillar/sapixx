<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户中间件
 */
namespace base\middleware;

use think\App;
use think\facade\Request;
use think\facade\View;
use base\model\SystemApps;
use base\model\SystemPlugins;

class Tenant
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
        //当前请求方法
        $module     = strtolower(trim($this->app->http->getName()));
        $controller = strtolower(trim($request->controller()));
        $action     = strtolower(trim($request->action()));
        //不需要登录验证的页面
        $noLogin = ['tenant' => ['index' => ['login','register','logout','forgot','wechatqrcode','checkwechatqrcode','getsms','getphonesms']]];
        //如果当前访问是无需登录验证则直接返回
        if (isset($noLogin[$module])) {
            if (isset($noLogin[$module][$controller]) && in_array($action, $noLogin[$module][$controller])) {
                View::assign(['web' => $request->web,'configs' => (array)$request->configs]);
                return $next($request);
            }
        }
        $script = '<script type="text/javascript">if(top.location.href!=self.location.href){top.location="'.(string)url('tenant/index/logout').'"}else{window.location.href="'.(string)url('tenant/index/logout').'"}</script>';
        //登录租户
        $request->tenant = $this->app->tenant->getLogin(); 
        if (empty($request->tenant)) {
            $this->app->tenant->clearLogin();
            return response($script);
        }
        //租户应用
        $request->apps  = $this->app->tenant->getApps();
        //空应用访问
        if(!$request->apps && !isset(array_flip(SYSAPP)[$module])){
            abort(404,'没有找到你访问应用');
        }
        //子帐号锁定后退出
        if($request->tenant->parent_id && !$request->apps){
            $this->app->tenant->clearLogin();
            return response($script);
        }
        $request->app = $request->apps?$request->apps->app:false;
        if($request->import == 'plugin' && $request->apps){
            //应用开通的扩展判断
            $request->plugins = SystemPlugins::where(['apps_id' => $request->apps->id,'plugin_id' => $request->plugin->id,'is_lock' => 0])->find();
            if(empty($request->plugins)){
                abort(403,'当前应用的扩展还没有启用');
            }
            //判断是否子管理(子管理,权限组,扩展权限)
            if($request->tenant->parent_apps_id == $request->apps->id && $request->tenant->group_id){
                if(!$request->plugins->group_ids){
                    abort(403,'请联系你的管理员开通扩展访问权限');
                }
                if(!in_array($request->tenant->group_id,explode(',',$request->plugins->group_ids ))){
                    abort(403,'你没有当前扩展的访问权限');
                }
            }
        }else{
            //是否跨应用访问
            if($request->app && !isset(array_flip(SYSAPP)[$module]) && $module != $request->app->appname){
                abort(403,'请先开通应用,已开通的需要先切换管理.');
            }
        }
        //判断应用过期
        if($request->apps && $request->apps->getData('end_time') <= time()){
            $this->app->tenant->clearApps();
            SystemApps::where(['id' =>$request->apps->id])->update(['is_lock' => 1]);
            abort(403,'你的应用已于 '.$request->apps->end_time.' 过期,请先续费.');
        }
        View::assign([
            'tenant'     => $request->tenant,
            'apps'       => $request->apps,
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
        $menu = $this->app->configs->tenant($this->app->http->getName());
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
        //前面追加
        if(!empty($breadcrumb) && $this->app->tenant->getApps()){
            array_unshift($breadcrumb,['name'=>$this->app->tenant->getApps('title'),'icon' =>'house','url'=>(string)url('tenant/apps/index')]);
        }
        return $breadcrumb;
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
