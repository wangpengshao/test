<?php

namespace App\Http\Middleware;

use App\Api\Helpers\ApiResponse;
use App\Models\Mini\Registration;
use Closure;
use Illuminate\Support\Str;

/**
 * Class CheckApiUser
 *
 * @package App\Http\Middleware
 */
class CheckCefMiniToken
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
        $token = $request->input('token');
        if (empty($token) || strlen($token) != 16 || substr($token, 0, 4) != 'MINI') {
            abort(404);
        }
        $cache = null;
        $cache = Registration::getCache($token);
        if ($cache == false) {
            abort(404);
        }

        if ($cache['status'] != 1) {
            return $this->setTypeCode(1001)->message('function has been shut down', false);
        }

        if ($cache['start_at'] > date('Y-m-d H:i:s') || $cache['end_at'] < date('Y-m-d H:i:s')) {
            return $this->setTypeCode(1002)->message('authorization expires', false);
        }

        return $next($request);
    }
}
