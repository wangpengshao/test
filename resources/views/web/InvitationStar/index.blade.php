<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>推广之星</title>
    <style>
        html {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* 去除iPhone中默认的input样式 */
        input[type="submit"],
        input[type="reset"],
        input[type="button"],
        input, textarea, button {
            -webkit-appearance: none;
            resize: none;
            outline: none;
        }

        input:-ms-input-placeholder,
        textarea:-ms-input-placeholder {
            color: cccccc;
        }

        input::-webkit-input-placeholder,
        textarea::-webkit-input-placeholder {
            color: cccccc;
        }

        button, input, textarea {
            font-family: "微软雅黑";
        }

        /* 取消链接高亮  */
        body, div, ul, li, ol, h1, h2, h3, h4, h5, h6, input, textarea, select, p, dl, dt, dd, a, img, button, form, table, th, tr, td, tbody, article,
        aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
            font-weight: normal;
        }

        /* 设置HTML5元素为块 */
        article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
            display: block;
        }

        /* 图片自适应 */
        img {
            width: 100%;
            max-height: 100%;
            width: auto \9; /* ie8 */
            -ms-interpolation-mode: bicubic; /*为了照顾ie图片缩放失真*/
            vertical-align: middle;
        }

        /* 初始化 */
        body, div, ul, li, ol, h1, h2, h3, h4, h5, h6, input, textarea, select, p, dl, dt, dd, a, img, button, form, table, th, tr, td, tbody, article,
        aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
            margin: 0;
            padding: 0;
            border: none;
        }

        body {
            font: normal 12px/1.5 Tahoma, "Lucida Grande", Verdana, "Microsoft Yahei", STXihei, hei;
            color: #333;
        }

        em, i {
            font-style: normal;
        }

        strong {
            font-weight: normal;
        }

        .clearfix:after {
            content: "";
            display: block;
            visibility: hidden;
            height: 0;
            clear: both;
        }

        .clearfix {
            zoom: 1;
        }

        a {
            text-decoration: none;
            color: #333;
            font-family: Tahoma, "Lucida Grande", Verdana, "Microsoft Yahei", STXihei, hei;
        }

        a:active {
            color: none;
            text-decoration: none;
        }

        ul, ol {
            list-style: none;
        }

        h1, h2, h3, h4, h5, h6, input {
            font-size: 100%;
            font-weight: normal;
            font-family: Microsoft YaHei;
        }

        img {
            border: none;
            vertical-align: top;
        }

        body {
            overflow-x: hidden;
            background: #f5f5f5;
            box-sizing: border-box;
        }

        * {
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
        }


        .row {
            /*横*/
            display: -webkit-box;
            display: -webkit-flex; /* Safari */
            display: -moz-box;
            display: -moz-flex;
            display: -ms-flexbox;
            display: flex;
            width: 100%;
        }

        .col { /*列*/
            -webkit-box-flex: 1;
            -webkit-flex: 1;
            -moz-box-flex: 1;
            -moz-flex: 1;
            -ms-flex: 1;
            flex: 1;
            display: block;
            width: 100%;
        }

        .col-25 {
            -webkit-box-flex: 0;
            -webkit-flex: 0 0 25%;
            -moz-box-flex: 0;
            -moz-flex: 0 0 25%;
            -ms-flex: 0 0 25%;
            flex: 0 0 25%;
            max-width: 25%;
        }


        .col-33, .col-34 {
            -webkit-box-flex: 0;
            -webkit-flex: 0 0 33.3333%;
            -moz-box-flex: 0;
            -moz-flex: 0 0 33.3333%;
            -ms-flex: 0 0 33.3333%;
            flex: 0 0 33.3333%;
            max-width: 33.3333%;
        }


        .col-50 {
            -webkit-box-flex: 0;
            -webkit-flex: 0 0 50%;
            -moz-box-flex: 0;
            -moz-flex: 0 0 50%;
            -ms-flex: 0 0 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }

        /*消除transition闪屏*/
        .prevent-splash {
            -webkit-transform-style: preserve-3d;
            transform-style: preserve-3d;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
        }

        /*解决页面闪白，保证动画流畅，开启硬件加速*/
        .prevent-flow {
            -webkit-transform: translate3d(0, 0, 0);
            -moz-transform: translate3d(0, 0, 0);
            -ms-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }

        body.hasBgColor {
            background: #fff;
        }

        .siteHeadArea {
            width: 100%;
            height: 5.2rem;
            background: url(../img/hdBg.png) no-repeat;
            background-size: 100% 100%;
            position: relative;
            margin-bottom: 1.02rem;
        }

        .sysTit {
            color: #fff;
            text-align: center;
            padding-top: 1rem;
            font-size: .66rem;
        }

        .fnName {
            font-size: .54rem;
            padding-top: .1rem;
        }

        .orderBtn, .loginBtn {
            font-size: .48rem;
            color: #fff;
            background: #25c875;
            position: absolute;
            bottom: -0.6rem;
            left: 50%;
            padding: .36rem .72rem;
            border-radius: 2rem;
            -webkit-border-radius: 2rem;
            transform: translateX(-50%);
            -webkit-transform: translateX(-50%);
        }

        .loginBtn {
            padding: .36rem 1.2rem;
        }


        .orderBtn:after {
            content: '';
            width: .56rem;
            height: .56rem;
            background: url(../img/actionIcon.png) no-repeat;
            background-size: 100% 100%;
            display: inline-block;
            vertical-align: middle;
            margin-left: .26rem;
            margin-top: -.1rem;
        }

        .pd40 {
            padding-left: .4rem;
            padding-right: .4rem;
            margin-bottom: .42rem;
        }

        .integralArea {
            color: #333;
            overflow: hidden;
        }

        .integralNum {
            font-size: .48rem;
            float: left;
            margin-top: .22rem;
        }

        .integralNum:before {
            content: '';
            width: .56rem;
            height: .5rem;
            background: url(../img/integral.png) no-repeat;
            background-size: 100% 100%;
            display: inline-block;
            vertical-align: middle;
            margin: -.12rem 0 0 0;
        }

        .integralLog {
            float: right;
            border: .02rem solid #ccc;
            background: #fff;
            padding: .26rem .48rem;
            border-radius: .1rem;
            font-size: .42rem;
        }

        .myOrderAreaIn {
            background: #fff;
            border-top: .16rem solid #25C875;
            border-radius: .2rem;
            box-shadow: 1px 1px 8px 0 #00000047;
        }

        .myOrderTop {
            padding: .4rem .38rem .38rem;
            font-size: .42rem;
            border-bottom: 2px dashed #e8e8e8;
            overflow: hidden;
        }

        .myOrderBot {
            padding: 0 .38rem;
        }

        .myOrdTit {
            color: #666;
        }

        .chekOrderLog {
            color: #24aaff;
            float: right;
        }

        .myOrderBot {
            position: relative;
            padding: .66rem 0.38rem .58rem;
            color: #000000;
            display: block;
        }

        .roomName {
            font-size: .48rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 72%;
        }

        .odTime {
            font-size: .42rem;
            padding-top: .1rem;
        }

        .roomIcon, .rightIcon {
            position: absolute;
            top: 50%;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            transform: translateY(-50%);
            -webkit-transform: translateY(-50%);
        }

        .roomIcon {
            width: .96rem;
            height: .96rem;
            background-image: url(../img/room.png);
            right: .76rem;
        }

        .rightIcon {
            width: .2rem;
            height: .34rem;
            background-image: url(../img/rightBlue.png);
            right: .38rem;
        }

        .rightIcon.gray {
            background-image: url(../img/rightGray.png);
        }

        .hotOrderArea {
            background: #fff;
            padding: .42rem 0 0 .38rem;
        }

        .hotOrderField {
            font-size: .48rem;
            color: #666;
            padding-right: .38rem;
        }

        .hotOrderField:before {
            content: '';
            width: .52rem;
            height: .56rem;
            background: url(../img/hot.png) no-repeat;
            background-size: 100% 100%;
            display: inline-block;
            vertical-align: middle;
            margin: -.1rem 0.3rem 0 0;
        }

        .fieldList li {
            border-bottom: 1px solid #e8e8e8;
        }

        .fieldLink {
            padding-left: 0;
            padding-right: 0;
        }

        .fieldLink .rightIcon {
            right: .78rem;
        }

        .mask {
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 2;
        }

        .integralHintIn {
            background: #fff;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            -webkit-ttransform: translate(-50%, -50%);
            z-index: 2;
            width: 92%;
            padding: 1.82rem 0 .84rem 0;
            border-radius: .4rem;
        }

        .inteHintCon {
            font-size: .54rem;
            color: #333;
            padding: 0 .4rem .98rem;
            text-align: center;
        }

        .closePop {
            width: .48rem;
            height: .48rem;
            background: url(../img/close.png) no-repeat;
            background-size: 100% 100%;
            position: absolute;
            top: .3rem;
            right: .3rem;
        }

        .knowBtn {
            text-align: center;
            font-size: .48rem;
            background: #f1f1f1;
            border: 1px solid #d8d8d8;
            padding: .25rem 0;
            display: block;
            width: 50%;
            margin: 0 auto;
        }

        .integralHint, .loginHint, .continueOrderArea {
            display: none;
        }

        .allOrd {
            float: right;
            color: #000;
        }

        .loginHint .integralHintIn {
            padding-top: 1.28rem;
            padding-bottom: 0;
        }

        .loginHint .inteHintCon, .continueOrderArea .inteHintCon {
            color: #666666;
            font-size: .48rem;
            text-align: left;
        }

        .fmGrop {
            padding-bottom: .46rem;
        }

        .fmTit {
            padding-bottom: .2rem;
        }

        .fmInp {
            width: 100%;
            border: 1px solid #b8d2e3;
            background: #fff;
            padding: .26rem 0 .26rem .2rem;
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
        }

        .loginSub {
            width: 100%;
            background: #20a6fd;
            color: #fff;
            font-size: .48rem;
            padding: .26rem 0;
            border-radius: .1rem;
            margin-top: .36rem;
            border: 1px solid #20a6fd;
        }

        /*integralLog start*/
        .logHead {
            height: 4.3rem;
            background: #f5f5f5;
            padding: 1.3rem 0;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1;

        }

        .inteTit {
            font-size: .48rem;
            color: #666666;
            padding-right: .12rem;
        }

        .inteNum {
            font-size: .66rem;
            color: #000000;
        }

        .logList {
            border-top: 1px solid #e8e8e8;
            margin-top: 1.58rem;
        }

        .logList li {
            border-bottom: 1px solid #e8e8e8;
            padding: .6rem 0.4rem;
            font-size: .48rem;
        }

        .logList li span:nth-of-type(1) {
            color: #000;
            padding-right: .4rem;
        }

        .logList li span:nth-of-type(2) {
            color: #999999;
            padding-right: .3rem;
        }

        .logList li span:nth-of-type(3) {
            color: #333333;
        }

        /*integralLog start*/

        /*chekOrderLog start*/
        .isOrderArea {
            background: #fff;
            padding: 0 .4rem .3rem;
            margin-bottom: .5rem;
        }

        .isOrderTit {
            font-size: .48rem;
            color: #666666;
            padding: .42rem 0 .32rem;
            margin-bottom: .32rem;
        }

        .isOrderTit:before {
            content: '';
            width: .56rem;
            height: .56rem;
            background: url(../img/holder.png) no-repeat;
            background-size: 100% 100%;
            display: inline-block;
            vertical-align: middle;
            margin: -0.1rem .26rem 0 0;
        }

        .signArea {
            background: #20a6fd;
            color: #fff;
            padding: .5rem .4rem;
            border-radius: .3rem;
            position: relative;
            box-shadow: 1px 1px 1px 1px rgba(0, 0, 0, 0.2);
            -webkit-box-shadow: 1px 1px 1px 1px rgba(0, 0, 0, 0.2);
        }

        .signBtn {
            color: #fff;
            font-size: .48rem;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            -webkit-transform: translateY(-50%);
            right: .4rem;
            border: .03rem solid #fff;
            padding: .2rem .46rem;
            border-radius: .1rem;
            background: #20a6fd;
        }

        .orderTmLine {
            overflow: hidden;
            font-size: .48rem;
            color: #000;
        }

        .listItem {
            position: relative;
            padding-left: 1.08rem;
            padding-top: .2rem;
        }

        .listItem:before {
            content: "";
            display: inline-block;
            position: absolute;
            top: 0;
            width: 1px;
            height: 100%;
            border-right: 1px dashed #b8d3e6;
            left: .4rem;
            z-index: 1;
        }

        .listItem-last:before {
            height: 50%;
        }

        .listItem:after {
            content: "";
            display: inline-block;
            position: absolute;
            width: .26rem;
            height: .26rem;
            background-color: #20a6fd;
            border-radius: 100%;
            left: .3rem;
            top: 50%;
            margin-top: -2px;
            z-index: 1;
        }

        .listItem.highlight .listItemContent-date {
            color: #fff;
        }


        .listItem-first:before {
            height: 50%;
            top: 50%;
        }

        .listItem-last:before {
            height: 50%;
        }

        .listItem.highlight:after {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            width: 16px;
            height: 16px;
            background-color: #3d93fd;
            border: 4px solid #88bdfe;
            border-radius: 100%;
            left: .2rem;
            -webkit-box-shadow: 0 0 0 3px #d8e9ff;
            box-shadow: 0 0 0 3px #d8e9ff;
            z-index: 2;
        }

        .listItem.highlight > .listItemContent {
            background-color: #20a6fd;
            color: #fff;
        }

        .listItemContent {
            border: 1px solid #f3f3f3;
            padding: .5rem .4rem;
            border-radius: .3rem;
        }

        .hisOrderTit {
            font-size: .48rem;
            color: #666;
            padding-bottom: .62rem;
        }

        .hisOrderTit:before {
            content: '';
            width: .52rem;
            height: .56rem;
            background: url(../img/history.png) no-repeat;
            background-size: 100% 100%;
            display: inline-block;
            vertical-align: middle;
            margin: -.1rem 0.28rem 0 0;
        }

        .hisLine {
            background: #fff;
            box-shadow: 1px 1px 1px 1px rgba(0, 0, 0, 0.2);
            -webkit-box-shadow: 1px 1px 1px 1px rgba(0, 0, 0, 0.2);
            border-radius: .4rem;
            position: relative;
            margin-bottom: .36rem;
        }

        .hisLineTop {
            padding: .52rem .4rem;
        }

        .hisLineBot, .hasSign, .hasCancel {
            font-size: .42rem;
            color: #666666;
        }

        .hisLineBot {
            width: 80%;
            border-top: 1px dashed #e8e8e8;
            padding: .56rem .4rem .5rem;
        }

        .hisRight {
            position: absolute;
            right: .52rem;
            top: 50%;
            transform: translateY(-50%);
            -webkit-transform: translateY(-50%);
        }

        .hasSign:before, .hasCancel:before {
            content: '';
            width: .96rem;
            height: .96rem;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            display: block;
            margin: 0 auto .16rem;
        }

        .hasSign:before {
            background-image: url(../img/signed.png);
        }

        .hasCancel:before {
            background-image: url(../img/cancel.png);
        }

        .disNone {
            display: none;
        }

        .highlight .disNone {
            display: block;
        }

        .continueOrderArea .integralHintIn {
            top: 35%;
            padding-top: 1.28rem;
            padding-bottom: 0;
        }

        .cancelBtn {
            color: #333;
            background: #f1f1f1;
            border: 1px solid #d8d8d8;
        }

        .tmGrop {
            padding-bottom: .32rem;
        }

        .tmInpArea {
            width: 100%;
            margin: 0 auto;
            height: 1rem;
            line-height: 1rem;
            overflow: hidden;
            text-align: center;
        }

        .tmInp {
            width: 44%;
            border: 1px solid #b8d2e3;
            border-radius: .1rem;
            overflow: hidden;
            text-align: center;
            height: 1rem;
            position: relative;
            overflow: hidden;
        }

        .firstTm {
            float: left;
        }

        .sencondTm {
            float: right;
        }

        .tmInp input {
            text-align: center;
            font-size: .48rem;
            width: 100%;
            font-family: "微软雅黑";
            height: 1rem;
            line-height: 1rem;
            padding-left: 24%;
            background: #fff;
        }

        .upArea {
            font-size: .48rem;
            color: #ccc;
            position: absolute;
            top: 0;
            left: 0;
            width: 98%;
            height: 1rem;
            line-height: 1rem;
            text-align: center;
            background: #fff;
            border-radius: .1rem;
        }

        /*chekOrderLog end*/

        /* orderStep01 start */
        .stepLine {
            width: 7.2rem;
        }

        .stepFont {
            width: 92%;
            margin: 0 auto;
            overflow: hidden;
        }

        .stepFont li {
            float: left;
            font-size: .36rem;
            width: 33.33%;
            margin: .42rem 0 0;
        }

        .stepFont li a {
            color: #999999;

        }

        .stepFont li.curr a {
            color: #333333;
        }

        .wrapper01, .wrapper02 {
            position: fixed;
            top: 4.2rem;
            left: 0;
            height: 44px;
            width: 100%;
            overflow: hidden;
            margin: 0 auto .38rem;
            border-bottom: 1px solid #d8d8d8;
            background: #fff;
            z-index: 3;
        }

        .wrapper01 .scroller {
            position: absolute;
        }

        .wrapper01 .scroller li {
            /*height:.9rem;*/
            color: #375369;
            float: left;
            line-height: 41px;
            font-size: .48rem;
            text-align: center;
        }

        .wrapper01 .scroller li a {
            color: #336699;
            display: block;
            padding: 0 .55rem;
            margin-right: .1rem;
            font-size: .48rem;
        }

        .wrapper01 .scroller li.cur a {
            color: #20a6fd;
            border-bottom: 2px solid #20a6fd;
        }

        .roomConArea .hotOrderArea {
            margin-top: 5.2rem;
        }

        /* orderStep01 end */
        /* orderStep02 start */
        .calendarArea {
            /*margin-top: 4.3rem;*/
        }

        .useTm {
            width: 100%;
            border-top: 1px dashed #e8e8e8;
            font-size: .48rem;
            padding: .62rem .48rem;
            margin-bottom: .5rem;
        }

        .btBtnArea {
            /*position:fixed;*/
            bottom: 0;
            left: 0;
            width: 100%;
            font-size: 0;
        }

        .btBtnArea button {
            width: 50%;
            display: inline-block;
            text-align: center;
            font-size: .42rem;
            padding: .42rem 0;
        }

        .btBtnArea button:first-of-type {
            color: #666666;
            background: #f1f1f1;
            border-top: 1px solid #d8d8d8;

        }

        .btBtnArea button:last-of-type {
            color: #fff;
            background: #20a6fd;
            border-top: 1px solid #20a6fd;
        }

        .hasSta {
            position: static;
        }

        .step02TmArea {
            width: 70%;
            display: inline-block;
            vertical-align: middle;
        }

        .step02Tm {
            width: 41%;
        }

        /* orderStep02 end */

        /* orderStep03 start */
        .siteMapArea {
            /*position:fixed;*/
            /*top: 4.3rem;*/
        }


        .siteMapCon {
            width: 100%;
            height: 6.59rem;
            position: relative;
        }

        .siteMapImg {
            width: 100%;
            height: 6.59rem;
        }

        .numHint {
            position: absolute;

        }

        .numCon {
            width: .8rem;
            height: .8rem;
            line-height: .7rem;
            text-align: center;
            color: #20a6fd;
            background: #fff;
            border: 2px solid #20a6fd;
            font-size: .48rem;
            border-radius: 100%;
            position: absolute;
        }

        .numCon:nth-of-type(1) {
            bottom: 1rem;
            left: 3rem;
        }

        .numCon:nth-of-type(2) {
            bottom: 4rem;
            left: 6rem;
        }

        .numCon:nth-of-type(3) {
            bottom: 4.5rem;
            left: 8rem;
        }

        .numCon:nth-of-type(4) {
            bottom: 2.6rem;
            left: 8rem;
        }

        .numCon:nth-of-type(5) {
            bottom: 1rem;
            left: 6.9rem;
        }


        .wrapper02 {
            position: relative;
            top: 0;
            height: 1.5rem;
            margin-bottom: .3rem;
        }

        .wrapper02 .scroller {
            position: absolute;
        }

        .wrapper02 .scroller li {
            /*height:.9rem;*/
            color: #375369;
            float: left;
            line-height: 1rem;
            font-size: .48rem;
            text-align: center;
            padding-top: .2rem;
        }

        .wrapper02 .scroller li a {
            color: #333333;
            display: block;
            /*margin:0 .36rem;*/
            padding: 0 .6rem;
            margin-right: .36rem;
            font-size: .48rem;
            border-radius: .2rem;
            border: 1px solid #e5e5e5;
        }

        .wrapper02 .scroller li:first-of-type a {
            margin-left: .4rem;
        }

        .wrapper02 .scroller li.cur a {
            color: #fff;
            background: #20a6fd;
            border: 1px solid #20a6fd;
        }

        .siteHintList, .ohterList {
            width: 80%;
            margin: 0 auto .32rem;
            overflow: hidden;
        }

        .siteHintList li, .ohterList li {
            float: left;
            font-size: .36rem;
            color: #666666;
            margin-right: .58rem;
        }

        .siteHintList li:last-of-type, .ohterList li:last-of-type {
            margin-right: 0;
        }

        .siteHintList li::before, .ohterList li::before {
            content: '';
            display: inline-block;
            vertical-align: middle;
        }

        .siteHintList li::before {
            width: .4rem;
            height: .4rem;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            margin: -.14rem .14rem 0 0;
        }

        .siteHintList li:nth-of-type(1)::before {
            background-image: url(../img/siteChose.png);
        }

        .siteHintList li:nth-of-type(2)::before {
            background-image: url(../img/siteOrder.png);
        }

        .siteHintList li:nth-of-type(3)::before {
            background-image: url(../img/siteChosed.png);
        }

        .siteHintList li:nth-of-type(4)::before {
            background-image: url(../img/sitReserve.png);
        }

        .ohterList {
            width: 60%;
            margin-bottom: .5rem;
        }

        .ohterList li::before {
            content: '';
            width: .26rem;
            height: .26rem;
            border-radius: 100%;
            margin: 0 .24rem 0;
        }

        .ohterList li:nth-of-type(1)::before {
            background: #20a6fd;
        }

        .ohterList li:nth-of-type(2)::before {
            background: #6bd3ee;
        }

        .ohterList li:nth-of-type(3)::before {
            background: #f7bb73;

        }

        .siteArea {
            width: 100%;
            height: 10rem;
            position: relative;
            overflow: auto;
        }

        .screen {
            background: #ddd;
            font-size: .36rem;
            text-align: center;
            width: 60%;
            margin: 0 auto .3rem;
            border-radius: .1rem;
            padding: .02rem 0;

        }

        .siteListArea {
            position: absolute;
            /*width:200%;*/
            width: 184%;
        }

        .yellow {
            width: 300px;
            height: 300px;
            background: blue;
            position: absolute;
            left: 0;
            top: 0;
        }

        .siteListAreaIn {
            width: 100%;
        }

        .siteLineNum {
            /*width:4%;*/
            width: 2%;
            height: 100%;
            position: absolute;
            left: .2rem;
            top: 0;
            background: rgba(0, 0, 0, 0.2);
            border-radius: .2rem;
            color: #fff;
            z-index: 1;
        }

        .siteLineNum li {
            position: absolute;
            /*top: 1rem;*/
            left: 50%;
            -webkit-transform: translateX(-50%);
            -moz-transform: translateX(-50%);
            -ms-transform: translateX(-50%);
            -o-transform: translateX(-50%);
            transform: translateX(-50%);

        }

        .siteLineNum li:nth-of-type(1) {
            /*top: .3rem;*/
            top: 8%;
        }

        .siteLineNum li:nth-of-type(2) {
            /*top: 1.5rem;*/
            top: 28%;
        }

        .siteLineNum li:nth-of-type(3) {
            /*top: 2.7rem;*/
            top: 48%;
        }

        .siteLineNum li:nth-of-type(4) {
            /*top: 3.9rem;*/
            top: 68%;
        }

        .siteLineNum li:nth-of-type(5) {
            /*top: 5.1rem;*/
            top: 88%;
        }

        .siteList {
            margin-left: .5rem;
            overflow: hidden;
        }

        .siteList li {
            float: left;
        }


        .siteList li:nth-of-type(6n) {
            margin-right: .3rem;
        }


        .sitePos {
            /*width:.64rem;
            height:.60rem;*/
            width: 1.24rem;
            height: 1.2rem;
            background: url(../img/siteChose.png) no-repeat;
            background-size: 100% 100%;
            /*margin: .3rem.05rem;*/
            margin: .32rem .1rem;
            text-align: center;
            /*color: rgba(0, 0, 0, 0.0);*/
            color: #fff;
            position: relative;
            font-size: .32rem;
        }

        .siteOrder {
            background-image: url(../img/siteOrder.png);
        }


        .sitReserve {
            background-image: url(../img/sitReserve.png);
        }


        .siteChosed {
            background-image: url(../img/siteChosed.png);
        }


        .smHint {
            width: 1.24rem;
            height: .26rem;
            text-align: center;
            position: absolute;
            top: -0.4rem;
        }

        .hasPover, .hasWin, .hasOther {
            width: .26rem;
            height: .26rem;
            border-radius: 100%;
            display: inline-block;
            /* position:absolute;
            left:50%;
            -webkit-transform: translateX(-50%);
               -moz-transform: translateX(-50%);
                -ms-transform: translateX(-50%);
                 -o-transform: translateX(-50%);
                    transform: translateX(-50%);*/
        }

        .hasPover {
            background: #20a6fd;
        }

        .hasPover.two {
            left: .2rem;
        }

        .hasWin {
            background: #6bd3ee;
        }

        .hasWin.two, .hasOther.two {
            left: .45rem;
        }

        .hasOther {
            background: #f7bb73;

        }


        .fangda {
            -webkit-transform: scale3d(2, 2, 2);
            -moz-transform: scale3d(2, 2, 2);
            -ms-transform: scale3d(2, 2, 2);
            -o-transform: scale3d(2, 2, 2);
            transform: scale3d(2, 2, 2);
        }

        .maxNum {
            background: rgba(0, 0, 0, 0.8);
            font-size: .38rem;
            color: #fff;
            padding: .1rem .4rem;
            border-radius: .2rem;
            position: fixed;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            -moz-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            -o-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            display: none;
            z-index: 6;
        }

        .location {
            font-size: .12rem;
        }

        /* orderStep03 end */
    </style>
    <script>
        (function (win, doc) {
            var docEl = doc.documentElement,
                design = 1080;
            var resizeEvt = "orientationchange" in win ? "orientationchange" : "resize";
            var recale = function () {
                var clientWidth = docEl.clientWidth;
                if (!clientWidth) return;
                docEl.style.fontSize = 100 * (clientWidth / design) + "px";
            }
            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recale, false);
            docEl.addEventListener("DOMContentLoaded", recale, false);
            recale();
        })(window, document)

    </script>
    <script src="https://cdn.staticfile.org/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
</head>
<body class="hasBgColor">
<div style="text-align: center;padding-top: 10px">
    <div id="qrcode"></div>
    <span>我的推广二维码</span>
</div>
<div style="text-align: center;padding-top: 10px">
    <span>总邀请人数:</span>{{count($qrCodeLogId)}}
</div>
<ul class="logList">
    @foreach($qrCodeLogId as $val)
        <li>
            <span>  <img style="width: 20px" src="{{$val->fans->headimgurl}}" alt=""> {{$val->fans->nickname}}</span>
            <span>邀请时间:{{$val['created_at']}}</span>
        </li>
    @endforeach

</ul>
</body>
<script>
    jQuery(function () {
        jQuery('#qrcode').qrcode("{{$bindQrReader['url']}}");
    })
</script>
</html>
