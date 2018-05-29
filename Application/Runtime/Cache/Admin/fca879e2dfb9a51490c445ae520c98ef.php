<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>版本补丁</title>
    <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bui-min2.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script src="/static/admin/js/jquery.combo.select.js" type="text/javascript"></script>
    <script src="/static/admin/js/bui.js" type="text/javascript"></script>
    <script src="/static/admin/js/config.js" type="text/javascript"></script>
    <script src="/static/admin/js/echart/echarts.min.js" type="text/javascript"></script>
</head>
<style>
    .copy_that{
        width: 50px;
        height: 100%;
        color: rgb(51, 102, 204);
        cursor: pointer;
        display: block;
        white-space: nowrap;
        overflow: hidden;
    }
</style>
<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">状态：</label>
                    <div class="controls">
                        <select name="status" style="width: 100px;">
                            <option value="0">全部</option>
                            <option value="1">开启</option>
                            <option value="2">关闭</option>
                        </select>
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">过期：</label>
                    <div class="controls">
                        <select name="show" style="width: 100px;">
                            <option value="1">不显示</option>
                            <option value="0">显示</option>
                        </select>
                    </div>
                </div>
                <div class="control-group span15">
                    <label class="control-label" style="width: 60px;">搜索条件：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="search" style="width: 400px;" placeholder="可输入游戏、渠道、渠道号、版本、IOS或安卓，多个条件用“|”分隔开">
                    </div>
                </div>
                <div class="control-group span3">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="search-grid-container">
        <div id="grid"></div>
    </div>
</div>
<!-- 弹窗 -->
<div id="content" class="hide"></div>
<script type="text/javascript">
    //编辑
    function patchEdit(id, obj) {
        if(!id) return false;
        $(".bui-dialog:not(.bui-message)").remove();
        $(".mark").show();
        $(".spinner").show();
        $.get("<?php echo U('Game/patchEdit');?>", {id:id}, function(ret) {
            $("#content").html(ret.Html);
            $("#content").show();
            $(".mark").hide();
            $(".spinner").hide();
        });
    }

    //复制
    function copyUrl(id)
    {
        $("#"+id).select();
        document.execCommand("Copy");
        alert("复制成功");
    }

    var num = 0;

    BUI.use("common/search", function (Search) {
        var columns = [
            {title:"ID", dataIndex:"id", width:40, elCls:"center"},
            {title:"开启时间", dataIndex:"start", width:130, elCls:"center"},
            {title:"关闭时间", dataIndex:"end", width:130, elCls:"center"},
            {title:"状态", dataIndex:"status", width:40, elCls:"center",/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer: function (value, obj) {
                if (value == 0) {
                    //开启
                    return "<img src='/static/admin/img/toggle_enabled.gif'>";
                } else if (value == 1) {
                    //关闭
                    return "<img src='/static/admin/img/toggle_disabled.gif'>";
                }
            }},
            {title:"SDK版本", dataIndex:"ver", width:60, elCls:"center"},
            {title:"补丁版本", dataIndex:"patchVer", width:60, elCls:"center"},
            {title:"更新条件", dataIndex:"map", width:350, elCls:"center"},
            {title:"备注", dataIndex:"ext", width:350, elCls:"center"},
            {title:"下载地址", dataIndex:"path", width:60, elCls:"center",/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer: function (value, obj) {
                num += 1;
                return "<span onclick=copyUrl('t"+num+"') class='copy_that' title='点击复制'>点击复制&nbsp;&nbsp;&nbsp;&nbsp;<textarea style='width: 0px; height: 0px;' id='t"+num+"'>"+value+"</textarea></span>";
            }},
            {title:"创建时间", dataIndex:"create", width:130, elCls:"center"},
            {title:"操作", dataIndex:"opt", width:40, elCls:"center",/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer: function (value, obj) {
                return "<a href='javascript:;' onclick='patchEdit(\""+obj.id+"\", this)'>编辑</a>";
            }}
        ],
        store = Search.createStore("<?php echo U('Game/patch');?>",{
            proxy: {
                save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType

                },
                method: "POST"
            },
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            tbar: {
                items: [
                    {text: "<i class='icon-plus'></i>新增补丁", btnCls: "button button-small opt-btn", handler: addFunction}
                ]
            },
            plugins: [BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
        }),
        search = new Search({
            store: store,
            gridCfg: gridCfg
        }),
        grid = search.get("grid");
        function addFunction() {
            $(".bui-dialog:not(.bui-message)").remove();
            $(".mark").show();
            $(".spinner").show();
            $.get("<?php echo U('Game/patchAdd');?>", "", function(ret) {
                $("#content").html(ret.Html);
                $("#content").show();
                $(".mark").hide();
                $(".spinner").hide();
            });
        }
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