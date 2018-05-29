<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>API</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
</head>
<body>

<div class="container">
    <div class="row">
        <form class="form-horizontal span48">
            <div class="row">
                <div class="control-group span10">
                    <label class="control-label" style="width: 90px;">游戏KEY：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="key" id="key" style="width: 250px;" />
                    </div>
                </div>
                <div class="control-group span10">
                    <label class="control-label" style="width: 90px;">用户token：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="token" id="token" style="width: 250px;" placeholder="无则无须输入！" />
                    </div>
                </div>
                <div class="control-group span3">
                    <div class="controls">
                        <button type="button" onclick="doDecrypt()" class="button button-primary">解密</button>
                    </div>
                </div>
            </div>
            <div class="row" style="height: 130px;">
                <div class="control-group span24">
                    <label class="control-label" style="width: 90px;">数据密文：</label>
                    <div class="controls">
                        <textarea name="info" id="info" class="input-text" style="width: 800px; height: 100px; resize: none;"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span24">
                    <label class="control-label" style="width: 90px;">数据原文：</label>
                    <div class="controls">
                        <textarea readonly="readonly" id="data" value="" class="input-text" style="width: 800px; height: 100px; resize: none;"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    function doDecrypt(){
        $.ajax({
            type: "POST",
            url: "<?php echo U('Decrypt/apiDecrypt');?>",
            data: "key="+$("#key").val()+"&token="+$("#token").val()+"&info="+encodeURIComponent($("#info").val()),
            async: true,
            dataType: "json",
            success: function(data){
                if (data.code == 1) {
                    $("#data").html(data.data);
                } else {
                    $("#data").html("");
                    BUI.Message.Alert(data.data, "error");
                }
            },
            error: function(){
                $("#data").html("");
                BUI.Message.Alert("网络错误，请重试", "error");
            }
        });
    }
</script>