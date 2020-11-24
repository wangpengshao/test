@extends('web.collectCard.app')

@section('bodyClass', 'bgLinear')
@section('content')
    <div class="bgPicSm bgPicSmLf"></div>
    <div class="bgPicSm bgPicSmRt"></div>
    <div class="ovFlow">
        <div class="stratHdTit firJoinTit"></div>
    </div>
    <div class="stratConArea">
        <div class="stratModIn firJoinArea">
            <div class="stratTopIcon">
                <i class="stratTopLt"></i>
                <i class="stratTopRt"></i>
            </div>
            <div class="stratModDet">
                <i class="stratNum">1</i><span>{{$htmlConf['first_info']}}</span>
            </div>
            <div class="firGetCd">
                <div class="firGetCdImg"><img src="{{$firstCard['image']}}" alt=""/></div>
                <div>
                    <p class="stratModDet">首次参与获得一张</p>
                    <p class="firCardName">{{$firstCard['text']}}</p>
                    <a href="{{route('CollectCard::myCard',['token'=>request()->input('token'),'a_id'=>request()->input('a_id')])}}#{{$collectLog['id']}}"
                       class="shareMethBtn toExcBtn">{{($firstCard['type']==1) ? '去兑换': '去查看'}}</a>
                </div>
            </div>
        </div>
    </div>
@endsection