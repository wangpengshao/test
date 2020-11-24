$(function () {
    let $ring = $(".ring"),
        $prize = $(".prize"), //转盘
        $btn = $("#btn"), //按钮
        $change = $("#change"), //显示剩余抽奖机会
        $li = $(".scroll li"), //中奖信息滚动的盒子
        $sNum = $(".start-num"), //手机头号，三位数
        $eNum = $(".end-num"), //手机尾号，四位数
        $info = $(".info"), //中奖提示信息
        bool = false, //判断是否在旋转，true表示是，false表示否
        timer; //定时器
    init();
    firstShowRule();

    function init() {
        timer = setInterval(function () {
            $ring.toggleClass("light");
        }, 1000);
    }

    function firstShowRule() {
        //首次进来 默认 展示抽奖规则
        let key = 'LuckyDraw01:first:' + token + ':' + config['id'];
        let isFirst = localStorage.getItem(key);
        if (isFirst == null) {
            $maskRule.show();
            localStorage.setItem(key, '1')
        }
    }

    //点击抽奖
    $btn.click(function () {
        // 开始抽奖判断
        if (config.status !== 1) {
            return showPrompts('抱歉,活动已经关闭了!')
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
            return showPrompts('抱歉,请先绑定读者证才能参与活动!', 0, function () {
                window.location.href = bindUrl;
            }, '前往绑定');
        }
        if (config['is_bind'] == 1 && !$.isEmptyObject(reader) && config['integral'] > reader['integral']) {
            return showPrompts('抱歉,您的积分不足 ' + config['integral'] + ' ,无法参与抽奖！');
        }
        //判断是否需要收集信息
        if (config['gather'][0] !== '' && (gatherId === '' || gatherId === null)) {
            return showGatherForm(config['gather']);
        }

        if (bool) return; // 如果在执行就退出
        bool = true; // 标志为 在执行
        if (allowNumber <= 0) { //当抽奖次数为0时
            $change.html(0); //次数显示为0
            bool = false;
            return showPrompts('抱歉,您的抽奖次数已经用完了');
        } else { //还有次数就执行
            allowNumber--;
            allowNumber <= 0 && (allowNumber = 0);
            $change.html(allowNumber); //显示剩余次数
            $prize.removeClass("running");
            toDraw();
        }
    });

    //选中函数。参数：奖品序号、角度、提示文字
    function rotateFn(awards, angle, prize) {
        /*手机号的处理
        var arr = [13477735912, 13100656035, 15926909285];
        var a = arr[0] + "";
        var f = a.substr(0, 3);
        var l = a.substr(7, 4);*/
        console.log(angle);
        angle = 360 - angle;
        console.log(angle);
        bool = true;
        $prize.stopRotate();
        $prize.rotate({
            angle: 0, //旋转的角度数
            duration: 6000, //旋转时间
            animateTo: angle + 1800, //给定的角度,让它根据得出来的结果加上1440度旋转。也就是至少转4圈
            callback: function () {
                bool = false; // 标志为 执行完毕
                if (awards !== 0) {
                    win(prize); //中奖函数
                    // show(1, 1, text);
                    return;
                }
                noWin(); //没有中奖函数
            }
        });


    }

    //中奖信息滚动。前两个参数为手机号前三位和后四位手机尾号，text为中的什么奖品
    function show(sNum, eNum, text) {
        //最新中奖信息
        $sNum.eq(2).html(sNum);
        $eNum.eq(2).html(eNum);
        $info.eq(2).html(text);
        $li.css("top", "-" + 40 / 75 + "rem"); //滚动
        //滚动之后的处理
        setTimeout(function () {
            $li.css({
                "top": "0",
                "transition": "all 0s ease-in-out"
            });
            //更新中奖信息
            $sNum.eq(0).html($sNum.eq(1).html());
            $eNum.eq(0).html($eNum.eq(1).html());
            $info.eq(0).html($info.eq(1).html());
            $info.eq(1).html($info.eq(2).html());
            $sNum.eq(1).html($sNum.eq(2).html());
            $eNum.eq(1).html($eNum.eq(2).html());
        }, 500);
        $li.css("transition", "all 0.5s ease-in-out");
    }

    movedome();

    function movedome() {
        var margintop = 0; //上边距的偏移量
        var stop = false;
        $li.css("top", "-" + 40 / 75 + "rem"); //滚动
        //滚动之后的处理
        setTimeout(function () {
            $li.css({
                "top": "0",
                "transition": "all 0s ease-in-out"
            });
        }, 500);
        $li.css("transition", "all 0.5s ease-in-out");
        setInterval(function () {
            if (stop == true || $('#infoScroll li').length <= 2) {
                return;
            }
            $("#infoScroll").children("li").first().animate({
                "margin-top": margintop--
            }, 0, function () {
                var $li = $(this);
                if (!$li.is(":animated")) { //第一个li的动画结束时
                    if (-margintop > $li.height()) {
                        $li.css("margin-top", "0px").appendTo($("#infoScroll"));
                        margintop = 0;
                    }
                }
            });
        }, 50);
    }

    //中奖信息提示
    $("#close,.win,.btn").click(function () {
        $prize.addClass("running");
        init();
    });
    $("#sureGet").click(function () {
        $("#inputShade").addClass("hidden")
    });
    $("#infoClose").click(function () {
        $("#inputShade").addClass("hidden")
    });
    $("#wordsSure").click(function () {
        $("#words").addClass("hidden")
    });
    $("#wordsCancel").click(function () {
        $("#words").addClass("hidden")
    });

    //进行抽奖
    function toDraw() {
        //先转起来
        let formJson = {gatherId: gatherId, l_id: config['id']};
        let loadingAngle = 0;
        let loadingTiming = setInterval(function () {
            loadingAngle += 9;
            $prize.rotate(loadingAngle);
        }, 50);

        $.ajax({
            type: 'POST',
            url: toDrawUrl,
            dataType: "json",
            data: formJson,
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            success: function (response) {
                //外部抽奖资格验证,不符合
                if (response.status == 'Wait') {
                    window.clearInterval(loadingTiming);
                    showPrompts(response.data.message, 1, function () {
                        window.location.href = response.data.url;
                    }, response.data.buttonName);
                    return;
                }

                if (response.status == true) {
                    let newAngle = null;
                    let newId = '';
                    let newText = '';
                    prize.some(function (e, index) {
                        if (e.id == response.data.id) {
                            newAngle = e.angle;
                            newId = e.id;
                            newText = e;
                            return true;
                        }
                    });
                    if (newAngle != null) {
                        window.clearInterval(loadingTiming);
                        rotateFn(newId, newAngle, newText);
                    } else {
                        alert('系统繁忙,请稍后再试!')
                    }
                    return;
                }
                window.clearInterval(loadingTiming);
                showPrompts(response.data.message, 1, function () {
                    window.location.reload();
                });
            },
            error: function (e) {
                alert('服务繁忙,请稍后再试！')
            }
        });
    }

});
