<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户管理
 */
namespace platform\admin\controller;
use base\model\SystemTenant;
use base\model\SystemApps;
use base\model\SystemTenantBill;
use base\model\SystemAgent;

class Tenant extends Common{

    /**
     * 会员列表
     */
    public function index($types = 0,$keyword = null){ 
        $condition[] = ['parent_id','=',0];
        switch ($types) {
            case 1:
                $condition[] = ['is_lock','=',1];
                break;
            case 2:
                $condition[] = ['agent_id','>',0];
                break;
            default:
                $condition[] = ['is_lock','=',0];
                break;
        }
        if(!empty($keyword)){
            $condition[] = ['phone_id','=',$keyword];
        }
        $view['list'] = SystemTenant::withCount('apps')->where($condition)->order('id desc')->paginate($this->pages);
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'租户管理','url' => (string)url('tenant/index')]];
        return view()->assign($view);   
    }

    /**
     * 编辑用户
     */
    public function edit(){
        if(IS_AJAX){
            $data = [
                'id'            => $this->request->param('id/d',0),
                'agent_id'      => $this->request->param('agent_id/d',0),
                'username'      => $this->request->param('username/s'),
                'phone_id'      => $this->request->param('phone/d'),
                'password'      => $this->request->param('password/s'),
                'safe_password' => $this->request->param('safe_password/s')
            ];
            $this->validate($data,'Tenant.edit');
            //判断手机号是否重复
            if($data['id']){
                $condition[] = ['id','<>',$data['id']];
            }
            $condition[] = ['phone_id','=',$data['phone_id']];
            $info = SystemTenant::where($condition)->find();
            if(!empty($info)){
                return enjson(0,'手机账号已存在');
            }
            SystemTenant::edit($data);
            return enjson(200,['url' =>(string)url('tenant/index')]);
        }else{
            $view['info']  = SystemTenant::where(['id'=>$this->request->param('id/d',0)])->find();
            $view['agent'] = SystemAgent::order('id desc')->field('id,title')->select();
            $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'租户管理','url' => (string)url('tenant/index')],['name' =>'添加/编辑']];
            return view()->assign($view);
        }
    }

    /**
     * [删除]
     * @access public
     * @return bool
     */
    public function delete(int $id){
        $apps = SystemApps::where(['tenant_id' => $id])->find();
        $bill = SystemTenantBill::where(['tenant_id' => $id])->find();
        if($apps or $bill){
            return enjson(403,'租户已产生数据,禁止删除');
        }
        SystemTenant::destroy($id);
        return enjson(204);
    }

    /**
     * 用户账单
     * @return void
     */
    public function details(int $id = 0,int $types = 0,int $money = 0){
        $view['tenant'] = SystemTenant::where(['id' => $id])->find();
        $apps = SystemApps::where(['tenant_id'=>$id,'is_lock'=>$types])->order('id desc')->select();
        $apps->filter(function($value){
            $value->logo = $value->logo ?: DAMAIN."static/{$value->app->appname}/logo.png";
            return $value;
        });
        $view['apps'] = $apps;
        $condition[] = ['tenant_id','=',$id];
        if($money){
            $condition[] = ['money',$money == 1?'>':'<',0];
        }
        $view['bill']       = SystemTenantBill::where($condition)->order('id desc')->paginate($this->pages);
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'租户管理','url' => (string)url('tenant/index')],['name' =>'租户详情']];
        return view()->assign($view);
    }

    /**
     * 帐号充值
     * @return void
     */
    public function recharge(){
        if(IS_AJAX){
            $data = [
                'id'         => $this->request->param('id/d',0),
                'money'      => $this->request->param('money/d',0),
                'lock_money' => $this->request->param('lock_money/d',0),
                'free_app'   => $this->request->param('free_app/d',0)
            ];
            $this->validate($data,'Tenant.recharge');
            $info = SystemTenant::where(['id' => $data['id']])->find();
            if(empty($info)){
                return enjson(0);
            }
            $info->money      = ['inc',$data['money']];
            $info->lock_money = ['inc',$data['lock_money']];
            $info->save();
            if($data['money'] > 0){
                SystemTenantBill::create(['state' => 1,'money' => $data['money'],'tenant_id'=>$info->id,'message' =>'平台充值到账户余额']);
            }
            if($data['lock_money'] > 0){
                SystemTenantBill::create(['state' => 1,'money' => $data['lock_money'],'tenant_id'=>$info->id,'message' =>'平台赠送到赠送账户']);
            }
            return enjson(200);
        }else{
            $view['info']  = SystemTenant::where(['id'=>$this->request->param('id/d',0)])->find();
            return view()->assign($view);
        }
    }

    /**
     * 检测手机号是否重复
     */
    public function isphone(){
        $tenant_id = $this->request->param('id/d');
        $phone     = $this->request->param('phone');
        if($tenant_id){
            $result = SystemTenant::where('id','<>',$tenant_id)->where(['phone_id' => $phone])->find();
        }else{
            $result = SystemTenant::where(['phone_id' => $phone])->find();
        }
        if($result){
            return json(['error'=>'手机号重复']);
        }else{
            return json(['ok'=>'手机号可用']);
        }
    }
    
    /**
     * 锁定
     * @param integer $id 用户ID
     */
    public function islock(int $id){
        SystemTenant::lock($id);
        return enjson(204);
    }

    /**
     * 锁定配置
     * @param integer $id 用户ID
     */
    public function lockConfig(int $id){
        SystemTenant::lockConfig($id);
        return enjson(200);
    }
}
