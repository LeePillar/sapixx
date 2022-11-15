<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------
return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    //应用不存在的时候，直接访问默认应用的路由
    'app_express'     =>  false,
    // 默认应用
    'default_app'     => 'tenant',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],
    //AJAX默认类型
    'default_ajax_return'  => 'json',
    //默认返回类型
    'default_return_type'  => 'html',
    // 异常页面的模板文件
    'exception_tmpl'   => app()->getRootPath() . 'base/view/500.html',
    //跳转提示
    'return_tmpl'      => app()->getRootPath() . 'base/view/result.html',
    //提示并跳转
    'jump_tmpl'        => app()->getRootPath() . 'base/view/jump.html',
    //404错误模板
    'http_exception_template'    =>  [
        404 =>  app()->getRootPath() . 'base/view/404.html',
        403 =>  app()->getRootPath() . 'base/view/jump.html',
    ],
    // 显示错误信息
    'show_error_msg'   => true,
    // 错误显示信息,非调试模式有效
    'error_message'    => '未找到请求的URL地址',
];
