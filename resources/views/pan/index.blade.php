<!-- header -->
@include('pan.header')
<!-- //header -->

<div class="container">
    <div class="row">
        <div class="col-md-2">
            <div class="lt-navs">
                <ul class="nav nav-pills nav-stacked fileList" style="max-width: 150px;">
                    <li class="active"><a href="/">所有资料</a></li>
                    <li><a href="/?type=2"><i class="fa fa-file"></i> 我的文档</a></li>
                    <li><a href="/?type=3"><i class="fa fa-picture-o"></i> 我的图片</a></li>
                    <li><a href="/?type=4"><i class="fa fa-music"></i> 我的娱乐</a></li>
                    <li><a href="/?type=5"><i class="fa fa-btc"></i> BT 资源</a></li>
                    <li><a href="/?type=1"><i class="fa fa-outdent"></i> 其他</a></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            组织共享库 <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#">NZWork</a></li>
                            <li class="divider"></li>
                            <li><a href="#">毕业设计</a></li>
                            <li><a href="#">软考资料</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-8">
                    <ul class="demo-btns">
                        <li><a class="btn btn-default-alt" id="upload" onclick="upload()"><i class="fa fa-cloud-upload">
                                    上传资料 </i></a></li>
                        <li><a href="#" class="btn btn-primary-alt" id="addFolder" onclick="addFolder()"><i
                                        class="fa fa-folder"> 新建文件夹 </i></a></li>
                        <li><a onclick="delFiles()" class="btn btn-success-alt"><i class="fa fa-trash-o"> 删除 </i></a>
                        </li>
                        <li><a onclick="downFiles()" class="btn btn-info-alt"><i class="fa fa-cloud-download"> 下载 </i></a></li>
                        <li><a href="/" class="btn btn-warning-alt"><i class="fa fa-retweet"> 移动 </i></a></li>
                        <li><a onclick="shareFiles()" class="btn btn-danger-alt"><i class="fa fa-share-square"> 分享 </i></a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <form href="/" method="get" id="form_search">
                        <div class="input-group">
                            <input type="text" name="search" value="{{isset($search) ? $search : ''}}"
                                   placeholder="请输入搜索关键字" class="form-control">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-danger">Go</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            <br>
            {{--<iframe name="files" id="files" src="/files" scrolling="true" frameborder="0" width="100%" height="75%">

            </iframe>--}}
            @include('pan.files')
        </div>
    </div>
</div>

<!-- footer -->
@include('pan.footer')
<!-- //footer -->
