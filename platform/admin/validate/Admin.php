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

class Admin extends Validate
{

    protected $rule = [
        'username'         => 'require',
        'login_id'         => 'require',
        'login_password'   => 'require',
        'password'         => 'require|confirm',
        'password_confirm' => 'require',
        'about'            => 'require',
        'captcha'          => 'require|captcha',
        'id'               => 'require|number',
        //代理
        'title'            => 'require',
        'money'            => 'require|float',
        'money_app'        => 'require|integer',
        'renew_money'      => 'require|float',
        'renew_app'        => 'require|integer',
        'discount'         => 'require|integer|>=:2|<=:9',
    ];

    protected $message = [
        'id'               => '未找到用户',
        'username'         => '用户名必须填写',
        'password'         => '密码必须填写',
        'password_confirm' => '两次密码输入不一致',
        'about'            => '备注必须填写',
        'captcha'          => '验证码错误',
        'login_id'         => '用户名必须填写',
        'login_password'   => '密码必须填写',
        //代理
        'title'               => '名称必须填写',
        'price'               => '开通价格必须填写',
        'price_gift'          => '开通赠送金额必须填写',
        'recharge_price'      => '续费价格必须填写',
        'recharge_price_gift' => '续期赠送金额必须填写',
        'discount'            => '折扣必须填写,必须是2-9之间',
    ];

    protected $scene = [
        'login'    => ['login_id', 'login_password', 'captcha'],
        'edit'     => ['id', 'username', 'password', 'password_confirm', 'about'],
        'password' => ['password', 'password_confirm'],
        'agent'    => ['title', 'price', 'price_gift', 'recharge_price', 'recharge_price_gift', 'discount'],
    ];
}
