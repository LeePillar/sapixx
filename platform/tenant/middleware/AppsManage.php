<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 验证当前访问是否应用的主管理员
 */
namespace platform\tenant\middleware;

class AppsManage
{

    public function handle($request, \Closure $next)
    {
        //判断当前应用是否主管理员(不是禁止访问)
        if($request->tenant->lock_config || $request->tenant->parent_id){
            abort(403,'没有权限访问');
        }
        //未开通应用访问应用商店
        if(empty($request->apps)){
            return redirect((string)url('tenant/store/index'))->send() or die;
        }
        //控制器SaaS是微信一键授权页面(关闭配置的阻止进入)
        $controller = strtolower(trim($request->controller()));
        if($controller == 'wechat'){
            if(!$request->app->config['is_open_wechat']){
                abort(403,'当前应用,需手动配置接入,不支持一键授权');
            }
            //把客户端的配置传到控制器
            $client = $request->apps->client->toArray();
            $request->client = array_combine(array_column($client,'title'),$client);
        }
        
        return $next($request);
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
