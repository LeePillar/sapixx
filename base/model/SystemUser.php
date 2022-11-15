<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户表
 */
namespace base\model;
use base\BaseModel;
use invite\Invite;

class SystemUser extends BaseModel{
   
   /**
     * 关联多端帐号
     */
    public function client()
    {
        return $this->hasOne('SystemUserRuid','uid');
    }

    /**
     * 新增更新服务ID邀请码
     * @param object $data
     * @return void
     */
    public static function onAfterInsert($data)
    {
        $data->invite_code = Invite::enCode($data->id);
        return $data->save(); 
    }

    /**
     * 搜索
     * @param object $query
     * @param string $value
     * @return void
     */
    public function searchKeywordAttr($query, $value){
        if(!empty($value)){
            if(validate()->isMobile($value)){
                $query->where('phone','=', $value);
            }else{
                $query->where('nickname','like','%'.$value.'%');
            }
        }
    }

    //用户头像
    public function getFaceAttr($value)
    {
        return $value?:DAMAIN.'common/img/nickname.png';
    }

    //用户昵称
    public function getNicknameAttr($value)
    {
        return $value?:'匿名用户';
    }

    //绑定手机号别名
    public function getAsPhoneAttr($value,$data)
    {
        return $data['phone']?:'未绑定';
    }

    /**
     * 查找邀请用户
     * @param string  $invite_code  邀请码
     */
    public static function isInvite($invite_code){
        if(empty($invite_code)){
            return;
        }
        $uid = Invite::deCode($invite_code);
        if(empty($uid)){
            return;
        }
        $invite = self::apps()->where(['id' => $uid])->field('id')->find();
        return $invite->id ?? false;
    }

    /**
     * 创建微信用户
     * @param string  $invite_code  邀请码
     */
    public static function addWechatUser($data){
        $rel = self::apps()->where(['unionid' => $data['unionid'],'is_delete' => 0])->find();
        if($rel){
            if($rel->is_lock){ //被锁定
                return false;
            }
            if(isset($data['nickname']) && isset($data['face']) ){
                $rel->nickname = $data['nickname'];
                $rel->face     = $data['face'];
                $rel->save();
            }
            return $rel;
        }else{
            if(isset($data['nickname']) && isset($data['face']) ){
                $data['nickname'] = $data['nickname'];
                $data['face']     = $data['face'];
             }
            $data['apps_id']  = $data['apps_id'];
            $data['unionid']  = $data['unionid'];
            $result = self::create($data);
            if($result && !empty($data['invite'])){
                $invite = self::isInvite($data['invite']);
                if($invite){
                    SystemUserRelation::addLayer($result->id,$invite);
                }
            }
            return self::where(['id' => $result->id])->find();
        }
    }
}