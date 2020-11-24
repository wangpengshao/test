@extends('admin.wechat.material.layout')

@section('content')

    <link rel="stylesheet"
          href="https://adminlte.io/themes/AdminLTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <script type="text/javascript" charset="utf-8"
            src="https://adminlte.io/themes/AdminLTE/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf-8"
            src="https://adminlte.io/themes/AdminLTE/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <!-- /.box -->
                <div class="box">
                    <div class="box-body">
                        <div id="example1_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table id="example1" class="table table-bordered table-striped dataTable"
                                           role="grid" aria-describedby="example1_info">
                                        <thead>
                                        <tr role="row">
                                            <th>图片</th>
                                            <th style="width: 200px;">链接</th>
                                            <th>时间</th>
                                            <th>操作</th>
                                        </thead>
                                        <tbody>
                                        @foreach($ls as $value)
                                            <tr role="row" class="{{ ($loop->index % 2 == 0) ? 'old' : 'even' }}">
                                                <td>
                                                    <img style="width: 50px;" src="{{$value['url']}}">
                                                </td>
                                                <td style="display:block;width: 230px;word-break:break-all; ">{{$value['url']}}</td>
                                                <td>{{$value['time']}}</td>
                                                <td style="text-align: center">
                                                    <span onclick="newtest($(this))"
                                                          data-name="{{$value['name']}}"
                                                          data-url="{{$value['url']}}"
                                                          class="glyphicon glyphicon-ok text-success"
                                                          aria-hidden="true"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
        </div>
    </section>
    <script>
        $(function () {
            $('#example1').DataTable({
                "sPaginationType": "full_numbers",
                "oLanguage": {
                    "sLengthMenu": "每页 _MENU_ 条",
                    "sZeroRecords": "抱歉， 没有找到",
                    "sInfo": "从 _START_ 到 _END_ /共 _TOTAL_ 条数据",
                    "sInfoEmpty": "",
                    "sInfoFiltered": "从 _MAX_ 条中查询",
                    "sZeroRecords": "无",
                    "sSearch": "查询:",
                    "oPaginate": {
                        "sFirst": "首页",
                        "sPrevious": "<<",
                        "sNext": ">>",
                        "sLast": "尾页"
                    }

                }
            });
        })

        function newtest(e) {
            var name = e.data('name');
            var url = e.data('url');
            window.parent.clearTest(name,url);
        }
    </script>
@endsection
