<?php

namespace App\Admin\Extensions\ExcelExporter;

use Encore\Admin\Grid\Exporters\ExcelExporter;

class UserListExporter extends ExcelExporter
{
    /**
     * @var string
     */
    protected $fileName = '参与用户列表.xlsx';

    /**
     * @var array
     */
    protected $columns = [
        'username' => '姓名',
        'nickname' => '微信昵称',
        'headimgurl' => '微信头像',
        'phone' => '手机号码',
        'created_at' => '参与时间'
    ];
}
