<?php

namespace App\Services;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Collection;
use RuntimeException;
use stdClass;

class EsBuilder
{
    /**
     * @var array
     */
    public $wheres = [];

    /**
     * @var array
     */
    public $columns = [];

    /**
     * @var null
     */
    public $offset = null;

    /**
     * @var null
     */
    public $limit = null;

    /**
     * @var array
     */
    public $orders = [];

    /**
     * @var array
     */
    public $aggs = [];

    /**
     * @var string
     */
    public $index = '';

    /**
     * @var string
     */
    public $scroll = '';

    /**
     * @var array
     */
    public $operators = [
        '=' => 'eq',
        '>' => 'gt',
        '>=' => 'gte',
        '<' => 'lt',
        '<=' => 'lte',
    ];

    /**
     * @var Grammar|null
     */
    protected $grammar = null;

    /**
     * @var Elasticsearch\Client|null
     */
    protected $elastisearch = null;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * 不允许从外部调用以防止创建多个实例
     */
    private function __construct()
    {
        $this->grammar = new EsGrammar();
        $this->config = config('search');
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setConnectionPool($this->config['connection_pool'])
            ->setSelector($this->config['selector'])
            ->setHosts($this->config['hosts']);
        $this->elastisearch = $clientBuilder->build();
    }

    /**
     * 防止实例被克隆（这会创建实例的副本）
     */
    private function __clone()
    {
    }

    public static function index(string $index): self
    {
        $instance = new static();
        return $instance->setIndex($index);
    }

    protected function setConfig($config): self
    {
        $this->config = $config;
        return $this;
    }

    protected function setIndex($index): self
    {
        $this->index = $index;
        return $this;
    }


    public function limit(int $value): self
    {
        $this->limit = $value;

        return $this;
    }

    public function take(int $value): self
    {
        return $this->limit($value);
    }

    public function offset(int $value): self
    {
        $this->offset = $value;

        return $this;
    }

    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    public function orderBy(string $field, $sort): self
    {
        $this->orders[$field] = $sort;

        return $this;
    }

    public function aggBy($field, $type = null): self
    {
        is_array($field) ?
            $this->aggs[] = $field :
            $this->aggs[$field] = $type;

        return $this;
    }

    public function scroll(string $scroll): self
    {
        $this->scroll = $scroll;

        return $this;
    }

    public function select($columns): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    public function whereMatch($field, $value, $boolean = 'and'): self
    {
        return $this->where($field, '=', $value, 'match', $boolean);
    }

    public function orWhereMatch($field, $value, $boolean = 'or'): self
    {
        return $this->whereMatch($field, $value, $boolean);
    }

    public function whereTerm($field, $value, $boolean = 'and'): self
    {
        return $this->where($field, '=', $value, 'term', $boolean);
    }

    public function whereIn($field, array $value)
    {
        return $this->where(function (self $query) use ($field, $value) {
            array_map(function ($item) use ($query, $field) {
                $query->orWhereTerm($field, $item);
            }, $value);
        });
    }

    public function orWhereIn($field, array $value)
    {
        return $this->orWhere(function (self $query) use ($field, $value) {
            array_map(function ($item) use ($query, $field) {
                $query->orWhereTerm($field, $item);
            }, $value);
        });
    }

    public function orWhereTerm($field, $value, $boolean = 'or'): self
    {
        return $this->whereTerm($field, $value, $boolean);
    }

    public function whereRange($field, $operator = null, $value = null, $boolean = 'and'): self
    {
        return $this->where($field, $operator, $value, 'range', $boolean);
    }

    public function orWhereRange($field, $operator = null, $value = null): self
    {
        return $this->where($field, $operator, $value, 'or');
    }

    public function whereBetween($field, array $values, $boolean = 'and'): self
    {
        return $this->where($field, null, $values, 'range', $boolean);
    }

    public function orWhereBetween($field, array $values): self
    {
        return $this->whereBetween($field, $values, 'or');
    }


    public function where($column, $operator = null, $value = null, $leaf = 'term', $boolean = 'and'): self
    {
        if ($column instanceof \Closure) {
            return $this->whereNested($column, $boolean);
        }
        if (func_num_args() === 2) {
            list($value, $operator) = [$operator, '='];
        }
        if (is_array($operator)) {
            list($value, $operator) = [$operator, null];
        }
        if ($operator !== '=') {
            $leaf = 'range';
        }
        if (is_array($value) && $leaf === 'range') {
            $value = [
                $this->operators['>='] => $value[0],
                $this->operators['<='] => $value[1],
            ];
        }
        $operator = $operator ? $this->operators[$operator] : $operator;
        $this->wheres[] = compact(
            'type', 'column', 'leaf', 'value', 'boolean', 'operator'
        );
        return $this;
    }

    public function orWhere($field, $operator = null, $value = null, $leaf = 'term'): self
    {
        if (func_num_args() === 2) {
            list($value, $operator) = [$operator, '='];
        }
        return $this->where($field, $operator, $value, $leaf, 'or');
    }

    public function first()
    {
        $this->limit = 1;
        $results = $this->runQuery($this->grammar->compileSelect($this));
        return $this->metaData($results)->first();
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        $results = $this->runQuery($this->grammar->compileSelect($this));
        return $this->metaData($results);
    }


