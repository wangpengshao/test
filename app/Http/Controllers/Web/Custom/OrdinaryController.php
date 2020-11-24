<?php

namespace App\Http\Controllers\Web\Custom;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wxuser;
use App\Services\CoverService;
use App\Services\OpacService;
use App\Services\OpacSoap;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OrdinaryController extends Controller
{
    use ApiResponse;

    public function rankingList(Request $request)
    {
        $token = $request->input('token');
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 50);
        $wxuser = Wxuser::getCache($token);
        if ($wxuser == false) {
            abort(404);
        }
        $libcode = $request->input('libcode', '');
        $params = http_build_query([
            'q0' => '',
            'searchWay0' => '',
            'isFacet' => 'true',
            'rows' => $rows,
            'page' => $page,
            'hasholding' => 1,
            'sortOrder' => 'desc',
            'sortWay' => 'loannum_sort',
            'wt' => 'json',
            'libcode' => $libcode
        ]);
        $url = $wxuser['opacurl'] . 'api/search?';
        $response = OpacService::request($url . $params);
        if (empty($response)) {
            return $this->failed('opac接口异常,请稍后再试', 400);
        }
        if (is_array($response) && Arr::has($response, 'errMes')) {
            return $this->failed($response['errMes'], 400);
        }
        $response = json_decode($response, true);
        $responseStatus = $response['response'];
        unset($response);
        $numFound = $responseStatus['numFound'];
        $start = $responseStatus['start'];
        //opac
        $success = [
            'libList' => [],
            'bookList' => [],
            'libcode' => $libcode,
            'url' => $request->url() . '?token=' . $token
        ];
        $opacSoap = OpacSoap::make(['token' => $token, 'opacurl' => $wxuser['opacurl']], 'webservice/loanWebservice');
        $libResponse = $opacSoap->requestFunction('getLibcodeList');
        if (!Arr::has($libResponse, 'errMes')) {                        //...异常处理
            $libResponse = $libResponse['return'];
            foreach ($libResponse as $k => $v) {
                $success['libList'][] = [
                    'name' => $v['name'],
                    'simpleName' => $v['name'],
                    'libcode' => $v['libcode'],
                    'address' => ""
                ];
            }
        }
        //由于 numFound 为一的时候存在着数据结构差异。
        if ($numFound === "0" || $numFound <= $start) {
            return view('web.custom.rankingList', $success);
        }
        $bookList = $responseStatus['docs'];
        $isbnImg = [];
        foreach ($bookList as $k => $v) {
            if (isset($v['isbn_meta'])) {
                $isbnImg[$v['isbn_meta']] = $v['isbn_meta'];
            }
            $bookList[$k]['loannum_sort'] = isset($v['loannum_sort']) ? (int)$v['loannum_sort'] : 0;
        }
        unset($k, $v);
        $cover = CoverService::search($isbnImg);
        foreach ($bookList as $k => $v) {
            $bookList[$k]['imgurl'] = '';
            if (isset($v['isbn_meta'])) {
                $bookList[$k]['imgurl'] = Arr::get($cover, $v['isbn_meta'], '');
            }
        }
        $success['bookList'] = $bookList;
        return view('web.custom.rankingList', $success);
    }

    public function hbLibRegister(Request $request)
    {
        if ($request->isMethod('post')) {
            if (!$request->filled('type', 'idCard', 'name')) {
                return $this->message('缺少参数', false);
            }
            $host_url = 'http://alipay.library.hb.cn';
            $type = $request->input('type');
            switch ($type) {
                case 'check':
                    return $this->message('验证通过,请完善一下注册资料', true);
//                    dd('http://10.118.61.96/mh_interface/api/alipay/isRegister?idNumber=' . $request->input('idCard'));
                    $http = new Client();
                    $response = $http->get($host_url.'/mh_interface/api/alipay/isRegister?idNumber=' . $request->input('idCard'));
//                    dd('https://alipay.library.hb.cn/mh_interface/api/alipay/isRegister?idNumber=' . $request->input('idCard'));
                    $response = json_decode((string)$response->getBody(), true);
                    dd($response);
                    break;
                case 'save':
                    break;
                default:
                    return $this->message('缺少参数', false);
            }
        }
        return view('web.custom.hbLibRegister', [
            'url' => $request->url()
        ]);
    }
}

