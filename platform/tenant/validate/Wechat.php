<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 小程序统一接管
 */
namespace platform\tenant\validate;
use think\Validate;

class Wechat extends Validate{

    protected $rule = [
        'category'         => 'require|integer|in:0,1,2,3,4,5',
        'scene'            => 'require|array',
        'method'           => 'require|array',
        'has_audit_team'   => 'require|integer|in:0,1'
    ];
    
    protected $message = [
        'category'       => '小程序类目必须填写',
        'scene'          => 'UGC场景必须选择必须填写',
        'method'         => 'UGC安全机制必须填写',
        'has_audit_team' => 'UGC审核团队必须填写',  
    ];

    protected $scene = [
        'submitWechat' => ['category','scene','method','has_audit_team']
    ];
}