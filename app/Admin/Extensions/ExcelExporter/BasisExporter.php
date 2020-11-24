<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;

/**
 * Class DomeExporter
 * @package App\Admin\Extensions\ExcelExporter
 */
class BasisExporter extends ExcelExporter
{
    /**
     * @var string
     */
    protected $fileName;
    /**
     * @var array
     */
    protected $columns;

    /**
     * DomeExporter constructor.
     * @param string $fileName
     * @param array $columns
     */
    public function __construct(string $fileName, array $columns)
    {
        $this->fileName = $fileName . '.xlsx';
        $this->columns = $columns;
    }

}
