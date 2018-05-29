<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>投放账号月报</title>
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap.min.css">
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/viewer.min.css" rel="stylesheet" type="text/css" />

    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/easyui/themes/default/easyui.css" rel="stylesheet" type="text/css" />

    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script src="/static/admin/js/viewer.min.js"></script>

    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/echart/echarts.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/easyui/jquery.easyui.min.js"></script>
</head>
<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">月份:</label>
                    <div class="controls">
                        <input type="text" id="month" value='<?php echo date("Y-m-01");?>' class="calendar" name="month"/>
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">账号：</label>
                    <div class="controls">
                        <input type="text" class="input-normal" name="account" id="account" />
                    </div>
                </div>
                <div class="control-group span6">
                    <div class="controls">
                        <input type="button" id="btnSearch" name="search" class="button button-primary" value="搜索" />
                        <input type="button" id="btnCreate" name="search" class="button button-info" value="录入" />
                        <input type="button" id="btnExport" name="search" class="button button-warning" value="导出" />
                    </div>
                </div>
            </div>
        </form>
        <form  method="post" action='<?php echo U("AdvterData/advterDetailMonth");?>' id="subfm">
            <input name="month" value="" type="hidden" />
            <input name="account" value="" type="hidden" />
            <input name="export" value=1 type="hidden" />
        </form>
    </div>
    <div class="search-grid-container span48">
        <div id="grid"></div>
    </div>
</div>

<!-- 弹窗 -->
<div id="content" class="hide"></div>

<script type="text/javascript">
    $(function() {
        BUI.use(["bui/calendar"], function (Calendar) {
            var DatePicker = new Calendar.DatePicker({
                trigger: "#month",
                autoRender: true
            });
        });
        doSearch();
    });

    $("#btnSearch").click(function(){
        doSearch();
    });

    //录入
    $("#btnCreate").click(function(){
        $(".bui-dialog:not(.bui-message)").remove();
        $(".mark").show();
        $(".spinner").show();
        $.get('<?php echo U("AdvterData/advterDetailAdd");?>', "", function(ret){
            if (ret.Result) {
                $("#content").html(ret.Html);
                $("#content").show();
            } else {
                BUI.Message.Alert(ret.Msg, "error");
            }
            $(".mark").hide();
            $(".spinner").hide();
        });
    });

    //导出
    $("#btnExport").click(function(){
        $("#subfm input[name=month]").val($("#month").val());
        $("#subfm input[name=account]").val($("#account").val());
        $("#subfm").submit();
    });

    function doSearch() {
        getData();
    }

    function getData(){
        var m = {}, _param = {}, _columns;
        m.month     = $("#month").val();
        m.account   = $("#account").val();
        if (m.month == "" || m.month == null) {
            alert("请选择时间！");
            return false;
        }
        _param      = m;
        _columns    = [[
            {title: "月份", field: "month", width: 120, align: "center"},
            {title: "账号", field: "account", width: 160, align: "center"},
            {title: "代理商", field: "proxyName", width: 200, align: "center"},
            {title: "渠道", field: "companyName", width: 100, align: "center"},
            {title: "充值", field: "cz", width: 80, align: "center"},
            {title: "转入", field: "zr", width: 80, align: "center"},
            {title: "赠送", field: "zs", width: 80, align: "center"},
            {title: "转出", field: "zc", width: 80, align: "center"},
            {title: "消耗", field: "xh", width: 250, align: "center"},
            {title: "余额", field: "balance", width: 100, align: "center"}
        ]];

        $("#grid").treegrid({
            cache: false,
            idField: "parentId",
            treeField: "month",
            height: 750,
            width: 2000,
            url: '<?php echo U("AdvterData/advterDetailMonth");?>',
            onBeforeLoad: function(row, param) {
                if (typeof param == "undefined") {param = {};}

                for (var p in _param) {
                    if (_param[p] == "" || _param[p] == 0 || _param[p] == null) continue;
                    param[p] = _param[p];
                }
                if ($("#btnSearch").attr("disable") == "true") {
                    $("#btnSearch").removeAttr("disable");
                    return false;
                } else {
                    $("#btnSearch").attr("disable", "true");
                }
                $("#grid").treegrid("reload");
            },
            onLoadSuccess: function(data) {
                $("#grid").removeAttr("disable");
            },
            columns: _columns
        });

    }
</script>

<style>
  .spinner {display:none; position: absolute;top: 50%; left: 50%; /* margin: 100px auto; */ width: 20px; height: 20px; position: absolute; } .container1 > div, .container2 > div, .container3 > div {width: 6px; height: 6px; background-color: #333; border-radius: 100%; position: absolute; -webkit-animation: bouncedelay 1.2s infinite ease-in-out; animation: bouncedelay 1.2s infinite ease-in-out; -webkit-animation-fill-mode: both; animation-fill-mode: both; } .spinner .spinner-container {position: absolute; width: 100%; height: 100%; } .container2 {-webkit-transform: rotateZ(45deg); transform: rotateZ(45deg); } .container3 {-webkit-transform: rotateZ(90deg); transform: rotateZ(90deg); } .circle1 { top: 0; left: 0; } .circle2 { top: 0; right: 0; } .circle3 { right: 0; bottom: 0; } .circle4 { left: 0; bottom: 0; } .container2 .circle1 {-webkit-animation-delay: -1.1s; animation-delay: -1.1s; } .container3 .circle1 {-webkit-animation-delay: -1.0s; animation-delay: -1.0s; } .container1 .circle2 {-webkit-animation-delay: -0.9s; animation-delay: -0.9s; } .container2 .circle2 {-webkit-animation-delay: -0.8s; animation-delay: -0.8s; } .container3 .circle2 {-webkit-animation-delay: -0.7s; animation-delay: -0.7s; } .container1 .circle3 {-webkit-animation-delay: -0.6s; animation-delay: -0.6s; } .container2 .circle3 {-webkit-animation-delay: -0.5s; animation-delay: -0.5s; } .container3 .circle3 {-webkit-animation-delay: -0.4s; animation-delay: -0.4s; } .container1 .circle4 {-webkit-animation-delay: -0.3s; animation-delay: -0.3s; } .container2 .circle4 {-webkit-animation-delay: -0.2s; animation-delay: -0.2s; } .container3 .circle4 {-webkit-animation-delay: -0.1s; animation-delay: -0.1s; } @-webkit-keyframes bouncedelay {0%, 80%, 100% { -webkit-transform: scale(0.0) } 40% { -webkit-transform: scale(1.0) } } @keyframes bouncedelay {0%, 80%, 100% {transform: scale(0.0); -webkit-transform: scale(0.0); } 40% {transform: scale(1.0); -webkit-transform: scale(1.0); } } 
  .mark{background-color: #fff;opacity: .5;top: 0; height: 100%; width: 100%; position: absolute;display: none;} 
</style>

<?php if(session('admin.role_id') == 17 or session('admin.role_id') == 25): ?>
    <style>
      .opt-btn,#export{display: none;}
    </style>
  <?php endif; ?>
<div class="mark"></div>
<div class="spinner">
  <div class="spinner-container container1">
    <div class="circle1"></div>
    <div class="circle2"></div>
    <div class="circle3"></div>
    <div class="circle4"></div>
  </div>
  <div class="spinner-container container2">
    <div class="circle1"></div>
    <div class="circle2"></div>
    <div class="circle3"></div>
    <div class="circle4"></div>
  </div>
  <div class="spinner-container container3">
    <div class="circle1"></div>
    <div class="circle2"></div>
    <div class="circle3"></div>
    <div class="circle4"></div>
  </div>
</div>
</body>
</html>