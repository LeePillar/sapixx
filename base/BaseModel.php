<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 基础模型
 */
namespace base;
use think\Request;

class BaseModel extends \think\Model
{

    /**
     * 小程序查询条件
     * @param integer $apps_id
     * @return void
     */
    public function scopeApps($query,int $apps_id = 0)
    {
        return $this->invoke(fn(Request $request) => $query->where('apps_id',$apps_id ?: ($request->apps->id ?? 0)));
    }

    /**
     * 租户查询条件
     * @param integer $apps_id
     * @return void
     */
    public function scopeTenant($query,int $tenant_id = 0)
    {
        return $this->invoke(fn(Request $request) => $query->where('tenant_id',$tenant_id ?: ($request->tenant->id ?? 0)));
    }

    /**
     * 注册用户查询条件(请确保应用表中有UID字段再使用)
     * @param integer $uid
     * @return void
     */
    public function scopeUid($query, int $uid = 0)
    {
        return $this->invoke(fn(Request $request) => $query->where('uid',$uid ?: ($request->user->id ?? 0)));
    }


    /**
     * 根据经纬度查询一个圆周内的距离
     * 使用本方法表中必须有经longitude,纬latitude两个字段
     * @param float   $lng 经
     * @param float   $lat 纬 
     * @param integer $km  范围距离
     * @return void
     */
    public function scopeLbs($query,$lng = 0,$lat = 0,$km = 2)
    {
        return $query->whereRaw('latitude > '.$lat.'-'.$km.' and latitude < '.$lat.'+'.$km.' and longitude > '.$lng.'-'.$km.' and longitude < '.$lng.'+'.$km)->orderRaw('ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('.$lng.'* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 asc');
    }

    /**
     * 一对一关联
     * 管理查询前台登录用户信息(请确保应用表中有UID字段再使用)
     * @return void
     */
    public function user(){
        return $this->hasOne('base\model\SystemUser','id','uid')->field(['id','apps_id','invite_code','phone','nickname','face'])->append(['as_phone']);
    }
}