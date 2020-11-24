<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;

class HeadTitle extends AbstractTool
{
    protected $title;

    public function __construct($title = '标题')
    {
        $this->title = $title;
    }

    public function render()
    {
        return '<div class="callout"><p class="text-muted">' . $this->title . '</p></div>';
//        return '<ol class="breadcrumb"> ' . $this->title . '</li></ol>';
    }
}
