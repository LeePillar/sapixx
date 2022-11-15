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
use Hashids\Hashids;

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
        //读取站点信息
        $client = $request->client?:self::getApps();
        if(empty($client)){
            abort(404,'访问应用不存在或未开通服务');
        }
        //应用信息
        $request->client  = $client;
        $request->apps    = $client->apps;
        $request->app     = $client->app;
        $request->user    = $this->app->user->getLogin($request->param('token',''));
        $request->config  = (object)$this->app->config->get('config');
        $request->configs = (object)$this->app->config->get('version');
        $request->web     = $request->config->site;
        View::assign(['web' => $request->web,'app' => $request->app,'configs' => (array)$request->configs]);
        return $next($request);
    }

    /**
     * 读取应用信息
     * @return bool|object
     */
    protected function getApps()
    {
        $get_id = $this->app->request->param('get/s');
        if(empty($get_id)){
            return false;
        }
        $hashids   = new Hashids(config('api.jwt_salt'),6,config('api.safeid_meta'));
        $client_id = $hashids->decode($get_id);
        if(empty($client_id[0])){
            return false;
        }
        return SystemAppsClient::where(['id' => intval($client_id[0])])->cache(true)->find();
    }

    public function end(\think\Response $response)
    {
        // 回调行为
    }
}
