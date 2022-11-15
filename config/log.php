<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
return [
    // 默认日志记录通道
    'default'      => env('log.channel','file'),
    // 日志记录级别
    'level'        => [],
    // 日志类型记录的通道
    'type_channel' => ['sql' =>'sql','error' => 'error'],
    // 关闭全局日志写入
    'close'        => true,
    // 全局日志处理 支持闭包
    'processor'    => null,
    // 日志通道列表
    'channels'     => [
        'file' => [
            'type' => 'File', // 日志记录方式
            'path' => app()->getRuntimePath().'log'.DIRECTORY_SEPARATOR.'file', // 日志保存目录
        ],
        'sql' => [
            'type' => 'File',
            'path' => app()->getRuntimePath().'log'.DIRECTORY_SEPARATOR.'sql',
        ],
        'error' => [
            'type' => 'File',
            'path' => app()->getRuntimePath().'log'.DIRECTORY_SEPARATOR.'error',
        ],
    ],

];
