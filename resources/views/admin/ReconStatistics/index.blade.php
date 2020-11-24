<head>
</head>
@isset($payCount)
    <div class="box col-md-6">
        <div class="box-body no-padding">
            <table class="table table-condensed">
                <tbody>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Data item</th>
                    <th style="width: 40px">Total</th>
                </tr>
                <tr>
                    <td>1.</td>
                    <td>已缴费次数</td>
                    <td><span class="badge bg-red">{{$payCount}}</span></td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>已支付并成功销账次数</td>
                    <td><span class="badge bg-yellow">{{$paycancelCount}}</span></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>已销账成功次数</td>
                    <td><span class="badge bg-yellow">{{$cancelCount}}</span></td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>销账失败次数</td>
                    <td><span class="badge bg-yellow">{{$cancelCountfail}}</span></td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>已支付总额</td>
                    <td><span class="badge bg-light-blue">{{$payCountNum}}</span></td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>已退款总额</td>
                    <td><span class="badge bg-green">{{$cancelCountNum}}</span></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
@endisset

@isset($dfpayCount)
    <div class="box col-md-6">
        <div class="box-body no-padding">
            <table class="table table-condensed">
                <tbody>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Dfdata item</th>
                    <th style="width: 40px">Total</th>
                </tr>
                <tr>
                    <td>1.</td>
                    <td>代付笔数</td>
                    <td><span class="badge bg-red">{{$dfpayCount}}</span></td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>代付并成功销账次数</td>
                    <td><span class="badge bg-yellow">{{$dfpaycancelCount}}</span></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>代付销账成功的次数</td>
                    <td><span class="badge bg-yellow">{{$dfcancelCount}}</span></td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>代付销账失败的次数</td>
                    <td><span class="badge bg-yellow">{{$dfcancelCountfail}}</span></td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>代付成功支付总额</td>
                    <td><span class="badge bg-light-blu">{{$dfpayCountNum}}</span></td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>代付成功退款总额</td>
                    <td><span class="badge bg-green">{{$dfcancelCountNum}}</span></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
@endisset
