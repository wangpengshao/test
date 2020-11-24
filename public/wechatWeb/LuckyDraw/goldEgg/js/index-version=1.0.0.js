$(function () {
    let $hammer = $("#hammer"),
        $tips = $(".info"),
        // $eggList = $(".egg li"),//金蛋父级
        $egg = $(".goldegg"),//金蛋
        $change = $("#change"),//剩余次数
        length = $egg.length,
        rem = 75,
        bool = false; //开馆

    firstShowRule();

    function firstShowRule() {
        //首次进来 默认 展示抽奖规则
        let key = 'LuckyDraw03:first:' + token + ':' + config['id'];
        let isFirst = localStorage.getItem(key);
        if (isFirst == null) {
            $maskRule.show();
            localStorage.setItem(key, '1')
        }
    }

    /*轮流提示*/
    $(function () {
        //初始跳动
        $egg.eq(length).addClass("jump");
        $tips.eq(length).show();
        setInterval(function () {
            //金蛋跳动
            length++;
            length %= 9;
            $egg.eq(length - 1 < 0 && 8 || length - 1).removeClass("jump");
            $tips.eq(length - 1 < 0 && 8 || length - 1).hide();
            if ($('.init').length === 0) {
                return false;
            }
            reback();
            $egg.eq(length).addClass("jump");
            $tips.eq(length).show();

        }, 1000);
    });

    //跳过砸开的金蛋
    function reback() {
        if (!$egg.eq(length).hasClass("init")) {//若已砸开
            length++;
            length %= 9;
            reback();
        }
    }

    $egg.click(function () {
        if (bool) return;
        if (!$(this).hasClass('init')) {
            return showPrompts('这枚金蛋已经被您砸开了');
        }
        if (allowNumber <= 0) {
            return showPrompts('您当前可砸蛋次数为0，无法砸蛋');
        }
        if (config.status !== 1) {
            return showPrompts('抱歉,活动已经关闭了!')
        }
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
        //开始敲
        bool = true;
        eggChange($(this).data('rank'))
    })

    /*砸蛋事件的处理*/
    function eggChange(i) {
        //砸蛋次数的变化
        allowNumber--;
        $change.html(allowNumber);
        $hammer.removeClass("shak");//清除锤子晃动动画
        //锤子砸蛋的位置
        (i === 0 || i === 3 || i === 6) && ($hammer.css("left", 165 / rem + "rem"));
        (i === 1 || i === 4 || i === 7) && ($hammer.css("left", 415 / rem + "rem"));
        (i === 2 || i === 5 || i === 8) && ($hammer.css("left", 665 / rem + "rem"));
        (i === 0 || i === 1 || i === 2) && ($hammer.css("top", 60 / rem + "rem"));
        (i === 3 | i === 4 || i === 5) && ($hammer.css("top", 280 / rem + "rem"));
        (i === 6 | i === 7 || i === 8) && ($hammer.css("top", 500 / rem + "rem"));
        //开始敲&&请求接口
        setTimeout(function () {
            $hammer.addClass("hit");
        }, 600);
        //金蛋破裂及锤子动画
        toDraw(i);
    }

    function showResult(data, i) {
        //蛋开始裂
        setTimeout(function () {
            $hammer.addClass("hit");
            $egg.eq(i).prop("src", step1);
            setTimeout(function () {
                $egg.eq(i).prop("src", step2);
            }, 300);
            setTimeout(function () {
                if (data['id'] !== 0) {
                    $egg.eq(i).prop("src", data['image']);
                } else {
                    $egg.eq(i).prop("src", step4);
                }
                $egg.eq(i).removeClass("init");
                $hammer.removeClass("hit");//清除锤子砸蛋动画
                $hammer.addClass("shak");
                goBack(data);
                bool = false;
            }, 600);
        }, 600);
    }
    //锤子返回
    function goBack(data) {
        setTimeout(function () {
            $hammer.css("left", 665 / rem + "rem");
            $hammer.css("top", 60 / rem + "rem");
            if (data['id'] !== 0) {
                return win(data);
            }
            // noWin();
        }, 100);
    }

    function toDraw(i) {
        let formJson = {gatherId: gatherId, l_id: config['id'], rank: i};
        let loadingAngle = 0;
        let loadingTiming = setInterval(function () {
            loadingAngle += 9;
            $tips.rotate(loadingAngle);
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
                    showResult(response.data, i);
                    return;
                }
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
