<div class="row">
    <div class="col-md-6">

        <div class="box box-primary">
            <div class="box-header with-border">
                <i class="fa fa-bar-chart-o"></i>
                <h3 class="box-title">扫码入座</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body">
                {{--<div id="scan_qrcode" style="height: 300px;"></div>--}}
                <div id="container1" style="height:300px"></div>
            </div>

            <div class="box-body">
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3>{{$data['allLog']}}</h3>
                            <p>总次数</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>{{$data['yearLog']}}</h3>
                            <p>今年</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>{{$data['monthLog']}}</h3>
                            <p>本月</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
            </div>

        </div>


    </div>

    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <i class="fa fa-bar-chart-o"></i>
                <h3 class="box-title">预约入座</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body">
                {{--<div id="booking" style="height: 300px;"></div>--}}
                <div id="container2" style="height:300px"></div>
            </div>

            <div class="box-body">
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3>{{$data['bookingNum_c']}}</h3>
                            <p>成功预约</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>{{$data['bookingNum_q']}}</h3>
                            <p>取消预约</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>{{$data['bookingNum_w']}}</h3>
                            <p>违约</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
            </div>

            <!-- /.box-body-->
        </div>
    </div>

</div>

<script src="{{asset('js/jquery.knob.js')}}"></script>
<script src="http://cdn.highcharts.com.cn/highcharts/highcharts.js"></script>
<script>
    $(function () {
        let init_scan_qrcode_data = {!! $data['downWeekData'] !!};
        let init_booking_data = {!! $data['bookingWeekData'] !!};

        var category1 = [], data1 = [], category2 = [], data2 = [];

        for (let i in init_scan_qrcode_data) {
            category1.unshift(i)
            data1.unshift(init_scan_qrcode_data[i])
        }

        for (let i in init_booking_data) {
            category2.unshift(i)
            data2.unshift(init_booking_data[i])
        }

        var chart1 = Highcharts.chart('container1', {
            chart: {
                type: 'column'
            },
            credits:{
                enabled: false // 禁用版权信息
            },
            title: {
                text: null
            },
            xAxis: {
                categories: category1,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: null,
            },
            tooltip: {
                headerFormat: '<table>',
                pointFormat: '<tr><td style="padding:0;">{point.category}: </td>' + '<td style="padding:0;"><strong>{point.y}</strong></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    borderWidth: 0,
                },
            },
            series: [{
                name: '名称',
                data: data1,
                showInLegend: false,
            }]
        })

        var chart2 = Highcharts.chart('container2', {
            chart: {
                type: 'column'
            },
            credits:{
                enabled: false // 禁用版权信息
            },
            title: {
                text: null
            },
            xAxis: {
                categories: category2,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: null,
            },
            tooltip: {
                headerFormat: '<table>',
                pointFormat: '<tr><td style="padding:0;">{point.category}: </td>' + '<td style="padding:0;"><strong>{point.y}</strong></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    borderWidth: 0,
                },
            },
            series: [{
                name: '名称',
                data: data2,
                showInLegend: false,
            }]
        })

    })
</script>