<?php

namespace App\Jobs;

use App\Services\EsBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EsAddRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $type;

    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }


    public function handle()
    {
        switch ($this->type) {
            case 'menu':
                $yearMonth = date('Ym', strtotime($this->data['created_at']));
                $index = 'click_record_' . $yearMonth;
                EsBuilder::index($index)->create($this->data);
                return true;
                break;
            case 'event':
                $yearMonth = date('Ym', strtotime($this->data['created_at']));
                $index = 'wechat_event_' . $yearMonth;
                EsBuilder::index($index)->create($this->data);
                return true;
                break;
            default:
                return true;
        }
        return true;
    }
}
