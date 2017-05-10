<html>
<head>
    <meta charset="utf-8">
    <title>{{ isset($title)? $title:'Tiki' }}</title>
    <link href="style/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="style/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="style/css/style.css" rel="stylesheet"/>
    <link href="style/css/scroll.css" rel="stylesheet" type="text/css"/>
    <script>

    </script>
</head>
<body>

<section class="content-section">
    <div class="row">
        <div class="col-md-12">
            <nav role="navigation" class="navbar navbar-inverse dark-nav">
                <div class="navbar-header">
                    <a href="/" class="navbar-brand">TIKI - C</a>
                </div>
                <div id="bs-example-navbar-collapse-3" class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        @if(isset($user))
                            <li><a>{{ $user->email }}</a></li>
                            <li class="dropdown">
                                <a data-toggle="dropdown" class="dropdown-menu-right" href="#">设置 <b class="caret"></b></a>
                                <ul role="menu" class="dropdown-menu" style="text-align:right;">
                                    <li><a href="#"> 用户 &nbsp;</a></li>
                                    <li><a href="/logout"> 退出 &nbsp;</a></li>
                                </ul>
                            </li>
                        @else
                            <li><a href="/login"> 登录 </a></li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>
    </div>