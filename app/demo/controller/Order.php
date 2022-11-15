<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 租户端
 */
namespace app\demo\controller;
use base\TenantController;

class Order extends TenantController
{

   /**
    * 需要访问控制的方法
    * @var array
    */
    protected $aclOff = ['service'];

    /**
     * 首页
     */
    public function index()
    {
       $this->error('效果展示');
    }

    /**
     * 订单服务
     */
    public function service()
    {
       $this->error('ACL访问控制效果展示,');
    }    
}
