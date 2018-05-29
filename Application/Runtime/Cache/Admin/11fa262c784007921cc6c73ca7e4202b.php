<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>注册地区分布</title>
    <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet" />
    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script src="/static/admin/js/echart/echarts.min.js" type="text/javascript"></script>
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

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">游戏名称：</label>
                    <div class="controls">
                        <select name="game_id" id="game_id"></select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">母包：</label>
                    <div class="controls" id="p_agent_contain">
                        <select id="agent_p" name="agent_p[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">

                        </select>
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">渠道商：</label>
                    <div class="controls">
                        <select name="advteruser_id" id="advteruser_id"></select>
                    </div>
                </div>

                <div class="control-group span9">
                    <label class="control-label" style="width: 80px;">渠道号：</label>
                    <div class="controls" id="agent_contain">
                        <select id="agent_id" name="agent[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">
                            
                        </select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">注册日期：</label>
                    <div class="controls">
                        <input type="text" class="calendar" name="startDate" id="startDate" value="<?php echo date('Y-m-d',strtotime(date('Y-m-d').' -7 day'));?>"><span> - </span><input type="text" class="calendar" name="endDate" id="endDate" value="<?php echo date('Y-m-d');?>">
                    </div>
                </div>

                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">查看方式：</label>
                    <div class="controls">
                        <select name="lookType" id="lookType" class="input-small">
                            <option value="1">汇总</option>
                            <option value="2">明细</option>
                        </select>
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
        <form  method="post" action='<?php echo U("AdvterData/areaRegister");?>' id="subfm">
            <input name="game_id" value="" type="hidden" />
            <input name="agent_p" value="" type="hidden" />
            <input name="agent" value="" type="hidden" />
            <input name="advteruser_id" value="" type="hidden" />
            <input name="startDate" value="" type="hidden" />
            <input name="endDate" value="" type="hidden" />
            <input type="hidden" name="lookType" />
            <input type="hidden" name="export" value=1 />
        </form>
    </div>

    <div style="color:red;margin-left:10px;">PS：注册地区分布数据15分钟更新一次</div>
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
        BUI.use(['bui/calendar'],function (Calendar) {
            var datepicker1 = new Calendar.DatePicker({
                trigger: '#startDate',
                autoRender: true
            });

            var datepicker2 = new Calendar.DatePicker({
                trigger: '#endDate',
                autoRender: true
            });
        });
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
            $("#subfm input[name=agent_p]").val($("#agent_p").val());
            $("#subfm input[name=advteruser_id]").val($("#advteruser_id").val());
            $("#subfm input[name=agent]").val($('#agent_id').val());
            $("#subfm input[name=lookType]").val($("#lookType").val());
            $("#subfm input[name=startDate]").val($("#startDate").val());
            $("#subfm input[name=endDate]").val($("#endDate").val());
            $('#subfm').submit();
        });
        gameLists();
        advteruser_id();

        $('#game_id').change(function() {
            var game_id       = $(this).val();
            var advteruser_id = $('#advteruser_id').val();
            getPAgentByGame(game_id,advteruser_id);
        });

        $('#advteruser_id').change(function() {
            var advteruser_id = $(this).val();
            var game_id = $('#game_id').val();
            getAgentByGame(game_id,advteruser_id);
        });

        $('#agent_p').change(function() {
            var game_id = $('#game_id').val();
            var advteruser_id = $('#advteruser_id').val();
            getAgentByGame(game_id,advteruser_id);
        });
    });

    function getPAgentByGame(game_id,advteruser_id,creater,gameType){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgent');?>",{game_id:game_id},function(ret){
            _html += "<option>--请选择母包--</option>";
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.agentAll+"</option>";
            });
            $('#agent_p').html(_html);
            $('#agent_p').selectpicker('refresh');
            $('#agent_p').selectpicker('val', '--请选择母包--');
        });
    }

    //获取渠道号
    function getAgentByGame(game_id,advteruser_id,creater,gameType){
        var agent = $('#agent_p').val();
        var _html = '';
        _data = {game_id:game_id,advteruser_id:advteruser_id,creater:creater,agent:agent}
        $.post("<?php echo U('Ajax/getAgentByGame');?>",_data,function(ret){
            _html += "<option>--请选择子包--</option>";
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.agents+">"+v.agent+"</option>";
            });
            $('#agent_id').html(_html);
            $('#agent_id').selectpicker('refresh');
            $('#agent_id').selectpicker('val', '--请选择子包--');
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

    //获取广告商
    function advteruser_id(){
        var _html = '';
        $.post("<?php echo U('Ajax/adv_company');?>",{all:1},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.company_name+"</option>";
            });
            $('#advteruser_id').html(_html);
            $('#advteruser_id').comboSelect();
        });
    }

    function getTreeData(_data){
        BUI.use(['bui/extensions/treegrid'],function (TreeGrid) {
            var data = _data;
            //由于这个树，不显示根节点，所以可以不指定根节点
            var tree = new TreeGrid({
                render : '#grid',
                nodes : data,
                columns : [
                    {title : '日期',dataIndex :'dayTime', width:300,elCls:'center'},
                    {title : '省份',dataIndex :'province', width:200,elCls:'center'},
                    {title : '城市',dataIndex :'city', width:240,elCls:'center'},
                    {title : '人数',dataIndex :'register', width:200,elCls:'center'},
                    {title : '占比',dataIndex :'Rate', width:200,elCls:'center'}
                ],
                height:520
            });
            tree.render();
            $('.mark').css('display','none');
            $('.spinner').css('display','none');
        });
    }

    $('#btnSearch').click(function(){
        var _data = $('#searchForm').serialize();
        $('.mark').show();
        $('.spinner').show();
        $.post('<?php echo U("AdvterData/areaRegister");?>',_data,function(ret){
            var ret = eval('('+ret+')');
            console.log(ret.rows.length);
            if(ret.rows.length > 0){
                $('#grid').html('');
                getTreeData(ret.rows);
            }else{
                $('.mark').hide();
                $('.spinner').hide();
                alert(ret.info);
                return false;
            }
        });
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