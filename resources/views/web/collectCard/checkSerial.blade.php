@extends('web.collectCard.app')
{{--@section('bodyClass', 'bgLinear')--}}
@section('content')
    @foreach($logList as $value)
        <div class="popArea newPopupView" style="display:{{$loop->first ?'block' :'none' }}">
            <div class="mask "></div>
            <div class="popCon">
                <div class="singleCard">
                    <div class="friSendTit">
                        <p>
                            @empty($value['get_tip'])
                                <span class="friName">恭喜您获得一张</span>
                            @else
                                <span class="friName">{{$value['get_tip']}}</span>
                            @endempty
                        </p>
                        <p class="friCardType"> {{$value->hasOneCard['text']}} </p>
                    </div>
                </div>
                <div class="cardOut">
                    <div class="firSendImg">
                        <img src="{{$value->hasOneCard['image']}}" alt=""/>
                    </div>
                </div>
                <div class="cardBtnArea">
                    @if($loop->count > 1)
                        <a href="{{route('CollectCard::myCard',$basisWhere)}}#{{$value['id']}}"
                           class="shareMethBtn w178">{{($value->hasOneCard['type']==1) ? '去兑换': '查看'}}</a>
                        @if($loop->first)
                            <a href="javascript:void(0);" class="shareMethBtn flRight nextShow">下一张</a>
                        @else
                            <a href="javascript:void(0);" class="shareMethBtn flRight lastShow">上一张</a>
                        @endif
                        {{--判断是首张还是最后一张--}}
                    @else
                        <a href="{{route('CollectCard::myCard',$basisWhere)}}#{{$value['id']}}"
                           class="shareMethBtn w178">{{($value->hasOneCard['type']==1) ? '去兑换': '查看'}}</a>
                    @endif

                </div>
                <a href="javascript:void(0);" class="closeBtn"></a>
            </div>
        </div>
    @endforeach
@endsection
@section('jsResources')
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script type="text/javascript" charset="utf-8">
        $('.nextShow').click(function () {
            let popArea = $(this).parents('.newPopupView');
            popArea.css('display', 'none');
            popArea.next().css('display', 'block');
        })
        $('.lastShow').click(function () {
            let popArea = $(this).parents('.newPopupView');
            popArea.css('display', 'none');
            popArea.prev().css('display', 'block');
        })
        $('.closeBtn').click(function () {
            window.location.href = indexUrl;
        })

    </script>
@endsection


