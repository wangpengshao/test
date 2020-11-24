@extends('web.newVote.app')

@section('cssResources')
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/workDet.css'}}"/>
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/mescroll.min.css'}}"/>
    <link rel="stylesheet" href="{{asset('wechatWeb/vote/css/dropload.css')}}">
    <style>
        #mcover {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            z-index: 20000;
        }

        #mcover img {
            position: fixed;
            right: 18px;
            top: 5px;
            width: 260px !important;
            height: 180px !important;
            z-index: 20001;
        }
    </style>
@endsection

@section('content')
    <div class="inputShade" id="qrCode">
        <div class="inner">
            <img id="wordsCancel"
                 src="{{asset('wechatWeb/LuckyDraw/common/image/redPack/close2.png')}}"
                 alt="">
            <span class="input_title">长按识别关注公众号</span>
            <div class="toInput ewrShow" id="promptsText">
                <img src="{{$qrCode}}" alt="公众号二维码">
            </div>
        </div>
    </div>
    <div class="workWriteMes">
        <div class="wrap workWriteIn">
            <div class="flLeft writeLeft">
                <div class="workNm">{{$details['title']}}</div>
                <div class="workHint">
                    <span class="workTm">{{substr($details['created_at'],0,10)}}</span>
                    <span class="workType">{{$groupInfo['title']}}</span>
                </div>
                <div class="workNumArea">
                    <div class="workNumIn colorRed"><i
                                class="numIcon rankNumIcon"></i><span>{{$details['ranking']['rank'] ? '第'.$details['ranking']['rank'].'名' : '未上榜'}}</span>
                    </div>
                    <div class="workNumIn"><i
                                class="numIcon likeNumIcon"></i><span>{{$details['voting_n'] ? $details['voting_n'] : 0}}</span>
                    </div>
                    <div class="workNumIn"><i class="numIcon lookNumIcon"></i><span>{{$details['view_n']}}</span></div>
                </div>
            </div>
            <div class="flRight writeRight">
                <div class="writeHead"><img src="{{$details['fans']['headimgurl']}}" height="103" width="102" alt=""/>
                </div>
                <div class="writeHint">
                    <p class="writeNm">{{$details['fans']['nickname']}}</p>
                    <p>编号：<span class="ftSize38">{{$details['number']}}</span></p>
                </div>
            </div>
        </div>
    </div>
    <div class="bgWite">
        <div class="wrap">
            <div class="workDetAreaIn">
                <div>
                    @empty(!$details['cover'])
                        <img class="pro" src="{{$details['cover']}}" alt="">
                    @endempty
                    <div class="de-info">
                        {{$details['info']}}
                    </div>
                    @empty(!$details['content'])
                        @foreach($details['content'] as $key => $value)
                            @if($fields[$key]['show_sw'] != 1)
                                @continue
                            @endif
                            @if($fields[$key]['type'] == 0)
                                <div class="de-info">{{$value}}</div>
                            @elseif($fields[$key]['type'] == 4 && is_array($value))
                                @foreach($value as $val)
                                    <div class="pics"><img class="pro"
                                                           src="{{Storage::disk(config('admin.upload.disk'))->url($val)}}"
                                                           alt=""></div>
                                @endforeach
                            @elseif($fields[$key]['type'] == 4)
                                <div class="pics"><img class="pro"
                                                       src="{{Storage::disk(config('admin.upload.disk'))->url($value)}}"
                                                       alt=""></div>
                            @endif
                        @endforeach
                    @endempty
                </div>
                <a href="javascript:;" class="voteArea">
                    <div class="voteAreaIn">
                        <div><i class="voteHeart"></i><span class="ftSize22">投票</span></div>
                        <div class="ftSize18">{{$details['voting_n']}}</div>
                    </div>
                </a>
                <a href="javascript:;" class="flRight shareArea">
                    <div class="shareAreaIn" onclick="$('#mcover').show()">
                        @if($fansInfo['openid']==$details['openid'])
                            <i class="shareIcon"></i>为自己拉票
                        @else
                            <i class="shareIcon"></i>帮TA拉票
                        @endif
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div id="mcover" class="guide-close" onClick="$(this).hide()">
        <img src="{{asset('wechatWeb/vote/img/guide.png')}}"/>
    </div>
    @if($config['comment_sw'] == 1 || $config['comment_sw'] == 2)
        <div class="bgWite">
            <div class="wrap">
                <div class="leaveMesArea">
                    <div class="leaveMesTit">
                        <div class="leaveMesTitIn">留言区</div>
                    </div>
                    <div class="lveMesInpArea">
                        <form action="" class="lveMesFm" method="post">
                            <div class="lveMesLine">
                                <input type="text" id="comContent" placeholder="评论 . . ." maxlength="60"/>
                            </div>
                            <div class="sendMesLine"><input type="button" id="comment" value="评论"/></div>
                            <span style="font-size: .2rem;color: #888;"><i id="in">60</i>字</span>
                        </form>
                    </div>
                    <div class="hisMesArea">
                        <div class="hisMesAreaIn">
                            <p class="allMesTit">全部评论<i class="mesNum"></i></p>
                            <div class="hisMesCon mescroll" id="mescroll">
                                <div id="comments"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('jsResources')
    <script type="text/javascript" src="{{$templatePath.'/js/index.js'}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/search.js'}}"></script>
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/mescroll.min.js'}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/dropload.js')}}"></script>
    <script>
        $("#comContent").on("input propertychange", function () {
            var nMax = 60;
            var textDom = document.getElementById("comContent");
            var len = textDom.value.length;
            if (len > nMax) {
                textDom.value = textDom.value.substring(0, nMax);
                return;
            }
            document.getElementById("in").innerText = (nMax - len);
        });


        $(function () {
            var mescroll = new MeScroll("body", {
                down: {
                    isLock: false  // 锁定下拉功能
                },
                up: {
                    callback: upCallback, //上拉加载的回调
                    page: {
                        num: 0, //当前页 默认0,回调之前会加1; 即callback(page)会从1开始
                        size: 5 //每页数据条数,默认10
                    },
                    htmlNodata: '',
                    noMoreSize: 1,
                    isBounce: false, //此处禁止ios回弹,
                    hardwareClass: "mescroll-hardware",
                    empty: {
                        warpId: 'mescroll', //父布局的id; 如果此项有值,将不使用clearEmptyId的值;
                        icon: null, //图标,默认null
                        tip: "暂无相关数据~", //提示
                        btntext: "", //按钮,默认""
                        btnClick: null, //点击按钮的回调,默认null
                    },
                    loadFull: {
                        delay: 800
                    },
                    scrollbar: {
                        use: true,
                        barClass: "mescroll-bar"
                    }
                }
            });

            //下拉刷新的回调
            function downCallback() {

            }

            //上拉加载的回调
            function upCallback(page) {
                console.log('上拉加载...')
                let ajaxGetCommentUrl = "{!!$urlArr['ajaxGetCommentUrl']!!}";
                $.ajax({
                    url: ajaxGetCommentUrl + '&t_id=' + t_id + '&page=' + page.num,
                    dataType: 'json',
                    type: 'GET',
                    success: function (res) {
                        let html = '';
                        let dataLength = res.data.length;
                        let hasNext = res.next_page_url ? true : false;
                        if (dataLength > 0) {
                            $.each(res.data, function (index, item) {
                                html += PJHTMLren(item);
                            })
                        }
                        if (res.next_page_url === null || dataLength === 0) {
                            mescroll.lockUpScroll(true);
                        }
                        mescroll.endSuccess(dataLength, hasNext);
                        if (html) {
                            $('#comments').append(html);
                        }
                    },
                    error: function (e) {
                        mescroll.endErr();
                    }
                });
            }

            function PJHTMLren(item) {
                let defaultImg = "{{asset('wechatWeb/vote/template9/images/perIcon.png')}}";
                let HTMLBank = `<div class="hisMesGroup">
                                <div class="hisMesHead"><img src="${item.fans ? item.fans.headimgurl : defaultImg}" alt="" /></div>
                                <div class="hisMesIn">
                                    <div><span class="hisMesNm">${item.fans ? item.fans.nickname : '佚名'}</span><span class="hisMesTm">${item.create_at}</span></div>
                                    <div class="hisMesDet">${item.content}</div>
                                </div>
                            </div>`;

                return HTMLBank
            }

            const subscribe = "{{$fansInfo['subscribe']}}";
            const t_id = "{{$details['id']}}";
            const g_id = "{{$details['g_id']}}";
            //提交评论
            $('#comment').click(function () {
                if (subscribe !== "1") {
                    toast('请先关注公众号才可进行评论！');
                    showQrCode();
                    return false;
                }

                let content = $('#comContent').val();
                if (content == '' || content.replace(/(^\s*)|(\s*$)/g, "").length <= 0) {
                    layer.open({content: '随便写点什么吧...', skin: 'msg', time: 3})
                    return false;
                }
                let loading = layer.open({type: 2, content: '稍等，正在提交留言 . . .', shadeClose: false});
                $.ajax({
                    url: "{!! $urlArr['ajaxCommentUrl'] !!}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'post',
                    dataType: 'json',
                    data: {'text': content, 'g_id': g_id, 't_id': t_id},
                    success: function (res) {
                        setTimeout(function () {
                            layer.close(loading);
                            layer.open({content: res.message, skin: 'msg', time: 3});
                        }, 1500);
                        $('#comContent').val('');
                        if (res.status == true) {
                            setTimeout(function () {
                                window.location.reload();
                            }, 2500);
                        }
                    },
                    error: function () {
                        layer.close(loading);
                        $('#comContent').val('');
                    }
                })

            })

            //投票
            $('.voteAreaIn').click(function (e) {
                if (subscribe !== "1") {
                    toast('请先关注公众号才可进行评论！');
                    showQrCode();
                    return false;
                }
                let token = "{weimicms:$_GET['token']}";
                let id = "{weimicms:$_GET['id']}";
                let zid = "{weimicms:$_GET['zid']}";

                e.preventDefault();
                let self = $(e.target).closest('.vote');
                let loading2 = layer.open({
                    type: 2,
                    content: '投票数据提交中，请等待',
                    shadeClose: false,
                    shade: 'background-color: rgba(0,0,0,.3)'
                });
                $.ajax({
                    type: "POST",
                    url: "{!! $urlArr['ajaxVoteUrl'] !!}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    data: {
                        t_id: t_id, g_id: g_id,
                    },
                    success: function (data) {
                        setTimeout(function () {
                            layer.close(loading2);
                            layer.open({
                                content: data.message
                                , skin: 'msg'
                                , time: 3
                            });
                        }, 1500);
                        if (data.status == true) {
                            setTimeout(function () {
                                window.location.reload();
                            }, 2500);
                        }
                    }
                });

            })

        })
    </script>
@endsection
