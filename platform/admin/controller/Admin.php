<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 管理员
 */
namespace platform\admin\controller;
use base\model\SystemAdmin;

class Admin extends Common{

    /**
     * 列表
     * @access public
     */
    public function index(){
        $view['list']  = SystemAdmin::order('id desc')->select();
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'帐号管理','url' => (string)url('admin/index')]];
        return view()->assign($view);
    }

    /**
     * 编辑用户
     * @access public
     */
    public function edit(){
        if(IS_AJAX){
            $data = [
                'id'               => $this->request->param('id/d',0),
                'username'         => $this->request->param('username/s'),
                'password'         => $this->request->param('password/s'),
                'password_confirm' => $this->request->param('repassword/s'),
                'about'            => $this->request->param('about/s'),
                'ip'               => $this->request->ip(),
            ];
            $this->validate($data,'Admin.edit');
            $result  = SystemAdmin::updateUser($data);
            if($result){
                return enjson(200,['url' => (string)url('admin/index')]);
            }
            return enjson(0);
        }else{
            $view['info']       = SystemAdmin::where(['id' => $this->request->param('id/d',0)])->find();
            $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'帐号管理','url' => (string)url('admin/index')],['name' =>'创建编辑']];
            return view()->assign($view);
        }
    }


    /**
     * [删除]
     * @access public
     * @return bool
     */
    public function delete(int $id){
        if($id == 1){
            return enjson(403);
        }
        SystemAdmin::destroy($id);
        return enjson(204);
    }

    /**
     * 检测手机号是否重复
     */
    public function isPass(){
        $id       = $this->request->param('id/d');
        $username = $this->request->param('username/s');
        if($id){
            $result = SystemAdmin::where('id','<>',$id)->where(['username' => $username])->find();
        }else{
            $result = SystemAdmin::where(['username' => $username])->find();
        }
        if($result){
            return json(['error'=>'用户名已存在']);
        }else{
            return json(['ok'=>'可以使用']);
        }
    }
}