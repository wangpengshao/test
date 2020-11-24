<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\IndexMenu;

use App\Models\Wechat\RelevanceMenu;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class IndexMenuController extends Controller
{
    use HasResourceActions;

    protected $enType;

    protected $extraOptions;

    protected $fansOptions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('菜单管理');
            $content->description('description');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('菜单管理');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('菜单管理');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new IndexMenu());
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('caption', '名称');
        });
        $grid->disableExport();
        // 设置初始排序条件
        $grid->model()->where('token', '=', session('wxtoken'));
        $grid->model()->orderBy('status', 'desc');
        $grid->caption('名称');
        $grid->icon()->image('', 70, 70);
        $grid->column('url', '链接')->limit(30);
        $grid->column('onlyUrl', '单点登录链接')->display(function () {
            $url = config('vueRoute.bindReader');
            $url = str_replace('{token}', session('wxtoken'), $url);
            return $url . '?id=' . $this->id;
        })->urlWrapper();

        $grid->order('排序')->sortable();

        $states = [
            'on' => ['value' => 1, 'text' => '显示', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
        ];
        $grid->status('状态')->switch($states);
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $this->enType = config('MenuOp.enType');
        $this->extraOptions = config('MenuOp.extraOptions');
        $this->fansOptions = config('MenuOp.fansOptions');

        $form = new Form(new IndexMenu());

        $imgArray = [config('materialPR') . 'icon'];
        $states = [
            'on' => ['value' => 1, 'text' => '显示', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
        ];
        $form->tab('基本设置', function ($form) use ($imgArray, $states) {

            $form->text('caption', '名称')->rules('required');
            $form->switch('status', '状态')->states($states);
            $form->url('url', '链接')->help('openid、rdid、libcode....所需参数会拼接在链接上');

            $form->number('order', '排序')->default(0);
            // 添加text类型的input框
            $form->text('flag', '标志');
            $form->color('flagColor', '标志颜色');

            /* 素材库 上传图片 例子 start */
            $form->image('icon')->move(materialUrl())->uniqueName();
            $form->hidden(config('materialPR') . 'icon');
            $form->ignore($imgArray);
            /* 素材库 上传图片 例子 end */
            $form->hidden('token')->default(session('wxtoken'));

        });
        if (Admin::user()->isRole('管理')) {
            $form->tab('高级参数设置', function ($form) {
                $form->select('r_id', '一键菜单')->options(RelevanceMenu::where('status', 1)->pluck('caption', 'id'));

                $sw = [
                    'on' => ['value' => 1, 'text' => '是', 'color' => 'primary'],
                    'off' => ['value' => 0, 'text' => '否', 'color' => 'default']
                ];
                $form->switch('need_bind', '绑定读者')->states($sw);
//                $form->switch('add_info', '粉丝信息')->states($sw)->help('openid,nickname(昵称),sex(性别),headimgurl(头像),subscribe(关注)');
                $form->checkbox('add_info', '粉丝信息')->options($this->fansOptions)->stacked();

                $form->switch('add_rdid', '读者证')->states($sw);
                $form->text('rdid_str', '参数名')->help('默认参数名: rdid');
                $form->switch('add_glc', '全局馆代码')->states($sw);
                $form->text('glc_str', '参数名')->help('默认参数名: glc');
                $form->switch('add_libcode', '分馆代码')->states($sw);
                $form->text('libcode_str', '参数名')->help('默认参数名: libcode');

                $form->text('signKey', 'signKey')->help('md5验证数据的可靠性，可以不填！');
                $form->radio('en_type', '加密类型')->options($this->enType)->default('0');

            });
            $form->tab('特殊参数设置', function ($form) {
                $form->embeds('extra', '特殊字段配置', function ($form) {

                    $form->text('text1', '字段名');
                    $form->select('source1', '数据源')->options($this->extraOptions);
                    $form->text('data1', '数据')->help('非本系统数据需要填写这项，否则不会携带参数');
                    $form->radio('enType1', '加密类型')->options($this->enType)->default('0');
                    $form->text('enKey1', '加密Key')->help('留空则使用默认值');
                    $form->divider();

                    $form->text('text2', '字段名');
                    $form->select('source2', '数据源')->options($this->extraOptions);
                    $form->text('data2', '数据')->help('非本系统数据需要填写这项，否则不会携带参数');
                    $form->radio('enType2', '加密类型')->options($this->enType)->default('0');
                    $form->text('enKey2', '加密Key')->help('留空则使用默认值');
                    $form->divider();

                    $form->text('text3', '字段名');
                    $form->select('source3', '数据源')->options($this->extraOptions);
                    $form->text('data3', '数据')->help('非本系统数据需要填写这项，否则不会携带参数');
                    $form->radio('enType3', '加密类型')->options($this->enType)->default('0');
                    $form->text('enKey3', '加密Key')->help('留空则使用默认值');

                });
            });
        }

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });

        $form->footer(function ($footer) {
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck(false);
        });

        $form->saving(function (Form $form) use ($imgArray) {
            foreach ($imgArray as $k => $v) {
                if (\request()->input($v)) {
                    $imgName = substr($v, strlen(config('materialPR')));
                    $form->model()->$imgName = \request()->input($v);
                }
            }
            unset($k, $v);
        });

        $form->saved(function (Form $form) {
            $cache = sprintf(config('cacheKey.vueMenu'), $form->model()->token, $form->model()->id);
            Cache::forget($cache);
            $cache = sprintf(config('cacheKey.vueShowMenu'), $form->model()->token);
            Cache::forget($cache);
        });
        return $form;
    }

    public function show($id, Content $content)
    {
        return $content->header('菜单')
            ->description('详情')
            ->body(Admin::show(IndexMenu::findOrFail($id), function (Show $show) {
                $show->id('ID');
                $show->order('顺序');
                $show->icon('封面');
                $show->created_at();
                $show->updated_at();
            }));
    }

}
