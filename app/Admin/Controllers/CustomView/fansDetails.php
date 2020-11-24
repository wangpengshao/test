<?php

namespace App\Admin\Controllers\CustomView;

use Illuminate\Contracts\Support\Renderable;

class fansDetails extends DiyWidget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin.datashow.fans-details';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * InfoBox constructor.
     *
//     * @param string $history
     * @param string $info
     */
    public function __construct($info)
    {
        $this->data = [
            'info' => $info,
//            'history' => $history,
        ];

//        $this->class("small-box bg-$color");
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
