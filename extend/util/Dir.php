<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 常用目录与文件
 */
namespace util;

class Dir {

    /**
     * 检测目录权限
     * @param  [type] $path [当前判断目录]
     * @return [boolean]    [是否存在或有权限]
     */
    public static function isDirs(string $path)
    {
        if(!(is_dir($path) && is_writable($path))){
            return false;
        }
        return true;
    }    
 
    /**
     * 创建文件夹
     * @param string $path
     * @param int $mode
     */
    public static function mkDirs($path, $mode = 0777)
    {
        if(self::isDirs($path)){
            return true;
        }
        try {
            mkdir($path,$mode,true);
        }catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 创建文件
     * @param string $path
     * @param int $mode
     */
    public static function mkFiles($path,$filecontent)
    {
        try {
            $file = fopen($path,"w");
            fwrite($file,$filecontent);
            fclose($file);
            return true;
        }catch (\Exception $e) {
            return false;
        }
    }  


    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    public static function rmDirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)){
            return false;
        }
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirname, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }


    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    public static function copyDirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
            if ($item->isDir()) {
                $sontDir = $dest. DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }

    /**
     * 取得文件扩展名
     *
     * @param string $file 文件名
     * @return string
     */
    public static function getExt($file)
    {
        $file_info = pathinfo($file);
        return $file_info['extension'];
    }


    /**
     * @param $dir 路径
     * @param $except 排除项
     * @return array
     * 搜索给定地址下目录列表
     */
    public static function getDir($dir,$except = [])
    {
        $dirArray[] = NULL;
        if(false != ($handle = opendir($dir))){
            $i=0;
            while(false !== ($file = readdir($handle))) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if (array_search($file,$except) === false && $file != ".htaccess" && $file != "." && $file != ".."&&!strpos($file,".")){
                    $dirArray[$i]=$file;
                    $i++;
                }
            }
            //关闭句柄
            closedir($handle);
        }
        return $dirArray;
    }

    /**
     * 读写文件内容
     * @param $key
     * @param $data
     */
    public static function fileBody(string $path,string $body = '')
    {
        self::mkDirs(dirname($path));
        if(empty($body)){
            if (file_exists($path)){
                return file_get_contents($path);
            }
        }else{
            if (file_put_contents($path,$body)) {
                return file_get_contents($path);
            }
        }
        return false;
    }
}