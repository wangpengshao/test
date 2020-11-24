<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Fans;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{
    use ApiResponse;

    // 其他通用的Api帮助函数

    public function index(Request $request)
    {
        if (!$request->filled(['time', 'sign', 'username', 'secretKey']))
            return $this->message('lack of parameter', false);
        [
            'time' => $time,
            'username' => $username,
            'sign' => $sign,
            'secretKey' => $password,
        ] = $request->input();

        if (time() - $time > 180) return $this->message('invalid time', false);
        /* MD5 username & time & keyStr */
        if (md5(implode('&', [$username, $time, config('envCommon.ENCRYPT_STR')])) != $sign)
            return $this->message('invalid sign', false);
        if (!Auth::attempt(['username' => $username, 'password' => $password]))
            return $this->message('the user information error', false);

        $redis = Redis::connection();
        $user = $request->user();
        $rKey = 'apiAuth:user:' . $user->id . ':rNum';
        $redis->incr('apiAuth:user:' . $user->id . ':allNum');
        if ($user->status == 0) return $this->message('this user has been closed', false);
        if (date('Y-m-d H:i:s') > $user->expires_at) return $this->message('this user has expired', false);
        $r_num = ($redis->get($rKey)) ?: $user->r_num;
        if ($r_num <= 0) return $this->message('forbid to clear quota because of reaching the limit', false);

        $r_num--;
        if ($r_num == 0) {
            $redis->del($rKey);
            $user->r_num = 0;
            $user->save();
        } else {
            $redis->set($rKey, $r_num);
        }
        DB::table('oauth_access_tokens')->where('user_id', $user->id)->where('client_id', 2)->delete();

        $form_params = [
                'scope' => str_replace(",", " ", $user->scopes),
                'username' => $username,
                'password' => $password,
            ] + config('Passport');

        $http = new Client();
        $response = $http->post($request->root() . '/oauth/token', ['form_params' => $form_params]);
        $response = json_decode((string)$response->getBody(), true);
        $response = array_only($response, ['token_type', 'expires_in', 'access_token']);
        return $this->success($response, true);
    }


    public function openid(Request $request)
    {
        //        if ( time() - $request->time > 120 ) {
//            return $this->failed('Time invalid.', 401);
//        }
        if (!$request->filled(['sign', 'openid', 'time', 'token'])) {
            return $this->failed('Lack of parameter.', 400);
        }
        $sign = $request->input('sign');
        $openid = $request->input('openid');
        $time = $request->input('time');
        $token = $request->input('token');
        /* md5 openid + time + keyStr */
        if (md5($openid . $time . config('envCommon.ENCRYPT_STR')) != $sign) {
            return $this->failed('Signature error.', 401);
        }
        $authDATA = [
            'username' => $openid,
            'password' => md5($openid)
        ];
        if (Fans::where(['token' => $token, 'username' => $openid])->doesntExist()) {
            return $this->failed('username or secretKey invalid.', 400);
        }
        /* 判断是否存在，写入缓存 */
        $http = new Client();
        $response = $http->post($request->root() . '/oauth/token', [
            'form_params' => config('Fanspasspost') + $authDATA
        ]);
        $response = json_decode((string)$response->getBody(), true);
        return $this->success($response, true);

    }


}
