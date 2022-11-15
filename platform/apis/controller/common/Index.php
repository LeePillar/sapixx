<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 全端公共接口
 */
namespace platform\apis\controller\common;
use base\ApiController;
use base\model\SystemUser;
use invite\Invite;

class Index extends ApiController
{
    //需要登录验证
    protected $aclOff = ['getCodeUser'];

    /**
     * 通过邀请码获取基本信息
     */
    public function getCodeUser(){
        $uid = Invite::deCode($this->request->param('ucode/s'));
        if(empty($uid)){
            return enjson(204);
        }
        $rel = SystemUser::apps()->where(['id' => $uid])->field('face,nickname,phone,invite_code')->find();
        return enjson(empty($rel)?204:200,$rel);
    }

    /**
     * 获取基本信息
     */
    public function getUserProfile(){
        $rel = SystemUser::apps()->where(['id' => $this->request->user->id])->field('face,nickname,phone,invite_code')->find();
        return enjson(200,$rel);
    }

    /**
     * 验证是否设置安全密码
     * @param  int $uid 用户ID
     * @param  string $safepassword 验证的安全密码
     */
    public function checkSafePassword(){
        if (IS_POST) {
            $param['safepassword'] =  $this->request->param('safepassword/s');
            $this->validate($param,'User.chickPassword');
            if(password_verify(md5($param['safepassword']),$this->request->user->safe_password)) {
                return enjson(200,'验证通过');
            }else{
                return enjson(403,'安全密码不正确');
            }
        }else{
            if(empty($this->request->user->phone)){
                return enjson(302,'请先认证手机号');
            }
            if(empty($this->request->user->safe_password)){
                return enjson(204,'未设置安全密码');
            }else{
                return enjson(200,'已设置安全密码');
            }
        }
    }

    /**
     * 设置安全密码
     * @return void
     */
    public function setSafePassword(){
        if(request()->isPost()){
            $param = [
                'safepassword'    => $this->request->param('safepassword/s'),
                'resafepassword'  => $this->request->param('resafepassword/s'),
                'code'            => $this->request->param('code/s'),
            ];
            $this->validate($param,'User.setSafePassword');
            //判断验证码
            if(!app('sms')->isCode($this->request->user->phone,$param['code'])){
                return enjson(403,'验证码错误');
            }
            $data['safe_password'] = password_hash(md5($param['safepassword']),PASSWORD_DEFAULT);
            $data['id']            = $this->request->user->id;
            $result = SystemUser::update($data);
            if($result){
                return enjson(200);
            }
            return enjson(403,'修改失败');
        }
    }
}