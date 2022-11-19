<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 租户端
 */
namespace plugin\test\controller;
use base\AdminController;

class Admin extends AdminController
{

    /**
     * 首页
     */
    public function index()
    {
       $this->success('后台管理中心默认首页','中台首页');
    }
}