<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\QrCodeSeo;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Wechatapp;
use App\Models\WechatApi\GroupList;
use App\Models\Wxuser;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class QrCodeSeoController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $type = Wxuser::whereToken(session('wxtoken'))->value('type');
        if ($type != 1) {
            return $content->withWarning('提示', '抱歉，此功能需要公众号类型为服务号才能使用..');
        }
        return $content
            ->header('统计二维码列表')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
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
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form($id)->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
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
        $GroupList = new GroupList();
        $tagList = $GroupList->getList();

        $grid = new Grid(new QrCodeSeo);
        $grid->disableExport();
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();
            $filter->like('title', '标题');
            $filter->equal('type', '二维码类型')->select(['0' => '临时', '1' => '永久']);

        });

        $grid->model()->where('token', session('wxtoken'));
        $grid->title('标题');
        $grid->invites('关注数')->sortable();
        $grid->views('扫码次数')->sortable();
        $grid->keyword('回复关键字');
        $grid->url('二维码链接')->urlWrapper();
        $grid->group_id('自动分组')->display(function ($id) use ($tagList) {
            return (empty($id)) ? '' : array_get($tagList, $id);
        })->label();

        $grid->type('二维码类型')->using([
            '0' => "<span class='label label-danger'>临时</span>",
            '1' => "<span class='label label-success'>永久</span>",
        ]);

        $grid->expire_at('过期时间')->sortable();
        $grid->status('状态')->switch();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(QrCodeSeo::findOrFail($id));

//        $show->id('Id');
//        $show->token('Token');
//        $show->invites('Invites');
//        $show->views('Views');
//        $show->keyword('Keyword');
        $show->url('二维码链接');
        $show->ticket('Ticket');
//        $show->status('Status');
//        $show->group_id('Group id');
//        $show->type('Type');
//        $show->expire_at('Expire at');
        $show->updated_at('最后编辑');
        $show->created_at('创建时间');

        return $show;
    }


    protected function form($id = null)
    {

        $GroupList = new GroupList();
        $tagList = $GroupList->getList();

        $form = new Form(new QrCodeSeo);
        $form->text('title', '标题')->rules('required');

        $form->text('keyword', '关键字')->rules('required')->help('扫码自动匹配关键字关联的内容回复');
        $form->switch('status', '状态')->default(1);
        $form->select('group_id', '自动分组')->options($tagList);
        if (empty($id)) {
            $form->radio('type', '二维码类型')->options(['0' => '临时', '1' => '永久']);
            $form->slider('k_days', '临时天数')->options(['max' => 30, 'min' => 1, 'step' => 1, 'postfix' => '天'])
                ->help('临时天数只对临时类型二维码有效,永久类型二维码此项无需选择!');
        } else {
            $form->display('type', '二维码类型')->with(function ($val) {
                return ($val == 1) ? '永久类型' : '临时 ( 有效期至 ' . $this->expire_at . ' )';
            });
        }

        $form->hidden('token')->default(session('wxtoken'));

        $form->saved(function (Form $form) {

            if (\request()->isMethod('post')) {
                $app = Wechatapp::initialize(session('wxtoken'));
                $model = $form->model();
                $qrData = [
                    's_id' => $model['id'],
                    'type' => 'seo'
                ];
                $s = Carbon::now()->addDays($model['k_days'])->diffInSeconds();
                $expire_at = null;
                if ($model['type'] == 1) {
                    $response = $app->qrcode->forever(json_encode($qrData));
                } else {
                    $expire_at = Carbon::now()->addSecond($s)->toDateTimeString();
                    $model->expire_at = $expire_at;

                    $response = $app->qrcode->temporary(json_encode($qrData), $s);

                }
                $model->ticket = $response['ticket'];
                $model->url = $response['url'];
                $model->save();

            }
        });

        return $form;
    }
}
