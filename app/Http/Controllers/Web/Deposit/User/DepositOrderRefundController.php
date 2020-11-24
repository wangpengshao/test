<?php

namespace App\Http\Controllers\Web\Deposit\User;

use App\Models\Deposit\Deposit;
use App\Models\Deposit\DepositLog;
use App\Models\Deposit\DepositEveryday;
use App\Models\Deposit\DepositUser;
use App\Models\Wxuser;
use App\Http\Controllers\Controller;
use App\Services\OpacSoap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Services\OpenlibService;
use App\Api\Helpers\ApiResponse;

class DepositOrderRefundController extends Controller
{
    use ApiResponse;

    /**
     * @time   2019/9/4
     * 接收token显示页面
     * @author wsp@xxx.com wsp
     */
    public function index(Request $request)
    {
        $token = $request->route('token');//获取token值
        $deposit = Deposit::where('token', $token)->first();
        // 判断押金系统状态是否开启
        if ($deposit && $deposit['status']) {
            return view('web.deposit.user.orderRefund', $deposit);
        } else {
            // 返回关闭系统的页面
            return view('web.deposit.user.error');
        }
    }

    /**
     * @time   2019/9/4
     * 根据身份证获取读者证信息
     * @author wsp@xxx.com wsp
     */
    //根据身份证获取读者证信息
    public function getReadersByIdcard(Request $request)
    {
        $token = $request->input('token');
        $idcard = $request->input('idCard');
        if ($this->is_idcard($idcard)) {
            $openlibService = OpenlibService::make($token);
            $info = $openlibService->searchreader('', $idcard);
            if ($info) {
                return $this->success(['result' => $info], 'successful');
            }
        } else {
            return $this->message('身份证件信息有误', 'failed');
        }
    }

