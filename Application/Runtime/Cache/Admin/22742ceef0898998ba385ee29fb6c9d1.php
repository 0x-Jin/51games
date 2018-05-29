<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            付费衰减统计
        </title>
        <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
        <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
        <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
        <script src="/static/admin/js/bootstrap/jquery.min.js">
        </script>
        <script src="/static/admin/js/bootstrap/bootstrap.min.js">
        </script>
        <script src="/static/admin/js/bootstrap/bootstrap-select.js">
        </script>
        <script src="/static/admin/js/jquery.combo.select.js" type="text/javascript">
        </script>
        <script src="/static/admin/js/bui.js" type="text/javascript">
        </script>
        <script src="/static/admin/js/config.js" type="text/javascript">
        </script>
        <script src="/static/admin/js/echart/echarts.min.js" type="text/javascript">
        </script>
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
                <form class="form-horizontal span48" id="searchForm" method="post">
                    <div class="row">
                        <div class="control-group span6">
                            <label class="control-label" style="width: 60px;">
                                游戏名称：
                            </label>
                            <div class="controls">
                                <select id="game_id" name="game_id">
                                </select>
                            </div>
                        </div>

                        <div class="control-group span7">
                            <label class="control-label" style="width: 80px;">渠道商：</label>
                            <div class="controls">
                                <select name="advteruser_id" id="advteruser_id"></select>
                            </div>
                        </div>

<!--                         <div class="control-group span8">
                            <label class="control-label" style="width: 60px;">
                                渠道号：
                            </label>
                            <div class="controls" id="agent_contain">
                                <select class="selectpicker" multiple data-live-search="true" data-actions-box="true" id="agent_id" name="agent[]">
                                </select>
                            </div>
                        </div> -->
                        <div class="control-group span8">
                            <label class="control-label" style="width: 60px;">母包：</label>
                            <div class="controls" id="p_agent_contain">
                                <select id="agent_p" name="agent_p[]" onchange="getAgentByGame()" class="selectpicker" multiple data-live-search="true" data-actions-box="true">

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
                            <label class="control-label" style="width: 60px;">
                                统计日期：
                            </label>
                            <div class="controls">
                                <input class="calendar" id="startDate" name="startDate" type="text" value="<?php echo date('Y-m-d',strtotime(date('Y-m-d').' -7 day'));?>">
                                    <span>
                                        -
                                    </span>
                                    <input class="calendar" id="endDate" name="endDate" type="text" value="<?php echo date('Y-m-d');?>">
                                    </input>
                                </input>
                            </div>
                        </div>
                        <div class="control-group span5">
                            <label class="control-label" style="width: 60px;">
                                查看方式：
                            </label>
                            <div class="controls">
                                <select class="input-small" id="lookType" name="lookType">
                                    <option value="1">
                                        汇总
                                    </option>
                                    <!-- <option value="2">
                                        明细
                                    </option> -->
                                </select>
                            </div>
                        </div>
                        <div class="control-group span8">
                            <div class="controls">
                                <button class="button button-warning" id="btnSearch" type="button">
                                    搜索
                                </button>
                            </div>
                            <div class="controls">
                                <button class="button button-primary" id="echart" type="button">
                                    图表
                                </button>
                            </div>
                            <div class="controls">
                                <button class="button button-info" id="export" type="button">
                                    导出
                                </button>
                            </div>
                            
                        </div>
                    </div>
                </form>
                <form action='<?php echo U("Data/payRemain");?>' id="subfm" method="post">
                    <input name="game_id" type="hidden" value="">
                        <input name="agent" type="hidden" value="">
                            <input name="agentName" type="hidden" value="">
                                    <input name="startDate" type="hidden" value="">
                                        <input name="endDate" type="hidden" value="">
                                            <input name="agent_p" type="hidden" value="">
                                                <input name="lookType" type="hidden"/>
                                                <input name="export" type="hidden" value="1"/>
                                            </input>
                                        </input>
                                    </input>
                                </input>
                            </input>
                        </input>
                    </input>
                </form>
            </div>
            <div class="search-grid-container span25">
                <div id="grid">
                </div>
            </div>
            <!-- 图表 -->
            <div class="search-grid-container" id="chartMain" style="width: 100%;height:700px;display:none;">
                <div id="chart" style="width: 100%;height:100%;">
                </div>
            </div>
        </div>
        <!-- 弹窗 -->
        <div class="hide" id="content">
        </div>
