<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class DomeExporter
 * @package App\Admin\Extensions\ExcelExporter
 */
class PayArrearsExporter extends ExcelExporter implements WithMapping
{
    /**
     * @var string
     */
    protected $fileName = '支付欠款数据列表.xlsx';
    /**
     * @var array
     */
    protected $columns = [
        'rdid' => '付款证号',
        'order_id' => '商户订单号',
        'transaction_id' => '微信订单号',
        'user.nickname' => '微信昵称',
        'price' => '支付金额(元)',
        'refunds' => '退款(元)',
        'created_at' => '支付时间',
        'pay_status' => '支付状态',
        'hasManyLog.tranid' => '流水号',
        'hasManyLog.feetype' => '缴费类型',
        'hasManyLog.price' => '金额(元)',
        'hasManyLog.status' => '销账状态',
        'hasManyLog.updated_at' => '实际处理时间',
        // .....关联字段  不需显示出来
        'openid' => ''
    ];

    public function map($row): array
    {
        // 订单内容
        $order_details = data_get($row, 'hasManyLog');
        $tranid = '';
        $feetype = '';
        $price = '';
        $order_status = '';
        $updated_at = '';

        foreach ($order_details as $order_detail) {
            $tranid = $tranid . $order_detail['tranid'] . "\r\n";
            $feetype = $feetype . $order_detail['feetype'] . "\r\n";
            $price = $price . $order_detail['price'] . "\r\n";
            switch ($order_detail['status']) {
                case 1:
                    $order_status = $order_status . '成功' . "\r\n";
                    break;
                default:
                    $order_status = $order_status . '失败' . "\r\n";
            }
            $updated_at = $updated_at . $order_detail['updated_at'] . "\r\n";
        }
        // 支付状态
        $pay_status = data_get($row, 'pay_status');
        switch ($pay_status) {
            case -1:
                $pay_status_str = '支付失败';
                break;
            case 1:
                $pay_status_str = '已支付';
                break;
            case 2:
                $pay_status_str = '退款处理';
                break;
            default:
                $pay_status_str = '未支付';
        }
        // 退款金额
        $refunds = data_get($row, 'refunds');
        $refund_fee = 0;
        $wait = 0;
        foreach ($refunds as $k => $v) {
            if ($v['status'] == 1) {
                $refund_fee += $v['refund_fee'];
            } else {
                $wait += $v['refund_fee'];
            }
        }
        unset($k, $v);
        $str = '';
        if ($refund_fee > 0) {
            $str .= '已退¥ ' . $refund_fee;
        }
        if ($wait > 0) {
            $str .= '待退¥ ' . $wait;
        }

        return [
            $row->rdid . "\r\n",
            $row->order_id . "\r\n",
            $row->transaction_id . "\r\n",
            data_get($row, 'user.nickname') . "\r\n",
            $row->price . "\r\n",
            $str . "\r\n",
            $row->created_at . "\r\n",
            $pay_status_str . "\r\n",
            $tranid,
            $feetype,
            $price,
            $order_status,
            $updated_at
        ];
    }

}
