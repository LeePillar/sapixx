<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户配置表
 */
namespace base\model;
use base\BaseModel;

class SystemAppsConfig extends BaseModel{

    protected $json      = ['config'];
    protected $jsonAssoc = true;

    /**
     * 发布状态
     */
    public function send()
    {
        return $this->hasOne('SystemAppsRelease','client_id');
    }

   /**
     * 获取配置参数
     * @param  array 数据
     */
    public static function configs(string $title,$is_config = false){
        $rel = self::apps()->where(['title' => $title])->find();
        if(empty($rel)){
            return false;
        }
        return $is_config ? $rel->config : $rel;
    }
}