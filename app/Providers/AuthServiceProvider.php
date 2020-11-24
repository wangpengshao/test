<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**admin_toastr
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //个人访问客户端 过期时间
        Passport::personalAccessTokensExpireIn(now()->addSecond(config('envCommon.ACCESSTOKEN_SECOND')));
        //个人访问客户端 ID
        Passport::personalAccessClientId(config('envCommon.PERSONAL_AC_CLIENT_ID'));

        Passport::tokensExpireIn(now()->addSecond(config('envCommon.ACCESSTOKEN_SECOND')));
        Passport::refreshTokensExpireIn(now()->addSecond(config('envCommon.RE_ACCESSTOKEN_SECOND')));

        Passport::tokensCan([
            'opBase' => 'Authorize Api',
            'opInfo' => 'Authorize Api',
            'opGetRe' => 'Authorize Api',
            'reGetOp' => 'Authorize Api',
//            'bindRe' => 'Authorize Api',
//            'unbindRe' => 'Authorize Api',
            'sendTemplate' => 'Authorize Api',
            'sendTemplateForOpenid' => 'Authorize Api',
            'sendMessage' => 'Authorize Api',
            'groupSMes' => 'Authorize Api',
            'sendImgMes' => 'Authorize Api',
            'groupSImgMes' => 'Authorize Api',
            'fansInfo' => 'Authorize Api',
//            'getJdk' => 'Authorize Api',
            'getAcToken' => 'Authorize Api',
            'getJsSdk' => 'Authorize Api',
            'getActUser' => 'Authorize Api',
        ]);

//        Passport::routes(function (RouteRegistrar $router) {
//            //对于密码授权的方式只要这几个路由就可以了
//            $router->forAccessTokens();
//        });
        Route::group(['middleware' => 'oauth.providers'], function () {
            Passport::routes(function ($router) {
                Route::post('/token', [
                    'uses' => 'AccessTokenController@issueToken',
                    'as' => 'passport.token',
//                    'middleware' => 'throttle',
                ]);
//                return $router->forAccessTokens();
            });

        });

//        Auth::provider('custom', function () {
//            return new CustomUserProvider();
//        });

    }
}
