<?php

namespace App\Admin\Controllers\Wechat\Vote;

use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\HeadTitle;
use App\Admin\Extensions\Tools\IconButton;
use App\Models\Vote\VoteConfig;
use App\Models\Vote\VoteGroup;
use App\Models\Vote\VoteItems;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Wechatapp;
use EasyWeChat\Kernel\Messages\Text;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class VoteItemsController extends Controller
{
    use HasResourceActions;

    protected $statusStates = [
        'on' => ['value' => 1, 'text' => '通过', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => '未审', 'color' => 'default'],
    ];
    protected $lockstatusStates = [
        'on' => ['value' => 0, 'text' => '正常', 'color' => 'success'],
        'off' => ['value' => 1, 'text' => '已锁', 'color' => 'danger'],
    ];

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('作品管理')
            ->description('description')
            ->body($this->grid());
    }

    public function show($id, Content $content, Request $request)
    {
        $gridWhere = $request->session()->get('gridWhere');
        $g_id = Arr::get($gridWhere, 'g_id');
        $a_id = Arr::get($gridWhere, 'a_id');
        $group = VoteGroup::where(['a_id' => $a_id, 'id' => $g_id])->first();
        $fields = $group->fields->keyBy('id');
        $vote = VoteItems::with('fans')->find($id);

        foreach ($fields as $key => $value) {
            if ($value['type'] == 1 || $value['type']) {
                $fields[$key]['options'] = explode('|', $value['data']);
            }
        }
        if (empty($vote['content'])) {
            $vote['content'] = [];
        }
        return $content
            ->header('展示')
            ->description('作品')
            ->body(view('admin.diy.voteDetails', ['fields' => $fields, 'vote' => $vote])->render());
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form($id)->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
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
        Admin::script($this->audtingJS());

        $request = \request();
        if ($request->filled('g_id')) {
            $request->session()->put('gridWhere.g_id', $request->input('g_id'));
        }
        $gridWhere = $request->session()->get('gridWhere');

        $g_id = Arr::get($gridWhere, 'g_id');
        $a_id = Arr::get($gridWhere, 'a_id');
        $title = VoteGroup::where('id', $g_id)->value('title');

        $grid = new Grid(new VoteItems);
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->equal('number', '编号');
            $filter->like('title', '名称');
        });
