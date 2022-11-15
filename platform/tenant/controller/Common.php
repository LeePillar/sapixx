<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户调用模块
 */
namespace platform\tenant\controller;
use base\TenantController;

class Common extends TenantController
{
    /**
     *  通过中台进入租户端的转跳
     */
    public function tenant(){
        return redirect((string)url('tenant/index/index'),302);
    }
}