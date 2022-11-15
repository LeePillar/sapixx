<?php
/**
 * @copyright   Copyright (c) 2022 https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * @author: pillar <ltmn@qq.com>
 * 应用入口文件
 */

namespace think;
if (version_compare(PHP_VERSION, '8.0.2', '<')) {
    echo '请升级您的PHP版本，要求：PHP >= 8.0.2，当前版本为 ' . PHP_VERSION;
    exit;
}
require __DIR__ . '/../vendor/autoload.php';
// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);