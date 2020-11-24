<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

/**
 * Class OssUpPeopleImage
 *
 * @package App\Jobs
 */
class OssUpPeopleImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * @var string
     */
    protected $name;

    /**
     * @var
     */
    protected $base64File;


    /**
     * OssUpPeopleImage constructor.
     *
     * @param      $token
     * @param      $base64File
     * @param null $fileName
     */
    public function __construct($token, $base64File, $fileName = null)
    {
        $this->base64File = $base64File;
        if ($fileName) {
            $this->name = $fileName;
        } else {
            $this->name = 'peopleImage/' . $token . '/' . date('Ymd') . '/' . uniqid() . '.jpeg';
        }
    }


    /**
     *
     */
    public function handle()
    {
        $storage = Storage::disk('oss');
        $storage->put($this->name, base64_decode($this->base64File));
        unset($storage, $this->base64File);
    }
}
