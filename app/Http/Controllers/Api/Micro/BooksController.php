<?php

namespace App\Http\Controllers\Api\Micro;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wxuser;
use App\Services\ArrayService;
use App\Services\CoverService;
use App\Services\MiniResources;
use App\Services\OpacService;
use App\Services\OpacSoap;
use App\Services\XmlService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * 书目相关
 * Class BooksController
 * @package App\Http\Controllers\Api\Micro
 */
class BooksController extends Controller
{
    use ApiResponse;

    /**
     * 检索书目 (普通检索、集群检索)
     * @param Request $request
     * @return mixed
     */
    public function search(Request $request)
    {
//        $openid = $request->user()->openid;
        $token = $request->user()->token;
        if (!$request->filled(['searchWay0', 'q0'])) {
            return $this->failed('检索类型或检索值不能为空!!', 400);
        }
        $wxuser = Wxuser::getCache($token);
        //公用的参数
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $libcode = $request->input('libcode', '');
        $params = [
            'q' => $request->input('q0'),
            'searchWay' => $request->input('searchWay0'),
            'isFacet' => 'true',
            'curlibcode' => $libcode,
            'rows' => $rows,
            'page' => $page,
        ];
        if ($wxuser['is_cluster'] == 1) {  //判断是否集群检索
            $wxuser['opacurl'] = $request->input('opacurl', $wxuser['opacurl']);   //opac_url
        }
        if ($request->filled(['q1', 'searchWay1'])) {
            $params['logical0'] = 'AND';
            $params['searchWay0'] = 'marc';
            $params[$request->input('searchWay1')] = $request->input('q1');
//            $params += $request->only(['q1', 'searchWay1']);
        }
        if ($request->filled(['q2', 'searchWay2'])) {
            $params['logical0'] = 'AND';
            $params['searchWay0'] = 'marc';
            $params[$request->input('searchWay2')] = $request->input('q2');
//            $params += $request->only(['q2', 'searchWay2']);
        }
        $url = $wxuser['opacurl'] . 'api/search?' . http_build_query($params);
        $response = OpacService::request($url);

        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $response = XmlService::loadOpacXML($response);
        $responseStatus = $response['response']['response'];
        $facet_counts = $response['response']['facet_counts']['facet_fields'];

        //返回分馆列表 start
        $libcodeList = [];
        $opacSoap = OpacSoap::make(['token' => $token, 'opacurl' => $wxuser['opacurl']], 'webservice/loanWebservice');
        $libResponse = $opacSoap->requestFunction('getLibcodeList');
        if (!Arr::has($libResponse, 'errMes')) {                        //...异常处理
            $libResponse = $libResponse['return'];
            foreach ($libResponse as $k => $v) {
                $libcodeList[$v['libcode']] = $v['name'];
            }
        }
        //返回分馆列表 end

        $success = [
            'numFound' => $responseStatus['numFound'],
            'start' => $responseStatus['start'],
            'rows' => $rows,
            'page' => $page,
            'filter' => Arr::only($facet_counts, ['curlibcode', 'f_author', 'f_pubdate']),
            'bookList' => [],
            'libcodeList' => $libcodeList
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
                $bookList['imgurl'] = CoverService::search($isbn_meta);
            }
            $success['bookList'][] = $bookList;
            return $this->success($success, true);
        }

