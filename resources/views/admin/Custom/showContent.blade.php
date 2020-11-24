<div class="row">
    <!-- /.col -->
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">View details</h3>

                <div class="box-tools pull-right">
                    <a href="{!! $nextUrl !!}" class="btn btn-box-tool" data-toggle="tooltip" title=""
                       data-original-title="Previous"><i class="fa fa-chevron-left"></i></a>
                    <a href="{!! $previousUrl !!}" class="btn btn-box-tool" data-toggle="tooltip" title=""
                       data-original-title="Next"><i class="fa fa-chevron-right"></i></a>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
                <div class="mailbox-read-info">
                    <h3 class="text-center">{{$detail['title']}}</h3>
                    <h5 class="mailbox-read-time">
                        From: 图创管理员
                        <span class=" pull-right">{{$detail['created_at']}}</span></h5>
                </div>
                <!-- /.mailbox-read-info -->
                @empty(!$detail['description'])
                    <div class="mailbox-controls with-border text-center">
                        <small><cite title="Source Title">{{$detail['description']}}</cite></small>
                    </div>
                @endempty
            <!-- /.mailbox-controls -->
                <div class="mailbox-read-message">
                    {!! $detail['content'] !!}
                </div>
                <!-- /.mailbox-read-message -->
            </div>

            <!-- /.box-footer -->
            <div class="box-footer">
                <div class="pull-right">
                    <span class="mailbox-read-time pull-right">最后编辑于 {{$detail['created_at']}} </span>
                </div>
            </div>
            <!-- /.box-footer -->
        </div>
        <!-- /. box -->
    </div>

    <div class="col-md-3">
        <a href="{{route('tcContent-home')}}" class="btn btn-primary btn-block margin-bottom">返回首页</a>

        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">列表</h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body no-padding">
                <ul class="nav nav-pills nav-stacked">
                    @foreach($list as $val)
                        <li><a href="{{route('tcContent-show', ['id' => $val['id']])}}">
                                <i class="fa fa-file-text-o"></i>{{$val['title']}}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
