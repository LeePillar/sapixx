<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 小程序统一接管
 */
namespace platform\admin\validate;
use think\Validate;

class Apps extends Validate
{

    protected $rule = [
        'id'        => 'require|number',
        'app_id'    => 'require|number',
        'tenant_id' => 'require|number',
        'title'     => 'require',
        'about'     => 'require',
        'end_time'  => 'require|date',
    ];

    protected $message = [
        'id'        => 'ID丢失',
        'app_id'    => '绑定应用必须选择',
        'tenant_id' => '绑定租户必须选择',
        'title'     => '应用名称必须填写',
        'about'     => '应用简述必须填写',
        'end_time'  => '到时时间必须填写',
    ];

    protected $scene = [
        'add'  => ['app_id','tenant_id','title','about','end_time'],
        'edit' => ['id','title','about','end_time'],
    ];
}
