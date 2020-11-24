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
                        {{--判断是否是朋友赠送的--}}
                        @if($user['id']!= $value['user_id'])
                            <a href="javascript:void(0);" data-userid="{{$value['user_id']}}"
                               data-card="{{$value['id']}}" class="shareMethBtn w178" id="getGiveCard">收下</a>
                        @else
                            <a href="{{route('CollectCard::myCard',$basisWhere)}}#{{$value['id']}}"
                               class="shareMethBtn w178">{{($value->hasOneCard['type']==1) ? '去兑换': '查看'}}</a>
                        @endif
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

        const getGiveCardUrl = "{!! route('CollectCard::getGiveCard',$basisWhere) !!}";
        const indexUrl = "{!!$indexUrl !!}";
        const csrf_token = "{{csrf_token()}}";

        $('#getGiveCard').click(function () {
            // if ($(this).text()=='') 
            showLoading('领取中...');
            let giveCard_id = $(this).data('card');
            let giveUser_id = $(this).data('userid');
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
                    if (response.status == false) {
                        return false;
                    }
                    toast({time: 4000, content: '已收下'})
                    $('#getGiveCard').removeAttr("id");
                    $('#getGiveCard').text('已收下');
                    // window.setTimeout(function () {
                    //     window.location.href=indexUrl;
                    // },3000);
                },
                error: function (e) {
                    // alert('服务繁忙,请稍后再试！')
                }
            });

        })

    </script>
@endsection


