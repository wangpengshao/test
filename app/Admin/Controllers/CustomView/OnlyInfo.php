<?php

namespace App\Admin\Controllers\CustomView;

use Illuminate\Contracts\Support\Renderable;

class OnlyInfo extends DiyWidget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin.datashow.only-Info';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * InfoBox constructor.
     *
     * @param string $name
     * @param string $icon
     * @param string $color
     * @param string $link
     * @param string $info
     */
    public function __construct($name, $icon, $color, $link, $info)
    {
        $this->data = [
            'name' => $name,
            'icon' => $icon,
            'link' => $link,
            'info' => $info,
        ];

        $this->class("small-box bg-$color");
    }


    /**
     * @return mixed|string
     * @throws \Throwable
     */
    public function render()
    {
        $variables = array_merge($this->data, ['attributes' => $this->formatAttributes()]);

        return view($this->view, $variables)->render();
    }
}
