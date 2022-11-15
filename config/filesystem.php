<?php
// +----------------------------------------------------------------------
// | 文件上传配置
// +----------------------------------------------------------------------
return [
    // 默认磁盘
    'default'  => env('filesystem.driver','public'), //public默认储存方式
    'filesize' => '10', //MB
    'fileext'  => 'jpeg,jpg,gif,bmp,png,pem,doc,docx,xls,xlsx,ppt,pptx,pdf,mp4,m4a,mp3,aac', //文件类型
    'filemime' => 'image/jpg,image/jpeg,image/png,image/gif,audio/mpeg,image/webp,text/plain,application/octet-stream,video/mp4,audio/mpeg,audio/midi,audio/mp4a,audio/x-m4a,audio/ogg,video/webm,audio/aac,audio/x-aac,audio/wav,audio/x-wav,audio/aiff,application/pdf,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation',
    // 磁盘列表
    'disks'   => [

        //安全路径
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],

        //本地储存,不建议修改
        'public' => [
            'type'       => 'local',
            'root'       => app()->getRootPath() . 'public/res',
            'url'        => '/res',     //不要斜杠结尾，此处为URL地址域名。
            'visibility' => 'public',
        ],

        //阿里云
        'aliyun' => [
            'type'         => 'aliyun',
            'accessId'     => '',
            'accessSecret' => '',
            'bucket'       => '',
            'endpoint'     => '',
            'url'          => '',//不要斜杠结尾，此处为URL地址域名。
        ],

        //七牛
        'qiniu'  => [
            'type'      => 'qiniu',
            'accessKey' => '',
            'secretKey' => '',
            'bucket'    => '',
            'url'       => '',//不要斜杠结尾，此处为URL地址域名。
        ],

        //腾讯云
        'qcloud' => [
            'type'      => 'qcloud',
            'region'    => '', //bucket 所属区域 英文
            'appId'     => '', //域名中数字部分
            'secretId'  => '',
            'secretKey' => '',
            'bucket'    => '',
            'domain'    => '', //域名,不要增加http协议
            'url'       => '',  //CDN加速域名
        ]
    ],
];