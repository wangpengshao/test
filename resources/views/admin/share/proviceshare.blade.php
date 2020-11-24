<div class="row">
    <!-- /.col -->
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">View details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
                <div class="mailbox-read-info">
                    <h3 class="text-center">{{$article['title']}}</h3>
                    <h5 class="mailbox-read-time">
                        From: {{$author}}
                        <span class=" pull-right">{{$article['created_at']}}</span></h5>
                </div>
                <!-- /.mailbox-read-info -->
                @empty(!$article['description'])
                    <div class="mailbox-controls with-border text-center">
                        <small><cite title="Source Title">{{$article['description']}}</cite></small>
                    </div>
            @endempty
            <!-- /.mailbox-controls -->
                <div class="mailbox-read-message">
                    {!! $article['content'] !!}
                </div>
                <!-- /.mailbox-read-message -->
            </div>

            <!-- /.box-footer -->
            <div class="box-footer">
                <div class="pull-right">
                    <span class="mailbox-read-time pull-right">最后编辑于 {{$article['created_at']}} </span>
                </div>
            </div>
            <!-- /.box-footer -->
        </div>
        <!-- /. box -->
    </div>

    <div class="col-md-3">
        @if(empty($isStore) || $isStore->store_status !=1)
            <a data-uid="{{$article['id']}}" class="btn addPower btn-success btn-block margin-bottom">点击助力</a>
        @else
            <a data-uid="{{$article['id']}}" class="btn cancelPower btn-warning btn-block margin-bottom">取消助力</a>
        @endif
        <a href="{{$backUrl}}" class="btn btn-primary btn-block margin-bottom">返回列表</a>
    {{--        <div class="box box-solid">--}}
    {{--            <div class="box-header with-border">--}}
    {{--                <h3 class="box-title">列表</h3>--}}
    {{--                <div class="box-tools">--}}
    {{--                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>--}}
    {{--                    </button>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--            <div class="box-body no-padding">--}}
    {{--                <ul class="nav nav-pills nav-stacked">--}}
    {{--                    @foreach($list as $val)--}}
    {{--                        <li><a href="{{route('tcContent-show', ['id' => $val['id']])}}">--}}
    {{--                                <i class="fa fa-file-text-o"></i>{{$val['title']}}</a>--}}
    {{--                        </li>--}}
    {{--                    @endforeach--}}
    {{--                </ul>--}}
    {{--            </div>--}}
    {{--            <!-- /.box-body -->--}}
    {{--        </div>--}}
    <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
