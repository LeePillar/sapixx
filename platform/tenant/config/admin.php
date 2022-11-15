<?php
/**
 * 管理端管理菜单
 */
return [
    [
        'name' => '关于应用',
        'icon' => 'info-square',
        'menu' => [
            ['name' =>'权限规则','url'=> (string)url('acl/index')]
        ]
    ] 
];