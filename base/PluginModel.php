<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 扩展基础模型
 */
namespace base;

class PluginModel extends BaseModel
{
    //插件数据库默认后置
    protected $suffix = '_plugin';
    
    //定义全局的查询范围
    protected $globalScope = ['Apps'];

}