<?php
return [
    //应用名称
    'name'               => '撒皮|SAPI++ @公共API',
    //应用描述
    'about'              => "撒皮(SAPI++)公共API,是SAPI++为第三方应用定制的常用API,避免二次重复开发.",
    //版本号
    'var'                => '1.0.0',
    //是否微信开发平台应用 
    'is_open_wechat'     => false,
    //is_open_wechat设置为true 必须设置本项未开放平台平台模板ID
    'open_wechat_tpl_id' => 0,
    //是否有管理端
    'is_admin'           => false,
    //微信支付
    'is_wechatpay'       => true,
    //支付宝
    'is_alipay'          => false,
    //应用类型(web|app|wechatmp|wechatapp|alipayapp|douyinapp)
    'types'              => 'web|app|wechatmp|wechatapp|alipayapp|douyinapp'
];