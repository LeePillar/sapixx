<?php
/**
 * 管理端管理菜单
 */
return [
    [
        'name' => '商城管理',
        'children'  => [
            ['name' => '商品管理','children'  => [
                ['name' => '商品列表','rule' => "shop.index"],
                ['name' => '增加修改','rule' => "shop.edit|shop.add"],
                ['name' => '产品上下架','rule' => "shop.onsale"]
            ]],
            ['name' => '栏目管理','children'  => [
                ['name' => '栏目列表','rule' => "cate.index"],
                ['name' => '增加编辑','rule' => "cate.add|cate.edit"],
                ['name' => '删除','rule' => "cate.delete"],
            ]],   
            ['name' => '订单管理','children'  => [
                ['name' => '订单列表','rule' => "order.index"],
                ['name' => '订单售后','rule' => "order.service"]
            ]]
        ],
    ],
    [
        'name' => '财务管理',
        'children'  => [
            ['name' => '财务统计','rule' => "bank.index"],
            ['name' => '帐号余额','rule' => "bank.account"]
        ]
    ],
];