<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\Imagewechat;

use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class ImagewechatController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('轮播列表');
            $content->description('服务大厅');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
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

            $content->header('header');
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
        $grid = new Grid(new Imagewechat());
        $grid->disableFilter();
        $grid->disableExport();
        // 设置初始排序条件
        $grid->model()->where('token', '=', session('wxtoken'));
        $grid->model()->orderBy('status', 'desc');
        $grid->caption('描述');
        $grid->image('图片')->image('', '100', '100');
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
        $form = new Form(new Imagewechat());
        $form->display('id', 'ID');
        // 添加text类型的input框
        $form->text('caption', '描述');
        $form->number('order', '排序')->default(0)->rules('required');

        /* 素材库 上传图片 例子 start */
        $form->image('image')->move(materialUrl())->uniqueName();

        $form->hidden(config('materialPR') . 'image');
        $imgArray = [config('materialPR') . 'image'];
        $form->ignore($imgArray);
        /* 素材库 上传图片 例子 end */

        $form->url('url');

        $states = [
            'on' => ['value' => 1, 'text' => '显示', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
        ];
        $form->switch('status', '状态')->states($states);
        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');

        $form->hidden('token')->default(session('wxtoken'));
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
            if (request()->isMethod('PUT')) {
                Cache::forget('vueIndex:img:' . $form->model()->token);
            }
        });
        return $form;
    }

    public function show($id, Content $content)
    {
        return $content->header('轮播')
            ->description('详情')
            ->body(Admin::show(Imagewechat::findOrFail($id), function (Show $show) {
                $show->id();
                $show->order('顺序');
                $show->image('封面');
                $show->text('caption', '标题');
                $show->created_at();
                $show->updated_at();
            }));


    }
}
