<?php
/**
 * @copyright Copyright (c) 2017-2030 SAPI++ All rights reserved.
 * @license   Licensed (http://www.apache.org/licenses/LICENSE-2.0).
 * @author    pillar<ltmn@qq.com>
 * @todo 商品分类
 */
namespace platform\index\validate;
use think\Validate;

class Category extends Validate{

    protected $rule = [
        'id'          => 'require|integer',
        'sort'        => 'require|integer|egt:0|elt:100',
        'parent_id'   => 'require|integer',
        'title'       => 'require'
    ];

    protected $message = [
        'id'          => 'ID丢失',
        'sort'        => '顺序只能填写数字,切禁止超过100',
        'title'       => '名称必须填写',
        'alias_title' => '别名必须填写'
    ];

    protected $scene = [
        'sort' => ['id','sort'],
        'save' => ['id','title'],
        'edit' => ['id','title','alias_title'],
    ];
}