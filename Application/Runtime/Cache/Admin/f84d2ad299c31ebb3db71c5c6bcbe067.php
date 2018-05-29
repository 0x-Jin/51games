<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>礼包列表</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
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
                <div class="control-group span8">
                    <label class="control-label">礼包名称：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="gift">
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
                <div class="control-group span8">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
    <!--<b style="color: red; margin-left: 10px; font-size: 16px;">PS：礼包类型中，不限指的是礼包可以无限次使用，限制指的是一个礼包只能使用一次</b>-->
    <div class="search-grid-container">
        <div id="grid"></div>
    </div>
</div>

<!-- 弹窗 -->
<div id="content" class="hide">

</div>

<script type="text/javascript">
    //编辑
    function giftEdit(id, obj){
        $(".bui-dialog:not(.bui-message)").remove();
        $(".mark").show();
        $(".spinner").show();
        $.get('<?php echo U("Gift/giftEdit");?>', {id:id}, function(ret){
            $("#content").html(ret.Html).show();
            $(".mark").hide();
            $(".spinner").hide();
        });
    }

    //导入
    function giftImport(id, obj) {
        $(".bui-dialog:not(.bui-message)").remove();
        $(".mark").show();
        $(".spinner").show();
        $.get('<?php echo U("Gift/giftImport");?>', {id:id}, function(ret){
            $("#content").html(ret.Html).show();
            $(".mark").hide();
            $(".spinner").hide();
        });
    }

    //复制
    function copyUrl(id) {
        $("#"+id).select();
        document.execCommand("Copy");
        alert("复制成功");
    }

    var num = 1;

    BUI.use("common/search", function (Search) {
        var columns = [
            {title: "ID", dataIndex: "id", width: 40, elCls: "center"},
            {title: "礼包名称", dataIndex: "gift", width: 200, elCls: "center"},
            {title: "礼包内容", dataIndex: "content", width: 350, elCls: "center"},
            {title: "部门", dataIndex: "departmentName", width: 70, elCls: "center"},
            {title: "微信链接", dataIndex: "url", width:60, elCls:"center", /*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer: function (value, obj) {
                num += 1;
                return "<span onclick=copyUrl('t"+num+"') class='copy_that' title='点击复制'>点击复制&nbsp;&nbsp;&nbsp;&nbsp;<textarea style='width: 0px; height: 0px;' id='t"+num+"'>"+value+"</textarea></span>";
            }},
//            {title: "领取范围", dataIndex: "ext", width: 300, elCls: "center"},
//            {title: "类型", dataIndex: "type", width: 50, elCls: "center", renderer: function (value, obj) {
//                return value == 1? "限制": "不限"
//            }},
            {title: "礼包库存", dataIndex: "stock", width: 150, elCls: "center"},
            {title: "开启时间", dataIndex: "start", width: 150, elCls: "center"},
            {title: "结束时间", dataIndex: "end", width: 150, elCls: "center"},
            {title: "操作", dataIndex: "opt", width: 70, elCls: "center"}
        ],
        store = Search.createStore('<?php echo U("Gift/index");?>', {
            proxy: {
                save: {},
                method: "POST"
            },
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            tbar: {
                items: [
                    {text: "<i class='icon-plus'></i>新增礼包", btnCls: "button button-small opt-btn", handler: addFunction}
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
            $.get('<?php echo U("Gift/giftAdd");?>', "", function(ret){
                $("#content").html(ret.Html).show();
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