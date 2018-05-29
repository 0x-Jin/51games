<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>区服列表</title>
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
</head>
<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <input type="hidden" name="table" value="<?php echo I('table');?>" />
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">游戏名称：</label>
                    <div class="controls">
                        <select name="game_id" id="game_id"></select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label">母包编号：</label>
                    <div class="controls">
                        <select name="agent" id="agent"></select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label">区服名称：</label>
                    <div class="controls">
                        <input type="text" name="serverName" class="control-text" />
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
    <div class="search-grid-container">
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

    $(function(){
        //数据加载
        gameList();
        getAgent(0);

        $("#game_id").change(function(){
            getAgent($(this).val());
        });
    });

    //获取游戏
    function gameList(){
        var _html = "";
        $.post("<?php echo U('Ajax/getGameList');?>", "", function(ret){
            var ret = eval("("+ret+")");
            _html = "<option value=''>全部</option>"
            $(ret).each(function(i, v){
                _html += "<option value="+v.id+">"+v.gameName+"</option>";
            });
            $("#game_id").html(_html).comboSelect();
//            getAgent($("#game_id").val());
        });
    }

    //获取渠道号
    function getAgent(game_id){
        var _html = "";
        $.post("<?php echo U('Ajax/getAgent');?>", {game_id:game_id}, function(ret){
            var ret = eval("("+ret+")");
            if(ret.length < 1) return false;
            _html = "<option value=''>全部</option>";
            $(ret).each(function(i, v){
                _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
            });
            $("#agent").html(_html).comboSelect();
        });
    }

    BUI.use('common/search',function (Search) {

        var editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        }),
        columns = [
            {title:'ID',dataIndex:'id',width:60,elCls:'center'},
            {title:'游戏母包',dataIndex:'agentName',width:300,elCls:'center'},
            {title:'游戏分类',dataIndex:'gameName',width:200,elCls:'center'},
            {title:'区服ID',dataIndex:'serverId',width:100,elCls:'center'},
            {title:'区服名称',dataIndex:'serverName',width:200,elCls:'center'},
            {title:'手机系统',dataIndex:'serverType',width:80,elCls:'center',renderer:function (value, obj) {
                if(value == 0){
                    //正常
                    return "-";
                }else if(value == 1){
                    //禁止
                    return "IOS";
                }else if(value == 2){
                    //禁止
                    return "安卓";
                }
            }},
            {title:'开服时间',dataIndex:'openTime',width:150,elCls:'center'},
            {title:'导入者',dataIndex:'real',width:100,elCls:'center'}
//            {title:'操作',dataIndex:'opt',width:80,elCls:'center'}
        ],
        store = Search.createStore('<?php echo U("Game/serverList");?>',{
            proxy : {
                save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method : 'POST'
            },
            autoSync : true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns,{
            tbar : {
                items : [
                    {text : '<i class="icon-plus"></i>新增区服',btnCls : 'button button-small opt-btn',handler:addFunction},
                    {text : '<i class="icon-plus"></i>批量导入',btnCls : 'button button-small opt-btn',handler:importFunction},
                    <?php if(in_array(session('admin.role_id'),array(1)) or (session('admin.uid')==62)): ?>{text : '<i class="icon-plus"></i>快速添加',btnCls : 'button button-small opt-btn',handler:quickFunction}<?php endif; ?>
                    
                ]
            },
            plugins : [editing,BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
        });

        var search = new Search({
            store : store,
            gridCfg : gridCfg,
            height : 520
        }),
        grid = search.get('grid');
    });

    function addFunction(){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        $.get('<?php echo U("Game/serverAdd");?>','',function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    function quickFunction(){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        $.get('<?php echo U("Game/quickAdd");?>','',function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    function importFunction() {
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        $.get('<?php echo U("Game/serverImport");?>','',function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
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