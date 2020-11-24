<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>阅读战"疫"</title>
    <script src="{{asset('wechatWeb/safeguard/js/resize.js')}}"></script>
    <link rel="stylesheet" href="{{asset('wechatWeb/safeguard/css/base.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/safeguard/css/index.css')}}">
    <script>
        (function () {
            let isToday = new Date().toDateString() === new Date("2020-04-04").toDateString();
            if (isToday !== true) {
                return true;
            }
            document.documentElement.setAttribute('style', 'filter: grayscale(100%);-webkit-filter: grayscale(100%);-moz-filter: grayscale(100%);-ms-filter: grayscale(100%);-o-filter: grayscale(100%);filter: progid:DXImageTransform.Microsoft.BasicImage(grayscale=1);filter: gray;-webkit-filter: grayscale(1);');
        })()
    </script>
</head>
<body>
<div id="app">
    <div class="header">
        <div class="addCollBtn">
            <i class="icon-coll"></i>
            <span>加入收藏</span>
        </div>
        {{--        <div class="title">防疫指南</div>--}}
    </div>
    <div class="main">
        <div class="right-icon">
            <img src="{{asset('wechatWeb/safeguard/imgs/icon-ta.png')}}" alt="">
        </div>
        <div class="title">
            <p class="title-text">疫情进行时,几本书助你用积极情绪应对危机</p>
            <p class="title-tips">共11本电子资源</p>
            <p class="title-explain">
                这场春节，一场突如其来的疫情，改变了所有人的生活。面对“2019-nCoV”病毒的来势汹汹，我们除了尽量不出门、戴口罩等“物理防御”的同时，也要进行“心理防疫”。在大量疫情新闻和信息的洪流中，我们挑选了一些书，希望阅读能为您带来一些慰藉与思考，帮助您正确面对疫情，科学调试心理，管理不良情绪。
            </p>
        </div>
        <!-- 书本列表 -->
        <div class="">
            <ul class="book-lists">
                <li class="book-item">
                    {{--                    山东人民 | 山东省疾病预防控制中心--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw5hmBmA7%2Bo3GlaUQiQ%2FNbbm8aGygsQ0usLqyFDR7vnqK2V7iCpdK6MaHynpv2KoicLu7SuOu9by8kgwvPE5awD3&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/01.png')}}" alt="">
                        <span class="book-name">新型冠状病毒感染的肺炎防控知识120</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    四川科技 | 四川新型冠状病毒肺炎疫情心理干预工作组--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw5FerT16RByHf2xItum1jKLp9rY6BdGJVtSsW8oDnOuWeT0tedwa%2FSrun1L5ZYckkd%2Bbeyh1HknQ80wNqZThtNN&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/02.png')}}" alt="">
                        <span class="book-name">新型冠状病毒大众心理防护手册</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    陕西人民 | 金发光--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw6nNujujkW%2BeJCi0YbE4doM6HnHTUZTKgjXH%2Bnplmvb5Duf%2BxuIFL4Z4rY%2BRloL9uhqT8boKU94dcu9881vYpkU&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/03.png')}}" alt="">
                        <span class="book-name">新型冠状病毒肺炎防护知识读本</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    江苏科技 | 吴超--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw6VGBTf%2B1H8FEezmo83KhRKZPjrCJJI0T2UGSJPuY7jn9%2F5FssqvauBHb9BJimVhvoT8e4vQM9ostCK73SLPZwp&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" class="book-img" src="{{asset('wechatWeb/safeguard/books/04.png')}}"
                             alt="">
                        <span class="book-name">新型冠状病毒感染的肺炎防护手册</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    北方文艺 | 黑龙江省卫生健康委员会--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw70re%2FcaBltXWcrwCIBrBNeDBE%2F%2FwrSuyfrryx2ZhhA0lxhBDeH0Ly94mrX%2BCjw8pOM0VJxnkdzGWvLYu47UiGb&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/05.png')}}" alt="">
                        <span class="book-name">新型冠状病毒预防绘本</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    中国中医药出版社 | 天津市委网信办--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw5mPP9G9m8K%2FbJyEacHjdkCulsmJXrY3UDRhM7n0BTlhYX4YPyFcLMTtxwKCe36CfNAyezpFpNXsAwsL4e%2B74WH&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/06.png')}}" alt="">
                        <span class="book-name">新型冠状病毒感染的肺炎防治知识问答</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    吉林科技 | 吉林省卫生健康委员会--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw5d18GGb%2ByPsAeeAt1bAdVOU%2B2makLoIs2CFgSlooWVV9QtpG4hGQTjiEfVAjwiYmGOg%2BxMDeahnj7gHjSVUWre&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/07.png')}}" alt="">
                        <span class="book-name">新型冠状病毒感染的肺炎预防手册：漫画版</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    吉林科技 | 尹永杰--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw5g3uHxRyhTEcu3cKbYmrQsvtsICm3bN3Gts4tidicWTkjDovzk44mKEqtOYEjs1KQZaMnLuUXYSFMLyzqF0oWK&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/08.png')}}" alt="">
                        <span class="book-name">病毒来了！：新型冠状病毒感染的肺炎预防知识绘本</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    湖南科技 | 湖南省疾病预防控制中心--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw7Vi4wv4f3FaHMYGDoMKmUqTj0xAqTsjsm%2FSlQuj6ucCyy5UQ1LzH4NZAFA4E%2BEq%2B6AUuuBnPLVMjHGlNctL1Cb&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/09.png')}}" alt="">
                        <span class="book-name">新型冠状病毒感染的肺炎防控知识问答</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    辽宁出版集团 | 辽宁省疾病预防控制中心--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw4AOZRSBjKJ1yhVWH4MiMtdBdPrf60UFqvl%2BSCUoXySNFtllITv%2FGaODxz2SHTgw6UURniorL225RwZt3gfV26V&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/10.png')}}" alt="">
                        <span class="book-name">预防新型冠状病毒肺炎宣传手册</span>
                    </a>
                </li>
                <li class="book-item">
                    {{--                    广东科技 | 广东省疾病预防控制中心--}}
                    <a class="bookDetail"
                       href="https://k12.zhangyue.com/zywap/read?usr=&rgt=&data=Fig7ZgzqMw45ckUbdbZF8Rq8fckju9u3Hr%2FKONqRIYinXm9PvLmwihYLbgX%2BKwM9rzj4MFjzQFZClnRyrDFpuQRgvBZkcxtv&cid=1&appId=0e5a5dcdf8e62158edc5757cbf67b662">
                        <img class="book-img" src="{{asset('wechatWeb/safeguard/books/11.png')}}" alt="">
                        <span class="book-name">新型冠状病毒感染防护</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- 书本列表 end -->
        <!-- 评论 -->
        <div class="commentBox">
            <div class="comment-field comment-field-btn">
                <div class="field-item itemL">
                    <img src="{{asset('wechatWeb/safeguard/imgs/icon-hand1.png')}}" alt="" srcset="">
                    <span>一起为武汉加油吧！</span>
                </div>
                <div class="field-item itemR">
                    <img src="{{asset('wechatWeb/safeguard/imgs/icon-pan.png')}}" alt="" srcset="">
                    <span>发评论</span>
                </div>
            </div>
            <div class="commentLists">
                <div class="comm-title">
                    <span>用户评论</span>
                </div>
                <div class="comm-body">
                    <!-- 评论列表 -->
                    <ul class="comm-lists"></ul>
                    <!-- 展开全部按钮 -->
                    <div class="showAllBtn">加载更多</div>
                    <div class="noData" style="display: none;text-align: center;color: #b1bdd7"> ~ 到底了 ~</div>
                </div>
            </div>
        </div>
        <!-- 评论 end -->
        <div class="footer">
            <div>
                <img src="{{asset('wechatWeb/safeguard/imgs/logo-c.png')}}" alt="" srcset="">
            </div>
            <div>
                <img src="{{asset('wechatWeb/safeguard/imgs/logo-z.png')}}" alt="" srcset="">
            </div>
        </div>
    </div>
    <!-- 返回顶部 -->
    <div class="backTop" style="display: none;">
        <img src="{{asset('wechatWeb/safeguard/imgs/icon-topback.png')}}" alt="" srcset="">
    </div>
    <!-- 输入评论 -->
    <div class="dialog comm-dialog" style="display: none;">
        <div class="comm-textarea">
            <div class="comm-input">
                <textarea class="" name="t" id="textarea" maxlength="100"></textarea>
                <span class="numTips">0/100</span>
            </div>
            <span class="sublim comm-act">发送</span>
        </div>
    </div>
    <!-- 输入评论 end -->
    <!-- 收藏提示 -->
    <div class="dialog collectBox" style="display: none;">
        <div class="collect-tips">
            <img src="{{asset('wechatWeb/safeguard/imgs/coll-tips.png')}}" alt="">
        </div>
    </div>
    <!-- 收藏提示 end -->
</div>
<script src="{{asset('wechatWeb/safeguard/js/jquery-3.4.1.min.js')}}"></script>
<script src="{{asset('wechatWeb/safeguard/layer_mobile/layer.js')}}"></script>
<script>
    let my_like = @json($myLike);
    let ajaxUrl = "{!! $ajaxUrl !!}";
    let saveCommentsUrl = "{!! $saveCommentsUrl !!}";
    let saveLikeUrl = "{!! $saveLikeUrl !!}";
    $(document).ready(function () {
        // 返回顶部按钮 显示/隐藏判断
        $(document).scroll(function () {
            var hidtop = $(document).scrollTop();
            if (hidtop > 100) {
                $(".backTop").fadeIn()
            } else {
                $(".backTop").fadeOut()
            }
        });
        // 点击写评论
        $(".comment-field-btn").on("click", function () {
            $(".comm-dialog").fadeIn(function () {
                $("#textarea").focus();
                $("html,body").css("overflow-y", "hidden")
            });
        });
        // 发送评论
        let saveComment_sw = true;
        $(".comm-act").on("click", function () {
            if (saveComment_sw !== true) {
                return false;
            }
            let value = $("#textarea").val();
            if (value.length == 0) {
                showMes('评论内容不能为空');
                return false;
            }
            saveComment_sw = false;
            let loading = layer.open({type: 2, content: '处理中', shadeClose: false});
            $.ajax({
                url: saveCommentsUrl,
                type: "post",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': "{{csrf_token()}}"
                },
                data: {
                    "content": value,
                },
                success: function (response) {
                    layer.close(loading)
                    if (response.status == false) {
                        showMes(response.message);
                        return false;
                    }
                    showMes('评论成功,等待审核中...', 4);
                    saveComment_sw = true;
                    // 发送成功后  隐藏写评论框
                    $(".comm-dialog").fadeOut(function () {
                        $(".comm-act").removeClass("active");
                        $(".numTips").text(`0/100`);
                        $("html,body").css("overflow-y", "auto");
                        $("#textarea").val('')
                    });
                },
                error: function () {
                }
            })

        });
        // 监听字符长度
        $("#textarea").bind("input", function () {
            let leng = $("#textarea").val().length;
            if (leng === 0) {
                $(".comm-act").removeClass("active");
                $(".numTips").text(`0/100`);
                return
            }
            $(".comm-act").addClass("active");
            if (leng <= 100) {
                if (leng == 100) {
                    $(".numTips").css("color", "rgba(255, 84, 81, 1)");
                } else {
                    $(".numTips").css("color", "rgba(153, 153, 153, 1)");
                }
                $(".numTips").text(`${leng}/100`);
                return
            }
        })
        // 返回顶部按钮
        $(".backTop").on("click", function () {
            $('html,body').animate({scrollTop: 0}, 500);
        });
        // 显示写评论时  点击空白隐藏
        $(".dialog").on("click", function (e) {
            var e = e || window.Event;
            var target = e.target;
            if (target.className.indexOf("comm-dialog") > -1) {
                $(".comm-dialog").fadeOut(function () {
                    $(".comm-act").removeClass("active");
                    $(".numTips").text(`0/100`);
                    $("html,body").css("overflow-y", "auto")
                });
            }
        });
        // 加入收藏
        $(".addCollBtn").on("click", function () {
            $(".collectBox").fadeIn();
        });
        $(".collectBox").on("click", function () {
            $(".collectBox").fadeOut();
        });

        let ajax_sw = true;

        function ajaxComments() {
            if (ajax_sw != true) {
                return false;
            }
            $('.showAllBtn').text('加载中..');
            ajax_sw = false;
            $.ajax({
                url: ajaxUrl,
                type: "get",
                dataType: "json",
                success: function (response) {
                    let dom = '';
                    let data = response.data;
                    ajaxUrl = response.next_page_url
                    if (data.length == 0 || ajaxUrl === null) {
                        noData();
                    }
                    if (data.length == 0) {
                        return false; //没有数据
                    }
                    $('.showAllBtn').text('加载更多');
                    $.each(data, function (e, item) {
                        let is_like = (my_like.indexOf(item.id + '') == -1) ? '' : ' active ';
                        dom += '  <li class="comm-item">' +
                            '                            <div class="user-img">' +
                            '                                <img src="' + item.headimgurl + '" alt="">' +
                            '                            </div>' +
                            '                            <div class="comm-main">' +
                            '                                <div class="user-name">' + item.nickname + '</div>' +
                            '                                <div class="user-comm">' +
                            '                                    <p>' + item.content + '</p>' +
                            '                                </div>' +
                            '                                <div class="comm-info">' +
                            '                                    <div class="comm-time ">' + item.created_at + '</div>' +
                            '                                    <div class="comm-praise ' + is_like + '" data-id="' + item.id + '">' +
                            '                                        <i class="icon-hand"></i>' +
                            '                                        <span class="praise-num">' + item.like_n + '</span>' +
                            '                                    </div>' +
                            '                                </div>' +
                            '                            </div>' +
                            '                        </li>';
                    });
                    $('.comm-lists').append(dom);
                    ajax_sw = true;
                },
                error: function () {
                    $('.showAllBtn').text('加载出错了!');
                }
            });

        }

        ajaxComments();
        $('.showAllBtn').click(function () {
            ajaxComments();
        });

        function noData() {
            $('.showAllBtn').hide();
            $('.noData').show();
        }

        function showMes(str, time = 2) {
            layer.open({
                content: str
                , skin: 'msg'
                , time: time //2秒后自动关闭
            });
        }

        //点赞
        let like_sw = true;
        $(document).on('click', '.comm-praise', function () {
            event.preventDefault();   // 阻止浏览器默认事件，重要
            if (like_sw !== true) {
                return false;
            }
            like_sw = false;
            let e = $(this);
            let praise_dom = e.children('.praise-num');
            let id = e.data('id');
            e.toggleClass("active");
            let praise_num = praise_dom.text();
            let is_like = e.hasClass("active");
            if (is_like) {
                praise_num++;
            } else {
                praise_num--;
            }
            praise_dom.text(praise_num);
            $.ajax({
                url: saveLikeUrl,
                type: "post",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': "{{csrf_token()}}"
                },
                data: {
                    "id": id,
                    "is_like": is_like
                },
                success: function (response) {
                    like_sw = true
                    console.log(response);
                },
                error: function () {
                }
            })
        });
    });
