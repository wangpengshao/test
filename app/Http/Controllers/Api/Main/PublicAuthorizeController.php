<?php

namespace App\Http\Controllers\Api\Main;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\ApiLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * api 授权主要接口
 * Class PublicAuthorizeController
 * @package App\Http\Controllers\Api\Main
 */
class PublicAuthorizeController extends Controller
{
    use ApiResponse;

    /**
     * 对外接口授权
     * @param Request $request
     * @return mixed
     */
    public function userAuth(Request $request)
    {
        if (!$request->filled(['time', 'sign', 'username', 'secretKey']))
            return $this->message('lack of parameter', false);
        [
            'time' => $time,
            'username' => $username,
            'sign' => $sign,
            'secretKey' => $password,
        ] = $request->input();

        if (time() - $time > 600) return $this->message('invalid time', false);
        /* MD5 username & time & keyStr */
        if (md5(implode('&', [$username, $time, config('envCommon.ENCRYPT_STR')])) != $sign)
            return $this->message('invalid sign', false);

        if (!Auth::attempt(['username' => $username, 'password' => $password]))
            return $this->message('the user information error', false);

        $redis = Redis::connection();
        $user = $request->user();
        $redis->incr('apiAuth:user:' . $user->id . ':allNum');

        if ($user->status == 0)
            return $this->message('this user has been closed', false);
        if (date('Y-m-d H:i:s') > $user->expires_at)
            return $this->message('this user has expired', false);

        $name = 'public';
        DB::table('oauth_access_tokens')->where(['user_id' => $user->id, 'name' => $name])->delete();

        $scopes = explode(',', $user->scopes);
        $accessToken = $user->createToken($name, $scopes)->accessToken;
        $success = [
            'token_type' => 'Bearer',
            'expires_in' => (int)config('envCommon.ACCESSTOKEN_SECOND') - 100,
            'access_token' => $accessToken,
//            'refresh_token' => ''
        ];
        $apiLogService = new ApiLogService();
        $apiLogService->recordGetToken($user->id, ['username' => $user->username, 'scopes' => $scopes]);

        return $this->success($success, true);
    }
}
