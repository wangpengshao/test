;(function (win, doc) {
    var docEl = doc.documentElement,
        design = 750;
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

function showLoading(text = null) {
    if (text) {
        $('.loadingText').text(text);
    }
    $('.loadingSw').show();
}

function hideLoading() {
    $('.loadingText').text('');
    $('.loadingSw').hide();
}
