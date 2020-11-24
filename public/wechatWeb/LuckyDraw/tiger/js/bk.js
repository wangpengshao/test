$(function () {
    // var prizeArr = ["豪华礼包", "索尼相机", "平板电脑"],//奖品内容
    let $li = $(".info li"),//中奖信息滚动的盒子
        $sNum = $(".start-num"),//手机头号，三位数
        $eNum = $(".end-num"),//手机尾号，四位数
        $info = $(".prize"),//中奖提示信息
        $go = $("#go"),
        $hand = $("#hand"),
        $roll = $(".roll ul"),
        $change = $("#change"), //显示剩余抽奖机会
        topRoll = parseInt($roll.eq(0).css("top")),//滚动盒子的top
        prizeHigh = parseInt($(".prize1").eq(0).height()),//奖品高度
        bool = true,//true为可点击
        clickTimer, timer1, timer2, timer3;

    //开始按钮
    $go.click(function () {
        if (!bool) return;  // 如果在执行就退出
        if (config.status !== 1) {
            return showPrompts('抱歉,活动已经关闭了!');
        }
        //比较时间
        let start_date = config.start_at.replace(/\-/g, '/');
        let end_date = config.end_at.replace(/\-/g, '/');
        start_date = new Date(start_date);
        end_date = new Date(end_date);
        let now_date = new Date();
        if (now_date < start_date) {
            return showPrompts('抱歉,活动尚未开始!');
        }
        if (now_date >= end_date) {
            return showPrompts('抱歉,活动已结束!', 1, function () {
                window.location.reload();
            });
        }
        //判断是否需要关注
        if (config['is_subscribe'] == 1 && fansInfo['subscribe'] != 1) {
            return showQrCode();
        }
        //判断是否需要绑定
        if (config['is_bind'] == 1 && $.isEmptyObject(reader)) {
            // 进行跳转到绑定页面  进行绑定
            return showPrompts('抱歉,请先绑定读者证才能参与活动!', 1, function () {
                window.location.reload();
            });
        }
        if (config['is_bind'] == 1 && !$.isEmptyObject(reader) && config['integral'] > reader['integral']) {
            return showPrompts('抱歉,您的积分不足 ' + config['integral'] + ' ,无法参与抽奖！');
        }
        //判断是否需要收集信息
        if (config['gather'][0] !== '' && gatherId == '') {
            return showGatherForm(config['gather']);
        }
        bool = false;

        if (allowNumber <= 0) { //当抽奖次数为0时
            $change.html(0); //次数显示为0
            bool = true;
            return showPrompts('抱歉,您的抽奖次数已经用完了');
        } else { //还有次数就执行
            allowNumber--;
            allowNumber <= 0 && (allowNumber = 0);
            $change.html(allowNumber); //显示剩余次数
            clearTimeout(clickTimer);
            $hand.removeClass("shak");//移除手摇晃动画
            $hand.animate({
                left: 50 + "%",
                top: 40 + "%"
            }, 500, function () {
                $hand.css("transform", "rotate(-20deg)");//按下按钮
                clickTimer = setTimeout(function () {
                    $hand.css("transform", "rotate(0deg)");//抬起
                    $hand.animate({
                        left: 60 + "%",
                        top: 50 + "%"
                    }, 500, function () {
                        $hand.addClass("shak");
                    });
                }, 300);
                // toDraw()
                clickFn();
            });
        }

    });

    //点击滚动
    let waitTimer1, waitTimer2, waitTimer3, topArr = [], topLength;
    let controlSwitch = [];
    initSwitch();
    // controlSwitch["stopAnimate0"] = 0;
    // controlSwitch["stopAnimate1"] = 0;
    // controlSwitch["stopAnimate2"] = 0;
    // controlSwitch["circle0"] = 0;
    // controlSwitch["circle1"] = 0;
    // controlSwitch["circle2"] = 0;

    topLength = prize.length + 5;
    for (var i = 0; i < topLength; i++) {
        topArr[i] = topRoll - (prizeHigh * i);
    }

    function clickFn() {
        clearInterval(timer1);  //点击抽奖时清除定时器
        clearInterval(timer2);
        clearInterval(timer3);
        waitTimer1 = setInterval(animate(0, 40, 0), 95);
        waitTimer2 = setInterval(animate(0, 40, 1), 100);
        waitTimer3 = setInterval(animate(0, 40, 2), 105);
        toDraw();
    }

    function ourFunction(ourMin, ourMax) {
        return Math.floor(Math.random() * (ourMax - ourMin + 1)) + ourMin;
    }

    function showResult(id) {
        clearInterval(waitTimer1);
        clearInterval(waitTimer2);
        clearInterval(waitTimer3);

        let r1 = ourFunction(1, 3);
        let r2 = ourFunction(3, 5);
        let r3 = ourFunction(1, topLength);
        // r3=6;

        // if (id !== 0) {
        //
        // } else {
        //     // //不中奖
        //     // parseInt(Math.random()*(max-min+1)+min,10);
        // }

        let random1 = 41, random2 = 42, random3 = 43;
        for (let i = 1; i <= random1; i++) {
            timer1 = setTimeout(animate(r1, i, 0), 1.5 * i * i);//第二个值越大，慢速旋转时间越长
        }
        for (let i = 1; i <= random2; i++) {
            timer2 = setTimeout(animate(r2, i, 1), 2.5 * i * i);//第二个值越大，慢速旋转时间越长
        }
        for (let i = 1; i <= random3; i++) {
            timer3 = setTimeout(animate(r3, i, 2), to * i * i);//第二个值越大，慢速旋转时间越长
        }

    }

    //滚动动画
    function animate(r, i, index) {//随机数、循环数，滚动盒子下标
        if (r === 0) {
            return function () {
                topRoll = parseFloat($roll.eq(index).css("top")) - prizeHigh / 2;//减去每个奖品高的一半
                if (topRoll <= topArr[topLength - 1]) {
                    topRoll = topArr[0];
                }
                $roll.eq(index).css("top", topRoll + "px");
            }
        }
        return function () {
            topRoll = parseFloat($roll.eq(index).css("top")) - prizeHigh / 2;//减去每个奖品高的一半
            if (topRoll <= topArr[topLength - 1]) {
                topRoll = topArr[0];
                controlSwitch["circle" + index]++;
            }
            if (controlSwitch["stopAnimate" + index] != 1) {
                $roll.eq(index).css("top", topRoll + "px");
            }

            if (controlSwitch["circle" + index] == 2 && topRoll == topArr[r - 1]) {
                controlSwitch["stopAnimate" + index] = 1;
                $roll.eq(index).css("top", topArr[r - 1] + "px");
                //最后一个停止的时候显示中奖提示 && 重置参数
                if (index == 2) {
                    clearTimeout(timer3);
                    console.log('停止了!');
                }

            } else {
                r + 40 === i && $roll.eq(index).css("top", topArr[r - 1] + "px");//循环到最后一次，设定top
            }

        }
    }

    function initSwitch() {
        controlSwitch["stopAnimate0"] = 0;
        controlSwitch["stopAnimate1"] = 0;
        controlSwitch["stopAnimate2"] = 0;
        controlSwitch["circle0"] = 0;
        controlSwitch["circle1"] = 0;
        controlSwitch["circle2"] = 0;
    }

    function toDraw() {
        //先转起来
        let formJson = {gatherId: gatherId, l_id: config['id']};
        $.ajax({
            type: 'POST',
            url: toDrawUrl,
            dataType: "json",
            data: formJson,
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            success: function (response) {
                if (response.status == true) {
                    showResult(response.data.id);
                    return;
                }
                // window.clearInterval(loadingTiming);
                showPrompts(response.data.message, 1, function () {
                    window.location.reload();
                });
            },
            error: function (e) {
                alert('服务繁忙,请稍后再试！')
            }
        });
    }


    //中奖信息滚动。前两个参数为手机号前三位和后四位手机尾号，text为中的奖品
    // function roll(sNum, eNum, text) {
    //     //最新中奖信息
    //     $sNum.eq(1).html(sNum);
    //     $eNum.eq(1).html(eNum);
    //     $info.eq(1).html(text);
    //     $li.css("top", "-" + 36 / 75 + "rem");//滚动
    //     //滚动之后的处理
    //     setTimeout(function () {
    //         $li.css({
    //             "top": "0",
    //             "transition": "all 0s ease-in-out"
    //         });
    //         //更新中奖信息
    //         $sNum.eq(0).html($sNum.eq(1).html());
    //         $eNum.eq(0).html($eNum.eq(1).html());
    //         $info.eq(0).html($info.eq(1).html());
    //     }, 500);
    //     $li.css("transition", "all 0.5s ease-in-out");
    // }

    //中奖信息提示

    $("#close,.win,.btn").click(function () {
        bool = true;
    });

    //奖品展示
    var show = new Swiper(".swiper-container", {
        direction: "horizontal",//水平方向滑动。 vertical为垂直方向滑动
        loop: false,//是否循环
        slidesPerView: "auto"//自动根据slides的宽度来设定数量
    });
});
