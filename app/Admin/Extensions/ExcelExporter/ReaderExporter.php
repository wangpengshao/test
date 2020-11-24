<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;
//use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithMapping;

//use PhpOffice\PhpSpreadsheet\Cell\Cell;
//use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Class DomeExporter
 * @package App\Admin\Extensions\ExcelExporter
 */
class ReaderExporter extends ExcelExporter implements WithMapping
{
    /**
     * @var string
     */
    protected $fileName = '绑定读者.xlsx';
    /**
     * @var array
     */
    protected $columns = [
        'id' => 'ID',
        'openid' => 'openid',
        'rdid' => '读者证',
        'name' => '姓名',
        'updated_at' => '绑定时间',
        'hasOneWechatinfo.nickname' => '微信昵称',
    ];

    public function map($row): array
    {
        return [
            $row->id,
            $row->openid,
            $row->rdid,
            $row->name,
            $row->updated_at,
            data_get($row, 'hasOneWechatinfo.nickname'),
        ];
    }

}
