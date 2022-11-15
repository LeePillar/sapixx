<?php
/**
 * 租户端管理菜单
 */
return [
    [
        'name' => '关于应用',
        'icon' => 'info-square',
        'menu' => [
            ['name' =>'帐号权限','url'=> (string)url('acl/index')],
            ['name' =>'会员管理','url'=> (string)url('user/index')],
            ['name' =>'关于应用','url'=> (string)url('apps/index')],
        ]
    ] 
];