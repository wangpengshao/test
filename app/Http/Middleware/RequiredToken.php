<?php

namespace App\Http\Middleware;

use App\Models\Wxuser;
use Closure;

class RequiredToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->route('token') ?: $request->input('token');
        $wxuser = null;
        if ($token) {
            $wxuser = Wxuser::getCache($token);
        };
        if (empty($wxuser)) {
            abort(404);
        }
        return $next($request);
    }
}
