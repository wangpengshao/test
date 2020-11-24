<?php

namespace App\Http\Middleware;

use App\Api\Helpers\ApiResponse;
use Closure;

class CheckFansapiSign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    use ApiResponse;

    public function handle($request, Closure $next)
    {
//        if ( time() - $request->time > 120 ) {
//            return $this->failed('Time invalid.', 401);
//        }

        if (!$request->sign || !$request->openid || !$request->time || !$request->token) {
            return $this->failed('Lack of parameter.', 401);
        }
        /* md5 openid + time + keyStr */
        if (md5($request->openid . $request->time . config('envCommon.ENCRYPT_STR')) != $request->sign) {
            return $this->failed('Signature error.', 401);
        }
        return $next($request);
    }
}
