<canvas id="myDataChart" width="200" height="120"></canvas>
<script>
    $(function () {
        var ctx = document.getElementById("myDataChart").getContext('2d');
        var myChart = new Chart(ctx, {
            "type": "line",
            "data": {
                "labels": {!! $labels !!},
                "datasets": [{
                    "label": "{{$caption}}",
                    "data": {!! $labelData !!},
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

                            return '新用户(个): ' + tooltipItem.yLabel;
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
