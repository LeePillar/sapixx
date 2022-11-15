<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户验证
 */
namespace platform\tenant\validate;
use think\Validate;

class User extends Validate{

    protected $rule = [
        'password'     => 'require|length:6,18',
        'safepassword' => 'require|number|length: 6',
        'phone'        => 'require|mobile',
        'code'         => 'require|number|length:4,6',
        'invite'       => 'require|alphaNum|min:4',
    ];

    protected $message = [
        'safepassword' => '密码只能输入6位数字',
        'password'     => '密码必须大于6-18位字符',
        'phone'        => '手机号错误',
        'code'         => '验证码错误',
        'invite'       => '请属于正确的邀请码',
    ];

    protected $scene = [
        'edit'         => ['phone'],
        'safepassword' => ['safepassword'],
        'password'     => ['password'],
        'invite'       => ['invite'],
    ];
}