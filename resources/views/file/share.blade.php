@include('pan.header')
<!-- //header -->
<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-offset-2 col-md-8">
            <div class="row" style="margin-top:32px;">
                <div class="col-xs-6">
                    <h4>{{ $data['file_name'] }}</h4>
                </div>
                <div class="col-xs-6">
                    <ul class="demo-btns pull-right">
                        <li><a onclick="shareLike('{{ $data['token'] }}')" class="btn btn-info-alt"><i
                                        class="fa fa-heart-o">
                                    点赞 (<span id="like">{{ $data['like_nums'] }}</span>)</i>
                            </a></li>
                        <li><a onclick="shareRepoet('{{ $data['token'] }}')" class="btn btn-info-alt"><i
                                        class="fa fa-ban">
                                    举报 (<span id="report">{{ $data['report_nums'] }}</span>)</i>
                            </a></li>
                    </ul>
                </div>
                <div class="col-xs-12">
                    <p>分享者：{{ $data['user'] }} {{ $data['time'] }} · <span style="color:#00b7ee">{{ $data['down_nums'] }}
                            次下载</span></p>
                </div>
                <div class="col-xs-12 text-center" style="margin-top:60px">
                    <img src="http://pic.sucaibar.com/pic/201306/29/3ef2d25abe.png" width="180px">
                </div>
                <div class="col-xs-12 text-center" style="margin-top: 30px">
                    <a href="/getShareFile?token={{ $data['token'] }}" class="btn btn-info-alt">{{ $data['file_size'] }}
                        立即下载</a>
                </div>
            </div>
        </div>
        <div class="col-xs-12 text-center" style="margin-top: 60px">
            <img src="http://www.xujc.com/uploads2/img/2016/12/06/20161206140228.jpg" width="80%" height="130px">
        </div>
    </div>
</div>

<!-- footer -->
@include('pan.footer')

<script>

    //点赞
    function shareLike(token) {
        $.ajax({
            url: '/shareLike',
            type: 'POST',
            data: {token: token},
            success: function(data) {
                if(data.code == 200) {
                    document.getElementById("like").innerHTML = data.result.num;
                    layer.msg(data.msg, {
                        time: 5000, //15s后自动关闭
                    });
                }else {
                    layer.msg('请求异常', {
                        time: 3000, //3s后自动关闭
                    });
                }

            }
        });
    }

    //举报
    function shareRepoet(token) {
        $.ajax({
            url: '/shareReport',
            type: 'POST',
            data: {token: token},
            success: function(data) {
                if(data.code == 200) {
                    document.getElementById("report").innerHTML = data.result.num;
                    layer.msg(data.msg, {
                        time: 5000, //15s后自动关闭
                    });
                }else {
                    layer.msg('请求异常', {
                        time: 3000, //3s后自动关闭
                    });
                }

            }
        });
    }
</script>