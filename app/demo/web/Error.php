<?php

/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 默认错误控制器
 */

namespace app\demo\web;

class Error 
{
    public function __call($method, $args)
    {
        return 'error request!';
    }
}