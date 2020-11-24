<?php

namespace App\Http\Controllers\Api\Mini;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Mini\EResources;
use App\Models\Wxuser;
use App\Services\CoverService;
use App\Services\JybService;
use App\Services\OpacService;
use App\Services\OpenlibService;
use App\Services\XmlService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Contracts\Encryption\DecryptException;


class EResourcesController extends Controller
{
    use ApiResponse;

    protected $mes = '';
    protected $myToken = '';
    protected $signKey = 'gflAV5YH19cQFWp';
    protected $expires_in = 43200;
    protected $info;
    protected $token;

    public function __construct(Request $request)
    {
        $this->myToken = $request->input('token');
        $this->token = $request->input('token');
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
    }

    public function checkHeader(Request $request)
    {
        $header = $request->header('Authorization', '');
        if (empty($header)) {
            return false;
        }
        if (!Str::startsWith($header, 'Bearer ')) {
            return false;
        }
        $access_token = Str::substr($header, 7);
        try {
            $decrypted = decrypt($access_token);
        } catch (DecryptException $e) {
            return false;
        }
        if (Arr::get($decrypted, 'c') + $this->expires_in < time()) {
            return false;
        }
        $this->info = $decrypted;
        return true;
    }

    public function getAuthorizeToken(Request $request)
    {
        $config = EResources::getCache($this->myToken);
        if ($config === false || $config['status'] != 1) {
            return $this->message('without authorization', false);
        }
        if (!$request->filled(['miniOpenid', 'sign'])) {
            return $this->message('lack of parameter', false);
        }
        $sign = $this->token . date('Ymd') . $this->signKey;
        $sign = md5($sign);
        if ($sign != $request->input('sign')) {
            return $this->message('sign the validation fails', false);
        }
        $time = time();
        $data = [
            't' => $this->token,
            'u' => $request->input('miniOpenid'),
            'c' => $time
        ];

        $Authorization = encrypt($data);
        $response = [
            'token_type' => 'Bearer',
            'expires_in' => $this->expires_in,
            'access_token' => $Authorization,
        ];
        return $this->success($response, true);

    }

    public function readerValidation(Request $request)
    {
        if (!$this->checkHeader($request)) {
            return response()->json(['message' => 'Unauthenticated'])->setStatusCode(401);
        }
        if (!$request->filled(['rdid', 'password'])) {
            return $this->message('lack of parameter', false);
        }
        $rdid = $request->input('rdid');
        $password = $request->input('password');
        //读者证认证
        $openlibService = OpenlibService::make($this->token);
        $confirmreader = $openlibService->confirmreader($rdid, $password);
        if ($confirmreader['success'] == false) {
            return $this->message(Arr::get($confirmreader, 'messagelist.0.message'), false);
        }
        $create = [
            'token' => $this->myToken,
            'openid' => $this->info['u'],
            'rdid' => $rdid,
            'created_at' => date('Y-m-d H:i:s')
        ];
        DB::table('mini_e_resources_l')->insert($create);
        return $this->message(Arr::get($confirmreader, 'messagelist.0.message'), true);
    }

