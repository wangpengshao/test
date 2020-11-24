<!-- Box Comment -->
<div class="box box-widget">
    <div class="box-header with-border">
        <div class="user-block">
            <img class="img-circle" src="{{$info['headimgurl']}}" alt="User Image">
            <span class="username"><a href="#">{{$info['nickname']}}</a></span>
            <span class="description">{{$info['openid']}}</span>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- post text -->
        <div class="tab-content">
            <div class="tab-pane active well" id="tab_1-1">
                <b>书单标题: {{$info['title']}} <span class="pull-right text-muted">{{$info['created_at']}}</span></b>
            </div>
        </div>
    </div>
</div>
<div class="box box-widget">
    <div class="box-body">
        <!-- post text -->
        <div class="tab-content">
            <div class="col-xs-12">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
{{--                        <tr>
                            <th style="width:20%">证号:</th>
                            <td>{{$info['rdid']}}</td>
                        </tr>
                        <tr>
                            <th>昵称:</th>
                            <td>{{$info['nickname']}}</td>
                        </tr>--}}
                        <tr>
                            <th>书单<br/>封面:</th>
                            <td>
                                <div class="timeline-item">
                                    <div class="timeline-body">
                                            <img class="img-responsive showBigImage"
                                                 style="border: 1px solid #cfcfcf;float: left;margin-right: 10px;"
                                                 width="60"
                                                 src="{{$info['image']}}" alt="Photo">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- /.box -->
