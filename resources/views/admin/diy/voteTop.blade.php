<div class="row">
    <div class="col-md-2">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">分组列表</h3>
            </div>
            <div class="box-body no-padding">
                <ul class="nav nav-pills nav-stacked">
                    @foreach($groupList as $key => $val)
                        <li @if($key == $g_id)class="active"@endif >
                            <a href="{{route('vote.top', ['g_id' => $key])}}">
                                {{$val}}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <!-- /.box-body -->
        </div>
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">组详情</h3>
            </div>
            <div class="box-body no-padding">
                <ul class="nav nav-pills nav-stacked">
                    <li class="margin: 10px;">
                        <a>票数: {{$info['vote']}}</a>
                    </li>
                    <li class="margin: 10px;">
                        <a>浏览量: {{$info['view']}}</a>
                    </li>
                    <li class="margin: 10px;">
                        <a>作品数: {{$info['number']}}</a>
                    </li>
                </ul>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
    <!-- /.col -->
    <div class="col-md-10">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">排行榜</h3>
                <div class="pull-right">
                    <div class="btn-group">
                        <a href="{{route('vote.topExport',['g_id'=>$g_id])}}" target="_blank"
                           class="btn btn-sm btn-twitter" title="导出">
                            <i class="fa fa-download"></i>
                            <span class="hidden-xs"> 导出数据</span>
                        </a>
                    </div>
                    <!-- /.btn-group -->
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>编号</th>
                            <th>封面</th>
                            <th style="width: 18%">名称</th>
                            <th>查看数</th>
                            <th>投票数</th>
                            <th>名次</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($rankList as $key => $val)
                            <tr>
                                <td>{{ $val['number'] }}</td>
                                <td><img class="img img-thumbnail" src="{{ $val['cover'] }}"
                                         style="max-width:80px;max-height:80px"></td>
                                <td><strong>{{ $val['title'] }}</strong></td>
                                <td><a>{{ $val['views'] }}</a></td>
                                <td><a>{{ $val['votes'] }}</a></td>
                                <td><span class="label label-primary">{{ $val['rank'] }}</span></td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    <!-- /.table -->
                </div>
                <!-- /.mail-box-messages -->
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /. box -->
    </div>

</div>
