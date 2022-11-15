<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 独立应用消息事件
 */
declare (strict_types = 1);
namespace base\event;

class WechatMessage
{

    /**
     * 默认消息处理
     * @param array $param ['message' => '服务消息,'client' ='客户端对象']
     */
    public function onMessage($param)
    {
        return '信息';
    }

    /**
     *扫码 
     * @param array $param ['message' => '服务消息,'client' ='客户端对象']
     */
    public function onScan($param)
    {
        return '扫码';
    }

    /**
     *关注 
     * @param array $param ['message' => '服务消息,'client' ='客户端对象']
     */
    public function onSubscribe($param)
    {
        return '关注';
    }

    /**
     *取关 
     * @param array $param ['message' => '服务消息,'client' ='客户端对象']
     */
    public function onUnsubscriben($param)
    {
        return '取关';
    }
}
