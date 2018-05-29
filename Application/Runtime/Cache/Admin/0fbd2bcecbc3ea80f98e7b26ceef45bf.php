<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo L('PAGE_MSG');?></title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        *{ padding:0; margin:0; font-size:12px}
        a:link,a:visited{text-decoration:none; color:#0068a6}
        a:hover,a:active{color:#ff6600; text-decoration: underline}
        .showMsg{position:absolute; top:44%; left:50%; margin:-87px 0 0 -225px}
    </style>
</head>

<body>
    <div class="container showMsg">
        <div class="row">
            <div class="span10">
                <div class="tips tips-large tips-warning">
                    <span class="x-icon x-icon-error">×</span>
                    <div class="tips-content">
                        <h2><?php echo ($error); ?></h2>
                        <p class="auxiliary-text">
                            温馨提示：
                        </p>
                        <p>
                            页面将在<span id="wait" style="color:blue;font-weight:bold"><?php echo ($waitSecond); ?></span>秒后自动跳转，如果不想等待，直接点击<a id="href" href="<?php echo ($jumpUrl); ?>">这里</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>
</html>