</script>

<script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js" type="text/javascript" charset="utf-8"></script>
<script>
    // <!-- JS-SDK -->
    wx.config({!! $app->jssdk->buildConfig([
    'hideAllNonBaseMenuItem',
     'updateAppMessageShareData',
      'updateTimelineShareData',
       'showMenuItems'
       ], false) !!});

    wx.ready(function () {
        wx.hideAllNonBaseMenuItem();
        wx.showMenuItems({
            menuList: ['menuItem:share:appMessage', 'menuItem:share:timeline', 'menuItem:favorite'] // 要显示的菜单项，所有menu项见附录3
        });

        let title = "阅读战“疫”";
        let desc = "疫情进行时,几本书助你用积极情绪应对危机";
        let imgUrl = "https://wechat-xin.oss-cn-shenzhen.aliyuncs.com/wechat/18c6684c/kangyi.jpeg";
        let link = window.location.href;
        wx.updateAppMessageShareData({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: link,
            imgUrl: imgUrl, // 分享图标
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });
        //分享给朋友
        wx.updateTimelineShareData({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: link,
            imgUrl: imgUrl, // 分享图标
            success: function () {
            },
            cancel: function () {
            }
        });
    });
</script>
{{--站长统计代码--}}
<div style="display: none" class="footer-copyright"></div>
<script type="text/javascript">
    $(function () {
        let cnzz = document.createElement("script");
        cnzz.src = "https://s9.cnzz.com/z_stat.php?id=1278605231"; //这里插入你的cnzz统计代码中的统计数据链接
        document.getElementsByClassName('footer-copyright')[0].appendChild(cnzz);
    });
</script>
</body>
</html>
