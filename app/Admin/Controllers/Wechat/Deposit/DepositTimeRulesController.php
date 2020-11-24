<?php

namespace App\Admin\Controllers\Wechat\Deposit;

use App\Models\Deposit\Deposit;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepositTimeRulesController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $data = Deposit::whereToken(session('wxtoken'))->first();
        if($data){
            $data['week1'] = explode('-',$data['week1']);
            $data['week2'] = explode('-',$data['week2']);
            $data['week3'] = explode('-',$data['week3']);
            $data['week4'] = explode('-',$data['week4']);
            $data['week5'] = explode('-',$data['week5']);
            $data['week6'] = explode('-',$data['week6']);
            $data['week0'] = explode('-',$data['week0']);
        }
        return $content->body(view('admin.deposit.time_rules_setting', $data));
    }
    public function config(Request $request){
        $data = $request->all();
        if($data){
            $config_data['token'] = session('wxtoken');
            $config_data['create_time'] = time();
            $config_data['week1'] = isset($data['week1_check']) ? 'false-00:00:00-00:00:00-00:00:00-00:00:00' : 'true-'.$data['week1_1'].'-'.$data['week1_2'].'-'.$data['week1_3'].'-'.$data['week1_4'];
            $config_data['week2'] = isset($data['week2_check']) ? 'false-00:00:00-00:00:00-00:00:00-00:00:00' : 'true-'.$data['week2_1'].'-'.$data['week2_2'].'-'.$data['week2_3'].'-'.$data['week2_4'];
            $config_data['week3'] = isset($data['week3_check']) ? 'false-00:00:00-00:00:00-00:00:00-00:00:00' : 'true-'.$data['week3_1'].'-'.$data['week3_2'].'-'.$data['week3_3'].'-'.$data['week3_4'];
            $config_data['week4'] = isset($data['week4_check']) ? 'false-00:00:00-00:00:00-00:00:00-00:00:00' : 'true-'.$data['week4_1'].'-'.$data['week4_2'].'-'.$data['week4_3'].'-'.$data['week4_4'];
            $config_data['week5'] = isset($data['week5_check']) ? 'false-00:00:00-00:00:00-00:00:00-00:00:00' : 'true-'.$data['week5_1'].'-'.$data['week5_2'].'-'.$data['week5_3'].'-'.$data['week5_4'];
            $config_data['week6'] = isset($data['week6_check']) ? 'false-00:00:00-00:00:00-00:00:00-00:00:00' : 'true-'.$data['week6_1'].'-'.$data['week6_2'].'-'.$data['week6_3'].'-'.$data['week6_4'];
            $config_data['week0'] = isset($data['week0_check']) ? 'false-00:00:00-00:00:00-00:00:00-00:00:00' : 'true-'.$data['week0_1'].'-'.$data['week0_2'].'-'.$data['week0_3'].'-'.$data['week0_4'];
            $config_data['black_rule'] = $request->input('black_rule','');
            if($config_data['token']){
                Deposit::where('token',$config_data['token'])->update($config_data);
            }else{
                Deposit::insert($config_data);
            }
        }
        return response()->json([
            'state' => 'true'
        ]);
    }

}
