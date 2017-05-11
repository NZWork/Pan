<div class="col-xs-12 text-center" style="margin-top: 25px">
    <span style="color:gray"> ©2016-2017 NZWork </span>
</div>

</section>
<div>

</div>
</body>
</html>

{{--<script type="text/javascript" src="style/js/jquery.min.1.6.1.js"></script>--}}
{{--<script src="style/js/jquery-1.10.2.js"></script>--}}
<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="style/js/bootstrap.min.js"></script>
<script src="style/js/jquery.pagination.js"></script>
<script src="style/js/jquery.knob.min.js"></script>
<script src="style/js/jquery.pjax.js"></script>
<script src="style/layer/layer.js"></script>
<script src="style/js/main.js"></script>
<script type="text/javascript" src="style/js/ue.scrollbar.js"></script>
<script>

    // 文件夹操作
    $(document).ready(function() {
        $(document).pjax('.fileList a', '#file_body');
        $(document).on('pjax:success', function() {
            scrollbar();
        })

    });

    // 查询搜索
    $(document).on('submit', '#form_search', function(event) {
        $.pjax.submit(event, '#file_body')
    })

    //上传文件操作
    var upList = true;
    function upload() {
        if(upList) {
            upList = !upList;
            layer.open({
                type: 2,
                area: ['400px', '300px'],
                title: '上传列表',
                //offset: 'rb', //右下角
                content: '/upload',
                shade: 0,//不显示遮罩
                cancel: function(index, layero) {
                    if(confirm("请确认文件已上传完毕\r\n关闭后将自动刷新目录")) {
                        layer.close(index)
                        upList = !upList;
                        Pjax('/openFolder');
                    }
                    return false;
                }
            });
        }
    }

    //新建文件夹
    function addFolder() {
        layer.prompt({title: '新建文件夹', value: '新建文件夹', formType: 3}, function(folder, index) {
            $.ajax({
                url: '/addFolder',
                type: 'POST',
                data: {folder: folder},
                success: function(data) {
                    Pjax('/openFolder');
                }
            });
            layer.close(index);
            layer.msg(folder + ' 创建成功', {offset: 't', time: 5000});
        });
    }

    //重命名
    function rename(id, f_name) {
        layer.prompt({title: '重命名', value: f_name, formType: 3}, function(name, index) {
            $.ajax({
                url: '/rename',
                type: 'POST',
                data: {id: id, name: name},
                success: function(data) {
                    Pjax('/openFolder');
                }
            });
            layer.close(index);
            layer.msg(name + ' 修改成功', {offset: 't', time: 5000});
        });
    }

    //分享地址
    function shareUrl(id) {
        $.ajax({
            url: '/getShare',
            type: 'POST',
            data: {id: id},
            success: function(data) {
                if(data.code == 200) {
                    layer.msg('分享地址：' + data.result.url, {
                        time: 15000, //15s后自动关闭
                    });
                    if(data.result.refresh) {
                        Pjax('/openFolder');
                    }
                }else {
                    layer.msg('请求异常', {
                        time: 3000, //3s后自动关闭
                    });
                }

            }
        });
    }

    /**
     * 关闭分享
     */
    function shareClose(id) {
        $.ajax({
            url: '/shareClose',
            type: 'POST',
            data: {'id': id},
            success: function(data) {
                console.log(data);
                layer.msg(data.msg, {
                    time: 3000, //3s后自动关闭
                });
                if(data.code == 200) {
                    Pjax('/openFolder');
                }
            }
        });
    }

    /**
     * 批量删除
     */
    function delFiles() {
        var cbox = new Array();
        $("input[name='checkbox']:checked").each(function() {
            cbox.push(this.value);
        });
        $.ajax({
            url: '/dels',
            type: 'POST',
            data: {ids: cbox},
            success: function(data) {
                if(data.code == 200) {
                    layer.msg(data.msg + '(' + data.result.count + '条)', {
                        time: 5000, //5s后自动关闭
                    });
                    Pjax('/openFolder');
                }else {
                    layer.msg('请求异常', {
                        time: 3000, //3s后自动关闭
                    });
                }
            }
        });
    }

    /**
     * ZIP方式批量下载 存在长连接超时 待改进
     */
    function zipDownFiles() {
        var cbox = new Array();
        $("input[name='checkbox']:checked").each(function() {
            cbox.push(this.value);
        });
        $.ajax({
            url: '/downFiles',
            type: 'POST',
            data: {ids: cbox},
            success: function(data) {
                if(data.code == 200) {
                    layer.msg(data.msg + '(' + data.result.count + '条)', {
                        time: 5000, //5s后自动关闭
                    });
                }else {
                    layer.msg('请求异常', {
                        time: 3000, //3s后自动关闭
                    });
                }
            }
        });
    }

    /**
     * 多文件下载
     */
    function downFiles() {
        $("input[name='checkbox']:checked").each(function() {
            var id = this.value;
            var ifr = document.createElement('iframe');
            ifr.style.display = 'none';
            ifr.src = '/getFile?id=' + id;
            document.body.appendChild(ifr);
        });
    }

    /**
     * 批量分享
     */
    function shareFiles() {
        var cbox = new Array();
        $("input[name='checkbox']:checked").each(function() {
            cbox.push(this.value);
        });
        $.ajax({
            url: '/shareFiles',
            type: 'POST',
            data: {ids: cbox},
            success: function(data) {
                layer.msg(data.msg, {
                    time: 3000, //3s后自动关闭
                });
                Pjax('/openFolder');
            }
        });
    }

    //pjax 刷新
    function Pjax(url) {
        $.pjax.reload({url: url, container: "#file_body", timeout: 5000});
        $(document).on('pjax:success', function() {
            scrollbar();
        })
    }

    //滚动条
    function scrollbar() {
        var height = $('#file_box').height();
        ue.scrollbar({
            height: height,
            scroll_per: 100,//每次滚动滑轮，滚动条移动10像素
            scrollbarbg: $(".v_scrollbar_bg"),
            target: $(".box"),
            box: $(".box_main"),
            scrollbar: $(".v_scrollbar"),
            btn: $(".v_scrollbar_btn")
        });
    }

    $(function() {
        scrollbar();
    })

</script>