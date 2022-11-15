<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户ID
 */
namespace base\model;
use base\BaseModel;

class SystemUserRuid extends BaseModel{

     /**
     * 用户信息
    * @return void
     */
    public function user(){
        return $this->hasOne('SystemUser','uid','id');
    }

     /**
     * 客户端ID
    * @return void
     */
    public function client(){
        return $this->hasOne('SystemAppsClient','id','client_id');
    }

    /**
     * 创建微信用户
     * @param string  $invite_code  邀请码
     */
    public static function addWechatUserRuid($data){
        $rel = self::apps()->where(['uid' => $data['uid'],'client_id' => $data['client_id'],'login_id' => $data['login_id']])->find();
        if($rel){
            $rel->login_ip    = request()->ip();
            $rel->update_time = time();
            $rel->save(); 
            return $rel;
        }
        $data['uid']         = $data['uid'];
        $data['client_id']   = $data['client_id'];
        $data['apps_id']     = $data['apps_id'];
        $data['login_id']    = $data['login_id'];
        $data['secret']      = $data['secret'];
        $data['login_ip']    = request()->ip();
        $data['update_time'] = time();
        return self::create($data);
    }
}