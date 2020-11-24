<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;

class IconButton extends AbstractTool
{
    protected $title;
    protected $url;
    protected $icon;

    public function __construct($url = 'javascript:void(0)', $title = '', $icon = '')
    {
        $this->url = $url;
        $this->title = $title;
        $this->icon = $icon;
    }

    public function render()
    {
        return "<a class='btn btn-xs btn-default' data-toggle='tooltip' title='{$this->title}'  href='{$this->url}'>" .
            "<i class='fa {$this->icon}'></i></a>";

    }
}
