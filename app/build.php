<?php
return [
    // 需要自动创建的目录
    '__dir__' => [
        'api',
        'web',
        'web/web',
        'web/view',
        'api/service',
        'api/v1_0',
        'config',
        'controller',
        'model',
        'package',
        'package/database',
        'package/static',
        'validate',
        'view'
    ],
    // 需要自动创建的文件
    '__file__' => [
        'config/admin.php',
        'config/tenant.php',
        'config/version.php',
        'api/service/Index.php',
        'api/v1_0/Index.php',
        'package/database/install.sql',
        'package/database/uninstall.sql'
    ],
    // 需要自动创建的控制器
    'controller' => ['Admin','Tenant'],
    // 需要自动创建的模型
    'model'      => ['NameIndex'],
    // 需要自动创建的模板
    'view'       => ['admin/index','tenant/index'],
];