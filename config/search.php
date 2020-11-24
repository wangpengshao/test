<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Connection Host
    |--------------------------------------------------------------------------
    |
    | Set up one or more host connections
    |
    */

    'hosts' => [
        env('ELASTICSEARCH_HOST', 'http://localhost:9200')
    ],

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Connection Pool
    |--------------------------------------------------------------------------
    |
    | Choose the following
    |
    | Elasticsearch\ConnectionPool\StaticNoPingConnectionPool::class
    | Elasticsearch\ConnectionPool\SimpleConnectionPool::class
    | Elasticsearch\ConnectionPool\SniffingConnectionPool::class
    | Elasticsearch\ConnectionPool\StaticConnectionPool::class
    |
    */

    'connection_pool' => Elasticsearch\ConnectionPool\StaticNoPingConnectionPool::class,

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Connection Pool
    |--------------------------------------------------------------------------
    |
    | Setting the Connection Selector
    |
    | Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector::class
    | Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector::class
    | Elasticsearch\ConnectionPool\Selectors\RandomSelector::class
    |
    */

    'selector' => Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector::class,

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch log path
    |--------------------------------------------------------------------------
    |
    | Set whether the log to open the record
    |
    */

    'open_log' => false,

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch log path
    |--------------------------------------------------------------------------
    |
    | Setting the Connection Selector
    |
    | Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector::class
    | Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector::class
    | Elasticsearch\ConnectionPool\Selectors\RandomSelector::class
    |
    */

    'log_path' => storage_path('logs/elasticsearch.log'),

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch 索引别名
    |--------------------------------------------------------------------------
    |
    */
    'aliases' => [
        // 微门户菜单点击统计  索引格式  wechat_event_* => wechat_event_年月
        'click' => 'wechat_click_record',
        // 微门户微信事件统计  索引格式 wechat_event_* => wechat_event_年月
        'event' => 'wechat_event_record',
        // 微门户关键字回复  索引格式 wechat_keyword  单索引
        'keyword' => 'wechat_keyword',
        // 微门户超期推送记录  索引格式 exp_notice_log_*  => exp_notice_log_年月
        'exp_notice_log' => 'expire_notice_record',
    ]

];
