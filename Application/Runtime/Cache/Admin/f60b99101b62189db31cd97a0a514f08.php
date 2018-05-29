<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>软件下载</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
</head>
<body>
<div class="container">
    <div class="search-grid-container" style="width: 1000px;">
        <p>请下载平台登录软件进行账号登陆</p>

        <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/<?php echo ($exe["win"]); ?>"><?php echo ($exe["name"]); ?>WIN版本</a>

        <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/<?php echo ($exe["mac"]); ?>"><?php echo ($exe["name"]); ?>MAC版本</a>

        </div>
</div>
</body>
</html>