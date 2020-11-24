<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>upload</title>
    <link href="https://cdn.staticfile.org/twitter-bootstrap/4.1.3/css/bootstrap.min.css"  rel="stylesheet">
    <link href="https://cdn.staticfile.org/bootstrap-fileinput/4.5.1/css/fileinput.min.css" media="all" rel="stylesheet"
          type="text/css">
    {{--<link href="https://cdn.staticfile.org/bootstrap-fileinput/4.5.1/themes/explorer-fa/theme.min.css" media="all" rel="stylesheet" type="text/css">--}}

    <script src="https://cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>

    <script src="https://cdn.staticfile.org/bootstrap-fileinput/4.5.1/js/fileinput.min.js"></script>

    <script src="https://cdn.staticfile.org/twitter-bootstrap/4.1.3/js/bootstrap.min.js"
            type="text/javascript"></script>
    <script src="https://cdn.staticfile.org/bootstrap-fileinput/4.5.1/js/locales/zh.min.js"></script>
    <script src="https://cdn.staticfile.org/clipboard.js/2.0.4/clipboard.min.js"></script>
    {{--<script src="https://cdn.staticfile.org/bootstrap-fileinput/4.5.1/themes/fa/theme.min.js"></script>--}}
    {{--<script src="https://cdn.staticfile.org/bootstrap-fileinput/4.5.1/themes/explorer-fa/theme.min.css"></script>--}}
    <style>
        .showMes{
            display: none;color: #0c5460;float: right;padding-right: 10px;
        }
    </style>
</head>
<body>
{!! csrf_field() !!}

<input id="input-mp3" name="input-mp3" multiple type="file" class="file file-loading">
（媒体文件不能大于20.0M,仅支持mp3格式）
<div class="form-group has-success has-feedback">
    <label for="disabledSelect">音频展示代码:</label>
    <textarea class="form-control" id="showHtml" rows="3" ></textarea>
    <button type="button" class="btn btn-info center-block cpDom"
            data-clipboard-target="#showHtml" style="float: right">点击复制代码
    </button>
</div>

<span class="label label-default showMes" >复制失败</span>
<span class="label label-success showMes" >复制成功</span>


</body>
<script type="text/javascript" charset="utf-8">
    var text = '';
    $("#input-mp3").fileinput({
        uploadUrl: "{{route('oldSystemUpload')}}",
        uploadExtraData: function (previewId, index) {
            return {
                _token: $("input[name='_token']").val(),
            };
        },
        // ajaxSettings: {
        //     contentType:false
        // },
        uploadAsync: true,  //异步上传
        language: 'zh',
        maxFileCount: 1,
        showPreview: false,
        maxFileSize: 20240,
        allowedFileExtensions: ["mp3"],
        hideThumbnailContent: true // hide image, pdf, text or other content in the thumbnail preview
    }).on('fileuploaded', function (event, data, previewId, index) {
        $('#showHtml').val('');
        console.log(data);
        var uploaded = data.response.uploaded;
        console.log(uploaded);
        if (uploaded) {
            text = '<audio controls="controls" preload="load"><source src="' + uploaded + '" type="audio/mpeg"></audio>';
            $('#showHtml').val(text);
        }
    });

    var clipboard = new ClipboardJS('.cpDom');

    clipboard.on('success', function (e) {
        console.info('Action:', e.action);
        console.info('Text:', e.text);
        console.info('Trigger:', e.trigger);
        $('.label-default').hide();
        $('.label-success').show();
        setTimeout(function () {
            $('.label-success').hide();
        },4000)
        // e.clearSelection();
    });

    clipboard.on('error', function (e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
        $('.label-success').hide();
        $('.label-default').show();
        setTimeout(function () {
            $('.label-default').hide();
        },4000)
    });
</script>
</html>
