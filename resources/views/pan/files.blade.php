{{--<script type="text/javascript" src="style/js/jquery.min.1.6.1.js"></script>--}}

<div style="border: 2px solid #fff;">
    <table class="table fileList" align="center">
        <thead>
        <th width="5%">#</th>
        <th width="30%"> 名称 <a href="/closeFolder"><i class="fa fa-arrow-circle-o-left" title="上一层"></i></a></th>
        <th width="10%">大小</th>
        <th width="25%">上传时间</th>
        <th width="15%">上传用户</th>
        <th width="15%">操作 <i class="fa fa-toggle-on"></i></th>
        </thead>
    </table>
    <div style="width:100%; height:65%;" class="c_files">
        <div class="box" id="file_box">
            <div class="box_inner">
                <div class="box_main">
                    <div class="tables" id="file_body">
                        <div class="lt-box">
                            <div class="table-responsive">
                                <table class="table table-hover t-fixed t-color-gray">
                                    <tbody>
                                    @foreach($data as $item)
                                        <tr>
                                            <td width="5%"><input type="checkbox" name="checkbox" value="{{$item->id}}"
                                                                  @if($item->type == 0) disabled @endif></td>
                                            <td width="30%">
                                                <div class="outer90 fileList">
                                                    <a @if($item->type == 0)href="/openFolder?pre={{$item->id}}" @endif>
                                                        <span title="{{$item->name}}">
                                                            <i class="fa {{ $type[$item->type] }}"></i> {{$item->name}}
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td width="10%">{{$item->size ?: '--'}}</td>
                                            <td width="25%"> {{$item->updated_at}}</td>
                                            <td width="15%">
                                                <div class="outer90">
                                                    {{ $user->name }}
                                                </div>
                                            </td>
                                            <td width="15%" class="actions-hover actions-fade fileList">
                                                <a onclick="rename({{$item->id}} ,'{{$item->name}}')" title="重命名">
                                                    <i class="fa fa-pencil"> </i>
                                                </a>
                                                @if($item->type)
                                                    <a href="/getFile?id={{$item->id}}" title="下载文件">
                                                        <i class="fa fa-download"> </i>
                                                    </a>
                                                    @if($item->share)
                                                        <a onclick="shareUrl({{$item->id}})" title="查看地址">
                                                            <i class="fa fa-search"> </i>
                                                        </a>
                                                        <a onclick="shareClose({{$item->id}})" title="关闭分享">
                                                            <i class="fa fa-close"> </i>
                                                        </a>
                                                    @else
                                                        <a onclick="shareUrl({{$item->id}})" title="分享">
                                                            <i class="fa fa-share"> </i>
                                                        </a>
                                                    @endif
                                                @endif
                                                <a href="/delete?id={{$item->id}}" title="删除">
                                                    <i class="fa fa-trash"> </i> </a>
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
            <div class="v_scrollbar" style="display: none;">
                <div class="v_scrollbar_bg"></div>
                <div class="v_scrollbar_btn"></div>
            </div>
        </div>
    </div>
</div>