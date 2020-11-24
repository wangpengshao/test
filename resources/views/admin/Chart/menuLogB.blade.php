<canvas id="myBarChart"></canvas>
<script>
    $(function () {
        var ctx = document.getElementById("myBarChart").getContext('2d');
        var myChart = new Chart(ctx, {
            "type": "bar",
            "data": {
                "labels": @json($labels),
                "datasets": [{
                    "label": "{{$caption}}",
                    "data": @json($data),
                    "fill": false,
                    // "borderColor": "rgb(75, 192, 192)",
                    "lineTension": 0.1,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
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
