<?php

namespace App\Providers;

//use App\Models\Wechat\Fans;
//use App\Observers\FansObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Encore\Admin\Config\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Blade::withoutDoubleEncoding();

//        Config::load();

        Carbon::setLocale('zh');

//        Fans::observe(FansObserver::class);

        $callback = function () {
            return false;
        };

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
