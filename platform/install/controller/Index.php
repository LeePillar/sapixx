<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 平台安装器
 */
namespace platform\install\controller;

use base\BaseController;
use think\facade\Session;
use think\facade\Request;
use util\Sql;

class Index extends BaseController
{

    // 初始化
    protected function initialize()
    {
        if (file_exists(PATH_RUNTIME."install.lock")) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location:'.Request::domain());
            exit('请删除install.lock文件后再运行SAPI++安装器!');
        }
    }

    /*
    * 默认首页
    * @return void
    */
    public function index($i = '')
    {
        $view['version']   = version_compare(PHP_VERSION,'8.0.2','>=')?'check-lg' : 'x-lg';
        $view['pdo']       = extension_loaded('pdo')?'check-lg' : 'x-lg';
        $view['pdo_mysql'] = extension_loaded('pdo_mysql')?'check-lg' : 'x-lg';
        $view['gd']        = extension_loaded('gd')?'check-lg' : 'x-lg';
        $view['dom']       = extension_loaded('dom')?'check-lg' : 'x-lg';
        $view['fileinfo']  = extension_loaded('fileinfo') ?'check-lg':'x-lg';
        $view['sodium']    = extension_loaded('sodium') ?'check-lg':'x-lg';
        $view['config']    = is_writable(PATH_TOOT.'config')?'check-lg': 'x-lg';
        $view['runtime']   = is_writable(PATH_RUNTIME)?'check-lg': 'x-lg';
        $view['res']       = is_writable(PATH_RES)?'check-lg' : 'x-lg';
        $view['static']    = is_writable(PATH_STATIC)?'check-lg':'x-lg';
        $view['curl_init'] = function_exists('curl_init') ?'check-lg':'x-lg';
        $view['env']       = (file_exists(PATH_TOOT.'.env') && is_writable(PATH_TOOT.'.env')) ?'check-lg':'x-lg';
        if($i == 'success' && Session::has('step1')){
            Session::delete('step1');
            if(in_array('x-lg',$view)){
                return redirect((string)url('index/index'));
            }
            Session::set('step2','step2');
            return view('/db');
        }else{
            if($i && !Session::has('step1')){
                return redirect((string)url('index/index'));
            }else{
                Session::set('step1','step1');
                return view('/index')->assign($view);
            }
        }
    }
    
     /*
    * 默认首页
    * @return void
    */
    public function install(){
        if(IS_POST && Session::has('step2')){
            $param = $this->param;
            $this->validate($param,'Install.db');
            try{
                //检查SQL文件是否存在
                $install_sql = PATH_SAPIXX.'install'.DS.'data'.DS.'install.sql';
                $sql = file_get_contents($install_sql);
                if (!$sql) {
                    return enjson(0,'数据库文件无法打开');
                }
                //连接数据库
                $link = @new \mysqli("{$param['DB_HOST']}:{$param['DB_PORT']}",$param['DB_USER'],$param['DB_PWD']);
                if (!is_null($link->connect_error)) {
                    return enjson(0,'输入信息有误,链接不成功');
                }
                $link->query("SET NAMES'utf8'");
                $link->server_info > 5.5 or enjson(0,'请将您的mysql升级到5.5以上');
                // 创建数据库并选中
                if(!$link->select_db($param['DB_NAME'])){
                    $create_sql = 'CREATE DATABASE IF NOT EXISTS '.$param['DB_NAME'].' DEFAULT CHARACTER SET utf8mb4;';
                    $link->query($create_sql) or enjson(0,'创建数据库失败');
                }
                $link->select_db($param['DB_NAME']);
                $param['DB_PREFIX'] = 'ai_';
                if(!write_config($param) && !write_database($param)){
                    return enjson(0,'配置文件写入错误');
                }
                //开始安装
                $array = Sql::sqlAarray($install_sql,DB_PREFIX);
                foreach ($array as $sql) {
                    if (!empty($sql)) {
                        $link->query($sql);
                    }
                }
                $link->close();
                @touch(PATH_RUNTIME.'install.lock');
                return enjson(302,['url' => Request::domain()]);
            }catch (\Exception $e) {
                return enjson(0,'安装失败,请删除 runtime/install.lock文件');
            }
        }else{
            return enjson(0,'SAPI++安装器执行路径错误');
        }
    }
}