<script type="text/javascript">
    BUI.use('common/page');
</script>

<script type="text/javascript">
    function getChart(){
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
        $.post("<?php echo U('Data/payRemain');?>",_data+'&chart=1',function(ret){
            
            var _day     = ret.info.day;
            var _day1    = ret.info.remain.day1;
            var _day3    = ret.info.remain.day3;
            var _day7    = ret.info.remain.day7;
            var _day15    = ret.info.remain.day15;
            var _day30   = ret.info.remain.day30;
            // 基于准备好的dom，初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart'));

            // 指定图表的配置项和数据
            // var colors = ['#5793f3', '#d14a61', '#675bba'];

            var option = {
                title: {
                    text: '流水增降幅度图'
                },
                tooltip : {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross',
                        label: {
                            backgroundColor: '#6a7985'
                        }
                    }
                },
                legend: {
                    data:['三十日流水增降幅度','十五日流水增降幅度','七日流水增降幅度','三日流水增降幅度','次日付流水增降幅度',]
                },
                toolbox: {
                    feature: {
                        saveAsImage: {}
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : _day
                    }
                ],
                yAxis : [
                    {
                        type : 'value',
                        axisLabel: {  
                          show: true,  
                          formatter: '{value} %'  
                        },
                    }
                ],
                series : [
                    {
                        name:'三十日流水增降幅度',
                        type:'line',
                        stack: '总量',
                        areaStyle: {normal: {}},
                        data:_day30
                    },
                    {
                        name:'十五日流水增降幅度',
                        type:'line',
                        stack: '总量',
                        areaStyle: {normal: {}},
                        data:_day15
                    },
                    {
                        name:'七日流水增降幅度',
                        type:'line',
                        stack: '总量',
                        areaStyle: {normal: {}},
                        data:_day7
                    },
                    {
                        name:'三日流水增降幅度',
                        type:'line',
                        stack: '总量',
                        areaStyle: {normal: {}},
                        data:_day3
                    },
                    {
                        name:'次日流水增降幅度',
                        type:'line',
                        stack: '总量',
                        label: {
                            normal: {
                                show: true,
                                position: 'top'
                            }
                        },
                        areaStyle: {normal: {}},
                        data:_day1
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            $('#grid').hide();
            
            $('.mark').hide();
            $('.spinner').hide();
            $('#pageCount').attr('page',ret.info.pageCount);
            $('#chartMain').show();

        });
    }

    $(function () {
        $('#btnSearch').click(function(){
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
            $("#subfm input[name=startDate]").val($("#startDate").val());
            $("#subfm input[name=endDate]").val($("#endDate").val());
            $('#subfm').submit();
        })
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
            getPAgentByGame(game_id,advteruser_id);
        });
        
    });

    //获取母包渠道号
    function getPAgentByGame(game_id,advteruser_id){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgent');?>",{game_id:game_id,advteruser_id:advteruser_id},function(ret){
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


    BUI.use('common/search',function (Search) {
    
    editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
    }),
    columns = [
        {title:'注册日期',dataIndex:'dayTime',width:100,elCls:'center'},
        {title:'游戏名称',dataIndex:'gameName',width:100,elCls:'center'},
        {title:'注册用户数',dataIndex:'newUser',width:100,elCls:'center',summary: true},
        {title:'首日付费金额',dataIndex:'newPay',width:100,elCls:'center',summary: true},
        {title:'次日流水增降幅度',dataIndex:'day1',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return '<span style="color:red;">'+value+'</span>';
        }},
        {title:'三日流水增降幅度',dataIndex:'day3',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return '<span style="color:red;">'+value+'</span>';
        }},
        {title:'七日流水增降幅度',dataIndex:'day7',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return '<span style="color:red;">'+value+'</span>';
        }},
        {title:'十五流水增降幅度',dataIndex:'day15',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return '<span style="color:red;">'+value+'</span>';
        }},
        {title:'三十日付费留存',dataIndex:'day30',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return '<span style="color:red;">'+value+'</span>';
        }},
        {title:'六十日流水增降幅度',dataIndex:'day60',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
            return '<span style="color:red;">'+value+'</span>';
        }}
        
    ],
    store = Search.createStore('<?php echo U("Data/payRemain");?>', {
            proxy: {
                save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method: 'POST'
            },
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            bbar : {pagingBar : false},
            plugins: [editing, BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
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