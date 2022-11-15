<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 微信开放平台授权事件
 */
declare (strict_types = 1);
namespace base\event;
use think\facade\Event;
use util\Dir;

class WechatTicket
{

    /**
     * 微信开放平台推送事件
    */
    public function onWithMessage($message){
        $appPath = Dir::getDir(PATH_APP,FORBIDEN);
        foreach ($appPath as $value) {
            try {
                $wechat_ticket = PATH_APP.$value.DS.'event'.DS.'WechatTicket.php';
                $namespace = "app\\{$value}\\event\\WechatTicket";
                if(is_file($wechat_ticket) && class_exists($namespace) && method_exists($namespace,'handle')){
                    Event::listen($value,$namespace);
                    Event::trigger($value,$message);
                }
            } catch (\Exception $e) {
                //错误跳过
            }
        }
    }

    /**
     *处理授权成功事件 
    */
    public function onAuthorize($message){}

    /**
     *处理授权更新事件 
    */
    public function onAuthorizeUpdated($message){}

    /**
     *处理授权取消事件 
    */
    public function onUnauthorized($message){}

    /**
     *处理 VerifyTicket 推送事件
    */
    public function onVerifyTicket($message){
        
    }
}
