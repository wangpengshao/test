<?php

use App\Admin\Extensions\Column\ExpandRow;
use App\Admin\Extensions\Column\OpenMap;
use App\Admin\Extensions\Column\FloatBar;
use App\Admin\Extensions\Column\Qrcode;
use App\Admin\Extensions\Column\UrlWrapper;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Column;

// 覆盖`admin`命名空间下的视图
//app('view')->prependNamespace('admin', resource_path('views/admin/sourceFile/v1.7.5'));
app('view')->prependNamespace('admin', resource_path('views/admin/sourceFile/v1.7.7'));

// 表格初始化
Grid::init(function (Grid $grid) {
//    $grid->disableActions();
//    $grid->disablePagination();
//    $grid->disableCreateButton();
//    $grid->disableFilter();
//    $grid->disableRowSelector();
//    $grid->disableTools();
    $grid->disableExport();
//    $grid->actions(function (Grid\Displayers\Actions $actions) {
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->disableView();
//        $actions->disableEdit();
//        $actions->disableDelete();
    });
});
// 表单初始化
Form::init(function (Form $form) {

//    $form->disableEditingCheck();

    $form->disableCreatingCheck();

    $form->disableViewCheck();

    $form->tools(function (Form\Tools $tools) {
//        $tools->disableDelete();
//        $tools->disableView();
//        $tools->disableList();
    });
});

Form::forget(['map', 'editor']);

//Form::extend('editor', WangEditor::class);
Form::extend('editor', \App\Admin\Extensions\Form\CKEditor::class);
Form::extend('map', \App\Admin\Extensions\Column\Map::class);

Form::extend('templateData', \App\Admin\Controllers\CustomView\TemplateData::class);

Admin::js('/vendor/clipboard/dist/clipboard.min.js');

Column::extend('expand', ExpandRow::class);
Column::extend('openMap', OpenMap::class);
Column::extend('floatBar', FloatBar::class);
Column::extend('qrcode', Qrcode::class);
Column::extend('urlWrapper', UrlWrapper::class);
Column::extend('action', Grid\Displayers\Actions::class);

//Column::extend('prependIcon', function ($value, $icon) {
//
//    return "<span style='color: #999;'><i class='fa fa-$icon'></i> $value </span>";
//
//});

//Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {
//
//    $navbar->left(view('admin.search-bar'));
//
//    $navbar->right(new Links());
//    \App\Models\RelationWxuser::all();
//
//});

// 素材库 => js  &&  emoji => init => js
Admin::js('/wechatAdmin/js/materialDiy.js');
// Chart
Admin::js('/wechatAdmin/js/Chart.min.js');

// sweetalert2
//Admin::js('wechatAdmin/js/sweetalert2.js');
//Admin::css('wechatAdmin/css/sweetalert2.min.css');


