@extends('web.collectCard.app')

@section('bodyClass', 'bgLinear')

@section('content')
    <div class="bgPicSm bgPicSmLf"></div>
    <div class="bgPicSm bgPicSmRt"></div>
    <div class="ovFlow">
        <div class="stratHdTit"></div>
    </div>
    <div class="stratConArea">
        <div class="stratModIn">
            <div class="stratTopIcon">
                <i class="stratTopLt"></i>
                <i class="stratTopRt"></i>
            </div>
            @if($htmlConfig['ty1_sw']==1)
                <div class="stratModCon">
                    <div class="ovFlow">
                        <div class="flLeft stratOneImg"></div>
                        <div class="flRight stratOneCon">
                            <div class="stratModTit">
                                <i class="stratNum">1</i><span
                                        class="stratModTitFont">{{$htmlConfig['ty1_title']}}</span>
                            </div>
                            <div class="stratModDet">{{$htmlConfig['ty1_info']}}</div>
                        </div>
                    </div>
                    <div class="expArea">
                        <div class="ovFlow expStepUl">
                            <span class="flLeft expTit">去体验 >></span>
                            @foreach($taskList as $value)
                                @if($value['url']!='javascript:void(0)')
                                    <div class="expStepLi">
                                        <a href="{{$value['url']}}"
                                           class="stratBtnIn expStepLink">{{$value['title']}}</a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            @if($htmlConfig['ty2_sw']==1)
                <div class="stratModCon stratModSec">
                    <div class="ovFlow">
                        <div class="flLeft  stratTwoCon">
                            <div class="stratModTit">
                                <i class="stratNum">2</i><span
                                        class="stratModTitFont">{{$htmlConfig['ty2_title']}}</span>
                            </div>
                            <div class="stratModDet">{{$htmlConfig['ty2_info']}}</div>
                        </div>
                        <div class="flRight stratTwoImg"></div>
                    </div>
                    <div class="expArea">
                        <div class="ovFlow expStepUl">
                            <div class="expStepLi"><a
                                        href="{{route('CollectCard::selfService',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')])}}"
                                        class="stratBtnIn expStepLink">查看附近借还机</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="stratConArea">
        <div class="stratModIn">
            <div class="myExpTit">
                <span>我的体验战绩</span>
            </div>
            <div class="myGetFn">
                <div class="myExpFnTit">已体验 {{$isOkNumber}} 个功能</div>
                <ul class="expFnUl">
                    @foreach($nperList as $value)
                        <li class="expFnLi {{$value['is_ok']?'hasExpFn':''}}">{{$value['info']}}</li>
                    @endforeach
                    @foreach($daysList as $value)
                        <li class="expFnLi {{$value['is_ok']?'hasExpFn':''}}">{{$value['info']}} (每日体验)</li>
                    @endforeach
                </ul>
            </div>
            @if(!$haveCardList->isEmpty())
                <div class="myGetCard">
                    <div class="myExpFnTit">共获得卡片</div>
                    <ul class="ovFlow colCardUl">
                        @foreach($haveCardList as $value)
                            @if(array_get($card,$value['c_id']) )
                                <li class="colCardLi hasCard">
                                    <img src="{{array_get($card,$value['c_id'])}}" alt="">
                                </li>
                            @endif
                        @endforeach

                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection



