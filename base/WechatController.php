<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 微信公众号基础控制器
 */
namespace base;

class WechatController extends BaseController
{
    protected $middleware = ['wechat'];
}