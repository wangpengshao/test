<?php

namespace App\Admin\Extensions\ExcelExporter;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class VoteTopExporter extends DefaultValueBinder implements FromArray, WithHeadings, WithCustomValueBinder
{
    private $array;
    private $headings;

    //数据注入
    public function __construct($array, $headings)
    {
        $this->array = $array;
        $this->headings = $headings;
    }

    public function array(): array
    {
        return $this->array;
    }

    //实现WithHeadings接口
    public function headings(): array
    {
        return $this->headings;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

}
