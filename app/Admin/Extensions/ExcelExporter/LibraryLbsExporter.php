<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;

class LibraryLbsExporter extends ExcelExporter
{
    /**
     * @var string
     */
    protected $fileName = '图书馆LBS定位列表.xlsx';

    /**
     * @var array
     */
    protected $columns = [
        'id' => '编号',
        'name' => '单位名称',
        'telephone' => '联系方式',
        'phone' => '手机号码',
        'lat' => 'lat',
        'lng' => 'lng',
        'address' => '详细地址',
        'intro' => '简介'
    ];
}
