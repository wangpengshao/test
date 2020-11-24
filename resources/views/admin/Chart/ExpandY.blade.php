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
                label: " 48小时活跃粉丝数",
                fill: false,
                borderColor: "rgb(75, 192, 192)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['ActiveMonthData'])
            },
            // 第二条线
            {
                label: " 绑定读者数",
                fill: false,
                borderColor: "rgb(255, 99, 132)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['BindMonthData'])
            },
            // 第三条线
            {
                label: " 存卡数",
                fill: false,
                borderColor: "rgb(255, 205, 86)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['SaveMonthData'])
            },
            // 第四条线
            {
                label: " 新增绑定数",
                fill: false,
                borderColor: "rgb(58,58,58)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['NewbindMonthData'])
            },
            // 第五条线
            {
                label: " 新增存卡数",
                fill: false,
                borderColor: "rgb(153, 102, 255)",
                lineTension: 0.1,
                // 表示数据的圆圈的颜色
                pointColor: "rgba(220,220,220,1)",
                // 表示数据的圆圈的边的颜色
                pointStrokeColor: "#fff",
                data: @json($data['NewsaveMonthData'])
            }
        ]
    }
    new Chart(ctx, {
        type: 'line',
        data: data
    });
</script>
