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
            ['name' => '应用中心', 'url' => (string)url('admin/app/index')],
            ['name' => '代理管理', 'url' => (string)url('admin/agent/index')],
            ['name' => '帐号管理', 'url' => (string)url('admin/admin/index')],
        ]
    ]
];
