<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 系统配置表
 */
namespace base\model;
use base\BaseModel;

class SystemConfig extends BaseModel{

    protected $json = ['config'];
    protected $jsonAssoc = true;
    protected $autoWriteTimestamp = true;

    /**
     * 获取配置参数
     * @param  array 数据
     */
    public static function configs(string $title){
        $rel = self::apps()->where(['title' => $title])->find();
        if(empty($rel)){
            return false;
        }
        return $rel->config;
    }

    //编辑和创建
    public static function edit($title,array $config){
        $rel = self::where(['title' => $title])->find();
        if(empty($rel)){
            return self::create(['config' => $config,'title' => $title]);
        }else{
            $rel->config = $config;
            return $rel->save();
        }
    }
}