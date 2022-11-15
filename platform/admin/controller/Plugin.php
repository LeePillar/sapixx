<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 扩展管理
 */
namespace platform\admin\controller;
use base\model\SystemApp;
use base\model\SystemPlugin;
use base\model\SystemPlugins;
use think\facade\Db;
use util\Sql,util\Ary,util\Dir;
use Exception;

class Plugin extends Common{

    /**
     * 扩展列表
     * @access public
     */
    public function index(int $lock = 0){
        $view['list']   = SystemPlugin::where(['is_lock' => $lock])->order('sort desc,id desc')->paginate($this->pages);
        $view['offapp'] = SystemPlugin::offPlugin();
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'应用扩展','url'=> (string)url('plugin/index')]];
        return view()->assign($view);
    }

    /**
     * 扩展应用
     * @return void
     */
    public function details(){
        $id = $this->request->param('id/d',0);
        $view['info'] = SystemPlugin::where(['id' => $id])->find();
        if(empty($view['info'])){
            $this->error('系统中未找到安装的此扩展','友情提示');
        }
        $view['applist']    = SystemApp::where(['is_lock' => 0])->whereIn('id',$view['info']->app_ids)->order('sort desc,id desc')->select();
        $view['apps']       = SystemPlugins::where(['plugin_id' => $id])->order('id desc')->paginate($this->pages);
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'应用扩展','url'=> (string)url('plugin/index')],['name' =>'租户应用']];
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
                'price'      => $this->request->param('price/f',0),
            ];
            $this->validate($data,'App.plugin');
            if(isset(array_flip(FORBIDEN)[$data['appname']])){
                return enjson(403,$data['appname'].'扩展名禁止用');
            }
            SystemPlugin::edit($data);
            return enjson(200,['url' => (string)url('plugin/index')]);
        }else{
            $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'应用扩展','url' => (string)url('plugin/index')],['name' =>'扩展编辑']];
            $view['info'] = SystemPlugin::where(['id' => $this->request->param('id/d')])->find();
            return view()->assign($view);
        }
    }
    
    /**
     * 锁定
     * @param integer $id 用户ID
     */
    public function islock(int $id){
        $result = SystemPlugin::where(['id' => $id])->find();
        $result->is_lock  = $result->is_lock == 1 ? 0 : 1;
        $result->save();
        return enjson(204);
    }

    /**
     * 卸载扩展
     * @access public
     * @return bool
     */
    public function delete(int $id){
        $tenant = SystemPlugins::where(['plugin_id' => $id])->count();
        if($tenant){
            return enjson(0,'当前扩展已产生数据,禁止删除,请禁用');
        }
        //卸载扩展的SQL文件
        $result = SystemPlugin::where(['id' => $id])->find();
        if(!$result){
            return enjson(0,'未找到扩展');
        }
        //删除数据库卸载脚本
        $file = PATH_PLUGIN.$result->appname.DS.'package'.DS.'database'.DS.'uninstall.sql';
        if (file_exists($file)) {
            $array = Sql::sqlAarray($file,DB_PREFIX);
            foreach ($array as $sql) {
                Db::query($sql);
            }
        }
        //删除静态资源
        Dir::rmDirs(PATH_STATIC.'plugin'.DS.$result->appname.DS);
        //删除安装数据
        SystemPlugin::destroy($id);
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
            SystemPlugin::update(['sort' => $data['sort'],'id' => $data['id']]);
            return enjson(302);
        }
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
                return enjson(0,'安装扩展未找到');
            }
            if(isset(array_flip(FORBIDEN)[$appname])){
                return enjson(403,$appname.'为系统目录禁止安装扩展');
            }
            $app_path = PATH_PLUGIN.$appname.DS; //扩展路径
            if(!is_dir($app_path)){
                return enjson(0,'未找到安装扩展路径');
            }
            $param = $this->app->configs->plugin($appname);
            if(empty($param)){
                return enjson(0,'未找到扩展配置信息');
            }
            if (!Dir::isDirs(PATH_STATIC)) {
                return enjson(0,'静态资源static目录权限不足');
            }
            $static_dir = PATH_STATIC.'plugin'.DS.$appname.DS;
            if (file_exists($static_dir.'install.lock')) {
                return enjson(0,'请删除[public/static/plugin/'.$appname.'/install.lock]锁文件再安装');
            }
            $app = SystemPlugin::column('appname');
            if(in_array($appname,$app)){
                return enjson(0,'扩展已安装,禁止重复安装');
            }
            //插入数据
            $data = [
                'appname'    => $appname,
                'title'      => $param['name'],
                'logo'       => DAMAIN.'static/plugin/'.$appname.'/logo.png',
                'about'      => $param['about']??'',
                'price'      => 0
            ];
            $this->validate($data,'App.plugin');
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
                //创建扩展锁
                @touch($static_dir.'install.lock');
                //执行安装
                $array = Sql::sqlAarray($install_sql,DB_PREFIX);
                foreach ($array as $sql) {
                    Db::query($sql);
                }
                SystemPlugin::edit($data);
                return enjson(200,'扩展安装成功');
            }else{
                return enjson(0,'未找到数据库脚本');
            }
        } catch (Exception $e) {
            return enjson(0,$e->getMessage());
        }
    }

    /**
     * 切换应用管理
     * */
    public function manage(int $id = 0){
        $info = SystemPlugin::where(['id' => $id])->field('appname')->find();
        if(empty($info)){
            abort('未找到安装扩展');
        }
        return redirect(plugurl($info->appname.'/admin/index'));
    }

    /**
     * 解除应用于扩展的绑定
     * */
    public function delBind(){
        $app_id    = $this->request->param('app_id/d');
        $plugin_id = $this->request->param('plugin_id/d');
        if(empty($app_id) || empty($plugin_id)){
            return enjson(403,'没有选择任何商品');
        }
        $info = SystemPlugin::where(['id' => $plugin_id])->field('id,app_ids')->find();
        if(empty($info)){
            return enjson(403,'未找到安装扩展');
        }
        $info->app_ids = implode(',',Ary::reform_keys(Ary::array_values_unset($app_id,Ary::array_int($info->app_ids,true))));
        $info->save();
        return enjson(200);

    }
     /**
     * 选择弹窗(SKU)
     */
    public function selectApp(){
        if(IS_POST){
            $ids = $this->request->param('ids/d');
            $id  = $this->request->param('id/d');
            if(empty($ids) || empty($id)){
                return enjson(403,'没有选择任何商品');
            }
            $info = SystemPlugin::where(['id' => $id])->field('id,app_ids')->find();
            if(empty($info)){
                return enjson(403,'未找到安装扩展');
            }
            $info->app_ids = implode(',',Ary::reform_keys(array_unique(array_merge([$ids],Ary::array_int($info->app_ids,true)))));
            $info->save();
            return enjson(200);
        }else{
            $view['list'] = SystemApp::where(['is_lock' => 0])->order('sort desc,id desc')->paginate($this->pages);
            return view()->assign($view);
        }
    }
}