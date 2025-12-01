<?php

return [
    // +----------------------------------------------------------------------
    // | 微信配置
    // +----------------------------------------------------------------------
    'wxmp' => [
        'app_id' => '',
        'secret' => '',

        'oauth' => [
            'scopes'   => ['snsapi_base'],
            'callback' => '/wxmp/wx/oauthcb',
        ],

        'log' => [
            'default' => 'dev', // 默认使用的 channel，生产环境可以改为下面的 prod
            'channels' => [
                // 测试环境
                'dev' => [
                    'driver' => 'single',
                    'path' => '/tmp/easywechat.log',
                    'level' => 'debug',
                ],
                // 生产环境
                'prod' => [
                    'driver' => 'daily',
                    'path' => '/tmp/easywechat.log',
                    'level' => 'info',
                ],
            ],
        ],
    ],


    // +----------------------------------------------------------------------
    // | 短信配置
    // +----------------------------------------------------------------------
    'easysms' => [
        'timeout'  => 3,
        'default'  => [
            'gateways' => [
                'aliyun',
            ],
        ],
        'gateways' => [
            'aliyun' => [
                'access_key_id'     => '',
                'access_key_secret' => '',
                'sign_name'         => '',
            ],
        ],
    ],
];
