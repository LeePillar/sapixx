<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 空控制器
 */
namespace platform\install\controller;

class Error
{
     /**
     * 空的控制器空方法
     * @param string $method
     * @param array  $args
     * @return void
     */
    public function __call($method, $args)
    {
        if (is_file(PATH_RUNTIME.'install.lock'))
        {
            exit('请删除install.lock文件后再运行SAPI++安装器!');
        }
        return redirect((string)url('index/index'));
    }
}