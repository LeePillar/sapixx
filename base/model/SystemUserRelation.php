<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户关系
 */
namespace base\model;
use base\BaseModel;

class SystemUserRelation extends BaseModel{

     /**
     * 用户信息关联
    * @return void
     */
    public function user(){
        return $this->hasOne('SystemUser','id','uid');
    }

     /**
     * 用户信息关联
    * @return void
     */
    public function parentUser(){
        return $this->hasOne('SystemUser','id','parent_id');
    }

    /**
     * 创建用户关系
     * @param integer $uid   当前用户ID
     * @param integer $parent_id 上级用户ID
     */
    public static function addLayer(int $uid,int $parent_id)
    {
        $result  = SystemUserRelation::where(['uid' => $uid])->find();
        if($result){
            return false;
        }
        $layer = SystemUserRelation::where(['uid' => $parent_id])->field(['uid','parent_id','layer'])->order('layer desc')->select();
        $layer->filter(function($value) use ($uid){
            $value->layer     = $value->layer+1;
            $value->parent_id = $value->parent_id;
            $value->uid       = $uid;
            return $value;
        });
        $data = array_merge($layer->toArray(),[['uid' => $uid,'parent_id' => $parent_id,'layer' => 1]]);
        SystemUserRelation::limit(100)->insertAll($data);
        return true;
    }

    /**
     * 邀请溯源
     * @param int $uid 用户ID
     * @return array
     */
    public static function source(int $uid){
        $layer = self::uid($uid)->where('parent_id','>',0)->field('parent_id,layer,uid')->order('layer desc')->select()->toArray();
        if(empty($layer)){
            return [];
        }
        $parent_id = array_column($layer,'parent_id');
        $data = [];
        if(!empty($layer)){
            $info = SystemUser::field('id,nickname,invite_code,face,phone,create_time')->whereIn('id',$parent_id)->select()->toArray();
            $key  = array_column($info,'id'); 
            $arr  = array_combine($key,$info) ;
            foreach ($parent_id as $value) {
                 $data[] = $arr[$value];
            }
        }
        $self = SystemUser::field('id,nickname,invite_code,face,phone,create_time')->where(['id' => $uid])->find()->toArray();
        array_push($data,$self);
        return $data;
    }

    /**
     * 查询伞下用户
     * @param array $agent 代理级别
     * @param int $children 用户ID
     * @return array
     */
    public static function pyramid($uid){
        $layer = self::with(['user'])->where(['parent_id' => $uid,'layer' => 1])->select();
        $data = [];
        $i = 0;
        foreach ($layer as $value) {
            $data[$i]['name']       = $value->user->nickname.' ( '.($value->user->phone??'未绑定').' )';
            $data[$i]['title']      = $value->user->invite_code;
            $data[$i]['id']         = $value['uid'];
            $data[$i]['isParent']   = self::where(['parent_id' => $value->uid,'layer' => 1])->count() ? true : false;
            ++$i;
        }
        return $data;
    }
}