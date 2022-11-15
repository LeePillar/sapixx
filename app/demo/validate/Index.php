<?php

/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 表单验证
 */

namespace app\demo\validate;

use think\Validate;

class Index extends Validate
{

    protected $rule = [
        'test' => 'require',
    ];

    protected $message = [
        'test' => '必须输入测试数据',
    ];

    protected $scene = [
        'post' => ['test'],
    ];
}