    //opac检索
    public function opacSearch(Request $request)
    {
        if (!$this->checkHeader($request)) {
            return response()->json(['message' => 'Unauthenticated'])->setStatusCode(401);
        }
        if (!$request->filled(['searchWay0', 'q0'])) {
            return $this->message('检索类型或检索值不能为空!!', false);
        }
        $wxuser = Wxuser::getCache($this->myToken);
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $params = [
            'q0' => $request->input('q0'),
            'searchWay0' => $request->input('searchWay0'),
            'isFacet' => 'true',
//            'curlibcode' => $wxuser['libcode'],
            'rows' => $rows,
            'page' => $page,
        ];
        if ($request->filled(['q1', 'searchWay1'])) {
            $params += $request->only(['q1', 'searchWay1']);
        }

        $url = $wxuser['opacurl'] . 'api/search?' . http_build_query($params);
        $response = OpacService::request($url);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $response = XmlService::loadOpacXML($response);
        $responseStatus = $response['response']['response'];
//        $facet_counts = $response['response']['facet_counts']['facet_fields'];
        $success = [
            'numFound' => $responseStatus['numFound'],
            'start' => $responseStatus['start'],
            'rows' => $rows,
            'page' => $page,
//            'filter' => Arr::only($facet_counts, ['curlibcode', 'f_author', 'f_pubdate']),
            'bookList' => [],
        ];
        $numFound = $responseStatus['numFound'];
        $start = $responseStatus['start'];
        //由于 numFound 为一的时候存在着数据结构差异。
        if ($numFound === "0" || $numFound <= $start) {
            return $this->success($success, true);
        } elseif ($numFound === "1" || $numFound - $start == 1) {
            $bookList = $responseStatus['doc'];
            $bookList['imgurl'] = '';
            $isbn_meta = Arr::get($bookList, 'isbn_meta');
            if ($isbn_meta) {
                $isbn_meta = str_replace('-', '', $isbn_meta);
                $bookList['imgurl'] = CoverService::search($isbn_meta);
            }
            $success['bookList'][] = $bookList;
            return $this->success($success, true);
        }

        $bookList = $responseStatus['doc'];
        $isbnImg = [];
        foreach ($bookList as $k => $v) {
            if (isset($v['isbn_meta'])) {
                $isbnImg[$v['isbn_meta']] = str_replace('-', '', $v['isbn_meta']);
            }
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
        return $this->success($success, true);
    }

    public function bookDetails(Request $request)
    {
        if (!$this->checkHeader($request)) {
            return response()->json(['message' => 'Unauthenticated'])->setStatusCode(401);
        }
        if (!$request->filled('bookrecno')) {
            return $this->message('缺少bookrecno参数!', false);
        }
        $wxuser = Wxuser::getCache($this->myToken);
        $bookrecno = $request->input('bookrecno');
        $url = $wxuser['opacurl'] . 'api/book/' . $bookrecno;
        $response = OpacService::request($url);

        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $response = json_decode($response, true);
        //初始化数据  start
        $success = [
            'details' => [],
            'holdings' => [
                'total' => 0,
                'list' => []
            ]
        ];
        //初始化数据  end
        $isbn = Arr::get($response, 'biblios.isbn');

        if ($isbn) {   //详情内容
            $success['details'] = CoverService::bookInfoLv2($isbn);
        }
        //非法isbn时 返回 模版默认数据
        if ((empty($isbn) || empty($success['details'])) && Arr::get($response, 'biblios')) {
            $biblios = $response['biblios'];
            $template = [
                'tags' => '', 'originTitle' => '', 'image' => '', 'sourceImage' => '', 'binding' => '', 'authorIntro' => '',
                'translator' => '', 'catalog' => '', 'subtitle' => '', 'alt' => '', 'url' => '', 'altTitle' => '',
                'smallImage' => '', 'mediumImage' => '', 'largeImage' => '', 'smallSourceImage' => '', 'mediumSourceImage' => '',
                'largeSourceImage' => '', 'rating' => '', 'createTime' => '', 'updateTime' => '', 'summary' => '',
                'isbn' => $biblios['isbn'],
                'title' => $biblios['title'],
                'author' => $biblios['author'],
                'pages' => $biblios['page'],
                'pubdate' => $biblios['pubdate'],
                'publisher' => $biblios['publisher'],
                'price' => $biblios['price'],
            ];
            $success['details'] = $template;
        }
        unset($response);

        //馆藏列举     start
        $url = $wxuser['opacurl'] . 'api/holding/' . $bookrecno;
        $holdingsResponse = OpacService::request($url);
        if (empty($holdingsResponse) || (is_array($holdingsResponse) && Arr::get($holdingsResponse, 'errMes'))) {
            $holdingsResponse = null;
        } else {
            $holdingsResponse = json_decode($holdingsResponse, true);
        }
        if ($holdingsResponse) {
            $holdingList = $holdingsResponse['holdingList'];
            $holdStateMap = $holdingsResponse['holdStateMap'];
            $localMap = $holdingsResponse['localMap'];
            $libcodeMap = $holdingsResponse['libcodeMap'];
            unset($holdingsResponse);
            $holding = [];

            if (count($holdingList) > 0) {
                $holdState = Arr::pluck($holdStateMap, 'stateName', 'stateType');

                foreach ($holdingList as $k => $v) {
                    $holdingKey = $v['curlib'] . '_' . $v['curlocal'];
                    if (!isset($holding[$holdingKey])) {
                        $holding[$holdingKey] = [
                            'libName' => $libcodeMap[$v['curlib']],
                            'localName' => $localMap[$v['curlocal']],
                            'curlib' => $v['curlib'],
                            'curlocal' => $v['curlocal'],
                            'count' => 0,
                        ];
                    }
                    $stateKey = $v['callno'] . '_' . $v['state'] . '_' . $v['shelfno'];
                    $holding[$holdingKey]['count']++;

                    if (isset($holding[$holdingKey]['stateList'][$stateKey])) {
                        $holding[$holdingKey]['stateList'][$stateKey]['count'] += 1;
                    } else {
                        $holding[$holdingKey]['stateList'][$stateKey] = [
                            'callno' => $v['callno'],
                            'statrStr' => $holdState[$v['state']],
                            'state' => $v['state'],
                            'count' => 1,
                            'shelfno' => $v['shelfno'],
                        ];
                    }

                }
                unset($k, $v);
            }

            if (count($holding) > 0) {
                $success['holdings'] = [
                    'total' => count($holdingList),
                    'list' => array_values($holding)
                ];
            }
        }
        //馆藏列举     end
        return $this->success($success, true);
    }

    public function qrCode(Request $request, JybService $jybService)
    {
        if (!$this->checkHeader($request)) {
            return response()->json(['message' => 'Unauthenticated'])->setStatusCode(401);
        }
        if (!$request->filled('rdid')) {
            return $this->message('缺少读者证参数!', false);
        }
        $rdid = $request->input('rdid');
        $where = [
            'token' => $this->myToken,
            'openid' => $this->info['u'],
            'rdid' => $rdid
        ];
        $doesntExist = DB::table('mini_e_resources_l')->where($where)->doesntExist();
        if ($doesntExist) {
            return $this->message('证号必须是当前用户验证通过的', false);
        }
        $wxuser = Wxuser::getCache($this->myToken);
        $http = new Client();
        if ($wxuser['qr_type'] === 1) {
            $time = date('Ymd');
            $ticket = md5($rdid . $time . $wxuser['glc']);
            $url = $wxuser['opacurl'] . 'reader/getReaderQrcode?';
            $params = http_build_query([
                'rdid' => $rdid,
                'time' => $time,
                'ticket' => $ticket
            ]);
            $response = $http->get($url . $params);
            $response = json_decode((string)$response->getBody(), true);
            if ($response['flag'] == 1) {
                return $this->success(['qrcode' => $response['qrcode']], true);
            }
        }
        if ($wxuser['qr_type'] === 2) {
            $response = $jybService->getElectronicCard($wxuser, $rdid);

            if ($response['code'] == 200) {
                return $this->success(['qrcode' => $response['uuid']], true);
            }
        }
        return $this->message('服务器繁忙，请稍后再试!', false);
    }

}
