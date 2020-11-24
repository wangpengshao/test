<?php

namespace App\Admin\Controllers\Wechat\MenuBar;

use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\MenuClassify;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MenuClassifyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '资源类目';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $token = request()->session()->get('wxtoken');
        $grid = new Grid(new MenuClassify);
        $grid->model()->where('token', $token);
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('title', __('Title'));
        });

        $grid->header(function ($query) use ($token) {
            $url = config('vueRoute.menuClassify');
            $url = str_replace('{token}', $token, $url);
            $url .= '?id=';
            return "<div class='callout'><h4>全资源展示链接:</h4><p>{$url}</p><small>如需单独展示某个分类资源,请在链接上加入分类对应的 id 即可</small></div>";
        });

        $grid->column('id', __('Id'));
        $grid->column('order', __('Order'))->sortable();
        $grid->column('title', __('Title'));
        $grid->column('desc', __('Desc'));
        $grid->column('logo', __('Logo'))->image('', 40, 40);
        $grid->column('created_at', __('Created at'));
        $grid->column('is_show', __('Is show'))->switch();
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
        $show = new Show(MenuClassify::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order', __('Order'));
        $show->field('title', __('Title'));
        $show->field('desc', __('Desc'));
        $show->field('is_show', __('Is show'));
//        $show->field('token', __('Token'));
        $show->field('logo', __('Logo'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $token = request()->session()->get('wxtoken');
        $form = new Form(new MenuClassify);
        $form->switch('is_show', __('Is show'));
        $form->text('title', __('Title'))->required();
        $form->textarea('desc', __('Desc'));

        $form->image('logo', __('Logo'))->move(materialUrl())->uniqueName()->removable();
        $form->number('order', __('Order'))->default(0);
        $form->hidden('token', __('Token'))->default($token);
        $options = IndexMenu::where('token', $token)->orderBy('status')->pluck('caption', 'id');
        $form->listbox('menus', __('MenuClassMenus'))->options($options);
        /* 素材库 上传图片 例子 start */

        $form->hidden(config('materialPR') . 'logo');
        $imgArray = [config('materialPR') . 'logo'];
        $form->ignore($imgArray);
        /* 素材库 上传图片 例子 end */
        $form->saving(function (Form $form) use ($imgArray) {

            foreach ($imgArray as $k => $v) {
                if (\request()->input($v)) {
                    $imgName = substr($v, strlen(config('materialPR')));
                    $form->model()->$imgName = \request()->input($v);
                }
            }
            unset($k, $v);
        });

        $form->deleting(function () {
            $id = current(\request()->route()->parameters);
            if ($id !== false) {
                $id = explode(',', $id);
                foreach ($id as $k => $v) {
                    MenuClassify::find($v)->menus()->detach();
                }
            }
            //删除之前先删除中间关系表 的 关联关系
        });

        return $form;
    }

}
