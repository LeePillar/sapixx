<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 应用管理
 */
namespace platform\admin\controller;
use base\model\SystemTenant;
use base\model\SystemApp;
use base\model\SystemApps;
use base\model\SystemAppsClient;
use think\facade\Validate;

class Apps extends Common{

     /**
     * 授权管理
     * @access public
     * @return bool
     */
    public function index(){
        $types      = $this->request->param('types/d',0);
        $keyword    = $this->request->param('keyword/s');
        $condition  = [];
        $condition[] = ['is_lock','=',$types];
        if(!empty($keyword)){
            $data['keyword'] = $keyword;
            $is_mobile = Validate::rule('keyword','mobile')->check($data);
            if($is_mobile){
                $tenant = SystemTenant::where(['phone_id' => $keyword])->field('id')->find();
                $condition[] = ['tenant_id','=',$tenant ? $tenant->id : 0];
            }else{
                $condition[] = ['title','like','%'.$keyword.'%'];
            }
        }
        $list = SystemApps::where($condition)->order('id desc')->paginate($this->pages);
        $list->filter(function($value){
            $value->logo = $value->logo ?: $value->app->logo;
            return $value;
        });
        $view['list'] = $list;
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'租户应用']];
        return view()->assign($view);
    }   

     /**
     * 添加授权
     * @access public
     */
    public function add(){
        if(IS_AJAX){
            $param = [
                'id'        => $this->request->param('id/d',0),
                'tenant_id' => $this->request->param('tenant_id/d'),
                'app_id'    => $this->request->param('app_id/d'),
                'title'     => $this->request->param('title/s'),
                'about'     => $this->request->param('about/s'),
                'end_time'  => $this->request->param('end_time/s'),
            ];
            $this->validate($param,'Apps.add');
            $app  = SystemApp::where(['id' => $param['app_id']])->field('id')->find();
            if(!$app){
                return enjson(0,'未找到应用');
            }
            $data['tenant_id'] = $param['tenant_id'];
            $data['app_id']    = $param['app_id'];
            $data['title']     = $param['title'];
            $data['about']     = $param['about'];
            $data['end_time']  = strtotime($param['end_time']);
            SystemApps::create($data);
            return enjson(200,['url' => (string)url('apps/index')]);
        }else{
            $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'租户应用','url' => (string)url('apps/index')],['name' =>'创建应用']];
            return view()->assign($view);
        }
    }   

    /**
     * 编辑授权
     */
    public function edit(){
        if(IS_AJAX){
            $param = [
                'id'       => $this->request->param('id/d',0),
                'title'    => $this->request->param('title/s'),
                'about'    => $this->request->param('about/s'),
                'end_time' => $this->request->param('end_time/s'),
            ];
            $this->validate($param,'Apps.edit');
            $data['id']        = $param['id'];
            $data['title']     = $param['title'];
            $data['about']     = $param['about'];
            $data['end_time']  = strtotime($param['end_time']);
            SystemApps::update($data);
            return enjson(200,['url' => (string)url('apps/index')]);
        }else{
            $info = SystemApps::where(['id' => $this->request->param('id/d')])->find();
            if(empty($info)){
                $this->error(404,"页面不存在");
            }
            $info->logo = $info->logo ?: DAMAIN."static/{$info->app->appname}/logo.png";
            $view['info'] = $info;
            return view()->assign($view);
        }
    }

    /**
     * 锁定授权
     * @param integer $id 用户ID
     */
    public function islock(int $id){
        $result = SystemApps::lock($id);
        if($result){
            return enjson(204);
        }
        return enjson(0,'应用解锁失败,租户已禁用');
    }

    /**
     * 搜索租户
     * @param integer $id 用户ID
     */
    public function tenant(){
        if(IS_AJAX){
            $phone = $this->request->param('phone/s');
            if(empty($phone)){
                $sql = SystemTenant::where(['parent_id'=>0]);
            }else{
                $is_mobile = Validate::rule('phone','mobile')->check(['phone' => $phone]);
                if($is_mobile){
                    $sql = SystemTenant::where(['phone_id' => $phone,'parent_id' => 0]);
                }else{
                    $sql = SystemTenant::where('phone_id|username', 'like','%'.$phone.'%','OR')->where(['parent_id' => 0]);
                }
            }
            $list = $sql->field('id,username,phone_id')->limit(10)->select();
            $list->filter(function($value){
                $value->text = $value->username.'【'.$value->phone_id.'】';
                return $value;
            });
            return enjson(200,$list->toArray());
        }
    }

    /**
     * 搜索应用
     * @param integer $title 用户ID
     */
    public function app(){
        if(IS_AJAX){
            $condition  = [];
            $title = $this->request->param('title/s');
            if(!empty($title)){
                $condition[] = ['title','like','%'.$title.'%'];
            }
            $list = SystemApp::where($condition)->field('id,title,appname,about')->limit(10)->select();
            $list->filter(function($value){
                $value->text = $value->title;
                $value->logo = $value->logo;
                $value->var  = $value->config['var']??'1.0';
                return $value;
            });
            return enjson(200,$list->toArray());
        }
    }

    /**
     * 进入租户管理中心
     * @param integer $id 用户ID
     */
    public function toTanent(int $id = 0){
        $this->app->tenant->clearLogin();
        $rel = SystemApps::where(['is_lock' =>0,'id' => $id])->find();
        if(empty($rel)){
            $this->jump('对不起,此应用已被禁止访问');
        }
        $this->app->tenant->setApps($rel->id);
        $this->app->tenant->setLogin($rel->tenant_id);
        return redirect((string)url('tenant/common/tenant'),302);
    }
}