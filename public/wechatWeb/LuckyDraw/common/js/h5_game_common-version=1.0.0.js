var $maskRule = $("#mask-rule"), //规则遮罩层
    $mask = $("#mask"), //红包遮罩层
    $winning = $(".winning"), //红包
    $card = $("#card"),
    $close = $("#close");
var $noMask = $("#noMask"), //红包遮罩层
    $noCard = $("#noCard"),
    $noClose = $("#noClose");


//link = false;//判断是否在链接跳转中

//规则
$(".rule").click(function () {
    $maskRule.show();
});
$("#close-rule").click(function () {
    $maskRule.hide();
});

/*中奖信息提示*/
function win(prize) {
    //遮罩层显示
    $("#mask .prizeTips").text(prize['title']);
    $("#mask .prizeImg").attr('src', prize['image']);
    $("#myRecord").attr('href', myRecord);

    $mask.show();
    $winning.addClass("reback");
    setTimeout(function () {
        $card.addClass("pull");
    }, 500);

    //关闭弹出层
    $("#close,.win,.btn").click(function () {
        $mask.hide();
        $winning.removeClass("reback");
        $card.removeClass("pull");
    });

}

function noWin() {
    //遮罩层显示
    $noMask.show();
    $winning.addClass("reback");
    setTimeout(function () {
        $noCard.addClass("pull");
    }, 500);

    //关闭弹出层
    $("#noClose,.win,.btn").click(function () {
        //$close.click(function () {
        $noMask.hide();
        $winning.removeClass("reback");
        $noCard.removeClass("pull");
    });
    /*$(".win,.btn").click(function () {
        link = true;
    });*/
}

//此处可以在commonjs中合并
function queryString(name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(window.location.search);
    if (results === null) {
        return "";
    } else {
        return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
}


// Yellen、========>>>  Start
$('.cancelImg').click(function () {
    $(this).parents('.inputShade').addClass('hidden');
})

function checkPhone(phone) {
    var mobileReg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;
    if (mobileReg.test(phone)) {
        return true;
    }
    return false;
}

function checkIdCard(id) {
    // 1 "验证通过!", 0 //校验不通过
    let format = /^(([1][1-5])|([2][1-3])|([3][1-7])|([4][1-6])|([5][0-4])|([6][1-5])|([7][1])|([8][1-2]))\d{4}(([1][9]\d{2})|([2]\d{3}))(([0][1-9])|([1][0-2]))(([0][1-9])|([1-2][0-9])|([3][0-1]))\d{3}[0-9xX]$/;
    //号码规则校验
    if (!format.test(id)) {
        return false;
    }
    //出生年月日校验
    let year = id.substr(6, 4),     //身份证年
        month = id.substr(10, 2),   //身份证月
        date = id.substr(12, 2),    //身份证日
        time = Date.parse(month + '-' + date + '-' + year),//身份证日期时间戳date
        now_time = Date.parse(new Date()),//当前时间戳
        dates = (new Date(year, month, 0)).getDate();//身份证当月天数
    if (time > now_time || date > dates) {
        return false;
    }
    //校验码判断
    var c = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);   //系数
    var b = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');  //校验码对照表
    var id_array = id.split("");
    var sum = 0;
    for (var k = 0; k < 17; k++) {
        sum += parseInt(id_array[k]) * parseInt(c[k]);
    }
    if (id_array[17].toUpperCase() != b[sum % 11].toUpperCase()) {
        return false;
    }
    return true;
}

function showGatherForm(gather) {
    $('.inputShade').addClass('hidden');
    $('#gatherDom').removeClass('hidden');
    if (gather.indexOf("1") != -1) {
        $('#gather1').removeClass('hidden');
    }
    if (gather.indexOf("2") != -1) {
        $('#gather2').removeClass('hidden');
    }
    if (gather.indexOf("3") != -1) {
        $('#gather3').removeClass('hidden');
    }

}

function showPrompts(Text, noCancel, callback, buttonName) {
    console.log(buttonName);
    $('.inputShade').addClass('hidden');
    $('#prompts').removeClass('hidden');
    $('#promptsText').text(Text);
    $('#wordsSure').text('确认');
    if (buttonName) {
        $('#wordsSure').text(buttonName);
    }
    if (noCancel == 1) {
        $('#wordsCancel').hide();
    }
    if (typeof callback == 'function') {
        $('#wordsSure').one('click', function () {
            callback();
        });
    } else {
        $('#wordsSure').one('click', function () {
            $(this).parents('.inputShade').addClass('hidden');
        });
    }
}

function toggleLoading(type, text) {
    $('.inputShade').addClass('hidden');
    let loading = $('#loading').parent();
    if (type == 1) {
        loading.removeClass('hidden');
    } else {
        loading.addClass('hidden');
    }
    if (text) {
        $('#loadingText').text(text);
    }
}

function showQrCode() {
    $('.inputShade').addClass('hidden');
    $('#qrCode').removeClass('hidden');
}

$('.gatherSubmit').click(function () {
    let gather = config['gather'];
    let formJson = {};
    if (gather.indexOf("1") != -1) {
        let name = $.trim($('#gather1 input').val());
        if (name.length == 0) {
            $('#gather1 input').focus();
            return false;
        }
        formJson['name'] = name;
    }
    if (gather.indexOf("2") != -1) {
        let phone = $.trim($('#gather2 input').val());
        if (phone.length == 0) {
            $('#gather2 input').focus();
            return false;
        }
        formJson['phone'] = phone;
    } else {
        gather2Switch = 1;
    }
    if (gather.indexOf("3") != -1) {
        let idCard = $.trim($('#gather3 input').val());
        if (idCard.length == 0) {
            $('#gather3 input').focus();
            return false;
        }
        formJson['idcard'] = idCard;
    } else {
        gather3Switch = 1;
    }
    if (gather2Switch == 1 && gather3Switch == 1) {
        toggleLoading(1);
        saveGather(saveGatherUrl, formJson);
    }
});

function saveGather(saveUrl, formJson) {
    formJson['token'] = token;
    formJson['l_id'] = config['id'];
    $.ajax({
        type: 'POST',
        url: saveUrl,
        dataType: "json",
        data: formJson,
        headers: {
            'X-CSRF-TOKEN': csrf_token
        },
        success: function (response) {
            toggleLoading(0);
            showPrompts(response.data.message, 1, function () {
                window.location.reload();
            });
        },
        error: function (e) {
            alert('服务繁忙,请稍后再试！')
        }
    });
}

$('#gatherDom input').bind("input propertychange", function () {
    let type = $(this).data('type');
    let val = $(this).val();
    let parentDom = $('#' + type);
    if (type == 'gather2') {
        if (checkPhone(val)) {
            parentDom.find('.yesTips').removeClass('hidden');
            parentDom.find('.noTips').addClass('hidden');
            gather2Switch = 1;
        } else {
            parentDom.find('.noTips').removeClass('hidden');
            parentDom.find('.yesTips').addClass('hidden');
            gather2Switch = 0;
        }
    }
    if (type == 'gather3') {
        if (checkIdCard(val)) {
            parentDom.find('.yesTips').removeClass('hidden');
            parentDom.find('.noTips').addClass('hidden');
            gather3Switch = 1;
        } else {
            parentDom.find('.noTips').removeClass('hidden');
            parentDom.find('.yesTips').addClass('hidden');
            gather3Switch = 0;
        }
    }
})

// Yellen、========>>>  End
