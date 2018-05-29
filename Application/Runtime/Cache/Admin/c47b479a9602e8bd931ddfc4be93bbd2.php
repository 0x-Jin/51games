<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1, minimum-scale=1.0, maximum-scale=1, user-scalable=no"/>
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection" />
    <meta content="email=no" name="format-detection">
    <title><?php echo L("ADMIN_NAME");?></title>
    <link href="/static/admin/css/style.css" rel="stylesheet" type="text/css" />
</head>
<body class="login_body">
    <div class="message warning">
        <div class="inset">
            <div class="login-head">
                <h1>可以游戏</h1>
            </div>
            <form method="post" action="<?php echo U('Index/login');?>">
                <li>
                    <input type="text" class="text" name="name" placeholder="用户账号"><span class="icon user" />
                </li>
                <div class="clear"> </div>
                <li>
                    <input type="password" name="password" placeholder="用户密码" /><span class="icon lock"></span>
                </li>
                <div class="clear"> </div>
                <li class="float-lt" style="width: 40%;">
                    <input type="text" class="text" name="code" placeholder="验证码" />
                </li>
                <ul class="verify_img float-lt">
                    <img alt="验证码" title="点击刷新" id="verify" src="<?php echo U('Admin/Index/verify', array());?>" />
                </ul>
                <div class="clear"> </div>
                <div class="submit">
                    <input type="submit" value="登  陆" >
                    <div class="clear">  </div>
                </div>
            </form>
        </div>
    </div>
</body>
<script language="javascript" type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
<script type="text/javascript">
    var img = $('#verify');
    var src = img.attr("src");
    img.click(function(){
        if(src.indexOf('?') > 0){
            $(this).attr("src", src + '&random=' + Math.random());
        }else{
            $(this).attr("src", src.replace(/\?.*$/,'') + '?' + Math.random());
        }
    });
</script>
</html>