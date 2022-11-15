<?php
return [
    //应用名称
    'name'               => '开发实例',
    //应用描述(40个字以内)
    'about'              => "撒皮|Sapi++是SaaS应用开发框架,基于TP6开发,开发实例是帮助你快速入门开发",
    //版本号
    'var'                => '1.0.0',
    //是否微信开发平台应用 
    'is_open_wechat'     => false,
    //is_open_wechat设置为true 必须设置本项未开放平台平台模板ID
    'open_wechat_tpl_id' => 1,
    //是否有管理端
    'is_admin'           => true,
    //微信支付
    'is_wechatpay'       => true,
    //支付宝
    'is_alipay'          => false,
    //应用类型(web|app|wechatmp|wechatapp|alipayapp|douyinapp)
    'types'              => 'web|wechatmp|wechatapp'
];