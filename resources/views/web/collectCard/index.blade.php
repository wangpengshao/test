@extends('web.collectCard.app')
@section('bodyClass', 'bgWhite')
@section('content')
    <div id="pageCon">
        <header class="cardColHd">
            <div class="bgPicSm bgPicSmLf"></div>
            <div class="bgPicSm bgPicSmRt"></div>
            @if($config['type']==1 )
                {{--判断是否集齐，是否可以开奖--}}
                @if($user['collect_all']==1 && array_get($collectReward,'is_get')==1)
                    @empty($collectReward['prize_id'])
                        <div class="bgPicSm redPacket packOpen hasOpen noMoney">
                            <div class="bgPicSm packOpenCon hasOpen">
                                <div class="failFont fontSiz5">很遗憾</div>
                                <div class="failFont">与奖品擦肩而过~</div>
                                <div class="cardTit">再接再厉</div>
                            </div>
                        </div>
                    @else
                        {{--区分奖品  红包还有奖品的类型--}}
                        @if($collectReward['type']==1 || $collectReward['type']==2)
                            <div class="bgPicSm redPacket packOpen hasOpen hasMoney">
                                <div class="bgPicSm packOpenCon hasOpen">
                                    <div class="congTit">恭喜您获得红包</div>
                                    <div class="moneyCon">¥<span class="moneyNum">{{$collectReward['money']}}</span>
                                    </div>
                                    <div class="cardTit">集卡达人</div>
                                </div>
                            </div>
                        @else
                            <div class="bgPicSm redPacket packOpen hasOpen hasMoney">
                                <div class="bgPicSm packOpenCon hasOpen">
                                    <div class="congTit">恭喜您获得奖品</div>
                                    <div class="moneyCon"><img src="{{$collectReward['prize_image']}}" alt="">
                                    </div>
                                    <div class="prize-title">{{$collectReward['prize_title']}}</div>
                                </div>
                            </div>
                        @endif
                    @endempty
                @elseif($user['collect_all']==1 && $config['end_at'] > date('Y-m-d H:i:s') )
                    <div class="bgPicSm redPacket" id="type1getAward">
                        <a href="javascript:;" class="openPacBtn"></a>
                        <div class="actlotteryTm">
                            <p class="lotteryFont"></p>
                            <div class="lotTmArea ">点击开奖</div>
                        </div>
                    </div>
                @else
                    {{--还没集齐显示活动倒计时 判断活动时间 --}}
                    @if($config['end_at']< date('Y-m-d H:i:s'))
                        <div class="bgPicSm redPacket packOpen hasOpen noMoney">
                            <div class="bgPicSm packOpenCon hasOpen">
                                <div class="failFont fontSiz5">很遗憾</div>
                                <div class="failFont">活动已结束~</div>
                                <div class="cardTit">再接再厉</div>
                            </div>
                        </div>
                    @else
                        <div class="bgPicSm redPacket timePack ">
                            <a href="javascript:;" class="openPacBtn"></a>
                            <div class="actlotteryTm">
                                <p class="lotteryFont">距离结束还有</p>
                                <div class="lotTmArea timespan">.....</div>
                            </div>
                        </div>
                    @endif
                @endif
            @else
                {{--需要集齐了才能开奖--}}
                {{--判断开奖时间--}}
                @if(strtotime($config['end_at']) + 900 < time())
                    {{--可以开奖--}}
                    @if($user['collect_all']==1 && array_get($collectReward,'is_get')==1)
                        @empty($collectReward['prize_id'])
                            <div class="bgPicSm redPacket packOpen hasOpen noMoney">
                                <div class="bgPicSm packOpenCon hasOpen">
                                    <div class="failFont fontSiz5">很遗憾</div>
                                    <div class="failFont">与奖品擦肩而过~</div>
                                    <div class="cardTit">再接再厉</div>
                                </div>
                            </div>
                        @else
                            @if($collectReward['type']==1 || $collectReward['type']==2)
                                <div class="bgPicSm redPacket packOpen hasOpen hasMoney">
                                    <div class="bgPicSm packOpenCon hasOpen">
                                        <div class="congTit">恭喜您获得红包</div>
                                        <div class="moneyCon">¥<span class="moneyNum">{{$collectReward['money']}}</span>
                                        </div>
                                        <div class="cardTit">集卡达人</div>
                                    </div>
                                </div>
                            @else
                                <div class="bgPicSm redPacket packOpen hasOpen hasMoney">
                                    <div class="bgPicSm packOpenCon hasOpen">
                                        <div class="congTit">恭喜您获得奖品</div>
                                        <div class="moneyCon"><img src="{{$collectReward['prize_image']}}" alt="">
                                        </div>
                                        <div class="prize-title">{{$collectReward['prize_title']}}</div>
                                    </div>
                                </div>
                            @endif
                        @endempty
                    @elseif($user['collect_all']==1)
                        <div class="bgPicSm redPacket" id="type1getAward">
                            <a href="javascript:;" class="openPacBtn"></a>
                            <div class="actlotteryTm">
                                <p class="lotteryFont"></p>
                                <div class="lotTmArea ">点击开奖</div>
                            </div>
                        </div>
                    @else
                        {{--还没集齐显示活动倒计时--}}
                        <div class="bgPicSm redPacket packOpen hasOpen noMoney">
                            <div class="bgPicSm packOpenCon hasOpen">
                                <div class="failFont fontSiz5">很遗憾</div>
                                <div class="failFont">活动已结束~</div>
                                <div class="cardTit">再接再厉</div>
                            </div>
                        </div>
                    @endif
                @else
                    @if($user['collect_all']==1)
                        <div class="bgPicSm redPacket timePack ">
                            <a href="javascript:;" class="openPacBtn"></a>
                            <div class="actlotteryTm">
                                <p class="lotteryFont">已集齐</p>
                                <div class="lotTmArea timespan">.....</div>
                            </div>
                        </div>
                    @else
                        <div class="bgPicSm redPacket timePack ">
                            <a href="javascript:;" class="openPacBtn"></a>
                            <div class="actlotteryTm">
                                <p class="lotteryFont">距离结束还有</p>
                                <div class="lotTmArea timespan">.....</div>
                            </div>
                        </div>
                    @endif
                    {{--等待开奖--}}
                @endif
            @endif
            <div class=" bgPicBot ">
                <div class="bgPicSm bgPicBot04"></div>
                <div class="bgPicSm bgPicBot01"></div>
                <div class="bgPicSm bgPicBot02"></div>
                <div class="bgPicSm bgPicBot03"></div>
            </div>
            <div class=" fgPicBig ">
                <div class="bgPicSm bgPaper fgPicB01"></div>
                <div class="bgPicSm bgPaper fgPicB02"></div>
                <div class="bgPicSm bgPaper fgPicB03"></div>
                <div class="bgPicSm bgPaper fgPicB04"></div>
            </div>
            <div class="actRuleBtn">
                <a href="{{route('CollectCard::rule',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')])}}"
                   class="actRuleBtnIn">活动规则</a>
            </div>
            <div class="actTitArea ovFlow">
                <div class="actTitAreaIn"></div>
            </div>
            <div class="actTime">
                活动时间:{{str_limit($config['start_at'],16,'').'~'.date('Y-m-d H:i',strtotime($config['end_at']))}}
            </div>
        </header>
        <div class="colCardArea">
            <div class="colCardAreaIn">
                <div class="swiper-container swiper-container-horizontal swiper-container-free-mode">
                    <ul class="ovFlow colCardUl2 swiper-wrapper">
                        @foreach($card as $value)
                            <li class="colCardLi {{array_get($myCount,$value['id']) ? 'hasCard':''}} swiper-slide">
                                <a href="{{route('CollectCard::myCard',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')])}}#@empty(!array_get($listFirstId,$value['id'])){{$listFirstId[$value['id']]}}@else{{$loop->index}}@endempty">
                                    <div class="cardItemImg">
                                        <img src="{{$value['image']}}" alt="">
                                        @empty(!array_get($myCount,$value['id']))
                                            <i class="circular">{{array_get($myCount,$value['id'])}}</i>
                                        @endempty
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            {{--            <div class="rankListArea" style="display:none">--}}
            {{--            <div class="bgPicSm rankTit">看看大家的手气</div>--}}
            {{--            <div class="ovFlow rankUl">--}}
            {{--            <div class="rankUlIn">--}}
            {{--            <div class="disFlex rankLi">--}}
            {{--            <div class="rankIcon rankFirIcon">1</div>--}}
            {{--            <div class="rankPer">--}}
            {{--            <span class="rankHead"><img src="img/share01.png" alt=""/></span>--}}
            {{--            <span class="rankPerName">会飞的猪</span>--}}
            {{--            </div>--}}
            {{--            <div class="perMoneyNum">88.8元</div>--}}
            {{--            </div>--}}
            {{--            <div class="disFlex rankLi">--}}
            {{--            <div class="rankIcon rankSecIcon">2</div>--}}
            {{--            <div class="rankPer">--}}
            {{--            <span class="rankHead"><img src="img/share02.png" alt=""/></span>--}}
            {{--            <span class="rankPerName">在跑的大鹅(用户本人)</span>--}}
            {{--            </div>--}}
            {{--            <div class="perMoneyNum">86.8元</div>--}}
            {{--            </div>--}}
            {{--            <div class="disFlex rankLi">--}}
            {{--            <div class="rankIcon rankThrIcon">3</div>--}}
            {{--            <div class="rankPer">--}}
            {{--            <span class="rankHead"><img src="img/share03.png" alt=""/></span>--}}
            {{--            <span class="rankPerName">在跑的大鹅</span>--}}
            {{--            </div>--}}
            {{--            <div class="perMoneyNum">84.8元</div>--}}
            {{--            </div>--}}
            {{--            <div class="disFlex rankLi">--}}
            {{--            <div class="rankIcon">4</div>--}}
            {{--            <div class="rankPer">--}}
            {{--            <span class="rankHead"><img src="img/share04.png" alt=""/></span>--}}
            {{--            <span class="rankPerName">在跑的大鹅</span>--}}
            {{--            </div>--}}
            {{--            <div class="perMoneyNum">54.8元</div>--}}
            {{--            </div>--}}
            {{--            <div class="disFlex rankLi">--}}
            {{--            <div class="rankIcon">5</div>--}}
            {{--            <div class="rankPer">--}}
            {{--            <span class="rankHead"><img src="img/share02.png" alt=""/></span>--}}
            {{--            <span class="rankPerName">在跑的大鹅</span>--}}
            {{--            </div>--}}
            {{--            <div class="perMoneyNum">56.8元</div>--}}
            {{--            </div>--}}
            {{--            <div class="disFlex rankLi">--}}
            {{--            <div class="rankIcon">6</div>--}}
            {{--            <div class="rankPer">--}}
            {{--            <span class="rankHead"><img src="img/share04.png" alt=""/></span>--}}
            {{--            <span class="rankPerName">在跑的大鹅</span>--}}
            {{--            </div>--}}
            {{--            <div class="perMoneyNum">54.8元</div>--}}
            {{--            </div>--}}
            {{--            <div class="disFlex rankLi">--}}
            {{--            <div class="rankIcon">7</div>--}}
            {{--            <div class="rankPer">--}}
            {{--            <span class="rankHead"><img src="img/share02.png" alt=""/></span>--}}
            {{--            <span class="rankPerName">在跑的大鹅</span>--}}
            {{--            </div>--}}
            {{--            <div class="perMoneyNum">56.8元</div>--}}
            {{--            </div>--}}
            {{--            </div>--}}
            {{--            </div>--}}
            {{--            </div>--}}
            @empty(!$htmlConfig)
                <div class="colCardStrat">
                    <div class="colCardStratIn">
                        <div class="colCardTit">集卡攻略</div>
                        <ul class="colCdStUl">
                            @if($htmlConfig['gl1_sw']==1)
                                <li class="disFlex colCdStLi colCdStLi01">
                                    <div class="stratIcon stratIcon01"></div>
                                    <div class="stratCon">
                                        <div class="stratTit">{{$htmlConfig['gl1_title']}}</div>
                                        <div class="stratConIn">{{$htmlConfig['gl1_info']}}</div>
                                    </div>
                                    <a href="{{route('CollectCard::strategy',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')])}}"
                                       class="disFlex stratBtnArea stratBtnArea01">
                                        <div class="stratBtnIn">去完成</div>
                                    </a>
                                </li>
                            @endif
                            @if($htmlConfig['gl2_sw']==1)
                                <li class="disFlex colCdStLi colCdStLi02">
                                    <div class="stratIcon stratIcon02"></div>
                                    <div class="stratCon">
                                        <div class="stratTit">{{$htmlConfig['gl2_title']}}</div>
                                        <div class="stratConIn">{{$htmlConfig['gl2_info']}}</div>
                                    </div>
                                    <a href="{{route('CollectCard::share',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')])}}"
                                       class="disFlex stratBtnArea stratBtnArea02">
                                        <div class="stratBtnIn">去完成</div>
                                    </a>
                                </li>
                            @endif
                            @if($htmlConfig['gl3_sw']==1)
                                <li class="disFlex colCdStLi colCdStLi03">
                                    <div class="stratIcon stratIcon03"></div>
                                    <div class="stratCon">
                                        <div class="stratTit">{{$htmlConfig['gl3_title']}}</div>
                                        <div class="stratConIn">{{$htmlConfig['gl3_info']}}</div>
                                    </div>
                                    <a href="{{route('CollectCard::firstTime',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')])}}"
                                       class="disFlex stratBtnArea stratBtnArea03">
                                        <div class="stratBtnIn">去完成</div>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </div>
                </div>
            @endempty
        </div>
    </div>
    <!--弹出框-->
    <div class="popArea redPacFailPop">
        <div class="mask"></div>
        <div class=" fgPicBig ">
            <div class="bgPicSm bgPaper fgPicB01"></div>
            <div class="bgPicSm bgPaper fgPicB02"></div>
            <div class="bgPicSm bgPaper fgPicB03"></div>
            <div class="bgPicSm bgPaper fgPicB04"></div>
        </div>
        <div class="popCon openPacArea">
            <div class="friSendTit">
                <p></p>
                <p class="friCardType">没有中奖</p>
            </div>
            <div class="openPacAreaIn">
                <div class="opPackCon">
                    <div class="opPkNum">
                        <div class="failFont">很遗憾</div>
                        <div class="failFont">与奖品擦肩而过~</div>
                    </div>
                    <div class="cardTit">再接再厉</div>
                </div>
            </div>
            <div class="moeyIcArea">
                <div class="moeyIcon moeyIc01"></div>
                <div class="moeyIcon moeyIc02"></div>
                <div class="moeyIcon moeyIc03"></div>
                <div class="moeyIcon moeyIc04"></div>
                <div class="moeyIcon moeyIc05"></div>
                <div class="moeyIcon moeyIc06"></div>
                <div class="moeyIcon moeyIc07"></div>
                <div class="moeyIcon moeyIc08"></div>
                <div class="moeyIcon moeyIc09"></div>
            </div>
            <div class="cardBtnArea"></div>
            <a href="javascript:;" class="clickRefresh closeBtn"></a>
        </div>
    </div>
    <div class="popArea redPacPop" style="{{$get_redpage ? 'display:block;':'' }}">
{{--    <div class="popArea redPacPop" >--}}
        <div class="mask"></div>
        <div class=" fgPicBig ">
            <div class="bgPicSm bgPaper fgPicB01"></div>
            <div class="bgPicSm bgPaper fgPicB02"></div>
            <div class="bgPicSm bgPaper fgPicB03"></div>
            <div class="bgPicSm bgPaper fgPicB04"></div>
        </div>
        <div class="popCon openPacArea">
            <div class="friSendTit">
                <p>恭喜你获得</p>
                <p class="friCardType">现金红包</p>
            </div>
            <div class="openPacAreaIn">
                <div class="opPackCon">
                    <div class="opPkNum">
                        <div class="congTit">恭喜你获得红包</div>
                        <div class="moneyCon">¥<span class="moneyNum">{{ $collectReward['money'] or '00.0' }}</span>
                        </div>
                    </div>
                    <div class="cardTit">集卡达人</div>
                </div>
            </div>
            <div class="moeyIcArea">
                <div class="moeyIcon moeyIc01"></div>
                <div class="moeyIcon moeyIc02"></div>
                <div class="moeyIcon moeyIc03"></div>
                <div class="moeyIcon moeyIc04"></div>
                <div class="moeyIcon moeyIc05"></div>
                <div class="moeyIcon moeyIc06"></div>
                <div class="moeyIcon moeyIc07"></div>
                <div class="moeyIcon moeyIc08"></div>
                <div class="moeyIcon moeyIc09"></div>
            </div>
            <div class="cardBtnArea" style="{{$get_redpage ? 'display:block;':'' }}">
                <a href="javascript:;" class="shareMethBtn w178 getRedpage">去领取</a>
            </div>
            <a href="javascript:;" class="clickRefresh closeBtn "></a>
        </div>
    </div>
    <div class="popArea prizePop">
        <div class="mask"></div>
        <div class=" fgPicBig ">
            <div class="bgPicSm bgPaper fgPicB01"></div>
            <div class="bgPicSm bgPaper fgPicB02"></div>
            <div class="bgPicSm bgPaper fgPicB03"></div>
            <div class="bgPicSm bgPaper fgPicB04"></div>
        </div>
        <div class="popCon openPacArea">
            <div class="friSendTit">
                <p>恭喜你获得</p>
                <p class="friCardType prizeText"></p>
            </div>
            <div class="openPacAreaIn">
                <div class="opPackCon">
                    <div class="opPkNum">
                        <div class="moneyCon"><img class="prizeImg"></div>
                    </div>
                    <div class="cardTit">集卡达人</div>
                </div>
            </div>
            <div class="moeyIcArea">
                <div class="moeyIcon moeyIc01"></div>
                <div class="moeyIcon moeyIc02"></div>
                <div class="moeyIcon moeyIc03"></div>
                <div class="moeyIcon moeyIc04"></div>
                <div class="moeyIcon moeyIc05"></div>
                <div class="moeyIcon moeyIc06"></div>
                <div class="moeyIcon moeyIc07"></div>
                <div class="moeyIcon moeyIc08"></div>
                <div class="moeyIcon moeyIc09"></div>
            </div>
            <a href="javascript:;" class="clickRefresh closeBtn "></a>
        </div>
    </div>
