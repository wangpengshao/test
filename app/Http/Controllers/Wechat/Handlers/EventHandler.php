<?php


namespace App\Http\Controllers\Wechat\Handlers;

use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

class EventHandler implements EventHandlerInterface
{
    
    public function handle($data = null)
    {
        $data['Event']=str_replace('_','',$data['Event']);
        $handler = 'App\\Http\\Controllers\\Wechat\\Handlers\\Events\\' . ucfirst(strtolower($data['Event'])) . 'Handler';
        return call_user_func_array([$handler, 'handle'], [$data]);
    }

}
