<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * jwt认证与加密解密工具
 */
declare(strict_types=1);

namespace base\service;
use think\Request;
use think\facade\Config;
use Firebase\JWT\JWT;
use code\Code;

/**
 * jwt加解密
 */
class CheckJwt
{

    /** 
     * @var Request 
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
 
    /** 加密
     * @param $jwt object|array 要加密的Data对象
     * @return mixed 已编码的json token字符串
     * @throws \think\Exception 读取配置失败时
     */
    public static function enJwt($jwt)
    {
        $secretKey = Config::get('api.jwt_secret_key');
        $algorithm = Config::get('api.jwt_algorithm');
        if (!$secretKey || !$algorithm) {
            exitjson(10003);
        }
        //使用JWT解码并返回
        return self::enToken(Jwt::encode($jwt,$secretKey,$algorithm));
    }

    /**
     * @param $jwt 已编码的json web token字符串
     * @return bool object|boolean 签名认证通过时，代表JWT加密的Data的对象;解码或签名认证失败时false
     * @throws \think\Exception 读取配置失败时
     */
    public static function deJwt($jwt)
    {
        $secretKey = Config::get('api.jwt_secret_key');
        $algorithm = Config::get('api.jwt_algorithm');
        if (!$secretKey || !$algorithm) {
            exitjson(10003);
        }
        try {
            $decode = Jwt::decode(self::deToken($jwt),$secretKey,[$algorithm]);
            if ($decode) {
                return $decode;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 加密
     * @param  string  $string 加密字符串
     * @param  integer $expiry 失效时间
     * @return mixed
     */
    public static function enToken($string)
    {
        return Code::en($string,Config::get('api.jwt_salt'),'ENCODE',Config::get('api.jwt_logout_time'));
    }

    /**
     * 解密
     * @param  string $string 加密字符串
     * @return mixed
     */
    public static function deToken($string)
    {
        return Code::de($string,Config::get('api.jwt_salt'));
    }

    /**
     * makeSign 生成签名
     * @param $data
     * @param string $signType
     * @return string
     */
    public static function makeSign(array $data,$key,$signType = 'HMAC-SHA256'){
        ksort($data);
        $string = self::toUrlParams($data)."&key=".$key;
        if ($signType == 'md5') {
            $string = md5($string);
        } else {
            $string = hash_hmac("sha256",$string,$key);
        }
        return strtoupper($string);
    }

    /**
     * ToUrlParams格式化参数格式化并删除空值,生成url参数
     * @param $data
     * @return string
     */
    public static function toUrlParams(array $data){
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v !== "null" && $v !== "" && !is_null($v) && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff,"&");
        return $buff;
    }
}
