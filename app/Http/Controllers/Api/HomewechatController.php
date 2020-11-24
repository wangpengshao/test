<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\EsAddRecords;
use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\OtherConfig;
use App\Models\Wechat\Reader;
use App\Models\Wxuser;
use App\Services\CoverService;
use App\Services\MenuService;
use App\Services\OpacService;
use App\Services\OpacSoap;
use App\Services\OpenlibService;
use App\Services\XmlService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use App\Admin\Extensions\Tools\PassWord;

//use Illuminate\Support\Str;

class HomewechatController extends Controller
{
    use ApiResponse;

    //访问菜单
    public function visitMenu(Request $request)
    {
        $menuID = $request->route('menuid');
        $token = $request->user()->token;
        $indexMenu = IndexMenu::getCache($token, $menuID);
        if ($indexMenu === false) {
            return $this->failed('无效的菜单id', 400, false);
        }
        $type = $request->input('type', '');
        if ($type != 'resources' && $indexMenu['status'] == 0) {
            return $this->failed('抱歉,该菜单已停止访问!', 400, false);
        }
        $builderData = [
            'token' => $token,
            'openid' => $request->user()->openid,
            'created_at' => date('Y-m-d H:i:s'),
            'mid' => $menuID,
            'name' => $indexMenu->caption,
        ];
        EsAddRecords::dispatch('menu', $builderData);

        $menuService = MenuService::make('self', $token);
        $url = $menuService->returnUrl($indexMenu, $request->user()->toArray(), 'prompt');
        if ($url === 1) {
            return $this->failed('需要绑定才能访问!!', 401, false);
        }
        unset($menuService);
        return $this->success(['url' => $url], true);
    }

    //猜你喜欢
    public function youlikeList(Request $request)
    {
        $token = $request->user()->token;
        $rdid = Reader::userGetBind($request->user())->value('rdid');
        if ($rdid == null) return $this->failed('尚未绑定读者证!', 401);
        $wxuser = Wxuser::getCache($token);

        $youlikeUrl = 'http://dev.data380.com:7070/analytics/api/reader/book/recommend?';
        $params = http_build_query([
            'g' => $wxuser['glc'],
            'r' => $rdid,
            'f' => 'PHP_7',
            'token' => md5('TcNuclearWeapons-301' . $wxuser['glc'] . $rdid . date('Ymd'))
        ]);
        $http = new Client();
        $response = $http->get($youlikeUrl . $params);
        $response = json_decode((string)$response->getBody(), true);
        $re = [];
        if (count($response['data']) > 0) {
            $re = $response['data'];
        }
        return $this->success($re);
    }

