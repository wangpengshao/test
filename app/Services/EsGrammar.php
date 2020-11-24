<?php

namespace App\Services;

class EsGrammar
{
    /**
     * @var array
     */
    protected $selectComponents = [
        '_source' => 'columns',
        'query' => 'wheres',
        'aggs',
        'sort' => 'orders',
        'size' => 'limit',
        'from' => 'offset',
        'index' => 'index',
        'type' => 'type',
        'scroll' => 'scroll',
    ];

    public function compileOffset(EsBuilder $builder): int
    {
        return $builder->offset;
    }

    public function compileLimit(EsBuilder $builder): int
    {
        return $builder->limit;
    }

    public function compileScroll(EsBuilder $builder): string
    {
        return $builder->scroll;
    }

    public function compileSelect(EsBuilder $builder)
    {
        $body = $this->compileComponents($builder);
        $index = array_pull($body, 'index');
        $scroll = array_pull($body, 'scroll');
        $params = ['body' => $body, 'index' => $index];
        if ($scroll) {
            $params['scroll'] = $scroll;
        }
        return $params;
    }

    public function compileCreate(EsBuilder $builder, $id, array $data): array
    {
        return array_merge([
            'id' => $id,
            'body' => $data,
        ], $this->compileComponents($builder));
    }

    public function compileNoIdCreate(EsBuilder $builder, array $data): array
    {
        $params['body'] = [];

        $params['body'][] = [
            'create' => [
                '_index' => $builder->index,
            ]
        ];
        $params['body'][] = $data;
        return $params;
    }

    public function compileDelete(EsBuilder $builder, $id): array
    {
        return array_merge([
            'id' => $id,
        ], $this->compileComponents($builder));
    }

    public function compileUpdate(EsBuilder $builder, $id, array $data): array
    {
        return array_merge([
            'id' => $id,
            'body' => ['doc' => $data],
        ], $this->compileComponents($builder));
    }

    public function compileAggs(EsBuilder $builder): array
    {
        $aggs = [];

        foreach ($builder->aggs as $field => $aggItem) {
            if (is_array($aggItem)) {
                $aggs[] = $aggItem;
            } else {
                $aggs[$field . '_' . $aggItem] = [$aggItem => ['field' => $field]];
            }
        }

        return $aggs;
    }

    public function compileColumns(EsBuilder $builder): array
    {
        return $builder->columns;
    }

    public function compileIndex(EsBuilder $builder): string
    {
        return is_array($builder->index) ? implode(',', $builder->index) : $builder->index;
    }


    public function compileOrders(EsBuilder $builder): array
    {
        $orders = [];

        foreach ($builder->orders as $field => $orderItem) {
            $orders[$field] = is_array($orderItem) ? $orderItem : ['order' => $orderItem];
        }

        return $orders;
    }

    protected function compileWheres(EsBuilder $builder): array
    {
        $whereGroups = $this->wherePriorityGroup($builder->wheres);
        $operation = count($whereGroups) === 1 ? 'must' : 'should';
        $bool = [];
        foreach ($whereGroups as $wheres) {
            $must = [];
            foreach ($wheres as $where) {
//                if ($where['type'] === 'Nested') {
//                    $must[] = $this->compileWheres($where['query']);
//                } else {
                $must[] = $this->whereLeaf($where['leaf'], $where['column'], $where['operator'], $where['value']);
//                }
            }
            if (!empty($must)) {
                $bool['bool'][$operation][] = count($must) === 1 ? array_shift($must) : ['bool' => ['must' => $must]];
            }
        }

        return $bool;
    }

    /**
     * @param string      $leaf
     * @param string      $column
     * @param string|null $operator
     * @param             $value
     *
     * @return array
     */
    protected function whereLeaf(string $leaf, string $column, string $operator = null, $value): array
    {
        if (in_array($leaf, ['term', 'match'], true)) {
            return [$leaf => [$column => $value]];
        } elseif ($leaf === 'range') {
            return [$leaf => [
                $column => is_array($value) ? $value : [$operator => $value],
            ]];
        }
    }

    /**
     * @param array $wheres
     *
     * @return array
     */
    protected function wherePriorityGroup(array $wheres): array
    {
        //get "or" index from array
        $orIndex = (array)array_keys(array_map(function ($where) {
            return $where['boolean'];
        }, $wheres), 'or');

        $lastIndex = $initIndex = 0;
        $group = [];
        foreach ($orIndex as $index) {
            $group[] = array_slice($wheres, $initIndex, $index - $initIndex);
            $initIndex = $index;
            $lastIndex = $index;
        }

        $group[] = array_slice($wheres, $lastIndex);

        return $group;
    }

    protected function compileComponents(EsBuilder $query): array
    {
        $body = [];
        foreach ($this->selectComponents as $key => $component) {
            if (!empty($query->$component)) {
                $method = 'compile' . ucfirst($component);
                $body[is_numeric($key) ? $component : $key] = $this->$method($query, $query->$component);
            }
        }
        return $body;
    }
}
