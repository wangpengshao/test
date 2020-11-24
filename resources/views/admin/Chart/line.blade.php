<canvas id="myLineChart" width="400" height="400"></canvas>
<script>

    $(function () {
        var ctx = document.getElementById("myLineChart").getContext('2d');
        var myChart = new Chart(ctx, {
            "type": "line",
            "data": {
                "labels": ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
                "datasets": [{
                    "label": "{{$caption}}",
                    "data": {{$monthData}},
                    "fill": false,
                    "borderColor": "rgb(75, 192, 192)",
                    "lineTension": 0.1
                }]
            }
            ,
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        },
                        // stacked: true,
                        // tension: 0 // 禁用贝塞尔曲线
                    }]
                }, tooltips: {
                    titleFontFamily: 'Helvetica Neue',
                    titleFontSize: 14,
                    yPadding: 14,
                    xPadding: 8,
                    bodyFontSize: 14,
                    titleMarginBottom: 10,
                    position: 'nearest',//tooltips就近显示
                    callbacks: {
                        label: function (tooltipItem, data) {

                            return '访问量(次): ' + tooltipItem.yLabel;
                        }
                    }
                },
                title: {
                    display: true,
                    text: '{{$caption}}',
                    fontFamily: 'Helvetica',
                    padding: 20,
                    fontSize: 16,
                    lineHeight: 1.2,
                }, legend: {
                    display: false,
                },

            }
        });
    });
</script>
