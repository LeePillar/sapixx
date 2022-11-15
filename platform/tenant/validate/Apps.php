<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 小程序统一接管
 */
namespace platform\tenant\validate;
use think\Validate;

class Apps extends Validate{

    protected $rule = [
        'app_id'        => 'require|integer',
        'apps_id'       => 'require|integer',
        'title'         => 'require',
        'about'         => 'require',
        'safepassword'  => 'require|number|length:6',
        'agree'         => 'require|=:1',
        'appid'         => 'require|alphaNum',
        'token'         => 'require|alphaDash',
        'aes_key'       => 'require|alphaDash',
        'secret'        => 'require|alphaDash',
        'mchid'         => 'require|number',
        'cert'          => 'require',
        'certkey'       => 'require',
        'driver' => 'require|checkDriver',
        'bucket' => 'require',
        'url'    => 'require|url',
    ];

    protected $message = [
        'app_id'               => '应用未上架应用商店',
        'apps_id'              => '未找到你的应用',
        'title'                => '应用名称必填',
        'about'                => '应用简述必须填写',
        'safepassword.require' => '安全密码必须输入',
        'safepassword.integer' => '安全密码仅支持数字',
        'safepassword.length'  => '安全密码仅支持6位数字',
        'agree'                => '开通条款,未确认同意',
        'appid'                => 'APPID必填,且仅支持字母和数字',
        'token'                => 'Token必填,且仅支持字母和数字',
        'aes_key'              => 'AESKey必填,且仅支持字母和数字',
        'secret'               => 'secret必填,且仅支持字母和数字',
        'mchid'                => '商户号必填,且仅支持数字',
        'cert'                 => '应用公钥Cert必填',
        'certkey'              => '应用私钥Key必填',
    ];
    
    protected $scene = [
        'edit'       => ['title','about'],
        'config'     => ['appid','secret','token','aes_key'], //接入配置
        'wechatpay'  => ['appid','mchid','secret'],
        'alipay'     => ['appid','cert','certkey'],
        'onbuy'      => ['app_id','title','about','safepassword','agree'],
        'renewal'    => ['apps_id','safepassword','agree'],
        'storage'    => ['driver','aes_key','secret','bucket','url'],
    ];


    // 自定义验证规则
    protected function checkDriver($value,$rule)
    {
        switch ($value) {
            case 'default':
                return true;
                break;
            case 'aliyun':
                return true;
                break;
            case 'qiniu':
                return true;
                break;
            case 'qcloud':
                return true;
                break;
            default:
                return '对象储存方式选择错误';
                break;
        }
    }
}