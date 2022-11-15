<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 邀请码服务
 */
namespace invite;
use think\Facade;

class Invite extends Facade{

    protected static function getFacadeClass()
    {
        return 'invite\InviteCodeService';
    }
}