<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>渠道数据统计</title>
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap.min.css">
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>

    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap-select.css">
    
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

                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">游戏名称：</label>
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

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">子包：</label>
                    <div class="controls" id="agent_contain">
                        <select id="agent" name="agent[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">

                        </select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">区服：</label>
                    <div class="controls">
                        <select name="serverId" id="serverId" class="selectpicker" data-live-search="true" data-actions-box="true"></select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">统计日期：</label>
                    <div class="controls">
                        <input type="text" class="calendar" name="startDate" id="startDate" value="<?php echo date('Y-m-d');?>"><span> - </span><input type="text" class="calendar" name="endDate" id="endDate" value="<?php echo date('Y-m-d');?>">
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

                <div class="control-group span8">
                    <div class="controls">
                        <button class="button button-primary" id="btnSearch" type="button">
                            搜索
                        </button>
                    </div>
                    <div class="controls">
                        <button class="button button-info" id="export" type="button">
                            导出
                        </button>
                    </div>
                    <div class="controls">
                        <button class="button button-warning" id="echart" type="button">
                            图表
                        </button>
                    </div>
                    <div class="controls">
                        <button class="button button-success" id="etable" type="button">
                            表格
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <form  method="post" action='<?php echo U("Data/agentDataCount");?>' id="subfm"><input name="game_id" value="" type="hidden"><input name="agent_p" value="" type="hidden"><input name="agent" value="" type="hidden"><input name="serverId" value="" type="hidden"><input name="startDate" value="" type="hidden"><input name="endDate" value="" type="hidden"><input type="hidden" name="lookType" /><input type="hidden" name="export" value=1 /></form>
    </div>

    <div style="color:red;margin-left:10px;">
        PS：渠道数据每十五分钟更新一次
    </div>
    <div class="search-grid-container span25">
        <div id="grid"></div>
    </div>

    <!-- 图表 -->
    <div class="search-grid-container" id="chartMain" style="width: 100%;height:600px;display:none;">
        <div id="chart" style="width: 100%;height:100%;">
        </div>
        <div id="page">
            <ul class="cd-pagination custom-buttons">
                <li class="button prebutton">
                    <a href="javascript:;">
                        Prev
                    </a>
                </li>
                <li>
                    <span id="pageCount" page="0">
                    </span>
                </li>
                <li class="button nextbutton">
                    <a href="javascript:;">
                        Next
                    </a>
                </li>
            </ul>
        </div>
    </div>

</div>

<!-- 弹窗 -->
<div id="content" class="hide">

</div>

<script type="text/javascript">
    BUI.use('common/page');
</script>

