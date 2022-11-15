<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 访问控制 
 */
namespace base\model;
use base\BaseModel;

class SystemTenantGroup extends BaseModel{

    protected $json      = ['rules','menu'];
    protected $jsonAssoc = true;

    //规则值
    public function getRulesTextAttr($value,$data,)
    {
        return implode('|',$data['rules']??[]);
    }

    //菜单组
    public function getMenuTextAttr($value,$data,)
    {
        return implode('|',$data['menu']??[]);
    }

    /**
     * 编辑修改
     * @param  array $param 
     */
    public static function edit(array $param){
        $data['title']         = $param['title'];
        $data['remark']        = $param['remark'];
        $data['rank']          = intval($param['rank']);
        $data['rank_text']     = $param['rank_text'];
        $data['rules']         = $param['rules'] ? explode('|',$param['rules']): [];
        $data['menu']          = $param['menu'] ? explode('|',$param['menu']): [];
        $data['update_time']   = time();
        if(isset($param['id']) && $param['id'] > 0){
            $data['id'] = $param['id'];
            return self::update($data);
        }else{
            $data['create_time'] = time();
            $data['apps_id']     = $param['apps_id'];
            return self::create($data);
        }
    }
}