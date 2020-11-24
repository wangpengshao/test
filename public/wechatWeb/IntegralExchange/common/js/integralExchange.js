$(function () {
    let gather = ["1", "2", "3"];
    <!-- 对触发指定的奖品id进行增减及其它操作 -->
    var ul = document.getElementById("ul");
    ul.onclick = function (event) {
        var tar = event.target;
        var id = $(tar).data('id');
        if (id) {
            matchPrize(id);
        } else {
            <!-- 兑奖方式为线上快递且不存在个人信息的情况下,需要调用保存信息的功能 -->
            var reward_id = $(tar).data('reward_id');
            return showGatherForm(gather);
            matchPrize(reward_id);
        }
        event.preventDefault();
    }

    function matchPrize(id) {
        let formJson = {token: token, id: id};
        $.ajax({
            type: 'POST',
            url: matchPrizeUrl,
            dataType: "json",
            data: formJson,
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            success: function (response) {
                if (response.status == true) {
                    /*layer.open({content: response.data.message,skin: 'msg',time: 4 });
                    window.location.reload();*/
                    win();
                } else {
                    layer.open({content: response.data.message, skin: 'msg', time: 2});
                }
            },
            error: function (e) {
                alert('服务繁忙,请稍后再试！')
            }
        });
    }
})