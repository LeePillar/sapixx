<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户应用
 */
namespace base\model;
use base\model\SystemTenant;
use base\BaseModel;

class SystemApps extends BaseModel
{

    /**
     * 获取后转换日期格式
     */
    public function getEndTimeAttr($value)
    {
        return date('Y-m-d',$value);
    }

    /**
     * 关联应用
     */
    public function app()
    {
        return $this->hasOne('SystemApp', 'id', 'app_id')->cache(true);
    }

    /**
     * 读取应用配置信息
     */
    public function client()
    {
        return $this->hasMany('SystemAppsClient','apps_id');
    }

    /**
     * 应用后台所属管理员
     */
    public function tenant()
    {
        return $this->hasOne('SystemTenant', 'id', 'tenant_id')->cache(true);
    }

    /**
     * 应用绑定的用户端口创始人
     */
    public function user()
    {
        return $this->hasOne('SystemUser', 'id', 'uid')->cache(true);
    }

    /**
     * 锁定用户
     * @param integer $id
     */
    public static function lock(int $id)
    {
        $result = self::where(['id' => $id])->find();
        $result->is_lock = $result->is_lock ? 0 : 1;
        if ($result->is_lock == 0) {
            $tenant = SystemTenant::where(['id' => $result->tenant_id])->field('is_lock')->find();
            if ($tenant->is_lock == 1) {
                return false;
            }
        }
        return $result->save();
    }
}
