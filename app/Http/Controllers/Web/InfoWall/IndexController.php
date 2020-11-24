<?php

namespace App\Http\Controllers\Web\InfoWall;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\InfoWall\InfoWallConfig;
use App\Models\InfoWall\InfoWallDanMuTpl;
use App\Models\InfoWall\InfoWallNewsList;
use App\Models\InfoWall\InfoWallUserInfo;
use App\Models\Wxuser;
use App\Services\WechatOAuth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class IndexController extends Controller
{
    use ApiResponse;

    // 初始化验证
    public function __construct()
    {
        $this->middleware('RequiredToken')->only(['index']);
    }

    /**
     * time  2020.3.19.
     *
     * @content  显示首页页面
     *
     * @author  wsp
     */
    public function index(Request $request)
    {
        $token = $request->input('token');
        $a_id = $request->input('a_id');
        $site = $request->input('site', 1);
        // 开始授权操作
        $WechatOAuth = WechatOAuth::make($token);
        $fansInfo = $WechatOAuth->webOAuth($request);
        // 获取当前馆下的活动信息
        $config = InfoWallConfig::where(['id' => $a_id, 'status' => 1])->first(['id', 'rule', 'is_custom', 'is_check', 'describe', 'start_at', 'end_at']);
        if ($config == null) {
            abort(404);
        }
        // 获取当前馆的信息
        $wxuser = Wxuser::getCache($token);
        $wxname = $wxuser->wxname;
        // 获取当前馆下的模板话题
        $Danmu = InfoWallDanMuTpl::where(['l_id' => $a_id, 'token' => $token])
            ->orderBy('id', 'asc')->get(['id', 'type', 'p_name'])->toArray();
        // 查看当前馆中是否有已经存在该用户的信息了
        $user = InfoWallUserInfo::where('openid', $fansInfo['openid'])->first();
        $sign = ''; // 标记用户信息是否已存在的状态
        if (empty($user)) {
            $sign = '1';
        }
        // 统计当前馆下的弹幕数据
        $inner_news = InfoWallNewsList::where(['token' => $token, 'site' => 1])->count(); // 场内弹幕数
        $out_news = InfoWallNewsList::where(['token' => $token, 'site' => 2])->count(); // 场外弹幕数
        // 获取当前时间数据
        $date = [
            'year' => date("Y", time()),
            'month' => date("F", time()),
            'day' => date("d", time()),
            'weekday' => date('l')
        ];
        // 获取当前URL，用于海报二维码的显示
        $url = URL::current();

        return view('web.infowall.index', compact('user', 'url', 'a_id', 'site', 'fansInfo', 'wxname', 'date',
            'token', 'config', 'sign', 'inner_news', 'out_news', 'Danmu', 'second_name'));
    }

    /**
     * time  2020.4.3.
     *
     * @content  获取二级话题
     *
     * @author  wsp
     */
    public function getTopic(Request $request)
    {
        $id = $request->input('id');
        $topic = InfoWallDanMuTpl::where('id', $id)->first();
        $second = explode(chr(10), trim($topic['s_name']));
        // 计算数组的长度
        $length = count($second);
        $len = ceil($length / 3);
        return $this->success(['topic' => $second, 'length' => $len, 'id' => $id], true);
    }

    /**
     * time  2020.4.3.
     *
     * @content  根据页码值获取二级话题
     *
     * @author  wsp
     */
    public function pageTopic(Request $request)
    {
        $id = $request->input('id');
        $cid = $request->input('cid');
        $topic = InfoWallDanMuTpl::where('id', $id)->first();
        $second = explode(chr(10), trim($topic['s_name']));
        $start = ($cid - 1) * 3; // 初始下标
        $ftopic = []; // 定义存放符合条件的数组
        for ($i = 0; $i <= 2; $i++) {
            if (!empty($second[$start])) {
                $ftopic[] = $second[$start];
                $start++;
            }
        }
        return $this->success(['ftopic' => $ftopic, 'id' => $id], true);
    }

    /**
     * time  2020.3.19.
     *
     * @content  添加个人信息
     *
     * @author  wsp
     */
    public function addUserInfo(Request $request)
    {
        $token = $request->input('token');
        $l_id = $request->input('l_id');
        $openid = $request->input('openid');
        $nickname = $request->input('nickname');
        $phone = $request->input('phone');
        $name = $request->input('name');
        $headimgurl = $request->input('headimgurl');
        $add = [
            'token' => $token,
            'l_id' => $l_id,
            'openid' => $openid,
            'nickname' => $nickname,
            'phone' => $phone,
            'username' => $name,
            'headimgurl' => $headimgurl,
            'created_at' => date('Y-m-d H:i:s', time()),
            'updated_at' => date('Y-m-d H:i:s', time()),
        ];
        $res = InfoWallUserInfo::insertGetId($add);
        if ($res) {
            return $this->success(['message' => '提交成功', 'user_id' => $res], true);
        } else {
            return $this->success(['message' => '提交失败！'], false);
        }
    }

    /**
     * time  2020.4.3.
     *
     * @content  保存心愿(敏感词过滤)
     *
     * @author  wsp
     */
    public function saveWish(Request $request)
    {
        $content = $request->input('content');
        $l_id = $request->input('id');
        $user_id = $request->input('user_id');
        $token = $request->input('token');
        $topic = $request->input('topic');
        $type = $request->input('type');
        $site = $request->input('site');
        // 先查看当前用户是否有剩余发弹幕次数
        // 获取活动下的敏感词开关配置数据
        $config = InfoWallConfig::where('id', $l_id)->first();
        $allowNumber = $this->currentStatus($config, $user_id);
        // 将弹幕信息提交到表中
        $data = [
            'user_id' => $user_id,
            'token' => $token,
            'topic' => $topic,
            'content' => $content,
            'site' => $site,
            'l_id' => $l_id,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s', time()),
            'updated_at' => date('Y-m-d H:i:s', time())
        ];
        if ($allowNumber['allowNumber'] > 0) {
            if ($type == 1) {
                // 如果馆方没有开启敏感词自检，则启用敏感词接口来验证许愿数据是否符合规则
                $access_token = '24.4e4edca50f41a3ee940a8cc35a912015.2592000.1588840552.282335-19086236';
                $url = 'https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined?access_token=' . $access_token;
                // 调用百度接口，如果不符合，直接 return;
                // 个人信息
                $param = [
                    'form_params' => [
                        'text' => $content
                    ]
                ];
                $client = new Client();
                $response = $client->request('POST', $url, $param);
                $auth = json_decode((string)$response->getBody(), true);/**/
                // 测试
//                $auth['conclusionType'] = 1;
                if ($auth['conclusionType'] == 1) {
                    // 如果馆方开启自己审核，则更改弹幕状态，添加到数据表中
                    if ($config['is_check'] ==1) {
                        $data['status'] = 0;
                    }
                    InfoWallNewsList::insert($data);
                    return $this->message('发送成功!', true);
                } elseif ($auth['data'][0]['conclusionType'] == 2) {
                    return $this->message($auth['data'][0]['msg'], false);
                } elseif ($auth['data'][0]['conclusionType'] == 3) {
                    return $this->message($auth['data'][0]['msg'], false);
                } else {
                    return $this->message($auth['error_msg'], false);
                }
            } else {
                // 直接将数据添加到数据表中
                InfoWallNewsList::insert($data);
                return $this->message('发送成功!', true);
            }
        } else {
            return $this->message('抱歉，您发弹幕的次数已达上限', false);
        }
    }

    /**
     * time  2020.3.19.
     *
     * @content  获取弹幕信息显示于屏幕上
     *
     * @author  wsp
     */
    public function screenGetDanmu(Request $request)
    {
        $token = $request->input('token');
        $pid = $request->input('pid', '1');
        if ($pid != 1) {
            $pid += 1;
        }
        $danmu = InfoWallNewsList::where(['token' => $token, 'is_shelf' => 1, 'status' => 1])->where('id','>=',$pid)
            ->with('hasOneUser')->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()->toArray();
        // 标记一个最后的查询id值，用于下次查询新数据的起始id
        if (!empty($danmu)) {
            foreach ($danmu as $key => $value) {
                $pid = $value['id'];
            }
        }
        // 查询当前馆内的所有弹幕数据总数，实时更新数据总计
        $inner_news = InfoWallNewsList::where(['token' => $token, 'is_shelf' => 1, 'site' => 1])->count(); // 场内弹幕数
        $out_news = InfoWallNewsList::where(['token' => $token, 'is_shelf' => 1, 'site' => 2])->count(); // 场外弹幕数
        return $this->success(['danmu' => $danmu, 'pid' => $pid, 'inner_news' => $inner_news, 'out_news' => $out_news], true);
    }


    /**
     * time  2020.3.19.
     *
     * @content  获取弹幕信息显示于屏幕上
     *
     * @author  wsp
     */
    public function getDanMu(Request $request)
    {
        $token = $request->input('token');
        $danmu = InfoWallNewsList::where(['token' => $token, 'is_shelf' => 1, 'status' => 1])
            ->with('hasOneUser')->orderBy('created_at', 'desc')
            ->get()->toArray();
        return $this->success(['danmu' => $danmu], true);
    }

    /**
     * time  2020.4.9.
     *
     * @content  大屏幕展示
     *
     * @author  wsp
     */
    public function largeScreen(Request $request)
    {
        $token = $request->input('token');
        $a_id = $request->input('a_id');
        // 统计当前馆下的弹幕数据
        $inner_news = InfoWallNewsList::where(['token' => $token, 'site' => 1])->count(); // 场内弹幕数
        $out_news = InfoWallNewsList::where(['token' => $token, 'site' => 2])->count(); // 场外弹幕数
        // 获取当前URL，用于海报二维码的显示
        $url = URL::current();
        $url = str_replace('largeScreen', 'index', $url);
        return view('web.infowall.largeScreen', compact('inner_news', 'url',  'a_id', 'out_news', 'token'));
    }

    /**
     * time  2020.3.19.
     *
     * @content  获取当前用户可发弹幕次数
     *
     * @author  wsp
     */
    protected function currentStatus($configure, $user_id)
    {
        $allowNumber = 0;
        //当前发弹幕次数
        $allowQuery = InfoWallNewsList::where(['token' => $configure['token'], 'l_id' => $configure['id']]);
        $allowQuery->where('user_id', $user_id);
        //个人发送的全部次数
        $allNumber = $allowQuery->count();
        if ($configure['type'] == 1) {
            //按天数的话需要对比总数
            $todayNumber = $allowQuery->whereDate('created_at', '>=', date('Y-m-d'))->count();
            if ($todayNumber < $configure['number']) {
                $allowNumber = $configure['number'] - $todayNumber;
            }
        } else {
            $allowNumber = ($allNumber >= $configure['number']) ? 0 : $configure['number'] - $allNumber;
        }
        return ['allowNumber' => $allowNumber];
    }

}
