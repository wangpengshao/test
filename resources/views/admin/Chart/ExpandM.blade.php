<canvas id="myChart"></canvas>
<script type="text/javascript" src="{{asset('wechatAdmin/js/chart.min.js')}}"></script>
<script>
    var ctx = document.getElementById("myChart").getContext("2d");
    var data = {
        // 表现在X轴上的数据，数组形式
        labels: ["1日", "2日", "3日", "4日", "5日", "6日", "7日", "8日", "9日", "10日", "11日", "12日", "13日", "14日", "15日", "16日", "17日", "18日", "19日", "20日", "21日", "22日", "23日", "24日", "25日", "26日", "27日", "28日", "29日", "30日", "31日"],
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
                data: @json($data['ActiveDayData'])
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
                data: @json($data['BindDayData'])
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
                data: @json($data['SaveDayData'])
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
                data: @json($data['NewbindDayData'])
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
                data: @json($data['NewsaveDayData'])
            }
        ]
    }
    new Chart(ctx, {
        type: 'line',
        data: data
    });
</script>
