<?php

namespace App\Admin\Extensions\ExcelImport;

use App\Models\LibraryLbs\Company;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LibraryLbsImport implements ToModel, WithValidation, WithHeadingRow
{
    use Importable;
    private $token;

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function model(array $row)
    {
        if (!isset($row['name'])) {
            return null;
        }
        return new Company([
            'name' => $row['name'],
            'is_show' => 1,
            'p_id' => 1,
            'token' => $this->token,
            'telephone' => $row['telephone'],
            'phone' => $row['phone'],
            'lat' => $row['lat'],
            'lng' => $row['lng'],
            'address' => $row['address'],
            'intro' => $row['intro'],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'phone' => 'numeric'
        ];
    }

    public function customValidationAttributes()
    {
        return [
            'name' => '单位名称',
            'phone' => '手机号码',
            'telephone' => '联系方式',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'lat.numeric' => '列 :attribute 必须是个数字类型的坐标',
            'lng.numeric' => '列 :attribute 必须是个数字类型的坐标',
            'phone.numeric' => '列 :attribute 必须是个数字类型的坐标',
        ];
    }
}
