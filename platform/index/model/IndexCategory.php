<?php
/**
 * @copyright Copyright (c) 2017-2030 SAPI++ All rights reserved.
 * @license https://www.sapixx.com/license.php
 * @link https://www.sapixx.com
 * @todo 产品分类
 */
namespace platform\index\model;
use base\BaseModel;
use tree\Tree;

class IndexCategory extends BaseModel{

    //添加或编辑
    public static function edit($param){
        $data['title']       = $param['title'];
        $data['alias_title'] = $param['alias_title'];
        $data['logo']        = $param['logo']?:null;
        if(empty($param['id'])){
            return self::create($data);
        }else{
            return self::where(['id'=>(int)$param['id']])->update($data);
        }
    } 

}