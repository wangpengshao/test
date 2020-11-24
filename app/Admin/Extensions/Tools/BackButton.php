<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;

class BackButton extends AbstractTool
{
    protected $title;
    protected $url;

    public function __construct($url = 'javascript:void(0)', $title = '返回')
    {
        $this->url = $url;
        $this->title = $title;
    }

    public function render()
    {
        return "<div class='btn-group pull-right' style='margin-right: 10px'>".
            "<a href='{$this->url}' class='btn btn-sm btn-default' title='{$this->title}'>".
            "<span class='hidden-xs'>&nbsp;{$this->title}</span></a></div>";
    }
}
