<?php

namespace App\Admin\Controllers\CustomView;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class TagList extends AbstractTool
{
    protected $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }


    public function script()
    {
        $url = Request::fullUrlWithQuery(['kind' => '_kind_']);

        return <<<EOT

$('input:radio.message-kind').change(function () {

    var url = "$url".replace('_kind_', $(this).val());

    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        $options = $this->options;

        return view('admin.tools.message-kind', compact('options'));
    }
}
