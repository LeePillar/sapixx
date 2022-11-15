<?php
/**
 * 租户端管理菜单
 */
return [
    [
        'name' => '应用开通',
        'icon' => 'info-square',
        'menu' => [
            ['name' =>'应用商店','url'=> (string)url('store/index')],
            ['name' =>'帐号充值','url'=> (string)url('account/recharge')],
            ['name' =>'我的帐号','url'=> (string)url('account/index')],
        ]
    ]
];