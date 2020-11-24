<style>
    .content {
        position: relative;
    }
    .box {
        margin:0 auto;
    }
    .subjectwrap {
        position: none;
        float: none;
        width: auto;
    }
    .clearfix {
        display: block;
    }
    .clearfix {
        zoom: 1;
        display: inline-block;
        _height: 1px;
        margin:0 auto;
    }
    .subjectwrap {
        position: relative;
        float: left;
        width: 100%;
        margin-bottom: 15px;
    }
    .clearfix {
        display: block;
    }
    .clearfix {
        zoom: 1;
        display: inline-block;
        _height: 1px;
    }
    #mainpic {
        margin: 3px 0 0 0;
        float: left;
        text-align: center;
        margin: 3px 12px 0 0;
        max-width: 155px;
        overflow: hidden;
    }
    #info {
        float: left;
        max-width: 248px;
        word-wrap: break-word;
    }
    .title {
        font-size: 18px;
        color: #323232;
        font-weight: 700;
    }
    .pl {
        color: grey;
    }
    .notice {
        color: #ee554a;
    }
    .reason {
        color: #323232;
        font-weight: 500;
    }
</style>
@foreach($bookList as $row => $value)
    <div class="subject clearfix" style="margin: 0 auto;">
    <div id="mainpic" class="">
            <img src="{{$value['image']}}" title="点击看大图" alt="爱是一种选择" rel="v:photo" style="width: 135px;max-height: 200px;">
    </div>
    <div id="info" class="">
        <span class="title">书籍名称:</span> <span class="title">{{$value['title']}}</span><br>
    <span>
      <span class="pl"> 作者:{{$value['author']}}</span>
    </span><br>
        <span class="pl">出版社:{{$value['publisher']}}</span> <br>
        <span class="pl">概要:{{$value['summary']}}</span> <br>
        <span class="reason">推荐理由:</span><span class="reason">{{$value['reason']}}</span><br>
        <span class="pl">书籍查看数:</span><span class="notice">{{$value['view_num']}}</span><br>
        <span class="pl">书籍收藏数:</span><span class="notice">{{$value['col_num']}}</span><br>
    </div>
</div>
@endforeach