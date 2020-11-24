<?php

namespace App\Http\Controllers\Web\SeatBooking;

use App\Api\Helpers\ApiResponse;
use App\Models\Seat\SeatAttr;
use App\Models\Seat\SeatByBooking;
use App\Models\Seat\SeatByScan;
use App\Models\Seat\SeatChart;
use App\Models\Seat\SeatQueue;
use App\Models\Seat\SeatRegion;
use App\Models\Seat\SeatScoreLog;
use App\Models\Seat\SeatUser;
use App\Models\Seat\SeatCurrBooking;
use App\Models\Seat\SeatViolationslog;
use App\Models\Seat\SeatViolationsReset;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use Illuminate\Http\Request;
use App\Services\WebOAuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use EasyWeChat\Kernel\Messages\Text;

class IndexController extends BaseController
{
    use ApiResponse;

    protected $wxUserInfo = null;

    public function __construct(Request $request)
    {
        parent::__construct($request);

    }

    /**
     * 座位预约首页
     * @param Request $request
     * @param WebOAuthService $webOAuthService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index(Request $request, WebOAuthService $webOAuthService)
    {
        $user = $this->getUserBySession($webOAuthService);

//        if(!$user){
//            // 调试数据
//            $this->wxUserInfo = $this->testData();
//            //$this->wxUserInfo = $webOAuthService->checkOauth();
//            $reader = $this->checkUserBind($this->wxUserInfo,true);
//            $user = $this->updateUser($reader);
//            $request->session()->put('seatUser',$user);
//        }

        $wxUser = $this->wxuser;
        $mySeat = SeatChart::where(['token'=> $this->token, 'status'=>2, 'rdid'=> $user['rdid']])->with('region:id,name')->get();
        $queue = SeatQueue::where(['token'=> $this->token, 'rdid'=> $user['rdid']])->get();
        $myBooking = SeatCurrBooking::where(['token'=> $this->token, 'rdid'=> $user['rdid']])->get();
        $hotBooking = SeatRegion::where(['token'=>$this->token, 'status'=>1, 'booking_switch'=>1, 'is_hot'=>1])->where('pid','>','0')->get();
        $app =  Wechatapp::initialize($this->token);
        $globalConfig = $this->globalConfig;
        $viewData = [
            'globalConfig' => $globalConfig,
            'wxUser' => $wxUser,
            'app'   => $app,
            'user'  => $user,
            'mySeat' => $mySeat,
            'queue' => $queue,
            'myBooking' => $myBooking,
            'hotBooking' =>$hotBooking
        ];
//dd($myBooking);
        return view('web.seatBooking.index',$viewData);
    }

    /**
     * 座位预约 Step 1
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function startBooking(Request $request)
    {
        $regions = SeatRegion::GetAllowRegions($this->token)->get();
        $level1 = $level2 = [];
        foreach ($regions as $value){
            if($value->pid == 0){
                $level1[] = $value;
                continue;
            }
            $level2[] = $value;
        }
        $app =  Wechatapp::initialize($this->token);
        $viewData = [
            'app' => $app,
            'level1' => $level1,
            'level2'   => $level2,
        ];
        return view('web.seatBooking.startBookingOne', $viewData);
    }

    /**
     * 座位预约 Step 2
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function startBookingTwo(Request $request)
    {
        $regionID = $request->input('id');
        $region = SeatRegion::where('id',$regionID)->first();
        $globalConfig = $this->globalConfig;
        $timeConfig['minH'] = floor($globalConfig->shortest_t / 60);
        $timeConfig['minM'] = $globalConfig->shortest_t % 60;
        $timeConfig['maxH'] = floor($globalConfig->longest_t / 60);
        $timeConfig['maxM'] = $globalConfig->longest_t % 60;
        $region->e_time = date('H:i:s', strtotime('2019-01-01 ' . $region->e_time) - $globalConfig->shortest_t * 60);

        $app =  Wechatapp::initialize($this->token);
        $viewData = [
            'app' => $app,
            'region' => $region,
            'globalConfig' => $globalConfig,
            'timeConfig' => $timeConfig
        ];
        return view('web.seatBooking.startBookingTwo',$viewData);
    }

    /**
     * 座位预约 Step 3
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function startBookingThree(Request $request)
    {
        $region = SeatRegion::where('id',$request->input('id'))->first();
        $globalConfig = $this->globalConfig;
        $app =  Wechatapp::initialize($this->token);

        $viewData = [
            'app' => $app,
            'region' => $region,
            'globalConfig' => $globalConfig,
            'storage' => Storage::disk(config('admin.upload.disk'))
        ];
        return view('web.seatBooking.startBookingThree', $viewData);
    }

    /**
     * 座位预约-修改 Step 2
     */
    public function modifyStepTwo(Request $request, WebOAuthService $webOAuthService)
    {
        $bookingID = $request->input('bookingId');
        $user = $this->getUserBySession($webOAuthService);
        $bookingData = SeatCurrBooking::where('id',$bookingID)->first();
        $chartData = SeatChart::where('id',$bookingData->chart_id)->with('attr:name')->with(['region:id,status,name,img,s_time,e_time'])->first();
        if($bookingData->rdid != $user['rdid']) return abort(404);

        //分钟转小时
        $globalConfig = $this->globalConfig;
        $timeConfig['minH'] = floor($globalConfig->shortest_t / 60);
        $timeConfig['minM'] = $globalConfig->shortest_t % 60;
        $timeConfig['maxH'] = floor($globalConfig->longest_t / 60);
        $timeConfig['maxM'] = $globalConfig->longest_t % 60;
        $chartData->region->e_time = date('H:i:s', strtotime('2019-01-01 ' . $chartData->region->e_time) - $globalConfig->shortest_t * 60);

        $app =  Wechatapp::initialize($this->token);
        $viewData = [
            'app' => $app,
            'bookingData' => $bookingData,
            'chartData' => $chartData,
            'globalConfig' => $globalConfig,
            'timeConfig' => $timeConfig
        ];
        return view('web.seatBooking.modifyStepTwo', $viewData);
    }

