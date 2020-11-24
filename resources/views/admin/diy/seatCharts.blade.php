<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">座位属性</h3>
                <div class="box-tools pull-right">
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <span>
                     <a class="btn btn-sm btn-primary grid-refresh" title="刷新"><i class="fa fa-refresh"></i><span class="hidden-xs"> 刷新</span></a>
                </span>
                <span>
                     <a class="btn btn-sm btn-primary" title="导出二维码" target="_blank" href="{!! route('seat.downloadQrcode',['token'=>session('wxtoken') ,'region'=>request()->route('id')])!!}"><i class="fa fa-qrcode"></i><span class="hidden-xs"> 导出二维码</span></a>
                </span>
                <span>
                     <a class="btn btn-sm btn-default" title="返回" href="{{url('admin/wechat/seat/seatRegion')}}"><span class="hidden-xs"> 返回</span></a>
                </span>
                @foreach($allAttrs as $attr)
                <div class="btn-group pull-right addAttr" style="margin-right: 10px">
                    <a href="javascript:void(0)" class="btn btn-sm btn-success" title="{{$attr->name}}"  onclick="addAttr({{$attr->id}})">
                        <i class="fa fa-plus"></i><span class="hidden-xs" >&nbsp;&nbsp;{{$attr->name}}</span>
                    </a>
                </div>
                @endforeach
            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">座位列表</h3>
                <div class="box-tools pull-right">
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="checkbox icheck">
                <label class="checkbox-inline" style="padding: 7px;">
                    <input type="checkbox" name="option" value="1" class="check-all"/>&nbsp;全部&nbsp;&nbsp;
                </label>
                <hr style="margin-top: 10px;margin-bottom: 0;">
                @foreach($allCharts as $chart)
                <div style="display: inline-block" class="seat-chart">
                    <label class="checkbox-inline" style="padding: 7px;">
                        <input type="checkbox" name="option[]" value="" data-id="{{$chart->id}}" class="chart"  />&nbsp;{{$chart->numid}}号座&nbsp;&nbsp;
                    </label>
                    <span class="select2 select2-container select2-container--default">
                        <span class="selection">
                            <span class="select2-selection select2-selection--multiple" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="-1">
                                <ul class="select2-selection__rendered">
                                    @foreach($chart->attr as $attr)
                                    <li class="select2-selection__choice" title="{{$attr->name}}"><span class="select2-selection__choice__remove" role="presentation" title="移除{{$attr->name}}" onclick="removeAttr({{$chart->id}},{{$attr->id}})">×</span>{{$attr->name}}</li>
                                    @endforeach
                                </ul>
                            </span>
                        </span>
                    </span>
                </div>
                @endforeach
                </div>

            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>
<style>
    .select2-container--default .select2-selection--multiple {
        border-color:#fff;
    }
    .seat-chart{
        position: relative;
        overflow: hidden;
        min-width: 24%;
        box-sizing: border-box;
    }
</style>
<script data-exec-on-popstate="">
    $(function () {
        $('.checkbox').iCheck({checkboxClass:'icheckbox_minimal-blue'});$('.check-all').iCheck({checkboxClass:'icheckbox_minimal-blue'}).on('ifChanged', function () {
            if (this.checked) {
                $('.checkbox').iCheck('check');
            } else {
                $('.checkbox').iCheck('uncheck');
            }
        })

        $('.grid-refresh').on('click', function() {
            $.pjax.reload('#pjax-container');
            toastr.success('刷新成功 !');
        });
    });

    let selectedRows = function () {
        var selected = [];
        $('.chart:checked').each(function(){
            selected.push($(this).data('id'));
        });
        return selected;
    }

    let addAttr = function(type){
        var ids = selectedRows().join();
        if(!ids.length){
            swal({type: 'warning', text: '请先选择座位……'})
            return;
        }
        swal({
            type: "question",
            text:"确定添加此属性吗？",
            showCancelButton: true,
            confirmButtonText: "确定",
            cancelButtonText: "取消",
            preConfirm: function() {
                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: "{{route('seat.chart.addAttr')}}",
                        type: "post",
                        dataType: "json",
                        data:{'type':type, 'ids':ids, _token: LA.token},
                        success: function (data) {
                            if(data.status==true){
                                swal({title:data.message,type:"success",showConfirmButton:false});
                                setTimeout(function(){
                                    window.location.reload();
                                }, 2000);
                            }else{
                                swal({title:data.message,type:"error",showConfirmButton:false});
                            }
                        },
                        error:function(){
                            swal("哎呦……", "出错了！","error");
                        }
                    });
                });
            },
        }).catch(swal.noop);
    }
    let removeAttr = function(chart,attr){
        swal({
            type: "question",
            text:"确定移除吗？",
            showCancelButton: true,
            confirmButtonText: "确定",
            cancelButtonText: "取消",
            preConfirm: function() {
                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: "{{route('seat.chart.removeAttr')}}",
                        type: "post",
                        dataType: "json",
                        data:{'id':chart, 'type':attr, _token: LA.token},
                        success: function (data) {
                            if(data.status==true){
                                swal({title:data.message,type:"success",showConfirmButton:false});
                                setTimeout(function(){
                                    window.location.reload();
                                }, 2000);
                            }else{
                                swal({title:data.message,type:"error",showConfirmButton:false});
                            }
                        },
                        error:function(){
                            swal("哎呦……", "出错了！","error");
                        }
                    });
                });
            },
        }).catch(swal.noop);
    }
</script>
