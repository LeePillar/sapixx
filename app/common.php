<?php
/**
 * @copyright  Copyright (c) 2022 https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * @author: pillar <ltmn@qq.com>
 * 常用自定义函数库
 */
use think\Response;
use think\facade\Config;
use think\facade\Route;
use think\facade\Request;
use think\route\Url as UrlBuild;
use think\exception\HttpResponseException;
use Ramsey\Uuid\Uuid;
use Godruoyi\Snowflake\Snowflake;

/**
 * 接口参数返回
 * @param array   $var  返回的数据
 * @param string|array  $msg  返回提示语,如果是数组就自动代替未返回的参数
 * @param integer $code 状态码 200
 * @return \think\response\Json
 */
function exitjson(int $code = 200, $msg = '', $data = [])
{
    $response = Response::create(apidata($code, $msg, $data),'json',200);
    throw new HttpResponseException($response);
}

/**
 * 接口参数返回
 * @param array   $var  返回的数据
 * @param string|array  $msg  返回提示语,如果是数组就自动代替未返回的参数
 * @param integer $code 状态码 200
 * @return \think\response\Json
 */
function enjson(int $code = 200, $msg = '', $data = [])
{
    return Response::create(apidata($code, $msg, $data),'json',200);
}

/**
 * API Url生成
 * @param string      $url    路由地址
 * @param array       $vars   变量
 * @param bool|string $domain 域名
 * @return UrlBuild
 */
function api(string $version = 'v1.0',string $url = '', array $vars = [], $domain = true): string
{
    $domain = config('api.api_sub_domain')?:$domain;
    return (string)Route::buildUrl($url, $vars)->domain($domain)->root('/api/'.$version);
}

/**
 * Url生成
 * @param string      $url    路由地址
 * @param array       $vars   变量
 * @param bool|string $domain 域名
 * @return UrlBuild
 */
function apis(string $url = '', array $vars = [], $domain = true): string
{
    $domain = config('api.api_sub_domain')?:$domain;
    return (string)Route::buildUrl($url, $vars)->domain($domain)->root('/apis');
}

/**
 * 扩展Url生成
 * @param string      $url    路由地址
 * @param array       $vars   变量
 * @param bool|string $domain 域名
 * @return UrlBuild
 */
function plugurl(string $url = '', array $vars = [], $domain = true): string
{
    $domain = config('api.api_sub_domain')?:$domain;
    return (string)Route::buildUrl($url, $vars)->domain($domain)->root('/plugin');
}

/**
 * Url生成
 * @param string      $url    路由地址
 * @param array       $vars   变量
 * @param bool|string $domain 域名
 * @return UrlBuild
 */
function web(string $url = '', array $vars = [], $domain = false): string
{
    $appname = app('http')->getName();
    if ($key = array_search($appname,config('app.domain_bind'))) {
        isset($bind[$_SERVER['SERVER_NAME']]) && $domain = $_SERVER['SERVER_NAME'];
        $domain = is_bool($domain) ? $key : $domain;
    }else{
        $root = '/web';
        $url  = substr(Request::baseUrl(),5,6).'/'.($url?:Request::controller().'/'.Request::action());
    }
    return (string)Route::buildUrl($url, $vars)->domain($domain)->root($root??'');
}

/**
 * 友好的调试打印方法
 * @param $var
 */
function code($var, $exit = true)
{
    $output = print_r($var, true);
    $output = "<pre>" . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
    echo $output;
    if ($exit) {
        exit();
    }
}

/**
 * 生成短信4位验证码
 * @param int $limit 要生成的随机数长度
 **/
function getcode($limit = 4)
{
    $rand_array = range(0,9);
    shuffle($rand_array);   //调用现成的数组随机排列函数
    return implode('', array_slice($rand_array, 0, $limit));
}

/**
 * 格式化钱保留小数点
 * @param float $amount
 */
function money($amount)
{
    $amount = round($amount, 2);
    return sprintf("%01.2f", $amount);
}

/**
 * 获取唯一ID
 * @param int  $type 类型
 * @param int  $data 要计算的KEY数据
 * @param bool $number 是否返回数字
 * @return void
 */
function uuid(int $type = 0,$number = false,$data = '',)
{
    switch ($type) {
        case 1: //基于时间
            $uuid = Uuid::uuid1();
            break;
        case 2:  //随机
            $uuid = Uuid::uuid4();
            break;
        case 3:  //基于主机ID、序列号
            $uuid = Uuid::uuid6();
            break;
        case 4: //基于散列的MD5(不建议加密敏感数据)
            $uuid = Uuid::uuid3(Uuid::NAMESPACE_DNS, $data);
            break;
        case 5: //基于SHA1(不建议加密敏感数据)
            $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $data);
            break;
        default: //基于时间
            $snowflake = new Snowflake;
            return $data.$snowflake->id();
            break;
    }
    return $number ? $uuid->getInteger()->toString() : $uuid->toString();
}

/**
 * 接口参数返回
 * @param mixed   $var  返回的数据
 * @param string  $msg  返回提示语,如果是数组就自动代替未返回的参数
 * @param integer $code 状态码 200
 * @return \think\response\Json
 */
function apidata(int $code = 200, $msg = '',$var = [])
{
    $message  = Config::get('code');
    $error = $message[(int)$code] ?? $message[10001];
    if (is_array($msg) || is_object($msg)) {
        $var = $msg;
        $data['message'] = $error;
    } else {
        $data['message'] = $msg ?: $error;
    }
    if (isset($var['url'])) {
        $data['url'] = $var['url'];
    }
    //控制返回的参数后台是否执行iframe父层
    if (isset($var['is_parent'])) {
        $data['is_parent'] = $var['is_parent'];
    }
    $data['code'] = $code;
    $data['data'] = $var;
    return $data;
}

/**
 * 模板过滤函数
 * @param string $content
 * @return string
 */
function htmlentities_view($content)
{
    return htmlentities((string)$content);
}

/**
 * 请求默认过滤
 * @param string $content
 * @return string
 */
function htmlentities_request($content)
{
    return strip_tags(htmlspecialchars(trim((string)$content)));
}