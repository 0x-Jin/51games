<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>官网静态化</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" src="/static/admin/js/ueditor/ueditor.all.js"></script>
    <script type="text/javascript" src="/static/admin/js/ueditor/lang/zh-cn/zh-cn.js"></script>
</head>
<body>

<style type="text/css">
    input{border-radius: 4px;}
</style>
<div class="demo-content">
    
    <br />
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Website/webStatic');?>" enctype="multipart/form-data">
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">官网名称：</label>
                <div class="controls">
                    <select name="home_id" id="home"></select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">选择栏目：</label>
                <div class="controls" id="column">
                    <select name="stype">
                        <option value="0">全部</option>
                        <?php if(is_array($col)): $i = 0; $__LIST__ = $col;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">官网：</label>
                <div class="controls">
                    <select name="webtype">
                        <option value="1">PC官网</option>
                        <option value="2">H5官网</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span30">
                <label class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="button button-primary" value="提交" />
                </div>
            </div>
        </div>
    </form>
</div>

<!-- script start -->
<script type="text/javascript">
    $(function () {
        homeLists();
    });


    //获取官网
    function homeLists(){
        var _html = '';
        $.post("<?php echo U('Ajax/getWebsiteHome');?>", "", function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i, v){
                _html += "<option value="+v.id+">"+v.name+"</option>";
            });
            $('#home').html(_html).comboSelect();
        });
    }

</script>
<!-- script end -->
</div>
</body>
</html>