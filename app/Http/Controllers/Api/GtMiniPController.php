<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Mini\MiniProgram;
use App\Models\Mini\Registration;
use App\Models\Wxuser;
use EasyWeChat\Factory;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Class GtMiniPController
 *
 * @package App\Http\Controllers\Api
 */
class GtMiniPController extends Controller
{
    use  ApiResponse;
    /**
     * @var array|null|string
     */
    protected $glc;

    /**
     * GtMiniPController constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        if ($request->input('glc') == 'P2GD020003' || $request->input('glc') == 'TC_TEST_LIB') {
            $this->glc = $request->input('glc');
        }
    }


    public function returnToken(Request $request)
    {
        if (empty($this->glc)) {
            return $this->message('缺少必填参数!', false);
        }
//        $wxuser = Wxuser::where('glc', $this->glc)->first(['openlib_appid', 'openlib_secret', 'openlib_url', 'openlib_opuser']);
//        if (empty($wxuser)) {
//            return $this->message('抱歉，数据不存在!!', false);
//        }
//        $config = $wxuser->only(['openlib_appid', 'openlib_secret', 'openlib_url', 'openlib_opuser']);

        $cacheKey = 'gtMiniToken' . $this->glc;
        if ($request->input('clearCache') == 1) {
            Cache::forget($cacheKey);
        }

        $data = Cache::get($cacheKey);
        if (empty($data)) {
            $params = http_build_query([
                'appid' => 'weixin_xcx',
                'secret' => 'b4e8d05f978sdkl63f9bcec36421229c168'
            ]);
            $url = 'https://resource4.gzlib.org.cn/openlib/service/barcode/token?' . $params;
            $http = new Client();
            $response = $http->get($url);
            $response = json_decode((string)$response->getBody(), true);
            if ($response['success'] === true) {
                Cache::put($cacheKey, $response['messagelist'][0], now()->parse($response['messagelist'][0]['time']));
                return $this->success($response['messagelist'][0], true);
            } else {
                return $this->message($response['messagelist'][0]['message'], false);
            }
        }
        return $this->success($data, true);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function cardBindUid(Request $request)
    {
        if (empty($this->glc) || !$request->filled(['uid', 'card'])) {
            return $this->message('缺少必填参数!', false);
        }
        $new = ['glc' => $this->glc, 'uid' => $request->input('uid'), 'card' => $request->input('card')];
        $exists = DB::table('mini_card_uid')->where($new)->exists();
        if ($exists) {
            return $this->message('该设备已绑定了对应读者证，请解绑原来的读者证。', false);
        }
        $new['create_at'] = date('Y-m-d H:i:s');
        $status = DB::table('mini_card_uid')->insert($new);
        if ($status) {
            return $this->message('绑定成功!', true);
        }
        return $this->message('系统繁忙，请稍后再试!', true);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function cardUnBindUid(Request $request)
    {
        if (empty($this->glc) || !$request->filled(['uid', 'card'])) {
            return $this->message('缺少必填参数!', false);
        }
        $delete = ['glc' => $this->glc, 'uid' => $request->input('uid'), 'card' => $request->input('card')];
        $exists = DB::table('mini_card_uid')->where($delete)->exists();
        if (!$exists) {
            return $this->message('数据不存在!', false);
        }
        $status = DB::table('mini_card_uid')->where($delete)->delete();
        if ($status) {
            return $this->message('解绑成功!', true);
        }
        return $this->message('系统繁忙，请稍后再试!', true);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function uidFindCard(Request $request)
    {
        if (empty($this->glc) || !$request->filled(['uid'])) {
            return $this->message('缺少必填参数!', false);
        }
        $new = ['glc' => $this->glc, 'uid' => $request->input('uid')];
        $first = DB::table('mini_card_uid')->where($new)->first(['card', 'uid', 'glc', 'create_at']);
        if (empty($first)) {
            return $this->message('数据不存在!', false);
        }
        return $this->success($first, true);
    }

    public function codeGetInfo(Request $request)
    {
        if (!$request->filled('code')) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $token = 'MINI9790113fc19b';
        $registration = Registration::getCache($token);
        $app = MiniProgram::initialize($registration['app_id'], $registration['secret']);
        $response = $app->auth->session($request->input('code'));

        if (!empty($response['errcode'])) {
            return $this->setTypeCode($response['errcode'])->message($response['errmsg'], false);
        }
        return $this->success($response, true);
//        $appid = $request->input('appid');
//        if (empty($this->glc) || !$request->filled(['code'])) {
//            return $this->message('缺少必填参数!', false);
//        }
//        $config = [
//            'app_id' => 'wx2592189b2ff9322a',
//            'secret' => '263a7836f3d865e6a99c8165aeec3032',
//            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
//            'response_type' => 'array',
//            'log' => [
//                'level' => 'error',
//                'file' => storage_path('logs/mini/wechat.log'),
//            ],
//        ];
//
//        if ($appid == 'wxf8f9c215d050b60d') {
//            $config['app_id'] = 'wxf8f9c215d050b60d';
//            $config['secret'] = '58d7b2390cf973e54b44b1647b355471';
//        }
//
//        $app = Factory::miniProgram($config);
//        $response = $app->auth->session($request->input('code'));
//        if (!empty($response['errcode'])) {
//            return $this->message($response['errmsg'], false);
//        }
//        return $this->success($response, true);
    }

    public function getAccessToken(Request $request)
    {
        $appid = $request->input('appid');
        if (empty($this->glc) || empty($appid)) {
            return $this->message('缺少必填参数!', false);
        }
        $token = 'MINI9790113fc19b';
        $cacheKey = sprintf(config('cacheKey.miniAccessToken'), $token);
        $data = Cache::get($cacheKey);
        if (empty($data)) {
            $registration = Registration::getCache($token);
            $app = MiniProgram::initialize($registration['app_id'], $registration['secret']);
            $response = $app->auth->getAccessToken()->getToken(true);
            $time = now()->addSeconds(5000);
            $response['expires_in'] = $time->toDateTimeString();
            Cache::put($cacheKey, $response, $time);
            $data = $response;
        }
        return $this->success($data, true);
    }

    public function getQrCode(Request $request)
    {
        if (empty($this->glc) || !$request->filled(['rdid'])) {
            return $this->message('缺少必填参数!', false);
        }
        $time = date('Ymd');
        $ticket = $request->input('rdid') . $time . $this->glc;
        $url = 'http://resource4.gzlib.org.cn/opac/reader/getReaderQrcode?';
        $params = http_build_query([
            'rdid' => $request->input('rdid'),
            'time' => $time,
            'ticket' => md5($ticket)
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        if ($response['flag'] == 1) {
            return $this->success(['code' => $response['qrcode']], true);
        }
        return $this->message('获取电子证失败', false);

    }


}
