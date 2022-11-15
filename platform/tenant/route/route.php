<?php
/**
 * @copyright 2022 http://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 默认路由配置
 */
use think\facade\Route;
Route::rule('index', 'tenant/index/index');
Route::rule('login', 'tenant/index/login');
Route::rule('register','tenant/index/register');
Route::rule('forgot', 'tenant/index/forgot');
Route::rule('logout', 'tenant/index/logout');
Route::rule('account','tenant/account/index');
Route::rule('manage','tenant/account/manage');
//租户公共服务路径简化
Route::rule('upload','common/upload');  //文件上传