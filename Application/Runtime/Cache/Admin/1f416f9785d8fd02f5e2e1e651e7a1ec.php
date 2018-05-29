<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <title>
        注册实时统计图表
    </title>
    <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script src="/static/admin/js/jquery.combo.select.js" type="text/javascript"></script>
    <script src="/static/admin/js/bui.js" type="text/javascript"></script>
    <script src="/static/admin/js/config.js" type="text/javascript"></script>
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
        <form class="form-horizontal span48" id="searchForm" method="post">
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

                <div class="control-group span7">
                    <label class="control-label" style="width: 100px;">渠道包创建人：</label>
                    <div class="controls">
                        <select name="creater" id="creater"></select>
                    </div>
                </div>
                
                <div class="control-group span9">
                    <label class="control-label" style="width: 80px;">渠道号：</label>
                    <div class="controls" id="agent_contain">
                        <select id="agent_id" name="agent[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">
                            
                        </select>
                    </div>
                </div>
                
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">
                        类型：
                    </label>
                    <div class="controls">
                        <select name="gameType">
                            <option value="0" selected="selected">全部</option>
                            <option value="1">ANDROID</option>
                            <option value="2">IOS</option>
                        </select>
                    </div>
                </div>

                <?php if(session('admin.partment') == 0): ?><div class="control-group span6">
                        <label class="control-label" style="width: 60px;">
                            部门：
                        </label>
                        <div class="controls">
                            <select name="department">
                                <option value="0" selected="selected">全部</option>
                                <?php echo ($tplPartment); ?>
                            </select>
                        </div>
                    </div><?php endif; ?>

                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">
                        展示：
                    </label>
                    <div class="controls">
                        <select name="showType" id="showType">
                            <option value="1" selected="selected">登录账号</option>
                            <option value="2">登陆设备</option>
                            <option value="3">激活设备</option>
                            <option value="4">注册设备</option>
                            <option value="5">创角设备</option>
                            <option value="6">充值账号</option>
                            <option value="7">充值金额</option>
                            <option value="8">充值次数</option>
                            <option value="9">落地页打开</option>
                            <option value="10">落地页下载</option>
                        </select>
                    </div>
                </div>
                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">
                        查询日期：
                    </label>
                    <div class="controls">
                        <input class="calendar" id="date" name="date" type="text" value="<?php echo date('Y-m-d');?>" >
                    </div>
                </div>
                <div class="control-group span8">
                    <div class="controls">
                        <button class="button button-primary" onclick="getChart()" type="button">
                            搜索
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- 图表 -->
    <div class="search-grid-container" id="chartMain" style="width: 100%; height: 600px; display:none;">
        <div id="chart" style="width: 100%;height:100%;"></div>
    </div>

    <div class="search-grid-container span25">
        <div id="grid"></div>
    </div>
