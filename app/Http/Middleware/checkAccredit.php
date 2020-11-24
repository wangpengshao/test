<?php

namespace App\Http\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Middleware\Pjax;

/**
 * Class checkAccredit
 * @package App\Http\Middleware
 */
class checkAccredit
{

    /**
     * @param         $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->session()->has('wxtoken')) {
//            trans('admin.deny')
            $url = route('wxuser.index');
            $messages = "尚未授权公众号， <a href='{$url}'>点击这里</a> 可进入公众号列表进行授权操作";
            $response = response(Admin::content()->withError('无权访问', $messages));
            Pjax::respond($response);
        }
        return $next($request);
    }
}
