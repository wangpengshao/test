<?php

namespace App\Admin\Controllers\Wechat\Recommend;

use App\Http\Controllers\Controller;
use App\Models\Recommend\RecommendIsbn;
use App\Services\CoverService;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

/**
 * 推荐书单
 * Class RecommendBooksController
 * @package App\Admin\Controllers\Wechat\Recommend
 */
class BookListController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $content->row(function (Row $row) {
            $row->column(8, function (Column $column) {
                $token = session('wxtoken');
                $Isbn = RecommendIsbn::whereToken(request()->route('token'))
                    ->where('s_id', request()->route('s_id'))->get()->toArray();
                foreach ($Isbn as $key => $value) {
                    // 获取书籍封面
                    $image = CoverService::search($value['isbn']);
                    // 获取书籍的详细数据
                    $detail = CoverService::bookInfoLv2($value['isbn']);
                    $bookList[] = [
                        'image' => $image,
                        'title' => $detail['title'],
                        'author' => $detail['author'],
                        'publisher' => $detail['publisher'],
                        'price' => $detail['price'],
                        'summary' => $detail['summary'],
                        'reason' => $value['reason'],
                        'view_num' => $value['view_num'],
                        'col_num' => $value['col_num'],
                    ];
                }
                $html = view('admin.recommend.detail', compact('bookList', 'token'))->render();
                $column->append(new Box("书籍详情", $html));
            });
        });
        return $content;
    }

}