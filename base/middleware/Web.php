<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * H5/WEB中间件
 */
namespace base\middleware;
use think\App;
use think\facade\View;
use base\model\SystemAppsClient;

class Web
{
    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function handle($request,\Closure $next)
    {
        $request->apps    = $request->client->apps;
        $request->app     = $request->client->app;
        $request->user    = $this->app->user->getLogin($request->param('token',''));
        $request->config  = (object)$this->app->config->get('config');
        $request->configs = (object)$this->app->config->get('version');
        $request->web     = $request->config->site;
        View::assign(['web' => $request->web,'app' => $request->app,'configs' => (array)$request->configs]);
        return $next($request);
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
