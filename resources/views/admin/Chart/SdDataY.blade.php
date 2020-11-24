<canvas id="myChart"></canvas>
<script type="text/javascript" src="{{asset('wechatAdmin/js/chart.min.js')}}"></script>
<script>
    var ctx = document.getElementById("myChart").getContext("2d");
    var data = {
        // 表现在X轴上的数据，数组形式
        labels: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
        // 第一条线
        datasets: [
            {
                label: "导入书单总期数",
                fill: false,
                borderColor: "rgb(75, 192, 192)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['SdData'])
            },
            // 第二条线
            {
                label: " 收藏其他馆书籍的总数",
                fill: false,
                borderColor: "rgb(255, 99, 132)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['ColData'])
            },
            // 第三条线
            {
                label: " 该馆书籍的总数",
                fill: false,
                borderColor: "rgb(255, 205, 86)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['SjData'])
            },
        ]
    }
    new Chart(ctx, {
        type: 'line',
        data: data
    });
</script>
