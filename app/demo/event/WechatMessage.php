<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 应用自定义微信消息事件
 */
declare (strict_types = 1);
namespace app\demo\event;

class WechatMessage
{

   /**
     *默认消息处理 
    */
    public function onMessage($param)
    {
        return '信息';
    }

    /**
     *扫码 
    */
    public function onScan($param)
    {
        return '扫码';
    }

    /**
     *关注 
    */
    public function onSubscribe($param)
    {
        return '关注';
    }

    /**
     *取关 
    */
    public function onUnsubscriben($param)
    {
        return '取关';
    }
}
