<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
 <head>
  <title> 页面操作快捷方式</title>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
       <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />   <!-- 下面的样式，仅是为了显示代码，而不应该在项目中使用-->
   <link href="/static/admin/css/prettify.css" rel="stylesheet" type="text/css" />
   <style type="text/css">
    code {
      padding: 0px 4px;
      color: #d14;
      background-color: #f7f7f9;
      border: 1px solid #e1e1e8;
    }
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
        <div class="tips tips-large tips-success">
          <span class="x-icon x-icon-success"><i class="icon icon-ok icon-white"></i></span>
          <div class="tips-content">
            <h2><?php echo ($message); ?></h2>
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
<body>
</html>