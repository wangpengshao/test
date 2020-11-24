@extends('web.collectCard.app')
@if($isFriend!=1)
    @section('bodyClass', 'bgLinear')
@endif
@section('content')
    <div class="shareTip">
        <div class="shareTipImg"></div>
        <div class="shareBtn">我知道了</div>
    </div>
    @if($isFriend==1)
        <div class="popArea" style="display:block" id="lastDiv">
            <div class="mask "></div>
            <div class="popCon">
                <div class="singleCard">
                    <div class="friSendTit">
                        <p><span class="friName">好友“ {{$giveUserInfo['nickname']}} ”</span>送您一张</p>
                        <p class="friCardType"> {{$card['text']}} </p>
                    </div>
                </div>
                <div class="cardOut">
                    <div class="firSendImg">
                        <img src="{{$card['image']}}" alt=""/>
                    </div>
                </div>
                <div class="cardBtnArea">
                    <a href="javascript:void(0);" class="shareMethBtn w178" id="getGiveCard">收下</a>
                </div>
                <a href="javascript:void(0);" class="closeBtn"></a>
            </div>
        </div>
    @else
        <div class="bgPicSm bgPicSmLf"></div>
        <div class="bgPicSm bgPicSmRt"></div>
        <div class="ovFlow">
            <div class="stratHdTit shareCardTit"></div>
        </div>
        <div class="stratConArea">
            <div class="stratModIn">
                <div class="stratTopIcon">
                    <i class="stratTopLt"></i>
                    <i class="stratTopRt"></i>
                </div>
                <div class="stratModCon">
                    <div class="ovFlow">
                        <div class="stratModDet">
                            <i class="stratNum">1</i><span>在当前页面点击右上角发 送给朋友 或 分享到朋友圈 即可</span>
                        </div>
                    </div>
                </div>
                <div class="firGetCd">
                    <div class="firGetCdImg"><img src="{{$card['image']}}" alt=""></div>
                    <div>
                        <p class="stratModDet">你要赠送是一张</p>
                        <p class="firCardName">{{$card['text']}}</p>
                    </div>
                </div>
                {{--<div class="disFlex shareMethArea">--}}
                {{--<div class="shareMethCon">--}}
                {{--<a href="javascript:;" class="shareMethImg shareMethImg01"></a>--}}
                {{--<a href="javascript:;" class="shareMethBtn">分享给朋友</a>--}}
                {{--</div>--}}
                {{--<div class="shareMethCon">--}}
                {{--<a href="javascript:;" class="shareMethImg shareMethImg02"></a>--}}
                {{--<a href="javascript:;" class="shareMethBtn">分享至朋友圈</a>--}}
                {{--</div>--}}
                {{--</div>--}}
            </div>
        </div>
    @endif

@endsection
@section('jsResources')
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
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
                title: "Hi,赠送你一张卡片!", // 分享标题
                link: window.location.href, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: "{!! $config['share_img'] !!}", // 分享图标
                success: function () {
                    // 用户点击了分享后执行的回调函数
                }
            })

            wx.onMenuShareAppMessage({
                title: "Hi,赠送你一张卡片!", // 分享标题
                desc: "{{$config['share_desc']}}", // 分享描述
                link: window.location.href, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: "{!! $config['share_img'] !!}", // 分享图标
                type: '', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                }
            });
        });

        const giveCard_id = "{{$giveCard_id}}";
        const giveUser_id = "{{$giveUser_id}}";
        const getGiveCardUrl = "{!! route('CollectCard::getGiveCard',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')]) !!}";
        const indexUrl = "{!!$indexUrl !!}";
        const csrf_token = "{{csrf_token()}}";

        $('#getGiveCard').click(function () {
            showLoading('领取中...');
            $.ajax({
                type: 'POST',
                url: getGiveCardUrl,
                dataType: "json",
                data: {"giveCard_id": giveCard_id, "giveUser_id": giveUser_id},
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                },
                success: function (response) {
                    hideLoading();
                    toast({time: 4000, content: response.message})
                    if (response.status == false) {
                        return false;
                    }
                    window.setTimeout(function () {
                        window.location.href = indexUrl;
                    }, 3000);
                },
                error: function (e) {
                    // alert('服务繁忙,请稍后再试！')
                }
            });
        })
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

        $(function () {
            let a_id = "{{request()->input('a_id')}}";
            let shareTipKey = 'shareCardTipKey_' + a_id;
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


