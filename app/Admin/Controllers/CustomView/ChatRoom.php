<?php

namespace App\Admin\Controllers\CustomView;

use Illuminate\Contracts\Support\Renderable;

class ChatRoom extends DiyWidget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin.datashow.chat-room';

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
    public function __construct($info, $history, $notReadList)
    {
        $this->data = [
            'info' => $info,
            'history' => $history,
            'notReadList' => $notReadList,
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
