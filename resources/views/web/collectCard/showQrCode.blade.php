@extends('web.collectCard.app')
@section('bodyClass', 'bgLinear')
@section('content')
    <div class="bgPicSm bgPicSmLf"></div>
    <div class="bgPicSm bgPicSmRt"></div>
    <div class="actTitArea ovFlow">
        <div class="actTitAreaIn"></div>
    </div>
    <div class="stratConArea">
        <div class="stratModIn">
            <div class="stratTopIcon">
                <i class="stratTopLt"></i>
                <i class="stratTopRt"></i>
            </div>
            <div class="stratModCon">
                <div class="ovFlow">
                    <div class="stratModDet" style="text-align: center">
                        <span>{{$config['sub_text']}}</span>
                    </div>
                </div>
            </div>
            <div class="disFlex shareMethArea">
                <div class="shareMethCon">
                    <a href="javascript:;" class=" "><img src="{{$qrcode}}" alt=""></a>
                    <a href="javascript:;" class="shareMethBtn">长按识别二维码关注公众号</a>
                </div>
            </div>
        </div>
    </div>
@endsection



