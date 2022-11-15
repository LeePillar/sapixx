<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户服务引入
 */
declare(strict_types=1);
namespace base\constant;
use think\Service;
use base\logic\Wechat;
use base\logic\Wepay;
use base\logic\Sms;
use base\logic\Upload;

/**
 * 微信应用基础服务
 */
class InitLogic extends Service
{

    //快速绑定
    public $bind = [
        'wechat'  => Wechat::class, //微信服务
        'wepay'   => Wepay::class, //微信支付
        'sms'     => Sms::class, //短信服务
        'upload'  => Upload::class, //上传服务
    ];
}
