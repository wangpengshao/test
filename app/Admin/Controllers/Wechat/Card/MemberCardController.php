<?php

namespace App\Admin\Controllers\Wechat\Card;

use App\Models\Card\Member;
use App\Http\Controllers\Controller;
use App\Admin\Extensions\MemberCard;
use App\Models\Card\MemberCardUser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\Request;


class MemberCardController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('列表')
            ->description('会员卡')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
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
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        Admin::script($this->customJs());
        $content
            ->header('编辑')
            ->description('会员卡');


        $content->row(function(Row $row) use($id) {
            $row->column(3, function (Column $column){
                $html = view('admin.diy.memberCard')->render();
                $column->append(new Box("预览", $html));
            });
            $row->column(8, function(Column $column) use($id){
                $column->append(new Box(" ", $this->form()->edit($id)));
            });
        });
        return $content;
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        Admin::script($this->customJs());
        $content
            ->header('新建')
            ->description('会员卡');

        $content->row(function(Row $row) {
            $row->column(3, function (Column $column){
                $html = view('admin.diy.memberCard')->render();
                $column->append(new Box("预览", $html));
            });
            $row->column(8, function(Column $column){
                $column->append(new Box(" ", $this->form()));
            });
        });
        return $content;
    }

    /**
     * Destroy interface.
     */
    public function destroy($id)
    {
        $model = Member::find($id);
        MemberCard::make(session('wxtoken'))->delete($model->card_id);
        $model->delete();
        return admin_toastr('删除成功', 'success');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        Admin::script($this->customJsInList());
        $grid = new Grid(new Member);
        $grid->model()->where('token', session('wxtoken'));

        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('card_id', 'Card_Id');
        });

        $grid->actions(function ($actions) {
            $route = route('membercard.users',['card_id'=>$actions->row->card_id]);
            $actions->disableView();
            $actions->prepend('<a href="'. $route .'"  data-value="'. $actions->row->card_id .'" title="领卡用户" style="padding: 0 5px;"><i class="fa fa-user"></i></a>');
            $actions->prepend('<a href="javascript:void(0);" class="qrcode" data-value="'. $actions->row->card_id .'" title="获取卡券二维码"><i class="fa fa-qrcode"></i></a>');

        });

        $grid->column('card_id', '会员卡ID');
         /*   ->modal('卡券详情', function ($model)
        {
            $res = MemberCard::make(session('wxtoken'))->getCardInfo($model->card_id);return json_encode($res);
            $data[]= [
                'card_id' => $res['card']['member_card']['base_info']['id'],
                'brand_name' => $res['card']['member_card']['base_info']['brand_name'],
                'title' => $res['card']['member_card']['base_info']['title'],
                'date_info' => $res['card']['member_card']['base_info']['date_info']['type'] == 'DATE_TYPE_PERMANENT' ?
                    '永久有效' : date('Y-m-d',$res['card']['member_card']['base_info']['date_info']['begin_timestamp']).' — '.date('Y-m-d',$res['card']['member_card']['base_info']['date_info']['end_timestamp']),
                'sku_total' => $res['card']['member_card']['base_info']['sku']['quantity'],
                'sku_residue' => $res['card']['member_card']['base_info']['sku']['total_quantity'],
            ];
            return new Table(['ID', '商户名字', '卡券标题', '有效时间', '总数', '剩余库存'], $data);

        });*/

        $grid->column('brand_name','商户名字');
        $grid->column('title','会员卡标题');
        $grid->column('logo_url','商户Logo')->image('',50);
        $grid->column('background_pic_url','背景图片')->image('',100,60);
        $grid->column('color','背景颜色')->display(function($color){
            $realColor = Member::GetColor($color);
            return '<span style="display: inline-block;width: 25px;height: 25px;background-color:'. $realColor .';"></span>';
        });
        $grid->column('sku','库存数量')->display(function($sku){
           return $sku['quantity'];
        });
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
        $show = new Show(Member::findOrFail($id));

        $show->created_at('Created at');
        $show->day_t('Day t');
        $show->delay_t('Delay t');
        $show->id('Id');
        $show->keeptime('Keeptime');
        $show->lat('Lat');
        $show->lng('Lng');
        $show->longest_t('Longest t');
        $show->min_score('Min score');
        $show->num('Num');
        $show->ok_t('Ok t');
        $show->reset_d('Reset d');
        $show->s_score('S score');
        $show->shortest_t('Shortest t');
        $show->status('Status');
        $show->token('Token');
        $show->updated_at('Updated at');
        $show->w_score('W score');
        $show->x_score('X score');

        return $show;
    }

    /**
     * 领卡用户
     * @param Content $content
     * @return Content
     */
    public function getUsers(Content $content)
    {
        return $content
            ->header('领卡用户列表')
            ->description('会员卡')
            ->body($this->grid2(request()->input('card_id')));
    }

    protected function grid2($card_id)
    {
        $grid = new Grid(new MemberCardUser());
        $grid->model()->where(['token' => session('wxtoken'), 'card_id'=> $card_id]);
        $grid->disableCreateButton();
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
            $tools->append('<div class="btn-group pull-right new-create" style="margin-right: 10px">
                                <a href="'. url('/admin/wechat/card/member') .'" class="btn btn-sm btn-success" title="返回">
                                    <span class="hidden-xs">&nbsp;&nbsp;返回</span>
                                </a>
                            </div>');
        });

        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->equal('openid', 'openID');
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->column('from','领取渠道')->using(['miniProgra' => '小程序', 'SOURCE_SCENE_QRCODE' => '扫码']);
        $grid->column('openid','openID');
        $grid->column('nickName','昵称');
        $grid->column('avatarUrl','头像')->image('',50);
        $grid->column('gender','性别')->using(['0' => '未知', '1' => '男', '2' => '女']);
        $grid->column('code','卡券code');
        $grid->column('rdid','读者证号');
        $grid->column('created_at','领取时间')->display(function($time){
            return date('Y-m-d H:i:s', $time);
        });
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Member);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->tab('基本信息', function ($form) {
            if(request()->route()->getActionMethod() == 'edit'){
                $form->text('brand_name', '商户名称')->default(session('wxname'))->rules('required|max:12')->readonly()->help('必填，字数上限为12个汉字，不支持更新，请谨慎填写');
            }else{
                $form->text('brand_name', '商户名称')->default(session('wxname'))->rules('required|max:12')->help('必填，字数上限为12个汉字，不支持更新，请谨慎填写');
            }
            $form->image('logo_url', '商户Logo')->rules('mimes:jpg,png,jpeg|max:200')->disk('uploads')->move(materialUrl().'/membercard')->uniqueName()->help('必填，建议像素为300*300')->attribute('hideMaterial');
            $form->radio('card_cover', '卡券封面')->options(['1' => '图片', '0'=> '纯色'])->default('0');
            $colors = [
                'Color010' => '#63b359', 'Color020' => '#2c9f67', 'Color030' => '#509fc9', 'Color040' => '#5885cf', 'Color050' => '#9062c0',
                'Color060' => '#d09a45', 'Color070' => '#e4b138', 'Color080' => '#ee903c', 'Color081' => '#f08500', 'Color082' => '#a9d92d',
                'Color090' => '#dd6549', 'Color100' => '#cc463d', 'Color101' => '#cf3e36', 'Color102' => '#5E6671', 'Color103' => '#026842'
            ];
            $form->select('color', '背景颜色')->options($colors)->default('Color010');
            $form->image('background_pic_url', '背景图片')->rules('mimes:jpg,png,jpeg|max:1024')->disk('uploads')->move(materialUrl().'/membercard')->uniqueName()->help('建议像素为1000*600')->attribute('hideMaterial');
            $code_type = [
                'CODE_TYPE_BARCODE' => '一维码',
                'CODE_TYPE_QRCODE' => '二维码',
                'CODE_TYPE_NONE' => '不显示任何码类型'
            ];
            $form->select('code_type', '卡类型')->options($code_type)->default('CODE_TYPE_QRCODE');
            $form->text('title', '会员卡标题')->default('会员卡')->rules('required|max:9')->help('必填，字数上限为9个汉字');
            $form->embeds('date_info', '有效期', function ($form) {
                $form->radio('type', '有效期')->options(['DATE_TYPE_FIX_TIME_RANGE' => '固定日期', 'DATE_TYPE_PERMANENT'=> '永久有效'])->default('DATE_TYPE_PERMANENT');
                $form->dateRange('begin_timestamp', 'end_timestamp', '请选择时间');
            });
            $form->radio('auto_activate', '激活方式')->options(['1' => '自动激活', '2'=> '跳转外链激活', '3'=>'跳转小程序激活'])->default(1);
            $form->url('activate_url', '外链地址');
            $form->text('activate_app_brand_user_name', '小程序')->help('激活跳转的小程序原始id+@app，示例 gh_86a091e50ad4@app');
            $form->text('activate_app_brand_pass', '小程序页面path');
        });

        $form->tab('会员卡详情', function ($form) {
            $form->embeds('sku', '商品信息', function ($form) {
                $form->number('quantity', '库存（总）')->min(100000)->max(100000000)->default(100000)->rules('required')->help('必填，最大数量100000000');
            });
            $form->number('get_limit', '限领 /人')->min(1)->max(1)->default(1)->rules('required')->help('必填，建议会员卡每人限领一张');
            $form->text('notice', '使用提醒')->default('使用时请出示此读者卡')->rules('required')->help('卡券使用提醒，字数上限为16个汉字');
            $form->textarea('description', '使用说明')->default('使用说明')->rules('required')->help('必填，字数上限为1024个汉字');
            $form->textarea('prerogative', '特权说明')->default('特权说明')->rules('required')->help('必填，字数上限为1024个汉字');
        });

        $form->tab('商户介绍', function ($form) {
            $form->mobile('service_phone', '客服电话');
        });

        $form->tab('自定义入口', function ($form) {
            $form->text('center_title', '中部按钮标题')->help('卡券中部居中的按钮，仅在卡券激活后且可用状态 时显示，字数上限6个汉字');
            $form->radio('center_type', '跳转方式')->options(['web_url' => 'url外接', 'mini_program'=> '小程序']);
            $form->url('center_url', '外链地址');
            $form->text('center_app_brand_user_name', '小程序')->help('卡券跳转的小程序原始id+@app，示例 gh_86a091e50ad4@app');
            $form->text('center_app_brand_pass', '小程序页面path');

            $form->divider();
            $form->text('custom_url_name', '自定义入口1标题')->help('卡券中部居中的按钮，仅在卡券激活后且可用状态时显示，字数上限6个汉字');
            $form->text('custom_url_sub_title', '提示语')->help('字数上限6个汉字');
            $form->radio('custom_type', '跳转方式')->options(['web_url' => 'url外接', 'mini_program'=> '小程序']);
            $form->url('custom_url', '外链地址');
            $form->text('custom_app_brand_user_name', '小程序')->help('卡券跳转的小程序原始id+@app，示例 gh_86a091e50ad4@app');
            $form->text('custom_app_brand_pass', '小程序页面path');

            $form->divider();
            $form->text('promotion_url_name', '自定义入口2标题')->help('卡券中部居中的按钮，仅在卡券激活后且可用状态时显示，字数上限6个汉字');
            $form->text('promotion_url_sub_title', '提示语')->help('字数上限6个汉字');
            $form->radio('promotion_type', '跳转方式')->options(['web_url' => 'url外接', 'mini_program'=> '小程序']);
            $form->url('promotion_url', '外链地址');
            $form->text('promotion_app_brand_user_name', '小程序')->help('卡券跳转的小程序原始id+@app，示例 gh_86a091e50ad4@app');
            $form->text('promotion_app_brand_pass', '小程序页面path');
            $form->hidden('token')->default(session('wxtoken'));
            $form->hidden('card_id');
        });

        /* 素材库 上传图片 例子 start */
        $form->hidden(config('materialPR') . 'logo_url');
        $form->hidden(config('materialPR') . 'background_pic_url');
        $imgArray = [config('materialPR') . 'logo_url', config('materialPR') . 'background_pic_url'];
        $form->ignore($imgArray);
        /* 素材库 上传图片 例子 end */

        $form->submitted(function (Form $form) {
            $old_date_info = $form->model()->date_info;
            $old_sku = $form->model()->sku;
            $old_logUrl = $form->model()->logo_url;
            $old_background_pic_url = $form->model()->background_pic_url;
            request()->merge(['old_date_info'=>$old_date_info, 'old_sku'=>$old_sku, 'old_logUrl'=>$old_logUrl , 'old_background_pic_url'=>$old_background_pic_url]);
        });

        $form->saved(function (Form $form){
            $data = request()->input();
            $data['logo_url'] = $form->model()->logo_url;
            $data['background_pic_url'] = $form->model()->background_pic_url;

            if(Request::Method()=='POST'){
                $res = MemberCard::make(session('wxtoken'))->create('MEMBER_CARD', $data);
                if($res['errcode'] != 0){
                    Member::destroy($form->model()->id);
                    return back()->with(admin_warning('创建失败', 'errcode:'.$res['errcode']. "<br>" .$res['errmsg']));
                }else{
                    $model = $form->model();
                    $model->card_id = $res['card_id'];
                    $model->save();
                }
            }

            if(Request::Method()=='PUT'){
                $data['logo_url'] = $form->model()->logo_url;
                $data['background_pic_url'] = $form->model()->background_pic_url;
                $res = MemberCard::make(session('wxtoken'))->update($form->model()->card_id, 'MEMBER_CARD', $data);
                if($res['errcode'] !=0){
                    return back()->with(admin_warning('编辑失败', 'errcode:'.$res['errcode']. "<br>" .$res['errmsg']));
                }
            }

        });
        return $form;
    }

    public function getQrcode(Request $request)
    {
        $card_id = request()->input('card_id');
        $res = MemberCard::make(session('wxtoken'))->qrcode($card_id);
        return $res;
    }

    public function customJs()
    {
        return <<<SCRIPT
        //商户名称
        $('#brand_name').keyup(function(){
            $('#js_brand_name_preview').html(this.value)
        })
        $('#js_brand_name_preview').html($('#brand_name').val())
        
        //Logo
        $('.logo_url').on('fileloaded', function(event, file, previewId, index, reader) {
            $('#js_logo_url_preview').attr('src',reader.result)
        });
        if($('.logo_url').attr('data-initial-preview')){
            $('#js_logo_url_preview').attr('src',$('.logo_url').attr('data-initial-preview'))
        }
        
        //会员卡标题
        $('#title').keyup(function(){
            $('#js_title_preview').html(this.value)
        })
        $('#js_title_preview').html($('#title').val())
        
        //中间按钮
        $('#center_title').keyup(function(){
            $('.js_use_card_button').html(this.value)
        })
        
        //卡类型
        let changeCodeType = function(type){
            switch(type){
                case 'CODE_TYPE_BARCODE':
                    $('.qrcode').css('display','inline')
                    break;
                case 'CODE_TYPE_QRCODE':
                    $('.qrcode').css('display','inline')
                    break;
                case 'CODE_TYPE_NONE':
                    $('.qrcode').css('display','none')
                    break;
            }
        }
        $('select[name="code_type"]').change( function() {
            changeCodeType(this.value)
        });
        changeCodeType($('select[name="code_type"]').val())
        
        //背景颜色
        let colorObj =  {'Color010':'#63b359', 'Color020':'#2c9f67', 'Color030':'#509fc9', 'Color040':'#5885cf',  'Color050':'#9062c0',
                'Color060':'#d09a45', 'Color070':'#e4b138', 'Color080':'#ee903c', 'Color081':'#f08500', 'Color082':'#a9d92d', 'Color090':'#dd6549',
                'Color100':'#cc463d', 'Color101':'#cf3e36', 'Color102':'#5E6671', 'Color103':'#026842'  
            };
        let colorSection = $('input[name="color"]').parent('div');
        let colorHtml = '<span id="color" style="display:inline-block;width:33px;height:33px;border:1px solid #ccc;background-color:'+ $('select[name="color"] option:selected').html() +'"></span>';
        colorSection.after(colorHtml);
        
        let changeBackground = function(currObj,file){
            let color = $('#color').css('background-color');
            let bg_pic = $('input[name="background_pic_url"]').parents('div .form-group').children().find('img').eq(0).attr('src');     
            if(currObj == '1'){
                if(!bg_pic){
                    $('.js_color_bg_preview').css('background-image','').css('background-color',color)
                }else{
                    $('.js_color_bg_preview').css('background-color','').css('background-image','url('+bg_pic+')')
                }
            }
            else{
                $('.js_color_bg_preview').css('background-image','').css('background-color',color)
            }
        }
        
        $('select[name="color"]').change( function() {
            $('#color').css('background-color',colorObj[this.value])
            changeBackground($('input[name="card_cover"]:checked').val())
        });
        
        $('.background_pic_url').on('fileloaded', function(event, file, previewId, index, reader) {
            if($('input[name="card_cover"]:checked').val() == 1){
                $('.js_color_bg_preview').css('background-color','').css('background-image','url('+reader.result+')')
            }
        });
       
        $('input[name="card_cover"]').on('ifChecked', function(event){
            changeBackground($(this).val()) 
        }); 
        changeBackground($('input[name="card_cover"]:checked').val())
        
        if($('.background_pic_url').attr('data-initial-preview') && $('input[name="card_cover"]:checked').val() == '1'){
            $('.js_color_bg_preview').css('background-color','').css('background-image','url('+$('.background_pic_url').attr('data-initial-preview')+')')
        }
        
        //有效日期
        let changeDateInfo = function(currObj){
            if(currObj == 'DATE_TYPE_PERMANENT'){
                $('input[name="date_info[begin_timestamp]"]').attr('disabled',true);
                $('input[name="date_info[end_timestamp]"]').attr('disabled',true);
            }
            else{
                $('input[name="date_info[begin_timestamp]"]').removeAttr('disabled');
                $('input[name="date_info[end_timestamp]"]').removeAttr('disabled');
            }
        }
        $('input[name="date_info[type]"]').on('ifChecked', function(event){
            changeDateInfo($(this).val()) 
        });
        changeDateInfo($('input[name="date_info[type]"]:checked').val())
       
       //激活方式
        let changeActivateType = function(currObj){
            if(currObj == '1'){
                $('#activate_url').parents('div .form-group').css('display','none');
                $('#activate_app_brand_user_name').parents('div .form-group').css('display','none');
                $('#activate_app_brand_pass').parents('div .form-group').css('display','none');
            }
            else if(currObj == '2'){
                $('#activate_url').parents('div .form-group').css('display','block');
                $('#activate_app_brand_user_name').parents('div .form-group').css('display','none');
                $('#activate_app_brand_pass').parents('div .form-group').css('display','none');
            }
            else{
                $('#activate_url').parents('div .form-group').css('display','none');
                $('#activate_app_brand_user_name').parents('div .form-group').css('display','block');
                $('#activate_app_brand_pass').parents('div .form-group').css('display','block');
            }
        }
        $('input[name="auto_activate"]').on('ifChecked', function(event){
            changeActivateType($(this).val()) 
        }); 
        changeActivateType($('input[name="auto_activate"]:checked').val())
         
        //自定义入口
        let changeUrl = function(prefix,currObj){
            if(currObj == 'web_url'){
                $('#'+prefix+'_url').parents('div .form-group').css('display','block');
                $('#'+prefix+'_app_brand_user_name').parents('div .form-group').css('display','none');
                $('#'+prefix+'_app_brand_pass').parents('div .form-group').css('display','none');
            }
            else if(currObj =='mini_program'){
                $('#'+prefix+'_url').parents('div .form-group').css('display','none');
                $('#'+prefix+'_app_brand_user_name').parents('div .form-group').css('display','block');
                $('#'+prefix+'_app_brand_pass').parents('div .form-group').css('display','block');
            }
            else{
                $('#'+prefix+'_url').parents('div .form-group').css('display','none');
                $('#'+prefix+'_app_brand_user_name').parents('div .form-group').css('display','none');
                $('#'+prefix+'_app_brand_pass').parents('div .form-group').css('display','none');
            }
        }
        $('input[name="center_type"]').on('ifChecked', function(event){
            changeUrl('center',$(this).val())
        });
        $('input[name="custom_type"]').on('ifChecked', function(event){
            changeUrl('custom',$(this).val())
        });
        $('input[name="promotion_type"]').on('ifChecked', function(event){
            changeUrl('promotion',$(this).val())
        });
        changeUrl('center',$('input[name="center_type"]:checked').val())
        changeUrl('custom',$('input[name="custom_type"]:checked').val());
        changeUrl('promotion',$('input[name="promotion_type"]:checked').val());
SCRIPT;
    }

    public function customJsInList()
    {
        $action = route('membercard.qrcode');
        return <<<SCRIPT
        $('.qrcode').on('click', function () {
                var card_id = this.dataset.value
                swal({
                    title: "获取二维码?",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "确认",
                    showLoaderOnConfirm: true,
                    cancelButtonText: "取消",
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                method: 'get',
                                url: '{$action}',
                                data: {
                                    card_id:card_id,
                                    _token:LA.token,
                                },
                                success: function (data) {
                                    swal.close();
                                    swal({ 
                                      text: "扫码领取！",
                                      imageUrl: data.show_qrcode_url
                                    });
                                },
                                error:function(){
                                    swal.close();
                                    swal({ 
                                        type: "error",
                                        title: "获取失败！",
                                    });
                                }
                            });
                        });
                    }
                }).then(function(result) {
                   
                });
        });
SCRIPT;


    }
}
