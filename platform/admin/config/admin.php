<?php
/**
 * 管理端管理菜单
 */
return [
    [
        'name' => '租户管理',
        'icon' => 'inboxes',
        'menu' => [
            ['name' => '租户列表', 'url' => (string)url('admin/tenant/index')],
            ['name' => '租户应用', 'url' => (string)url('admin/apps/index')],
        ]
    ],
    [
        'name' => '控制面板',
        'icon' => 'x-diamond',
        'menu' => [
            ['name' => '应用商店', 'url' => (string)url('admin/app/index')],
            ['name' => '应用扩展', 'url' => (string)url('admin/plugin/index')],
            ['name' => '代理管理', 'url' => (string)url('admin/agent/index')],
            ['name' => '帐号管理', 'url' => (string)url('admin/admin/index')],
        ]
    ],
    [
        'name' => '系统管理',
        'icon' => 'info-square',
        'menu' => [
            ['name' => '关于应用', 'url' => (string)url('admin/license/index')],
        ]
    ]
];
