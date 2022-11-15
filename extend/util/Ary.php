<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 常用数组工具
 */
namespace util;

class Ary
{

    /**
     * 多维数组中修改某个值
     * @param  array $arr 要处理的数组
     * @param callable $function($value,$key)
     * @return array
     */
    public static function array_values_set(array $arr,callable $function)
    {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $key => $value) {
            if(is_array($value)){
                $arr[$key] = self::array_values_set($value,$function);
            }else{
                $arr[$key] = call_user_func($function,$value,$key);
            }
        }
        return $arr;
    }

    /**
     * 根据条件追加值
     * @param array $arr 要处理的数组
     * @param array|string $appendkey
     * @param callable $function($value,$key)
     * @return array
     */
    /** */
    public static function array_values_append(array $arr,string|array $appendkey,callable $function)
    {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $key => $value) {
            if(is_array($value)){
                $arr[$key] = self::array_values_append($value,$appendkey,$function);
            }else{
                $arr[$key] = $value;
                if(is_array($appendkey)){
                    foreach ($appendkey as $name) {
                        $arr[$name]  = call_user_func($function,$value,$key);
                    }
                }else{
                    $arr[$appendkey] = call_user_func($function,$value,$key);
                }
            }
        }
        return $arr;
    }
    
    /**
     * 重建多维数组的索引
     */
    public static function reform_keys($array){
        if(!is_array($array)){
            return $array;
        }
        $keys = implode('', array_keys($array));
        if(is_numeric($keys)){
            $array = array_values($array);
        }
        $array = array_map('self::reform_keys', $array);
        return $array;
    }

    /**
     * 删除数组中指定键值
     * @param  array $arr  要删除的数组
     * @return array
     */
    public static function array_values_unset(string $values, array $ary)
    {
        $ary_key = array_search($values, $ary);
        if ($ary_key !== false) {
            unset($ary[$ary_key]);
        }
        return $ary;
    }

    /**
     * 方法库-数组去除空值
     * @param string $arr  数组
     * @return array
     */
    public static function array_remove_empty($arr, $trim = true)
    {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                self::array_remove_empty($arr[$key]);
            } else {
                $value = ($trim == true) ? trim((string)$value) : $value;
                if ($value == "") {
                    unset($arr[$key]);
                } else {
                    $arr[$key] = $value;
                }
            }
        }
        return $arr;
    }

    /**
     * 多维数组删除相同的数组组合
     * @param  array $arr  要删除的数组
     * @return array
     */
    public static function unique_array($arr)
    {
        foreach ($arr as $k => $v) {
            $arr[$k] = serialize($v);
        }
        $arr = array_unique($arr);
        foreach ($arr as $k => $v) {
            $arr[$k] = unserialize($v);
        }
        return $arr;
    }

    /**
     * 判断数组是否存在某个Key的值,
     * @param  array  $array  
     * @param  string $key  取值或使用”.“号从嵌套数组中获取值
     * @param  string $default 如果指定键不存在的话则返回该默认值
     * @return string|array
     */
    public static function array_get($array, $key, $default = null){
        if (is_null($key)) {
            return $array; 
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }
            $array = $array[$segment];
        }
        return $array;
    }

    
    /**
     * array_int 参数强制转换为整形
     * @param  string|array $array  要强制转换的参数是字符串1,2,3还是数组[1,2,3]
     * @param  bool         $is_ary 返回是字符串1,2,3还是数组[1,2,3]
     * @return string|array
     */
    public static function array_int($ids, $is_ary = false)
    {
        if (empty($ids)) {
            return $is_ary ? [] : '';
        }
        if (is_array($ids)) {
            $ids_ary = $ids;
        } else {
            $ids_ary = explode(',', trim((string)$ids,','));
        }
        $id_array = [];
        foreach ($ids_ary as $key => $value) {
            $id_array[$key] = intval($value);
        }
        return $is_ary ? $id_array : implode(',', $id_array);
    }
}