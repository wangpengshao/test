<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="email=no">
    <meta name="referrer" content="never">
    <title>图书借阅排行榜</title>
    <link rel="stylesheet" href="https://u.interlib.cn/tpl/Mysql/Top/style2.css?5">
</head>
<body>
<div class="rankings">
    <div class="head-seclect">
        <div>
            <select onchange="goData($(this))">
                <option value="">图书馆</option>
                @foreach($libList as $val)
                    <option {{$val['libcode'] == $libcode ? 'selected' :''}}
                            value="{{$val['libcode']}}">{{$val['name']}}</option>
                @endforeach
            </select>
        </div>
        <div></div>
    </div>
    <div class="ranklist">
    @forelse ($bookList as $key => $val)
        <!-- 数据列表 -->
            <ul id="list">
                <li>
                    <div class="top">
                        <div class="left">
                            <img class="img-responsive"
                                 src="{{empty($val['imgurl']) ? 'https://u.interlib.cn/tpl/Mysql/public/Defaultbook.jpg' : $val['imgurl']}}">
                        </div>
                        <div class="center">
                            <span class="title oneLine">{{$val['title_meta']}}</span>
                            <span class="line oneLine">著作:{{$val['author_meta']}}</span>
                            <span class="line oneLine">出版社:{{$val['publisher_meta']}}</span>
                            <span class="line oneLine">出版时间:{{$val['pubdate_meta']}}</span>
                            <span class="line oneLine">借阅次数:{{$val['loannum_sort']}}</span>
                        </div>
                        <div class="right">
                            @if($key <=2)
                                <img class="img-responsive"
                                     src="https://u.interlib.cn/tpl/Mysql/Top/images/ranks_01.png">
                            @else
                                <img class="img-responsive"
                                     src="https://u.interlib.cn/tpl/Mysql/Top/images/ranks_04.png">
                            @endif
                            <div class="ranks">
                                <span>{{$key+1}}</span>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
    @empty
        <!-- 没有数据 -->
            <div class="no-data">
                <div class="pic"><img class="img-responsive"
                                      src="https://u.interlib.cn/tpl/Mysql/Top/images/heartNotAct.png"></div>
                <div class="text">抱歉，暂无排行数据！</div>
            </div>
        @endforelse
    </div>
</div>
<script type="text/javascript" src="{{asset('common/js/jquery-3.4.1.min.js')}}"></script>
<script>
    let url = "{!! $url !!}";
    function goData(e) {
        let libcode = e.val();
        window.location.href = url + "&libcode=" + libcode;
    }
</script>
</body>
</html>
