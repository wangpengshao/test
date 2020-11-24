<canvas id="myPieChart{{$id?:''}}" width="200" height="120"></canvas>
<script>
    $(function () {
        var ctx = document.getElementById("myPieChart{{$id?:''}}").getContext('2d');
        var myChart = new Chart(ctx, {
            "type": "doughnut",
            "data": {
                "labels": {!! $labels !!},
                "datasets": [{
                    "label": "My First Dataset",
                    "data": {!! $data !!},
                    "backgroundColor": [
                        "rgb(255, 99, 132)",
                        "rgb(54, 162, 235)",
                        "rgb(76,121,125)",
                        "rgb(228,29,70)",
                        "rgb(146,236,241)",
                        "rgb(71,183,8)",
                        "rgb(24,49,183)",
                        "rgb(183,147,0)",
                    ]
                }]
            }
        });
    });
</script>
