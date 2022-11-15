<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户础控制器
 */
namespace base;

use base\model\SystemUser;
use think\facade\View;

class TenantController extends BaseController
{

    //定义默认租户中间件
    protected $middleware = ['tenant'];

    //强制访问控制
    protected $acl    = [];

    //免于访问控制
    protected $aclOff = [];

    /**
     * 
     * 初始化
     * @return void
     */
    protected function initialize(){
        /**
         * 方法1
         * Acl 可以是config/middleware.php定义的别名,也可以是类 Acl::class
         * 可以向中间件传参数,是否需要可以根据中间件方法handle中第三个参数决定
         * $this->middleware('acl','参数1','参数2','参数3') 
         * 正确使用方法 only('生效控制器方法1','生效控制器方法2')或->except('不生效制器方法1','不生效制器方法2'); 
         * $this->middleware('acl')->only('fn1','fn2')或->except('fn3','fn4'); 
         */
        //方法2
        if(!empty($this->acl) || !empty($this->aclOff)){
            if(empty($this->acl)){
                $this->middleware['acl'] = ['except' => $this->aclOff];
            }else{
                $this->middleware['acl'] = ['only' => $this->acl];
            }
        }
    }

   /**
     * 面包屑导航
     * 在控制器方法使用 $this->bread([['name'=>'名称',url=>'连接']]);
     */
    protected function bread(array $bread = []){ 
        if($this->app->tenant->getApps()){
            array_unshift($bread,['name'=> $this->app->tenant->getApps('title'),'icon' =>'house','url'=>(string)url('tenant/index/welcome')]);
        }
        View::assign(['breadcrumb' => $bread]);
    }

   /**
     * 租户端搜索用户公共方法
     */
    public function selectUser(){ 
        if(IS_AJAX){
            $keyword = $this->request->param('keyword/s');
            $page    = $this->request->param('page/d',1);
            $list = SystemUser::apps()->withSearch(['keyword'],['keyword' => $keyword])->where(['is_delete' => 0,'is_lock'=> 0])->field(['id','apps_id','invite_code','face','nickname','phone'])->append(['as_phone'])->page($page,10)->select();
            return enjson(200,$list->toArray());
        }
    }

    /**
     * 文件统一上传服务
     */
    public function upload(){
        if(IS_POST){
            $private = $this->request->param('private/d',0); //安全目录(本地runtime/storage),对外不访问
            $local   = $this->request->param('local/d',0); //true强制本地
            $upload  = $this->app->upload;
            $result  = $local?$upload->local():$upload->start($private?true:false);
            return enjson(200,$result);
        }else{
            $this->error('Not Found',404);
        }
    }
}