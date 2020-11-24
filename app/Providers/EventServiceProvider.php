<?php

namespace App\Providers;

//use App\Models\LoginHistory;
//use Illuminate\Auth\Events\Login;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
//        'Laravel\Passport\Events\AccessTokenCreated' => [
//            'App\Listeners\RevokeOldTokens',
//        ],
//
//        'Laravel\Passport\Events\RefreshTokenCreated' => [
//            'App\Listeners\PruneOldTokens',
//        ],

//        'Illuminate\Database\Events\QueryExecuted' => [
//            'App\Listeners\QueryListener',
//        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

//        Event::listen(Login::class, function ($_) {
//            LoginHistory::log(request()->getClientIp());
//        });
    }
}