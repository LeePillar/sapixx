<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 扩展管理 
 */
namespace base\model;
use base\BaseModel;
use util\Ary;
use util\Dir;

class SystemPlugin extends BaseModel{
    
    //获取配置
    public function getConfigAttr($value,$data)
    {
        return app('configs')->plugin($data['appname']);
    }

    //应用状态
    public function getLockTextAttr($value,$data)
    {
        $status = [0 => '上架',1 => '下架'];
        return $status[$data['is_lock']];
    }

    //应用Logo
    public function getLogoAttr($value,$data)
    {
        if(empty($value)){
            if(file_exists(PATH_STATIC.'plugin'.DS.$data['appname'].DS.'logo.png')){
                return DAMAIN.'static/plugin/'.$data['appname'].'/logo.png';
            }else{
                return DAMAIN.'static/error.png';
            }
        }else{
            return $value;
        }
    }


    /**
     * 待安装APP列表
     * @param  array $param 
     */
    public static function offPlugin(){
        $install_app = self::column('appname');
        $install = array_values(Ary::array_remove_empty(array_diff(Dir::getDir(PATH_PLUGIN,FORBIDEN),$install_app)));
        $app = [];
        foreach ($install as $key => $value) {
            $config = app('configs')->plugin($value);
            if(!empty($config)){
                $app[$key] = $config;
                $app[$key]['appname'] = $value;
                $app[$key]['logo']    = DAMAIN.'static/offline.png';
            }
        }
        return $app;
    }

    /**
     * 管理小程序
     * @param  array $param 
     */
    public static function edit(array $param){
        $data['title']         = $param['title'];
        $data['logo']          = $param['logo']??'';
        $data['about']         = $param['about']??'';
        $data['price']         = $param['price']??0;
        $data['update_time']   = time();
        if(isset($param['id']) && $param['id'] > 0){
            $data['id'] = $param['id'];
            return self::update($data);
        }else{
            $data['appname']     = strtolower($param['appname']);
            $data['create_time'] = time();
            return self::create($data);
        }
    }
}