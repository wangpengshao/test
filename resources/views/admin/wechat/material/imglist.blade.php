@extends('admin.wechat.material.layout')

@section('content')
    {{--<div class="row">--}}
    <style>
        .div1 {
            margin: 2rem;
            width: 160px;
            border: 1px solid #eee;
            position: relative;
            padding: 0;
        }

        .div1:hover {
            border: 1px solid #03a9f4;
        }

        .div2 {
            height: 100px;
            text-align: center;
            line-height: 100px;
            box-sizing: border-box;
        }

        .div2 > img {
            max-width: 100%;
            height: auto;
            max-height: 92px;
            vertical-align: middle;
            cursor: pointer;
        }

        .file-info {
            text-align: center;
            padding: 10px;
            background: #f4f4f4;
        }

        .file-name {
            font-weight: bold;
            color: #666;
            display: block;
            overflow: hidden !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
        }

        .copy {
            cursor: pointer;
            color: #888a85;
        }
    </style>
    <div class="col-xs-12">
        <div class="box">
            @foreach($ls as $value)
                <div class="col-sm-3 col-md-2 p-3 div1" data-toggle="tooltip" title="点击图片选择">
                    <div class="color-palette-set div2" onclick="newtest($(this))"
                         data-name="{{$value['name']}}" data-url="{{$value['url']}}">
                        <img @if($loop->index>18)
                             class="lazy preview  " data-original="{{$value['url']}}"
                             @else
                             class=" preview" src="{{$value['url']}}"
                             @endif
                             class="glyphicon glyphicon-ok text-success" aria-hidden="true" title="点击图片选择">
                    </div>
                    <div class="file-info">
                        <a target="_blank" href="{{$value['url']}}" class="file-name">
                            {{substr($value['name'],strrpos($value['name'],'/')+1)}}
                        </a>
                        <span class="copy" title="复制链接" data-url="{{$value['url']}}"
                              onclick="copyUrl($(this))">复制链接</span>
                    </div>
                </div>
            @endforeach

        </div>
        <!-- /.box -->
    </div>
    {{--</div>--}}
@endsection
