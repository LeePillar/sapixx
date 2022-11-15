<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 管理用户表
 */
namespace base\model;
use base\BaseModel;

class SystemAdmin extends BaseModel
{
    /**
     * 添加或编辑用户
     * @param  array 数据
     * @return bool
     */
    public static function updateUser($param)
    {
        $data['username']        = $param['username'];
        $data['about']           = $param['about'];
        $data['last_login_ip']   = $param['ip'];
        $data['last_login_time'] = time();
        $data['update_time']     = time();
        if (isset($param['id']) && $param['id'] > 0) {
            $data['id']          = $param['id'];
            if (!empty($param['password'])) {
                $data['password']  = password_hash(md5($param['password']), PASSWORD_DEFAULT);
            }
            return SystemAdmin::update($data);
        }
        $data['password']  = password_hash(md5($param['password']), PASSWORD_DEFAULT);
        return SystemAdmin::create($data);
    }

    /**
     * 判断登录用户
     * @access public
     * @return bool
     */
    public static function login($param)
    {
        $result = SystemAdmin::where(['username' => $param['login_id'], 'locks' => 0])->find();
        if ($result) {
            if (!password_verify(md5($param['login_password']), $result->getAttr('password'))) {
                return FALSE;
            }
            $result->last_login_time = time();
            $result->last_login_ip   = request()->ip();
            $result->save();
            return $result;
        }
        return FALSE;
    }
}