<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 安全过滤
 */
namespace filter;

class Filter {

    /**
     * 安全过滤类-通用数据过滤
     * @param string $value 需要过滤的变量
     * @return string|array
     */

    public static function filter_escape($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if(is_array($v)){
                    $value[$k] = self::filter_escape($v);
                }else{
                    $v = self::filter_sql($v);
                    $v = self::filter_script($v);
                    $v = self::filter_str($v);
                    $value[$k] = self::filter_html($v);
                }
            }
        } else {
            $value = self::filter_sql($value);
            $value = self::filter_script($value);
            $value = self::filter_str($value);
            $value = self::filter_html($value);
        }
        return $value;
    }

    /**
     * 安全过滤类-过滤javascript,css,iframes,object等不安全参数 过滤级别高
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filter_script($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::filter_script($v);
            }
            return $value;
        } else {
            $parten =[
                "/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i",
                "/<script(.*?)>(.*?)<\/script>/si",
                "/<iframe(.*?)>(.*?)<\/iframe>/si",
                "/<object.+<\/object>/isU"
            ];
            $replace =["\\2", "", "", ""];
            $value = preg_replace($parten, $replace, $value, -1, $count);
            if ($count > 0) {
                $value = self::filter_script($value);
            }
            return $value;
        }
    }

    /**
     * 安全过滤类-过滤HTML标签
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filter_html($value) {
        if (function_exists('htmlspecialchars')) {
            return htmlspecialchars($value);
        }
        return str_replace(["&", '"', "'", "<", ">"],["&amp;", "&quot;", "&#039;", "&lt;", "&gt;"], $value);
    }

    /**
     * 安全过滤类-对进入的数据加下划线 防止SQL注入
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filter_sql($value) {
        $sql    = ["select", 'insert', "update", "delete", "\'", "\/\*","\.\.\/", "\.\/", "union", "into", "load_file", "outfile"];
        $sql_re = ["","","","","","","","","","","",""];
        return str_replace($sql, $sql_re, $value);
    }


    /**
     * 安全过滤类-字符串过滤 过滤特殊有危害字符
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filter_str($value) {
        $value = str_replace(["\0","%00","\r"], '', $value);
        $value = preg_replace(['/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'], ['', '&amp;'], $value);
        $value = str_replace(["%3C",'<'], '&lt;', $value);
        $value = str_replace(["%3E",'>'], '&gt;', $value);
        $value = str_replace(['"',"'","\t"],['&quot;','&#39;',''], $value);
        return $value;
    }

    /**
     * 私有路径安全转化
     * @param string $fileName
     * @return string
     */
    public static function filter_dir($fileName) {
        $tmpname = strtolower($fileName);
        $temp = ['://',"\0", ".."];
        if (str_replace($temp, '', $tmpname) !== $tmpname) {
            return false;
        }
        return $fileName;
    }

    /**
     * 过滤目录
     * @param string $path
     * @return array
     */
    public static function filter_path($path) {
        $path = str_replace(["'",'#','=','`','$','%','&',';','>'], '', $path);
        return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $path), '/');
    }

    /**
     * 严格过滤目录（任意路径参数）
     * @param string $path
     * @return void
     */
    public static function  filter_path_strict($path){
        $path = self::filter_str(str_replace(['\\','../','./','..','\'','//'],['/','','','','',''],self::filter_path($path)));
        return self::filter_sql($path);
    }

    /**
     * 过滤PHP标签
     * @param string $string
     * @return string
     */
    public static function filter_phptag($string) {
        return str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $string);
    }

    /**
     * 安全过滤类-返回函数
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function str_out($value) {
        $badstr = ["&", '"', "'", "<", ">", "%3C", "%3E"];
        $newstr = ["&amp;", "&quot;", "&#039;", "&lt;", "&gt;", "&lt;", "&gt;"];
        $value  = str_replace($newstr, $badstr, $value);
        return stripslashes($value); //下划线
    }

    /**
     * 清楚字符串中有Emoji的昵称
     * @param  string $str 需要过滤的值
     * @return string
     */
    public static function filter_Emoji($str){
        $str = preg_replace_callback('/./u',function (array $match) {
          return strlen($match[0]) >= 4 ? '' : trim($match[0]);
        },$str);
        return trim($str);
    }
}