<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户应用
 */
namespace base\model;
use base\BaseModel;

class SystemPlugins extends BaseModel
{
    protected $autoWriteTimestamp = true;
    
    /**
     * 关联应用
     */
    public function apps()
    {
        return $this->hasOne('SystemApps', 'id', 'apps_id')->cache(300);
    }

    /**
     * 应用后台所属管理员
     */
    public function tenant()
    {
        return $this->hasOne('SystemTenant', 'id', 'tenant_id')->cache(300);
    }

    /**
     * 所属插件
     */
    public function plugin()
    {
        return $this->hasOne('SystemPlugin', 'id', 'plugin_id')->cache(300);
    }

    /**
     * 获取权限组ID
     * @return array
     */
    public function getGroupIdsAttr($value)
    {
        return empty($value) ? [] : explode(',',$value);
    }
}
