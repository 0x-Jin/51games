<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>融合运营数据概况</title>
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap.min.css">
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap-select.css">
    
    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>

    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
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
        <form id="searchForm" method="post" class="form-horizontal span48">
            <div class="row">
                <input type="hidden" name="lookType" id="lookType" value="2" />
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">游戏名称：</label>
                    <div class="controls">
                        <select name="game_id" id="game_id"></select>
                    </div>
                </div>

                <?php if(session('admin.role_id') != 3): ?><div class="control-group span8">
                    <label class="control-label" style="width: 60px;">母包：</label>
                    <div class="controls" id="p_agent_contain">
                        <select id="agent_p" name="agent_p[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">

                        </select>
                    </div>
                </div><?php endif; ?>


                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">区服：</label>
                    <div class="controls">
                        <select name="serverId" id="serverId" class="selectpicker" data-live-search="true" data-actions-box="true"></select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">注册日期：</label>
                    <div class="controls">
                        <input type="text" class="calendar" name="startDate" id="startDate" value="<?php echo date('Y-m-d');?>"><span> - </span><input type="text" class="calendar" name="endDate" id="endDate" value="<?php echo date('Y-m-d');?>">
                    </div>
                </div>

                <div class="control-group span5">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                    </div>

                    <div class="controls">
                        <button  type="button" id="export" class="button button-info">导出</button>
                    </div>
                </div>

            </div>
        </form>
        <form  method="post" action='<?php echo U("Data/userRegRemain");?>?table=sp_user_game_day' id="subfm"><input name="game_id" value="" type="hidden"><input name="agent_p" value="" type="hidden"><input name="agent" value="" type="hidden"><input name="serverId" value="" type="hidden"><input name="startDate" value="" type="hidden"><input name="endDate" value="" type="hidden"><input type="hidden" name="lookType" /><input type="hidden" name="export" value=1 /></form>
    </div>

    <div style="color:red;margin-left:10px;">PS：注册、充值数据每十五分钟更新一次，留存数据一天更新一次</div>
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
    $(function () {
        $('.selectpicker').selectpicker({
                selectAllText: '全选',
                deselectAllText: '不选',
                liveSearchPlaceholder: '搜索关键字',
                noneSelectedText: '',
                multipleSeparator: ',',
                liveSearch: true,
                actionsBox: true
            });
        $('#export').click(function(){
            $("#subfm input[name=game_id]").val($("#game_id").val());
            $("#subfm input[name=lookType]").val($("#lookType").val());
            $("#subfm input[name=agent]").val($('#agent').val());
            $("#subfm input[name=agent_p]").val($('#agent_p').val());
            $("#subfm input[name=agentName]").val($("#agentName").val());
            $("#subfm input[name=serverId]").val($("#serverId").val());
            $("#subfm input[name=startDate]").val($("#startDate").val());
            $("#subfm input[name=endDate]").val($("#endDate").val());
            $('#subfm').submit();
        })
        gameLists();
        $('#game_id').change(function() {
            var game_id = $(this).val();
            getPAgentByGame(game_id);
            serverList();
        });

        $('#agent_p').change(function() {
            serverList();
        });
        
    });

    //获取母包渠道号
    function getPAgentByGame(game_id){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgent');?>",{game_id:game_id,channel:'all'},function(ret){
            _html += "<option>--请选择母包--</option>";
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
            });
            $('#agent_p').html(_html);
            $('#agent_p').selectpicker('refresh');
            $('#agent_p').selectpicker('val', '--请选择母包--');
        });
    }


    //获取游戏
    function gameLists(){
        var _html = '';
        $.post("<?php echo U('Ajax/getGameList');?>",{all:1},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.gameName+"</option>";
            });
            $('#game_id').html(_html);
            $('#game_id').comboSelect();
        });
    }

    /**
     * 获取区服列表
     * @return {[type]} [description]
     */
     function serverList(){
      var _html = '';
      var game_id = $("#game_id").val();
      var agent = $("#agent_p").val();
      $.post("<?php echo U('Ajax/getServerList');?>",{game_id:game_id,agent:agent},function(ret){
          _html += "<option value='0'>--全部--</option>";
          var ret = eval('('+ret+')');
          $(ret).each(function(i,v){
              _html += "<option value="+v.serverId+">"+v.serverName+'['+v.serverId+']'+"</option>";
          });
          $('#serverId').html(_html);
          $('#serverId').selectpicker('refresh');
      })
     }

    BUI.use('common/search',function (Search) {
    colGroup = new BUI.Grid.Plugins.ColumnGroup({
        groups: [{
            title: '新增玩家',
            from: 2,
            to: 5
        }, {
            title: '活跃玩家',
            from: 6,
            to: 7
        }, {
            title: '充值概况',
            from: 8,
            to: 12
        }, {
            title: '新增充值',
            from: 13,
            to: 17
        }]
    }),
    Summary = new BUI.Grid.Plugins.Summary(),
    editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
    }),
    columns = [
        {title:'注册日期',dataIndex:'dayTime',width:100,elCls:'center'},
        {title:'包名称',dataIndex:'gameName',width:200,elCls:'center'},
        {title:'新增设备数',dataIndex:'newDevice',width:100,elCls:'center',summary: true},
        {title:'唯一注册数',dataIndex:'disUdid',width:100,elCls:'center',summary: true},
        {title:'新增用户数',dataIndex:'newUser',width:100,elCls:'center',summary: true},
        {title:'用户转化率',dataIndex:'userRate',width:100,elCls:'center',summary: true},
        {title:'总日活跃数',dataIndex:'allUserLogin',width:100,elCls:'center',summary: true},
        {title:'老用户日活跃数',dataIndex:'oldUserLogin',width:100,elCls:'center',summary: true},
        {title:'充值总额',dataIndex:'allPay',width:100,elCls:'center',summary: true},
        {title:'充值总账号数',dataIndex:'allPayUser',width:100,elCls:'center',summary: true},
        {title:'付费率',dataIndex:'payRate',width:100,elCls:'center',summary: true},
        {title:'ARPU',dataIndex:'ARPU',width:100,elCls:'center',summary: true},
        {title:'ARPPU',dataIndex:'ARPPU',width:100,elCls:'center',summary: true},
        {title:'新用户充值总额',dataIndex:'newPay',width:100,elCls:'center',summary: true},
        {title:'新用户充值账号数',dataIndex:'newPayUser',width:100,elCls:'center',summary: true},
        {title:'新增付费率',dataIndex:'newPayRate',width:100,elCls:'center',summary: true},
        {title:'新增ARPU',dataIndex:'newARPU',width:100,elCls:'center',summary: true},
        {title:'新增ARPPU',dataIndex:'newARPPU',width:100,elCls:'center',summary: true},
        {title:'次日留存',dataIndex:'day1',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            if(value == '0.00%'){
                return value;
            }else{
                return '<span style="color:red;">'+value+'</span>';
            }
        }},
        {title:'七日留存',dataIndex:'day6',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            if(value == '0.00%'){
                return value;
            }else{
                return '<span style="color:red;">'+value+'</span>';
            }
        }},
        {title:'十四日留存',dataIndex:'day13',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            if(value == '0.00%'){
                return value;
            }else{
                return '<span style="color:red;">'+value+'</span>';
            }
        }},
        {title:'三十日留存',dataIndex:'day29',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            if(value == '0.00%'){
                return value;
            }else{
                return '<span style="color:red;">'+value+'</span>';
            }
        }}
        
    ],
    store = Search.createStore('<?php echo U("Data/userRegRemain");?>?table=sp_user_game_day', {
            proxy: {
                save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method: 'POST'
            },
            pageSize:1000,
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            forceFit : true,
            plugins: [editing, colGroup, Summary, BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
        });

    var search = new Search({
            store: store,
            gridCfg: gridCfg
        }),
        grid = search.get('grid');
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