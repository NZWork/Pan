<html>
<head>
    <link rel="stylesheet" type="text/css" href="style/css/webuploader.css">
    <link href="style/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <script src="style/js/jquery-1.10.1.min.js"></script>
    <script src="style/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="style/js/webuploader.js"></script>
    <style>
        .box--btn {
            position: absolute;
            bottom: 0;
            display: block;
            width: 100%;
            text-align: center;
            padding-bottom: 5px;
        }

        .box--btn .btn-default {
            vertical-align: middle;
            height: 42px;
            s
        }

        .item--btn {
            display: inline-block;
            vertical-align: middle;
            margin-right: 25px;
        }

        #thelist {
            height: 200px;
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
</head>
<body>
<div id="uploader" class="wu-example">
    <!--用来存放文件信息-->
    <div id="thelist" class="uploader-list"></div>
    <div class="box--btn">
        <div class="item--btn" id="picker">选择文件</div>
        <button id="ctlBtn" class="item--btn btn btn-default">开始上传</button>
    </div>
</div>
</body>
</html>

<script>
    var $ = jQuery,
        $list = $('#thelist'),
        $btn = $('#ctlBtn'),
        state = 'pending',
        uploader;

    uploader = WebUploader.create({

        // 不压缩image
        resize: false,

        // swf文件路径
        swf: 'style/Uploader.swf',

        // 文件接收服务端。
        server: 'https://pan.tiki.im/upload',

        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#picker',

        // 开起分片上传。
        chunked: true,

        chunkSize: 5 * 1024 * 1024
    });

    // 当有文件添加进来的时候
    uploader.on('fileQueued', function (file) {
        $list.append('<div id="' + file.id + '" class="item">' +
            '<h4 class="info">' + file.name + '</h4>' +
            '<p class="state">等待上传...</p>' +
            '</div>');

        var hash;
        uploader.md5File(file)
        // 及时显示进度
        //            .progress(function(percentage) {
        //                console.log('Percentage:', percentage);
        //            })
            .then(function (val) {
                hash = val;
                console.log('md5 result:', hash);
                //$('#' + file.id).find('p.state').text('秒传文件');
            });
        uploader.remove(file)
    });

    // 文件上传过程中创建进度条实时显示。
    uploader.on('uploadProgress', function (file, percentage) {
        var $li = $('#' + file.id),
            $percent = $li.find('.progress .progress-bar');

        // 避免重复创建
        if (!$percent.length) {
            $percent = $('<div class="progress progress-striped active">' +
                '<div class="progress-bar" role="progressbar" style="width: 0%">' +
                '</div>' +
                '</div>').appendTo($li).find('.progress-bar');
        }

        $li.find('p.state').text('上传中');

        $percent.css('width', percentage * 100 + '%');
    });

    uploader.on('uploadSuccess', function (file) {
        $('#' + file.id).find('p.state').text('已上传');
    });

    uploader.on('uploadError', function (file) {
        $('#' + file.id).find('p.state').text('上传出错');
    });

    uploader.on('uploadComplete', function (file) {
        $('#' + file.id).find('.progress').fadeOut();
    });

    uploader.on('all', function (type) {
        if (type === 'startUpload') {
            state = 'uploading';
        } else if (type === 'stopUpload') {
            state = 'paused';
        } else if (type === 'uploadFinished') {
            state = 'done';
        }

        if (state === 'uploading') {
            $btn.text('暂停上传');
        } else {
            $btn.text('开始上传');
        }
    });

    $btn.on('click', function () {
        if (state === 'uploading') {
            uploader.stop();
        } else {
            uploader.upload();
        }
    });
</script>
