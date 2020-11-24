<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\Replycontent;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ContentReplyController extends Controller
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
            if (request()->route()->getName() == 'textcontent.index') {
                $content->header('文本');
            } else {
                $content->header('图文');
            }
            $content->description('回复');
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
            if (Str::contains(request()->route()->uri, 'textcontent')) {
                $content->header('文本');
            } else {
                $content->header('图文');
            }
            $content->description('回复');
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
            if (request()->route()->getName() == 'textcontent.index') {
                $content->header('文本');
            } else {
                $content->header('图文');
            }
            $content->description('回复');
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
        $redis = Redis::connection('default');
        $token = request()->session()->get('wxtoken');

        $grid = new Grid(new Replycontent());
        $grid->model()->where('token', $token);
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableExport();
        $grid->disableFilter();

        $grid->title('标题');
        if (Str::contains(request()->route()->uri, 'textcontent')) {
            $grid->model()->where('type', 0);
            $grid->content('内容')->display(function ($text) {
                return strip_tags($text);
            })->limit(40);
        } else {
            $grid->model()->where('type', 1);
            $grid->image('图文封面')->image('', 100, 100);
            $grid->column('url', '图文链接')->display(function ($val) use ($token) {
                if (empty($val)) {
                    $url = config('vueRoute.showImgContent');
                    $url = str_replace('{token}', $token, $url);
                    return $url . $this->id;
                }
                return $val;
            })->urlWrapper();

        }
        $grid->keyword('关键字')->display(function ($keyword) {
            $str = preg_replace("/\s(?=\s)/", "\\1", $keyword);
            $str = trim($str);
            if (empty($str) && $str !== "0") {
                return [];
            }
            return explode(" ", $str);
        })->badge('danger')->sortable();

        $grid->column('matchtype', '匹配类型')->using([
            '1' => '<span class="label label-primary">完全匹配</span>',
            '0' => '<span class="label label-success">模糊匹配</span>'
        ]);

        $grid->order('排序')->sortable();
        $grid->column('已回复次数')->display(function () use ($redis) {
            $cacheKey = 'wechat:replycontent_' . $this->id;
            return $redis->get($cacheKey) ?: 0;
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Replycontent());
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck(false);
        });

        $form->display('id', 'ID');
        $matchtype = [
            'on' => ['value' => 1, 'text' => '完全', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '模糊', 'color' => 'success'],
        ];

        $form->switch('matchtype', '匹配类型')->states($matchtype);
        $form->text('keyword', '关键字')->rules('required');
        $form->hidden('token')->default(session('wxtoken'));
        $form->text('title', '标题');
        $form->number('order', '排序')->default(0);

        if (Str::contains(request()->route()->uri, 'textcontent')) {
            $form->textarea('content', '内容');
        } else {
            $form->text('description', '说明');

            /* 素材库 上传图片 例子 start */
            $form->image('image', '封面')->move(materialUrl())->uniqueName()->removable();

            $form->hidden(config('materialPR') . 'image');
            $imgArray = [config('materialPR') . 'image'];
            $form->ignore($imgArray);
            /* 素材库 上传图片 例子 end */

            $form->editor('content', '内容');
            $form->url('url', '链接')->help('如果填写了链接，将不会进行内容的展示，点击会直接跳转到目的链接！');

            $form->hidden('type')->default(1);

            $form->saving(function (Form $form) use ($imgArray) {

                foreach ($imgArray as $k => $v) {
                    if (\request()->input($v)) {
                        $imgName = substr($v, strlen(config('materialPR')));
                        $form->model()->$imgName = \request()->input($v);
                    }
                }
                unset($k, $v);
            });

        }
        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');
        return $form;
    }
}
