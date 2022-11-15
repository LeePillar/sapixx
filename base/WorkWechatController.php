<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 企业微信基础控制器
 */
namespace base;

class WorkWechatController extends BaseController
{
    protected $middleware = ['wechat'];
    
}
