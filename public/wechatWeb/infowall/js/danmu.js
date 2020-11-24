/*!
 *@name     jquery.barrager.min.js
 *@project  jquery.barrager.js
 *@author   yaseng@uauc.net
 *@project  https://github.com/yaseng/jquery.barrager.js
 */
!
    function (a) {
        a.fn.barrager = function (b) {
            function m() {
                var b = a(window).width() + 910;
                return b > k ? (k += 1, a(e).css({"margin-right":k}), void 0) : (a(e).remove(), !1)
            }
            var c, d, e, f, g, h, i, j, k, l, demo;
            b = a.extend({
                self: false,
                adder: true,
                user: '用户名',// 用户名
                topic: '#话题#',//话题
                wish: '心愿',
                close: !0,
                top: 0,
                max: 10,
                speed: 6,
            },
                b || {}),
                c = (new Date).getTime(),
                d = "barrage_" + c,
                e = "#" + d,
                f = a(`<div class='barrage' id='${d}'></div>`).appendTo(a(this)),
                // g = a(window).height() - 100,
                h = 0 == b.top ? Math.floor(Math.random() * g + 40) : b.top,
                f.css("top", h + "px"),
                div_barrager_box = a(`<div class="barrage_box cl ${b.adder ? 'danmu-libIn' : 'danmu-libOut'}  ${!b.self?'':'barrage_self'}"></div>`).appendTo(f),
                b.img && (div_barrager_box.append("<div class='portrait z'></div>"),
                    i = a("<img src='' >").appendTo(e + " .barrage_box .portrait"),
                    i.attr("src", b.img)),
                div_barrager_box.append(" <div class='z p'></div>"),
                j = a(`<p class="danmu-text-title"><span class="danmu-user-name">${b.user || '佚名'}</span><span>${b.topic || '话题'}</span></p>
                <p class="danmu-text-topic">${b.wish || '佚心愿名'}</p>`).appendTo(e + " .barrage_box .p"),
                k = 0,
                f.css("margin-right", k),
                l = setInterval(m, b.speed),
                div_barrager_box.mouseover(function (e) {
                    $(e.currentTarget).parent('.barrage').css("z-index",999)
                    clearInterval(l)
                }),
                div_barrager_box.mouseout(function (e) {
                    $(e.currentTarget).parent('.barrage').css("z-index",1000)
                    l = setInterval(m, b.speed);
                }),
                a(e + ".barrage .barrage_box .close").click(function () {
                    a(e).remove()
                })
        },
            a.fn.barrager.removeAll = function () {
                a(".barrage").remove()
            }
    }(jQuery);