    /**
     * @param int  $page
     * @param int  $perPage
     * @param bool $returnArr
     * @return array|Collection
     */
    public function paginate(int $page, int $perPage = 15, bool $returnArr = false)
    {
        $from = (($page * $perPage) - $perPage);
        if (empty($this->offset)) {
            $this->offset = $from;
        }
        if (empty($this->limit)) {
            $this->limit = $perPage;
        }
        $results = $this->runQuery($this->grammar->compileSelect($this));
        if ($returnArr === true) {
            $data = [];
            foreach ($results['hits']['hits'] as $k => $v) {
                $data[$k] = array_merge($v['_source'], ['_id' => $v['_id']]);
            }
        } else {
            $data = collect($results['hits']['hits'])->map(function ($hit) {
                return (object)array_merge($hit['_source'], ['_id' => $hit['_id']]);
            });
        }
        $total = $results['hits']['total']['value'];
        $maxPage = intval(ceil($total / $perPage));
        $pagingData = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'next_page' => $page < $maxPage ? $page + 1 : $maxPage,
            'total_pages' => $maxPage,
            'from' => $from,
            'to' => $from + $perPage,
            'data' => $data,
        ];
        if ($returnArr === true) {
            return $pagingData;
        }
        return collect($pagingData);
    }

    public function byId($id)
    {
        $result = $this->runQuery(
            $this->whereTerm('_id', $id)->getGrammar()->compileSelect($this)
        );
        return isset($result['hits']['hits'][0]) ?
            $this->sourceToObject($result['hits']['hits'][0]) :
            null;
    }

    public function byIdOrFail($id): stdClass
    {
        $result = $this->byId($id);

        if (empty($result)) {
            throw new RuntimeException('Resource not found');
        }

        return $result;
    }

    public function chunk(callable $callback, $limit = 2000, $scroll = '10m')
    {
        if (empty($this->scroll)) {
            $this->scroll = $scroll;
        }
        if (empty($this->limit)) {
            $this->limit = $limit;
        }
        $results = $this->runQuery($this->grammar->compileSelect($this), 'search');
        if ($results['hits']['total'] === 0) {
            return;
        }
        $total = $this->limit;
        $whileNum = intval(floor($results['hits']['total'] / $this->limit));
        do {
            if (call_user_func($callback, $this->metaData($results)) === false) {
                return false;
            }
            $results = $this->runQuery(['scroll_id' => $results['_scroll_id'], 'scroll' => $this->scroll], 'scroll');
            $total += count($results['hits']['hits']);
        } while ($whileNum--);
    }

    public function create(array $data, $id = null, $key = 'id'): stdClass
    {
        $id = isset($data['id']) ? $data['id'] : $id;
        if (isset($id) !== true) {
            $result = $this->elastisearch->bulk($this->grammar->compileNoIdCreate($this, $data));
            if (!isset($result['items'][0]['create']['result']) || $result['items'][0]['create']['result'] !== 'created') {
                throw new RunTimeException('Create params: ' . json_encode($this->getLastQueryLog()));
            }
            $data['_id'] = $result['items'][0]['create']['_id'];
            return (object)$data;
        }
        $result = $this->runQuery(
            $this->grammar->compileCreate($this, $id, $data),
            'create'
        );
        if (!isset($result['result']) || $result['result'] !== 'created') {
            throw new RunTimeException('Create params: ' . json_encode($this->getLastQueryLog()));
        }
        $data['_id'] = $id;
        return (object)$data;
    }


    /**
     * 批量插入数据
     * @param array $data
     * @return array
     */
    public function bulkInsert(array $data)
    {
        $params['body'] = [];
        foreach ($data as $k => $v) {
            $params['body'][] = [
                'create' => [
                    '_index' => $this->index,
                    '_id' => $v['id'],
                ]
            ];
            unset($v['id']);
            $params['body'][] = $v;
        }
        return $this->elastisearch->bulk($params);
    }

    /**
     * 条件删除
     * @return array
     */
    public function deleteByQuery()
    {
        return $this->elastisearch->deleteByQuery($this->grammar->compileSelect($this));
    }

    public function update($id, array $data): bool
    {
        $result = $this->runQuery($this->grammar->compileUpdate($this, $id, $data), 'update');
        if (!isset($result['result']) || $result['result'] !== 'updated') {
            throw new RunTimeException('Update error params: ' . json_encode($this->getLastQueryLog()));
        }
        return true;
    }

    public function delete($id)
    {
        $result = $this->runQuery($this->grammar->compileDelete($this, $id), 'delete');
        if (!isset($result['result']) || $result['result'] !== 'deleted') {
            throw new RunTimeException('Delete error params:' . json_encode($this->getLastQueryLog()));
        }
        return true;
    }

    public function count(): int
    {
        $result = $this->runQuery($this->grammar->compileSelect($this), 'count');
        return $result['count'];
    }

    public function getGrammar()
    {
        return $this->grammar;
    }

    protected function runQuery(array $params, string $method = 'search')
    {
        return call_user_func([$this->elastisearch, $method], $params);
    }

    protected function metaData(array $results): Collection
    {
        return collect($results['hits']['hits'])->map(function ($hit) {
            return $this->sourceToObject($hit);
        });
    }

    protected function sourceToObject(array $result): stdClass
    {
        return (object)array_merge($result['_source'], ['_id' => $result['_id']]);
    }

    public function customSearch(array $customBody = [])
    {
        if ($customBody) {
            $params = $this->grammar->compileSelect($this);
            $params['body'] = array_merge($params['body'], $customBody);
            return $this->runQuery($params, 'search');
        }
        return $this->runQuery($this->grammar->compileSelect($this), 'search');
    }
}
