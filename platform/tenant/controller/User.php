<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 会员管理
 */
namespace platform\tenant\controller;
use base\model\SystemUser;
use base\model\SystemUserRelation;
use base\model\SystemUserRuid;

class User extends Common
{

    /**
     * 高级权限控制
     * @return void
     */
    protected function initialize(){
        $this->middleware('platform\tenant\middleware\AppsManage')->except('selectWin');
    }

   /**
     * 会员列表
     */
    public function index()
    {
        if($this->request->tenant->lock_config || $this->request->tenant->parent_id){
            $this->jump('没有权限访问应用配置功能');
        }
        $keyword = $this->request->param('keyword/s');
        $types   = $this->request->param('types/d',0);
        switch ($types) {
            case 1:
                $condition['is_lock']   = 1;
                $condition['is_delete'] = 0;
                break;
            case 2:
                $condition['is_delete'] = 1;
                break;
            default:
                $condition['is_lock']   = 0;
                $condition['is_delete'] = 0;
                break;
        }
        $view['list']    = SystemUser::withSearch(['Keyword'], ['keyword' => $keyword])->where($condition)->apps()->order('id desc')->paginate($this->pages);
        $view['types']   = $types;
        $view['keyword'] = $keyword;
        return view()->assign($view);
    }

    /**
     * 弹窗选择用户
     * @return void
     */
    public function selectWin(){
        if(IS_POST){
            $ids = $this->request->param('ids/s');
            if(empty($ids)){
                return enjson(403,'没有选择任何用户');
            }else{
                return enjson(204,['uid' => $ids]);
            }
        }else{
            $keyword = $this->request->param('keyword/s');
            $page    = $this->request->param('page/d',1);
            $view['list']    = SystemUser::apps()->withSearch(['keyword'],['keyword' => $keyword])->where(['is_delete' => 0,'is_lock'=> 0])->append(['as_phone'])->page($page,6)->select();
            $view['keyword'] = $keyword;
            return view()->assign($view);
        }
    }

    /**
     * 编辑用户
     * @access public
     */
    public function edit(){
        if(IS_AJAX){
            $param = [
                'id'           => $this->request->param('id/d'),
                'phone'        => $this->request->param('phone/s'),
                'password'     => $this->request->param('password/s'),
                'safepassword' => $this->request->param('safepassword/s'),
            ];
            $updata['phone'] = $param['phone'];
            //安全支付密码
            if ($param['safepassword']) {
                $this->validate($param, 'User.safepassword');
                $updata['safe_password'] = password_hash(md5($param['safepassword']),PASSWORD_DEFAULT);
            }
            //登录密码
            if($param['password']){
                $this->validate($param, 'User.password');
                $updata['password'] = password_hash(md5($param['password']),PASSWORD_DEFAULT);
            }
            $this->validate($param,'User.edit');
            SystemUser::where(['id' => $param['id']])->apps()->update($updata);
            return enjson(200);
        }else{
            $id = $this->request->param('id/d');
            $view['info'] = SystemUser::where(['id' => $id])->apps()->append(['as_phone'])->find();
            return view()->assign($view);
        }
    }

    /**
     * 锁定
     * @param integer $id 用户ID
     */
    public function islock(int $id){
        $result = SystemUser::where(['id' => $id])->apps()->find();
        if(!$result){
            return enjson(403);
        }
        if($result->is_delete == 1){
            return enjson(403);
        }
        $result->is_lock = $result->is_lock ? 0 : 1;
        $result->save();
        return enjson(200);
    }

    /**
     * 作废
     * @param integer $id 用户ID
     */
    public function delete(int $id){
        $result = SystemUser::where(['id' => $id])->apps()->find();
        if(!$result){
            return enjson(403);
        }
        $result->is_delete = 1;
        $result->is_lock   = 1;
        $result->save();
        return enjson(200);
    }

    /**
     * 溯源
     */
    public function source(int $id){
        $view['info'] = SystemUser::where(['id' => $id])->apps()->find();
        if(!$view['info']){
            $this->error('未找到用户');
        }
        $view['level'] = SystemUserRelation::source($id);
        return view()->assign($view);
    }

    /**
     * 伞下
     * @param integer $uid
     * @return void
     */
    public function pyramid(int $id){
        if (IS_POST) {
            $info = SystemUserRelation::pyramid($id);
            return json($info);
        }else{
            $view['info'] = SystemUser::where(['id' => $id])->apps()->find();
            if(!$view['info']){
                $this->error('未找到用户');
            }
            $view['people_num'] = SystemUserRelation::where(['parent_id' => $id])->count();
            return view()->assign($view);
        }
    }

    /**
     * 来源
     * @param integer $uid
     * @return void
     */
    public function client(int $id){
        $view['info'] = SystemUser::where(['id' => $id])->apps()->find();
        if(!$view['info']){
            $this->error('未找到用户');
        }
        $view['lists'] = SystemUserRuid::uid($id)->apps()->select();
        return view()->assign($view);
    }
    

     /**
     * 重新梳理用户关系
     */
    public function reset(){
        if (IS_POST) {
            $uid    = $this->request->param('uid/d');
            $invite = $this->request->param('invite/d');
            if (empty($invite) || empty($uid)) {
                return enjson(403, '移动用户没有选择');
            }
            if($uid == $invite){
                return enjson(403, '当前关系禁止改变');
            }
            $rel  = SystemUser::where(['id' => $uid])->apps()->find();
            if(!$rel){
                return enjson(403, '未找到要操作的用户');
            }
            $result  = SystemUserRelation::where(['uid' => $uid,'layer'=> 1])->find();
            if($result && $invite == $result->parent_id){
                return enjson(403, '关系相同,不用改变');
            }
            //重置全部伞下关系
            SystemUserRelation::where(['uid' => $uid])->delete();
            if(SystemUserRelation::addLayer($uid,$invite)){
                $sub_layer  = SystemUserRelation::where(['parent_id' => $uid])->field('uid')->order('layer asc')->select();
                if(!$sub_layer->isEmpty()){
                    //读取原一级关系保存到变量
                    $sub_uid = array_column($sub_layer->toArray(),'uid'); 
                    $layer   = SystemUserRelation::where(['uid' => $sub_uid,'layer' => 1])->order('layer asc')->select();
                    //删除所有子节点的关系
                    SystemUserRelation::where(['uid' => $sub_uid])->delete();  
                    foreach ($layer as $value) {
                        SystemUserRelation::addLayer($value->uid,$value->parent_id);
                    }
                }
                return enjson(200);
            }
            return enjson(403,'关系重排失败');
        }else{
            $id = $this->request->param('id/d');
            $view['info'] = SystemUser::where(['id' => $id])->apps()->find();
            if(!$view['info']){
                $this->error('未找到用户');
            }
            return view()->assign($view);
        }
    }
}
