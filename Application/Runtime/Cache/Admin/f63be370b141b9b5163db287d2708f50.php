<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>MD5</title>
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
            <div class="row" style="height: 130px;">
                <div class="control-group span24">
                    <label class="control-label" style="width: 90px;">字符串：</label>
                    <div class="controls">
                        <textarea name="info" id="info" class="input-text" style="width: 800px; height: 100px; resize: none;"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span24">
                    <div style="margin: 0 0 20px 400px;">
                        <button type="button" onclick="doMd5()" class="button button-primary">加密</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span24">
                    <label class="control-label" style="width: 90px;">密文：</label>
                    <div class="controls">
                        <span id="data"></span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    function doMd5(){
        $.ajax({
            type: "POST",
            url: "<?php echo U('Decrypt/md5Decrypt');?>",
            data: "info="+encodeURIComponent($("#info").val()),
            async: true,
            dataType: "json",
            success: function(data){
                if (data.code == 1) {
                    var str = "";
                    str += "16位小写&nbsp;&nbsp;:&nbsp;&nbsp;<span ondblclick=\"copyStr('l16')\" id='l16'>" + data.data.l16 + "</span><br>";
                    str += "16位大写&nbsp;&nbsp;:&nbsp;&nbsp;<span ondblclick=\"copyStr('u16')\" id='u16'>" + data.data.u16 + "</span><br>";
                    str += "32位小写&nbsp;&nbsp;:&nbsp;&nbsp;<span ondblclick=\"copyStr('l32')\" id='l32'>" + data.data.l32 + "</span><br>";
                    str += "32位大写&nbsp;&nbsp;:&nbsp;&nbsp;<span ondblclick=\"copyStr('u32')\" id='u32'>" + data.data.u32 + "</span>";
                    $("#data").html(str);
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

    //复制密文
    function copyStr(str) {
        $("#"+str).select();
        document.execCommand("Copy");   //执行浏览器复制命令
        var type = "";
        if (str == "l16") {
            type = "16位小写";
        } else if (str == "u16") {
            type = "16位大写";
        } else if (str == "l32") {
            type = "32位小写";
        } else if (str == "u32") {
            type = "32位大写";
        }
        BUI.Message.Show({
            msg: "复制" + type + "密文成功！",
            icon: "success",
            buttons: [],
            autoHide: true,
            autoHideDelay: 1000
        });
    }
</script>