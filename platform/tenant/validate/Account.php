<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 帐号管理
 */
namespace platform\tenant\validate;
use think\Validate;

class Account extends Validate
{

    protected $rule = [
        'id'                    => 'require|number',
        'captcha'                =>'require|captcha',
        'sms_code'              => 'require',
        'phone_id'              => 'require|mobile',
        'username'              => 'require',
        'login_password'        => 'require|alphaDash|length:6,18',
        'edit_password'         => 'alphaDash|length:6,18',
        'password'              => 'require|alphaDash|length:6,18|confirm:repassword',
        'repassword'            => 'require',
        'safe_password'         => 'require|integer|length: 6',
        'safe_password'         => 'require|confirm:safe_password_confirm',
        'safe_password_confirm' => 'require|integer|length: 6',
        'money'                 => 'require|integer|egt:10',
        'agree'                 => 'require|=:1',
    ];

    protected $message = [
        'id'                            => '用户未找到',
        'captcha'                       => '验证码不正确',
        'sms_code'                      => '验证码必须填写',
        'phone_id.require'              => '手机号不能空',
        'phone_id.mobile'               => '手机号不正确',
        'login_password.require'        => '密码必须填写或输入密码不一致',
        'login_password.length'         => '密码仅支持6位至18位字符',
        'login_password.alphaDash'      => '密码仅支持字母和数字，下划线_及破折号-',
        'edit_password.length'          => '密码仅支持6位至18位字符',
        'edit_password.alphaDash'       => '密码仅支持字母和数字，下划线_及破折号-',
        'username'                      => '用户名必须填写',
        'password.require'              => '密码必须填写或输入密码不一致',
        'password.length'               => '密码不能小于6位',
        'password.alphaDash'            => '密码仅支持字母和数字，下划线_及破折号-',
        'repassword'                    => '两次密码输入不一致',
        'safe_password.require'         => '请输入安全密码',
        'safe_password.integer'         => '安全密码只能输入6位数字',
        'safe_password.length'          => '安全密码只能输入6位数字',
        'safe_password.confirm'         => '两次密码输入不一致',
        'safe_password_confirm.integer' => '安全密码只能输入6位数字',
        'safe_password_confirm.length'  => '安全密码只能输入6位数字',
        'agree'                         => '充值条款,未确认同意',
        'money.require'                 => '充值金额必须填写',
        'money.integer'                 => '充值金额必须是整数',
        'money.egt'                     => '充值金额必须大于10元',
    ];

    protected $scene = [
        'password'     => ['login_password','password', 'repassword'],//修改密码
        'safepassword' => ['sms_code','safe_password', 'safe_password_confirm'],  //修改安全密码
        'recharge'     => ['money','agree'], //充值
        'getsms'       => ['phone_id'], //验证手机号
        'phone'        => ['sms_code','phone_id'], //修改手机号
        'edit'         => ['username'], //修改昵称     
        'register'     => ['phone_id','sms_code','password','repassword'], //修改昵称
        'login'        => ['phone_id', 'login_password','captcha'], //登录
        'subset'       => ['username','phone_id','edit_password'], //设置子帐号
    ];
}
