<?php
//中间件配置
return [
    'alias' => [
        'admin'   => base\middleware\Admin::class, //超管中间件
        'tenant'  => base\middleware\Tenant::class, //租户中间件
        'web'     => base\middleware\Web::class, //WEB/H5中间件
        'wechat'  => base\middleware\Wechat::class, //公众号中间件
        'api'     => base\middleware\Api::class, //Api中间件
        'acl'     => base\middleware\Acl::class //API控制器中间件
    ],
    //优先级设置
    'priority' => [
        think\middleware\SessionInit::class,
        base\constant\InitCross::class
    ]
];
