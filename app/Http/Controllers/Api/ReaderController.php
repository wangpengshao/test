<?php

namespace App\Http\Controllers\Api;

use App\Jobs\EsAddRecords;
use App\Models\Wechat\BindLog;
use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\Reader;
use App\Models\Wxuser;
use App\Services\CoverService;
use App\Services\FansEvent;
use App\Services\JybService;
use App\Services\MenuService;
use App\Services\OpacService;
use App\Services\OpacSoap;
use App\Services\OpenlibService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReaderController extends BaseController
{
    public function openidGetReader(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $where = [
            'token' => $token,
            'openid' => $openid
        ];
        $cardList = Reader::where($where)->orderBy('is_bind', 'desc')
            ->get(['name', 'id', 'rdid', 'token', 'is_bind', 'created_at']);

        $borrowStatus = [
            'a' => 0,  //在借中
            'b' => 0,  //将过期
            'c' => 0   //已过期
        ];
        //判断是否存在绑定关系
        $currentBind = $cardList->firstWhere('is_bind', 1);

        if ($currentBind) {
            $openlibService = OpenlibService::make($token);
            $response = $openlibService->currentloan($currentBind->rdid);

            if ($response['success']) {
                $loanlist = $response['loanlist'];
                $now = Carbon::now();
                foreach ($loanlist as $k => $v) {
                    $compareTime = Carbon::parse($v['returndate']);
                    $differDay = $now->diffInDays($compareTime, false);
                    if ($differDay < 0) {
                        $borrowStatus['c']++;
                    } elseif ($differDay >= 0 && $differDay < 15) {
                        $borrowStatus['b']++;
                        $borrowStatus['a']++;
                    } else {
                        $borrowStatus['a']++;
                    }
                }
            }
        }

        $success = [
            'cardList' => $cardList,
            'borrowStatus' => $borrowStatus,
        ];
        return $this->success($success, true);
    }

    public function bindReader(Request $request)
    {
        if (!$request->filled(['username', 'password', 'token'])) return $this->failed('缺少必须参数!');

        $token = $request->input('token');
        $username = $request->input('username');
        $password = $request->input('password');
        $forceCode = $request->input('forceCode');
        $openid = $request->user()->openid;

        if ($token != $request->user()->token) return $this->failed('非法token!');

        if ($forceCode) {
            $forceCode = decrypt($forceCode);
            if ($forceCode['r'] != $username || $forceCode['o'] != $openid || time() - $forceCode['t'] > 600) {
                return $this->failed('非法forceCode!');
            }
        }

        $OpenlibService = OpenlibService::make($token);
        $response = $OpenlibService->confirmreader($username, $password);        //读者证认证
        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        //判断是否存在已绑定数据
        if (Reader::checkBind($openid, $token)->exists()) {
            return $this->message('抱歉,您已经绑定过读者证了!', false);
        }
        //检查证件状态是否有效
        $reader = $OpenlibService->searchreader($username);
        if ($reader['rdcfstate'] != 1 || $reader['rdenddate'] < date('Y-m-d')) {
            return $this->message('抱歉,该证不是有效状态,无法进行绑定!', false);
        }
        //一个读者证只能被一个微信号绑定  (普通绑定 || 顶号绑定)
        if (empty($forceCode) && Reader::rdidGetBind($username, $token)->exists()) {
            $forceCode = encrypt(['r' => $username, 'o' => $openid, 't' => time()]);
            $success = [
                'message' => '抱歉,该证号已被微信用户绑定了!',
                'forceCode' => $forceCode
            ];
            return $this->success($success, false);
        }
        //删除自己存在保存中的证件
        Reader::where([
            'token' => $token,
            'openid' => $openid,
            'rdid' => $username,
            'is_bind' => 0
        ])->delete();

        //顶号处理
        if ($forceCode) {
            Reader::rdidGetBind($forceCode['r'], $token)->update(['is_bind' => 0]);
        }
        //新增绑定
        $create = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $username,
            'password' => encrypt($password),
            'is_bind' => 1,
            'name' => $reader['rdname']
        ];
        $status = Reader::create($create);
        if ($status == false) {
            return $this->internalError('服务器繁忙，请稍后再试!');
        }
        $response = ['id' => $status->id, 'url' => '', 'typeData' => '', 'typeName' => ''];
        //链接绑定跳转
        if ($request->has('menuId')) {
            $indexMenu = IndexMenu::getCache($token, $request->input('menuId'));
            if ($indexMenu) {
                $menuService = MenuService::make('self', $token);
                $url = $menuService->returnUrl($indexMenu, $request->user()->toArray());
                $response['url'] = $url;
                unset($menuService);
            }
        }
        //关联事件统一处理  ====>>  集卡特殊处理
        $eventData = Cache::get('fEvent:' . $token . ':' . $openid);
        if (!$eventData) {
            $eventService = new FansEvent($token, $openid);
            $eventData = $eventService->check('bindReader');
        }
        if ($eventData) {
            $response = array_merge($response, $eventData);
        }

        $log = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $status['rdid'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 1
        ];
        DB::table('admin_wechat_reader_log')->insert($log);
        return $this->success($response, true);
    }


    public function checkBindReader(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $success = [
            'id' => '',
            'rdid' => '',
            'url' => ''
        ];
        if ($request->filled('menuId')) {
            $menuID = $request->input('menuId');
            $indexMenu = IndexMenu::getCache($token, $menuID);
            if ($indexMenu) {
                $menuService = MenuService::make('self', $token);
                $url = $menuService->returnUrl($indexMenu, $request->user()->toArray(), 'prompt');
                if ($url === 1) {
                    return $this->message('尚未绑定读者证!', false);
                }
                $success['url'] = $url;
                //添加访问记录到ES  考虑加入队列!
                $builderData = [
                    'token' => $token,
                    'openid' => $openid,
                    'created_at' => time(),
                    'mid' => $menuID,
                    'hour' => (int)date('H'),
                    'id' => (string)Str::uuid()
                ];
                EsAddRecords::dispatch('wechat_menu_log', $builderData);
                unset($menuService);
            }
        } else {
            $reader = Reader::userGetBind($request->user())->first(['id', 'rdid']);
            if ($reader == null) return $this->message('尚未绑定读者证!', false);
            $reader->url = '';
            $success = $reader;
        }
        return $this->success($success, true);
    }

    public function openidGetReaderInfo(Request $request)
    {
        $reader = $this->firstBind($request, ['rdid']);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $OpenlibService = OpenlibService::make($request->user()->token);
        $response = $OpenlibService->searchreader($reader->rdid);
        $exceptArray = [
            'rdpasswd', 'rdsort1', 'rdsort2', 'rdsort3', 'rdsort4', 'rdsort5', 'rdnation', 'rdnative',
            'rdpostcode', 'rdremark', 'rdunit', 'rdglobal'
        ];
        $returnData = Arr::except($response, $exceptArray);
        return $this->success($returnData, true);
    }

    public function removeBindReader(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $reader->is_bind = 0;
        $status = $reader->save();
        if ($status == false) {
            return $this->internalError('服务器繁忙，请稍后再试!');
        }
        $log = [
            'token' => $request->user()->token,
            'openid' => $request->user()->openid,
            'rdid' => $reader['rdid'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 0
        ];
        DB::table('admin_wechat_reader_log')->insert($log);
        return $this->message('解绑成功!', true);
    }

    //切换证件 && 绑定接口
    public function toggleBindReader(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $where = [
            'id' => $request->route('id'),
            'token' => $token,
            'openid' => $openid,
            'is_bind' => 0
        ];
        $reader = Reader::where($where)->first();
        if (empty($reader)) return $this->failed('非法访问,数据不存在!');

        //forceCode 验证
        $forceCode = $request->input('forceCode');
        if ($forceCode) {
            $forceCode = decrypt($forceCode);
            if ($forceCode['r'] != $reader['rdid'] || $forceCode['o'] != $openid || time() - $forceCode['t'] > 600) {
                return $this->failed('非法forceCode!');
            }
        }
        //一个读者证只能被一个微信号绑定  (普通绑定 || 顶号绑定)
        if (empty($forceCode) && Reader::rdidGetBind($reader['rdid'], $token)->exists()) {
            $forceCode = encrypt(['r' => $reader['rdid'], 'o' => $openid, 't' => time()]);
            $success = [
                'message' => '抱歉,该证号已被微信用户绑定了!',
                'forceCode' => $forceCode
            ];
            return $this->success($success, false);
        }
        //认证读者  密码错误情况,手动重新输入密码
        if ($request->filled('password')) {
            $reader->password = encrypt($request->input('password'));
        }

        $openlibService = OpenlibService::make($token);
        $response = $openlibService->confirmreader($reader['rdid'], decrypt($reader->password));
        if ($response['success'] == false) {
            $success = [
                'message' => '绑定失败，密码错误，请重新输入!',
                'type' => 'invalidPassword'
            ];
            return $this->success($success, false);
        }

        $response = $openlibService->searchreader($reader['rdid']);
        if ($response['rdcfstate'] != 1 || $response['rdenddate'] < date('Y-m-d')) {
            return $this->message('抱歉,该证不是有效状态,无法进行绑定!', false);
        }
        //顶号处理
        if ($forceCode) {
            Reader::rdidGetBind($forceCode['r'], $token)->update(['is_bind' => 0]);
        }

        $log = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $reader['rdid'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 0
        ];

        $currentBind = Reader::checkBind($openid, $token)->first(['id', 'is_bind', 'rdid']);
        if (empty($currentBind)) {
            $reader->is_bind = 1;
            $reader->save();
            //增加读者证绑定记录
            $log['type'] = 1;
            DB::table('admin_wechat_reader_log')->insert($log);
            return $this->message('绑定成功', true);
        }

        $currentBind->is_bind = 0;
        $unStatus = $currentBind->save();
        //增加读者证解绑记录
        $log['rdid'] = $currentBind['rdid'];
        DB::table('admin_wechat_reader_log')->insert($log);

        if ($unStatus) {
            $reader->is_bind = 1;
            $status = $reader->save();
            if ($status == false) {
                return $this->internalError('出错了，请稍后再试!');
            }
            //增加读者证绑定记录
            $log['rdid'] = $reader['rdid'];
            $log['type'] = 1;
            DB::table('admin_wechat_reader_log')->insert($log);
        }
        return $this->message('切换成功!', true);
    }

    public function deleteBindReader(Request $request)
    {
        //判断是否合法
        $reader = Reader::where('id', $request->route('id'))->first(['id', 'token', 'openid']);
        if ($reader == false || $reader->token != $request->user()->token || $reader->openid != $request->user()->openid) {
            return $this->failed('数据不存在!', 400);
        }
        $status = $reader->delete();
        if ($status == false) {
            return $this->internalError('出错了，请稍后再试!');
        }
        return $this->message('操作成功', true);
    }

    //存卡
    public function addBindReader(Request $request)
    {
        if (!$request->filled(['username', 'password'])) {
            return $this->failed('账号跟密码不能为空!', 400);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $username = $request->input('username');
        $password = $request->input('password');
        //读者证认证
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->confirmreader($username, $password);
        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }

        $response = $openlibService->searchreader($username);
        if ($response['rdcfstate'] != 1 || $response['rdenddate'] < date('Y-m-d')) {
            return $this->message('抱歉,该证不是有效状态,无法进行保存!', false);
        }
//        $where = [
//            'token' => $token,
//            'rdid' => $username,
//            'is_bind' => 1
//        ];
//        if (Reader::where($where)->exists()) {
//            return $this->message('抱歉,此证号已被绑定使用中!', false);
//        }
        $onlyWhere = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $username,
        ];
        if (Reader::where($onlyWhere)->exists()) {
            return $this->message('你已添加此证了,无法重复添加!', false);
        }
        //存卡
        $create = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $username,
            'password' => encrypt($password),
            'is_bind' => 0,
            'name' => $response['rdname']
        ];
        $status = Reader::create($create);
        if ($status == false) {
            return $this->internalError('服务器繁忙，请稍后再试!');
        }
        return $this->success(['id' => $status->id]);
    }

    // 读者当前借阅
    public function getCurrentloan(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $token = $request->user()->token;
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->currentloan($reader['rdid']);
        $loanlist = [];
        if ($response['success']) {
            $loanlist = $response['loanlist'];
            $isbnImg = [];
            foreach ($loanlist as $k => $v) {
                if (isset($v['isbn'])) {
                    $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
                }
            }
            unset($k, $v);
            $isbnImg = $this->giveImgApi($isbnImg);

            foreach ($loanlist as $k => $v) {
                $loanlist[$k]['imgurl'] = '';
                if (isset($v['isbn'])) {
                    $loanlist[$k]['imgurl'] = (isset($isbnImg[$v['isbn']])) ? $isbnImg[$v['isbn']] : '';
                }
            }
            unset($k, $v);
        }
        return $this->success($loanlist);
    }

    //读者历史借阅
    public function getHistoryloan(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $startdate = $request->input('startdate') ?: '2017-01-01';
        $enddate = $request->input('enddate') ?: date('Y-m-d');
        $page = $request->input('page') ?: 1;
        $rows = $request->input('rows') ?: 20;

        $token = $request->user()->token;
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->historyloan($reader['rdid'], $startdate, $enddate, $page, $rows);
        $hloanlist = [
            'total' => 0,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'page' => $page,
            'rows' => $rows,
            'list' => []
        ];
        if ($response['success']) {
            $dataList = $response['hloanlist'];
            $isbnImg = [];
            foreach ($dataList as $k => $v) {
                if (isset($v['isbn'])) {
                    $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
                }
            }
            unset($k, $v);

            $isbnImg = $this->giveImgApi($isbnImg);
            foreach ($dataList as $k => $v) {
                $dataList[$k]['imgurl'] = '';
                if (isset($v['isbn'])) {
                    $dataList[$k]['imgurl'] = (isset($isbnImg[$v['isbn']])) ? $isbnImg[$v['isbn']] : '';
                }
            }
            unset($k, $v);
            if ($response['total'] > 0) {
                $hloanlist = [
                    'total' => $response['total'],
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'page' => $page,
                    'rows' => $rows,
                    'list' => $dataList
                ];
            }
        }
        return $this->success($hloanlist);
    }

    //续借
    public function readerRenewbook(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $barcode = $request->route('barcode') ?: '';
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->renewbook($reader['rdid'], $barcode);
        $messagelist = Arr::get($response, 'messagelist');
        $successMes = [];
        $failureMes = [];
        if (!empty($messagelist) && count($messagelist) > 0) {
            foreach ($messagelist as $k => $v) {
                if ($v['code'] == 'R00108') {
                    $successMes[] = $v['message'];
                    continue;
                }
                $failureMes[] = $v['message'];
            }
            unset($k, $v);
        }
        $response = ['successMes' => $successMes, 'failureMes' => $failureMes, 'typeData' => '', 'typeName' => ''];
        if (count($successMes) > 0) {
            $eventService = new FansEvent($token, $openid);
            $eventData = $eventService->check('readerRenewbook');
            if ($eventData) {
                $response = array_merge($response, $eventData);
            }
        }
        return $this->success($response, true);
    }

    //openlib检索
    public function bookSearchbib(Request $request)
    {
        if (!$request->filled(['queryparam', 'queryvalue'])) {
            return $this->failed('检索类型或检索值不能为空!!', 400);
        }
        if (!$request->filled('libcode')) {
            return $this->failed('缺少分馆代码!!', 400);
        }

        $response = $this->searchbib($request->user()->token, $request);

        $searchList = [];
        if ($response['success']) {
            $searchList = $response['pagelist'];
            $isbnImg = [];
            foreach ($searchList as $k => $v) {
                if (isset($v['isbn'])) {
                    $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
                }
            }
            unset($k, $v);
            $isbnImg = $this->giveImgApi($isbnImg);

            foreach ($searchList as $k => $v) {
                $searchList[$k]['imgurl'] = '';
                if (isset($v['isbn'])) {
                    $searchList[$k]['imgurl'] = (isset($isbnImg[$v['isbn']])) ? $isbnImg[$v['isbn']] : '';
                }
            }
            unset($k, $v);
            if ($response['total'] > 0) {
                $searchList = [
                    'total' => $response['total'],
                    'page' => $request->input('page', 1),
                    'rows' => $request->input('rows', 10),
                    'list' => $searchList
                ];
            }
        }
        return $this->success($searchList);
    }

    //分馆列表
    public function libSecondaryList(Request $request)
    {
        $openlibService = OpenlibService::make($request->input('token'));
        $response = $openlibService->getlibSecondaryList();
        if ($response['success'] == false) {
            return $this->failed(Arr::get($response, 'messagelist.0.message'), 400);
        }
        return $this->success($response['pagedata'], true);
    }

    //预借
    public function registerprelend(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        if (!$request->route('barcode')) {
            return $this->failed('缺少图书条码号!!', 400);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $locationcode = $request->input('locationcode', '');

        $openlibService = OpenlibService::make($token);
        $response = $openlibService->sendRegisterprelend($reader['rdid'], $request->route('barcode'), $locationcode);

        $message = Arr::get($response, 'messagelist.0.message');
        if ($response['success'] == false) {
            return $this->message($message, false);
        }
        $response = ['message' => $message, 'typeData' => '', 'typeName' => ''];
        $eventService = new FansEvent($token, $openid);
        $eventData = $eventService->check('registerprelend');
        if ($eventData) {
            $response = array_merge($response, $eventData);
        }
        return $this->success($response, true);
    }

    //取消预借
    public function cancelprelend(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        if (!$request->route('barcode')) {
            return $this->failed('缺少图书条码号!!', 400);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $openlibService = OpenlibService::make($token);
        $response = $openlibService->sendCancelprelend($reader['rdid'], $request->route('barcode'));
        $message = Arr::get($response, 'messagelist.0.message');
        if ($response['success'] == false) {
            return $this->message($message, false);
        }
        return $this->message($message, true);
    }

    //预约
    public function registerreserve(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $barcode = $request->route('barcode');
        if (!$barcode) {
            return $this->failed('缺少图书条码号!!', 400);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $piclocal = $request->input('piclocal', '');         //预约取书地点
        $canceldate = $request->input('canceldate', '');         //取消时间

        $openlibService = OpenlibService::make($token);

        $response = $openlibService->sendRegisterreserve($reader['rdid'], $barcode, $canceldate, $piclocal);
        $message = Arr::get($response, 'messagelist.0.message');
        if ($response['success'] == false) {
            return $this->message($message, false);
        }
        $response = ['message' => $message, 'typeData' => '', 'typeName' => ''];
        $eventService = new FansEvent($token, $openid);
        $eventData = $eventService->check('registerreserve');
        if ($eventData) {
            $response = array_merge($response, $eventData);
        }
        return $this->success($response, true);
    }

    //取消预约
    public function cancelreserve(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $barcode = $request->route('barcode');
        if (!$barcode) {
            return $this->failed('缺少图书条码号!!', 400);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->sendCancelreserve($reader['rdid'], $barcode);
        $message = Arr::get($response, 'messagelist.0.message');

        if ($response['success'] == false) {
            return $this->message($message, false);
        }
        return $this->message($message, true);
    }

    //馆藏列表
    public function getBookHolding(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->getQueryHolding($request);
        //type区分 预约 或 预借
        $type = $request->input('type');

        $holding = [];
        if ($response['success'] && isset($response['holdingList'])) {
            $holdingList = $response['holdingList'];
            switch ($type) {
                case 1:
                    $stateArr = [2];
                    break;
                case 2:
                    $stateArr = [3];
                    break;
                default:
                    $stateArr = [];
            };
            if (count($stateArr) > 0) {
                foreach ($holdingList as $k => $v) {
                    if (in_array($v['state'], $stateArr)) {
                        $holding[] = $v;
                    }
                }
                unset($k, $v);
            } else {
                $holding = $holdingList;
            }
        }
        return $this->success($holding);
    }

    public function getReaderQrcode(Request $request, JybService $jybService)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $token = $request->user()->token;
        $wxuser = Wxuser::getCache($token);
        if ($wxuser['qr_type'] === 0) {
            return $this->failed('读者二维码功能未开启!!', 400);
        }

        if ($wxuser['qr_type'] === 1) {
            $time = date('Ymd');
            $ticket = md5($reader['rdid'] . $time . $wxuser['glc']);
            $url = $wxuser['opacurl'] . 'reader/getReaderQrcode?';
            $url .= http_build_query([
                'rdid' => $reader['rdid'],
                'time' => $time,
                'ticket' => $ticket
            ]);
            $response = OpacService::request($url);
            if (empty($response)) {
                return $this->failed('opac接口异常,请稍后再试', 400);
            }
            if (is_array($response) && Arr::get($response, 'errMes')) {
                return $this->failed(Arr::get($response, 'errMes'), 400);
            }
            $response = json_decode($response, true);
            if ($response['flag'] == 1) {
                return $this->success(['qrcode' => $response['qrcode']]);
            }
        }

        if ($wxuser['qr_type'] === 2) {
            $response = $jybService->getElectronicCard($wxuser, $reader['rdid']);

            if ($response['code'] == 200) {
                return $this->success(['qrcode' => $response['uuid']]);
            }
        }
        return $this->internalError('服务器繁忙，请稍后再试!');

    }

    public function readTheCharts(Request $request)
    {
        $libcode = $request->input('libcode', '');
        $days = $request->input('days', 20);
        $token = $request->input('token');
        $wxuser = Wxuser::getCache($token);

        $url = $wxuser['opacurl'] . 'ranking/readerLoanRank/json?';
        $url .= http_build_query([
            'libcode' => $libcode,
            'limitDays' => $days,
        ]);
        $response = OpacService::request($url);
        if (empty($response)) {
            return $this->failed('opac接口异常,请稍后再试', 400);
        }
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $response = json_decode($response, true);
        $success = [];
        if (is_array($response[0])) {
            foreach ($response as $k => $v) {
                $success[] = [
                    'rdid' => $v['reader']['rdid'],
                    'totalNum' => $v['totalNum'],
                    'rdName' => $v['reader']['rdName'],
                ];
            }
        }
        return $this->success($success, true);
    }

    public function bookTheCharts(Request $request)
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
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
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

    public function lossCard(Request $request)
    {
        $password = $request->input('password');
        if (!$password) {
            return $this->failed('缺少必填参数!!', 400);
        }
        $username = $request->input('username');
        if (!$username) {
            $reader = $this->firstBind($request);
            if ($reader == false) {
                return $this->failed('尚未绑定读者证!', 401);
            }
            $username = $reader['rdid'];
        }
        $token = $request->user()->token;
//        $openid = $request->user()->openid;
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->confirmreader($username, $password);
        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        $response = $openlibService->cardmanage($username, 3);
        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        return $this->message('证挂失成功', true);

    }

    public function loginResetReader(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $rdid = Reader::checkBind($openid, $token)->value('rdid');
        if (empty($rdid)) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $wxuser = Wxuser::getCache($token);
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
        $rdPasswd = $response['return']['rdPasswd'];
        $oldEndDate = $response['return']['endDate'];

        $arguments = [
            'rdid' => $rdid,
            'password' => md5($rdPasswd)
        ];
        $response = $opacSoap->requestFunction('loginByRdid', $arguments);
        if (Arr::get($response, 'errMes')) {                        //...异常处理
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }

        if (Arr::get($response, 'return')) {
            $arguments = [
                'rdid' => $rdid,
                'key' => 'TCSOFT_INTERLIB'
            ];
            $response = $opacSoap->requestFunction('getReader', $arguments);
            if ($oldEndDate != $response['return']['endDate']) {
                //对比过期时间,如果不相同说明读者证已延期
                return $this->message('更新成功,读者证已延期', true);
            }
            return $this->message('更新成功', true);
        }
        return $this->message('更新失败', false);
    }

    public function getReaderBindLog(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $rows = $request->input('rows', 10);

        $rdid = Reader::checkBind($openid, $token)->value('rdid');
        if (empty($rdid)) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $bindLog = BindLog::where(['token' => $token, 'rdid' => $rdid])
            ->with(['fansInfo' => function ($query) use ($token) {
                $query->where('token', $token);
            }])->orderBy('id', 'desc')->paginate($rows);
        return $bindLog;
    }

}
