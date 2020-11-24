@extends('web.collectCard.app')
@section('cssResources')
    <link rel="stylesheet" href="{{asset('wechatWeb/collectCard/css/swiper.min.css')}}">
@endsection
@section('bodyClass', 'bgWhite')
@section('content')
    <div class="bgPicSm bgPicSmLf"></div>
    <div class="bgPicSm bgPicSmRt"></div>
    <!-- Swiper -->
    <div class="swiper-container gallery-top">
        <div class="swiper-wrapper">
            @foreach($topList as $value)
                <div class="swiper-slide {{$value['is_have']? 'hasCard':''}}" data-hash="{{$value['log_id']}}">
                    <div class="cardImgArea">
                        <div class="sendCardArea">
                            @if($config['giving_sw']==1 && $value['is_expend'] != 1)
                                <div class="verLine"></div>
                                <a href="{{$value['shareUrl']}}" class="sendCardIn">赠卡</a>
                            @endif
                        </div>
                        <img src="{{$value['image']}}" alt="">
                        @if($value['type']==1 && $value['is_have']==1)
                            <div class="exchangeBtn" data-id="{{$value['log_id']}}">
                                <a href="javascript:void(0);" class="shareMethBtn w158">兑换</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="swiper-pagination topPageClass"></div>
    </div>
    <div class="swiper-container gallery-thumbs">
        <div class="swiper-wrapper">
            @foreach($card as $value)
                <div class="swiper-slide  {{array_get($myCard,$value['id']) ? 'hasCard':''}}">
                    <div class="cardImgArea"><img src="{{$value['image']}}" alt="">
                        @if(array_get($myCard,$value['id']))
                            <i class="circular">{{count($myCard[$value['id']])}}</i>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="popArea excPopArea">
        <div class="mask"></div>
        <div class="popCon excPopCon">
            <div class="popConIn choseExcCard">
                <div class="choseExcTit">请选择你要兑换的卡片</div>
                <ul class="disFlex colCardUl excCardUl">
                    @foreach($card as $value)
                        @if($value['type']===0)
                            <li class="colCardLi" data-cid="{{$value['id']}}" data-img="{{$value['image']}}">
                                <img src="{{$value['image']}}" alt="">
                            </li>
                        @endif
                    @endforeach
                </ul>
                <div class="cardBtnArea">
                    <a href="javascript:;" class="shareMethBtn confExcBtn notChose">确认兑换</a>
                </div>
            </div>
            <a href="javascript:;" class="closeBtn"></a>
        </div>
    </div>

    <div class="popArea excSucPopArea">
        <div class="mask"></div>
        <div class="popCon excPopCon">
            <div class="popConIn excSucIn">
                <div class="choseExcTit hasMvTit">兑换成功！</div>
                <div class="excResArea">
                    <div class="excResCon">
                        <div class="excPaPerBg"></div>
                        <div class="excResCard"><img id="exchangeImge" alt=""/>
                        </div>
                    </div>
                    <div class="excResBg"></div>
                </div>
                <div class="cardBtnArea">
                    <a href="javascript:;" class="shareMethBtn reloadBtn">知道了</a>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('jsResources')
    <script type="text/javascript" src="{{asset('wechatWeb/collectCard/js/swiper.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script>
        let cardIndex = @json($cardIndex);
        let galleryThumbs = new Swiper('.gallery-thumbs', {
            spaceBetween: 24,
            slidesPerView: 4.2,
            freeMode: true,
            on: {
                tap: function () {
                    if (typeof (galleryThumbs.clickedIndex) != "undefined") {
                        updateNavPosition();
                        galleryTop.slideTo(cardIndex[galleryThumbs.clickedIndex][0])
                    }
                }
            }
        });
        let galleryTop = new Swiper('.gallery-top', {
            pagination: {
                el: '.swiper-pagination',
                type: 'fraction',
            },
            spaceBetween: 10,
            hashNavigation: true,
            on: {
                slideChangeTransitionEnd: function () {
                    let acIndex = this.activeIndex;
                    $.each(cardIndex, function (e, index) {
                        if (index.indexOf(acIndex) != '-1') {
                            updateNavPosition(e);
                        }
                    })
                },
                imagesReady: function () {
                    let acIndex = this.activeIndex;
                    $.each(cardIndex, function (e, index) {
                        if (index.indexOf(acIndex) != '-1') {
                            updateNavPosition(e);
                        }
                    })
                }
            },
        });

        function updateNavPosition(goIndex = galleryThumbs.clickedIndex) {
            // console.log(goIndex);
            if (typeof (goIndex) != "undefined") {
                $('.gallery-thumbs .swiper-slide-thumb-active').removeClass('swiper-slide-thumb-active');
                let activeNav = $('.gallery-thumbs .swiper-slide').eq(goIndex);
                if (!activeNav.hasClass('swiper-slide-thumb-active')) {
                    activeNav.addClass('swiper-slide-thumb-active');
                }
            }
        }

        function initView() {
            if (location.hash === '#0' || location.hash === '') {
                $('.gallery-thumbs .swiper-slide').eq(0).addClass('swiper-slide-thumb-active');
            }
        }

        initView();
        let ccard_id = '';
        let exchange_id = '';
        const exchangeCardUrl = "{!! route('CollectCard::exchangeCard',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')]) !!}";
        const csrf_token = "{{csrf_token()}}";

        $('.exchangeBtn').click(function (event) {
            $('.excPopArea').show();
            ccard_id = $(this).data('id');
        });

        $('.confExcBtn').click(function () {
            if ($('.curChose').length == 0) {
                toast('请先选择要兑换的卡片');
                return false;
            }
            if (!ccard_id) return false;
            let c_id = $('.curChose').data('cid');
            let img = $('.curChose').data('img');
            showLoading('兑换中...');
            $.ajax({
                type: 'POST',
                url: exchangeCardUrl,
                dataType: "json",
                data: {"ccard_id": ccard_id, "c_id": c_id},
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                },
                success: function (response) {
                    hideLoading();
                    if (response.status == false) {
                        toast({time: 4000, content: response.message})
                        return false;
                    }
                    // toast({time: 4000, content: response.data.message})
                    exchange_id = response.data.id;
                    $('#exchangeImge').attr('src', img);
                    $('.excPopArea').hide();
                    $('.excSucPopArea').show();
                },
                error: function (e) {
                    alert('服务繁忙,请稍后再试！');
                }
            });
        });

        $('.excCardUl li').click(function () {
            $(this).addClass('curChose').siblings().removeClass('curChose');
            $('.confExcBtn').removeClass('notChose');
        });

        $('.reloadBtn').click(function () {
            window.location.href = "{!! route('CollectCard::myCard',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')]) !!}" + '#' + exchange_id;
            window.location.reload();
        })

        $('.closeBtn').click(function () {
            $('.popArea').hide();
            $('.excCardUl li').removeClass('curChose');
            $('.confExcBtn').addClass('notChose');
        });
    </script>

@endsection


