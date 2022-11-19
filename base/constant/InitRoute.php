<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * API访问全局入口控制
 */
declare(strict_types=1);
namespace base\constant;
use think\App;
use think\facade\Config;
use filter\Filter;
use base\model\SystemAppsClient;
use base\model\SystemPlugin;

/**
 * API解析到指定的应用
 */
class InitRoute
{

    /** @var App */
    protected $app;

    /**
     * 应用名称
     * @var string
     */
    protected $name;

    /**
     * 应用三方库
     * @var string
     */
    protected $composer;
    
    /**
     * 应用路径
     * @var string
     */
    protected $path;

    /**
     * 路径入口判断
     * @var string
     */
    protected $import;

    /**
     * API版本
     * @var string
     */
    protected $version;

    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->path    = $this->app->request->pathinfo();
        $pathinfo      = explode('/',$this->path);
        $this->import  = trim(strtolower(current($pathinfo)));
        $this->version = trim(strtolower(next($pathinfo) ?: ''));
        $this->name    = trim(strtolower(next($pathinfo) ?: ''));
        $this->app->request->import = $this->import;
    }

    /**
     * 多应用解析
     * @access public
     */
    public function handle()
    {
        $this->checkSystem();
        switch ($this->import) {
            case 'api':
                $this->setApi();
                break;
            case 'plugin':
                $this->setPlugin();
                break;
            case 'web':
                $this->setTenantWeb();
                break;
            default:
                $this->tenantWebDomain();
                $this->setPlatform();
                break;
        }
        $this->LoadAppComposer();
    }

    /**
     * 租户应用独立域名解析
     */
    private function tenantWebDomain()
    {
        if($this->import == 'install'){
            return;
        }
        $subDomain = Filter::filter_str($this->app->request->host(true));
        $client = SystemAppsClient::where(['domain' => $subDomain])->cache(180)->find();
        if(empty($client)){
            return;
        }
        $this->app->request->client = $client;
        Config::set(['domain_bind' => [$client->domain => $client->app->appname]], 'app');
        Config::set(['controller_layer' => 'web\\'.$client->title],'route');
        Config::set(['view_path' => PATH_APP.$client->app->appname.DS.'web'. DS.'view'.DS.$client->title.DS], 'view'); 
    }

    /**
     * 解析多应用API
     * @return bool
     */
    protected function setApi(): bool
    {
        //是否为应用API配置独立的访问域名
        $api_sub_domain = Config::get('api.api_sub_domain');
        if (!empty($api_sub_domain) && $api_sub_domain != $this->app->request->domain()) {
            exitjson(403,'APIDomain Error');
        }
        $this->checkApi();
        //检查API路径
        if (empty($this->name) || !is_dir($this->app->getBasePath() . $this->name . DS)) {
            exitjson(404,'没有找到应用');
        }
        Config::set(['controller_layer' => 'api\\'.str_replace('.', '_', $this->version)], 'route');
        $this->app->request->setPathinfo(ltrim(substr($this->path, strlen($this->import . '/' . $this->version)), '/'));
        $this->composer = $this->name.DS;
        return true;
    }
 
    /**
     * 解析WEB路径
     */
    protected function setTenantWeb()
    {
        if (empty($this->version) || !preg_match('/^([a-zA-Z0-9]){6}$/',$this->version)) {
            abort(404,'WEB路径访问错误');
        }
        $client = SystemAppsClient::where(['id' => idcode($this->version)])->cache(180)->find();
        if(empty($client)){
            abort(404,'你访问的URL不存在');
        }
        //是否独立租户域名
        if(!empty($client->domain)){
            redirect('//'.$client->domain)->send() or die;
        }
        $path = PATH_APP.$client->app->appname.DS;
        if (!is_dir($path)) {
            abort(404,'未找到访问的应用');
        }
        //动态修改配置
        Config::set(['controller_layer' => 'web\\'.$client->title],'route');
        Config::set(['view_path' => PATH_APP.$client->app->appname.DS.'web' . DS.'view'.DS.$client->title.DS], 'view'); 
        //修改系统默认配置
        $this->app->request->setPathinfo($client->app->appname.'/'.ltrim(substr($this->path,strlen($this->import.'/'.$this->version)),'/'));
        $this->app->request->client = $client;
        $this->composer = $client->app->appname.DS;
        return true;
    }

    /**
     * 解析系统API
     * @return bool
     */
    protected function setPlatform(): bool
    {
        //为每个独立应用解析独立域名
        $bind = $this->app->config->get('app.domain_bind',[]);
        if (!empty($bind)) {
            $subDomain = $this->app->request->subDomain();
            $domain    = $this->app->request->host(true);
            if (isset($bind[$domain])) {
                $appname = $bind[$domain];
            } elseif (isset($bind[$subDomain])) {
                $appname = $bind[$subDomain];
            }else{
                abort(404,'未找到域名解析应用');
            }
            $appname = $this->import=='apis'?'apis':$appname;
        }else{
            $appname = $this->import ?: Config::get('app.default_app');
        }
        //判断是否系统应用(如果是系统应用则走SAPI++系统应用目录)
        if(isset(array_flip(SYSAPP)[$appname])){
            $appPath = PATH_SAPIXX.$appname.DS;
            if (!is_dir($appPath)) {
                return true;
            }
            $this->app->http->path($appPath); 
            $this->app->setAppPath($appPath);
            $this->app->setNamespace('platform\\'.$appname);
            $this->app->request->setRoot('/' . $appname);
            Config::set(['app_namespace' => 'platform\\'.$appname], 'app'); 
            Config::set(['view_path' => $appPath . 'view' . DS], 'view'); 
            if ($appname == 'apis') {
                $this->checkApi();
                Config::set(['controller_layer' => 'controller\\'.str_replace('.', '_', $this->version)],'route');
                $this->app->request->setPathinfo($appname.'/'.ltrim(substr($this->path,strlen($this->import.'/'.$this->version)),'/'));
            }
        }else{
            $this->composer = $appname.DS;
        }
        return true;
    }

   /**
     * 解析插件路径
     * @return bool
     */
    protected function setPlugin(): bool
    {
        $plugName = $this->version;
        if (empty($plugName) || !preg_match('/^[a-z]+$/',$plugName)) {
            abort(404,'扩展请求 /'.$plugName.' 路径不正确');
        }
        $plugPath = PATH_TOOT.'plugin'.DS.$plugName.DS;
        if (!is_dir($plugPath)) {
            abort(404,'扩展 '.$plugName.' 未找到');
        }
        $plugin = SystemPlugin::where(['appname' => $plugName])->cache(true)->find();
        if(empty($plugin)){
            abort(404,'扩展 '.$plugName.' 未安装');
        }
        $this->app->request->plugin = $plugin;
        //重定义扩展目录
        $this->app->http->path($plugPath); 
        $this->app->setAppPath($plugPath);
        $this->app->setNamespace('plugin\\'.$plugName);
        $this->app->request->setPathinfo(ltrim(substr($this->path,strlen('plugin')),'/'));
        //加载扩展的全局函数
        if (is_file($plugPath . 'common.php')) {
            include_once $appPath . 'common.php';
        }
        Config::set(['app_namespace' => 'plugin\\'.$plugName], 'app'); 
        Config::set(['view_path' => $plugPath.'view' . DS], 'view'); 
        return true;
    }

 
   /**
     * 检查API版本规则
     * @return bool
     */   
    protected function checkApi(): bool
    {
        if (empty($this->version) || !preg_match('/((^([a-zA-Z]*)$)|(^[v]\d{1}?(\.(0|[1-9]\d?)){1,2})$)/',$this->version)) {
            exitjson(403, 'APIVersion Rules Error');
        }
        return true;
    }

    /**
     * 1、检查是否已经正常安装系统
     * 2、检查API是否配置独立域名
     */
    private function checkSystem()
    {
        //检查是否已经正常安装系统
        if (!file_exists(PATH_RUNTIME."install.lock")) {
            Config::set(['default_app' => 'install'],'app');
            $this->import = 'install';
        }else{
            //检查API是否配置独立域名(如果访问)
            $api_sub_domain = Config::get('api.api_sub_domain');
            if (!empty($api_sub_domain) && $api_sub_domain == $this->app->request->domain()) {
                if(empty($this->import) || $this->import != 'api' && $this->import != 'apis'){
                    exitjson(404, 'API is not found');
                }
            }
        }
    }

    /**
     * 载入应用独立资源包
     */
    private function LoadAppComposer()
    {
        $autoLoadFile = $this->app->getAppPath().$this->composer.'vendor'.DS.'autoload.php';
        if(file_exists($autoLoadFile)){
            require_once $autoLoadFile;
        }
    }
}