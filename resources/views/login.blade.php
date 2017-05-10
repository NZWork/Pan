Title: {{ empty($title) ? '登入' : $title }}<br>
Msg: {{ empty($msg) ? '' : $msg }}<br>
<iframe src='https://oauth.tiki.im/auth?response_type=code&redirect_uri=https://app.dev.tiki.im/login/callback&client_id=test&state=2'></iframe>