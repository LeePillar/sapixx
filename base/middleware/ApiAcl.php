<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * API用户登录验证中间件
 */
namespace base\middleware;
use think\App;

class ApiAcl
{

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app  = $app;
    }

    public function handle($request, \Closure $next)
    {
        if(!$request->user){
            return enjson(401,'帐号未登录,禁止访问');
        }
        return $next($request);
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
