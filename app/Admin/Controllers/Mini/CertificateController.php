<?php

namespace App\Admin\Controllers\Mini;

use App\Admin\Extensions\Tools\IconButton;
use App\Models\Mini\Registration;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('办证小程序授权')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Registration);

        $grid->actions(function ($actions) {
            $actions->disableDelete();

            $url = url('admin/miniProgram/certificate/type') . '?miniToken=' . $actions->row->token;
            $actions->append(new IconButton($url, '读者类型', 'fa-calendar-o'));
            $url = url('admin/miniProgram/certificate/img') . '?miniToken=' . $actions->row->token;
            $actions->append(new IconButton($url, '图片素材', 'fa-image'));
            $url = route('certificate.paySet', $actions->row->id);
            $actions->append(new IconButton($url, '支付配置', 'fa-jpy'));
        });

        $grid->id('编号');
        $grid->token('token');
        $grid->mininame('名称');

//        $grid->wx_id('Wx id');
//        $grid->openlib_appid('Openlib appid');
//        $grid->openlib_secret('Openlib secret');
//        $grid->openlib_url('Openlib url');
//        $grid->openlib_opuser('Openlib opuser');
//        $grid->glc('Glc');
//        $grid->app_id('App id');
//        $grid->secret('Secret');
//        $grid->libcode('Libcode');
//        $grid->qr_type('Qr type');
//        $grid->opacurl('Opacurl');
        $grid->start_at('起始时间');
        $grid->end_at('结束时间');
        $grid->status('状态')->switch();
//        $grid->created_at('创建时间');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Registration::findOrFail($id));

        $show->id('Id');
        $show->token('Token');
//        $show->wx_id('Wx id');
//        $show->openlib_appid('Openlib appid');
//        $show->openlib_secret('Openlib secret');
//        $show->openlib_url('Openlib url');
//        $show->openlib_opuser('Openlib opuser');
//        $show->glc('Glc');
//        $show->app_id('App id');
//        $show->secret('Secret');
//        $show->libcode('Libcode');
//        $show->qr_type('Qr type');
//        $show->opacurl('Opacurl');
//        $show->mininame('Mininame');
//        $show->status('Status');
//        $show->start_at('Start at');
//        $show->end_at('End at');
        $show->created_at('创建时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Registration);

        $form->display('token', 'Token');
//          $form->number('wx_id', 'Wx id');
        $form->text('mininame', '小程序名称');
        $form->text('app_id', '小程序-appid');
        $form->text('secret', '小程序-secret');
        $form->text('glc', '全局馆代码');
        $form->text('libcode', '分馆代码');
        $form->url('opacurl', 'Opac-url');

        $form->divider();
//          $form->text('openlib_appid', 'Openlib-appid');
        $form->hidden('openlib_appid')->default('weixin_xcx');
//          $form->text('openlib_secret', 'Openlib-secret');
        $form->hidden('openlib_secret')->default('b4e8d05f978sdkl63f9bcec36421229c168');
        $form->url('openlib_url', 'Openlib-url');
        $form->text('openlib_opuser', 'Openlib-操作员');
        $form->radio('is_cluster', '集群')->options([1 => '是', 0 => '否'])->default(0);
        $form->divider();

        $qrStates = [0 => '关闭', 1 => '1.0版', 2 => '2.0版'];
        $form->select('qr_type', '证二维码类型')->options($qrStates);
        $form->switch('status', '授权状态');
        $form->datetime('start_at', '起始时间')->default(date('Y-m-d H:i:s'));
        $form->datetime('end_at', '结束时间')->default(date('Y-m-d H:i:s'))->help('授权时间');

        $form->divider();
        $form->table('colors', '颜色编辑', function ($table) {
            $table->text('key');
            $table->color('color');
        });

        $form->text('template_bz', '办证模板消息ID');
        $form->divider();
        $form->text('public_token', '公众号token');
        $form->text('card_id', '会员卡ID');

        $form->saving(function (Form $form) {
            if (request()->isMethod('post')) {
                $form->model()->token = 'MINI' . Str::uuid()->getNodeHex();
            }
        });

        $form->saved(function (Form $form) {
            if (request()->isMethod('put')) {
                $token = $form->model()->token;
                Cache::forget('mini:register:' . $token . ':c');
                $cacheKey = sprintf(config('cacheKey.miniAccessToken'), $token);
                Cache::forget($cacheKey);
            }
        });
        return $form;
    }


    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function paySet($id, Content $content)
    {

        $miniToken = request()->session()->get('gridWhere.miniToken');
        if (empty($miniToken)) {
            $registration = Registration::find($id);
            $miniToken = $registration->token;
            request()->session()->put('gridWhere.miniToken', $miniToken);
        }

        if (request()->method() == 'PUT') {
            return $this->form2($miniToken)->update($id);
        }

        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form2($miniToken)->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form2($token)
    {
        $form = new Form(new Registration);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->setAction(url()->current());

        $form->text('payconfig.app_id', '小程序 AppId');
        $form->text('payconfig.mch_id', '商户号');
        $form->text('payconfig.key', 'API密钥');

        $form->file('payconfig.cert_path', 'cert')
            ->disk('admin')->move('miniPay/' . $token)->attribute('hideMaterial')->removable();
        $form->file('payconfig.key_path', 'key')
            ->disk('admin')->move('miniPay/' . $token)->attribute('hideMaterial')->removable();

        $form->saving(function (Form $form) {

        });

        $form->saved(function (Form $form) use ($token) {
            if (request()->isMethod('put')) {
                Cache::forget(sprintf(config('cacheKey.miniPayConf'), $token));
            }
        });
        return $form;
    }
}
