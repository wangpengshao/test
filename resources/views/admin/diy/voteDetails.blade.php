<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">详细</h3>
                <div class="box-tools">
                    {{--<div class="btn-group pull-right" style="margin-right: 5px">--}}
                        {{--<a href="javascript:void(0);" class="btn btn-sm btn-danger 5d63eee4d6e26-delete" title="删除">--}}
                            {{--<i class="fa fa-trash"></i><span class="hidden-xs">  删除</span>--}}
                        {{--</a>--}}
                    {{--</div>--}}
                    {{--<div class="btn-group pull-right" style="margin-right: 5px">--}}
                        {{--<a href="/admin/wechat/vote/items/141/edit" class="btn btn-sm btn-primary" title="编辑">--}}
                            {{--<i class="fa fa-edit"></i><span class="hidden-xs"> 编辑</span>--}}
                        {{--</a>--}}
                    {{--</div>--}}
                    <div class="btn-group pull-right" style="margin-right: 5px">
                        <a href="{{route('items.index')}}" class="btn btn-sm btn-default" title="列表">
                            <i class="fa fa-list"></i><span class="hidden-xs"> 列表</span>
                        </a>
                    </div>
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="form-horizontal">
                <div class="box-body">
                    <div class="fields-group">
                        <div class="form-group ">
                            <label class="col-sm-2 control-label">标题</label>
                            <div class="col-sm-8">
                                <div class="box box-solid box-default no-margin box-show">
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                        {{$vote['title']}}&nbsp;
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label class="col-sm-2 control-label">联系电话</label>
                            <div class="col-sm-8">
                                <div class="box box-solid box-default no-margin box-show">
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                        {{$vote['phone']}}&nbsp;
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label class="col-sm-2 control-label">封面</label>
                            <div class="col-sm-8">
                                <div class="box box-solid box-default no-margin box-show">
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                        <img src="{{$vote['cover']}}" style="max-width:200px;max-height:200px;margin: 5px;border: 1px solid #eee;" class="img">&nbsp;
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label class="col-sm-2 control-label">简介</label>
                            <div class="col-sm-8">
                                <div class="box box-solid box-default no-margin box-show">
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                        {{$vote['info']}}
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>

                        @foreach($vote['content'] as $key=>$value)
                            <div class="form-group ">
                                <label class="col-sm-2 control-label">{{$fields[$key]['name']}}</label>
                                <div class="col-sm-8">
                                    <div class="box box-solid @if($fields[$key]['type'] == 0) box-default @elseif($fields[$key]['type'] == 4)box-default @endif no-margin box-show">
                                        <!-- /.box-header -->
                                        <div class="box-body">
                                            @switch($fields[$key]['type'])
                                                @case(0)
                                                    {{$value}}
                                                    @break
                                                @case(1)
                                                    @foreach($fields[$key]['options'] as $k=>$val)
                                                    <span class="icheck">
                                                        <label class="radio-inline">
                                                            <div class="iradio_minimal-blue @if($value == $k)checked @endif" aria-checked="false" aria-disabled="false" style="position: relative;"><input type="radio" name="field{{$key}}" value="1" class="minimal content_39" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins></div>&nbsp;{{$val}}&nbsp;&nbsp;
                                                        </label>
                                                    </span>
                                                    @endforeach
                                                    @break
                                                @case(2)
                                                    @foreach($fields[$key]['options'] as $k=>$val)
                                                        <span class="icheck">
                                                            <label class="checkbox-inline">
                                                                <div class="icheckbox_minimal-blue @if(in_array($k,$value))checked @endif" aria-checked="false" aria-disabled="false" style="position: relative;"><input type="checkbox" name="field{{$key}}" value="2" class="content_42" data-value="" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins></div>&nbsp;{{$val}}&nbsp;&nbsp;
                                                            </label>
                                                        </span>
                                                    @endforeach
                                                    @break
                                                @case(4)
                                                    @if(is_array($value) && count($value)>1)
                                                            <!-- /.box-header -->
                                                                <div id="carousel-5d63f883c0b98" class="carousel slide" data-ride="carousel" width="400" height="250" style="padding: 5px;border: 1px solid #f4f4f4;background-color:white;width:300px;">
                                                                    <ol class="carousel-indicators">
                                                                        @foreach($value as $val)
                                                                        <li data-target="#carousel-5d63f883c0b98" data-slide-to="{{$loop->index}}" class="@if($loop->index == 0) active @endif"></li>
                                                                        @endforeach
                                                                    </ol>
                                                                    <div class="carousel-inner">
                                                                        @foreach($value as $val)
                                                                            <div class="item @if($loop->index == 0) active @endif">
                                                                                <img src="{{Storage::disk(config('admin.upload.disk'))->url($val)}}" alt="" style="max-width:300px;max-height:200px;display: block;margin-left: auto;margin-right: auto;">
                                                                                <div class="carousel-caption">
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                    <a class="left carousel-control" href="#carousel-5d63f883c0b98" data-slide="prev">
                                                                        <span class="fa fa-angle-left"></span>
                                                                    </a>
                                                                    <a class="right carousel-control" href="#carousel-5d63f883c0b98" data-slide="next">
                                                                        <span class="fa fa-angle-right"></span>
                                                                    </a>
                                                                </div>
                                        </div><!-- /.box-body -->

                                                    @else
                                                    <img src="{{Storage::disk(config('admin.upload.disk'))->url($value[0])}}" style="max-width:200px;max-height:200px;margin: 5px;border: 1px solid #eee;" class="img">&nbsp;
                                                    @endif
                                                    @break
                                            @endswitch
                                        </div><!-- /.box-body -->
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>