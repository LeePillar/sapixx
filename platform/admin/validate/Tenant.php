<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户验证
 */

namespace platform\admin\validate;

use think\Validate;

class Tenant extends Validate
{

    protected $rule = [
        'id'            => 'require|number',
        'app_id'        => 'require|number',
        'phone_id'      => 'require|mobile',
        'username'      => 'require',
        'password'      => 'min:6',
        'safe_password' => 'integer|length:6',
        'money'         => 'require|integer|>=:0',
        'lock_money'    => 'require|integer|>=:0',
    ];

    protected $message = [
        'id'                    => '用户未找到',
        'app_id'                => '您的应用没有选择',
        'phone_id.require'      => '手机号不能空',
        'phone_id.mobile'       => '手机号不正确',
        'username'              => '用户名必须填写',
        'password'              => '密码不能少于 6 个字符',
        'safe_password'         => '安全密码只能输入 6 位数字',
        //充值余额
        'money.require'         => '充值金额必须填写',
        'money.integer'         => '充值金额必须是整数',
        'money.>='              => '充值金额必须大于00元',
        //赠送金额
        'lock_money.require'    => '充值金额必须填写',
        'lock_money.integer'    => '充值金额必须是整数',
        'lock_money.>='         => '充值金额必须大于00元',
    ];

    protected $scene = [
        'edit'      => ['id','username','phone_id','password','safe_password'],
        'recharge'  => ['lock_money','money'],
    ];
}
