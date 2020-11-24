<?php

namespace App\Admin\Controllers\Wechat\CollectCard;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\HtmlConfig;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class HtmlConfController extends Controller
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
            $content->header('页面文案配置');
            $content->description('编辑');
            $doesntExist = CollectCard::whereToken(session('wxtoken'))->where('id', request()->input('a_id'))->doesntExist();
            if ($doesntExist) {
                return $content->withWarning('提示', '抱歉，非法访问');
            }
            $content->row(function (Row $row) {
                $row->column(8, function (Column $column) {

                    $data = HtmlConfig::whereToken(session('wxtoken'))->where('a_id', request()->input('a_id'))->first();
                    $form = new \Encore\Admin\Widgets\Form($data);
                    $url = ($data) ?
                        route('collectCard.html.up', ['a_id' => request()->input('a_id'), 'id' => $data->id]) :
                        route('collectCard.html.add', ['a_id' => request()->input('a_id')]);

                    $form->action($url);
                    $form->html(new BackButton(url('admin/wechat/collectCard/index'),'返回活动'));
                    $form->switch('gl1_sw', '攻略一(开关)');
                    $form->text('gl1_title', '攻略一(标题)');
                    $form->textarea('gl1_info', '攻略一(描述)');
                    $form->switch('gl2_sw', '攻略二(开关)');
                    $form->text('gl2_title', '攻略二(标题)');
                    $form->textarea('gl2_info', '攻略二(描述)');
                    $form->switch('gl3_sw', '攻略三(开关)');
                    $form->text('gl3_title', '攻略三(标题)');
                    $form->textarea('gl3_info', '攻略三(描述)');
                    $form->switch('ty1_sw', '体验一(开关)');
                    $form->text('ty1_title', '体验一(标题)');
                    $form->textarea('ty1_info', '体验一(描述)');
                    $form->switch('ty2_sw', '体验二(开关)');
                    $form->text('ty2_title', '体验二(标题)');
                    $form->textarea('ty2_info', '体验二(标题)');
                    $form->textarea('first_info', '首次参与(描述)');
                    $form->textarea('fx1_info', '分享一(描述)');
                    $form->textarea('fx2_info', '分享二(描述)');

                    $form->hidden('a_id')->default(request()->input('a_id'));
                    $form->hidden('token')->default(session('wxtoken'));
                    $column->append(new Box(" ", $form));
                });
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Admin::form(HtmlConfig::class, function (Form $form) {
            $form->switch('gl1_sw', '攻略一(开关)');
            $form->text('gl1_title', '攻略一(标题)');
            $form->textarea('gl1_info', '攻略一(描述)');
            $form->switch('gl2_sw', '攻略二(开关)');
            $form->text('gl2_title', '攻略二(标题)');
            $form->textarea('gl2_info', '攻略二(描述)');
            $form->switch('gl3_sw', '攻略三(开关)');
            $form->text('gl3_title', '攻略三(标题)');
            $form->textarea('gl3_info', '攻略三(描述)');
            $form->switch('ty1_sw', '体验一(开关)');
            $form->text('ty1_title', '体验一(标题)');
            $form->textarea('ty1_info', '体验一(描述)');
            $form->switch('ty2_sw', '体验二(开关)');
            $form->text('ty2_title', '体验二(标题)');
            $form->textarea('ty2_info', '体验二(标题)');
            $form->textarea('first_info', '首次参与(描述)');
            $form->textarea('fx1_info', '分享一(描述)');
            $form->textarea('fx2_info', '分享二(描述)');

            $form->hidden('a_id');
            $form->hidden('token');
            $form->saved(function (Form $form) {
                $cacheKey = 'collectCard:html:' . $form->model()->token . ':' . $form->model()->a_id;
                Cache::forget($cacheKey);
                return redirect()->route('collectCard.html', ['a_id' => request()->input('a_id')]);
            });
        });
    }


}
