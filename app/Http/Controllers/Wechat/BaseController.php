<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\Wxuser;
use EasyWeChat\Factory;
use Symfony\Component\Cache\Simple\RedisCache;

/**
 * Class BaseController
 *
 * @package App\Http\Controllers\Wechat
 */
class BaseController extends Controller
{
    /**
     * @var \Illuminate\Routing\Route|object|string
     */
    public $token;
    /**
     * @var Application
     */
    public $app;

    /**
     * @var array
     */
    public $option;


    /**
     * BaseController constructor.
     *
     * @param null $token
     */
    public function __construct($token = null)
    {
        $this->token = ($token) ?: \request()->route('token');
        if ($this->token) {
            $this->initOption($this->token);
            $this->initApplication();
        }
    }

    /**
     * @param $token
     */
    public function initOption($token)
    {
        $config = Wxuser::getConfig($token);
        $config['response_type'] = 'array';
        $config['log'] = [
            'default' => 'prod', // 默认使用的 channel，生产环境可以改为下面的 prod
            'channels' => [
                // 测试环境
                'dev' => [
                    'driver' => 'single',
                    'path' => storage_path('logs/wechat.log'),
                    'level' => 'debug',
                ],
                // 生产环境
                'prod' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/wechat.log'),
                    'level' => 'info',
                ],
            ]
        ];
        $config['http'] = [
            'retries' => 1,
            'retry_delay' => 500,
            'timeout' => 5.0,
            // 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
        ];
        $config['oauth'] = [
            'scopes' => ['snsapi_userinfo'],
            'callback' => '/examples/oauth_callback.php',
        ];
        $this->option = $config;
    }

    /**
     *
     */
    protected function initApplication()
    {
        $this->app = Factory::officialAccount($this->option);
        $predis = app('redis')->connection()->client(); // connection($name), $name 默认为 `default`
        $cache = new RedisCache($predis);
//        $this->app['cache'] = $cache;
        $this->app->rebind('cache', $cache);
    }

    /**
     * @param $option
     */
    protected function setOptions($option)
    {
        $this->option = $option;
        $this->initApplication();
    }

    /**
     * @param $option
     */
    protected function setOption($option)
    {
        array_merge($this->option, $option);
        $this->initApplication();
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function __get($value)
    {
        return $this->app->$value;
    }
}
