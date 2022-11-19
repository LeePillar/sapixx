<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 跨应用读取配置
 */
declare(strict_types=1);

namespace base\service;
use think\Request;
use base\model\SystemAppsConfig;
use base\model\SystemAppsClient;

class Configs
{

    /** 
     * @var Request 
     */
    protected $request;
    protected $appname;

    public function __construct(Request $request)
    {
        $this->request = $request;
        if(isset($this->request->apps->app)){
            $this->appname = $this->request->apps->app->appname;
        }else{
            $this->appname = $this->request->app ? $this->request->app->appname : app('http')->getName();
        }
    }

    /**
     * 应用配置
     * @param string $app
     * @return void
     */
    protected static function config(string $app, string $files): array
    {
        $path = isset(array_flip(SYSAPP)[$app])?PATH_SAPIXX:PATH_APP;
        $module_config = $path . $app . DS . 'config' . DS . $files;
        return is_file($module_config) ? include $module_config : [];
    }

    /**
     * 后台管理菜单
     * @return json
     */
    public function admin(string $app = '')
    {
        return self::config($app?:$this->appname,'admin.php');
    }

    /**
     * 租户管理菜单
     * @return json
     */
    public function tenant(string $app = '',string $menu = 'tenant')
    {
        return self::config($app?:$this->appname,$menu.'.php');
    }

    /**
     * 租户侧边栏
     * @return json
     */
    public function siderbar(string $app = '')
    {
        return self::config($app?:$this->appname,'tenant_sider.php');
    }
    
    
    /**
     * 应用配置信息
     * @return json
     */
    public function version(string $app = '')
    {
        return self::config($app?:$this->appname,'version.php');
    }

    /**
     * 读取扩展配置
     * @return json
     */
    public function plugin(string $app = '',string $menu = 'version')
    {
        $module_config = PATH_PLUGIN . $app . DS . 'config' . DS .$menu.'.php';
        return is_file($module_config) ? include $module_config : [];
    }

   /**
     * 读取租户的应用配置
     * @param string $app
     * @return void
     */
    public function getCfg(string $title)
    {
        $config = ['storage','wechatsp','wechatpay','alipay'];
        if(!in_array($title,$config)){
            abort(403,'应用配置仅支持storage|wechatsp|wechatpay|alipay');
        }
        $wechatsp = SystemAppsConfig::where(['title' => $title])->field('config')->apps()->find();
        if(empty($wechatsp->config)){
            abort(403,'未找到配置信息');
        }
        return (object)$wechatsp->config;
    }

   /**
     * 读取租户的应用配置
     * @param string $app
     * @return void
     */
    public function client(string $title)
    {
        $client = ['web','app','wechatmp','wechatapp','alipayapp','douyinapp'];
        if(!in_array($title,$client)){
            abort(403,'接入终端仅支持web|app|wechatmp|wechatapp|alipayapp|douyinapp');
        }
        $wechatsp = SystemAppsClient::where(['title' => $client])->apps()->find();
        if(empty($wechatsp)){
            abort(403,'微信支付服务商配置错误');
        }
        return $wechatsp;
    }
}