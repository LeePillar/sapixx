<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 应用管理
 */
namespace platform\admin\controller;
use base\model\SystemApp;
use base\model\SystemApps;
use think\facade\Db;
use util\Sql;
use util\Dir;
use Exception;

class App extends Common{

    /**
     * 应用列表
     * @access public
     */
    public function index(int $lock = 0){
        $view['list']   = SystemApp::where(['is_lock' => $lock])->order('sort desc,id desc')->paginate($this->pages);
        $view['offapp'] = SystemApp::offApp(); //未安装的APP
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'应用商店','url'=> (string)url('app/index')]];
        return view()->assign($view);
    }

    /**
     * 租户应用
     * @return void
     */
    public function details(int $types = 0){
        $id = $this->request->param('id/d',0);
        $info = SystemApp::where(['id' => $id])->find();
        if(empty($info)){
            $this->error('系统中未找到安装的此应用','友情提示');
        }
        $apps = SystemApps::where(['is_lock'=>$types,'app_id' => $id])->order('id desc')->select();
        $apps->filter(function($value){
            $value->logo = $value->logo ?: DAMAIN."static/{$value->app->appname}/logo.png";
            return $value;
        });
        $view['info'] = $info;
        $view['apps'] = $apps;
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'应用商店','url'=> (string)url('app/index')],['name' =>'租户应用']];
        return view()->assign($view);
    }
    
    /**
     * 编辑用户
     */
    public function edit(){
        if(IS_AJAX){
            $data = [
                'id'         => $this->request->param('id/d',0),
                'appname'    => $this->request->param('appname/s'),
                'title'      => $this->request->param('title/s'),
                'logo'       => $this->request->param('logo',''),
                'about'      => $this->request->param('about'),
                'expire_day' => $this->request->param('expire_day/d'),
                'price'      => $this->request->param('price/f',0),
                'qrcode'     => $this->request->param('qrcode/s'),
                'theme'      => $this->request->param('theme/a',[]),
            ];
            $this->validate($data,$data['id']?'App.edit':'App.install');
            if(isset(array_flip(FORBIDEN)[$data['appname']])){
                return enjson(403,$data['appname'].'应用名禁止用');
            }
            SystemApp::edit($data);
            return enjson(200,['url' => (string)url('app/index')]);
        }else{
            $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'应用商店','url' => (string)url('app/index')],['name' =>'应用编辑']];
            $view['info'] = SystemApp::where(['id' => $this->request->param('id/d')])->find();
            return view()->assign($view);
        }
    }
    
    /**
     * 锁定
     * @param integer $id 用户ID
     */
    public function islock(int $id){
        $result = SystemApp::where(['id' => $id])->find();
        $result->is_lock  = $result->is_lock == 1 ? 0 : 1;
        $result->save();
        return enjson(204);
    }

    /**
     * 卸载应用
     * @access public
     * @return bool
     */
    public function delete(int $id){
        $tenant = SystemApps::where(['app_id' => $id])->count();
        if($tenant){
            return enjson(0,'当前应用已产生数据,禁止删除,请禁用');
        }
        //卸载应用的SQL文件
        $result = SystemApp::where(['id' => $id])->find();
        if(!$result){
            return enjson(0,'未找到应用');
        }
        $app_path = isset(array_flip(SYSAPP)[$result->appname])?PATH_SAPIXX:PATH_APP; //应用路径
        $file = $app_path.$result->appname.DS.'package'.DS.'database'.DS.'uninstall.sql';
        if (file_exists($file)) {
            $array = Sql::sqlAarray($file,DB_PREFIX);
            foreach ($array as $sql) {
                Db::query($sql);
            }
        }
        //删除安装数据
        SystemApp::destroy($id);
        return enjson(200);
    }

    /**
     * 排序
     */
    public function sort(){
        if(IS_POST){
            $data = [
                'sort' => $this->request->param('sort/d'),
                'id'   => $this->request->param('id/d'),
            ];
            $this->validate($data,'App.sort');
            SystemApp::update(['sort'=>$data['sort'],'id' => $data['id']]);
            return enjson(302);
        }
    }

    /**
     * 切换应用管理
     * */
    public function manage(int $id = 0){
        $info = SystemApp::where(['id' => $id])->find();
        if(!$info){
            return enjson(0,'未找到管理的应用');
        }
        $config = $this->app->configs->version($info->appname);
        if(empty($config)){
            return [];
        }
        if(!$config['is_admin']){
            return enjson(0,'当前应用没有独立管理中心');
        }
        $this->app->admin->clearApp();        //清除应用
        $this->app->admin->setApp(['appid' => $info->id,'appname' => $info->appname]); //切换应用
        return enjson(204);
    }

    /**
     * @param $dir
     * @return \think\response\Json
     * 安装程序
     */
    public function install(){
        try {
            $appname  = $this->request->param('appname');
            if(empty($appname)){
                return enjson(0,'安装应用未找到');
            }
            if(isset(array_flip(FORBIDEN)[$appname])){
                return enjson(403,$appname.'为禁止创建目录名称');
            }
            $app_path = (isset(array_flip(SYSAPP)[$appname])?PATH_SAPIXX:PATH_APP).$appname.DS; //应用路径
            if(!is_dir($app_path)){
                return enjson(0,'未找到安装应用路径');
            }
            $param = $this->app->configs->version($appname);
            if(empty($param)){
                return enjson(0,'未找到应用配置信息');
            }
            if (!Dir::isDirs(PATH_STATIC)) {
                return enjson(0,'静态资源static目录权限不足');
            }
            $static_dir = PATH_STATIC.$appname.DS;
            if (file_exists($static_dir.'install.lock')) {
                return enjson(0,'请删除[public/static/'.$appname.'/install.lock]锁文件再安装');
            }
            $app = SystemApp::column('appname');
            if(in_array($appname,$app)){
                return enjson(0,'应用已安装,禁止重复安装');
            }
            //插入数据
            $data = [
                'appname'    => $appname,
                'title'      => $param['name'],
                'logo'       => DAMAIN.'static/'.$appname.'/logo.png',
                'about'      => $param['about']??'',
                'expire_day' => 7,
                'price'      => 0
            ];
            $this->validate($data,'App.install');
            $install_sql = $app_path.'package'.DS.'database'.DS.'install.sql';
            if (file_exists($install_sql)) {
                //创建静态资源
                if(!Dir::mkdirs($static_dir)){
                    return enjson(0,'静态资源目录创建失败');
                }
                //复制资源到静态目录
                $static = $app_path.'package'.DS.'static';
                if (file_exists($static)) {
                    Dir::copyDirs($static,$static_dir); 
                }
                //创建应用锁
                @touch($static_dir.'install.lock');
                //执行安装
                $array = Sql::sqlAarray($install_sql,DB_PREFIX);
                foreach ($array as $sql) {
                    Db::query($sql);
                }
                SystemApp::edit($data);
                return enjson(200,'应用安装成功');
            }else{
                return enjson(0,'未找到数据库脚本');
            }
        } catch (Exception $e) {
            return enjson(0,$e->getMessage());
        }
    }
}