    /**
     * 座位预约-修改 Step 3
     */
    public function modifyStepTree(Request $request, WebOAuthService $webOAuthService)
    {
        $bookingID = $request->input('bookingId');
        $user = $this->getUserBySession($webOAuthService);
        $bookingData = SeatCurrBooking::where('id',$bookingID)->first();
        $chartData = SeatChart::where('id',$bookingData->chart_id)->with('attr:name')->with(['region:id,status,name,img,s_time,e_time,cols'])->first();
        if($bookingData->rdid != $user['rdid']) return abort(404);

        $globalConfig = $this->globalConfig;
        $app =  Wechatapp::initialize($this->token);
        $viewData = [
            'app' => $app,
            'bookingData' => $bookingData,
            'chartData' => $chartData,
            'globalConfig' => $globalConfig,
            'storage' => Storage::disk(config('admin.upload.disk'))
        ];
        return view('web.seatBooking.modifyStepTree',$viewData);
    }

    /**
     * 座位数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initSeat(Request $request)
    {
        $input = $request->input();
        $stime = date('Y-m-d H:i:s',$input['stime']);
        $etime = date('Y-m-d H:i:s',$input['etime']);
        $allAttr = SeatAttr::where('token',$input['token'])->get()->toArray();
        $seats = SeatChart::with('attr')->where(['token'=>$input['token'], 'region_id'=>$input['regionId']])->orderBy('id','asc')->get()->toArray();
        $allBooking = SeatCurrBooking::where(['token'=> $this->token])->get(['rdid','chart_id','s_time','e_time'])->toArray();
        $temp = [];
        // 判断各位置在当前选择时间段内是否有预约
        foreach($seats as $key=>$value){
            $seats[$key]['state'] = 0;
            foreach ($allBooking as $v){
                if($value['id'] == $v['chart_id'] && $stime>=$v['s_time'] && $stime<=$v['e_time']){
                    $seats[$key]['state'] = 1;
                    continue;
                }
            }
        }
        return response()->json(['allAttr' => $allAttr, 'list' => $seats]);
    }

    /**
     * 提交预约
     * @param Request $request
     * @return json
     */
    public function submitBooking(Request $request, WebOAuthService $webOAuthService)
    {
        $input = $request->input();
        $user = $this->getUserBySession($webOAuthService);

        //判断区域控制开关状态
        $region = SeatRegion::where(['id'=> $input['id'], 'status'=> 1, 'booking_switch'=>1])->first();
        if(!$region) return $this->message('抱歉，该区域暂停预约服务！',false);

        //预约数量限制判断
        $bookingNum = SeatCurrBooking::where(['token'=> $this->token, 'rdid'=> $user['rdid']])->count();
        if ($bookingNum + count($input['data']) > $this->globalConfig['num']) {
            return $this->message('已超过你可以预约的数量',false);
        }

        $s_time = date('Y-m-d H:i:s', $input['stime']);
        $e_time = date('Y-m-d H:i:s', $input['etime']);
        $start_time = date('Y-m-d 00:00:01', $input['stime']);
        $end_time = date('Y-m-d 23:59:59', $input['stime']);

        $All = [];
        $w = false;
        foreach ($input['data'] as $k => $v) {
            $All[] = [
                'token' => $this->token,
                'openid' => $user['openid'],
                'rdid' => $user['rdid'],
                'chart_id' => $v[0],
                's_time' => $s_time,
                'e_time' => $e_time,
                'status' => 0,
                'from' => 'WeChat',
                'sign_min' => date('Y-m-d H:i:s', $input['stime'] - $this->globalConfig['ok_t'] * 60),
                'sign_max' => date('Y-m-d H:i:s', $input['stime'] + $this->globalConfig['delay_t'] * 60),
                'mark' => $region['name'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $currBooking = SeatCurrBooking::where(['token'=> $this->token, 'chart_id'=>$v[0] ])->get();
            foreach ($currBooking as $value){
                if($value['s_time'] >= $s_time && $value['s_time'] < $e_time){
                    $w = true;continue;
                }
                elseif ($s_time >= $value['s_time'] && $e_time <= $value['e_time']){
                    $w = true;continue;
                }
                elseif ($s_time < $value['s_time'] && $e_time > $value['e_time']){
                    $w = true;continue;
                }
                elseif ($value['s_time'] >= $s_time && $value['e_time'] <= $e_time){
                    $w = true;continue;
                }
            }
        }
        if ($w) return $this->message('抱歉，您预约的时段内已有人抢先一步预约了！',false);

        $status = SeatCurrBooking::insert($All);
        if ($status) return $this->message('预约成功，正在为你跳转首页',true);

    }

    /**
     * 修改预约
     * @param Request $request
     * @return json
     */
    public function updateBooking(Request $request)
    {
        $input = $request->input();

        $oldBooking = SeatCurrBooking::find($input['oldBookingId']);
        $currBooking = SeatCurrBooking::where(['token'=> $this->token, 'chart_id'=> $input['data'][0]])->get();

        $s_time = date('Y-m-d H:i:s', $input['stime']);
        $e_time = date('Y-m-d H:i:s', $input['etime']);
        $w = false;
        foreach ($currBooking as $value)
        {
            if($value['rdid'] == $oldBooking->rdid) continue;

            if($value['s_time'] >= $s_time && $value['s_time'] < $e_time){
                $w = true;break;
            }
            elseif ($s_time >= $value['s_time'] && $e_time <= $value['e_time']){
                $w = true;break;
            }
            elseif ($s_time < $value['s_time'] && $e_time > $value['e_time']){
                $w = true;break;
            }
            elseif ($value['s_time'] >= $s_time && $value['e_time'] <= $e_time){
                $w = true;break;
            }
        }

        if($w) return $this->message('抱歉，预约修改失败——时间冲突',false);

        $oldBooking->chart_id = $input['data'][0];
        $oldBooking->s_time = $s_time;
        $oldBooking->e_time = $e_time;
        $oldBooking->sign_min = date('Y-m-d H:i:s', $input['stime'] - $this->globalConfig['ok_t'] * 60);
        $oldBooking->sign_max = date('Y-m-d H:i:s', $input['stime'] + $this->globalConfig['delay_t'] * 60);
        $oldBooking->is_sendMsg = 0;

        $res = $oldBooking->save();

        if ($res){
            return $this->message('修改成功，正在为你跳转首页',true);
        } else{
            return $this->message('修改失败，稍后再试！',false);
        }

    }

    /**
     * 积分记录
     * @return string
     */
    public function scoreLog(Request $request, WebOAuthService $webOAuthService)
    {
        $user = $this->getUserBySession($webOAuthService);
        $score = SeatUser::where('id',$user['id'])->first();
        $user['score'] = $score->score;
        if(!$request->ajax()){
            $app =  Wechatapp::initialize($this->token);//null
            $viewData = [
                'app' => $app,
                'user' => $user,
                'ajaxItemsUrl' => route('Seat::scoreLog', ['token'=>$this->token])
            ];
            return view('web.seatBooking.seatScoreLog', $viewData);
        }
        else{
            $allLog = SeatScoreLog::where(['token'=> $this->token,'user_id'=> $user['rdid']])->orderBy('c_time','desc')->paginate(10);
            return $allLog;
        }
    }

    /**
     * 入座记录
     * @return string
     */
    public function seatLogList(Request $request, WebOAuthService $webOAuthService)
    {

        if(!$request->ajax()){
            $user = $this->getUserBySession($webOAuthService);
            $app =  Wechatapp::initialize($this->token);
            $viewData = [
                'app' => $app,
                'user' => $user,
                'ajaxItemsUrl' => route('Seat::seatLogList', ['token'=>$this->token])
            ];
            return view('web.seatBooking.seatLogList',$viewData);
        }
        else{
            $rdid = $request->input('rdid');
            $allLog = SeatByScan::with('chart:id,numid')->where(['token'=>$this->token, 'rdid'=>$rdid])->orderBy('created_at','desc')->paginate(10);
            return $allLog;
        }
    }

    /**
     * 预约记录
     * @return string
     */
    public function seatBookingLog(Request $request, WebOAuthService $webOAuthService)
    {
        $user = $this->getUserBySession($webOAuthService);
        if(!$request->ajax()){
            $myBooking = SeatCurrBooking::where(['token'=> $this->token, 'rdid'=> $user['rdid']])->with('chart')->get();
            $app =  Wechatapp::initialize($this->token);
            $viewData = [
                'app' => $app,
                'user' => $user,
                'myBooking' => $myBooking,
                'ajaxItemsUrl' => route('Seat::seatBookingLog', ['token'=>$this->token])
            ];
            return view('web.seatBooking.seatBookingLog', $viewData);
        }
        else{
            $allBooking = SeatByBooking::where(['token'=> $this->token, 'rdid'=> $user['rdid']])->with('chart')->orderBy('s_time','desc')->paginate(10);
            return $allBooking;
        }

    }

    /**
     * 预约详情
     */
    public function seatBookingStatus(Request $request)
    {
        $data = SeatCurrBooking::where('id',$request->input('id'))->first();
        if(!$data) return abort(404);
        $chartData = SeatChart::where('id',$data->chart_id)->with('attr:name')->with(['region:id,status,name,img'])->first();
        $allBooking = SeatCurrBooking::where(['token'=>$this->token, 'chart_id'=>$data->chart_id])->orderBy('s_time')->get()->toArray();
        $allBookingNum = count($allBooking);
        $data->allowTime = date('Y-m-d',strtotime($data->s_time)).' '.date('H:i',strtotime($data->s_time)-($this->globalConfig->ok_t)*60).'-'.date('H:i',strtotime($data->s_time)+($this->globalConfig->delay_t)*60);
        $app =  Wechatapp::initialize($this->token);
        $viewData = [
            'app' => $app,
            'data' => $data,
            'chartData' => $chartData,
            'allBooking' => $allBooking,
            'allBookingNum' => $allBookingNum
        ];

        return view('web.seatBooking.seatBookingStatus', $viewData);
    }

    /**
     * 取消预约
     */
    public function cancelBooking(Request $request)
    {
        $input = $request->input();
        $userBooking = SeatCurrBooking::where('id', $input['id'])->first();

        $log = [
            'token' => $this->token,
            'openid' => $userBooking->openid,
            'rdid' => $userBooking->rdid,
            'chart_id' => $userBooking->chart_id,
            's_time' => $userBooking->s_time,
            'e_time' => $userBooking->e_time,
            'status' => 2,
            'from' => $userBooking->from,
            'sign_min' => $userBooking->sign_min,
            'sign_max'=>$userBooking->sign_max,
            'sign_in' => date('Y-m-d H:i:s'),
            'mark'=>$userBooking->mark,
            'created_at' => $userBooking->created_at
        ];

        SeatByBooking::create($log);
        SeatCurrBooking::where('id', $input['id'])->delete();

        return $this->message('预约取消成功，正在为你跳转首页',true);
    }

    /**
     * 签到二维码
     * http://newuwei.natapp1.cc/webWechat/SeatBooking/signQrcode?token=18c6684c
     */
    public function signQrcode(Request $request)
    {
        //根据ip判断，此页面只能在一个地方打开
        if ($request->isMethod('get')) {
            $wxuser = $this->wxuser;
            $url = route('Seat::bookingAttendance',['token'=>$this->token]);

            $viewData = [
                'wxuser' => $wxuser,
                'url' => $url
            ];
            return view('web.seatBooking.signQrcode', $viewData);
        }
        if($request->isMethod('post')){
            $key = $this->encrypt_url($this->token . '-' . time(), 'yanlong2018');
            Cache::put('signQrcode_key_' . $this->token, $key, 5);
            return response()->json($key);
        }

    }

    /**
     * 签到提示页
     * http://newuwei.natapp1.cc/webWechat/SeatBooking/bookingAttendance?token=18c6684c&key=
     */
    public function bookingAttendance(Request $request, WebOAuthService $webOAuthService)
    {
        if($request->isMethod('get')){
            $inputKey = $request->input('key');
            $viewData = ['status'=>true,'message'=>''];
            $key = $this->decrypt_url($inputKey, 'yanlong2018');
            $get = explode("-", $key);
            if (!$key || !$get[0] || !$get[1]) {
                abort(404);
            }
            $this->token = $get[0];
            $cacheKey = Cache::get('signQrcode_key_' . $this->token);
            if ($cacheKey != urlencode($inputKey)) $viewData = ['status'=>false,'message'=>'二维码已过期，请重新扫描 !'];

            $this->getUserBySession($webOAuthService);
            $viewData['app'] = Wechatapp::initialize($this->token);

            return view('web.seatBooking.seatBookingAttendance',$viewData);
        }
        if($request->isMethod('post')) {

            $lat1 = $this->globalConfig->lat;
            $lng1 = $this->globalConfig->lng;
            $lat2 = $request->lat;
            $lng2 = $request->lng;

            $distance = $this->distanceSimplify($lat1,$lng1,$lat2,$lng2);

            if($this->globalConfig->purview && $distance > $this->globalConfig->purview){
                return $this->message('超出可签到地点允许范围内',true);
            }

            $user = $this->getUserBySession($webOAuthService);
            $data = SeatCurrBooking::where(['token'=> $this->token, 'rdid'=> $user['rdid'], 'status'=> 0])->get(['id', 'sign_min', 'sign_max']);
            if ($data->first()) {
                $now = date('Y-m-d H:i:s');
                foreach ($data as $k => $v) {
                    if ($now >= $v['sign_min'] && $now <= $v['sign_max']) {
                        SeatCurrBooking::where('id', $v->id)->update(['status' => 1, 'sign_in' => $now, 'updated_at' => $now]);
                    }
                }
                return $this->message('签到成功',true);
            } else {
                return $this->message('抱歉，暂无可签到的预约记录!',true);
            }
        }
    }

    /**
     * 座位详情
     */
    public function chartStatus(Request $request, WebOAuthService $webOAuthService)
    {
        $seat_id = $request->input('id');
        $sign = $request->input('sign');
        if ($request->input('opentype') == 'qrcode' && $sign != md5($seat_id . '2019')) {
           abort(404);
        }
        $user = $this->getUserBySession($webOAuthService);
        //查询是否已有入座
        $data = SeatChart::where(['id'=> $seat_id, 'token'=> $this->token])->with(['region:id,name','attr:id,name','queue'])->first()->toArray();
        //查询入座记录
        $downLog = '';
        if($data['status'] == 2 && $data['seating_type'] == 0){
            $downLog = SeatByScan::where('id', $data['seated_id'])->with('fans')->first();
        }
        elseif($data['status'] == 2 && $data['seating_type'] == 1){
            $downLog = SeatByBooking::where('id', $data['seated_id'])->with('fans')->first();
        }
        if($downLog){
            $data['curr_user'] = $downLog->rdid;
            $data['nickname'] = $downLog->fans->nickname;
            $data['headimgurl'] = $downLog->fans->headimgurl;
        }

        //是否有排队
        $data['countdown'] = '';
        $data['queueNum'] = 0;
        if ($data['queue_id']) {
            $data['countdown'] = SeatQueue::where('id',$data['queue_id'])->first()->toArray();
            $data['countdown'] = $data['countdown']['seating_time'] < date('Y-m-d H:i:s') ? 1 : $data['countdown']['seating_time'];
            //通过二维码进入清空队列
            if ($request->input('opentype') == 'qrcode' ) {
                $data['queueNum'] = SeatQueue::where(['token'=>$this->token, 'chart_id'=>$seat_id])->count();
            }
        }

        //座位当天的预约
        $allbooking = SeatCurrBooking::where(['token'=> $this->token, 'chart_id'=> $seat_id])->whereBetween('s_time', [date('Y-m-d H:i:s'), date('Y-m-d').' 23:59:59'])->orderBy('s_time')->get(['rdid','s_time','e_time']);

        $currbooking = SeatCurrBooking::where(['token'=> $this->token, 'chart_id'=> $seat_id])->where('s_time','<=',date('Y-m-d H:i:s'))->where('e_time','>=',date('Y-m-d H:i:s'))->orderBy('s_time')->get(['rdid','s_time','e_time']);

        $wxUser = $this->wxuser;
        $app =  Wechatapp::initialize($this->token);
        $viewData = [
            'app' => $app,
            'data' => $data,
            'wxUser' => $wxUser,
            'user' => $user,
            'allbooking' => $allbooking,
            'currbooking' => $currbooking,
            'downLog' => $downLog,
        ];

        return view('web.seatBooking.seatChartStatus',$viewData);
    }

    /**
     * 座位入座
     */
    public function seatUseAjax(Request $request, WebOAuthService $webOAuthService)
    {
        $input = $request->input();
        $user = $this->getUserBySession($webOAuthService);
        if(!$user) return $this->message('error',false);
        $checkStatus = SeatChart::where(['id'=> $input['id'], 'token'=> $this->token])->with('region:id,name')->first()->toArray();

        if ($checkStatus['status'] == 2 && !empty($checkStatus['seated_id']) && empty($checkStatus['queue_id']))  return $this->message('入座慢了，此座刚刚已被人使用了!',false);

        //判断否有正在入座的记录
        $nums = SeatByScan::where(['token'=>$this->token, 'rdid'=>$user['rdid'], 'e_time'=>''])->count();
        if($nums > 0)   return $this->message('您当前已有正在使用的座位，请离座后再使用此座位!',false);

        //插入入座数据
        $log = [
            'token' => $this->token,
            'openid' => $user['openid'],
            'rdid' => $user['rdid'],
            'chart_id' => $input['id'],
            's_time' => date('Y-m-d H:i:s'),
            'e_time' => '',
            'mark' => $checkStatus['region']['name']. ' '.$checkStatus['numid'].'号位'
        ];
        $insert = SeatByScan::create($log);
        if($insert->id){
            //修改正式的座位数据
            $chartData = [
                'status' => 2,
                'seating_type' => 0,
                'seated_id' => $insert->id,
                'queue_id' => 0,
                'rdid' => $user['rdid'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $updated = SeatChart::where(['id'=> $input['id'], 'token'=> $this->token])->update($chartData);
            SeatQueue::where(['token'=> $this->token, 'rdid'=> $user['rdid'],'chart_id'=> $input['id']])->delete();

            //修改前面入座的人的入座数据
            if($checkStatus['seating_type'] == 0){
                SeatByScan::where('id', $checkStatus['seated_id'])->update(['e_time'=>date('Y-m-d H:i:s')]);
            }

        }else{
            return $this->message('入座失败，请稍后再试!',false);
        }
        return $this->message('入座成功',true);

    }

    /**
     * 座位排队
     */
    public function seatQueue(Request $request, WebOAuthService $webOAuthService)
    {
        $input = $request->input();
        $user = $this->getUserBySession($webOAuthService);
        $now = date('Y-m-d H:i:s');
        $keepTime = $this->globalConfig->keeptime;
        $chart = SeatChart::where('id',$input['id'])->with('region:id,name')->first(['id','region_id','numid','queue_id','seating_type','seated_id']);
        $queue = [
            'token' => $this->token,
            'openid' => $user['openid'],
            'rdid' => $user['rdid'],
            'chart_id' => $input['id'],
            'seating_time' => date('Y-m-d H:i:s', time() + $this->globalConfig->keeptime * 60)
        ];
        $created = SeatQueue::create($queue);

        if(!$chart['queue_id']){
            $updateChart= [
                'queue_id' => $created->id
            ];
            $updated = SeatChart::where('id',$input['id'])->update($updateChart);
        }
        if($chart['seating_type'] == 0){
            $openid = SeatByScan::where('id', $chart['seated_id'])->first(['openid']);
        }
        else{
            $openid = SeatByBooking::where('id', $chart['seated_id'])->first(['openid']);
        }

        $text = '你好，有人正在排队等待你的座位，如还需使用请于' . $this->globalConfig->keeptime . '分钟之内返回位置扫码清空队列!';

        $res = $this->sendTextMsg(Wechatapp::initialize($this->token), $openid->openid, $text);

        $response = ($created) ? $this->message('排队成功',true) : $this->message('排队成功，请稍后再试!', false);

        return $response;
    }

    /**
     * 清空排队
     */
    public function seatFanhuiAjax(Request $request, WebOAuthService $webOAuthService)
    {
        $input = $request->input();
        $user = $this->getUserBySession($webOAuthService);
        $chart = SeatChart::where('id',$input['id'])->first();

        if($user['rdid'] == $chart->rdid){
            //修改正式的座位数据
            $updated = SeatChart::where('id',$input['id'])->update([ 'queue_id' => '']);
            if ($updated) {
                SeatQueue::where(['token'=> $this->token, 'chart_id'=> $input['id']])->delete();
            }
            $response = ($updated) ? $this->message('返回座位成功',true) : $this->message('返回座位失败，请重新再试!', false);
        }else{
            $response = $this->message('返回座位失败！', false);
        }

        return $response;
    }

    /**
     * 座位离座
     */
    public function seatLogoffAjax(Request $request)
    {
        $input = $request->input();
        $type = $request->input('type');
        $downLog = $request->input('downLog');
        $chartData = [
            'status' => 1,
            'seating_type' => 0,
            'seated_id' => '',
            'rdid' => '',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $updated = SeatChart::where(['id'=> $input['id'],'token'=> $this->token])->update($chartData);

        //非预约入座记录离座时间
        if($type == 2 && $downLog){
            SeatByScan::where('id', $downLog)->update(['e_time'=>date('Y-m-d H:i:s')]);
        }
        else{
            SeatByBooking::where('id', $downLog)->update(['real_time' => date('Y-m-d H:i:s')]);
        }

        if($updated){
            return $this->message('离座成功',true);
        }else{
            return $this->message('入座失败，请稍后再试!', false);
        }
    }

    protected function getUserBySession($webOAuthService)
    {
        $user = Session::get('seatUser');
        if(!$user){
            //$this->wxUserInfo = $this->testData();
            $this->wxUserInfo = $webOAuthService->checkOauth();
            $reader = $this->checkUserBind($this->wxUserInfo,true);
            $user = $this->updateUser($reader);
            Session::put('seatUser',$user);
        }
        return $user;
    }

    /**
     * 更新用户信息
     * @param $reader
     * @return mixed
     */
    protected function updateUser($reader)
    {
        $user = SeatUser::getUser($this->token,$reader['rdid'])->first();
        if(!$user){
            $data = [
                'token' => $this->token,
                'rdid' => $reader['rdid'],
                'last_date'=>date('Y-m-d H:i:s'),
            ];
            SeatUser::create($data);
        }else{
            $update['last_date'] = date('Y-m-d H:i:s');
            if($user->violations == $this->globalConfig->violate_num && date('Y-m-d H:i:s')>$user->forbid_at){
                $update['violations'] = 0;

                $resetData = [
                  'token' => $this->token,
                  'rdid' => $user->rdid,
                  'created_at' => date('Y-m-d H:i:s')
                ];
                SeatViolationsReset::create($resetData);
            }
            SeatUser::where(['token'=> $this->token, 'rdid'=> $reader['rdid']])->update($update);
        }
        $user = SeatUser::GetUser($this->token,$reader['rdid'],$reader['openid'])->first();

        $userInfo = array_merge($this->wxUserInfo, $reader, $user->toArray());
        return $userInfo;
    }

    /**
     * 检查用户是否绑定读者证号
     * @param array $wxUserInfo
     * @param bool $bind
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function checkUserBind($wxUserInfo,$bind=false)
    {
        $reader = Reader::CheckBind($wxUserInfo['openid'], $this->token)->select(['rdid', 'name', 'openid'])->first();
        if (empty($reader) && $bind) {
            $bindUrl = str_replace('{token}', $this->token, config('vueRoute.bindReader'));
            header('Location:'.$bindUrl);
            exit;
        }
        $reader = $reader->toArray();
        return $reader;
    }

    /**
     * 发送文本消息
     * @param $app
     * @param $openId
     * @param $text
     * @return mixed
     */
    protected function sendTextMsg($app, $openId, $text)
    {
        $message = new Text($text);
        $result = $app->customer_service->message($message)->to($openId)->send();

        return $result;
    }

    /**
     * 定时任务
     * http://newuwei.natapp1.cc/webWechat/SeatBooking/timingTask?token=18c6684c
     */
    public function timingTask()
    {
        $whereTime = [ date('Y-m-d'), date('Y-m-d', strtotime('+1 day'))];
        $tokenArr = SeatCurrBooking::whereBetween('s_time',$whereTime)->groupBy('token')->select('token')->get()->toArray();
        if (empty($tokenArr))  exit('1');
        foreach ($tokenArr as $k => $v) {
            $app = Wechatapp::initialize($v['token']);
            //获取全局、区域配置信息
            $seatGlobalConfig = Cache::get('seatGlobalConfig_task_'.$v['token']);
            $seatRegionConfig = Cache::get('seatRegionConfig_task_'.$v['token']);
            if (!$seatGlobalConfig) {
                $seatGlobalConfig = \App\Models\Seat\Config::where('token',$v['token'])->first()->toArray();
                Cache::put('seatGlobalConfig_task_'.$v['token'],$seatGlobalConfig,10);
            }
            if (!$seatRegionConfig) {
                $temp = SeatRegion::where([['token',$v['token']],['pid','>',0]])->select(['id','name'])->get()->toArray();
                $seatRegionConfig = [];
                foreach ($temp as $val){
                    $seatRegionConfig[$val['id']] = $val['name'];
                }
                unset($val,$temp);
                Cache::put('seatRegionConfig_task_'.$v['token'],$seatRegionConfig,10);
            }


            //查出"当天"可签到的--进行通知  start
            $nowdate = date('Y-m-d H:i:s');
            $dataArr = SeatCurrBooking::where([['token',$v['token']],['status',0],['is_sendMsg',0],['sign_min','<',$nowdate],['sign_max','>',$nowdate]])->whereBetween('s_time',$whereTime)->with('chart')->get()->toArray();
            foreach ($dataArr as $ak => $av) {
                $text = '您预约的' . $seatRegionConfig[$av['chart']['region_id']] . ' ' . $av['chart']['numid'] . '号位置(' . substr($av['sign_min'],11,5) . '~' . substr($av['sign_max'],11,5) . ')已经可以签到了，请到图书馆进行扫码签到。';
                $sendRes = $this->sendTextMsg($app, $av['openid'], $text);
                unset($text);
                SeatCurrBooking::where('id',$av['id'])->update(['is_sendMsg' => 1]);
            }
            unset($dataArr, $ak, $av, $status);
            //先查出"当天"可签到的--进行通知  end


            //查出违约的--删除--并记录  start
            $dataArr = SeatCurrBooking::where([['token',$v['token']],['status',0],['sign_max','<',date('Y-m-d H:i:s')]])->with('chart')->get()->toArray();
            //违约：积分是否设置，如设置进行积分扣除
            $delArr = [];
            foreach ($dataArr as $bk => $bv) {
                $del = [
                    'token' => $v['token'],
                    'openid' => $bv['openid'],
                    'rdid' => $bv['rdid'],
                    'chart_id' => $bv['chart_id'],
                    's_time' => $bv['s_time'],
                    'e_time' => $bv['e_time'],
                    'status' => 3,
                    'sign_min' => $bv['sign_min'],
                    'sign_max' => $bv['sign_max'],
                    'mark' => $bv['mark']
                ];
                $insertId = SeatByBooking::create($del);

                //记入违约表
                $violationslog = [
                    'token' => $v['token'],
                    'rdid' => $bv['rdid'],
                    'booking_id' => $insertId,
                    'mark' => '未在规定时间签到',
                    'create_at' => date('Y-m-d H:i:s'),
                ];

                SeatViolationslog::create($violationslog);
                $disabled_date = date('Y-m-d H:i:s', time()+ 86400*$seatGlobalConfig['disabled_date']);
                SeatUser::where(['token'=> $v['token'], 'rdid'=> $bv['rdid']])->increment('violations',1);
                $user = SeatUser::where(['token'=> $v['token'], 'rdid'=> $bv['rdid']])->first(['id','violations']);
                if($user->violations >= $seatGlobalConfig['violate_num']){
                    SeatUser::where('id', $user->id)->update(['forbid_at'=>$disabled_date]);
                }

                $text = '通知: 您预约的' . $seatRegionConfig[$bv['chart']['region_id']] . ' ' . $bv['chart']['numid'] . '号位置由于未能在规定时间内到馆签到，已违约。请文明预约座位。';
                $this->sendTextMsg($app, $bv['openid'], $text);

                unset($text);
                $delArr[] = $bv['id'];
                unset($del);
            }
            if (count($delArr) > 0) {
                SeatCurrBooking::whereIn('id', [implode(',', $delArr)])->delete();
            }
            unset($dataArr, $bk, $bv, $delArr);
            //查出违约的--删除--并记录  end


            //查询出已签到的座位，时间到的，进行入座处理 并通知，并记录。 start
            $dataArr = SeatCurrBooking::where([['token',$v['token']],['status',1],['s_time','<=',date('Y-m-d H:i:s')]])->with('chart')->get()->toArray();
            $delArr = [];
            foreach ($dataArr as $ck => $cv) {
                $useUserID = SeatChart::where(['id' => $cv['chart_id']])->select(['seating_type', 'seated_id','rdid'])->first()->toArray();
                if($useUserID['rdid'] && $cv['rdid'] != $useUserID['rdid']){
                    if($useUserID['seating_type'] == 0){
                        $seated = SeatByScan::where('id', $useUserID['seated_id'])->first(['openid']);
                    }
                    else{
                        $seated = SeatByBooking::where('id', $useUserID['seated_id'])->first(['openid']);
                    }

                    $text = '通知: 您所坐的' . $seatRegionConfig[$cv['chart']['region_id']] .' ' . $cv['chart']['numid'] . '号位置由于现已被预约使用，请文明礼让座位!';
                    $this->sendTextMsg($app, $seated['openid'], $text);
                }

                $log = [
                    'token' => $v['token'],
                    'openid' => $cv['openid'],
                    'rdid' => $cv['rdid'],
                    'chart_id' => $cv['chart_id'],
                    's_time' => $cv['s_time'],
                    'e_time' => $cv['e_time'],
                    'status' => 1,
                    'sign_min' => $cv['sign_min'],
                    'sign_max' => $cv['sign_max'],
                    'sign_in'  => $cv['sign_in'],
                    'mark' => $cv['mark']
                ];
                $createId = SeatByBooking::create($log);

                if ($createId) {
                    SeatChart::where('id',$cv['chart_id'])->update(['status' => 2,'rdid'=>$cv['rdid'], 'seating_type' => '1', 'seated_id'=>$createId->id, 'queue_id'=>0]);
                    $text = '通知:您预约的' . $seatRegionConfig[$cv['chart']['region_id']] . $cv['chart']['numid'] . '号座位可以入座了';
                    $this->sendTextMsg($app, $createId['openid'], $text);
                    $delArr[] = $cv['id'];
                }
            }
            if (count($delArr) > 0) {
                SeatCurrBooking::whereIn('id', [implode(',', $delArr)])->delete();
            }
            unset($dataArr, $ck, $cv, $delArr);
            //查询出已签到的座位，时间到的，进行入座处理 并通知，并记录 end
        }
        exit;
    }

    /**
     * 调试用户数据模拟
     */
    public function testData()
    {
        $users = [
            0 => ["openid" => 'ofgxfuGkQ5Gvu3A2SFeVUgUbZEG0',
                "subscribe"=> 1,
                "headimgurl"=> 'http://thirdwx.qlogo.cn/mmopen/OZ3libxicno68UKa0cgOibwXFI5ymbg7I2yRPVVJ1IU2lxpyyhFGFW2UoMFJMFemOA2RickEiap5ZjfDKkWibsUNLVCvssvKxUcH3J/132',
                "sex"=>1,
                "subscribe_time"=> 1560838016,
                "unionid" => '',
                "language"=> 'zh_CN',
                "city"=> '广州',
                "province"=> '广东',
                "nickname" => 'Jay'
            ],
            1 => ["openid" => "oWcJl58-sF3wJgcGhouQo4M_zyKs",
                "subscribe"=> 1,
                "headimgurl"=> "http://thirdwx.qlogo.cn/mmopen/7N2JRaWooRAlQYEAQAWWXMQe2XoPxViaxxv0stWZbQSibZfDV5NWs1F9IdQnuzBcRQicLfIHtfBbOAZYl0Z5O2gOFt1Iic0EVNML/132",
                "sex"=>2,
                "subscribe_time"=> 1560842016,
                "unionid" => '',
                "language"=> 'zh_CN',
                "city"=> '济南',
                "province"=> '山东',
                "nickname" => '夏目的猫咪老师'
            ],
            2 => ["openid" => 'oWcJl5yc8HKITzOWLXmbH9rYDvtU',
                "subscribe"=> 1,
                "headimgurl"=> 'http://thirdwx.qlogo.cn/mmopen/cTG7qicia6apwXHmibMVRFpljnd78s3GicMuhYdn1LprHK0M8pUXZTJGu0zicI78DdsRk3JTyAic8MvWS1Rdr1IWeT5ozPEbeUxJSu/132',
                "sex"=>2,
                "subscribe_time"=> 1560852016,
                "unionid" => '',
                "language"=> 'zh_CN',
                "city"=> '淄博',
                "province"=> '广东',
                "nickname" => '曦兮'
            ],
            3 => ["openid" => 'oWcJl522fSvOGgErF7aAFIkAeJtI',
                "subscribe"=> 1,
                "headimgurl"=> 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6jf99cCLxFosp4T2MbEQMMcnyDL0Ts5GDUV23GmdAFkndagW5demlrBr1tNCEMofp3943Y9BVCjQ/132',
                "sex"=>2,
                "subscribe_time"=> 1560862016,
                "unionid" => '',
                "language"=> 'zh_CN',
                "city"=> '淄博',
                "province"=> '广东',
                "nickname" => '苗苗'
            ],
        ];
        $user = Session::get('wxUserInfo');
        if(!$user){
            $index = mt_rand(0,3);
            $user = $users[$index];
            Session::put('wxUserInfo', $user);
        }
        return $user;
    }

    /*****************
     *     Tools     *
     * ***************/
    protected function keyED($txt, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $tmp .= substr($txt, $i, 1) ^ substr($encrypt_key, $ctr, 1);
            $ctr++;
        }
        return $tmp;
    }

    protected function encrypt($txt, $key)
    {
        $encrypt_key = md5(mt_rand(0, 100));
        $ctr = 0;
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $tmp .= substr($encrypt_key, $ctr, 1) . (substr($txt, $i, 1) ^ substr($encrypt_key, $ctr, 1));
            $ctr++;
        }
        return $this->keyED($tmp, $key);
    }

    protected function decrypt($txt, $key)
    {
        $txt = $this->keyED($txt, $key);
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            $md5 = substr($txt, $i, 1);
            $i++;
            $tmp .= (substr($txt, $i, 1) ^ $md5);
        }
        return $tmp;
    }

    protected function encrypt_url($url, $key)
    {
        return rawurlencode(base64_encode($this->encrypt($url, $key)));
    }

    protected function decrypt_url($url, $key)
    {
        return $this->decrypt(base64_decode(rawurldecode($url)), $key);
    }

    /**
     * 坐标点距离计算
     * @param $lat1 起点维度
     * @param $lng1 起点经度
     * @param $lat2 终点维度
     * @param $lng2 终点经度
     * @param int $unit 单位：1:m  2:km
     * @param int $decimal 精度 小数点位数
     * @return float
     */
    public function distanceSimplify($lat1, $lng1, $lat2, $lng2, $unit=2, $decimal=2)
    {
        $EARTH_RADIUS = 6367000.0; //地球半径

        $dx = $lng1 - $lng2; // 经度差值
        $dy = $lat1 - $lat2; // 纬度差值
        $b = ($lat1 + $lat2) / 2.0; // 平均纬度
        $Lx = deg2rad($dx) * $EARTH_RADIUS * cos(deg2rad($b)); // 东西距离
        $Ly = $EARTH_RADIUS * deg2rad($dy); // 南北距离
        $distance = sqrt($Lx * $Lx + $Ly * $Ly);

        if($unit==2){
            $distance = $distance / 1000;
        }
        return round($distance, $decimal);

    }

    public function qiandao(Request $request, WebOAuthService $webOAuthService){
        $user = $this->getUserBySession($webOAuthService);
        $data = SeatUserBooking::where([['token', $this->token], ['user_id', $user['rdid']], ['status', 1]])->get(['id', 'numid', 'allow_min', 'allow_max']);
        if ($data->first()) {
            $now = time();
            foreach ($data as $k => $v) {
                if ($now >= strtotime($v['allow_min']) && $now <= strtotime($v['allow_max'])) {
                    SeatUserBooking::where('id', $v->id)->update(['status' => 2]);
                }
            }
            return $this->message('签到成功',true);
        } else {
            return $this->message('抱歉，暂无可签到的预约记录!',true);
        }
    }
}
