<?php
/**
 * @copyright   Copyright (c) 2022 https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * @author: pillar <ltmn@qq.com>
 * 事件定义文件
 */
return [

    'bind' => [],

    'listen' => [
        'AppInit'   => ['base\constant\InitApp'],
        'HttpRun'   => ['base\constant\InitRoute'],
        'HttpEnd'   => [],
        'LogLevel'  => [],
        'LogWrite'  => [],
    ],

    'subscribe'     => [],
];