<script type="text/javascript">
    var pageIndex = 1;
    $('.prebutton').click(function(event) {
       var page = $('#pageCount').attr('page');
       pageIndex -= 1;
       if(pageIndex < 1){
        pageIndex = 1;
        return false;
       }else{
        getChart();
       }
       $('#pageCount').html(pageIndex+'/'+page);
    });

    $('.nextbutton').click(function(event) {
       var page = $('#pageCount').attr('page');
       pageIndex += 1;
       if(pageIndex > page){
        pageIndex = page;
        return false;
       }else{
        getChart();
       }
       $('#pageCount').html(pageIndex+'/'+page);

    });

    function getChart(showPage){
        showPage == true ? pageIndex = 1 : '';
        var limit = 30; //每页显示30条
        var width = $('#chartMain').width();
        var height = $('#chartMain').height();
        $('#chart').css({width:width,height:height});

        var game_id = $('#game_id').val();
        var lookType = $('#lookType').val();
        if(game_id == 0 || lookType == 2){
            alert('图表必须选择游戏和汇总查看方式');
            return false;
        }
        $('.mark').show();
        $('.spinner').show();
        //提交表单获取数据
        var _data = $('#searchForm').serialize();
        var start = (pageIndex - 1) * limit;
        var limit = limit;
        $.post("<?php echo U('Data/agentPay');?>",_data+'&chart=1&start='+start+'&limit='+limit,function(ret){
            
            var _agentName = ret.info.agent;
            var _rate = ret.info.rate;
            var _data = ret.info.data;
            // 基于准备好的dom，初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart'));

            // 指定图表的配置项和数据
            var option = {
                    title: {
                        text: '渠道充值分布',
                        subtext: '数据来自创娱'
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: ['充值金额']
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'value',
                        boundaryGap: [0, 0.01]
                    },
                    yAxis: [{
                        
                        position:'left',
                        type: 'category',
                        data: _agentName
                    },
                    {
                        
                        position:'right',
                        type: 'category',
                        data: _rate
                    }
                    ],
                    series: [
                        {
                            name: '充值金额',
                            barWidth: '10%',
                            type: 'bar',
                            data: _data
                        }
                    ]
                };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            $('#grid').hide();
            
            showPage == true ? $('#pageCount').html('1/'+ret.info.pageCount) : '';
            $('.mark').hide();
            $('.spinner').hide();
            $('#pageCount').attr('page',ret.info.pageCount);
            $('#chartMain').show();

        });
    }
    $(function () {
        $('#etable').click(function(){
            $('#grid').show();
            $('#chartMain').hide();
        });

        $('#echart').click(function(){
            getChart(true);
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
            getAgentByGame();
            serverList();
        });
        
    });

    //获取母包渠道号
    function getPAgentByGame(game_id){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgent');?>",{game_id:game_id},function(ret){
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

    //获取子包渠道号
    function getAgentByGame() {
        var agent = $("#agent_p").val();
        if (agent != "--请选择渠道号--" && agent != null) {
            var _html = '';
            $.post("<?php echo U('Ajax/getChildAgentByAgent');?>", {agent:agent}, function(ret){
                _html += "<option>--请选择子包--</option>";
                var ret = eval('('+ret+')');
                $(ret).each(function(i,v){
                    _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
                });
                $('#agent').html(_html);
                $('#agent').selectpicker('refresh');
                $('#agent').selectpicker('val', '--请选择子包--');
            });
        }
    }


    //获取包名称
    /*function agentName(game_id){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgentName');?>",{all:1,game_id:game_id},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.agentName+"</option>";
            });
            $('#agentName').html(_html);
            $('#agentName').comboSelect();
        });
    }*/

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
    
    Summary = new BUI.Grid.Plugins.Summary(),
    editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
    }),
    columns = [
        {title:'统计日期',dataIndex:'dayTime',width:100,elCls:'center'},
        {title:'渠道号',dataIndex:'agent',width:100,elCls:'center'},
        // {title:'包名称',dataIndex:'agentName',width:150,elCls:'center'},
        // {title:'游戏名称',dataIndex:'gameName',width:100,elCls:'center'},
        {title:'激活设备数',dataIndex:'newDevice',width:100,elCls:'center',summary: true},
        {title:'唯一注册数',dataIndex:'disUdid',width:100,elCls:'center',summary: true},
        {title:'新增用户数',dataIndex:'newUser',width:100,elCls:'center',summary: true},
        {title:'用户转化率',dataIndex:'newUserRate',width:100,elCls:'center',summary: true},
        {title:'新增用户占比',dataIndex:'userRate',width:100,elCls:'center',summary: true},
        {title:'活跃用户数',dataIndex:'allUserLogin',width:100,elCls:'center',summary: true},
        {title:'老用户日活跃数',dataIndex:'oldUserLogin',width:100,elCls:'center',summary: true},
        {title:'活跃用户占比',dataIndex:'loginRate',width:100,elCls:'center',summary: true},
        {title:'充值金额',dataIndex:'allPay',width:100,elCls:'center',summary: true},
        {title:'充值占比',dataIndex:'chargeRate',width:100,elCls:'center',summary: true},
        {title:'充值帐户数',dataIndex:'allPayUser',width:100,elCls:'center',summary: true},
        {title:'付费率',dataIndex:'payRate',width:100,elCls:'center',summary: true},
        {title:'ARPU',dataIndex:'ARPU',width:100,elCls:'center',summary: true},
        {title:'ARPPU',dataIndex:'ARPPU',width:100,elCls:'center',summary: true},
        {title:'新用户充值总额',dataIndex:'newPay',width:100,elCls:'center',summary: true},
        {title:'新用户充值账号数',dataIndex:'newPayUser',width:100,elCls:'center',summary: true},
        {title:'新增付费率',dataIndex:'payRateNew',width:100,elCls:'center',summary: true},
        {title:'新增ARPU',dataIndex:'newARPU',width:100,elCls:'center',summary: true},
        {title:'新增ARPPU',dataIndex:'newARPPU',width:100,elCls:'center',summary: true},

        {title:'次留',dataIndex:'day1',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return value+'%';
        }},
        {title:'三留',dataIndex:'day2',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return value+'%';
        }},
        {title:'四留',dataIndex:'day3',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return value+'%';
        }},
        {title:'五留',dataIndex:'day4',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return value+'%';
        }},
        {title:'六留',dataIndex:'day5',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return value+'%';
        }},
        {title:'七留',dataIndex:'day6',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                return value+'%';
        }},
        {title:'十四留',dataIndex:'day13',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                return value+'%';
        }},
        {title:'三十留',dataIndex:'day29',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                return value+'%';
        }},
    ],
    store = Search.createStore('<?php echo U("Data/agentDataCount");?>', {
            proxy: {
                save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method: 'POST'
            },
            pageSize:200,
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            plugins: [editing, Summary, BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
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