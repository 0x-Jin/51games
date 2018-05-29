<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>渠道号列表</title>
    <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
    <script type="text/javascript" src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
</head>

    <style>
        tfoot .bui-grid-cell-text{text-align: center;}
        .btn-default {height:25px;}
        .filter-option {margin-top: -4px;}
        .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
    </style>

<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">包编号：</label>
                    <div class="controls">
                        <input name="agent" type="text" class="input-normal control-text">
                    </div>
                </div>
                <div class="control-group span8">
                    <div class="controls">
                        <button type="button" id="btnSearch" class="button button-primary">搜索</button>
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
<div id="content" class="hide">
</div>
<script type="text/javascript">
    //修改状态
    function set_status (status, id, obj) {
        var str = "", tip = "";
        if (status == 1) {
            str = "enabled";
            tip = "开启";
        } else {
            str = "disabled";
            tip = "关闭";
        }
        BUI.Message.Confirm("是否确认"+tip+"？", function() {
            var _status = 1 - status, data = {id: id, status: _status, change: 1};
            $.post ('<?php echo U("Advter/paramEdit");?>', data, function (ret) {
                if (ret.Result == 1) {
                    $(obj).attr({src:"/static/admin/img/toggle_"+str+".gif", onclick:"set_status("+_status+","+id+",this)"});
                    BUI.Message.Alert(tip+"成功！", "success");
                } else {
                    BUI.Message.Alert(ret.Msg, "error");
                }
            });
        }, "question");
    }

    //编辑
    function paramEdit (id) {
        $(".bui-dialog:not(.bui-message)").remove();
        $(".mark").show();
        $(".spinner").show();
        $.get("<?php echo U('Advter/paramEdit');?>", {id: id}, function (ret) {
            if (ret.Result) {
                $("#content").html(ret.Html).show();
                $(".mark").hide();
                $(".spinner").hide();
            } else {
                BUI.Message.Alert(ret.Msg, "error");
            }
        });
    }

    BUI.use("common/search", function (Search) {
        var columns = [
            {title: "ID", dataIndex: "id", width: 50, elCls: "center"},
            {title: "包编号", dataIndex: "agent", width: 120, elCls: "center"},
            {title: "包名称", dataIndex: "agentName", width: 280, elCls: "center"},
            {title: "广告商", dataIndex: "advteruserName", width: 80, elCls: "center"},
            {title: "状态", dataIndex: "status", width: 60, elCls: "center", renderer: function (value, obj) {
                if (value == 0) {
                    //正常
                    return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick=set_status('+value+','+obj.id+',this) data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
                } else if(value == 1) {
                    //禁止
                    return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick=set_status('+value+','+obj.id+',this) data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
                }
            }},
            {title: "创建时间", dataIndex: "createTime", width: 130, elCls: "center",/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer: function (value, obj) {
                return formatDate(value);
            }},
            {title: "操作", dataIndex: "opt", width: 100, elCls: "center",/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer: function (value, obj) {
                return "<a href='javascript:;' onclick='paramEdit(\""+obj.id+"\")'>编辑</a>";
            }}
        ],
        store = Search.createStore('<?php echo U("Advter/adverParam");?>', {
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
                    {text: '<i class="icon-plus"></i>新增配置', btnCls: 'button button-small opt-btn', handler: addFunction}
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
            $.get('<?php echo U("Advter/paramAdd");?>', "", function (ret) {
                if (ret.Result) {
                    $("#content").html(ret.Html).show();
                    $(".mark").hide();
                    $(".spinner").hide();
                } else {
                    BUI.Message.Alert(ret.Msg, "error");
                }
            });
        }
    });

    //时间转换
    function formatDate(time) {
        var now = new Date(time*1000);
        var year = now.getFullYear();
        var month = now.getMonth()+1;
        var date = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        var second = now.getSeconds();
        return year+"-"+(month<10?"0"+month:month)+"-"+(date<10?"0"+date:date)+" "+(hour<10?"0"+hour:hour)+":"+(minute<10?"0"+minute:minute)+":"+(second<10?"0"+second:second);
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