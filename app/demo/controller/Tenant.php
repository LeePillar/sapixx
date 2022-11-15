<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 租户端
 */
namespace app\demo\controller;
use base\TenantController;

class Tenant extends TenantController
{

    /**
     * 后台主框架
     */
    public function index()
    {
        return view();
    }
}
