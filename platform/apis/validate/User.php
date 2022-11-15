<?php
/**
 * @copyright   Copyright (c) 2021 https://www.sapixx.com All rights reserved.
 * @license     Licensed (http://www.apache.org/licenses/LICENSE-2.0).
 * @author      pillar<ltmn@qq.com>
 * 用户验证
 */
namespace platform\apis\validate;
use think\Validate;

class User extends Validate{

    protected $rule = [
        'token'             => 'require|max: 25|token',
        'safepassword'      => 'require|length:6',
        'resafepassword'    => 'require|confirm:safepassword',
        'phone'             => 'require|mobile',
        'code'              => 'require|number|length:4',
    ];

    protected $message = [
        'token'                => '不合法的数据来源',
        'safepassword.require' => '密码必须输入',
        'safepassword.length'  => '密码只能输入6位数字',
        'resafepassword'     => '密码输入不一致',
        'phone'                => '手机号错误',
        'code'                 => '验证码错误',
    ];

    protected $scene = [
        'chickPassword'   => ['safepassword'],  //检查密码
        'setSafePassword' => ['id','safepassword','resafepassword','code'],  //修改安全密码
    ];
}