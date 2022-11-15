<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 公众号
 */
namespace app\demo\web\wechatmp;
use base\WechatController;

class Index extends WechatController
{
    /**
     * 首页
     */
    public function index()
    {
        return '公众号';
    }
}
