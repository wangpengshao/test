<?php

namespace App\Http\Middleware;

use Closure;
//use Illuminate\Support\Facades\App;

class FrameHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        header_remove('X-Frame-Options');
        $response->header('X-Frame-Options', 'ALLOW FROM http://u.interlib.cn/index.php?g=Mysql&m=Mysql&a=test');
        return $response;
    }
}
