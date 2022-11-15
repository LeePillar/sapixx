<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 租户端
 */
namespace app\demo\controller;
use base\TenantController;

class Shop extends TenantController
{

    
    /**
     * 首页
     */
    public function index()
    {
       $this->error('产品列表');
    }


    /**
     * 增加
     */
    public function add()
    {
       $this->error('增加');
    }

    /**
     * 修改
     */
    public function edit()
    {
       $this->error('修改');
    }

    /**
     * 上下架
     */
    public function onsale()
    {
       $this->error('上下架');
    }

}
