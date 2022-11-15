<?php
/**
 * @copyright 2022 http://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 默认路由配置
 */

use think\facade\Route;
//控制台
Route::rule('index', 'admin/index/index');
Route::rule('manage', 'apps/manage');
Route::rule('login', 'admin/index/login');
Route::rule('password','admin/index/password');
Route::rule('logout', 'admin/index/logout');
//租户公共服务路径简化
Route::rule('upload','common/upload');  //文件上传