<script language="javascript" type="/text/javascript">
    $('.accredit').on('click', function () {
                var id=$(this).attr('uid');
                var status=1;
                swal({
                    title: "点击助力操作",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "确认",
                    showLoaderOnConfirm: true,
                    cancelButtonText: "取消",
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                method: 'post',
                                url: '/admin/share/regionalShare/eddit/'+id+'/'+status,
                                data: {
                                    _token:LA.token,
                                },
                                success: function (data) {
                                    swal.close();
                                    swal({
                                      type:"success",
                                      text: "助力成功！"
                                    });
                                    window.location.href="/admin/share/regionalShare/2";
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
        $('.accredit-cancel').on('click', function () {
                var id=$(this).attr('uid');
                var status=0;
                swal({
                    title: "取消助力操作",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "确认",
                    showLoaderOnConfirm: true,
                    cancelButtonText: "取消",
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                method: 'post',
                                url: '/admin/share/regionalShare/eddit/'+id+'/'+status,
                                data: {
                                    _token:LA.token,
                                },
                                success: function (data) {
                                    swal.close();
                                    swal({
                                      type:"success",
                                      text: "取消成功！"
                                    });
                                    window.location.href="/admin/share/regionalShare/2";
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
</script>
<div id="app">
    <section class="content-header">
        <h1>
            文章
            <small>预览</small>
        </h1>
    </section>
    <section class="content">
        <div class="row"><div class="col-md-12"><div class="box box-info">
                    <form action="/admin/share/regionalShare/2" method="get" accept-charset="UTF-8" class="form-horizontal" pjax-container="">
                        @csrf
                        <div class="box-body">
                            <div class="fields-group">
                                <div class="tm-content-container">
                                    <div class="tm-content" style="text-align: center;">
                                        <h2 class="tm-page-title">{{$title}}</h2>
                                        {!! $content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <div class="tm-content-container">
                                <div style="text-align: center;">
                                    <td>
                                        @if(empty($user_articles_store))
                                            <button type="button" class="accredit btn btn-primary btn-xs active done"  uid="{{$id}}" status="{{$value['store_status']}}" style="padding: 6px 12px;">点击助力</button>
                                            <button type="submit" class="btn btn-primary" style="margin-left: 20px;">返回</button>
                                        @else
                                            @foreach($user_articles_store as $value)
                                                @if($value['store_status'] == 0)
                                                    <button type="button" class="accredit btn btn-primary btn-xs active done"  uid="{{$id}}" status="{{$value['store_status']}}" style="padding: 6px 12px;">点击助力</button>
                                                    <button type="submit" class="btn btn-primary" style="margin-left: 20px;">返回</button>
                                                @else
                                                    <button type="button" class="accredit-cancel btn btn-primary btn-xs active done"  uid="{{$id}}" status="{{$value['store_status']}}" style="padding: 6px 12px;">取消助力</button>
                                                    <button type="submit" class="btn btn-primary" style="margin-left: 20px;">返回</button>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div></div>
    </section>
</div>