    /**
     * @time   2019/9/4
     * 身份证格式验证
     * @author wsp@xxx.com wsp
     */
    public function is_idcard($id)
    {
        $id = strtoupper($id);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if (!preg_match($regx, $id)) {
            return FALSE;
        }
        if (15 == strlen($id)) {
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
            @preg_match($regx, $id, $arr_split);
            // 检查生日日期是否正确
            $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            if (!strtotime($dtm_birth)) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            // 检查18位
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            if (!strtotime($dtm_birth))//检查生日日期是否正确
            {
                return FALSE;
            } else {
                // 检验18位身份证的校验码是否正确。
                // 校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $sign = 0;
                for ($i = 0; $i < 17; $i++) {
                    $b = (int)$id{$i};
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n = $sign % 11;
                $val_num = $arr_ch[$n];
                if ($val_num != substr($id, 17, 1)) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            }
        }
    }

    /**
     * @time   2019/9/4
     * 获取读者押金数据
     * @author wsp@xxx.com wsp
     */
    public function ajaxGetMoney(Request $request)
    {
        $rdid = $request->input('rdid');
        $token = $request->input('token');
        $deposit = Redis::get('deposit');
        if ($deposit) {
            return json_encode(array('status' => 'successful', 'deposit' => $deposit));
        } else {
            $userInfo = $this->getReadInfo($rdid, $token);
            if ($userInfo) {
                $reader = array('rdid'    =>  $userInfo['return']['rdid'],
                                'name'    =>  $userInfo['return']['rdName'],
                                'idCard'  =>  $userInfo['return']['rdCertify'],
                                'deposit' =>  $userInfo['return']['rdDeposit'],
                                );
                Redis::set('idCard', $reader['idCard']);
                Redis::set('name', $reader['name']);
                Redis::set('deposit', $reader['deposit']);
                return $this->success(['deposit' => $reader['deposit']], 'successful');
            } else {
                return $this->message('', 'failed');
            }
        }
    }

    /**
     * @time   2019/9/4
     * 获取读者的信息
     * @author wsp@xxx.com wsp
     */
    private function getReadInfo($rdid, $token)
    {
        $wxuser = Wxuser::getCache($token);
        $arguments = [
            'rdid' => $rdid,
            'key' => 'TCSOFT_INERLIB'
        ];
        $opacSoap = OpacSoap::make($wxuser, 'webservice/readerWebservice');
        $response = $opacSoap->requestFunction('getReader', $arguments);
        if (Arr::get($response, 'errMes') || empty($response) ) {                        //...异常处理
            return false;
        }
        ///初始化
        return $response;
    }

    /**
     * @time   2019/9/4
     * 读者预约操作
     * @author wsp@xxx.com wsp
     */
    public function subscribe(Request $request)
    {
        $data = $request->all();
        // 取缓存中的身份证号
        $redis_data = Redis::get('idCard');
        // 判断是否有提交过来读书者证信息,则进行一系列的预约流程操作
        if (isset($data['rdid']) && isset($data['time']) && isset($data['date'])) {
            // 判断是否已经存在读书者证号，若不存在，则去调用接口获取读书者信息
            if (!$redis_data) {
                $readerInfo = $this->getReadInfo($data['rdid'], $data['token']);
                $reader = array('rdid'    => $readerInfo['return']['rdid'],
                                'name'    => $readerInfo['return']['rdName'],
                                'idCard'  => $readerInfo['return']['rdCertify'],
                                'deposit' => $readerInfo['return']['rdDeposit'],
                                );
                Redis::set('idCard', $reader['idCard']);
                Redis::set('name', $reader['name']);
                Redis::set('deposit', $reader['deposit']);
            }
            //将读者信息进行赋值
            $data['idCard']       =  Redis::get('idCard');
            $data['name']         =  Redis::get('name');
            $data['deposit']      =  Redis::get('deposit');
            $data['yuyue_date']   =  $data['date'];
            $data['yuyue_time']   =  $data['time'];
            $data['create_time']  =  time();
            $data['from'] = 2;
            $data['token'] = $data['token'];
            if (!isset($data['deposit']) || $data['deposit'] == 0) {
                return $this->message('当前证件没有押金，无需预约', false);
            }
            // 查询客户是否已经被拉黑了
            $block = DepositLog::where(['rdid' => $data['rdid'], 'token' => $data['token'], 'status' => 4])->first();
            if ($block) {
                return $this->message('当前客户已被列为黑名单，无法预约', false);
            }
            $deposit = Deposit::where('token', $data['token'])->first();
            $data['deposit_id'] = $deposit['id'];
            $res1 = DepositEveryday::where(['deposit_id' => $deposit['id'], 'date' => $data['yuyue_date']])->first();
            // 每日总额变化,若是押金表中未存在该值，则赋值余额为总数
            if (!$res1) {
                $everyday['deposit_id']   =   $deposit['id'];
                $everyday['amount']       =   $deposit['total_money'];
                $everyday['balance']      =   $deposit['total_money'];
                $everyday['date']         =   $data['yuyue_date'];
                $everyday['update_time']  =   time();
                DepositEveryday::insert($everyday);
            }
            $res2 = DepositUser::where('rdid', $data['rdid'])->first();
            // 退押金读者,若不存在该押金读者号，则添加到user表中
            if (!$res2) {
                $user['token']        =   $data['token'];
                $user['rdid']         =   $data['rdid'];
                $user['name']         =   $data['name'];
                $user['idCard']       =   $data['idCard'];
                $user['deposit']      =   $data['deposit'];
                $user['create_time']  =   time();
                DepositUser::insert($user);
            }
            $log = DepositLog::where(['rdid' => $data['rdid'], 'token' => $data['token']])->orderBy('create_time', 'desc')->first();
            $black = explode(',', $deposit['black_rule']);
            $black = [];
            foreach ($black as $v) {
                $tm = explode('-', $v);
                $blacks[$tm[0]] = $tm[1];
            }
            if ($res2['loss_sum']) {
                $endTime = strtotime($log['yuyue_date']) + $res2['loss_sum'] * 86400;
                if (strtotime($data['yuyue_date']) <= $endTime) {
                    return $this->message('逾约已达' . $res2['loss_sum'] . '次，截止' . date('m-d', $endTime) . '日暂停预约服务', false);
                }
            }
            // 查看是否已经有提交预约记录了
            $oneLog = DepositLog::where(['rdid' => $data['rdid'], 'token' => $data['token'], 'status' => 0])->first();
            // 有预约记录，则提示已预约
            if ($oneLog) {
                return $this->message('已预约了' . $oneLog['yuyue_date'] . '， 请勿重复操作', false);
            }
            // 查看是否已经办理退证了
            $oneLog2 = DepositLog::where(['rdid' => $data['rdid'], 'token' => $data['token'], 'status' => 1])->first();
            if ($oneLog2) {
                return $this->message('当前读者证已办理退证，请勿重复操作', false);
            }
            // 进行余额相减操作
            $res3 = DepositEveryday::where(['deposit_id' => $deposit['id'], 'date' => $data['yuyue_date']])->decrement('balance', $data['deposit']);
            $res4 = false;
            unset($data['date']);
            unset($data['time']);
            unset($data['_token']);
            unset($data['money']);
            // 余额充足，添加记录
            if ($res3) {
                $res4 = DepositLog::insert($data);
            } else {
                return $this->message('预备押金余额不足', false);
            }
            if ($res4) {
                return $this->success(['result' => $data], 'successful');
            } else {
                return $this->message('预约失败', true);
            }
        } else {
            return $this->message('缺少参数', false);
        }
    }

    /**
     * @time   2019/9/4
     * 读者预约操作
     * @author wsp@xxx.com wsp
     */
    public function cancelDeposit(Request $request)
    {
        $type = $request->input('type');
        $depositId = $request->input('depositId');
        $rdid = $request->input('rdid');
        if (empty($depositId) || empty($rdid)) {
            return $this->message('参数缺失', false);
        }
        $log = DepositLog::where(['id' => $depositId, 'rdid' => $rdid])->first();
        // 判断是否操作类型为3（取消）,若取消类型不为3，则进行软删除操作
        if ($type == 3) {
            if (!$log) {
                return $this->message('预约记录不存在', false);
            }
            if ($log) {
                switch ($log['status']) {
                    case 1:
                        return $this->message('当前证已办理退款', false);
                        break;
                    case 2:
                        return $this->message('本次预约已逾期', false);
                        break;
                    case 3:
                        return $this->message('本次预约已取消', false);
                        break;
                }
            }
            // 开启事物
            DB::beginTransaction();
            // 补捉异常
            try {
                DepositEveryday::where(['deposit_id' => $log['deposit_id'], 'date' => $log['yuyue_date']])->increment('balance', $log['deposit']);
                $result = DepositLog::where('id', $log['id'])->update(array('status' => 3));
                if ($result) {
                    // 提交保存
                    DB::commit();
                    return $this->success(['errCode' => 200, 'msg' => '操作成功'], 'successful');
                } else {
                    DB::rollBack();
                    return $this->success(['errCode' => 400, 'msg' => '操作失败'], true);
                }
            } catch (Exception $e) {
                // 数据回滚, 当try中的语句抛出异常。
                DB::rollBack();
            }
        } else {
            $result = DepositLog::where('id', $log['id'])->insert(array('client_status' => 1));
            if ($result) {
                return $this->success(['errCode' => 200, 'msg' => '操作成功'], 'successful');
            } else {
                return $this->success(['errCode' => 400, 'msg' => '操作失败'], true);
            }
        }
    }

    /**
     * @time   2019/9/4
     * 获取预约分段时间
     * @author wsp@xxx.com wsp
     */
    public function ajaxIndex(Request $request)
    {
        $token = $request->route('token');
        if ($token) {
            // 预约可选天数
            $days = 15;
            $deposit = Deposit::where('token', $token)->first();
            if ($deposit) {
                // 可预约最早时间
                $a = strtotime(date('Y-m-d', strtotime('+' . $deposit['before_time'] . 'day')));
                $initDate = date('Y-m-d', $a);
                // 可预约最晚时间
                $maxDate = date('Y-m-d', strtotime($initDate) + $days * 86400);
                // 节假日
                $holiday = explode(',', $deposit['holiday']);
                // 时间段计算 start，获取分断时间
                $blocktimes = 60 * $deposit['block'];
                $changeData = [];
                for ($i = 0; $i < $days; $i++) {
                    $weekIndex = date('w', $a);
                    $weekData = explode('-', $deposit['week' . $weekIndex]);
                    if ($weekData[0] == 'false') {
                        //若设置头为false，则说明是当天是闭馆的
                        $changeData[date('Y-m-d', $a)] = 'false';
                    } else {
                        if (in_array(date('m-d', $a), $holiday)) {
                            $changeData[date('Y-m-d', $a)] = 'false';
                        } else {
                            $block = [];
                            // 获取上午的时间段
                            $blocks = floor((strtotime('2018-12-21' . $weekData[2]) - strtotime('2018-12-21' . $weekData[1])) / $blocktimes);
                            for ($j = 0; $j < $blocks; $j++) {
                                // 将上午时间段分割
                                $block[] = date('H:i:s', strtotime('2018-12-21' . $weekData[1]) + $j * $blocktimes);
                            }
                            // 获取下午的时间段
                            $blocks = floor((strtotime('2018-12-21' . $weekData[4]) - strtotime('2018-12-21' . $weekData[3])) / $blocktimes);
                            for ($j = 0; $j < $blocks; $j++) {
                                $block[] = date('H:i:s', strtotime('2018-12-21' . $weekData[3]) + $j * $blocktimes);
                            }
                            // 当天的上下午所有时间段
                            $changeData[date('Y-m-d', $a)] = $block;
                        }
                    }
                    $a += 86400;
                }
                // 时间段计算 end
                echo json_encode(array('data' => $deposit, 'changeData' => $changeData));
            }
        }
    }

    /**
     * @time   2019/9/4
     * 获取预约记录
     * @author wsp@xxx.com wsp
     */
    public function record(Request $request)
    {
        $token = $request->input('token');
//        $rdid = '5000009';
        $rdid = $request->input('rdid');
        // 如果不存在身份证号
        if (!isset($rdid)) {
            return $this->message('参数缺失', 'failed');
        }
        // 查询该身份证号下的预约记录
        $data = DepositLog::where(['rdid' => $rdid, 'token' => $token])->orderBy('create_time', 'desc')->get()->toArray();
        if (!$data) {
            return $this->message('目前没有预约记录', 'failed');
        }
        // 将搜索的数据成功返回
        return $this->success(['errCode' => 0, 'result' => $data], true);
    }
}
