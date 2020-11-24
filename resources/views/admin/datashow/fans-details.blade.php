<!-- Profile Image -->
<div class="box box-primary">
    <div class="box-body box-profile">
        <img class="profile-user-img img-responsive img-circle" src="{{$info['headimgurl']}}" alt="User picture">
        <h3 class="profile-username text-center">{{$info['nickname']}}
            @switch($info['sex'])
                @case(1)
                <i class="fa fa-male text-light-blue"></i>
                @break
                @case(2)
                <i class="fa  fa-female text-yellow"></i>
                @break
                @default
            @endswitch
        </h3>
        <p class="text-muted text-center">{{$info['openid']}}</p>
        <ul class="list-group list-group-unbordered">
            <li class="list-group-item">
                <b>关注时间</b> <a class="pull-right">{{$info['subscribe_time']}}</a>
            </li>
            <li class="list-group-item">
                <b>来自</b> <a class="pull-right">{{$info['country']}} {{$info['province']}} {{$info['city']}} </a>
            </li>
            <li class="list-group-item">
                <b>读者证</b> <a class="pull-right">{{$info['reader']['rdid'] ?? '未绑定'}}</a>
            </li>
            <li class="list-group-item">
                <b>绑定时间</b> <a class="pull-right">{{$info['reader']['created_at']}}</a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
{{--<div class="alert alert-success alert-dismissible">--}}
{{--    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>--}}
{{--    <h4><i class="icon fa fa-warning"></i> 注意!</h4>进来当前页面可能会出现一直加载无法使用的情况，只要"刷新"下页面即可正常使用!--}}
{{--</div>--}}
<!-- /.box -->

<!-- About Me Box -->
@empty(!$info->readerInfo)
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">读者信息</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <th style="width:50%">姓名</th>
                    <td>{{$info->readerInfo['rdname']}}</td>
                </tr>
                <tr>
                    <th>性别</th>
                    <td>{{$info->readerInfo['rdsex']}}</td>
                </tr>
                <tr>
                    <th>证状态</th>
                    <td>{{$info->readerInfo['rdcfstatename']}}</td>
                </tr>
                <tr>
                    <th>有效终止日期</th>
                    <td>{{$info->readerInfo['rdenddate']}}</td>
                </tr>
                <tr>
                    <th>身份证号码</th>
                    <td>{{$info->readerInfo['rdcertify']}}</td>
                </tr>
                <tr>
                    <th>联系方式</th>
                    <td>{{isset($info->readerInfo['rdloginid'])?$info->readerInfo['rdloginid']:''}}</td>
                </tr>
                <tr>
                    <th>开户馆名称</th>
                    <td>{{isset($info->readerInfo['rdlibname'])?$info->readerInfo['rdlibname']:''}}</td>
                </tr>
                <tr>
                    <th>读者馆际流通类型</th>
                    <td>{{isset($info->readerInfo['rdlibtype'])?$info->readerInfo['rdlibtype']:''}}</td>
                </tr>
                </tbody>
            </table>
        </div>
        {{--<!-- /.box-body -->--}}
    </div>
@endempty
<!-- /.box -->
