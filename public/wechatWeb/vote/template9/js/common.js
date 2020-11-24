//- 设置font-size
(function(doc, win) {
    var _root = doc.documentElement,
        resizeEvent = 'orientationchange' in window ? 'orientationchange' : 'resize',
        resizeCallback = function() {
            var clientWidth = _root.clientWidth,
                fontSize = 100;
            if (!clientWidth) return;
            if (clientWidth < 750) {
                fontSize = 100 * (clientWidth / 750);
            } else {
                fontSize = 100 * (750 / 750);
            }
            _root.style.fontSize = fontSize + 'px';
        };
    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvent, resizeCallback, false);
    doc.addEventListener('DOMContentLoaded', resizeCallback, false);
})(document, window);

