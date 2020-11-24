<?php

namespace App\Http\Controllers\Web\Custom;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Reader;
use App\Services\JybDes;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Class FunController
 * @package App\Http\Controllers\Web\Custom
 */
class FunController extends Controller
{
//   https://uwei.dataesb.com/webWechat/replaceFun?t=
//   链接格式

    use ApiResponse;

    protected $jybEnKey = 'B3389BF3A96F50C3F3';

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(Request $request)
    {
        if (!$request->filled(['uweiToken', 't', 'uweiTime', 'uweiSign', 'openid'])) {
            abort(404);
        }
        $input = $request->input();
        $token = $input['uweiToken'];
        $t = $input['t'];
        $timestamp = $input['uweiTime'];
        $sign = $input['uweiSign'];
        $openid = $input['openid'];
        $key = config('envCommon.MENU_ENCRYPT_STR');
        if (time() - $timestamp > 180) {
            abort(404);
        }
        if ($sign != md5($key . $timestamp . $token)) {
            abort(404);
        }

        $reader = Reader::checkBind($openid, $token)->first(['name', 'rdid']);
        if (empty($reader)) {
            abort(404);
        }

        $Client = new Client();

        switch ($t) {
            case 1:
                $resources = [
                    'key' => 'hzcylib',
                    'orgID' => 200
                ];
                $uid = $reader['rdid'];
                $url = 'http://rjttj.softtone.cn/softtone/signIn?';
                $params = http_build_query([
                    'signkey' => md5($uid . $resources['key'] . date('YmdH')),
                    'uid' => $uid,
                    'orgID' => $resources['orgID'],
                    'residcode' => 'sfp_wx',
                ]);
                $response = $Client->get($url . $params);
                $response = json_decode((string)$response->getBody(), true);
                if ($response['Status'] == 1) {
                    dd($response['Data']);
                }
                return redirect($response['Data']);
                break;
            case 2:
                // 超星少儿绘本
                $accountMsg = [
                    // 潮州市图书馆
                    '7c2710bec5a1' => [
                        'unitId' => '722',
                        'staticKey' => '5D8D02F2EDFFA43139AF20F47F55EEFB'
                    ],
                    // 潮州市潮安区图书馆
                    '9263703c31c8' => [
                        'unitId' => '725',
                        'staticKey' => '10680A331D2B9CE44A50459A3C37E9C0'
                    ],
                    // 潮州市湘桥区文化馆图书馆
                    '104e51737d67' => [
                        'unitId' => '749',
                        'staticKey' => 'D790A1A5E0C539B3FCF623C35B565EF1'
                    ],
                    // 汕头市澄海区图书馆
                    '817b719b7a79' => [
                        'unitId' => '822',
                        'staticKey' => '0ABF87663F10AEB47FF1108145E34D7D'
                    ],
                    // 汕头市龙湖区图书馆
                    'a7d58c78648d' => [
                        'unitId' => '821',
                        'staticKey' => 'E026DF56185F063251DFE2CAC33FCE23'
                    ],
                    // 安吉县图书馆
                    'e0cd6218118b' => [
                        'unitId' => '851',
                        'staticKey' => 'B67A1FF3BB935E1E320DA8DB044224B5'
                    ],
                    // 揭阳市图书馆
                    'b3f09f89b0f6' => [
                        'unitId' => '840',
                        'staticKey' => 'D0D38A41D8A9457B313653921868C104'
                    ],
                    // 河源市图书馆
                    '0002f13b6d67' => [
                        'unitId' => '468',
                        'staticKey' => 'C246E01C140D82CBDB38941CE3FED890'
                    ],
                ];
                $sn = strtoupper(md5($reader['rdid'] . $accountMsg[$token]['staticKey'] . date('YmdH')));
                $verifyUrl = 'https://shaoerhuiben.chaoxing.com/inter/verify?unitId=' . $accountMsg[$token]['unitId'] . '&sn=' . $sn . '&username=' . $reader['rdid'];
                $response = $Client->get($verifyUrl);
                $response = json_decode((string)$response->getBody(), true);
                if ($response['code'] == 1) {
                    dd($response['message']);
                }
                $url = 'https://shaoerhuiben.chaoxing.com/mobile?siteId=' . $accountMsg[$token]['unitId'] . '&sn=' . $response['data']['sn'];
                return redirect($url);
                break;
            case 3:
                // 软件通
                $resources = $this->factoryData($request, ['key', 'orgID']);
                $uid = $reader['rdid'];
                $url = 'http://rjttj.softtone.cn/softtone/signIn?';
                $params = http_build_query([
                    'signkey' => md5($uid . $resources['key'] . date('YmdH')),
                    'uid' => $uid,
                    'orgID' => $resources['orgID'],
                    'residcode' => 'sfp_wx',
                ]);
                $response = $Client->get($url . $params);
                $response = json_decode((string)$response->getBody(), true);
                if ($response['Status'] == 1) {
                    dd($response['Data']);
                }
                return redirect($response['Data']);
                break;
            case 4:
                // 少儿绘本
                $resources = $this->factoryData($request, ['unitId', 'staticKey']);
                $sn = strtoupper(md5($reader['rdid'] . $resources['staticKey'] . date('YmdH')));
                $verifyUrl = 'https://shaoerhuiben.chaoxing.com/inter/verify?unitId=' . $resources['unitId'] . '&sn=' . $sn . '&username=' . $reader['rdid'];
                $response = $Client->get($verifyUrl);
                $response = json_decode((string)$response->getBody(), true);
                if ($response['code'] == 1) {
                    dd($response['message']);
                }
                $url = 'https://shaoerhuiben.chaoxing.com/mobile?siteId=' . $resources['unitId'] . '&sn=' . $response['data']['sn'];
                return redirect($url);
            default:
                abort(404);
        }
    }

    public function factoryData(Request $request, array $field = [])
    {
        if (!$request->filled($field)) {
            abort(404);
        }
        $JybDes = new JybDes();
        $data = [];
        foreach ($field as $val) {
            $decode = $JybDes->decrypt($request->input($val), $this->jybEnKey);
            if (empty($decode)) {
                abort(404);
            }
            $data[$val] = $decode;
        }
        return $data;
    }
}