</div>
<!-- 弹窗 -->
<div class="hide" id="content">
</div>
<script type="text/javascript">
    function getChart(){
        var width = $('#chartMain').width();
        var height = $('#chartMain').height();
        $('#chart').css({width:width, height:height});

        var _chart = [
            {title:"", name:""},
            {title:"登陆账号实时图表", name:"登陆账号数："},
            {title:"登陆设备实时图表", name:"登陆设备数："},
            {title:"激活设备实时图表", name:"激活设备数："},
            {title:"注册设备实时图表", name:"注册设备数："},
            {title:"创角设备实时图表", name:"创角设备数："},
            {title:"充值账号实时图表", name:"充值账号数："},
            {title:"充值金额实时图表", name:"充值金额数："},
            {title:"充值次数实时图表", name:"充值次数："},
            {title:"落地页打开实时图表", name:"落地页打开数："},
            {title:"落地页下载实时图表", name:"落地页下载数："}
        ];

        //提交表单获取数据
        var _data = $('#searchForm').serialize();
        $('.mark').show();
        $('.spinner').show();
        $.post("<?php echo U('Data/realtimeChart');?>", _data, function(ret){
            var res     = eval('('+ret+')');
            var type    = $("#showType").val();
            // 基于准备好的dom，初始化echarts实例
            var myChart = echarts.init(document.getElementById('chart'));

            // 指定图表的配置项和数据
            var timeData = [
                '0时', '1时', '2时', '3时', '4时', '5时', '6时', '7时',
                '8时', '9时', '10时', '11时', '12时', '13时', '14时', '15时',
                '16时', '17时', '18时', '19时', '20时', '21时', '22时', '23时'
            ];

            option = {
                title: {
                    text: _chart[type].title,
                    x: 'left'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        animation: false
                    }
                },
                legend: {
                    data: ['时间'],
                    x: 'left'
                },
//                toolbox: {
//                    feature: {
//                        dataZoom: {
//                            yAxisIndex: 'none'
//                        },
//                        restore: {},
//                        saveAsImage: {}
//                    }
//                },
                axisPointer: {
                    link: {
                        xAxisIndex: 'all'
                    }
                },
//                dataZoom: [{
//                    show: true,
//                    realtime: true,
//                    start: 0,
//                    end: 100,
//                    xAxisIndex: [0, 1]
//                }, {
//                    type: 'inside',
//                    realtime: true,
//                    start: 30,
//                    end: 70,
//                    xAxisIndex: [0, 1]
//                }],
                grid: [{
                    left: 40,
                    right: 40
                }, {
                    left: 40,
                    right: 40
                }],
                xAxis: [{
                    type: 'category',
                    boundaryGap: false,
                    axisLine: {
                        onZero: true
                    },
                    data: timeData
                }, {
                    gridIndex: 1
                }],

                yAxis: [{
                    type: 'value',
                    name: _chart[type].name,
                    min: 0
                }, {
                    gridIndex: 1
                }],
                series: [{
                    name: '数值',
                    type: 'line',
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 9,
                    showSymbol: false,
                    lineStyle: {
                        normal: {
                            width: 1
                        }
                    },
                    markPoint: {
                        data: [{
                            type: 'max',
                            name: '最大值'
                        }, {
                            type: 'min',
                            name: '最小值'
                        }]
                    },
                    markArea: {
                        silent: true,
                        label: {
                            normal: {
                                position: ['10%', '50%']
                            }
                        },
                        data: []
                    },
                    data: res.info
                }]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);


            BUI.use('bui/grid', function(Grid){
                var Grid = Grid,
                    columns = [
                        {title: '包号', dataIndex: 'agent', width: 80, elCls: 'center'},
                        {title: '游戏', dataIndex: 'agentName', width: 150, elCls: 'center'},
                        {title: '统计', dataIndex: 'count', width: 45, elCls: 'center'},
                        {title: '0时', dataIndex: '00', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '1时', dataIndex: '01', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '2时', dataIndex: '02', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '3时', dataIndex: '03', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '4时', dataIndex: '04', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '5时', dataIndex: '05', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '6时', dataIndex: '06', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '7时', dataIndex: '07', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '8时', dataIndex: '08', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '9时', dataIndex: '09', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '10时', dataIndex: '10', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '11时', dataIndex: '11', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '12时', dataIndex: '12', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '13时', dataIndex: '13', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '14时', dataIndex: '14', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '15时', dataIndex: '15', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '16时', dataIndex: '16', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '17时', dataIndex: '17', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '18时', dataIndex: '18', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '19时', dataIndex: '19', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '20时', dataIndex: '20', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '21时', dataIndex: '21', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '22时', dataIndex: '22', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }},
                        {title: '23时', dataIndex: '23', width: 45, elCls: 'center', renderer: function (value) {
                            if (value) {
                                return value;
                            } else {
                                return 0;
                            }
                        }}
                    ];


                var grid = new Grid.SimpleGrid({
                    render:'#grid',
                    columns : columns,
                    items : res.list,
                    idField : 'a'
                });
                $(".bui-simple-grid").remove();

                grid.render();
            });
            
            $('.mark').hide();
            $('.spinner').hide();

            $('#chartMain').show();
        });
    }
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

        gameLists();
        advteruser_id();
        getAgentCreater();

        $('#game_id').change(function() {
            var game_id       = $(this).val();
            var advteruser_id = $('#advteruser_id').val();
            var creater = $('#creater').val();
            getPAgentByGame(game_id,advteruser_id,creater);
            // getAgentByGame(game_id,advteruser_id,creater);
        });

        $('#advteruser_id').change(function() {
            var advteruser_id = $(this).val();
            var game_id = $('#game_id').val();
            var creater = $('#creater').val();
            getAgentByGame(game_id,advteruser_id,creater);
        });

        $('#creater').change(function() {
            var creater = $(this).val();
            var game_id = $('#game_id').val();
            var advteruser_id = $('#advteruser_id').val();
            getAgentByGame(game_id,advteruser_id,creater);
        });

        $('#agent_p').change(function() {
            var creater = $('#creater').val();
            var game_id = $('#game_id').val();
            var advteruser_id = $('#advteruser_id').val();
            getAgentByGame(game_id,advteruser_id,creater);
        });
    });

    BUI.use('bui/calendar',function(Calendar){
        var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
            autoRender : true
        });
    });

    //获取母包渠道号
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

    //获取包创建人
    function getAgentCreater(){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgentCreater');?>",{all:1},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                if(v.creater == '--全部--'){
                    _html += "<option value=0>"+v.creater+"</option>";
                }else{
                    _html += "<option value="+v.creater+">"+v.creater+"</option>";
                }
            });
            $('#creater').html(_html);
            $('#creater').comboSelect();
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