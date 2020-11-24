<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL') . '/uploads',
            'visibility' => 'public',
        ],


        'admin' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL') . '/uploads',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        'qiniu' => [
            'driver' => 'qiniu',
            'domains' => [
                'default' => 'xxxxx.com1.z0.glb.clouddn.com', //你的七牛域名
                'https' => 'dn-yourdomain.qbox.me',         //你的HTTPS域名
                'custom' => 'static.abc.com',                //你的自定义域名
            ],
            'access_key' => '',  //AccessKey
            'secret_key' => '',  //SecretKey
            'bucket' => '',  //Bucket名字
            'notify_url' => '',  //持久化处理回调地址
            'url' => 'http://of8kfibjo.bkt.clouddn.com/',  // 填写文件访问根url
        ],

        'oss' => [
            'driver' => env('ALI_OSS_DRIVER'),
            'access_id' => env('ALI_OSS_ACCESS_ID'),
            'access_key' => env('ALI_OSS_ACCESS_KEY'),
            'bucket' => env('ALI_OSS_BUCKET'),
            'endpoint' => env('ALI_OSS_ENDPOINT'), // OSS 外网节点或自定义外部域名
//            'endpoint_internal' => 'http://oss-cn-shenzhen.aliyuncs.com', // v2.0.4 新增配置属性，如果为空，则默认使用 endpoint 配置(由于内网上传有点小问题未解决，请大家暂时不要使用内网节点上传，正在与阿里技术沟通中)
//                'cdnDomain'     => '<CDN domain, cdn域名>', // 如果isCName为true, getUrl会判断cdnDomain是否设定来决定返回的url，如果cdnDomain未设置，则使用endpoint来生成url，否则使用cdn
//                'ssl'           => <true|false> // true to use 'https://' and false to use 'http://'. default is false,
//            'isCName'       => <true|false> // 是否使用自定义域名,true: 则Storage.url()会使用自定义的cdn或域名生成文件url， false: 则使用外部节点生成url
            'ssl' => env('ALI_OSS_SSL'), // true to use 'https://' and false to use 'http://'. default is false,
            'isCName' => env('ALI_OSS_ISCNAME'), // 是否使用自定义域名,true: 则Storage.url()会使用自定义的cdn或域名生成文件url， false: 则使用外部节点生成url
            'debug' => env('ALI_OSS_DEBUG')
        ],

    ],

];
