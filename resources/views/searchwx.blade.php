<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>search</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.staticfile.org/admin-lte/2.4.15/css/AdminLTE.min.css">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        body {
            background-color: #f0f4ff;
        }

        .color-palette span {
            display: none;
            font-size: 12px;
        }

        .color-palette:hover span {
            display: block;
        }

        .color-palette-box h4 {
            position: absolute;
            top: 100%;
            left: 25px;
            margin-top: -40px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            display: block;
            z-index: 7;
        }

        .pb-120 {
            padding-bottom: 30px;
        }

        .pt-60 {
            padding-top: 60px;
        }

        .domain-search input {
            width: 100%;
            height: 78px;
            border: 2px solid #dbdcff;
            border-radius: 50px;
            padding: 0 30px;
            color: #7d859f;
            outline: none;
        }

        .domain-search .btn {
            position: absolute;
            top: 11px;
            right: -18px;
            box-shadow: 0px 10px 20px 0px rgba(0, 43, 183, 0.3);
        }

        .btn:hover {
            border-color: #926bfa;
            background: none;
            color: #fff;
        }

        .btn-2:hover {
            color: #002bb7;
            background: #fff;
        }

        .btn:focus, .btn:hover {
            text-decoration: none;
        }

        .btn {
            -moz-user-select: none;
            background: #002bb7;
            border: medium none;
            color: #fff;
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
            line-height: 1;
            margin-bottom: 0;
            padding: 19px 50px;
            text-align: center;
            touch-action: manipulation;
            transition: all 0.3s ease 0s;
            vertical-align: middle;
            white-space: nowrap;
            border-radius: 30px;
            border: 2px solid transparent;
            text-transform: capitalize;
        }

        .list-title-color {
            color: #aab5cf;
        }

        .widget {
            padding: 20px;
            background-color: rgb(255, 255, 255);
            box-shadow: 0px 8px 16px 0px rgba(200, 183, 255, 0.2);
            border-top: 3px solid #e8e8e8;
        }

        ul.cat li:first-child {
            border-top: 0;
            padding-top: 0;
        }

        ul.cat li {
            border-top: 1px solid #eaedff;
            padding: 15px 0;
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-all;
        }

        li {
            list-style: none;
        }

        ul {
            margin: 0px;
            padding: 0px;
        }

        .diy-title {
            color: #647589;
        }

        .diy-content {
            color: #acaeb9;
        }

        *::-moz-selection {
            background: #d6b161;
            color: #fff;
            text-shadow: none;
        }

        ::-moz-selection {
            background: #444;
            color: #fff;
            text-shadow: none;
        }

        ::selection {
            background: #444;
            color: #fff;
            text-shadow: none;
        }
    </style>
</head>
<body class="hold-transition">
<section class="domain-search-area pt-60 pb-120">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 offset-xl-2">
                <div class="domain-search">
                    <div class="position-relative">
                        <form action="{{$postUrl}}" method="post">
                            {{csrf_field()}}
                            <input value="{{$searchKey ?? ''}}" type="text" name="searchKey" required
                                   placeholder="Enter your search term...">
                            <button type="submit" class="btn btn-2">search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Main content -->
@if(count($searchData) > 0)
    <section class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <p class="box-title list-title-color">找到 {{count($searchData)}} 条结果</p>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="box-group" id="accordion">
                            <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                            @foreach( $searchData as $key => $val)
                                <div class="panel box {{ $key%2 == 0 ? 'box-primary' : ' box-success'}} ">
                                    <div class="box-header with-border" data-toggle="collapse" data-parent="#accordion"
                                         href="#collapse{{$key}}">
                                        <h4 class="box-title">
                                            <a href="javascript:void(0)">{{$val['wxname']}}</a>
                                        </h4>
                                    </div>
                                    <div id="collapse{{$key}}" class="panel-collapse collapse in">
                                        <div class="box-body">
                                            <div class="widget mb-40">
                                                <ul class="cat">
                                                    <li>
                                                        @if($val['type']==1)
                                                            <span class="pull-right badge bg-blue">服务号</span>
                                                        @else
                                                            <span class="pull-right badge bg-green">订阅号</span>
                                                        @endif
                                                        @if($val['activity_sw']==1)
                                                            <span class="pull-right badge bg-aqua">活动</span>
                                                        @endif
                                                        @if($val['guesslike_sw']==1)
                                                            <span class="pull-right badge bg-yellow">猜你喜欢</span>
                                                        @endif
                                                        @if($val['newbook_sw']==1)
                                                            <span class="pull-right badge bg-black">新书通报</span>
                                                        @endif
                                                        @if($val['yujie_sw']==1)
                                                            <span class="pull-right badge bg-orange">预借</span>
                                                        @endif
                                                        @if($val['yuyue_sw']==1)
                                                            <span class="pull-right badge bg-gray">预约</span>
                                                        @endif
                                                        @if($val['qr_type']>0)
                                                            <span class="pull-right badge bg-red">二维码{{$val['qr_type']}}.0</span>
                                                        @endif
                                                        @switch($val['auth_type'])
                                                            @case(1)
                                                            <span class="pull-right badge bg-blue">openlib</span>
                                                            @break
                                                            @case(2)
                                                            <span class="pull-right badge bg-gray">opac</span>
                                                            @break
                                                        @endswitch
                                                        @if($val['is_cluster']==1)
                                                            <span class="pull-right badge bg-purple">集群</span>
                                                        @endif
                                                    </li>
                                                    <li class="text-center">
                                                        <img style="width: 120px; height: 120px;"
                                                             src="{{$val['qr_code']}}" alt="公众号二维码"
                                                             class="text-center img-thumbnail">
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">token</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['token']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">appid</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['appid']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">libcode</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['libcode']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">glc</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['glc']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">opac</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['opacurl']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">openlib</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['openlib_url']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">荐购</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['opcs_url']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">sso数字资源</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['sso_url']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">U书快借</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['ushop_url']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">活动系统</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['activity_url']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-4 diy-title">知识库</div>
                                                            <div class="col-md-8 text-right diy-content">{{$val['knowledge_url']}}
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
        <!-- /.row -->
    </section>
    <!-- ./wrapper -->
@endif

<!-- jQuery 3 -->
<script src="https://cdn.staticfile.org/jquery/3.4.1/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
