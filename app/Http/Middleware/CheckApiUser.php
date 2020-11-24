<?php

namespace App\Http\Middleware;

use App\Api\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Redis;

/**
 * Class CheckApiUser
 *
 * @package App\Http\Middleware
 */
class CheckApiUser
{
    use ApiResponse;

    /**
     * @param          $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $redis = Redis::connection();
        $user = $request->user();
        $redis->incr('apiAuth:user:' . $user->id . ':allNum');

        if (!$request->filled(['token']))
            return $this->message('lack of parameter', false);

        if (!in_array('uWei', $user->s_token) && !in_array($request->input('token'), $user->s_token))
            return $this->message('permission denied', false);

        if ($user->status == 0)
            return $this->message('this user has been closed', false);

        if (date('Y-m-d H:i:s') > $user->expires_at)
            return $this->message('this user has expired', false);

        return $next($request);

    }
}
