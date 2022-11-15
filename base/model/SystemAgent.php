<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 代理管理
 */
namespace base\model;
use base\model\SystemTenant;
use base\BaseModel;

class SystemAgent extends BaseModel{

    /**
     * 一对多关联
     */
    public function tenant()
    {
        return $this->hasMany(SystemTenant::class,'agent_id');
    }
}