<?php

namespace App\Models\Wechat;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use EasyWeChat\Factory;

/**
 * Class WechatPay
 * @package App\Models\Wechat
 */
class WechatPay
{

    /**
     * @param $token
     * @return \EasyWeChat\Payment\Application
     */
    public static function initialize($token)
    {
        $payConfig = PayConfig::getCache($token);
        $config = [
            'app_id' => $payConfig['app_id'],
            'mch_id' => $payConfig['mch_id'],
            'key' => $payConfig['key'],
            'cert_path' => public_path('uploads/' . $payConfig['cert_path']), // XXX: 绝对路径！！！！
            'key_path' => public_path('uploads/' . $payConfig['key_path']), // XXX: 绝对路径！！！！
            'notify_url' => route('wxPay_default', $token),     // 你也可以在下单时单独设置来想覆盖它
//            'sandbox'            => true, // 设置为 false 或注释则关闭沙箱模式
        ];

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
                    'path' => storage_path('logs/pay/wechatPay.log'),
                    'level' => 'debug',
                ],
                // 生产环境
                'prod' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/pay/wechatPay.log'),
                    'level' => 'error',
                ],
            ]
        ];

        $app = Factory::payment($config);
        $predis = app('redis')->connection()->client();
        $cache = new RedisAdapter($predis);
        $app->rebind('cache', $cache);
        return $app;
    }

}
