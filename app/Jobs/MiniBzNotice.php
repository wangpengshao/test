<?php

namespace App\Jobs;

use App\Models\Mini\CertificateLog;
use App\Models\Mini\CertificateOrders;
use App\Models\Mini\MiniProgram;
use App\Models\Mini\Registration;
use App\Services\PayLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MiniBzNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $certificateLog;

    protected $templateId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CertificateLog $certificateLog)
    {
        $this->certificateLog = $certificateLog;
    }

    /**
     * Execute the job.
     *
     * @param PayLogService $payLogService
     * @return void
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(PayLogService $payLogService)
    {
        $registration = Registration::getCache($this->certificateLog->mini_token);

        $order = CertificateOrders::where(['mini_token' => $this->certificateLog->mini_token, 'order_id' => $this->certificateLog->order_id])->first();

        $miniApp = MiniProgram::initialize($registration['app_id'], $registration['secret']);

        $data = [
            'touser' => $this->certificateLog->openid,
            'template_id' => $registration['template_bz'],
            'page' => 'pages/index/pageindex',
            'form_id' => $this->certificateLog->prepay_id,
            'data' => [
                'keyword1' => $this->certificateLog->rdname,
                'keyword2' => hidenIdCard($this->certificateLog->rdcertify),
                'keyword3' => $this->certificateLog->rdid,
                'keyword4' => $order['price'] . '元',
            ],
        ];

        $res = $miniApp->template_message->send($data);
    }
}
