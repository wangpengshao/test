<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Extensions\ExcelExporter\MenuDataExporter;
use App\Admin\Extensions\Tools\IconButton;
use App\Models\Wechat\IndexMenu;
use Carbon\Carbon;
use App\Services\EsBuilder;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class MenulogController extends Controller
{

    public function index(Content $content, Request $request)
    {
        return $content->title('菜单访问数据统计')->description('列表')
            ->row(function (Row $row) use ($request) {
                $row->column(6, $this->grid($request));

                $row->column(6, function (Column $column) use ($request) {
                    $form = new Form();
                    $form->method('get');
                    $form->action($request->url());

                    $end = $request->input('end', date('Y-m-d H:i:s'));
                    $form->datetimeRange('start', 'end', '时间')->default([
                        'start' => $request->input('start'),
                        'end' => $end,
                    ]);
                    $form->text('caption', '名称')->default($request->input('caption', ''));
                    $column->append((new Box('条件筛选', $form))->style('success'));
                });
            });
    }

    protected function grid(Request $request)
    {
        $token = $request->session()->get('wxtoken');
        $caption = $request->input('caption');
        $start_at = $request->input('start');
        $end_at = $request->input('end');
        $grid = new Grid(new IndexMenu);
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
        $grid->disableFilter();
        $grid->disableCreateButton();

        $grid->disableExport(false);
        $grid->exporter(new MenuDataExporter($start_at, $end_at));

        if ($caption) {
            $grid->model()->where('caption', 'like', '%' . $caption . '%');
        }
        $grid->model()->where('token', $token)->orderBy('status', 'desc');

        $grid->column('icon')->display(function ($icon, $column) {
            if ($icon) {
                return $column->image('', 70, 70);
            }
            return "<img src='https://wechat-xin.oss-cn-shenzhen.aliyuncs.com/wechat/18c6684c/nopic.png' class='img img-thumbnail' />";
        });

        $grid->column('caption', '名称');
        $grid->column('访问次数')->display(function () use ($start_at, $end_at) {
            $query = EsBuilder::index(config('search.aliases.click'))->whereTerm('mid', $this->id);
            if ($start_at && $end_at) {
                $query->whereBetween('created_at', [$start_at, $end_at]);
            }
            return $query->count();
        });
        $grid->actions(function ($actions) {
            $url = route('menuLog.show', $actions->row->id);
            $actions->append(new IconButton($url, '查看更多图表', 'fa-area-chart'));
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
        return $grid;
    }

    public function show(Request $request, Content $content)
    {
        $content->header('图表');
        $content->description('....');

        $token = $request->session()->get('wxtoken');
        $mid = $request->route('mid');
        $type = $request->input('type', 'm');
        $year = $request->input('year', date('Y'));

        $caption = IndexMenu::where([
            'token' => $token,
            'id' => $mid
        ])->value('caption');
        if (empty($caption)) {
            return $content->withError('警告', '非法访问..');
        }

        $box = '';
        $yearCarbon = Carbon::create($year);
        $startOfYear = $yearCarbon->startOfYear()->toDateTimeString();
        $endOfYear = $yearCarbon->endOfYear()->toDateTimeString();
        $EsBuilder = EsBuilder::index(config('search.aliases.click'))->whereTerm('mid', $request->route('mid'));

        switch ($type) {
            //24小时表
            case 'h':
                $size = 24;
                $hourResponse = $EsBuilder->whereRange('created_at', '', [$startOfYear, $endOfYear])
                    ->customSearch([
                        "aggs" => [
                            'byHours' => [
                                'terms' => [
                                    'script' => [
                                        "lang" => "painless",
                                        "source" => "doc['created_at'].value.getHour()",
                                    ],
                                    'size' => $size,
                                ]
                            ]
                        ],
                    ]);
                $hourBuckets = $hourResponse['aggregations']['byHours']['buckets'];
                $data = array_fill(0, 24, 0);
                foreach ($hourBuckets as $k => $v) {
                    $key = (int)$v['key'];
                    $data[$key] = $v['doc_count'];
                }
                $labels = range(0, 23);
                $box = new Box($year . '年 "按小时分类" 数据表', view('admin.Chart.menuLogB')->with([
                    'labels' => $labels,
                    'caption' => $caption,
                    'data' => $data,
                ]));
                break;
            case 'w':
                $size = 7;
                $weekResponse = $EsBuilder->whereRange('created_at', '', [$startOfYear, $endOfYear])
                    ->customSearch([
                        "size" => 7,
                        "aggs" => [
                            'byWeek' => [
                                'terms' => [
                                    'script' => [
                                        "lang" => "painless",
                                        "source" => "doc['created_at'].value.dayOfWeekEnum.value",
                                    ],
                                    "size" => $size,
                                ]
                            ]
                        ],
                    ]);
                $hourBuckets = $weekResponse['aggregations']['byWeek']['buckets'];
                $data = array_fill(0, $size, 0);
                foreach ($hourBuckets as $k => $v) {
                    $key = (int)$v['key'];
                    $data[$key - 1] = $v['doc_count'];
                }
                $labels = ["星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期天"];
                $box = new Box($year . '年 "按星期分类" 数据表', view('admin.Chart.menuLogB')->with([
                    'labels' => $labels,
                    'caption' => $caption,
                    'data' => $data,
                ]));
                break;
            case 'm':
                $size = 12;
                $monthResponse = $EsBuilder->whereRange('created_at', '', [$startOfYear, $endOfYear])
                    ->customSearch([
                        "aggs" => [
                            'byMonth' => [
                                'terms' => [
                                    'script' => [
                                        "lang" => "painless",
                                        "source" => "doc['created_at'].value.getMonthValue()",
                                    ],
                                    "size" => $size,
                                ]
                            ]
                        ],
                    ]);
                $monthBuckets = $monthResponse['aggregations']['byMonth']['buckets'];
                $data = array_fill(0, $size, 0);
                foreach ($monthBuckets as $k => $v) {
                    $key = (int)$v['key'];
                    $data[$key - 1] = $v['doc_count'];
                }
                $labels = ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"];
                $box = new Box($year . '年 "按月份分类" 数据表', view('admin.Chart.menuLogL')->with([
                    'labels' => $labels,
                    'caption' => $caption,
                    'data' => $data,
                ]));
                break;
        }
        return $content->row(function ($row) use ($request, $box, $year, $type) {
            $row->column(8, function (Column $column) use ($box) {
                $column->append($box);
            });
            $row->column(4, function (Column $column) use ($request, $year, $type) {
                $form = new Form();
                $form->method('get');
                $form->action($request->url());
                $form->date('year', '年份')->format('YYYY')->default($year);
                $form->radio('type', '横坐标')->options(['m' => '12月份', 'h' => '24小时', 'w' => '星期'])
                    ->stacked()->default($type);
                $column->append((new Box(' ', $form))->style('success'));
            });
        });

    }

}
