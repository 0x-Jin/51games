<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>时间戳</title>
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
                <div class="control-group span5">
                    <label class="control-label" style="width: 90px;">当前时间戳：</label>
                    <div class="controls">
                        <span id="nowTimestamp" ondblclick="copyTimestamp('nowTimestamp')"></span>
                    </div>
                </div>
                <div class="control-group span4">
                    <div class="controls">
                        <button type="button" id="timestampKeyButton" onclick="changeKey()" class="button button-danger">停止</button>
                        <button type="button" onclick="flushTimestamp()" class="button button-info">刷新</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span24">
                    <div class="controls">
                        <input type="text" name="input" id="input" class="input-text" value="" />
                        <button type="button" onclick="changeTimestamp()" class="button button-primary">转时间戳</button>
                        <button type="button" onclick="changeDate()" class="button button-primary">转日期</button>
                        <input type="text" name="output" id="output" readonly class="input-text" value="" ondblclick="copyTimestamp('output')" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span24">
                    <div class="controls">
                        <input type="text" name="year" id="year" class="input-text" style="width: 26px;" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="4" value="" />
                        年
                        <input type="text" name="month" id="month" class="input-text" style="width: 13px;" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="2" value="" />
                        月
                        <input type="text" name="day" id="day" class="input-text" style="width: 13px;" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="2" value="" />
                        日
                        <input type="text" name="hour" id="hour" class="input-text" style="width: 13px;" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="2" value="" />
                        时
                        <input type="text" name="minute" id="minute" class="input-text" style="width: 13px;" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="2" value="" />
                        分
                        <input type="text" name="second" id="second" class="input-text" style="width: 13px;" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="2" value="" />
                        秒
                        <button type="button" onclick="dayChangeTimestamp()" class="button button-primary">转时间戳</button>
                        <input type="text" name="outputTimestamp" id="outputTimestamp" readonly class="input-text" value="" ondblclick="copyTimestamp('outputTimestamp')" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    var timeKey = 1;

    $(function(){
        $("#nowTimestamp").html(Math.round(new Date().getTime()/1000));
        //实时更新
        setInterval("timestampAdd()", 500);
    });

    //当前的时间戳
    function timestampAdd() {
        if (timeKey == 1) {
            flushTimestamp();
        }
    }

    //开始或停止时间戳刷新
    function changeKey() {
        if (timeKey == 1) {
            timeKey = 0;
            $("#timestampKeyButton").attr("class", "button button-success").html("开始");
        } else {
            timeKey = 1;
            $("#timestampKeyButton").attr("class", "button button-danger").html("停止");
        }
    }
    
    //刷新时间戳
    function flushTimestamp() {
        $("#nowTimestamp").html(Math.round(new Date().getTime()/1000));
    }

    //日期转时间戳
    function changeTimestamp() {
        var dateTime =  $("#input").val().replace(/-/g,'/');
        var timestamp = Date.parse(new Date(dateTime))/1000;
        $("#output").val(timestamp? timestamp: 0);
    }

    //时间戳转日期
    function changeDate() {
        var now = new Date($("#input").val()*1000);
        var year = now.getFullYear();
        var month = now.getMonth()+1;
        var day = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        var second = now.getSeconds();
        if (year) {
            $("#output").val(year + "-" + (month < 10? "0" + month: month) + "-" + (day < 10? "0" + day: day) + " " + (hour < 10? "0" + hour: hour) + ":" + (minute < 10? "0" + minute: minute) + ":" + (second < 10? "0" + second: second));
        } else {
            $("#output").val("0")
        }
    }

    //日期转时间戳
    function dayChangeTimestamp() {
        var year = $("#year").val();
        var month = $("#month").val();
        var day = $("#day").val();
        var hour = $("#hour").val();
        var minute = $("#minute").val();
        var second = $("#second").val();
        var timestamp = Date.parse(new Date((year? year: 0) + "-" + (month? month: 0) + "-" + (day? day: 0) + " " + (hour? hour: 0) + ":" + (minute? minute: 0) + ":" + (second? second: 0)))/1000;
        $("#outputTimestamp").val(timestamp? timestamp: 0);
    }

    //复制时间戳
    function copyTimestamp(str) {
        $("#"+str).select();
        document.execCommand("Copy");   //执行浏览器复制命令
        BUI.Message.Show({
            msg: "复制成功！",
            icon: "success",
            buttons: [],
            autoHide: true,
            autoHideDelay: 1000
        });
    }
</script>