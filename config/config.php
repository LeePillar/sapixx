<?php
return [
    /**
     * 站点信息根据需要自行扩展
     */
    'site' => [
        'title'    => '撒皮|Sapi++', //站点名称
        'subtitle' => '简约的SaaS应用管理引擎', //站点副标题
        'logo'     => '/common/img/logo.png', //站点Logo,基于public目录
        'url'      => 'https://www.sapixx.com', //网址
        'copyright'=> '© 2017-2022 布朗斯通', //版权声明
        'safe_ip'  => '127.0.0.1',  ///安全IP,设置后只有指定IP可以进入后台（无授权的不提示默认跳转的首页）
        'icp'      => '豫ICP备00000000号', //站点ICP备案号
    ],
    
    /**
     * 微信开发平台
     * 授权事件接收   https://www.*.com/apis/service/message/ticket
     * 消息与事件接收 https://www.*.com/apis/service/message/$APPID$
     */
    'wechat_open' => [
        'app_id'  => '', //开发平台APPID
        'secret'  => '',
        'token'   => '',    //32位长度
        'aes_key' => '',  //消息加解密Key43位长度
        'domain'  =>[ //设置服务器（这里的域名必须和开发平台的设置一样）
            "requestdomain"   => ["https://www.sapixx.com","https://res.sapixx.com"],//协议 https://www.qq.com
            "wsrequestdomain" => ["wss://www.sapixx.com","wss://res.sapixx.com"],    //协议 wss://www.qq.com
            "uploaddomain"    => ["https:/www.sapixx.com","https://res.sapixx.com"], //协议 https://www.qq.com
            "downloaddomain"  => ["https://www.sapixx.com","https://res.sapixx.com"], //协议 https://www.qq.com
            "udpdomain"       => ["udp://www.sapixx.com"],
            "tcpdomain"       => ["tcp://www.sapixx.com"],   //协议 tcp://www.qq.com
        ],
        'webview'  =>["https://www.sapixx.com"]  //协议 https://www.qq.com
    ],

    /**
     * 微信服务商
     * 证书根路径 runtime/storage
     */
    'wechat_sp' => [
        'appid'          => '',  //发起微信支付的APPID
        'mchid'          => '',  //收款商户号
        'secret'         => '',  //API密钥
        'cert'           => '/storage/sapixx/apiclient_cert.pem',  //支付证书,"/" 开始
        'certkey'        => '/storage/sapixx/apiclient_key.pem',  //证书密钥,"/" 开始
        'secretv3'       => '',  //APIv3密钥
        'serial_no'      => '',  //APIv3证书序列号
        'platform_certs' => '',  //APIv3平台证书
    ],

    /**
     * 
     * 微信支付商户号
     * 证书根路径 runtime/storage
     */
    'wechat_pay' => [
        'appid'          => '',  //发起微信支付的APPID
        'mchid'          => '',  //收款商户号
        'secret'         => '',  //API密钥
        'cert'           => '/sapixx/apiclient_cert.pem',  //支付证书,"/" 开始
        'certkey'        => '/sapixx/apiclient_key.pem',  //证书密钥,"/" 开始
        'secretv3'       => '',  //APIv3密钥
        'serial_no'      => '',  //APIv3证书序列号
        'platform_certs' => '',  //APIv3平台证书,"/" 开始
    ],

    /**
     * 管理端认证服务号（需要在安全模式下）
     * 用户扫码关注并登录租户端(限服务号)
     * 消息与事件接收 https://*.com/apis/service/message/$APPID$
     */
    'wechat_account' => [
        'account' => false,//是否开启扫码关注登录
        'text'    => '欢迎使用『SAPI++』SaaS引擎', //扫码登录/绑定后默认回复文本
        'appid'   => '', //APPID
        'secret'  => '',
        'token'   => '',  //如不填写则默认为“TOKEN”
        'aes_key' => '', 
    ],

   /**
     * QQ地图定位服务
     */
    'wechat_lbs' => [
        'key' => '',
    ],

    /**
     * 阿里云市场接口服务
     */
    'aliyun_market_api' => [
        'appkey' => '',
        'appsecret' => '',
        'appcode' => '',
        'price'    => 0.45  //应用调用扣除价格
    ],

    /**
     * API接入服务
     * topthink.com API市场https://market.topthink.com/api
     */
    'topthink_api' => [
        'appcode'    => '',
        'signId'     => 154,   //签名id，在我的服务->短信服务->签名管理里面查看
        'templateId' => 2,     //模板id，在我的服务->短信服务->模板管理里面查看
        'price'      => 0.05,  //每次请求扣除租户多少钱(最小到分)
    ]
];
