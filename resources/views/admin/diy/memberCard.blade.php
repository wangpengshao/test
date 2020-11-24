<style>
    .col-md-3{
        //min-width: 380px;
    }
    .card_body{
        max-width: 350px;
        height: 550px;
        border:1px solid #e4e4e4;
        background-color: #f6f6f8;
        border-top: 3px solid #00c0ef;
        position: relative;
        left: 0;
        right: 0;
        margin: 0 auto;
    }
    .card_body ol, .card_body ul{
        list-style: none;
        padding: 0;
    }
    .card_body .shop {
        padding: 80px 26px 10px;
        border-radius: 0;
        -moz-border-radius: 0;
        -webkit-border-radius: 0;
        background: #fff url(/img/card/ios.png) no-repeat center 5px;
        border-bottom: 0;
    }
    .card_body .shop .js_background_pic_url_preview{
        border-radius: 10px;
        min-height: 140px;
    }
    .card_body .logo_area {
        position:relative;
        margin-bottom:7px;
        line-height:42px;
        color:#8d8d8d;
    }
    .card_body .logo {
        display:block;
        width:38px;
        height:38px;
        padding-top:0;
        margin:0 auto;
        border-radius:22px;
        -moz-border-radius:22px;
        -webkit-border-radius:22px;
        float:none;
        border:1px solid #e7e7eb;
        background-color:#fff
    }
    .card_body .logo img {
        display:block;
        width:100%;
        height:100%;
        border-radius:inherit;
        -moz-border-radius:inherit;
        -webkit-border-radius:inherit
    }
    .card_body .shop .shop_panel {
        background-color:#fff;
        border-radius:12px;
        -moz-border-radius:12px;
        -webkit-border-radius:12px;
        text-align:left;
        padding:0 0 10px;
        overflow:hidden;
        position:relative;
        background-size:100% 100%;
        background-size:cover;
        background-position:top center
    }
    .card_body .msg_card_cell {
        background-color:#fff;
        *zoom:1
    }
    .card_body .shop .logo_area {
        margin:0;
        padding:24px 20px 12px 66px;
        text-align:left;
        line-height:20px;
        color:#fff;
        font-size:14px;
        font-weight:bolder;
        text-shadow:1px 1px 3px #666
    }
    .card_body .shop .card_name {
        font-size:14px;
        font-weight:bold
    }
    .card_body .shop .logo {
        position:absolute;
        width:38px;
        height:38px;
        top:24px;
        left:20px
    }
    .card_body .shop .qrcode {
        width:36px;
        height:36px;
        background:url('/img/card/tcsoft_qrcode.png') center/100% no-repeat;
        position:absolute;
        top:26px;
        right:20px
    }
    .card_body .msg_area {
        padding:2px 20px 10px;
        margin-top:30px;
        position:relative
    }
    .card_body .msg_area .member_number {
        font-size:17px;
        font-family:menlo, tahoma;
        text-shadow:1px 1px 2px #666;
        line-height:18px;
        color:#fff
    }
    .msg_card_cell.quick_pay .quick_pay_wording {
        font-size:12px
    }
    .msg_card_cell.shop_detail {
        border-radius:0 0 5px 5px;
        -moz-border-radius:0 0 5px 5px;
        -webkit-border-radius:0 0 5px 5px
    }
    .msg_card_cell.shop_detail .list li:last-child .li_panel {
        border-bottom-width:0
    }
    .msg_card_cell.custom_detail {
        margin-top:1em;
        border-radius:5px;
        -moz-border-radius:5px;
        -webkit-border-radius:5px
    }
    .msg_card_cell.custom_detail .list {
        margin: 0 10px;
        border-bottom-width:0
    }
    .msg_card_cell.custom_detail .list li:last-child .li_panel {
        border-bottom-width:0
    }
    .msg_card_cell.dispose {
        display:none
    }
    .msg_card_cell.promotion {
        color:#ff9f00;
        text-align:center;
        padding:5px 0;
        background-color:transparent;
        visibility:hidden
    }
    .msg_card_cell.last_cell {
        border-radius:0 0 5px 5px;
        -moz-border-radius:0 0 5px 5px;
        -webkit-border-radius:0 0 5px 5px
    }
    .msg_card_cell.last_cell .list {
        border-bottom-width:0;
        margin: 0 10px;
    }
    .msg_card_section{
        position: relative;
    }
    .msg_card_section p{
        margin: 0;
    }
    .list li .li_panel {
        display:block;
        padding:11px 30px 11px 0;
        border-bottom:1px solid #e7e7eb
    }
    .list li .li_panel .ic {
        position:absolute;
        top:36%;
        right:5px;
        width:16px;
        height:15px;
        background:url('/img/card/icon.png') 0 -128px no-repeat
    }
    .card_body .quick_pay {
        height:auto;
        padding:17px 0 30px;
        border-top:0;
        border-bottom:1px solid #e7e7eb
    }
    .msg_card_cell.quick_pay {
        text-align:center;
    }
    .msg_card_cell.quick_pay .quick_pay_wording {
        font-size:12px
    }
    .card_body .quick_pay .btn_card_preview {
        display:inline-block;
        height:36px;
        line-height:36px;
        border-width:1px;
        border-style:solid;
        -webkit-border-radius:6px;
        -moz-border-radius:6px;
        -ms-border-radius:6px;
        border-radius:6px;
        width:136px
    }
</style>
<div class="card_body" id="js_preview_area">
    <div class="js_preview msg_card_section shop ">
        <div class="shop_panel js_color_bg_preview js_background_pic_url_preview" style='background-color: rgb(99, 179, 89);'>
            <div class="mask"></div>
            <div class="logo_area">
<span class="logo">
<img id="js_logo_url_preview" src="http://storage-oss-jay.oss-cn-shenzhen.aliyuncs.com/images/logo.jpg">
</span>
                <span id="js_brand_name_preview">图创软件</span>

                <p class="card_name" id="js_title_preview">读者证</p>
                <span class="qrcode"></span>
            </div>
            <div class="msg_area"> <span class="member_number">0268 8888 8888</span>
                <span class="icon_info"></span>
            </div>
        </div>
    </div>
    <div class="msg_card_cell member_extend_area" style="display:none;">
        <div class="member_extend_item">
            <div class="member_extend_title">积分</div>
            <div class="member_extend_des js_title_color_preview" style="color: rgb(99, 179, 89);">100</div>
        </div>
    </div>
    <div class="msg_card_cell quick_pay" id="js_wepay_item">
        <a class="btn_card_preview js_title_color_preview js_use_card_button" style="color: rgb(99, 179, 89);" href="javascript:;">立即使用</a>
    </div>
    <div class="msg_card_cell shop_detail last_cell">
        <ul class="list">
            <li class="msg_card_section">
                <div class="li_panel" href="">
                    <div class="li_content">
                        <p>详情</p>
                    </div> <span class="ic ic_go"></span>

                </div>
            </li>
            <li class="msg_card_section last_li">
                <div class="li_panel" href="">
                    <div class="li_content">
                        <p>公众号</p>
                    </div> <span class="ic ic_go"></span>

                </div>
            </li>
        </ul>
    </div>
    <div class="msg_card_cell custom_detail">
        <ul class="list" id="js_custom_url_preview">
            <li class="msg_card_section last_li">
                <div class="li_panel">
                    <div class="li_content">
                        <p><span class="supply_area"><span class="js_custom_url_tips_pre"></span><span class="ic ic_go"></span></span><span class="js_custom_url_name_pre">自定义入口(选填)</span>
                        </p>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>