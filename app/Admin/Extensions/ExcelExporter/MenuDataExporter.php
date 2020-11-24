<?php

namespace App\Admin\Extensions\ExcelExporter;

use App\Services\EsBuilder;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Class LuckyDrawListExporter
 * @package App\Admin\Extensions\ExcelExporter
 */
class MenuDataExporter extends ExcelExporter implements WithMapping, WithEvents
{
    /**
     * @var string
     */
    protected $fileName = '菜单数据导出表.xlsx';
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @var array
     */
    protected $columns = [
        'caption' => '菜单名称',
        'id' => '访问次数',
        'created_at' => '访问的初始时间',
        'updated_at' => '访问的结束时间'
    ];

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $query = EsBuilder::index(config('search.aliases.click'))->where('mid', $row->id);
        $start = $this->start ?: '';
        $end = $this->end ?: '';
        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }
        $count = $query->count();
        $count = !$count ? '0' : $count;
        return [
            $row->caption,
            "\t" . $count,
            "\t" . $start,
            "\t" . $end,
        ];
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
                $cellRange = 'A1:D1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(30);
            }
        ];
    }
}
