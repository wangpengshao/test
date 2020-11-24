<?php

namespace App\Admin\Controllers\CustomView;

use Illuminate\Contracts\Support\Renderable;

class GridHeadA extends DiyWidget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin.datashow.head-a';

    /**
     * @var array
     */
    protected $data = [];


    public function __construct($list)
    {
        $this->data = $list;
    }


    /**
     * @return mixed|string
     * @throws \Throwable
     */
    public function render()
    {
        $variables = array_merge(['list' => $this->data], ['attributes' => $this->formatAttributes()]);
        return view($this->view, $variables)->render();
    }
}
