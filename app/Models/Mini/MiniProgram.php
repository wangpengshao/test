<?php

namespace App\Models\Mini;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use EasyWeChat\Factory;

/**
 * Class MiniProgram
 *
 * @package App\Models\Mini
 */
class MiniProgram
{
    /**
     * @param $app_id
     * @param $secret
     * @return \EasyWeChat\MiniProgram\Application
     */
    public static function initialize($app_id, $secret)
    {
        $config['app_id'] = $app_id;
        $config['secret'] = $secret;
        $config['response_type'] = 'array';
        /**
         * 日志配置
         *
         * level: 日志级别, 可选为：
         *         debug/info/notice/warning/error/critical/alert/emergency
         * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
        $config['log'] = [
            'default' => 'prod', // 默认使用的 channel，生产环境可以改为下面的 prod
            'channels' => [
                // 测试环境
                'dev' => [
                    'driver' => 'single',
                    'path' => storage_path('logs/mini/record.log'),
                    'level' => 'debug',
                ],
                // 生产环境
                'prod' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/mini/record.log'),
                    'level' => 'error',
                ],
            ]
        ];
        $config['http'] = [
            'retries' => 1,
            'retry_delay' => 500,
            'timeout' => 5.0,
            // 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
        ];

        $app = Factory::miniProgram($config);
        $predis = app('redis')->connection()->client();
        $cache = new RedisAdapter($predis);
        $app->rebind('cache', $cache);
        return $app;
    }

}
