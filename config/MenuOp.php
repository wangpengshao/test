<?php

return [

    'enType' => [
        '0' => '明文',
        '1' => '1.0',
        '2' => '2.0(借阅宝)',
        '3' => 'md5(小写)',
        '4' => 'md5(大写)',
        '5' => 'sha256(小写)'
    ],

    'extraOptions' => [
        '1' => '非本系统数据',
        'index_url' => '链接: 服务大厅',
        'bind_url' => '链接: 读者绑定',
        'token' => '参数: token',
        'opacurl' => '链接: opac地址',
        'opackey' => '参数: opac key',
        'activity_url' => '链接: 活动地址',
        'appid' => '参数: 公众号appid',
        'headerpic' => '参数: 公众号头像',
        'type' => '参数: 公众号类型',
    ],

    'fansOptions' => [
        'nickname' => '微信昵称',
        'openid' => 'openid',
        'subscribe' => '关注状态',
        'sex' => '性别',
        'headimgurl' => '微信头像'
    ]
];
