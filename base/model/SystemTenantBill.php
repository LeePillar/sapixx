<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 *  会员消费账单
 */
namespace base\model;
use base\BaseModel;

class SystemTenantBill extends BaseModel{

    //成功失败
    public function getStateTextAttr($value,$data)
    {
        return $data['state']==1?'成功':'失败';
    }

    //
    public function getTypesAttr($value,$data)
    {
        return $data['money']>0?'充值':'消费';
    }
}