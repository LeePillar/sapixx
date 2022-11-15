<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型使用Think
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'     => 1,
    // 模板目录名
    'view_dir_name' => 'view',
    // 模板后缀
    'view_suffix'   => 'html',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
    //是否去除模板文件里面的html空格与换行
    'strip_space'   => true,
    //编译缓存
    'tpl_cache'     => true,
    //模板缓存有效期 0 为永久
    'cache_time'    => 0,
    //模板标签过滤函数
    'default_filter' => 'htmlentities_view',
    //扩展模板标签库
    'taglib_pre_load' =>  'base\taglib\Tag', 
    // 静态资源
    'tpl_replace_string'  =>  [
        '__STATIC__'  => '/static',
        '__RES__'     => '/res',
        '__PUBLIC__'  => '/common',
    ]
];
