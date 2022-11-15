<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户管理
 */
namespace base\model;
use base\model\SystemApps;
use base\BaseModel;

class SystemTenant extends BaseModel
{

    /**
     * 获取后转换日期格式
     */
    public function getLoginTimeAttr($value)
    {
        return date('Y-m-d H:i',$value);
    }

    //随机一个头绪
    public function getAvatarAttr($value)
    {
        $num = range(1,5);
        shuffle($num);
        return DAMAIN.'common/img/avatar-5.png';
    }

    /**
     * 代理身份
     * @return void
     */
    public function agent()
    {
        return $this->hasOne(SystemAgent::class,'id', 'agent_id');
    }
    /**
     * 权限组
     */
    public function group()
    {
        return $this->hasOne(SystemTenantGroup::class,'id','group_id');
    }

    /**
     * 一对多关联租户开通的所有应用
     */
    public function apps()
    {
        return $this->hasMany(SystemApps::class,'tenant_id');
    }

    /**
     * 添加编辑
     * @param  array $param 数组
     */
    public static function edit(array $param)
    {
        $data['phone_id']  = $param['phone_id'];
        $data['username']  = $param['username'];
        $data['agent_id']  = $param['agent_id']??0;
        $data['group_id']  = $param['group_id']??0;
        if (isset($param['id']) && $param['id'] > 0) {
            if (!empty($param['password'])) {
                $data['password'] = password_hash(md5($param['password']), PASSWORD_DEFAULT);
            }
            if (!empty($param['safe_password'])) {
                $data['safe_password'] = password_hash(md5($param['safe_password']),PASSWORD_DEFAULT);
            }
            $data['id'] = $param['id'];
            return self::update($data);
        } else {
            $data['parent_id']      = $param['parent_id']??0;
            $data['parent_apps_id'] = $param['parent_apps_id']??0;
            $data['lock_config']    = $param['lock_config']??0;
            $data['password']       = empty($param['password'])?password_hash(md5(123456),PASSWORD_DEFAULT):password_hash(md5($param['password']),PASSWORD_DEFAULT);
            $data['safe_password']  = empty($param['safe_password'])?password_hash(md5(substr($param['phone_id'],-6)), PASSWORD_DEFAULT):password_hash(md5($param['safe_password']),PASSWORD_DEFAULT);
            $data['login_ip']       = request()->ip();
            $data['login_time']     = time();
            return self::create($data);
        }
    }

    /**
     * 锁定用户
     * @param integer $id
     */
    public static function lock(int $id)
    {
        $result = self::where(['id' => $id])->find();
        $result->is_lock = $result->is_lock ? 0 : 1;
        if ($result->is_lock) {
            SystemApps::where(['tenant_id' => $id])->update(['is_lock' => 1]);
        }
        return $result->save();
    }

    /**
     * 锁定用户
     * @param integer $id
     */
    public static function lockConfig(int $id)
    {
        $result = self::where(['id' => $id])->find();
        $result->lock_config = $result->lock_config ? 0 : 1;
        return $result->save();
    }

 
    /**
     * 增加金额
     * @param int $tenant_id
     * @param float $money
     * @return void
     */
    public static function moneyInc($tenant_id,$money)
    {
        $bank = self::where(['id' => $tenant_id])->find();
        if ($bank) {
            $bank->money       = ['inc',$money];
            $bank->update_time = time();
            return $bank->save();
        }
        return false;
    }

    /**
     * 金额减少
     * @param int $tenant_id
     * @param float $money
     * @return void
     */
    public static function moneyDec($tenant_id,$money)
    {
        $bank = self::where(['id' => $tenant_id])->find();
        if ($bank) {
            //判断余额
            if(money($bank->money+$bank->lock_money) < $money){
                return false;
            }
            //减少余额
            if($bank->money >= $money){
                $bank->money = ['dec',$money];
            }else{
                $bank->lock_money = ['dec',money($money-$bank->money)];
                $bank->money      = 0;
            }
            $bank->update_time = time();
            return $bank->save();
        }
        return false;
    }
}
