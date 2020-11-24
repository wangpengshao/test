<?php

namespace App\Http\Middleware;

use App\Api\Helpers\ApiResponse;
use App\Models\specialColumn\RedGevemment;
use Closure;

class CheckRedGevemmentToken
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $token = $request->input('token');
        if (empty($token) || strlen($token) != 15 || substr($token, 0, 3) != 'RES') {
            return $this->setTypeCode('404')->message('无效Token','error');
        }
        $cache = null;
        $cache = RedGevemment::getCache($token);
        if ($cache == false) {
            return $this->setTypeCode('404')->message('无效Token','error');
        }

        if($cache['status'] != 1){
            return $this->failed('资源未开放！');
        }

        $now = date('Y-m-d H:i:s');
        if($cache['date_end'] < $now){
            return $this->failed('资源授权已到期！');
        }

        return $next($request);
    }
}
