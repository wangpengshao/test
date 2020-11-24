<?php

namespace App\Admin\Extensions\ExcelImport;

use App\Models\Recommend\RecommendBooks;
use App\Models\Recommend\RecommendIsbn;
use App\Models\Recommend\Isbn;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BooksImport implements ToModel, WithValidation, WithHeadingRow
{
    use Importable;
    private $token;
    private $id;
    private $count;

    public function setParam($token, $id, $count)
    {
        $this->token = $token;
        $this->id = $id;
        $this->count = $count;
    }

    public function model(array $row)
    {
        $a = [];
        if (!empty($row['title'])) {
            $a = new RecommendBooks([
                'title' => $row['title'],
                'image' => $row['image'],
                'status' => 1,
                'a_status' => 1,
                'intro' => $row['intro'],
                'token' => $this->token,
                'stage_id' => $this->count,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        // 判断导入书单的isbn中是否已经包含在isbn表库中了，若不存在则添加到isbn表库中
        $exists = Isbn::where(['isbn' => $row['isbn'], 'token' => session('wxtoken')])->exists();
        if ($exists) {
            $c = [];
        } else {
            $c = new Isbn([
                'isbn' => $row['isbn'],
                's_id' => $this->id,
                'token' => $this->token,
                'reason' => $row['reason'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $b = new RecommendIsbn([
            'isbn' => $row['isbn'],
            's_id' => $this->id,
            'token' => $this->token,
            'reason' => $row['reason'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        // 添加数据
        // 全不空则将两边数据添加到三张表中，否则代表有重复的isbn存在于isbn表库中，则数据无需添加到isbn表库中
        // excel书单默认书单名称只写在第一行，其它行无需重复写。所以当$a为空的时候，也无需操作recommendBook表
        if (!empty($a) && !empty($b) && !empty($c)) {
            return [$a, $b, $c];
        } elseif (!empty($a) && !empty($b)) {
            return [$a, $b];
        } elseif (!empty($b) && !empty($c)) {
            return [$b, $c];
        } else {
            return [$b];
        }
    }

    public function rules(): array
    {
        return [
            'isbn' => 'required',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            'isbn' => '书籍编号',
        ];
    }

}