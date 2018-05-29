<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>苹果日报</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
</head>
<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">日报时间：</label>
                    <div class="controls">
                        <input type="text" class="calendar" id="day" name="day" value="<?php echo date('Y-m-d', strtotime('-2 day'));?>">
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">母包名称：</label>
                    <div class="controls">
                        <select name="agent" id="agent"></select>
                    </div>
                </div>
                <div class="control-group span5">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">
                            搜索
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="search-grid-container span25">
        <div id="grid"></div>
    </div>
</div>

<!-- 弹窗 -->
<div id="content" class="hide"></div>

<script type="text/javascript">
    $(function () {
        agentList();
    });

    //母包格式化
    function agentList() {
        var _html = "<option value=''>--请选择母包--</option>";
        $.post("<?php echo U('Ajax/getAgent');?>", {gameType: 2}, function(ret) {
            var ret = eval("(" + ret + ")");
            $(ret).each(function(i, v) {
                _html += "<option value='" + v.agent + "'>" + v.agentAll + "</option>";
            });
            $("#agent").html(_html).comboSelect();
        });
    }

    BUI.use("common/search", function(Search) {
        var columns = [
            {title: "报告日期", dataIndex: "day", width: 100, elCls: "center"},
            {title: "苹果应用", dataIndex: "app", width: 250, elCls: "center"},
            {title: "商品ID", dataIndex: "package", width: 250, elCls: "center"},
            {title: "商品名称", dataIndex: "title", width: 150, elCls: "center"},
            {title: "商品金额", dataIndex: "price", width: 100, elCls: "center"},
            {title: "商品收益", dataIndex: "proceeds", width: 100, elCls: "center"},
            {title: "单位数量", dataIndex: "units", width: 80, elCls: "center"},
            {title: "货币单位", dataIndex: "currency", width: 80, elCls: "center"},
            {title: "抓取时间", dataIndex: "time", width: 150, elCls: "center"}
        ],
        store = Search.createStore('<?php echo U("Data/appleDaily");?>', {
            proxy: {
                save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType

                },
                method: "POST"
            },
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            plugins: [BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
        }),
        search = new Search({
            store: store,
            gridCfg: gridCfg
        }),
        grid = search.get("grid");
    });
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