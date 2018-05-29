<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <title>激活走势图</title>
    <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
    <link href="/static/admin/css/jquery.jqplot.min.css" rel="stylesheet"/>
    <script type="text/javascript" src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jqplot/jquery.jqplot.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/jqplot/jqplot.cursor.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/jqplot/jqplot.canvasAxisLabelRenderer.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/jqplot/jqplot.categoryAxisRenderer.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/jqplot/jqplot.highlighter.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/jqplot/jqplot.pointLabels.min.js"></script>
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

                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">类型：</label>
                    <div class="controls" id="type_contain">
                        <select id="type" name="type">
                            <option value="1">新增设备</option>
                            <option value="2">唯一注册</option>
                            <option value="3">充值</option>
                        </select>
                    </div>
                </div>
                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">
                        统计日期：
                    </label>
                    <div class="controls">
                        <input type="text" class="calendar" name="startDate" id="startDate" value="<?php echo date('Y-m-d', strtotime('-7 day'));?>" /><span> - </span><input type="text" class="calendar" name="endDate" id="endDate" value="<?php echo date('Y-m-d');?>" />
                    </div>
                </div>
                <div class="control-group span8">
                    <div class="controls">
                        <button class="button button-info" onclick="doSearch()" type="button">搜索</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- 图表 -->
    <div class="search-grid-container">
        <div id="chart" style="width: 100%;height:550px;">
        </div>
    </div>
</div>

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
        gameLists();
        $('#game_id').change(function() {
            var game_id = $(this).val();
            getPAgentByGame(game_id);
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

    BUI.use(['bui/calendar'], function(Calendar) {
        var datepicker1 = new Calendar.DatePicker({
            trigger: '#startDate',
            dateMask: 'yyyy-mm-dd',
            autoRender: true
        });

        var datepicker2 = new Calendar.DatePicker({
            trigger: '#endDate',
            dateMask: 'yyyy-mm-dd',
            autoRender: true
        });
    });

    //计算两个日期天数差的函数，通用
    function DateDiff(sDate1, sDate2) {  //sDate1和sDate2是yyyy-MM-dd格式
        var time1 = Date.parse(new Date(sDate1));
        var time2 = Date.parse(new Date(sDate2));
        return Math.abs(parseInt((time2 - time1)/1000/3600/24));
    }

    function doSearch(){
        var _startDate  = $("#startDate").val();
        var _endDate    = $("#endDate").val();
        var type = $('#type').val();
        if (_endDate < _startDate) {
            alert("请选择正确的时间区域！");
            return;
        }
//        if (DateDiff(_startDate, _endDate) > 30) {
//            alert("时间区域请不要超过30天！");
//            return;
//        }
        if(type=='2'){
            var texts = '唯一注册图表';
            var labels = '唯一注册数';
        }else if(type == '3'){
            var texts = '充值图表';
            var labels = '充值人数';
        }else{
            var texts = '新增设备图表';
            var labels = '新增设备数';
        }

        $.ajax({
            type:"post",
            dataType:"json",
            data:$('#searchForm').serialize(),
            url:"<?php echo U('advterData/activateDiagram');?>",
            success:function(data){
                $("#chart").html('');
                if(!data || !data.length){
                    $("#chart").html("<b style='color: red;'>暂无数据！</b>");
                    return false;
                }

                var _data = [[]];

                for(var i in data){
                    var tda = [], tda1 = [];
                    tda[0] = tda1[0] = data[i]['day'];
                    tda[1] = parseInt(data[i]['totalData']);

                    _data[0][i] = tda;
                }

                if(_data){
                    buildchart(_data,texts,labels);
                }
            }
        });

    }

    function buildchart(_data,texts,labels){
        var plot1 = $.jqplot('chart', _data, {
            title:{
                text:texts,
                show:true,
                fontSize:'20px'
            },
            axesDefaults: {
                tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
                tickOptions: {
                    angle: -30,
                    fontSize: '10pt'
                }
            },
            axes:{
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    label: "日期"
                },
                yaxis:{
                    min:0,
                    tickOptions:{
                        //formatString:'%.2f'
                    }
                }
            },
            series:[{label:labels}],
            legend: {
                show: true,
                placement: 'outsideGrid',
                location:'ne'
            },
            seriesDefaults: {
                showMarker: true,
                pointLabels: {
                    show:true
                }
            },
            highlighter: {
                show: true,
                useAxesFormatters: false,
                tooltipFormatString:'<b><span style="color:#000;font-size:12px;">%.2f</span></i></b>',
                sizeAdjust: 7.5,
                tooltipAxes: 'x',
                tooltipContentEditor:function (str, seriesIndex, pointIndex, plot) {
                    // display series_label, x-axis_tick, y-axis value
                    return '<span style="text-align:left;font-size:12px">&nbsp;&nbsp;'+
                        plot.data[0][pointIndex][0]+"&nbsp;&nbsp;<br/>&nbsp;&nbsp;<span style='color:"+plot.seriesColors[0]+"'>"+plot.series[0]["label"] +
                        "</span>：<b>" + plot.data[0][pointIndex][1]+"</b></span>";
                }
            },
            cursor: {
                show: false
            }

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