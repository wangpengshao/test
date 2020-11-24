<?php

namespace App\Listeners;

use App\Services\ApiLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Events\AccessTokenCreated;

class RevokeOldTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function handle(AccessTokenCreated $event)
    {
        if ($event->clientId != 8) {
            $apiLogService = new ApiLogService();
            $apiLogService->recordGetToken(json_decode(json_encode($event), true));
//            DB::table('oauth_access_tokens')->where('id', '!=', $event->tokenId)
//                ->where('user_id', $event->userId)
//                ->where('client_id', $event->clientId)
//                ->where('created_at', '<', Carbon::now()->toDateString())
//                ->where('revoked', 0)
//                ->delete();
        }

    }
}