    public function newbookList(Request $request)
    {
        $token = $request->input('token');
        $wxuser = Wxuser::getCache($token);
        if ($wxuser['newbook_sw'] != 1) {
            return $this->failed('新书通报已关闭使用!!', 400);
        }

        $opacSoap = OpacSoap::make($wxuser->only('token', 'opacurl'), 'webservice/newPubWebservice');

        $rows = $request->input('rows', 10);
        $start = $request->input('start', 1);

        $arguments = [
            'limitDays' => 720,
            'rows' => $rows,
            'start' => $start,
            'libcode' => $wxuser['libcode']
        ];
        $response = $opacSoap->requestFunction('getNewPub', $arguments);

        if (Arr::get($response, 'errMes')) {                        //...异常处理
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $success = [
            'rows' => $rows,
            'start' => $start,
            'count' => 0,
            'list' => []
        ];
        //...没有数据处理
        if (Arr::get($response, 'return.bookSign') || Arr::get($response, 'return.bookrecno')) {
            return $this->success($success, true);
        }
        $list = $response['return'];
        $count = count($list);
        if ($count > 0) {
            $isbnImg = [];
            foreach ($list as $k => $v) {
                if (isset($v['isbn'])) {
                    $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
                }
            }
            unset($k, $v);
            $cover = CoverService::search($isbnImg);
            foreach ($list as $k => $v) {
                $list[$k]['imgurl'] = '';
                $list[$k]['booktype'] = Arr::get($v, 'subject');
                if (isset($v['isbn'])) {
                    $list[$k]['imgurl'] = Arr::get($cover, $v['isbn']) ?? '';
                }
            }
            $success['count'] = $count;
            $success['list'] = $list;
        }
        return $this->success($success, true);
    }

    //opac检索
    public function opacSearchbib(Request $request)
    {
        if (!$request->filled(['searchWay0', 'q0'])) {
            return $this->failed('检索类型或检索值不能为空!!', 400);
        }
        $token = $request->user()->token;
        $wxuser = Wxuser::getCache($token);
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $libcode = $request->input('libcode', '');

        $params = [
            'q0' => $request->input('q0'),
            'searchWay0' => $request->input('searchWay0'),
            'isFacet' => 'true',
            'curlibcode' => $libcode,
            'rows' => $rows,
            'page' => $page,
        ];
//        if ($request->filled(['q0', 'searchWay0'])) {
//            $params += $request->only(['q0', 'searchWay0']);
//        }
        if ($request->filled(['q1', 'searchWay1'])) {
            $params += $request->only(['q1', 'searchWay1']);
//            $params += ['logical0' => 'AND'];
        }
        if ($request->filled(['q2', 'searchWay2'])) {
            $params += $request->only(['q2', 'searchWay2']);
//            $params += ['logical1' => 'AND'];
        }

        $url = $wxuser['opacurl'] . 'api/search?' . http_build_query($params);
        $response = OpacService::request($url);

        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $response = XmlService::loadOpacXML($response);
        $responseStatus = $response['response']['response'];
        $facet_counts = $response['response']['facet_counts']['facet_fields'];
        $success = [
            'numFound' => $responseStatus['numFound'],
            'start' => $responseStatus['start'],
            'rows' => $rows,
            'page' => $page,
            'filter' => Arr::only($facet_counts, ['curlibcode', 'f_author', 'f_pubdate']),
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

    //书目详情
    public function getBookInfo(Request $request)
    {
        $token = $request->user()->token;
        $wxuser = Wxuser::getCache($token);
        $bookrecno = $request->route('bookrecno');

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

    public function getSearchHot(Request $request)
    {
        $token = $request->input('token');
        $cacheKey = 'searchHot:' . $token;

        $response = Cache::get($cacheKey);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->success([], true);
        }
        if ($response) return $this->success($response, true);

        $wxuser = Wxuser::getCache($token);
        $url = $wxuser['opacurl'] . 'hotsearch/keywordList?return_fmt=json';
        $response = OpacService::request($url);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            Cache::put($cacheKey, $response, 40);
            return $this->success([], true);
        }
        $response = json_decode($response, true);
        Cache::put($cacheKey, $response, 45);
        return $this->success($response, true);
    }

    public function openidGetReaderInfo(Request $request)
    {
        $rdid = Reader::userGetBind($request->user())->value('rdid');
        if ($rdid == null) return $this->failed('尚未绑定读者证!', 401);
        $wxuser = Wxuser::getCache($request->user()->token);

        $opacSoap = OpacSoap::make($wxuser, 'webservice/readerWebservice');
        $arguments = [
            'rdid' => $rdid,
            'key' => 'TCSOFT_INTERLIB'
        ];
        $response = $opacSoap->requestFunction('getReader', $arguments);
        if (Arr::get($response, 'errMes')) {                        //...异常处理
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        if (empty($response)) return $this->failed('抱歉,没有找到读者数据!', 400);
        $response = $response['return'];
        $exceptArray = [
            'rdPasswd', 'rdSort1List',
            'rdSort2List', 'rdSort3',
            'rdSort4', 'rdSort3List',
            'rdSort4List', 'rdSort5List'
        ];
        $success = Arr::except($response, $exceptArray);
        return $this->success($success, true);
    }

    public function editReaderInfo(Request $request)
    {
        $rdid = Reader::userGetBind($request->user())->value('rdid');
        if ($rdid == null) return $this->failed('尚未绑定读者证!', 401);

        if (!$request->hasAny(['password', 'email', 'phone', 'address'])) {
            return $this->failed('缺少必带参数!!', 400);
        }
        $token = $request->user()->token;
        $wxuser = Wxuser::getCache($token);

        $rdPasswd = $request->input('password');
        $opacSoap = OpacSoap::make($wxuser, 'webservice/readerWebservice');

        //判断是否只修改密码
        if (!empty($rdPasswd)) {
            if (!$request->filled('newpassword')) {
                return $this->failed('缺少必带参数!!', 400);
            }
            $newPasswd = $request->input('newpassword');
            // 获取当前馆的密码配置项
            $config = OtherConfig::getPwdConfig($wxuser['id'])->first();
            if ($config['pw_check_sw']) {
                // 校验密码强度
                $res = PassWord::checkWeak($newPasswd, $config['pw_type'], $config['pw_min_length']);
                if ($res) {
                    return $this->message('密码强度不符合要求,请重新输入', false);
                }
            }
            $arguments = [
                'rdid' => $rdid,
                'oldPasswd' => $rdPasswd,
                'newPasswd' => $newPasswd
            ];
            $response = $opacSoap->requestFunction('updateReaderPasswd', $arguments);
            if (Arr::get($response, 'errMes')) {                        //...异常处理
                return $this->failed(Arr::get($response, 'errMes'), 400);
            }
            if (isset($response['return'])) {
                return $this->message($response['return'], false);
            }
            return $this->message('修改密码成功!', true);
        }
        $arguments = [
            'rdid' => $rdid,
            'key' => 'TCSOFT_INTERLIB'
        ];
        $response = $opacSoap->requestFunction('getReader', $arguments);
        if (Arr::get($response, 'errMes')) {                        //...异常处理
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        if (empty($response)) return $this->failed('抱歉,没有找到读者数据!', 400);

        $readerInfo = $response['return'];
        $reader = [
            "rdid" => $rdid,
            "hasPhoto" => $readerInfo['hasPhoto'],
            "global" => $readerInfo['global'],
        ];
        $rdPasswd = $readerInfo['rdPasswd'];

        if ($request->filled('email')) {
            $reader['rdEmail'] = $request->input('email');
        }

        if ($request->filled('phone')) {
            $reader['rdLoginId'] = $request->input('phone');
        }

        if ($request->filled('address')) {
            $reader['rdAddress'] = $request->input('address');
        }
//        if ($request->filled('idcard')) {
//            $returnData['rdCertify'] = $request->input('idcard');
//        }
        $arguments = [
            'reader' => $reader,
            'password' => md5($rdPasswd),
        ];

        $response = $opacSoap->requestFunction('updateReaderInfo', $arguments);
        if (Arr::get($response, 'errMes')) {                        //...异常处理
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }

        if (isset($response['return']) && $response['return'] == 'OK') {
            return $this->message('修改信息成功!', true);
        }
        return $this->message('修改失败，请稍后再试试!', false);

    }

    //预约 && 预借 书本馆藏列表
    public function getBookHolding(Request $request)
    {
        if (!$request->filled(['bookrecno', 'type', 'curlib', 'curlocal'])) {
            return $this->failed('缺少必填参数', 400);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $rdid = Reader::checkBind($openid, $token)->value('rdid');
        if ($rdid === null) {
            return $this->failed('尚未绑定读者证!', 401);
        }

        $bookrecno = $request->input('bookrecno');
        $type = $request->input('type');
        $wxuser = Wxuser::getCache($token);

        //判断读者是否可以进行预约、预借操作 start
        switch ($type) {
            case 1:
                $url = $wxuser['opacurl'] . 'api/prelend/canPrelend?rdid=' . $rdid;
                break;
            case 2:
                $url = $wxuser['opacurl'] . 'api/reservation/canReservation?rdid=' . $rdid;
                break;
            default:
                return $this->failed('type is invalid', 400);
        }
        $response = OpacService::request($url);
        $response = json_decode($response, true);
        if (Arr::get($response, '0.status_code') !== 0) {
            return $this->failed(Arr::get($response, '0.status'), 400);
        }
        //判断读者是否可以进行预约、预借操作 end

        $curlib = $request->input('curlib');
        $curlocal = $request->input('curlocal');

        //馆藏列表
        $url = $wxuser['opacurl'] . 'api/holding/' . $bookrecno;
        $holdingsResponse = OpacService::request($url);
        $holdingsResponse = json_decode($holdingsResponse, true);
        $localMap = $holdingsResponse['localMap'];
        $libcodeMap = $holdingsResponse['libcodeMap'];
        $holdStateMap = $holdingsResponse['holdStateMap'];

        $success = [];
        switch ($type) {
            case 1:    //预借
                $success = [
                    'list' => [],
                    'wayList' => []
                ];
                $opacSoap = OpacSoap::make($wxuser, 'webservice/prelendWebservice');
                $arguments = [
//                    'libcode' => $curlib,
                    'bookrecno' => $bookrecno,
                    'doPage' => false,
                    'toPage' => 1,
                    'pageSize' => 100
                ];
                //馆藏列表
                $response = $opacSoap->requestFunction('getCanPrelendList', $arguments);
                if ($response) {
                    $response = $response['return'];
                    if (Arr::get($response, 'barcode')) {
                        if ($response['curlib'] == $curlib && $response['curlocal'] == $curlocal) {
                            $success['list'][] = [
                                'title' => $response['biblios']['title'],
                                'barcode' => $response['barcode'],
                                'bookrecno' => $response['bookrecno'],
                                'callno' => Arr::get($response, 'callno'),
                                'orglib' => $response['orglib'],
                                'orglib_str' => Arr::get($libcodeMap, $response['orglib'], '...'),
//                            'orglocal' => $response['orglocal'],
                                'curlib' => $response['curlib'],
                                'curlib_str' => Arr::get($libcodeMap, $response['curlib'], '...'),
                                'curlocal' => $response['curlocal'],
                                'curlocal_str' => Arr::get($localMap, $response['curlocal'], '...'),
                                'volnum' => $response['volnum'],
                                'state' => Arr::get($holdStateMap, $response['state'], '...'),
                            ];
                        }

                    } else {
                        foreach ($response as $k => $v) {
                            if ($v['curlib'] == $curlib && $v['curlocal'] == $curlocal) {
                                $success['list'][] = [
                                    'title' => $v['biblios']['title'],
                                    'barcode' => $v['barcode'],
                                    'bookrecno' => $v['bookrecno'],
                                    'callno' => Arr::get($v, 'callno'),
                                    'orglib' => $v['orglib'],
                                    'orglib_str' => Arr::get($libcodeMap, $v['orglib'], '...'),
                                    'curlib' => $v['curlib'],
                                    'curlib_str' => Arr::get($libcodeMap, $v['curlib'], '...'),
                                    'curlocal' => $v['curlocal'],
                                    'curlocal_str' => Arr::get($localMap, $v['curlocal'], '...'),
                                    'volnum' => $v['volnum'],
                                    'state' => Arr::get($holdStateMap, $v['state'], '...'),
                                ];
                            }

                        }
                    }
                }
                //取书方式
                $response = $opacSoap->requestFunction('getPrelendLocalList');
                if ($response) {
                    $response = $response['return'];
                    if (Arr::get($response, 'libcode')) {
                        if ($response['libcode'] == $curlib) {
                            $success['wayList'][] = $response;
                        }
                    } else {
                        foreach ($response as $k => $v) {
                            if ($v['libcode'] == $curlib) {
                                $success['wayList'][] = $v;
                            }
                        }
                    }
                }
                break;
            case 2:  //预约
                $success = [
                    'list' => [],
                    'wayList' => [],
                    //Other custom  默认值 可取消时间
                    'parameter' => [
                        'appointment_min_day' => 7,
                        'appointment_max_day' => 30,
                    ]
                ];

                $customOp = OtherConfig::otherCustom($wxuser['id'])->first();
                if ($customOp) {
                    $success['parameter']['appointment_min_day'] = $customOp->appointment_min_day;
                    $success['parameter']['appointment_max_day'] = $customOp->appointment_max_day;
                }

                $opacSoap = OpacSoap::make($wxuser, 'webservice/reservationWebservice');
                $arguments = [
                    'libcode' => $curlib,
                    'bookrecno' => $bookrecno,
                    'doPage' => false,
                    'toPage' => 1,
                    'pageSize' => 100
                ];
                $response = $opacSoap->requestFunction('getCanReservationList', $arguments);
                if ($response) {
                    $response = $response['return'];
                    if (Arr::get($response, 'barcode')) {
                        $success['list'][] = [
                            'title' => $response['biblios']['title'],
                            'barcode' => $response['barcode'],
                            'bookrecno' => $response['bookrecno'],
                            'callno' => $response['callno'] ?? '',
                            'orglib' => Arr::get($libcodeMap, $response['orglib'], '...'),
                            'orglocal' => Arr::get($localMap, $response['orglocal'], '...'),
                            'jie_time' => date('Y-m-d H:i:s', strtotime($response['loan']['loanDate'])),
                            'huan_time' => date('Y-m-d H:i:s', strtotime($response['loan']['returnDate'])),
                        ];
                    } else {
                        foreach ($response as $k => $v) {
                            $success['list'][] = [
                                'title' => $v['biblios']['title'],
                                'barcode' => $v['barcode'],
                                'bookrecno' => $v['bookrecno'],
                                'callno' => $v['callno'] ?? '',
                                'orglib' => Arr::get($libcodeMap, $v['orglib'], '...'),
                                'orglocal' => Arr::get($localMap, $v['orglocal'], '...'),
                                'jie_time' => date('Y-m-d H:i:s', strtotime($v['loan']['loanDate'])),
                                'huan_time' => date('Y-m-d H:i:s', strtotime($v['loan']['returnDate'])),
                            ];
                        }
                    }
                }
                //取书方式  webservice接口 需要新版本支持
//                $response = $opacSoap->requestFunction('getPickupLocals');
//                $response = Arr::get($response, '_return.entry');
//                if ($response) {
//                    if (Arr::get($response, 'key')) {
//                        $success['wayList'][] = $response;
//                    } else {
//                        foreach ($response as $k => $v) {
//                            $success['wayList'][] = $v;
//                        }
//                    }
//                }
                $url = $wxuser['opacurl'] . 'api/reservation/pickupLocals';
                $response = OpacService::request($url);
                $response = json_decode($response, true);
                if ($response) {
                    $success['wayList'] = $response;
                }
                break;
            default:
        }
        return $this->success($success, true);
    }

    public function myPrelendList(Request $request)
    {
        $rdid = Reader::userGetBind($request->user())->value('rdid');
        if ($rdid == null) return $this->failed('尚未绑定读者证!', 401);

        $token = $request->user()->token;
        $wxuser = Wxuser::getCache($token);
        $opacSoap = OpacSoap::make($wxuser, 'webservice/readerWebservice');
        $arguments = [
            'rdid' => $rdid,
            'key' => 'TCSOFT_INTERLIB'
        ];
        $response = $opacSoap->requestFunction('getReader', $arguments);
        if (Arr::get($response, 'errMes')) {                                //...异常处理
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $password = $response['return']['rdPasswd'];
        $arguments = [
            'password' => md5($password),
            'rdid' => $rdid,
            'doPage' => false,
            'toPage' => 1,
            'pageSize' => 100
        ];
        //初始化数据   start
        $success = [
            'history' => [],
            'current' => []
        ];

        //end
        $opacSoap = OpacSoap::make($wxuser, 'webservice/prelendWebservice');

        //馆藏列表
        $url = $wxuser['opacurl'] . 'api/holding/1';
        $holdingsResponse = OpacService::request($url);
        if (empty($holdingsResponse)) {
            return $this->failed('opac接口异常,请稍后再试', 400);
        }
        if (is_array($holdingsResponse) && Arr::has($holdingsResponse, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $holdingsResponse = json_decode($holdingsResponse, true);
        $localMap = $holdingsResponse['localMap'];
        $libcodeMap = $holdingsResponse['libcodeMap'];

        $currentResponse = $opacSoap->requestFunction('getCurrentPrelendList', $arguments);
        if (!empty($currentResponse)) {
            $currentResponse = $currentResponse['return'];
            if (Arr::get($currentResponse, 'barcode')) {
                $isbn = Arr::get($currentResponse, 'isbn');
                if ($isbn) {
                    $isbn_meta = str_replace('-', '', $isbn);
                    $currentResponse['imgurl'] = CoverService::search($isbn_meta);
                }
                $regLocal = $currentResponse['locationCode'] ?: $currentResponse['regLocal'];
                $currentResponse['locationcode_str'] = Arr::get($localMap, $regLocal, '...');    //取书地点
                $currentResponse['regLocal_str'] = Arr::get($libcodeMap, $currentResponse['libcode'], '...') . '-' .
                    Arr::get($localMap, $currentResponse['regLocal'], '...');

                $success['current'][] = $currentResponse;
            } else {
                $isbnImg = [];
                foreach ($currentResponse as $k => $v) {
                    if (isset($v['isbn'])) {
                        $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
                    }
                }
                unset($k, $v);
                $cover = CoverService::search($isbnImg);

                foreach ($currentResponse as $k => $v) {
                    $regLocal = $v['locationCode'] ?: $v['regLocal'];
                    $currentResponse[$k]['locationcode_str'] = Arr::get($localMap, $regLocal, '...');  //取书地点
                    $currentResponse[$k]['regLocal_str'] = Arr::get($libcodeMap, $v['libcode'], '...') . '-' .
                        Arr::get($localMap, $v['regLocal'], '...');

                    $currentResponse[$k]['imgurl'] = '';
                    if (isset($v['isbn'])) {
                        $currentResponse[$k]['imgurl'] = Arr::get($cover, $v['isbn'], '');
                    }
                }
                unset($k, $v);
                $success['current'] = $currentResponse;
            }
        }

        $historyResponse = $opacSoap->requestFunction('getHistoryPrelendList', $arguments);
        if (!empty($historyResponse)) {
            $historyResponse = $historyResponse['return'];
            if (Arr::get($historyResponse, 'barcode')) {
                $isbn = Arr::get($historyResponse, 'isbn');
                if ($isbn) {
                    $isbn_meta = str_replace('-', '', $isbn);
                    $historyResponse['imgurl'] = CoverService::search($isbn_meta);
                }
                $success['history'][] = $historyResponse;
            } else {
                $isbnImg = [];
                foreach ($historyResponse as $k => $v) {
                    if (isset($v['isbn'])) {
                        $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
                    }
                }
                unset($k, $v);
                $cover = CoverService::search($isbnImg);

                foreach ($historyResponse as $k => $v) {
                    $historyResponse[$k]['imgurl'] = '';
                    if (isset($v['isbn'])) {
                        $historyResponse[$k]['imgurl'] = Arr::get($cover, $v['isbn'], '');
                    }
                }
                unset($k, $v);
                $success['history'] = $historyResponse;
            }
        }
        return $this->success($success, true);
    }

    public function mySearchreslist(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $rdid = Reader::checkBind($openid, $token)->value('rdid');
        if ($rdid == null) return $this->failed('尚未绑定读者证!', 401);

        $wxuser = Wxuser::getCache($token);

        $openlibService = OpenlibService::make($token, $wxuser);

        $response = $openlibService->searchreslist($rdid);
        if ($response['success'] == false) {
            return $this->failed(Arr::get($response, 'messagelist.0.message'), 400);
        }
        //馆藏列表
        $url = $wxuser['opacurl'] . 'api/holding/1';
        $holdingsResponse = OpacService::request($url);
        if (empty($holdingsResponse)) {
            return $this->failed('opac接口异常,请稍后再试', 400);
        }
        if (is_array($holdingsResponse) && Arr::has($holdingsResponse, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $holdingsResponse = json_decode($holdingsResponse, true);
        $localMap = $holdingsResponse['localMap'];
        $libcodeMap = $holdingsResponse['libcodeMap'];

        $history = [];
        $current = [];
        if (count($response['reslist']) > 0) {
            $reslist = $response['reslist'];

            $isbnImg = [];
            foreach ($reslist as $k => $v) {
                if (isset($v['isbn'])) {
                    $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
                }
            }
            unset($k, $v);

            $isbnImg = CoverService::search($isbnImg);

            foreach ($reslist as $k => $v) {
                $v['imgurl'] = '';
                if (isset($v['isbn'])) {
                    $v['imgurl'] = $isbnImg[$v['isbn']] ?? '';
                }
                $v['pickuplocal_str'] = '';
                if (isset($v['pickuplocal'])) {
                    $v['pickuplocal_str'] = $localMap[$v['pickuplocal']] ?? '';
                }
                $v['reslib_str'] = $libcodeMap[$v['reslib']] ?? '';

                if ($v['state'] == '1' || $v['state'] == '2') {
                    $current[] = $v;
                    continue;
                }
                $history[] = $v;
            }
            unset($k, $v);
        }
        return $this->success(['history' => $history, 'current' => $current], true);
    }

}
