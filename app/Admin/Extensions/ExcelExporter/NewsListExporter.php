<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class DomeExporter
 * @package App\Admin\Extensions\ExcelExporter
 */
class NewsListExporter extends ExcelExporter implements WithMapping
{
    /**
     * @var string
     */
    protected $fileName = '弹幕消息列表.xlsx';
    /**
     * @var array
     */
    protected $columns = [
        'hasOneUser.username' => '姓名',
        'hasOneUser.nickname' => '微信昵称',
        'hasOneUser.phone' => '手机号码',
        'content' => '消息内容',
        'status' => '审核状态',
        'is_shelf' => '下架状态',
        'created_at' => '参与时间',
        // .....关联字段  不需显示出来
        'user_id' => ''
    ];

    public function map($row): array
    {
        // 审核状态
        $status = data_get($row, 'status');
        switch ($status) {
            case 0:
                $status = '未审核';
                break;
            case 1:
                $status = '通过';
                break;
            case 2:
                $status = '未通过';
                break;
            default:
                $status = '通过';
        }
        // 下架状态
        $is_shelf = data_get($row, 'is_shelf');
        switch ($is_shelf) {
            case 1:
                $is_shelf = '未下架';
                break;
            case 2:
                $is_shelf = '已下架';
                break;
            default:
                $is_shelf = '未下架';
        }

        return [
            data_get($row, 'hasOneUser.username'),
            data_get($row, 'hasOneUser.nickname'),
            data_get($row, 'hasOneUser.phone'),
            $row->content,
            $status,
            $is_shelf,
            $row->created_at,
        ];
    }

}
