<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class IsWinning extends AbstractTool
{
    public function script()
    {
        $url = Request::fullUrlWithQuery(['is_winning' => '_is_winning_']);

        return <<<EOT

$('input:radio.user-gender').change(function () {

    var url = "$url".replace('_is_winning_', $(this).val());
    
    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        $options = [
            'all' => '全部',
            '0' => '未中奖',
            '1' => '已中奖',
        ];

        return view('admin.tools.isWinning', compact('options'));
    }
}
