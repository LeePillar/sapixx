<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 后台管理基础控制器
 */
namespace base;
use think\facade\View;

class AdminController extends BaseController{

    protected $middleware = ['admin'];

    /**
     * 文件统一上传服务
     */
    public function upload(){
        if(IS_POST){
            $private = $this->request->param('private/d',0);  //安全目录(本地runtime/storage),对外不访问
            $result  = $this->app->upload->start($private?true:false,'admin');
            return enjson(200,$result);
        }else{
            $this->error('Not Found',404);
        }
    }

   /**
     * 面包屑导航
     * 在控制器方法使用 $this->bread([['name'=>'名称',url=>'连接']]);
     */
    protected function bread(array $bread = []){ 
        if($this->app->admin->getApp()){
            array_unshift($bread,['name'=> $this->app->admin->getApp('title'),'icon' =>'house']);
        }
        View::assign(['breadcrumb' => $bread]);
    }
}