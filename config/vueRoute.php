<?php
return [
    //绑定读者页面 {token}/{ID}
    'bindReader' => env('WEB_VUE_HOST') . '/#/inBindReader/{token}',
    //图文详情页面 {token}/{ID}
    'showImgContent' => env('WEB_VUE_HOST') . '/#/newsDetail/{token}/',
    //自助机扫码登录 {token}/{uuid}
    'selfLogin' => env('WEB_VUE_HOST') . '/#/login/{token}/',
    //首页 {token}
    'index' => env('WEB_VUE_HOST') . '/#/index/{token}',
    //实名办证
    'lv2Certificate' => env('WEB_VUE_HOST') . '/#/authentication/{token}',
    //普通办证
    'lv1Certificate' => env('WEB_VUE_HOST') . '/#/GeneralAcc/{token}',
    //书目检索
    'search' => env('WEB_VUE_HOST') . '/#/search/{token}',
    //个人中心
    'personal' => env('WEB_VUE_HOST') . '/#/personal/{token}',
    //读者证挂失
    'reportCard' => env('WEB_VUE_HOST') . '/#/reportCard/{token}',
    //活动列表
    'actList' => env('WEB_VUE_HOST') . '/#/actList/{token}',
    //在借中
    'booklist2' => env('WEB_VUE_HOST') . '/#/booklist/{token}?id=2',
    //借阅清单
    'booklist1' => env('WEB_VUE_HOST') . '/#/booklist/{token}?id=1',
    //预约列表
    'appointBook' => env('WEB_VUE_HOST') . '/#/appointBook/{token}',
    //预借列表
    'borrowBook' => env('WEB_VUE_HOST') . '/#/borrowBook/{token}',
    //缴纳欠费
    'payArrears' => env('WEB_VUE_HOST') . '/#/payment/{token}',
    //代付欠款
    'paycost' => env('WEB_VUE_HOST') . '/#/paycost/{token}',
    //二维码电子证
    'ewrpage' => env('WEB_VUE_HOST') . '/#/ewrpage/{token}',
    //读者借阅排行
    'readerRankings' => env('WEB_VUE_HOST') . '/#/readerRankings/{token}',
    //图书借阅排行
    'bookRankings' => env('WEB_VUE_HOST') . '/#/bookRankings/{token}',
    //资源菜单
    'menuClassify' => env('WEB_VUE_HOST') . '/#/directory/{token}',
    //新书通报
    'newBookShow' => env('WEB_VUE_HOST') . '/#/newBookShow/{token}',
];
