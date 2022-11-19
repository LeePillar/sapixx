<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 扩展租户端
 */
namespace plugin\test\controller;
use base\TenantController;
use plugin\test\model\Test;

class Index extends TenantController
{

    /**
     * 首页
     */
    public function index()
    {
        return view();
    }
    
}