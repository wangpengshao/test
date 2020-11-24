<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{$wxuser['wxname']}}</h3>
                <h3 class="box-title label label-default headerWx" style="display: none">
                    <i class="fa fa-arrow-circle-left"></i>
                    <i class="fa fa-arrow-circle-left"></i>
                    <i class="fa fa-arrow-circle-left"></i>
                    <i class="fa fa-arrow-circle-left"></i>
                    <i class="fa fa-arrow-circle-left"></i>
                    <i class="fa fa-arrow-circle-left"></i>
                </h3>
                <h3 class=" box-title headerWxText headerWx" style="display: none"></h3>
                <div class="box-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <div class="input-group-btn">
                            <button id="checkToken" type="button" class="btn btn-default">
                                点击这里开始迁移 <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding infoDetails" style="display: none">
                <table class="table table-hover">
                    <tbody>
                    <tr>
                        <th>数据类型</th>
                        <th>当前存在数量</th>
                        <th>需迁移数量</th>
                        <th>迁移后的数量</th>
                        <th>提示</th>
                        <th>操作</th>
                    </tr>
                    <tr class="bindTr">
                        <td>绑定的读者</td>
                        <td>{{$current['bindNumber']}}</td>
                        <td class="goal">0</td>
                        <td class="reality">0</td>
                        <td>
                            @if($current['bindNumber'] > 0 )
                                <span class="label label-warning">当前存在绑定读者,谨慎操作,如需要迁移,需要先清除数据</span>
                            @else
                                <span class="label label-success">可正常迁移</span>
                            @endif
                        </td>
                        <td>
                            <div class="box-tools">
                                <button type="button" class="btn btn-box-tool migrate"
                                        data-toggle="tooltip"
                                        data-original-title="数据迁移"
                                        data-type="bindReader"
                                >
                                    <i class="fa fa-recycle"></i>
                                </button>
                                {{--                                <button type="button" class="btn btn-box-tool"--}}
                                {{--                                        data-toggle="tooltip"--}}
                                {{--                                        data-original-title="清空数据">--}}
                                {{--                                    <i class="fa fa-times"></i>--}}
                                {{--                                </button>--}}
                            </div>
                        </td>
                    </tr>
                    <tr class="textTr">
                        <td>文本回复</td>
                        <td>{{$current['textNumber']}}</td>
                        <td class="goal">0</td>
                        <td class="reality">0</td>
                        <td>
                            @if($current['textNumber'] > 0 )
                                <span class="label label-warning">当前存在自定义文本回复数据,谨慎操作,如需要迁移,需要先清除数据</span>
                            @else
                                <span class="label label-success">可正常迁移</span>
                            @endif
                        </td>
                        <td>
                            <div class="box-tools">
                                <button type="button" class="btn btn-box-tool migrate"
                                        data-toggle="tooltip"
                                        data-original-title="数据迁移"
                                        data-type="text"
                                >
                                    <i class="fa fa-recycle"></i>
                                </button>
                            </div>
                        </td>

                    </tr>
                    <tr class="imgTr">
                        <td>图文回复</td>
                        <td>{{$current['imgNumber']}}</td>
                        <td class="goal">0</td>
                        <td class="reality">0</td>
                        <td>
                            <span class="label label-success">可正常迁移</span>
                        </td>
                        <td>
                            <div class="box-tools">
                                <button type="button" class="btn btn-box-tool migrate"
                                        data-toggle="tooltip"
                                        data-original-title="数据迁移"
                                        data-type="img"
                                >
                                    <i class="fa fa-recycle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    {{--<tr class="diyMenusTr">--}}
                        {{--<td>自定义菜单</td>--}}
                        {{--<td>{{$current['diyMenusNumber']}}</td>--}}
                        {{--<td class="goal">0</td>--}}
                        {{--<td class="reality">0</td>--}}
                        {{--<td>--}}
                            {{--@if($current['diyMenusNumber'] > 0 )--}}
                                {{--<span class="label label-warning">当前存在自定义菜单数据,谨慎操作,如需要迁移,需要先清除数据</span>--}}
                            {{--@else--}}
                                {{--<span class="label label-success">可正常迁移</span>--}}
                            {{--@endif--}}
                        {{--</td>--}}
                        {{--<td>--}}
                            {{--<div class="box-tools">--}}
                                {{--<button type="button" class="btn btn-box-tool migrate"--}}
                                        {{--data-toggle="tooltip"--}}
                                        {{--data-original-title="数据迁移"--}}
                                        {{--data-type="diyMenus"--}}
                                {{-->--}}
                                    {{--<i class="fa fa-recycle"></i>--}}
                                {{--</button>--}}
                            {{--</div>--}}
                        {{--</td>--}}
                    {{--</tr>--}}
                    <tr class="indexMenusTr">
                        <td>九宫格菜单</td>
                        <td>{{$current['indexMenusNumber']}}</td>
                        <td class="goal">0</td>
                        <td class="reality">0</td>
                        <td>
                            @if($current['indexMenusNumber'] > 0 )
                                <span class="label label-warning">当前存在菜单数据,谨慎操作,如需要迁移,需要先清除数据</span>
                            @else
                                <span class="label label-success">可正常迁移</span>
                            @endif
                        </td>
                        <td>
                            <div class="box-tools">
                                <button type="button" class="btn btn-box-tool migrate"
                                        data-toggle="tooltip"
                                        data-original-title="数据迁移"
                                        data-type="indexMenus"
                                >
                                    <i class="fa fa-recycle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>