        $bookList = $responseStatus['doc'];
        $isbnImg = [];
        foreach ($bookList as $k => $v) {
            if (isset($v['isbn_meta'])) {
                $isbnImg[$v['isbn_meta']] = $v['isbn_meta'];
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

    /**
     * 书目详情 (普通检索、集群检索)
     * @param Request $request
     * @return mixed
     */
    public function details(Request $request)
    {
        $token = $request->user()->token;
        $wxuser = Wxuser::getCache($token);

        if ($wxuser['is_cluster'] == 1 && $request->filled('opacurl')) {  //判断是否集群检索
            $wxuser['opacurl'] = $request->input('opacurl'); //opac_url
        }
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
            ],
            'miniResources' => []
        ];
        //初始化数据  end
        $isbn = Arr::get($response, 'biblios.isbn');
        if ($isbn) {   //详情内容
            $biblios = $response['biblios'];
            $cover = CoverService::bookInfoLv2($isbn);
            $success['miniResources'] = MiniResources::searchResources($wxuser['id'], $isbn);
            $template = [
                'tags' => '', 'originTitle' => '', 'binding' => '', 'translator' => '', 'catalog' => '',
                'subtitle' => '', 'alt' => '', 'url' => '', 'altTitle' => '', 'smallImage' => '', 'mediumImage' => '',
                'largeImage' => '', 'smallSourceImage' => '', 'mediumSourceImage' => '', 'largeSourceImage' => '',
                'rating' => '', 'createTime' => '', 'updateTime' => '',
                'isbn' => $biblios['isbn'],
                'title' => $biblios['title'],
                'author' => $biblios['author'],
                'pages' => $biblios['page'],
                'pubdate' => $biblios['pubdate'],
                'publisher' => $biblios['publisher'],
                'price' => $biblios['price'],
                'sourceImage' => isset($cover['sourceImage']) ? $cover['sourceImage'] : '',
                'image' => isset($cover['image']) ? $cover['image'] : '',
                'summary' => isset($cover['summary']) ? $cover['summary'] : '',
                'authorIntro' => isset($cover['authorIntro']) ? $cover['authorIntro'] : '',
            ];
            $success['details'] = $template;
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

    /**
     * 新书通报
     * @param Request $request
     * @return mixed
     */
    public function newBookReport(Request $request)
    {
        $rows = $request->input('rows', 10);
        $start = $request->input('start', 1);
        $token = $request->input('token');

        $wxuser = Wxuser::getCache($token);
        if ($wxuser['newbook_sw'] != 1) {
            return $this->failed('新书通报已关闭使用!!', 400);
        }
        $opacSoap = OpacSoap::make($wxuser->only('token', 'opacurl'), 'webservice/newPubWebservice');
        $arguments = [
            'limitDays' => 720,
            'rows' => $rows,
            'start' => $start,
//            'libcode' => $wxuser['libcode']
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

    /**
     * 热门图书 | 图书借阅排行榜
     * @param Request $request
     * @return mixed
     */
    public function popularBooks(Request $request)
    {
        $token = $request->input('token');
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $params = http_build_query([
            'q0' => '',
            'searchWay0' => '',
            'isFacet' => 'true',
            'rows' => $rows,
            'page' => $page,
            'hasholding' => 1,
            'sortOrder' => 'desc',
            'sortWay' => 'loannum_sort',
            'wt' => 'json'
        ]);
        $wxuser = Wxuser::getCache($token);
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
        $success = [
            'numFound' => $responseStatus['numFound'],
            'start' => $responseStatus['start'],
            'rows' => $rows,
            'page' => $page,
            'bookList' => [],
        ];
        $numFound = $responseStatus['numFound'];
        $start = $responseStatus['start'];
        //由于 numFound 为一的时候存在着数据结构差异。
        if ($numFound === "0" || $numFound <= $start) {
            return $this->success($success, true);
        }

        $bookList = $responseStatus['docs'];
        $isbnImg = [];
        foreach ($bookList as $k => $v) {
            if (isset($v['isbn_meta'])) {
                $isbnImg[$v['isbn_meta']] = $v['isbn_meta'];
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

    /**
     * 检索书目 (普通检索、集群检索)
     * @param Request $request
     * @return mixed
     */
    public function newSearch(Request $request)
    {
        $token = $request->user()->token;
        if (!$request->filled(['searchWay0', 'q0'])) {
            return $this->failed('检索类型或检索值不能为空!!', 400);
        }
        $wxuser = Wxuser::getCache($token);
        //公用的参数
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $libcode = $request->input('libcode', '');
        $params = [
            'q' => $request->input('q0'),
            'searchWay' => $request->input('searchWay0'),
            'isFacet' => 'true',
            'curlibcode' => $libcode,
            'rows' => $rows,
            'page' => $page,
            'wt' => 'json'
        ];
        if ($wxuser['is_cluster'] == 1) {  //判断是否集群检索
            $wxuser['opacurl'] = $request->input('opacurl', $wxuser['opacurl']);   //opac_url
        }
        if ($request->filled(['q1', 'searchWay1'])) {
            $params['logical0'] = 'AND';
            $params['searchWay0'] = 'marc';
            $params[$request->input('searchWay1')] = $request->input('q1');
//            $params += $request->only(['q1', 'searchWay1']);
        }
        if ($request->filled(['q2', 'searchWay2'])) {
            $params['logical0'] = 'AND';
            $params['searchWay0'] = 'marc';
            $params[$request->input('searchWay2')] = $request->input('q2');
//            $params += $request->only(['q2', 'searchWay2']);
        }
        $url = $wxuser['opacurl'] . 'api/search?' . http_build_query($params);
        $response = OpacService::request($url);

        if (empty($response)) {
            return $this->failed('opac接口异常,请稍后再试', 400);
        }
        if (is_array($response) && Arr::has($response, 'errMes')) {
            return $this->failed($response['errMes'], 400);
        }
        $response = json_decode($response, true);
        $responseStatus = $response['response'];
        $facet_counts = $response['facet_counts']['facet_fields'];
        $facet_counts = ArrayService::regroupParity($facet_counts);
        //返回分馆列表 start
        $libcodeList = [];
        $opacSoap = OpacSoap::make(['token' => $token, 'opacurl' => $wxuser['opacurl']], 'webservice/loanWebservice');
        $libResponse = $opacSoap->requestFunction('getLibcodeList');
        if (!Arr::has($libResponse, 'errMes')) {                        //...异常处理
            $libResponse = $libResponse['return'];
            foreach ($libResponse as $k => $v) {
                $libcodeList[$v['libcode']] = $v['name'];
            }
        }
        //返回分馆列表 end
        $success = [
            'numFound' => $responseStatus['numFound'],
            'start' => $responseStatus['start'],
            'rows' => $rows,
            'page' => $page,
            'filter' => Arr::only($facet_counts, ['curlibcode', 'f_author', 'f_pubdate']),
            'bookList' => [],
            'libcodeList' => $libcodeList
        ];
        $numFound = $responseStatus['numFound'];
        $start = $responseStatus['start'];

        //由于 numFound 为一的时候存在着数据结构差异。
        if ($numFound === "0" || $numFound <= $start) {
            return $this->success($success, true);
        }

        $bookList = $responseStatus['docs'];
        $isbnImg = [];
        foreach ($bookList as $k => $v) {
            if (isset($v['isbn_meta'])) {
                $isbnImg[$v['isbn_meta']] = $v['isbn_meta'];
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

    /**
     * 图书借阅排行榜
     * @param Request $request
     * @return mixed
     */
    public function rankingList(Request $request)
    {
        $libcode = $request->input('libcode', '');
        $days = $request->input('days', 20);
        $token = $request->input('token');

        $wxuser = Wxuser::getCache($token);
        $url = $wxuser['opacurl'] . 'ranking/bookLoanRank/json?';
        $url .= http_build_query([
            'libcode' => $libcode,
            'limitDays' => $days,
        ]);
        $response = OpacService::request($url);
        if (empty($response)) {
            return $this->failed('opac接口异常,请稍后再试', 400);
        }
        if (is_array($response) && Arr::has($response, 'errMes')) {
            return $this->failed($response['errMes'], 400);
        }
        $response = json_decode($response, true);
        $success = [];
        foreach ($response as $k => $v) {
            $success[] = [
                'author' => $v['biblios']['author'],
                'pubdate' => $v['biblios']['pubdate'],
                'publisher' => $v['biblios']['publisher'],
                'title' => $v['biblios']['title'],
                'isbn' => $v['biblios']['isbn'],
                'bookrecno' => $v['biblios']['bookrecno'],
                'totalNum' => $v['totalNum']
            ];
        }
        unset($k, $v);
        $isbnImg = [];
        foreach ($success as $k => $v) {
            if (isset($v['isbn'])) {
                $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
            }
        }
        $cover = CoverService::search($isbnImg);
        foreach ($success as $k => $v) {
            $success[$k]['imgurl'] = '';
            if (isset($v['isbn'])) {
                $success[$k]['imgurl'] = Arr::get($cover, $v['isbn'], '');
            }
        }
        return $this->success($success, true);
    }

    /**
     * 检索热门词
     * @param Request $request
     * @return mixed
     */
    public function hotWords(Request $request)
    {
        $token = $request->input('token');
        $cacheKey = 'hotWords:' . $token;
        $response = Cache::get($cacheKey);
        if ($response) return $this->success($response, true);

        $wxuser = Wxuser::getCache($token);
        $url = $wxuser['opacurl'] . 'hotsearch/keywordList?return_fmt=json';
        $response = OpacService::request($url);
        if (empty($response)) {
            return $this->failed('opac接口异常,请稍后再试', 400);
        }
        if (is_array($response) && Arr::has($response, 'errMes')) {
            return $this->failed($response['errMes'], 400);
        }
        $response = json_decode($response, true);
        Cache::put($cacheKey, $response, 30);

        return $this->success($response, true);

    }

}
