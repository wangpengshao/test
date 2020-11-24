<div class="row">
    <!-- ./col -->
    <div class="col-md-12">
        <div class="box box-solid">
            <!-- /.box-header -->
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>馆代码</dt>
                    <dd>{{$data['rdlib']}}</dd>
                    <dt>读者类型</dt>
                    <dd>{{$data['rdtype']}}</dd>
                    <dt>操作员</dt>
                    <dd>{{$data['operator']}}</dd>
                    <dt>真实姓名</dt>
                    <dd>{{$data['rdname']}}</dd>
                    <dt>身份证号</dt>
                    <dd>{{$data['rdcertify']}}</dd>
                    @foreach($otherData as $k =>$v)
                        <dt>{{$otherDataOp[$k]}}</dt>
                        <dd>{{$v}}</dd>
                    @endforeach
                </dl>
            </div>
            <!-- /.box-body -->
        </div>
        <div class="timeline-item">
            相片:
            <div class="timeline-body">
                @forelse ($img as $url)
                    <div class="col-sm-4"><img class="img-responsive" src="{{$url}}" alt="Photo"></div>
                @empty
                    <p class="text-muted">抱歉,没有收集</p>
                @endforelse
            </div>
        </div>
        <!-- /.box -->
    </div>
    <!-- ./col -->
</div>
