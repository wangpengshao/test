<?php

namespace App\Admin\Controllers\Api;

//use App\Admin\Extensions\Button\ApiUserAlter;
use App\Api\Helpers\ApiResponse;
use App\Models\Wxuser;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

class ApiuseradminController extends AdminController
{
    use ApiResponse;

    protected $title = '接口授权';

    protected function grid()
    {
        $wxuserList = ['uWei' => '全部'];
        $wxuserList += Wxuser::pluck('wxname', 'token')->toArray();

        $grid = new Grid(new User);
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('username', '用户名');
                $filter->like('s_token', '馆token');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->like('name', '客户端');
            });
        });
        $grid->column('name', '客户端信息')->label('primary');
        $grid->model()->orderBy('created_at');
        $grid->column('s_token', '授权公众号')->display(function ($s_token) use ($wxuserList) {
            if (in_array('uWei', $s_token)) {
                return '<span class="badge bg-black">全部</span>';
            }
            if ($s_token) {
                $str = '';
                foreach ($s_token as $k => $v) {
                    $str .= "<span class='badge bg-gray'>{$wxuserList[$v]}</span>&nbsp;";
                }
                return $str;
            }
            return '';
        });

        $grid->column('username', '用户名(username)')->copyable();
        $grid->column('secretkey', 'secretkey')->copyable();
        $grid->column('rate_limit', '频率/min');
        $grid->column('r_allnum', '请求 (次)')->display(function ($r_allnum) {
            $allNum = Redis::get('apiAuth:user:' . $this->id . ':allNum');
            return ($allNum) ?: $r_allnum;
        });

