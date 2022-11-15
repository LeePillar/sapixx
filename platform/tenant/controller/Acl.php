<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 访问控制
 */
namespace platform\tenant\controller;
use base\model\SystemTenantGroup;
use base\model\SystemTenant;
use think\exception\ValidateException;
use util\Ary;

class Acl extends Common{

    /**
     * 帐号管理
     */
    public function index()
    {
        $list = SystemTenant::where(['parent_id' => $this->request->tenant->id,'parent_apps_id' => $this->request->apps->id])->order('id desc')->paginate($this->pages);
        $view['list'] = $list;
        return view()->assign($view);
    }

    /**
     * 编辑用户
     */
    public function edit(int $id = 0){
        if(IS_POST){
            $data = [
                'id'             => $id,
                'group_id'       => $this->request->post('group_id/d',0),
                'username'       => $this->request->post('username/s'),
                'phone_id'       => $this->request->post('phone/d'),
                'edit_password'  => $this->request->post('password/s'),
            ];
            $this->validate($data,'Account.subset');
            //判断手机号是否重复
            $condition[] = ['phone_id','=',$data['phone_id']];
            if($data['id']){
                $condition[] = ['id','<>',$data['id']];
            }
            if(SystemTenant::where($condition)->count()){
                return enjson(0,'手机账号已存在');
            }
            $data['parent_id']      = $this->request->tenant->id;
            $data['parent_apps_id'] = $this->request->apps->id;
            $data['password']       = $data['edit_password'];
            SystemTenant::edit($data);
            return enjson(200,['url' =>(string)url('acl/index')]);
        }else{
            $view['info']  = SystemTenant::where(['parent_id' => $this->request->tenant->id,'id'=> $id])->find();
            $view['group'] = SystemTenantGroup::apps()->order('id desc')->select();
            $this->bread([['name'=>'关于应用'],['name'=>'帐号管理','url' => (string)url('acl/index')]]);
            return view()->assign($view);
        }
    }

    /**
     * 删除子帐号
     * @access public
     * @return bool
     */
    public function delete(int $id){
        SystemTenant::where(['parent_id' => $this->request->tenant->id,'id' => $id])->delete();
        return enjson(204);
    }

    /**
     * 检测手机号是否重复
     */
    public function isphone(){
        $tenant_id = $this->request->param('id/d');
        $phone     = $this->request->param('phone');
        try {
            validate(['phone_id' => 'require|mobile'])->check(['phone_id'  => $phone]);
        } catch (ValidateException $e) {
            return json(['error'=>'手机号不正确']);
        }
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
        $result = SystemTenant::where(['parent_id' => $this->request->tenant->id,'id' => $id])->find();
        $result->is_lock = $result->is_lock ? 0 : 1;
        $result->save();
        return enjson(200);
    }

    /**
     * 权限组
     */
    public function group()
    {
        $this->bread([['name'=>'关于应用'],['name'=>'角色管理','url' => (string)url('acl/group')]]);
        $view['list'] = SystemTenantGroup::apps()->order('id desc')->paginate($this->pages);
        return view()->assign($view);
    }

    /**
     * 编辑权限
     */
    public function groupEdit(int $id = 0){
        if(IS_POST){
            $data = [
                'id'      => $id,
                'title'   => $this->request->param('title/s'),
                'remark'  => $this->request->param('remark/s'),
                'rank'    => $this->request->param('rank/d',0),
                'menu'    => $this->request->param('menu/s'),
                'rules'   => $this->request->param('rules/s'),
                'apps_id' => $this->request->apps->id,
            ];
            $rank = app('configs')->tenant($this->request->app->appname,'rank');
            if(empty($rank)){
                $data['rank_text'] = '子管理员';
            }else{
                $rank = array_combine(array_column($rank,'rank'),array_column($rank,'title'));
                $data['rank_text'] = $rank[$data['rank']]??'子管理员';
            }
            SystemTenantGroup::edit($data);
            return enjson(200,['url' =>(string)url('acl/group')]);
        }else{
            $this->bread([['name'=>'关于应用'],['name'=>'角色管理','url' => (string)url('acl/group')]]);
            $view['info']   = SystemTenantGroup::where(['id' => $id])->apps()->find();
            $view['rank']   = app('configs')->tenant($this->request->app->appname,'rank');
            $view['rules']  = empty(app('configs')->tenant($this->request->app->appname,'rules'))?false:true;
            return view()->assign($view);
        }
    }

