<?php

namespace App\Admin\Extensions\ExcelExporter;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ImportDome implements FromArray, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }
//     $event->sheet->getDelegate()->getDefaultRowDimension()->setRowHeight(20);//所有单元格（行）默认高度
//     $event->sheet->getDelegate()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
//     $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(30);//设置行高度
//     $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(30);//设置列宽度
//     $event->sheet->getDelegate()->getStyle('A1')->getFont()->setSize(18);//设置文字大小
//     $event->sheet->getDelegate()->getStyle('A1')->getFont()->setBold(true);//设置是否加粗
//     $event->sheet->getDelegate()->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);// 设置文字颜色
//     $event->sheet->getDelegate()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
//     $event->sheet->getDelegate()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
//     $event->sheet->getDelegate()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);//设置填充颜色
//     $event->sheet->getDelegate()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF7F24');//设置填充颜色
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 所有表头-设置字体为14
                $cellRange = 'A1:W1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal('HORIZONTAL_CENTER');
                $event->sheet->getDelegate()->getDefaultColumnDimension()->setWidth('20');
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(24);
            },
        ];
    }
}
