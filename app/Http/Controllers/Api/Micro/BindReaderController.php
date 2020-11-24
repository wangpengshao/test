<?php

namespace App\Http\Controllers\Api\Micro;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\EsAddRecords;
use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\Reader;
use App\Models\Wxuser;
use App\Services\FansEvent;
use App\Services\MenuService;
use App\Unified\ReaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * 微门户 vue前端 相关
 * Class BindReaderController
 * @package App\Http\Controllers\Api\Micro
 */
class BindReaderController extends Controller
{
    use ApiResponse;

    /**
     * 绑定页面 绑定读者
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
    public function bindReader(Request $request)
    {
        if (!$request->filled(['username', 'password', 'token'])) return $this->failed('lack of parameter!', 400);

        $token = $request->input('token');
        $username = $request->input('username');
        $password = $request->input('password');
        $forceCode = $request->input('forceCode');
        $openid = $request->user()->openid;

        if ($token != $request->user()->token) return $this->failed('token is invalid!', 400);

        if ($forceCode) {
            $forceCode = decrypt($forceCode);
            if ($forceCode['r'] != $username || $forceCode['o'] != $openid || time() - $forceCode['t'] > 600) {
                return $this->failed('forceCode is invalid!', 400);
            }
        }
        //判断是否存在已绑定数据
        if (Reader::checkBind($openid, $token)->exists()) {
            return $this->message('抱歉,您已经绑定过读者证了!', false);
        }

        $wxuser = Wxuser::getCache($token);

        $readerService = new ReaderService($wxuser);
        $response = $readerService->certification($username, $password);

        if ($response['status'] == false) {
            return $this->message($response['message'], false);
        }
        $reader = $response['data'];
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
        Reader::where(['token' => $token, 'openid' => $openid, 'rdid' => $username, 'is_bind' => 0])->delete();
        //顶号处理
        if ($forceCode) {
            Reader::rdidGetBind($forceCode['r'], $token)->update(['is_bind' => 0]);
        }
        //新增绑定
        $create = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $reader['rdid'],
            'password' => encrypt($password),
            'is_bind' => 1,
            'name' => $reader['name'],
            'origin_glc' => Arr::get($reader, 'origin_glc'),
            'origin_libcode' => Arr::get($reader, 'origin_libcode'),
            'is_cluster' => Arr::get($reader, 'is_cluster', 0)
        ];
        $createStatus = Reader::create($create);
        if ($createStatus == false) {
            return $this->internalError('服务器繁忙,绑定失败!');
        }
        $response = ['id' => $createStatus->id, 'url' => '', 'typeData' => '', 'typeName' => ''];
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
            'rdid' => $createStatus['rdid'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 1
        ];
        DB::table('admin_wechat_reader_log')->insert($log);
        return $this->success($response, true);
    }


    /**
     * 检查绑定
     * @param Request $request
     * @return mixed
     */
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
//                //添加访问记录到ES  考虑加入队列!
                $builderData = [
                    'token' => $token,
                    'openid' => $request->user()->openid,
                    'created_at' => date('Y-m-d H:i:s'),
                    'mid' => $menuID,
                    'name' => $indexMenu->caption,
                ];
                EsAddRecords::dispatch('menu', $builderData);
                unset($menuService);
            }
        } else {
            $reader = Reader::checkBind($openid, $token)->first(['id', 'rdid']);
            if ($reader == null) return $this->message('尚未绑定读者证!', false);
            $reader->url = '';
            $success = $reader;
        }
        return $this->success($success, true);
    }

    /**
     * 解除绑定
     * @param Request $request
     * @return mixed
     */
    public function unBindReader(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first();
        if (empty($reader)) return $this->failed('尚未绑定读者证!', 401);

        $reader->is_bind = 0;
        $reader->save();
        $log = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $reader['rdid'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 0
        ];
        DB::table('admin_wechat_reader_log')->insert($log);
        return $this->message('解绑成功!', true);
    }

    /**
     * 切换证件 || 绑定证号
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
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
        if (empty($reader)) return $this->failed('非法访问,数据不存在!', 400);

        //forceCode 验证
        $forceCode = $request->input('forceCode');
        if ($forceCode) {
            $forceCode = decrypt($forceCode);
            if ($forceCode['r'] != $reader['rdid'] || $forceCode['o'] != $openid || time() - $forceCode['t'] > 600) {
                return $this->failed('非法forceCode!', 400);
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

        $wxuser = Wxuser::getCache($token);
        $readerService = new ReaderService($wxuser);
        $response = $readerService->certification($reader['rdid'], decrypt($reader->password));
        if ($response['status'] == false) {
            $success = [
                'message' => $response['message'],
                'type' => 'invalidPassword'
            ];
            return $this->success($success, false);
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
            $reader->save();
            //增加读者证绑定记录
            $log['rdid'] = $reader['rdid'];
            $log['type'] = 1;
            DB::table('admin_wechat_reader_log')->insert($log);
        }
        return $this->message('切换成功!', true);
    }

    /**
     * 删除证号关联的数据
     * @param Request $request
     * @return mixed
     */
    public function deleteBindReader(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        //判断是否合法
        $reader = Reader::where('id', $request->route('id'))->first(['id', 'token', 'openid']);

        if ($reader == false || $reader->token != $token || $reader->openid != $openid) {
            return $this->failed('数据不存在!', 400);
        }
        $reader->delete();
        return $this->message('操作成功', true);
    }


    /**
     * 添加证号保存
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
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
        $wxuser = Wxuser::getCache($token);

        $readerService = new ReaderService($wxuser);
        $response = $readerService->certification($username, $password);

        if ($response['status'] == false) {
            return $this->message($response['message'], false);
        }
        $readerInfo = $response['data'];

        $onlyWhere = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $username,
        ];
        if (Reader::where($onlyWhere)->exists()) {
            return $this->message('抱歉，无法重复添加相同证号!', false);
        }
        //存卡
        $create = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $readerInfo['rdid'],
            'password' => encrypt($password),
            'is_bind' => 0,
            'name' => $readerInfo['name'],
            'origin_glc' => Arr::get($readerInfo, 'origin_glc'),
            'origin_libcode' => Arr::get($readerInfo, 'origin_libcode'),
            'is_cluster' => Arr::get($readerInfo, 'is_cluster', 0)
        ];
        $status = Reader::create($create);
        if ($status == false) {
            return $this->internalError('服务器繁忙，请稍后再试!');
        }
        $success = ['id' => $status->id];
        return $this->success($success);
    }

    /**
     * openid 查询绑定列表
     * 如存在绑定关系 => 则返回该读者的借阅状态数量
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
    public function openidGetBindList(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $where = [
            'token' => $token,
            'openid' => $openid
        ];
        $cardList = Reader::where($where)->orderBy('is_bind', 'desc')
            ->get(['name', 'id', 'rdid', 'token', 'is_bind', 'created_at', 'is_cluster']);

        $borrowStatus = [
            'a' => 0,  //在借中
            'b' => 0,  //将过期
            'c' => 0   //已过期
        ];
        //判断是否存在绑定关系
        $currentBind = $cardList->firstWhere('is_bind', 1);

        if ($currentBind) {
            $wxuser = Wxuser::getCache($token);
            $readerService = new ReaderService($wxuser);
            $response = $readerService->getCurrentLoan($currentBind->rdid, $currentBind->toArray());
            if ($response['status'] !== false) {
                $loanlist = $response['data'];
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

}
