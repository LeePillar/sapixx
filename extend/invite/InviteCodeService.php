<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 邀请码服务
 */
namespace invite;

class InviteCodeService{

    protected $key,$num;

    public function __construct()
    {
        $this->key = 'kh8sjpdazetnmb5yw7rq4gc9fuv3216x';
        $this->num = strlen($this->key);
    }

    /**
     * 根据用户ID创建邀请码
     * @param integer $uid
     * @return void
     */
    public function enCode(int $uid){
        $code = ''; //邀请码
        while ($uid > 0) {  //转进制
            $mod  = $uid % $this->num; //求模
            $uid  = ($uid - $mod) / $this->num;
            $code = $this->key[$mod] . $code;
        }
        //不足用0补充
        return str_pad($code, 4, '0', STR_PAD_LEFT); 
    }

    /**
     * 根据邀请码解密ID
     * @param string $code
     * @return void
     */
    public function deCode($code){
        if (strrpos($code,'0') !== false)
            $code = substr($code, strrpos($code,'0') + 1);
        $len  = strlen($code);
        $code = strrev($code);
        $uid  = 0;
        for ($i = 0; $i < $len; $i++)
            $uid += strpos($this->key, $code[$i]) * pow($this->num,$i);
        return $uid;
    }
}