   /**
     * 删除角色
     * @access public
     * @return bool
     */
    public function groupDel(int $id){
        $tenant = SystemTenant::where(['parent_id' => $this->request->tenant->id,'group_id' => $id])->count();
        if($tenant){
            return enjson(403,'角色使用中,禁止删除.');
        }
        SystemTenantGroup::where(['id' => $id])->apps()->delete();
        return enjson(204);
    }

    /**
     * 读取权限表
     */
    public function rules($id = 0){
        $rules = [];
        if($id){
            $info = SystemTenantGroup::where(['id' => $id])->apps()->find();
            $rules = $info?array_flip($info->rules):[];
        }
        $acl  = app('configs')->tenant($this->request->app->appname,'rules');
        if(empty($acl)){
            return enjson(204);
        }
        //设置规则的MD5值
        $acl = Ary::array_values_set($acl,function($value,$key){
            if($key == 'rule'){
                $value = array_map(fn($var) => md5($var),explode('|',$value));
                return implode('|',$value);
            }
            return $value;
        });
        $acl = Ary::array_values_append($acl,'checked',function($value,$key) use ($rules){
            if($key == 'rule'){
                $rule = explode('|',$value);
                if(count($rule) == 1 ){
                    if(isset($rules[$value])){
                        return true;
                    }
                }else{
                    $i = 0;
                    foreach ($rule as $k => $v) {
                        if(isset($rules[$v])){
                            $i++; 
                        }
                    }
                    if($i == count($rule)){
                        return true;
                    }
                }
            }
            return false;
        });
        return enjson(204,$acl);
    }

    /**
     * 读取菜单中
     */
    public function menus($id = 0){
        $menu = [];
        if($id){
            $info = SystemTenantGroup::where(['id' => $id])->apps()->find();
            $menu = $info?array_flip($info->menu):[];
        }
        $data = [];
        $siderbar = $this->app->configs->siderbar($this->request->app->appname);
        if(empty($siderbar)){
            $tenant = app('configs')->tenant($this->request->app->appname,'tenant');
            if(!empty($tenant)){
                foreach ($tenant as $key => $value) {
                    $data[$key]['name'] = $value['name'];
                    $data[$key]['nocheck'] = true;
                    if(!empty($value['menu'])){
                        foreach ($value['menu'] as $k => $val) {
                            $data[$key]['children'][$k]['name'] = $val['name'];
                            $data[$key]['children'][$k]['rule']  =  md5($val['url']);
                            if(empty($val['url'])){
                                $data[$key]['children'][$k]['nocheck']  = true;
                            }else{
                                $data[$key]['children'][$k]['rule']    = md5($val['url']);
                                $data[$key]['children'][$k]['checked'] = isset($menu[md5($val['url'])]);
                            }
                        }
                    }
                }
            }
        } foreach ($siderbar as $key => $value) {
            $data[$key]['name']     = $value['name'];
            $data[$key]['anchor']   = $value['anchor'];
            $data[$key]['nocheck']  = true;
            $children = app('configs')->tenant($this->request->app->appname,$value['anchor']=='tenant'?'tenant':'tenant_'.$value['anchor']);
            if(!empty($children)){
                foreach ($children as $k => $v) {
                    $data[$key]['children'][$k]['name']    = $v['name'];
                    $data[$key]['children'][$k]['nocheck'] = true;
                    if(!empty($v['menu'])){
                        foreach ($v['menu'] as $i => $val) {
                            $data[$key]['children'][$k]['children'][$i]['name'] = $val['name'];
                            if(empty($val['url'])){
                                $data[$key]['children'][$k]['children'][$i]['nocheck']  = true;
                            }else{
                                $data[$key]['children'][$k]['children'][$i]['rule']     = md5($val['url']);
                                $data[$key]['children'][$k]['children'][$i]['checked']  = isset($menu[md5($val['url'])]);
                            }
                         }
                    }
                }
            }
        }
        return enjson(204,$data);
    }
}