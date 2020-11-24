// function to(con) {
//     var $object = $(con);
//     var aindex = $object.index();
//     window.location.href = "myCard.html?values=" + aindex;
// }
let temp;
function noScroll() {
    let top = $(window).scrollTop();
    temp = top;
    $('#pageCon').css("top", -top + "px");
    $('#pageCon').addClass('addPf');
    $('#pageCon').css('width', document.documentElement.clientWidth);
}

function canScroll() {
    $('#pageCon').removeClass('addPf'); //去掉给div的类
    $(window).scrollTop(temp); //设置页面滚动的高度，如果不设置，关闭弹出层时页面会回到顶部。
}


//倒计时
function timer(intDiff) {
    let nextime = intDiff;
    let intervalId = window.setInterval(function () {
        nextime--;
        let day = 0,
            hour = 0,
            minute = 0,
            second = 0; //时间默认值
        if (nextime > 0) {
            day = Math.floor(nextime / (60 * 60 * 24)); //天
            hour = Math.floor(nextime / (60 * 60)) - (day * 24); //小时
            minute = Math.floor(nextime / 60) - (day * 24 * 60) - (hour * 60); //分钟
            second = Math.floor(nextime) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60); //秒
        }
        if (hour <= 9)
            hour = '0' + hour;
        if (minute <= 9)
            minute = '0' + minute;
        if (second <= 9)
            second = '0' + second;

        $(".timespan").html(day + "天" + hour + "小时" + minute + "分钟" + second + "秒");

        if (nextime == 0) {
            clearInterval(intervalId);
            location.reload();
            $('.actTime').text("活动已结束");
        }
    }, 1000);
}

$(function () {
    if (typeof time_diff != 'undefined' && time_diff > 0 && start_at_diff < 0) {
        timer(time_diff);
    }

    $('.toSucCheckIdx').click(function () {
        canScroll();
        $('.redPacPop').hide();
        $('.timePack').hide();
        $('.hasMoney').show();
        setTimeout(function () {
            $('.rankListArea').slideDown();
        }, 1000);
    });
    $('.toFailCheckIdx').click(function () {
        canScroll();
        $('.redPacFailPop').hide();
        $('.timePack').hide();
        $('.noMoney').show();
        setTimeout(function () {
            $('.rankListArea').slideDown();
        }, 1000);
    });


    $('.shareMyGetCard .colCardUl li').each(function (index, el) {
        $(this).click(function () {
            if (!$(this).hasClass('hasTap')) {
                $(this).addClass('hasTap');
                $(this).find(".frontImg").children().addClass("turn");
                $(this).find(".backImg").children().addClass('turn2');
            }
            ;
        });
    });

    $('.closeBtn').click(function () {
        canScroll();
        $('.popArea').hide();
        $('.excCardUl li').removeClass('curChose');
        $('.confExcBtn').addClass('notChose');
    });

    $('.excImple').click(function (event) {
        $('.excPopArea').show();
    });

    $('.excCardUl li').click(function () {
        $(this).addClass('curChose').siblings().removeClass('curChose');
        $('.confExcBtn').removeClass('notChose');
    });

    $('.confExcBtn').click(function () {
        $('.excPopArea').hide();
        $('.excSucPopArea').show();
    });
});