<script>
    $('#checkToken').on('click', function () {
        swal({
            title: '请输入uWei旧版 token',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: '确认',
            showLoaderOnConfirm: true,
            inputValidator: function (value) {
                value = $.trim(value)
                let en = value.substr(0, 6);
                let nu = value.substr(6);
                let pattern = new RegExp("^[a-zA-Z]+$");
                let pattern2 = new RegExp("[0-9]+");
                return new Promise(function (resolve, reject) {
                    if (value && value.length == 16 && pattern2.test(nu) && pattern.test(en)) {
                        resolve();
                    } else {
                        reject('请输入合法token');
                    }
                })
            },
            preConfirm: function (oldToken) {
                return fetch("{!! $checkUrl !!}" + oldToken, {method: 'GET'}
                ).then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                }).catch(error => {
                    throw new Error(error);
                })
            },
            allowOutsideClick: false
        }).then(function (response) {
            if (response.status == false) {
                sweetAlert(
                    '哎呦……',
                    '找不到当前公众号数据',
                    'error'
                );
                return false;
            }
            swal(
                '公众号:' + response.info.wxname,
                '验证通过',
                'success'
            ).catch(swal.noop);
            initHTML(response.info);
            return true;
        }).catch(swal.noop);
    });

    let oldWxuser;

    function initHTML(info) {
        oldWxuser = info;
        // console.log(oldWxuser);
        $('#checkToken').hide();
        $('.headerWxText').text(oldWxuser.wxname);
        $('.headerWx').show();
        $('.infoDetails').show();
        $('.bindTr .goal').text(info.bindNumber);
        $('.textTr .goal').text(info.textNumber);
        $('.imgTr .goal').text(info.imgNumber);
        //$('.diyMenusTr .goal').text(info.diyMenusNumber);
        $('.indexMenusTr .goal').text(info.indexMenusNumber);
    }

    $('.migrate').on('click', function () {
        let type = $(this).data('type');
        let thisDom = $(this);
        swal({
            title: '确定迁移吗',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: '确认',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                let data = {_token: LA.token, yToken: oldWxuser.token, type: type};
                let headers = {'Content-Type': 'application/json'};
                return fetch("{!! $upUrl !!}", {
                        method: 'POST',
                        body: JSON.stringify(data),
                        headers: new Headers(headers)
                    }
                ).then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                }).catch(error => {
                    throw new Error(error);
                })
            },
            allowOutsideClick: false
        }).then(function (response) {
            console.log(response);
            if (response.status == false) {
                sweetAlert(
                    '哎呦……',
                    '找不到当前公众号数据',
                    'error'
                );
                return false;
            }
            swal(
                '通知',
                '迁移完成',
                'success'
            ).catch(swal.noop);
            thisDom.hide();
            $(response.data.classType + ' .reality').text(response.data.number);
            return true;
        }).catch(swal.noop);
    });

</script>
