<?php
/**
 * 租户端管理菜单
 */
return [
    [
        'name' => '扩展中心',
        'icon' => 'x-diamond',
        'menu' => [
            ['name' =>'我的扩展','url'=> (string)url('plugin/index')],
            ['name' =>'扩展商店','url'=> (string)url('plugin/store')],
        ]
    ] 
];