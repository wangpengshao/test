<?php

namespace App\Admin\Extensions\ExcelExporter;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * https://laravel-excel.com/
 * https://phpspreadsheet.readthedocs.io/en/latest/
 * Class LbsImportDome
 * @package App\Admin\Extensions\ExcelExporter
 */
class LbsImportDome implements FromArray, WithEvents
{
    /**
     * @var array
     */
    protected $data;

    /**
     * LbsImportDome constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 设置全局默认样式
                $event->sheet->getDelegate()->getDefaultColumnDimension()->setWidth(20);
                // 所有表头 - 设置字体为 14
                $cellRange = 'A1:G1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(30);
                // 合并单元格用作导入说明 设置范围内文本自动换行 顶部对齐
//                $noteCellRange = 'I1:I9';
                $noteCellRange = 'I1';
//                $event->sheet->getDelegate()->mergeCells($noteCellRange);
                $event->sheet->getDelegate()->getStyle($noteCellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(40);
                $event->sheet->getDelegate()->getStyle($noteCellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($noteCellRange)->getFont()->setColor(new Color(Color::COLOR_RED));
                $event->sheet->getDelegate()->getStyle($noteCellRange)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);
                $event->sheet->getDelegate()->getStyle($noteCellRange)->getAlignment()->setWrapText(true);
            }
        ];
    }
}