@endsection
@section('jsResources')
    <script type="text/javascript" src="{{asset('wechatWeb/collectCard/js/swiper.min.js')}}"></script>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script type="text/javascript" charset="utf-8">
        new Swiper('.swiper-container', {
            slidesPerView: "auto",
            spaceBetween: 0,
            freeMode: true,
            observer: true, //修改swiper自己或子元素时，自动初始化swiper
            observeParents: false, //修改swiper的父元素时，自动初始化swiper
        });
        const config = @json($config);
        const csrf_token = "{{csrf_token()}}";
        const getAwardUrl = "{!! route('CollectCard::getAward',$basisWhere) !!}";
        const initRedPageUrl = "{!! route('CollectCard::initRedPage',$basisWhere) !!}";
        const is_collect = "{{$user['collect_all']}}";

        let end_diff = parseInt("{{strtotime($config['end_at'])-time()}}");
        let start_diff = parseInt("{{strtotime($config['start_at'])-time()}}");
        let run_time = parseInt("{{strtotime($config['end_at'])+900-time()}}");

        function timer(intDiff) {
            let nextime = intDiff;
            let intervalId = window.setInterval(function () {
                nextime--;
                let day = 0, hour = 0, minute = 0, second = 0; //时间默认值
                if (nextime > 0) {
                    day = Math.floor(nextime / (60 * 60 * 24)); //天
                    hour = Math.floor(nextime / (60 * 60)) - (day * 24); //小时
                    minute = Math.floor(nextime / 60) - (day * 24 * 60) - (hour * 60); //分钟
                    second = Math.floor(nextime) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60); //秒
                }
                if (hour <= 9) hour = '0' + hour;
                if (minute <= 9) minute = '0' + minute;
                if (second <= 9) second = '0' + second;
                let text = '';
                if (day > 0) {
                    text += day + '天' + hour + '时' + minute + '分' + second + '秒';
                } else if (hour > 0) {
                    text += hour + '时' + minute + '分' + second + '秒';
                } else if (minute > 0) {
                    text += minute + '分' + second + '秒';
                } else {
                    text += second + '秒';
                }
                ;
                $(".timespan").html(text);
                if (nextime == 0) {
                    clearInterval(intervalId);
                    $('.actTime').text("活动已结束");
                    window.setTimeout(function () {
                        location.reload();
                    }, 4000)
                }
            }, 1000);
        }

        function runTimer(intDiff) {
            let nextime = intDiff;
            $(".lotteryFont").html('开奖倒计时');
            let intervalId = window.setInterval(function () {
                nextime--;
                let day = 0, hour = 0, minute = 0, second = 0; //时间默认值
                if (nextime > 0) {
                    day = Math.floor(nextime / (60 * 60 * 24)); //天
                    hour = Math.floor(nextime / (60 * 60)) - (day * 24); //小时
                    minute = Math.floor(nextime / 60) - (day * 24 * 60) - (hour * 60); //分钟
                    second = Math.floor(nextime) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60); //秒
                }
                if (hour <= 9) hour = '0' + hour;
                if (minute <= 9) minute = '0' + minute;
                if (second <= 9) second = '0' + second;
                let text = '';
                if (day > 0) {
                    text += day + '天' + hour + '时' + minute + '分' + second + '秒';
                } else if (hour > 0) {
                    text += hour + '时' + minute + '分' + second + '秒';
                } else if (minute > 0) {
                    text += minute + '分' + second + '秒';
                } else {
                    text += second + '秒';
                }
                ;
                $(".timespan").html(text);
                ;
                if (nextime == 0) {
                    clearInterval(intervalId);
                    toast('开奖完成,等待刷新');
                    window.setTimeout(function () {
                        location.reload();
                    }, 4000)
                }
            }, 1000);
        }

        function canScroll() {
            $('#pageCon').removeClass('addPf'); //去掉给div的类
        }

        $(function () {
            if (typeof end_diff != 'undefined' && end_diff > 0 && start_diff < 0) {
                timer(end_diff);
            }
            if (config.type != 1 && run_time > 0 && is_collect == 1) {
                runTimer(run_time);
            }
            // $('.rankListArea').slideDown();
            $('.shareMyGetCard .colCardUl li').each(function (index, el) {
                $(this).click(function () {
                    if (!$(this).hasClass('hasTap')) {
                        $(this).addClass('hasTap');
                        $(this).find(".frontImg").children().addClass("turn");
                        $(this).find(".backImg").children().addClass('turn2');
                    }
                    ;
                });
            });

            $('.closeBtn').click(function () {
                canScroll();
                $('.popArea').hide();
                $('.excCardUl li').removeClass('curChose');
                $('.confExcBtn').addClass('notChose');
            });

            $('.excImple').click(function (event) {
                $('.excPopArea').show();
            });

            $('.excCardUl li').click(function () {
                $(this).addClass('curChose').siblings().removeClass('curChose');
                $('.confExcBtn').removeClass('notChose');
            });

            $('.confExcBtn').click(function () {
                $('.excPopArea').hide();
                $('.excSucPopArea').show();
            });
            $('.clickRefresh').click(function () {
                $('.lotTmArea ').hide();
                location.reload();
            })

            $('#type1getAward').click(function () {  //进行领奖
                showLoading('抽奖中...');
                $.ajax({
                    type: 'POST',
                    url: getAwardUrl,
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': csrf_token
                    },
                    success: function (response) {
                        hideLoading();
                        if (response.status == false) {
                            toast({time: 4000, content: response.message})
                            return false;
                        }
                        if (response.data.is_winning === 0) {
                            $('.redPacFailPop').show();
                            return false;
                        }
                        if (response.data.type === 1) {
                            $('.redPacPop .moneyNum').text(response.data.money);
                            $('.redPacPop').show();
                            return true;
                        } else {
                            $('.prizeText').text(response.data.title);
                            $('.prizeImg').attr('src', response.data.image);
                            $('.prizePop').show();
                        }
                    },
                    error: function (e) {
                    }
                });
            })

            $('.getRedpage').click(function () {
                showLoading('领取中...');
                $.ajax({
                    type: 'POST',
                    url: initRedPageUrl,
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': csrf_token
                    },
                    success: function (response) {
                        hideLoading();
                        if (response.status == false) {
                            toast({time: 4000, content: response.message})
                            return false;
                        }
                        if (response.data.url) {
                            toast({time: 4000, content: response.data.message});
                            setTimeout(function () {
                                location.href = response.data.url;
                            },4000);
                        }
                    },
                    error: function (e) {
                    }
                });
            });
        });
    </script>
    {{--微信分享 start--}}
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
    {{--微信分享 end--}}
@endsection



