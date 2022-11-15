<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 系统服务引入
 */
declare(strict_types=1);
namespace base\constant;
use think\Service;
use think\Request;
use base\service\Configs;
use base\service\CheckAdmin;
use base\service\CheckTenant;
use base\service\CheckUser;
use base\service\CheckJwt;
use util\Dir;

/**
 * 微信应用基础服务
 */
class InitService extends Service
{

    //快速绑定
    public $bind = [
        'admin'   => CheckAdmin::class,
        'tenant'  => CheckTenant::class,
        'user'    => CheckUser::class,
        'jwt'     => CheckJwt::class,
        'configs' => Configs::class
    ];

    /**
     * 注册服务到容器中
     * @return void
     */
    public function register()
    {
        //$this->app->bind('系统服务名称',类名::class);
    }

    /**
     * 启动服务
     * @return void
     */
    public function boot(Request $request)
    {
        if ($request->isCli()) {
            $appPath = Dir::getDir(PATH_APP,FORBIDEN);
            $files = [];
            foreach ($appPath as $value) {
                $file = PATH_APP.$value.DS.'console'.$this->app->getConfigExt();
                if (file_exists($file)) {
                    $files[] = require_once $file;
                }
            }
            $title = [];
            $class = [];
            array_walk_recursive($files, function($value,$key) use (&$class,&$title) {
                array_push($class,$value);
                array_push($title,$key);
            });
            $console = array_combine($title,$class);
            $this->commands($console);
        }
    }
}