<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 租户端
 */
namespace app\demo\controller;
use base\TenantController;

class Bank extends TenantController
{

    /**
     * 需要访问控制的方法
     * @var array
     */
    protected $aclOff = ['account'];

    /**
     * 首页
     */
    public function index()
    {
       $this->error('ACL访问Index控制效果展示');
    }

    /**
     * 帐号余额
     */
    public function account()
    {
      $this->error('ACL访问Account控制效果展示');
    } 
}
