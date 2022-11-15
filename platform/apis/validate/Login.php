<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license (http://www.apache.org/licenses/LICENSE-2.0).
 * @link https://www.sapixx.com
 * 小程序公共验证
 */
namespace platform\apis\validate;
use think\Validate;

class Login extends Validate{

    protected $rule = [
        'code'  => 'require|length: 32',
        'get'   => 'require|length: 6',
        'state' => 'require',
    ];

    protected $message = [
        'code'  => '微信Code验证码丢失',
        'get'   => '应用认证码不正确',
        'state' => 'state不能为空',
    ];
    
    protected $scene = [
        'weapp' => ['code'], //小程序
        'wemp'  => ['get','code','state'],  //公众号
    ];
}