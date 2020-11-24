@extends('web.collectCard.app')
@section('bodyClass', 'bgLinear')
@section('content')
    <div class="bgPicSm bgPicSmLf"></div>
    <div class="bgPicSm bgPicSmRt"></div>
    <div class="ovFlow">
        <div class="stratHdTit shareCardTit"></div>
    </div>
    <div class="shareTip">
        <div class="shareTipImg"></div>
        <div class="shareBtn">我知道了</div>
    </div>
    <div class="stratConArea">
        <div class="stratModIn">
            <div class="stratTopIcon">
                <i class="stratTopLt"></i>
                <i class="stratTopRt"></i>
            </div>
            @empty(!$htmlConfig['fx1_info'])
                <div class="stratModCon">
                    <div class="ovFlow">
                        <div class="stratModDet">
                            <i class="stratNum">1</i><span>{{$htmlConfig['fx1_info']}}</span>
                        </div>
                    </div>
                </div>
            @endempty
            @empty(!$htmlConfig['fx2_info'])
                <div class="stratModCon stratModSec">
                    <div class="ovFlow">
                        <div class="stratModDet">
                            <i class="stratNum">2</i><span>{{$htmlConfig['fx2_info']}}</span>
                        </div>
                    </div>
                </div>
            @endempty
            <div class="disFlex shareMethArea">
                <div class="shareMethCon">
                    <a href="javascript:;" class="shareMethImg shareMethImg01"></a>
                    <a href="javascript:;" class="shareMethBtn">分享给朋友</a>
                </div>
                <div class="shareMethCon">
                    <a href="javascript:;" class="shareMethImg shareMethImg02"></a>
                    <a href="javascript:;" class="shareMethBtn">分享至朋友圈</a>
                </div>
            </div>
        </div>
    </div>
    <div class="stratConArea">
        <div class="stratModIn">
            <div class="myExpTit">
                <span>我的分享战绩</span>
            </div>
            <div class="myGetFn">
                <div class="myExpFnTit">已成功邀请{{$shareUser->count()}}位好友</div>
                <ul class="ovFlow friendUl">
                    @foreach($shareUser as $value)
                        <li class="friendLi"><img src="{{$value->hasOneFansInfo['headimgurl']}}" alt=""></li>
                    @endforeach
                </ul>
            </div>
            <div class="shareMyGetCard">
                <div class="myExpFnTit">共获得卡片</div>
                <ul class="ovFlow colCardUl shareRecord">

                    @foreach($showTask as $value)
                        @empty($value['image'])
                            <li class="colCardLi hasCard hasTap">
                                <div class="backImg" style="">
                                    @if($value['is_get']==1)
                                        <div class="backImgIn">
                                            <i class="quesCard"></i>
                                            <p>翻我啊</p>
                                        </div>
                                    @else
                                        <div class="unfinished" data-num="{{$value['requireNum']}}">
                                            <div class="unImg">
                                                <div class="unText" style="">待完成</div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </li>
                        @else
                            <li class="colCardLi hasCard hasTap">
                                <div class="frontImg">
                                    <img class="" src="{{$value['image']}}" alt="">
                                </div>
                            </li>
                        @endempty
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    {{--    领取card --}}
    <div class="popArea" id="showGet" style="display:none">
        <div class="mask "></div>
        <div class="popCon">
            <div class="singleCard">
                <div class="friSendTit">
                    <p id="taskText"></p>
                    <p class="friCardType" id="cardText"></p>
                </div>
            </div>
            <div class="cardOut">
                <div class="firSendImg">
                    <img id="cardImage" src="" alt=""/>
                </div>
            </div>
            <div class="cardBtnArea">
                <a href="#" id="cardUrl" class="shareMethBtn w178">查看</a>
            </div>
            <a href="javascript:;" class="closeBtn"></a>
        </div>
    </div>
@endsection

@section('jsResources')
    <script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" charset="utf-8">
        wx.config({!! $app->jssdk->buildConfig(['hideAllNonBaseMenuItem', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'showMenuItems'], false) !!});
        wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
            wx.hideAllNonBaseMenuItem();
            wx.showMenuItems({
                menuList: ['menuItem:share:appMessage', 'menuItem:share:timeline'] // 要显示的菜单项，所有menu项见附录3
            });
            {{--wx.updateAppMessageShareData({--}}
            {{--    title: "{{$config['title']}}", // 分享标题--}}
            {{--    desc: "{{$config['share_desc']}}", // 分享描述--}}
            {{--    link: "{!! $shareUrl !!}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致--}}
            {{--    imgUrl: "{!! $config['share_img'] !!}", // 分享图标--}}
            {{--    success: function () {--}}
            {{--        console.log('成功');--}}
            {{--    }--}}
            {{--});--}}
            {{--wx.updateTimelineShareData({--}}
            {{--    title: "{{$config['title']}}", // 分享标题--}}
            {{--    link: "{!! $shareUrl !!}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致--}}
            {{--    imgUrl: "{!! $config['share_img'] !!}", // 分享图标--}}
            {{--    success: function () {--}}
            {{--        // 设置成功--}}
            {{--    }--}}
            {{--})--}}
            wx.onMenuShareTimeline({
                title: "{{$config['title']}}", // 分享标题
                link: "{!! $shareUrl !!}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: "{!! $config['share_img'] !!}", // 分享图标
                success: function () {
                    // 用户点击了分享后执行的回调函数
                }
            })

            wx.onMenuShareAppMessage({
                title: "{{$config['title']}}", // 分享标题
                desc: "{{$config['share_desc']}}", // 分享描述
                link: "{!! $shareUrl !!}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: "{!! $config['share_img'] !!}", // 分享图标
                type: '', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                }
            });

        });
        $(function () {
            pushHistory();
        });

        function pushHistory() {
            window.addEventListener("popstate", function (e) {
                self.location.reload();
            }, false);
            let state = {title: "", url: "#"};
            window.history.replaceState(state, "", "#");
        };
    </script>
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script>
        $('.unfinished').click(function () {
            toast('需要邀请人数达到:' + $(this).data('num'));
        });

        const getUrl = "{!! $getUrl !!}";
        const csrf_token = "{{csrf_token()}}";

        $('.backImgIn').click(function () {
            showLoading('领取中...');
            $.ajax({
                type: 'POST',
                url: getUrl,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                },
                success: function (response) {
                    hideLoading();
                    if (response.status == false) {
                        toast({time: 4000, content: response.data.message})
                        return false;
                    }
                    $('#taskText').text(response.data.taskText);
                    $('#cardText').text(response.data.cardText);
                    $('#cardImage').attr('src', response.data.cardImage);
                    $('#cardUrl').attr('href', response.data.cardUrl);
                    $('#showGet').show();
                },
                error: function (e) {
                }
            });
        });
        $('.closeBtn').click(function () {
            location.reload();
        })
        $(function () {
            let a_id = "{{request()->input('a_id')}}";
            let shareTipKey = 'shareTipKey_' + a_id;
            let shareSwitch = window.localStorage.getItem(shareTipKey);
            if (shareSwitch == null){
                $('.shareTip').show();
            }
            $('.shareBtn').click(function () {
                $('.shareTip').hide();
                window.localStorage.setItem(shareTipKey,'1');
            })

        });


    </script>
@endsection



