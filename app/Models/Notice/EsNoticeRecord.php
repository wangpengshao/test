<?php

namespace App\Models\Notice;

use App\Services\EsBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class EsNoticeRecord extends Model
{
    protected $wheres = [];
    protected $filterColumn = ['rdid', 'openid'];

    public function paginate()
    {
        $perPage = Request::get('per_page', 10);
        $page = Request::get('page', 1);

        $EsBuilder = EsBuilder::index(config('search.aliases.exp_notice_log'));

        foreach ($this->wheres as $k => $v) {
            $EsBuilder->whereTerm($k, $v);
        }
        foreach ($this->filterColumn as $column) {
            $filterValue = Request::get($column);
            if ($filterValue) {
                $EsBuilder->whereTerm($column, $filterValue);
            }
        }
        $response = $EsBuilder->paginate($page, $perPage, true);
        $data = static::hydrate($response['data']);         //需要插入源数据，否则无法使用$this
        $paginator = new LengthAwarePaginator($data, $response['total'], $perPage);
        $paginator->setPath(Request::url());
        return $paginator;
    }

    // 覆盖`where`来收集筛选的字段和条件
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->wheres[$column] = $operator;
        return $this;
    }

    public static function with($relations)
    {
        return new static;
    }


}
