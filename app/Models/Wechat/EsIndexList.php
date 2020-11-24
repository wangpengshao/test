<?php

namespace App\Models\Wechat;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class EsIndexList extends Model
{

    public function paginate()
    {
        $perPage = Request::get('per_page', 20);

        $page = Request::get('page', 1);

        $start = ($page - 1) * $perPage;

        // 获取当前ES下的所有索引
        $hosts = config('search.hosts.0');
        // 筛选过滤
        $caption = Request::get('caption', '');
        if (!empty($caption)) {
            $url = $hosts . '/_cat/indices/' . $caption . '?v&format=json&pretty&s=index,docs.count:desc';
        } else {
            $url = $hosts . '/_cat/indices?v&format=json&pretty&s=index,docs.count:desc';
        }
        try {
            $http = new Client();
            $responseOne = $http->get($url);
        } catch (RequestException $e) {
            //接口异常处理
            $context = [
                'url' => $url,
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ];
            if ($e->hasResponse()) {
                $context['mes'] = $e->getResponse()->getReasonPhrase();
            }
        }
        // 组合数据
        $index_all = []; // 存放索引名数据
        if (!empty($context) && $context['code'] == 404) {
            $index_all = [];
            $total = count($index_all);
        } else {
            $responseOne = json_decode((string)$responseOne->getBody(), true);
            // 数据的起始位置  -- 分页
            $start_position = $start;
            $total = count($responseOne);
            if ($total >= $page * $perPage) {
                $end_position = $page * $perPage - 1;
            } else {
                $end_position = $total - 1;
            }
            foreach ($responseOne as $key => $value) {
                // 计算当前数组长度
                // 控制当前长度在分页区间内
                if ($key >= $start_position && $key <= $end_position) {
                    $index_all[$key]['name'] = $value['index']; // 索引名称
                    // 索引状态值转化
                    if ($value['status'] == 'open') {
                        $status = 1;
                    } else {
                        $status = 2;
                    }
                    $index_all[$key]['status'] = $status; // 索引开启状态
                    $index_all[$key]['health'] = $value['health']; // 索引健康状态
                    $index_all[$key]['count'] = $value['docs.count']; //文档总数
                    $index_all[$key]['size'] = $value['store.size']; // 总数据大小
                    $index_all[$key]['pri_size'] = $value['pri.store.size']; // 主分片数据大小
                    $index_all[$key]['pri'] = $value['pri']; // 分片个数
                    $index_all[$key]['rep'] = $value['rep']; // 从分片个数
                }
            }
        }
        $indexs = static::hydrate($index_all);
        $paginator = new LengthAwarePaginator($indexs, $total, $perPage);

        $paginator->setPath(url()->current());

        return $paginator;
    }

    public static function with($relations)
    {
        return new static;
    }

}