//        $grid->disableCreateButton();
        $grid->model()->where('g_id', $g_id)->with('fans');
        $grid->model()->where('a_id', $a_id)->with('fans');
        $grid->header(function ($query) use ($title) {
            return new HeadTitle($title);
        });
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/vote/group'), '返回分组'));
        });
        $grid->actions(function ($actions) {
            $url = route('message.index', ['g_id' => $actions->row->g_id, 't_id' => $actions->row->id]);
            $actions->prepend(new IconButton($url, '查看留言', 'fa-comments-o'));
        });
        $grid->column('number', '编号');
        $grid->title('名称');

        $grid->column('微信昵称')->display(function () {
            if (isset($this->fans)) {
                return $this->fans->nickname;
            }
            return '';
        });
        $grid->phone('手机号码');
        $grid->cover('封面')->image('', 100, 100);
        $grid->status('审核状态')->switch($this->statusStates)->sortable();;
        $grid->lockstatus('锁定状态')->switch($this->lockstatusStates)->sortable();
        $grid->created_at('参赛时间')->sortable();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(VoteItems::findOrFail($id));
        $show->id('Id');
        $show->a_id('A id');
        $show->g_id('G id');
        $show->title('Title');
        $show->phone('Phone');
        $show->cover('Cover');
        $show->info('Info');
        $show->view_n('View n');
        $show->voting_n('Voting n');
        $show->ranking('Ranking');
        $show->content('Content');
        $show->status('Status');
        $show->openid('Openid');
        $show->lockinfo('Lockinfo');
        $show->lockstatus('Lockstatus');
        $show->create_at('Create at');
        $show->number('Number');

        return $show;
    }

    protected function form($id = null)
    {
        $request = \request();
        if ($request->filled('g_id')) {
            $request->session()->put('gridWhere.g_id', $request->input('g_id'));
        }
        $gridWhere = $request->session()->get('gridWhere');
        $g_id = Arr::get($gridWhere, 'g_id');
        $a_id = Arr::get($gridWhere, 'a_id');
        $group = VoteGroup::where(['a_id' => $a_id, 'id' => $g_id])->first();
        $fields = $group->fields;

        $token = $request->session()->get('wxtoken');
        $form = new Form(new VoteItems);
        $form->footer(function ($footer) {
            // 显示`继续创建`checkbox
            $footer->disableCreatingCheck(false);
        });
        $form->hidden('a_id')->default($a_id);
        $form->hidden('g_id')->default($g_id);
        $form->text('title', '标题')->required();
        $form->mobile('phone', '手机号码')->required();

        $moveUrl = 'voteImage/' . $token . '/' . $a_id;
        $form->image('cover', '封面')->move($moveUrl)->uniqueName();
        $form->textarea('info', '简介');
        $form->switch('status', '审核状态')->states($this->statusStates);
        $form->switch('lockstatus', '锁定状态')->states($this->lockstatusStates);
        $form->text('lockinfo', '锁定说明');
        $form->embeds('content', '其他信息', function ($form) use ($fields, $moveUrl) {
            foreach ($fields as $k => $v) {
                switch ($v['type']) {
                    case 1:
                        $data = explode('|', $v['data']);
                        $form->radio($v['id'], $v['name'])->options($data);
                        break;
                    case 2:
                        $data = explode('|', $v['data']);
                        $form->checkbox($v['id'], $v['name'])->options($data);
                        break;
                    case 4:
                        if ($v['data'] < 2) {
                            $form->image($v['id'], $v['name'])->move($moveUrl)->attribute('hideMaterial')->removable();
                        } else {
                            //暂不支持多图
                            $form->multipleImage($v['id'], $v['name'])->move($moveUrl)->attribute('hideMaterial')->removable();
                        }
                        break;
                    default:
                        $form->text($v['id'], $v['name']);
                }
            }
        });

        $form->display('created_at', '创建时间')->default(date('Y-m-d H:i:s'));
        /* 素材库 上传图片 例子 start */
//        $form->image('image', '封面')->move(materialUrl())->uniqueName();
        $form->hidden(config('materialPR') . 'cover');
        $imgArray = [config('materialPR') . 'cover'];
        $form->ignore($imgArray);
        /* 素材库 上传图片 例子 end */
        $form->saving(function (Form $form) use ($imgArray) {
            if (\request()->isMethod('post')) {
                //新增数据
                $number = VoteItems::where(['a_id' => $form->a_id, 'g_id' => $form->g_id])
                    ->orderBy('number', 'Desc')->value('number');
                $form->model()->number = $number + 1;
            }
            foreach ($imgArray as $k => $v) {
                if (\request()->input($v)) {
                    $imgName = substr($v, strlen(config('materialPR')));
                    $form->model()->$imgName = \request()->input($v);
                }
            }
            unset($k, $v);
        });

        $form->saved(function (Form $form) {
            $model = $form->model();
            $a_id = $model->a_id;
            $g_id = $model->g_id;
            $cache = Redis::connection('cache');
            $keys = $cache->keys(config('cache.prefix') . ':vote:item:a:' . $a_id . ':g:' . $g_id . '*');
            foreach ($keys as $key) {
                $cache->del($key);
            }

            //清除all的缓存
            $cacheKey = sprintf('vote:itemAll:a:%s:g:%s', $a_id, $g_id);
            Cache::forget($cacheKey);

            if (\request()->isMethod('post')) {
                //新增数据
                $redis = Redis::connection();
                $redis->zadd($this->cacheKey($a_id, $g_id, 'view'), 0, $model->id);
                $redis->zadd($this->cacheKey($a_id, $g_id, 'vote'), 0, $model->id);
            }
        });
        return $form;
    }

    public function cacheKey($a_id, $g_id, $type = 'vote')
    {
        return 'voteRank:' . $type . ':a:' . $a_id . ':g:' . $g_id;
    }

    public function auditing(Request $request)
    {
        $id = $request->post('id');
        $status = $request->post('status');
        $token = session('wxtoken');

        $vote = VoteItems::find($id);
        $vote->status = $this->statusStates[$status]['value'];
        $vote->save();

        //清除all的缓存
        $cacheKey = sprintf('vote:itemAll:a:%s:g:%s', $vote->a_id, $vote->g_id);
        Cache::forget($cacheKey);

        if(!empty($vote->openid) && $vote->status == 1){

            $voteConfig = VoteConfig::find($vote->a_id);
            if($voteConfig->template_id == 9){
                $url = route('Vote::details2', ['token' => $token, 'a_id' => $vote->a_id, 't_id'=>$vote->id]);
            }else{
                $url = route('Vote::details', ['token' => $token, 'a_id' => $vote->a_id, 't_id'=>$vote->id]);
            }

            $app = Wechatapp::initialize($token);
            $text = "您好，您的参赛作品<a href='".$url ."'>《". $vote->title ."》</a>已经审核通过了，请锁定投票时间，准时前往进行拉票哟! ";
            $app->customer_service->message(new Text($text))->to($vote->openid)->send();
        }

        return response()->json(['status'=>true, 'message'=>'更新成功']);
    }

    public function audtingJS()
    {
        $router = route('vote.auditing');
        return <<<SCRIPT
            $('.grid-switch-status').bootstrapSwitch({
                size: 'mini',
                onText: '通过',
                offText: '未审',
                onColor: 'success',
                offColor: 'default',
                onSwitchChange: function (event, state) {

                    $(this).val(state ? 'on' : 'off');
        
                    var pk = $(this).data('key');
                    var value = $(this).val();
                    var _status = true;
        
                    $.ajax({
                        url: "{$router}",
                        type: "POST",
                        async: false,
                        data: {
                            "id": pk,
                            "status": value,
                            _token: LA.token,
                        },
                        success: function (data) {
                            if (data.status)
                                toastr.success(data.message);
                            else
                                toastr.warning(data.message);
                        },
                        complete: function (xhr, status) {
                            if (status == 'success')
                                _status = xhr.responseJSON.status;
                        }
                    });
        
                    return _status;
                }
            });
SCRIPT;

    }
}
