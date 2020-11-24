<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Controllers\CustomView\ChatRoom;
use App\Admin\Controllers\CustomView\fansDetails;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Admincontent;
use App\Models\Wechat\Fans;
use App\Models\Wechat\Fanscontent;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use App\Unified\ReaderService;
use EasyWeChat\Kernel\Messages\Text;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ChatRoomController extends Controller
{
    private $fansInfo = [];

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('客服中心');
            $content->description('....');
            $content->row(function ($row) {
                $row->column(3, new fansDetails($this->getFansInfo()));
                $row->column(9, new ChatRoom($this->getFansInfo(), $this->getHistory(), $this->getNotReadList()));
            });

        });
    }

    public function saveAdminContent(Request $request)
    {
        $data = $request->input('data');
        $openId = $request->route('openid');
        $token = $request->session()->get('wxtoken');
        $create = [
            'type' => 0,
            'token' => $token,
            'content' => $data,
            'openid' => $openId,
            'user_id' => Admin::user()->id
        ];
        $message = new Text($data);
        $app = Wechatapp::initialize($token);
        $result = $app->customer_service->message($message)->to($openId)->send();

        $re = ['status' => false, 'message' => '发送失败'];
        if ($result['errmsg'] == 'ok') {
            Admincontent::create($create);
            $re = ['status' => true, 'message' => '发送成功'];
        }
        return $re;
    }

    public function getHistory()
    {
        $openid = \request()->route('openid');
        $token = \request()->session()->get('wxtoken');
        $where = [
            'openid' => $openid,
            'token' => $token
        ];
        $fansContent = Fanscontent::where($where)->orderBy('created_at', 'Desc')->limit(20)->get();
        //判断是否存在未阅读的数据
        $notRead = $fansContent->first(function ($value, $key) {
            return $value->is_reading != 1;
        });

        $last_at = ($fansContent->last()) ? $fansContent->last()->created_at : '';

        if ($notRead) {
            Fanscontent::where($where)->update(['is_reading' => 1]);
        }
        $admincontentModel = Admincontent::with('hasOneAdministrators:id,name,avatar')->where($where);
        if (!empty($last_at)) {
            $admincontentModel->where('created_at', '>=', $last_at);
        }
        $adminContent = $admincontentModel->limit(20)->get();
        $allContent = $fansContent->merge($adminContent)->sortBy('created_at');
        return $allContent;
    }

    public function getFansInfo()
    {
        if ($this->fansInfo) {
            return $this->fansInfo;
        }
        $openid = \request()->route('openid');
        $token = \request()->session()->get('wxtoken');
        $where = [
            'token' => $token,
            'openid' => $openid
        ];

        $fans = Fans::where($where)->first([
            'subscribe',
            'openid',
            'nickname',
            'sex',
            'headimgurl',
            'subscribe_time',
            'country',
            'province',
            'city']);
        $reader = Reader::checkBind($openid, $token)->first(['rdid', 'created_at', 'name']);

        $redis = Redis::connection();
        $exist = $redis->zscore('fans:' . $token, $openid);

        $fans['send_sw'] = ($exist) ? 1 : 0;

        if ($reader) {
            $ReaderService = new ReaderService(Wxuser::getCache($token));
            $response = $ReaderService->searchUser($reader['rdid']);
            $readerInfo = [];
            if ($response['status'] === true) {
                $readerInfo = $response['data'];
                if (!Arr::has($readerInfo, 'rdcfstatename')) {
                    $readerInfo['rdcfstatename'] = $readerInfo['rdcfstate'];
                    $readerInfo['rdlibtype'] = $readerInfo['globaltype'];
                }
            }
            $fans->readerInfo = $readerInfo;
            $fans->reader = $reader;
        }
        $this->fansInfo = $fans;
        return $fans;
    }

    public function getNotReadList()
    {
        $token = \request()->session()->get('wxtoken');
        $notReadList = Fanscontent::where([
            'token' => $token,
            'is_reading' => 0
        ])->select(DB::raw("count(1) as count"), 'openid', 'id')
            ->orderBy('id')
            ->groupBy('openid')
            ->limit(20)
            ->pluck('count', 'openid')->toArray();
        if ($notReadList) {
            $openids = array_keys($notReadList);
            $fansInfo = Fans::where('token', $token)->whereIn('openid', $openids)
                ->get(['openid', 'nickname', 'sex', 'headimgurl'])
                ->keyBy('openid')
                ->toArray();
            foreach ($notReadList as $k => $v) {
                $notReadList[$k] = [
                    'count' => $v,
                    'info' => Arr::get($fansInfo, $k, [])
                ];
            }
        }
        return $notReadList;
    }


}
