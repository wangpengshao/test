<style>

    .color1{
        color:#888787;
    }
    .width-28{
        box-sizing: border-box;
        width: 28%;
        flex-shrink: 0;
        flex-grow: 0;

    }  }
    .wrap{
        width: 100%;
    }
    .wrap>.chatPanel{
        width: 90%;
        position: relative;
        left: 0;
        right: 0;
        margin: 0 auto 3rem;
        box-sizing: border-box;
        border: 1px solid #cdcdcd;
        box-shadow: 0 3px 6px #999999;
        border-radius: 6px;
    }
    .chatPanel>.mediaPanel{
        padding: 1rem 0;
    }
    .mediaPanel>.mediaHead,
    .mediaPanel>.mediaContent{
        padding: .5rem 1rem;
    }
    .mediaPanel>.mediaHead>.title{
        font-weight: bold;
    }
    .mediaContent>.item{
        display: flex;
        align-items: flex-start;
    }
    .mediaContent>.item>p{
        flex-grow:1;
    }
    .mediaPanel>.mediaFooter{
        padding: .5rem 1rem 0;
        border-top: 1px solid #e4e4e4;
    }

</style>
<div class="wrap">
    <div class="chatPanel">
        <div class="mediaPanel">
            <div class="mediaHead">
                <p class="title" id="title">服务申请提交成功</p>
                {{--<p class="first color1">尊敬的xxx . . .</p>--}}
            </div>
            <div class="mediaContent" style=" " id="default">
                您好，您的“修改订单”申请小易已经收到，正抓紧为您解决，请耐心等待 :)<br><br>
                服务单号：3845020<br>
                服务类型：修改订单<br>
                处理状态：待处理<br>
                提交时间：2018-01-07 11:13:01<br><br>
                点击“详情”查看服务单详细信息，如有疑问，可回复KF联系小易。
            </div>
            <div class="mediaContent" id="custom">

            </div>

            <div class="mediaFooter">
                <span>查看详情</span>
            </div>
        </div>
    </div>
</div>