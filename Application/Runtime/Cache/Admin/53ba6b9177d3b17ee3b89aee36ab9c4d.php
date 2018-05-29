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
    <link href="/static/admin/css/main-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/easyui/themes/default/easyui.css" rel="stylesheet" type="text/css" />
    <!-- <link href="/static/admin/css/easyui/themes/default/icon.css" rel="stylesheet" type="text/css" /> -->
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/easyui/jquery.min.js"></script>
	<script type="text/javascript" src="/static/admin/js/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
</head>
<body>
<div class="header">
    <div class="dl-log">欢迎您，
        <span class="dl-log-user"><?php echo ($adminInfo["rolename"]); ?></span>
        <span class="dl-log-user"><?php echo ($adminInfo["username"]); ?></span>
        <span class="dl-log-user"><a href="javascript:;" id="edit" style="color:#fc1">[修改密码]</a></span>
        <a href="<?php echo U('Index/logout');?>" title="退出" class="dl-log-quit">[退出]</a>
    </div>
</div>
<div class="content">
    <div class="dl-main-nav">
        <ul id="J_Nav" class="nav-list ks-clear">
            <!-- <li class="nav-item dl-selected"><div class="nav-item-inner nav-home">首页</div></li> -->
            <?php if(is_array($topMenu)): $i = 0; $__LIST__ = $topMenu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><li class="nav-item" menu_id="<?php echo ($val["id"]); ?>"><div class="nav-item-inner nav-order"><?php echo L($val['name']);?></div></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
    <ul id="J_NavContent" class="dl-tab-conten">

    </ul>
</div>
<div id="editdiv" style="display:none;"></div>


<script>
    BUI.use('common/main',function(){
        var config = <?php echo ($leftmenu); ?>;
        new PageUtil.MainPage({
            modulesConfig : config
        });
    });
    $('#edit').click(function(){
        $.get("<?php echo U('Index/edit');?>",'',function(tpl){
            $('#editdiv').html(tpl);
            $('#editdiv').show();
        });
    })
</script>
</body>
</html>