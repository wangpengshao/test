<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class LuckyDrawListExporter
 * @package App\Admin\Extensions\ExcelExporter
 */
class LuckyDrawListExporter extends ExcelExporter implements WithMapping
{
    /**
     * @var string
     */
    protected $fileName = '中奖列表.xlsx';

    /**
     * @var array
     */
    protected $columns = [
        'id' => 'ID',
        'openid' => 'openid',
        'nickname' => '微信昵称',
        'rdid' => '读者证',
        'hasOneGather.phone' => '手机',
        'hasOneGather.name' => '姓名',
        'hasOneGather.idcard' => '身份证',
        'text' => '奖品',
        'code' => '兑奖码',
        'address.name' => '收件人',
        'address.phone' => '电话号码',
        'address.address' => '地址',
        'created_at' => '抽奖时间',
        'status' => '发奖状态',
        'gather_id' => '',  //需要取出字段用于关联
    ];

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->openid,
            $row->nickname,
            "\t" . (string)$row->rdid,
            "\t" . data_get($row, 'hasOneGather.phone'),
            data_get($row, 'hasOneGather.name'),
            "\t" . data_get($row, 'hasOneGather.idcard'),
            $row->text,
            $row->code,
            data_get($row, 'address.name'),
            data_get($row, 'address.phone'),
            data_get($row, 'address.address'),
            $row->created_at,
            $row->status == 1 ? '已发,' . $row->updated_at : '',
        ];
    }

}
