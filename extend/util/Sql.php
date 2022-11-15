<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * SQL语句处理
 */
namespace util;

class Sql {
    
    /**
     * 数据库表前缀替换
     * @param string $file   SQL路径
     * @param string $prefix 替换的表前缀
     * @param string $defaultTablePre  原来的默认表前缀
     * @param string $defaultCharset   原来的默认数据库类型
     * @return void
     */
    public static function hcSplitSql($file,$prefix,$charset = 'utf8mb4', $defaultTablePre = '$prefix$', $defaultCharset = 'utf8mb4'){
        if (file_exists($file)) {
            //读取SQL文件
            $sql = file_get_contents($file);
            $sql = str_replace("\r", "\n", $sql);
            $sql = str_replace("BEGIN;\n", '', $sql);//兼容 navicat 导出的 insert 语句
            $sql = str_replace("COMMIT;\n", '', $sql);//兼容 navicat 导出的 insert 语句
            $sql = str_replace($defaultCharset, $charset, $sql);
            $sql = trim($sql);
            //替换表前缀
            $sql  = str_replace(" `{$defaultTablePre}", " `{$prefix}", $sql);
            $sqls = explode(";\n", $sql);
            return $sqls;
        }
        return [];
    }

    /**
     * 读取sql文件为数组
     * @param $sqlFile sql 文件路径
     * @param string $prefix 添加表前缀
     * @return array|bool
     */
    public static function sqlAarray($sqlFile,$prefix = ''){
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $str = preg_replace('/(--.*)|(\/\*(.|\s)*?\*\/)|(\n)/', '',$sql);
            if(!empty($prefix)){
                $sql = str_replace("\r", "\n", str_replace('ai_', $prefix, $str));
            }
            $list = explode(';',trim($str));
            foreach ($list as $key => $val) {
                if (empty($val)) {
                    unset($list[$key]);
                } else {
                    $list[$key] .= ';';
                }
            }
            return array_values($list);
        }
        return [];
    }
}