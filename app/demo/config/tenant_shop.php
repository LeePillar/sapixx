<?php
/**
 * 管理端管理菜单
 */
return [
    [
        'name' => '商品管理',
        'icon' => 'code-square',
        'menu' => [
            ['name' => '产品列表', 'url' => (string)url('demo/shop/index')],
            ['name' => '类目管理', 'url' => (string)url('demo/cate/index')],
        ]
    ],
    [
        'name' => '订单管理',
        'icon' => 'code-square',
        'menu' => [
            ['name' => '订单列表', 'url' => (string)url('demo/order/index')],
            ['name' => '订单售后', 'url' => (string)url('demo/order/service')],
        ]
    ],
    [
        'name' => '财务管理',
        'icon' => 'code-square',
        'menu' => [
            ['name' => '财务管理', 'url' => (string)url('demo/bank/index')],
            ['name' => '帐号余额', 'url' => (string)url('demo/bank/account')],
        ]
    ]
];
