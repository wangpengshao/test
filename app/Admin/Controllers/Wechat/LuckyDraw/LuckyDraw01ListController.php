<?php

namespace App\Admin\Controllers\Wechat\LuckyDraw;

use App\Admin\Extensions\ExcelExporter\LuckyDrawListExporter;
use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\IsWinning;
use App\Models\LuckyDraw\LuckyDraw01List;
use App\Http\Controllers\Controller;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;

class LuckyDraw01ListController extends Controller
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
        Admin::script($this->script());
        return $content
            ->header('抽奖列表')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $request = request();
        $token = $request->session()->get('wxtoken');
        $is_winning = $request->get('is_winning');
        $grid = new Grid(new LuckyDraw01List);

        if ($is_winning == 1) {
            $grid->disableExport(false);
            $grid->exporter(new LuckyDrawListExporter());
        }

        $grid->disableCreateButton();
        $grid->model()->isWinning($is_winning)->with('address');
        $grid->model()->where('token', $token);
        $grid->model()->orderBy('id', 'desc');
        if ($request->filled('l_id')) {
            $grid->model()->where('l_id', $request->get('l_id'));
        }

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '抽奖时间')->datetime();
                $filter->equal('rdid', '读者证');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('code', '兑奖码');
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('user', function ($query) use ($input) {
                        $query->where('nickname', 'like', '%' . $input . '%');
                    });
                }, '微信昵称', 'nickname')->inputmask([], $icon = 'wechat');
            });
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            if ($actions->row->is_winning == 1 && $actions->row->status == 0) {
                $actions->append("<button class='btn btn-xs btn-success lucky-draw-01-award' data-id='{$actions->row->id}'>确认发奖</button>");
            }
        });

        $grid->tools(function ($tools) {
            $tools->append(new IsWinning());
            $tools->append(new BackButton(route('luckyDraw.type01'), '返回活动'));
        });
        $grid->column('rdid', '读者证')->width(20);
        $grid->column('fansInfo.nickname', '微信昵称');
        $grid->column('fansInfo.headimgurl', '微信头像')->image('', 50, 50);

        $grid->column('hasOneGather.phone', '手机号码');
        $grid->column('hasOneGather.name', '姓名');
        $grid->column('hasOneGather.idcard', '身份证');

        $grid->column('prize.title', '奖品名称');
//        $grid->column('prize.image', '奖品图片')->image('', 100, 100);
//        $grid->column('text', '中奖说明')->display(function ($text) {
//            return $text;
//        });
        $grid->code('兑奖码');

        $grid->column('地址详情')->expand(function () {
            $address = $this->toArray()['address'];
            $da = [
                '手机号码' => $address['phone'],
                '收件人' => $address['name'],
                '地址' => $address['address'],
            ];
            return new Table(['类型', '值'], $da);
        }, '查看');

        $grid->column('status', '状态')->display(function ($status) {
            if ($this->is_winning == 1) {
                return ($status == 1) ? '<span class="badge bg-green">已发奖</span>' :
                    '<span class="badge bg-yellow">未发奖</span>';
            }
            return '';
        });
        $grid->column('created_at', '抽奖时间')->sortable();
        $grid->column('updated_at', '发奖时间')->display(function ($updated_at) {
            return ($this->status == 1) ? $updated_at : '';
        })->hide();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(LuckyDraw01List::findOrFail($id));

        $show->id('Id');
        $show->rdid('Rdid');
        $show->openid('Openid');
        $show->is_winning('Is winning');
        $show->code('Code');
        $show->status('Status');
        $show->token('Token');
        $show->prize_id('Prize id');
        $show->created_at('Created at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LuckyDraw01List);
        $form->display('id', 'ID');
        $form->text('rdid', 'Rdid');
        $form->text('nickname', 'nickname');
        $form->text('text', 'text');
        $form->text('openid', 'Openid');
        $form->switch('is_winning', 'Is winning');
        $form->text('code', 'Code');
        $form->switch('status', 'Status');
        $form->text('token', 'Token');
        $form->number('gather_id', 'gather_id');
        $form->number('prize_id', 'Prize id');
        $form->number('l_id', 'l_id');
        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');
        return $form;
    }

    /**
     * time  2020.4.15.
     *
     * @content  添加物流单号，更新状态值
     *
     * @author  wsp
     */
    protected function addExpressNo(Request $request)
    {
        $id = $request->input('id');
        $odd_num = $request->input('odd_num');
        // 更新状态和物流单号
        $update = [
            'status' => 1,
            'expressNo' => $odd_num
        ];
        $res = LuckyDraw01List::where('id', $id)->update($update);
        if ($res) {
            return ['status' => true, 'mes' => '更新成功'];
        } else {
            return ['status' => false, 'mes' => '更新失败'];
        }
    }

    protected function script()
    {
        $logisticsUrl = route('LuckyDraw01.addExpressNo');
        return <<<SCRIPT
        
$('.lucky-draw-01-award').on('click', function () {
    let id=$(this).data('id');
    swal({
         title: '请填写物流单号',
         type: 'warning',
         input: 'text',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         inputPlaceholder:'请输入物流单号',
         preConfirm: function(text) {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$logisticsUrl}",
                        type: "post",
                        data: {"_token": LA.token,'odd_num':text,'id':id},
                        dataType: "json",
                        success: function (data) {
                          var type="error";
                          if(data.status==true){
                            type="success";
                          }
                         $.pjax.reload('#pjax-container');
                          swal('',data.mes,type);
                        },
                        error:function(){
                         swal("哎呦……", "出错了！","error");
                        }
                    });
                });
              },
         }).then(result => {
         console.log(result)
    }).catch(swal.noop)

});

SCRIPT;
    }
}
