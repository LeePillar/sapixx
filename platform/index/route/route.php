<?php
/**
 * @copyright 2022 http://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 默认路由配置
 */
use think\facade\Route;
Route::rule('index/<cate>', 'index/index/index');
Route::rule('details/<id>', 'index/index/details');