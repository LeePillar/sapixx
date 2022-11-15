<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 请求统一处理
 */
namespace base\provider;

// 应用请求对象类
class Request extends \think\Request
{
    protected $filter = ['htmlentities_request'];
    
}