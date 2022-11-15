<?php
/**
 * @copyright   Copyright (c) 2022 https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * @author: pillar <ltmn@qq.com>
 * 
 * 全局中间件定义文件
 */
return [
    // Session初始化
    \think\middleware\SessionInit::class,
    //跨域请求支持
    \base\constant\InitCross::class,
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    //表单令牌
    // \think\middleware\FormTokenCheck::class,
];