//        $grid->column('r_num', '剩余 (次)')->display(function ($r_num) {
//            $rNum = Redis::get('apiAuth:user:' . $this->id . ':rNum');
//            return ($rNum) ?: $r_num;
//        });

        $grid->expires_at('到期时间')->sortable()->display(function ($text) {
            $dt = Carbon::parse($text);
            $now = Carbon::now();
            $days = $dt->diffInDays($now);
            if ($dt->lt($now)) {
                return "<span class='badge bg-red'>已过期 {$days} 天</span>";
            }
            if ($days <= 30) {
                return "<span class='badge bg-yellow'>{$dt->diffForHumans($now)}</span>";
            }
            return $dt->diffForHumans($now);
        });

        $grid->column('created_at', '创建时间')->sortable();

        $states = [
            'on' => ['value' => 1, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
        ];
        $grid->status('状态')->switch($states)->sortable();
        $grid->actions(function ($actions) {
//            $actions->append(new ApiUserAlter($actions->row->id));
            $actions->disableDelete();
            $actions->disableView();
        });
        return $grid;
    }

    protected function form()
    {
        $form = new Form(new User);
        $form->tools(function (Form\Tools $tools) {
            // 去掉`查看`按钮
            $tools->disableView();
        });

        if ($form->isCreating()) {
            $form->text('username', '用户名')->default(uniqid('uwei'))->required();
        } else {
            $form->display('username', '用户名');
            $form->display('secretkey');
        }

        $form->embeds('name', '客户端信息', function ($form) {
            $form->text('a', '公司简称')->datalist([
                '图创' => '图创',
                '嘉图' => '嘉图',
                '超星' => '超星',
                '海恒' => '海恒',
            ])->required();
            $form->text('b', '内部系统')->help('公司内部，需要填写此项')->datalist([
                '云书馆' => '云书馆',
                '活动积分' => '活动积分',
                'interlib' => 'interlib',
                'opac' => 'opac',
                'openlib' => 'openlib',
                '全媒体' => '全媒体',
                '大数据' => '大数据',
            ]);
            $form->text('c', '用途简述')->datalist([
                '消息推送' => '消息推送',
                'js_sdk' => 'js_sdk',
                '微信授权' => '微信授权',
            ])->help('简单描述文字长度最好不要超过10个,可以在后边添加补充,如消息推送(催还)')->required();
        });

        $form->number('rate_limit', '频率')->default(100);
        $form->datetime('expires_at', '过期时间')->format('YYYY-MM-DD HH:mm:ss')
            ->default(Carbon::now()->addYear(1))->required();
        $states = [
            'on' => ['value' => 1, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
        ];
        $form->switch('status', '状态')->states($states);
        $op = [
            'opBase' => '静默授权( code 换取 openid )',
            'opInfo' => '网页授权( code 换取 粉丝信息 )',
            'fansInfo' => 'openid 获取粉丝信息',
            'opGetRe' => 'openid 获取读者证',
            'reGetOp' => '读者证 获取openid',
//                'bindRe' => '绑定读者',
//                'unbindRe' => '解绑读者',
            'sendTemplate' => '发送模版消息',
            'sendTemplateForOpenid' => '发送模板消息（openid）',
            'sendMessage' => '发送客服消息(文本)',
            'groupSMes' => '群发客服消息(文本)',
            'sendImgMes' => '发送客服消息(图文)',
            'groupSImgMes' => '群发客服消息(图文)',
//                'getJdk' => '微信JDK配置',
            'getAcToken' => '获取 access_token',
            'getJsSdk' => '获取 js_sdk',
            'getActUser' => '获取 微信48h内交互用户',
        ];

        $form->checkbox('scopes', '接口权限')->options($op)->default(
            function ($form) {
                return explode(',', $form->model()->scopes);
            }
        )->stacked();

        $form->multipleSelect('s_token', '公众号权限')->options(function ($code) {
            if (is_array($code) && count($code) > 0) {
                $op = ['uWei' => '全部'];
                $op += Wxuser::pluck('wxname', 'token')->toArray();
                return $op;
            }
        })->ajax('/admin/apiuseradmin/wxUser/search', 'token', 'wxname')
            ->placeholder('输入 公众号名称或者token 进行搜索,可填写多个!')
            ->help('注:授权所有公众号请输入"全部"');

        $form->display('created_at', __('Created at'));
        $form->display('updated_at', __('Updated at'));

        $form->saving(function (Form $form) {
            if (\request()->isMethod('post')) {
                $exists = User::where('username', $form->username)->exists();
                if ($exists) {
                    $message = sprintf('抱歉,用户名为 %s 用户已存在！', $form->username);
                    $error = new MessageBag([
                        'title' => '提示',
                        'message' => $message,
                    ]);
                    return back()->with(compact('error'));
                }
                $secretkey = Str::uuid()->getHex();
                $form->model()->secretkey = $secretkey;
                $form->model()->password = bcrypt($secretkey);
            }

        });
//        $form->saved(function (Form $form) {
//            if (\request()->isMethod('post')) {
//                $user = $form->model();
//                Redis::set('apiAuth:user:' . $user['id'] . ':rNum', 200000);
//            }
//        });

        $form->deleting(function () {
            $id = current(\request()->route()->parameters);
            if ($id !== false) {
                $id = explode(',', $id);
                foreach ($id as $k => $v) {
                    Redis::del('apiAuth:user:' . $v . ':allNum', 'apiAuth:user:' . $v . ':rNum');
                }
            }
        });

        return $form;
    }

    public function userAlter(Request $request)
    {
        $redis = Redis::connection();
        $id = $request->input('id');
        $number = $request->input('number');
        $user = User::find($id);
        if (empty($user)) {
            return $this->notFond();
        }
        //增加记录Log
        DB::table('users_update_log')->insert([
            'user_id' => $id,
            'operate_u' => Admin::user()->id,
            'number' => $number,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $key = "apiAuth:user:{$id}:rNum";
        $cache = $redis->get($key);
        if (empty($cache)) {
            if ($number > 0) {
                $user->increment('r_num', $number);
            } else {
                $number = abs($number);
                $user->decrement('r_num', $number);
            }
        } else {
            if ($number > 0) {
                $redis->incrby($key, $number);
            } else {
                $number = abs($number);
                $redis->decrby($key, $number);
            }
        }
        return $this->message('编辑成功', true);
    }

    public function searchWxuser(Request $request)
    {
        $q = $request->get('q');
        if ($q == '全部') {
            $response = [
                'current_page' => 1,
                'data' => [['token' => 'uWei', 'wxname' => '全部']],
                'from' => 1,
                'last_page' => 1,
                'next_page_url' => '',
                'to' => 1,
                'total' => 1
            ];
            return $response;
        }
        $is_zw = preg_match("/[\x7f-\xff]/", $q);
        $model = Wxuser::select('token', 'wxname')->when($is_zw, function ($model) use ($q) {
            return $model->where('wxname', 'like', "%{$q}%");
        }, function ($model) use ($q) {
            return $model->where('token', 'like', "%{$q}%");
        });
        return $model->paginate();
    }
}
