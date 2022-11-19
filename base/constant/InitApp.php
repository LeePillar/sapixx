<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 初始化应用,并设置常用常量
 */
declare(strict_types=1);
namespace base\constant;
use think\facade\Env;
use think\facade\Config;
use think\facade\Request;

class InitApp
{

    /**
     * 系统版本
     * @var string
     */
    protected $version = 'v2.3.0';
    
    /**
     * 应用名称
     * @var string
     */
    protected $sysname = 'SAPI++';


    /**
     * SAPI++服务中心与应用互联(改变后将没办法检查版本、升级和跨平台应用互联服务)
     * @var string
     */
    protected $baseuri = 'https://www.apixx.cn';

    /**
     * 系统应用
     * @var array
     */
    protected $sysapp    = ['admin','tenant','apis','install','index','cloud'];

    /**
     * 禁止应用名称
     * @var array
     */
    protected $forbidden = ['admin','tenant','apis','install','web','api','plugin'];

    /**
     * 初始化行为入口
     */
    public function handle()
    {
        // 初始化系统常量
        $this->initPathConst();
        //设置DEBUG环境
        $this->initDebugEnv();
    }

    /**
     * 初始化路径常量
     */
    private function initPathConst()
    {
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        defined('PATH_TOOT') or define('PATH_TOOT', root_path());
        defined('PATH_APP') or define('PATH_APP', app_path());
        defined('PATH_SAPIXX') or define('PATH_SAPIXX', PATH_TOOT . "platform" . DS);
        defined('PATH_PLUGIN') or define('PATH_PLUGIN', PATH_TOOT . "plugin" . DS);
        defined('PATH_RUNTIME') or define('PATH_RUNTIME', PATH_TOOT . "runtime" . DS);
        defined('PATH_STORAGE') or define('PATH_STORAGE', PATH_RUNTIME ."storage");
        defined('PATH_PUBLIC') or define('PATH_PUBLIC', public_path());
        defined('PATH_COMMON') or define('PATH_COMMON', PATH_PUBLIC . 'common' . DS);
        defined('PATH_RES') or define('PATH_RES', PATH_PUBLIC . 'res' . DS);
        defined('PATH_STATIC') or define('PATH_STATIC', PATH_PUBLIC . 'static' . DS);
        defined('DB_PREFIX') or define('DB_PREFIX', env('database.prefix','ai_'));
        defined('IS_DEBUG') or  define('IS_DEBUG', Env::get('APP_DEBUG'));
        defined('DAMAIN') or  define('DAMAIN', Request::domain().'/');
        defined('BASEVER') or define('BASEVER',$this->version);
        defined('SYSNAME') or define('SYSNAME',$this->sysname);
        defined('SYSAPP') or define('SYSAPP',$this->sysapp);
        defined('FORBIDEN') or define('FORBIDEN',$this->forbidden);
        defined('BASEURI') or define('BASEURI',$this->baseuri);
    }

    /**
     * 调试模式缓存
     */
    private function initDebugEnv()
    {
        if (IS_DEBUG) {
            ini_set('display_errors','On');
            // 如果是调试模式将version置为当前的时间戳可避免缓存
            Config::set(['version' => time()], 'site');
            //如果是调试模式将关闭视图缓存
            Config::set(['tpl_cache' => false,'strip_space' => false,'cache_time' => 3600], 'view');
            // 如果是开发模式那么将异常模板修改成官方的
            Config::set(['exception_tmpl' => app()->getThinkPath() . 'tpl/think_exception.tpl', 'http_exception_template' => [], 'show_error_msg' => true], 'app');
        }
    }
}
