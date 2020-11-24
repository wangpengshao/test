//规则
$(".rule").click(function () {
    $maskRule.show();
});
$("#close-rule").click(function () {
    $maskRule.hide();
});

/*中奖信息提示*/
function win() {
    //遮罩层显示
    $("#mask .prizeTips").text('兑奖完成');
    $("#myRecord").attr('href', awardRecord);

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
        window.location.reload();
    });

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
        $('#gather4').removeClass('hidden');
    }

}

function showPrompts(Text, noCancel, callback, buttonName) {
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
    let gather = ["1", "2", "3"];
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
        let address = $.trim($('#gather4 input').val());
        if (address.length == 0) {
            $('#gather4 input').focus();
            return false;
        }
        formJson['address'] = address;
    }
    if (gather2Switch == 1) {
        toggleLoading(1);
        saveGather(saveGatherUrl, formJson);
    }
});

function saveGather(saveUrl, formJson) {
    formJson['token'] = token;
    $.ajax({
        type: 'POST',
        url: saveGatherUrl,
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
})

// Yellen、========>>>  End
