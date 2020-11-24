<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class CertificateLv1Exporter
 * @package App\Admin\Extensions\ExcelExporter
 */
class CertificateLv1Exporter extends ExcelExporter implements WithMapping
{
    /**
     * @var string
     */
    protected $statusSelect;

    public function __construct($statusSelect)
    {
        $this->statusSelect = $statusSelect;
    }


    protected $fileName = '办证列表.xlsx';

    /**
     * @var array
     */
    protected $columns = [
//        'id' => 'ID',
        'order_id' => '商户单号',
        'openid' => 'openid',
        'user.nickname' => '微信昵称',
        'rdid' => '办证号码',
        'rdtype' => '读者类型',
        'rdname' => '姓名',
        'rdcertify' => '身份证',
        'orders.pay_status' => '押金方式',
        'orders.price' => '押金(元)',
        'created_at' => '提交申请时间',
        'updated_at' => '实际处理时间',
        'status' => '办证状态',
        //.....关联字段  不需显示出来
        'is_pay' => '',
    ];

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $pay_status = data_get($row, 'orders.pay_status');
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
        if ($row->is_pay != 1) {
            $pay_status_str = '免付';
        }
        return [
            $row->order_id,
            $row->openid,
            data_get($row, 'user.nickname'),
            "\t" . (string)$row->rdid,
            "\t" . (string)$row->rdtype,
            "\t" . (string)$row->rdname,
            "\t" . (string)$row->rdcertify,
            $pay_status_str,
            data_get($row, 'orders.price'),
            $row->created_at,
            $row->updated_at,
            $this->statusSelect[$row->status],
        ];
    }

}
