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
use think\facade\View;
use base\model\SystemTenantGroup;

class Acl
{

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app  = $app;
    }

    public function handle($request, \Closure $next)
    {
        $controller = strtolower(trim($request->controller()));
        $action     = strtolower(trim($request->action()));
        /**
         * 1、确定是子帐号
         * 2、确定有权限组
         * 3、确定帐号是当前应用的子帐号
         */
        $request->acl  = false;
        $request->rank = 0;
        if($request->tenant->parent_id && $request->tenant->group_id && $request->tenant->parent_apps_id == $request->apps->id){
            $info = SystemTenantGroup::where(['id' => $request->tenant->group_id])->apps()->field('title,apps_id,rank,rules')->find();
            if(empty($info->rules)){
                abort(403,'无权访问任何路径,请联系管理增加');
            }
            $info->rules = $info?array_flip($info->rules):[];
            $acl_path = md5($controller.'.'.$action); //当前访问的路由值
            if(!isset($info->rules[$acl_path])){
                abort(403,'你无权访问当前路径');
            }
            $request->acl  = $info;
            $request->rank = $info->rank;
        }
        View::assign(['acl' => $request->acl,'rank' => $request->rank]);
        return $next($request);
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
