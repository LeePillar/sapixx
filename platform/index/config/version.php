<?php
return [
    //应用名称
    'name'               => 'SAPI++应用引擎',
    //应用描述
    'about'              => "撒皮|SAPI++SaaS应用引擎",
    //版本号
    'var'                => '1.0.0',
    //是否微信开发平台应用 
    'is_open_wechat'     => false,
    //is_open_wechat设置为true 必须设置本项未开放平台平台模板ID
    'open_wechat_tpl_id' => 1,
    //是否有管理端
    'is_admin'           => true,
    //微信支付
    'is_wechatpay'       => false,
    //支付宝
    'is_alipay'          => false,
    //应用类型(web|app|wechatmp|wechatapp|alipayapp|douyinapp)
    'types'              => 'web'
];