<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * Web
 */
namespace app\demo\web\web;
use think\facade\Request;
use base\WebController;

class Index extends WebController
{

    /**
     * 首页
     */
    public function index()
    {
        return 'Web';
    }
}
