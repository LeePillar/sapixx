<?php
/**
 * 租户端管理菜单
 * 仅支持单组菜单
 */
return [
    'name' => '扩展实例',      //组名称
    'icon' => 'house',  //组ICON
    'menu' => [
        ['name' =>'扩展开发','url'=> (string)plugurl('test/index/index')],
    ]
];