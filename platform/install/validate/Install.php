<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 链接验证
 */
namespace platform\install\validate;
use think\Validate;

class Install extends Validate
{

    protected $rule = [
        'DB_HOST' => 'require',
        'DB_PORT' => 'require|integer',
        'DB_NAME' => 'require',
        'DB_USER' => 'require',
        'DB_PWD'  => 'require',
    ];

    protected $message = [
        'DB_HOST' => '数据库主机必须填写',
        'DB_PORT' => '数据库端口必须填写',
        'DB_NAME' => '数据库名称必须填写',
        'DB_USER' => '用户名必须填写',
        'DB_PWD'  => '密码必须填写',
    ];

    protected $scene = [
        'db' => ['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PWD'],
    ];
}
