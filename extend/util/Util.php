<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 常用工具库
 */
namespace util;

class Util {

    /**
     * 获取字符串长度
     * @param string $str 字符串
     * @param int $zhLen 中文字符长度
     * @return int
     */
    public static function getStrLen($str, $zhLen = 0){
        if ($zhLen == 0) {
            return strlen($str);
        } else {
            $match = [];
            preg_match_all("/./us", $str, $match);
            $count = 0;
            foreach ($match[0] as $v) {
                $count += (strlen($v) == 1) ? 1 : $zhLen;
            }
            return $count;
        }
    }

    /**
     * 中英文字符串截取
     * @param  string  $str     字符串
     * @param  integer $start   起始长度
     * @param  integer $length  截取长度
     * @param  string  $charset 字符编码
     * @param  boolean $suffix  截取后缀
     * @return string
     */
    public static function msubstr($str, $start = 0, $length = 42,$suffix = true,$charset="utf-8"){
        $str = strip_tags($str);
        if(function_exists("mb_substr")){
            $slice = mb_substr($str, $start, $length, $charset);
            $strlen = mb_strlen($str,$charset);
        }elseif(function_exists('iconv_substr')){
            $slice = iconv_substr($str,$start,$length,$charset);
            $strlen = iconv_strlen($str,$charset);
        }else{
            $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
            $strlen = count($match[0]);
        }
        if($suffix && $strlen>$length)$slice.='...';
        return $slice;
    }

     /**
     * 显示友好时间格式
     * @param int $time 时间戳
     * @param string $format
     * @param int $start_time
     * @param string $suffix
     * @return string
     */
    public static function ftime($time, $format = 'Y-m-d H:i:s', $start_time = 0, $suffix = '前'){
        if ($start_time == 0) {
            $start_time = time();
        }
        $t = $start_time - $time;
        if ($t < 63072000) {
            $f = [
                '31536000' => '年',
                '2592000' => '个月',
                '604800' => '星期',
                '86400' => '天',
                '3600' => '小时',
                '60' => '分钟',
                '1' => '秒'
            ];
            foreach ($f as $k => $v) {
                if (0 != $c = floor($t / (int)$k)) {
                    return $c . $v . $suffix;
                }
            }
        }
        return date($format,$time);
    }

    /**
     * html转换字符串
     * @param  string $field 字段名/HTML内容
     * @param  type   $type 字段类型
     * @return string
     */
    public static function htmlEncode($data){
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 字符串转换html
     * @param  string $field 字段名/HTML内容
     * @param  type   $type 字段类型
     * @return string
     */
    public static function htmlDecode($data){
        return htmlspecialchars_decode(html_entity_decode($data, ENT_QUOTES, 'UTF-8'));
    }


    /**
     * 函数通过千位分组来格式化数字。
     * @param [type] $str
     * @return void
     */
    public static function priceFormat(string $str) {
        if (empty($str)) {
            return $str = 0;
        }
        return @number_format($str, 2, ".", ",");
    }

    /**
     * 两个任意精度的数字计算
     *
     * @param float $n1  计算数字一
     * @param float $symbol 计算方式
     * @param float $n2  计算数字二
     * @param string $scale 精度
     * @return void
     */
    public static function calculate($n1, $symbol,  $n2, $scale = '2') {
        switch ($symbol) {
            case "+"://加法
                $res = bcadd($n1, $n2, $scale);
                break;
            case "-"://减法
                $res = bcsub($n1, $n2, $scale);
                break;
            case "*"://乘法
                $res = bcmul($n1, $n2, $scale);
                break;
            case "/"://除法
                $res = bcdiv($n1, $n2, $scale);
                break;
            case "%"://求余、取模
                $res = bcmod($n1, $n2, $scale);
                break;
            default: //比较大小 > = <
                $res = bccomp($n1, $n2, $scale);;
                break;
        }
        return $res;
    }

    
    /**
     * 隐藏字符串中一部分
     * @param string $phone
     * @return 从第三个字符隐藏4个字符
     */
    public static function hideStr(string $str, $start = 3, $length = 4)
    {
        return substr_replace($str,'****',$start,$length);
    }
}