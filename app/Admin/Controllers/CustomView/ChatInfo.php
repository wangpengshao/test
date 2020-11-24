<?php

namespace App\Admin\Controllers\CustomView;

use Illuminate\Contracts\Support\Renderable;

class ChatInfo extends DiyWidget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin.datashow.message-info';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * InfoBox constructor.
     *
     * @param string $history
     * @param string $info
     */
    public function __construct($info, $history, $admin_info)
    {
        $this->data = [
            'info' => $info,
            'history' => $history,
            'admin_info' => $admin_info,
        ];
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
