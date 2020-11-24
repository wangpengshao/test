<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Accredit;
//use App\Models\ChinaArea;
use App\Models\Relationusers;
use App\Models\RelationWxuser;
use App\Models\Wxuser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;

//use Encore\Admin\Widgets\Table;
//use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

/**
 * Class WxuserController
 * @package App\Admin\Controllers
 */
class WxuserController extends Controller
{
    use HasResourceActions;

    /**
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('公众号管理')->description('列表')->body($this->grid());
    }

    /**
     * @param         $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $exists = RelationWxuser::where(['wxuser_id' => $id, 'user_id' => Admin::user()->id])->exists();
        if ($exists == false) {
            return $content->withError('非法访问,请返回!');
        }
        return $content->header('公众号管理')->description('...')->body($this->form()->edit($id));
    }

    /**
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content->header('公众号管理')->description('...')->body($this->form());
    }

    /**
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Wxuser);
        //判断是否管理员权限
        $isRole = Admin::user()->isRole('管理');

        $grid->filter(function ($filter) use ($isRole) {
            $filter->disableIdFilter();
            $filter->like('token', 'token');
            $filter->like('wxname', '公众号名称');
            if ($isRole) {
                $filter->expand();
            }
        });

        $grid->actions(function ($actions) {
            $actions->append(new Accredit($actions->row->token));
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->header(function () {
            return "<div class='callout callout-success'>
<h5><i class='icon fa  fa-lightbulb-o'></i> 提示</h5>
<h5>1.点击操作栏 <i class='fa fa-paper-plane'></i> 图标确认授权即可进入管理界面</h5>
<h5>2.由于系统的编码技术较前沿，国内众多浏览器尚未支持兼容，如出现无法正常授权，
<a target='_blank' href='https://dl.google.com/tag/s/appguid%3D%7B8A69D345-D564-463C-AFF1-A69D9E530F96%7D%26iid%3D%7B5B183E16-50AB-983A-6503-F82B8E29B3C1%7D%26lang%3Dzh-CN%26browser%3D4%26usagestats%3D1%26appname%3DGoogle%2520Chrome%26needsadmin%3Dprefers%26ap%3Dx64-stable-statsdef_1%26installdataindex%3Dempty/update2/installers/ChromeSetup.exe'>请点击这里</a> 
下载\"谷歌浏览器\"进行安装使用</h5>
<h5></h5>
</div>";
        });

        if (!$isRole) {
            $wxuserList = RelationWxuser::where('user_id', Admin::user()->id)->pluck('wxuser_id');
            $grid->model()->whereIn('id', $wxuserList);
        }
        $grid->model()->orderBy('id', 'desc');
        // 默认为每页5条
        $grid->paginate(5);

        $grid->token('token')->copyable();
        $grid->wxname('公众号名称');
        $grid->headerpic('logo')->image('', 65, 65);
        $grid->type('类型')->using([
            '1' => '<span class="label label-info">服务号</span>',
            '0' => '<span class="label label-success">订阅号</span>',
        ]);

        $grid->column('授权url')->display(function () {
            return route('wechatNotice', $this->token);
        });

        $grid->column('auth_type', '认证方式')->using([
            '1' => '<span class="label label-primary">openlib</span>',
            '2' => '<span class="label label-default">opac</span>',
            '3' => '<span class="label label-default">统一认证</span>',
            '4' => '<span class="label label-warning">联盟认证</span>',
        ]);
//        $grid->column('定位')->openMap(function () {
//            return [$this->lat, $this->lng];
//        }, '位置');
//        $grid->column('详情')->expand(function () {
//            $showData = Arr::only($this->toArray(), ['wxid', 'appid', 'appsecret', 'opacurl', 'openlib_url', 'libcode', 'glc']);
//            return new Table([], $showData);
//        }, '查看');
        $grid->updated_at('操作时间');
        return $grid;
    }

    /**
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Wxuser);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();          // 去掉`删除`按钮
            $tools->disableView();           // 去掉`查看`按钮
        });
        $imgArray = [];
        $form->tab('微信基础配置', function ($form) use (&$imgArray) {
            $form->display('token', 'Token');
            $form->image('headerpic', '头像')->move(materialUrl())->uniqueName();
            $form->text('wxname', '公众号名称')->placeholder('必填')->rules('required');
            $typeStates = [
                'on' => ['value' => 0, 'text' => '订阅号', 'color' => 'success'],
                'off' => ['value' => 1, 'text' => '服务号', 'color' => 'success'],
            ];
            $form->switch('type', '公众号类型')->states($typeStates)->help('公众号必须通过认证!');
            $form->image('qr_code', '关注二维码')->move(materialUrl())->uniqueName();
            $form->text('wxid', '公众号原始id')->placeholder('必填')->rules('required');
            $form->text('appid', 'AppID')->placeholder('必填')->rules('required');
            $form->text('appsecret', 'AppSecret')->placeholder('必填')->rules('required');
            $form->text('keytoken', '令牌Token')->placeholder('必填')->rules('required');
            $form->text('aes_key', '加解密密钥')->placeholder('必填')->rules('required');
            $form->divider();
            $form->text('old_token', '旧版Token');


            /* 素材库 上传图片 例子 start */
            $form->hidden(config('materialPR') . 'headerpic');
            $form->hidden(config('materialPR') . 'qr_code');
            $imgArray = [config('materialPR') . 'headerpic', config('materialPR') . 'qr_code'];
            $form->ignore($imgArray);
            /* 素材库 上传图片 例子 end */
        });

        if (Admin::user()->isRole('管理')) {

            $form->tab('门户配置', function ($form) {
                $states = [
                    'on' => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                ];
                $form->switch('status', '公众号状态')->states($states);

                $options = [
                    0 => '模版一  [ 2018新版(绿色) ]',
                    1 => '模版二  [ 九宫格 ]',
                ];
                $form->select('template_id', '主题模版')->options($options)->default(0)->rules('required');
                $form->switch('activity_sw', '活动开关')->states($states);
                $form->switch('guesslike_sw', '猜你喜欢开关')->states($states);
                $form->switch('newbook_sw', '新书推荐开关')->states($states);
                $form->switch('yujie_sw', '预借开关')->states($states);
                $form->switch('yuyue_sw', '预约开关')->states($states);
                $form->switch('knowledge_sw', '知识库开关')->states($states);

                $payOptions = [
                    0 => '公众号',
                    1 => '工商银行聚合支付',
                ];
                $form->select('payment_opt', '支付方式')->options($payOptions)->default(0);
                $form->divider();
                $qrStates = [
                    0 => '关闭',
                    1 => '1.0版',
                    2 => '2.0版',
                    10 => '静态版'
                ];
                $form->select('qr_type', '证二维码类型')->options($qrStates);
                $form->divider();
                $form->map('lat', 'lng', '地图');

//            $form->select('address.province_id', '省')->options(
//                ChinaArea::province()->pluck('name', 'id')
//            )->load('address.city_id', '/admin/api/china/city');
//
//            $form->select('address.city_id', '市')->options(function ($id) {
//                return ChinaArea::options($id);
//            })->load('address.district_id', '/admin/api/china/district');
//            $form->select('address.district_id', '区')->options(function ($id) {
//                return ChinaArea::options($id);
//            });

                $form->text('address.address', '详细地址');
            });

            $form->tab('业务系统', function ($form) {
                $options = [
                    1 => 'openlib',
                    2 => 'opac',
                    3 => '统一认证',
                    4 => '联盟认证'
                ];
                $form->select('auth_type', '认证方式')->options($options)->default(1)->rules('required');
                $form->radio('is_cluster', '集群')->options([1 => '是', 0 => '否'])->default(0);
                $form->divider();
                $form->text('glc', '全局馆ID');
                $form->text('libcode', '分馆ID');
                $form->divider();
                $form->url('opacurl', 'opac-地址');
                $form->text('opackey', 'opac-key');
                $form->email('opac_email', 'opac-邮箱')->help('接口异常通知邮箱');
                $form->divider();
                $form->url('openlib_url', 'openlib-地址');
                $form->text('openlib_opuser', 'openlib-操作员');
                $form->email('openlib_email', 'openlib-邮箱')->help('接口异常通知邮箱');

                $form->hidden('openlib_appid')->default('weixin');
                $form->hidden('openlib_secret')->default('6b5bf263d766d5d5603817ead799f382');
                $form->divider();
                $form->url('activity_url', '活动-地址');
                $form->url('opcs_url', '荐购-地址');
                $form->url('ushop_url', 'U书快借-地址');
                $form->url('sso_url', 'SSO数字资源-地址');
                $form->url('knowledge_url', '知识库-地址');

                $form->divider();
                $form->url('sms_url', '短信平台地址')->help('该选项为图创短信平台,其他短信平台无需填写该项');
                $form->text('sms_u', '平台账号');
                $form->text('sms_p', '平台密码');

            });

            $form->tab('支付配置', function ($form) {
                $form->text('payconfig.app_id', 'Appid');
                $form->text('payconfig.mch_id', '商户号')->help('注意!如要上传cert、key文件,请先返回列表页执行授权操作!');

                if (session('wxtoken')) {
                    $form->text('payconfig.key', 'API密钥');
                    $form->file('payconfig.cert_path', 'cert')
                        ->disk('admin')->move(materialUrl() . '/wxPay')->attribute('hideMaterial')->removable();
                    $form->file('payconfig.key_path', 'key')
                        ->disk('admin')->move(materialUrl() . '/wxPay')->attribute('hideMaterial')->removable();
                }
            });

            $form->tab('聚合支付', function ($form) {
                $form->divider('工商银行公众号聚合支付');
                $form->text('aggregatepayment.icbc_app_id', 'Appid');
                $form->text('aggregatepayment.icbc_mer_id', '商户号');
                if (session('wxtoken')) {
                    $form->text('aggregatepayment.icbc_sign_type', '签名类型');
                    $form->file('aggregatepayment.icbc_private_key', '签名私钥')
                        ->disk('admin')->move(materialUrl() . '/aggregatePayment')->attribute('hideMaterial')->removable();
                    $form->file('aggregatepayment.icbc_public_key', '签名公钥')
                        ->disk('admin')->move(materialUrl() . '/aggregatePayment')->attribute('hideMaterial')->removable();
                    $form->file('aggregatepayment.icbc_geteway_publickey', '网关公钥')
                        ->disk('admin')->move(materialUrl() . '/aggregatePayment')->attribute('hideMaterial')->removable();
                    $form->text('aggregatepayment.icbc_encrypt_type', '加密类型');
                    $form->text('aggregatepayment.icbc_encrypt_key', '加密key');
                }
            });

            $form->tab('事件关联', function ($form) {
                $form->select('event_type', '事件类型')->options([0 => '无', 1 => '集卡活动']);
                $form->number('event_id', '事件ID');
                $form->datetime('event_s_at', '开始时间');
                $form->datetime('event_e_at', '结束时间');
            });

            $form->tab('拓展配置', function ($form) {
                $states = [
                    'on' => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                ];
                $form->divider('小程序-电子资源');
                $form->switch('miniresources.status', '接口授权')->states($states);
                $form->switch('otherconfig.mn_resources_sw', '详情二维码')->states($states)
                    ->help('书目详情如存在电子资源则会显示图标(电子书或音频),点击会弹出小程序的二维码');
                $form->text('otherconfig.mn_resources_appid', 'appid');
                $form->text('otherconfig.mn_resources_key', 'key')->help('开启详情二维码功能,需填appid,key两项');
                $form->divider('密码强度检查');
                $form->switch('otherconfig.pw_check_sw', '检验开关')->states($states)->default(0)
                    ->help('开启则自动使用弱密码检验功能，关闭则功能隐藏');
                $form->number('otherconfig.pw_min_length', '最小长度')->default(0);
                $form->number('otherconfig.pw_max_length', '最大长度')->help('填0为不限制,最大跟最小相等时即固定长度')->default(0);
                $options = [
                    1 => '不限制字符类型',
                    2 => '只允许数字',
                    3 => '只允许字母',
                    4 => '数字和字母混合',
                    5 => '数字或字母都可以',
                    6 => '必须数字字母特殊字符',
                ];
                $form->select('otherconfig.pw_type', '类型')->options($options)->default(1);
                $form->text('otherconfig.pw_prompt', '提示')->default('');

                $form->divider('联盟认证');
                $form->url('otherconfig.union_url', '接口地址')->help('开启联盟认证方式需要填写此项!');

                $form->divider('其它定制');
                $form->switch('otherconfig.vue_nav_sw', '导航开关')->states($states)->default(1)
                    ->help('开启微门户导航则正常显示，关闭则导航隐藏');
                $form->number('otherconfig.appointment_min_day', '默认预约(天)')->default(7);
                $form->number('otherconfig.appointment_max_day', '最大可预约(天)')->default(30)->help('由当前的时间往后顺延天数');

                $form->number('otherconfig.tplmsg_group_num', '模板消息群发次数')->default(50);
                $form->number('otherconfig.custommsg_group_num', '客服消息群发次数')->default(50);
            });
        }


        $form->tab('其他信息', function ($form) {
            $form->email('email', '邮箱');
            $form->mobile('phone', '号码')->options(['mask' => '999 9999 9999'])->help('紧急主要联系人');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '修改时间');
        });

        $form->hidden('user_id')->value(Admin::user()->id);
        $form->saving(function (Form $form) use ($imgArray) {
            $model = $form->model();
            if (empty($model->token)) {
                $model->token = Str::uuid()->getNodeHex();
                $model->user_id = Admin::user()->id;
            } else {
                $wxuser_id = $model->id;
                $exists = RelationWxuser::where(['wxuser_id' => $wxuser_id, 'user_id' => Admin::user()->id])->exists();
                if ($exists == false) {
                    $error = new MessageBag([
                        'title' => '非法操作',
                        'message' => '抱歉你已失去编辑此公众号的权限....',
                    ]);
                    return back()->with(compact('error'));
                }
            }

            foreach ($imgArray as $k => $v) {
                if (\request()->input($v)) {
                    $imgName = substr($v, strlen(config('materialPR')));
                    $form->model()->$imgName = request()->input($v);
                }
            }
            unset($k, $v);
        });

        $form->saved(function (Form $form) {
            //判断是否是第一次
            if (request()->isMethod('put')) {
                Cache::forget(sprintf(config('cacheKey.wxuser'), $form->model()->token));
                Cache::forget(sprintf(config('cacheKey.wxuserConf'), $form->model()->token));
                Cache::forget(sprintf(config('cacheKey.wxuserPayConf'), $form->model()->token));
                Cache::forget('wechat.aggregatePayment.payConf:' . $form->model()->token);
            }
            if (request()->isMethod('post')) {
                $wxuser_id = $form->model()->id;
                $create = [];
                $gene = Relationusers::where('user_id', Admin::user()->id)->value('gene');
                $create[] = ['user_id' => Admin::user()->id, 'wxuser_id' => $wxuser_id];
                if ($gene != null) {
                    $geneArr = explode(',', $gene);
                    $geneArr = array_filter($geneArr);
                    foreach ($geneArr as $k => $v) {
                        if ($v != '-1') {
                            $create[] = ['user_id' => $v, 'wxuser_id' => $wxuser_id];
                        }
                    }
                    unset($k, $v);
                }
                RelationWxuser::insert($create);
            }
        });
        return $form;
    }
}
