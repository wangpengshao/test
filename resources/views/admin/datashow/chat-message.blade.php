<style>
    .chart-room-user-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .chart-room-user-list > li {
        width: 23%;
        /*float: left;*/
        padding: 10px;
        text-align: center;
        position: relative;
        display: inline-block;
    }

    .chart-room-user-list > li img {
        border-radius: 50%;
        max-width: 100%;
        height: auto;
    }

    .direct-chat-warning .right > .direct-chat-text {
        background: #f39c12;
        border-color: #f39c12;
        color: #fff;
    }
</style>

{{--<div class="col-md-8">--}}
    <div class="box box-success direct-chat direct-chat-success">
        <div class="box-header with-border">
            <h3 class="box-title">消息窗</h3>
            <div class="box-tools pull-right">
                <span data-toggle="tooltip" title="当前窗口记录条数" class="badge bg-green">{{$history->count()}}</span>
                <span data-toggle="tooltip" title="刷新" class="badge bg-green" id="chatRefresh"><i
                            class="fa fa-refresh fa-spin"></i></span>
            </div>
        </div><!-- /.box-header -->
        <div class="box-body" style="height: 500px;">
            <!-- Conversations are loaded here -->
            <div class="direct-chat-messages" id="chat-messages" style="height: 100%;">
                <!-- Message. Default to the left -->
                @foreach($history as $k=>$v)
                    @if(!empty($v['r_reply']))
                        <div class="direct-chat-msg">
                            <div class="direct-chat-info clearfix">
                                <span class="direct-chat-name pull-left">{{$info['nickname']}}</span>
                                <span class="direct-chat-timestamp pull-right">{{$v['created_at']}}</span>
                            </div><!-- /.direct-chat-info -->
                            <img class="direct-chat-img" src="{{$info['headimgurl']}}"
                                 alt="message user image"><!-- /.direct-chat-img -->
                            <div class="direct-chat-text">
                                {{$v['r_reply']}}
                                @if($v['is_reading'] != 1)
                                    <span class="label label-danger" style="margin-left: 4px;">未读</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(!empty($v['a_reply']))
                        <div class="direct-chat-msg right">
                            <div class="direct-chat-info clearfix">
                                <span class="direct-chat-name pull-right">{{$admin_info['wxname']}}</span>
                                <span class="direct-chat-timestamp pull-left">{{$v['created_at']}}</span>
                            </div>
                            <!-- /.direct-chat-info -->
                            <img class="direct-chat-img" src="/uploads/images/long.png" alt="message user image">
                            <!-- /.direct-chat-img -->
                            <div class="direct-chat-text">
                                {{$v['a_reply']}}
                                @if($v['is_reading'] != 1)
                                    <span class="label label-danger" style="margin-left: 4px;">未读</span>
                                @endif
                            </div>
                            <!-- /.direct-chat-text -->
                        </div>
                    @endif
                @endforeach

            </div><!--/.direct-chat-messages-->
        </div>
        <div class="box-footer">
            <div class="progress progress-xxs active" id="stopCountdown">
                <div class="progress-bar progress-bar-primary progress-bar-striped" id="Countdown" role="progressbar"
                     style="width: 100%"></div>
            </div>
            <form action="#" method="post">
                <p class="lead emoji-picker-container">
            <textarea class="form-control textarea-control" rows="3" placeholder="请输入需要回复内容..."
                      data-emojiable="true" id="sendMesdata"></textarea>
                </p>
                <div class="form-group">

                    <div class="box-footer">
                        {{--                        <button type="button" id="sendFile" class="btn btn-default">图片</button>--}}
                        <button type="button" id="sendMes" class="btn btn-info pull-right">发送</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.box-footer-->
    </div>
{{--</div>--}}

<script type="text/javascript" charset="utf-8">
    // $(document).on('ready pjax:start', function (event) {
    // if (timing && seconds > 0) {
    //     seconds = 0;
    //     $('#Countdown').css('width', '0%');
    // window.clearTimeout(timing);
    // timing = null;
    // }
    // })
    // function animationInit() {
    //     seconds++;
    //     var witchStr = seconds * 2 + '%';
    //     $('#Countdown').css('width', witchStr);
    //     console.log(witchStr);
    //     if (seconds == 50) {
    //         $.pjax.reload('#pjax-container');
    //     }
    // }

    $(function () {
        var chatDIV = document.getElementById('chat-messages');
        chatDIV.scrollTop = chatDIV.scrollHeight;

        $('.showBigImage').click(function () {
            swal({
                imageUrl: $(this).attr('src'),
                animation: true
            })
        })

        $('#chatRefresh').click(function () {
            chatRefresh();
        });

        function chatRefresh() {
            $.pjax.reload('#pjax-container');
        }

        var posturl = "{{route('chat-message.send', ['mid'=>request()->input('m_id'),'sid'=>request()->input('s_id')])}}";
        console.log(posturl)
        $('#sendMes').click(function () {
            var sendMesdata = $.trim($('#sendMesdata').val());
            if (sendMesdata.length == 0) return false;
            $('#sendMes').attr('disabled', true);
            $('#sendFile').attr('disabled', true);
            $.ajax({
                url: posturl,
                type: "post",
                data: {_token: LA.token, data: sendMesdata}, dataType: "json",
                success: function (data) {
                    chatRefresh();
                    if (data.status == true) {
                        toastr.success(data.message, null, {timeOut: 2000});
                    } else {
                        toastr.error(data.message, null, {timeOut: 2000});
                    }
                },
                error: function () {
                    swal("哎呦……", "出错了！", "error");
                }
            });
        });

        // $('#stopCountdown').click(function () {
        //     if (timing) {
        //         window.clearTimeout(timing);
        //         timing = null;
        //         return false;
        //     }
        //     timing = window.setInterval(function () {
        //         animationInit();
        //     }, 1000);
        // })


    });


</script>

