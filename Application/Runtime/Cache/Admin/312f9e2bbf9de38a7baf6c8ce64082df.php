<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>崩溃日志</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
</head>

    <style>
        tfoot .bui-grid-cell-text{text-align: center;}
    </style>

<body>

<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">日期：</label>
                    <div class="controls">
                        <input type="text" class="calendar" id="startDate" name="date" value="<?php echo date('Y-m-d');?>">
                    </div>
                </div>


                    <div class="control-group span8">
                        <label class="control-label">游戏类型：</label>
                        <div class="controls">
                            <select name="type" id="type">
                                <option value="0" selected="selected">全部</option>
                                <option value="1">ANDROID</option>
                                <option value="2">IOS</option>
                            </select>
                        </div>
                    </div>


                <div class="control-group span5">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">
                            搜索
                        </button>
                        <button  type="button" onclick="doExport()" class="button button-warning">
                            导出
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <form method="post" action='<?php echo U("Assist/crashLog");?>' id="subfm">
            <input name="date" value="" type="hidden">
            <input type="hidden" name="export_name" value="1" />
        </form>
    </div>
    <div class="search-grid-container span25">
        <div id="grid"></div>
    </div>

</div>
<!-- 弹窗 -->
<div id="content" class="hide">

</div>

<script type="text/javascript">
    BUI.use('common/page');
</script>

<script type="text/javascript">
    BUI.use('common/search',function (Search) {
        var editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        }),
        columns = [
            {title:'时间',dataIndex:'time',width:150,elCls:'center'},
            {title:'IP',dataIndex:'ip',width:120,elCls:'center'},
            {title:'机型',dataIndex:'device',width:150,elCls:'center'},
            {title:'GID',dataIndex:'gid',width:50,elCls:'center'},
            {title:'渠道号',dataIndex:'agent',width:100,elCls:'center'},
            {title:'版本',dataIndex:'ver',width:50,elCls:'center'},
            {title:'系统',dataIndex:'type',width:50,elCls:'center'},
            {title:'崩溃',dataIndex:'log',width:700,elCls:'center',
                renderer: function (value, obj) {
                    return "<div onclick='showLog(this)' style='width: 700px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>"+value+"</div>";
                }
            }
        ],
        store = Search.createStore('<?php echo U("Assist/crashLog");?>',{
            proxy : {
                save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method : 'POST'
            },
            autoSync : true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns,{
            plugins : [] // 插件形式引入多选表格
        }),
        search = new Search({
            store : store,
            gridCfg : gridCfg
        }),
        grid = search.get('grid');
    });

    function showLog(obj) {
        var value = $(obj).html().replace(/\n/g, "<br />");
        BUI.use('bui/overlay',function(Overlay){
            var dialog = new Overlay.Dialog({
                title: '崩溃日志',
                mask: false,
                buttons: [],
                bodyContent: '<div style="max-height: 500px; width: 1000px; overflow-y: auto;">'+value+'</div>'
            });
            dialog.show();
        });
    }

    //导出
    function doExport() {
        $("#subfm input[name=date]").val($("#startDate").val());
        $("#subfm").submit();
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