/**
 * IP地址校验
 */
function ipAddress(rule, value, callback) {
    if (
        value &&
        !/((25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))/.test(
            value
        )
    ) {
        callback(new Error("IP地址不符合规范"));
    } else {
        callback();
    }
}

/**
 * 手机号码校验
 */
function phoneNumber(rule, value, callback) {
    if (
        value &&
        (!/^[1][34578]\d{9}$/.test(value) ||
            !/^[1-9]\d*$/.test(value) ||
            value.length !== 11)
    ) {
        callback(new Error("手机号码不符合规范"));
    } else {
        callback();
    }
}

/**
 * 电话号码校验
 */
function telephoneNumber(rule, value, callback) {
    if (value && !/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/.test(value)) {
        callback(new Error("电话号码不符合规范"));
    } else {
        callback();
    }
}

/**
 * 邮箱校验
 */
function emailValue(rule, value, callback) {
    if (
        value &&
        !/^[A-Za-zd]+([-_.][A-Za-zd]+)*@([A-Za-zd]+[-.])+[A-Za-zd]{2,5}$/.test(
            value
        )
    ) {
        callback(new Error("邮箱格式不符合规范"));
    } else {
        callback();
    }
}

/**
 * 身份证号码校验
 */
function idCard(rule, value, callback) {
    if (
        value &&
        (!/\d{17}[\d|x]|\d{15}/.test(value) ||
            (value.length !== 15 && value.length !== 18))
    ) {
        callback(new Error("身份证号码不符合规范"));
    } else {
        callback();
    }
}

/**
 * 正整数校验
 */
function integerP(rule, value, callback) {
    if (value && !/^[1-9]\d*$/.test(value)) {
        callback(new Error("只能填写正整数"));
    } else {
        callback();
    }
}

/**
 * 负整数校验
 */
function integerN(rule, value, callback) {
    if (value && !/^-[1-9]\d*$/.test(value)) {
        callback(new Error("只能填写负整数"));
    } else {
        callback();
    }
}

/**
 * 英文字符校验
 */
function enText(rule, value, callback) {
    let a = "",
        arr = value.split(" ");
    for (let i = 0; i < arr.length; i++) {
        //删除行内空格
        a += arr[i];
    }
    if (value && !/^[A-Za-z]+$/.test(a)) {
        callback(new Error("只能填写英文字符"));
    } else {
        callback();
    }
}
/**
 * 中文字符校验
 */
function cnText(rule, value, callback) {
    let a = "",
        arr = value.split(" ");
    for (let i = 0; i < arr.length; i++) {
        //删除行内空格
        a += arr[i];
    }
    if (value && /[^\u4e00-\u9fa5]/.test(a)) {
        callback(new Error("只能填写中文字符"));
    } else {
        callback();
    }
}