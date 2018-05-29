<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
		<title>
		    回款周期&单价预估
		</title>
		<link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
		<link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
		<link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
		<link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css" />
		<link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet" />

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
	      th,td {
	      	text-align: center;
	      	width: 100px;
	      }
	    </style>
	

	<body>
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
			                    </select>
			                </div>
			            </div>
			            <div class="control-group span8">
			                <label class="control-label" style="width: 60px;">
			                    投放单价：
			                </label>
			                <div class="controls">
			                    <input class="control-text" id="putPrice" name="putPrice" style="width: 120px;" type="text">
			                    </input>
			                </div>
			            </div>
			            <div class="control-group span6">
			                <label class="control-label" style="width: 60px;">
			                    首月LTV：
			                </label>
			                <div class="controls">
			                    <input class="control-text" id="firstLtv" name="firstLtv" style="width: 100px;" type="text">
			                    </input>
			                </div>
			            </div>
			            <div class="control-group span6">
			                <label class="control-label" style="width: 90px;">
			                    我方分成比例：
			                </label>
			                <div class="controls">
			                    <input class="control-text" id="ourRatio" name="ourRatio" style="width: 100px;" type="text">
			                    </input>
			                </div>
			            </div>
			            <div class="control-group span7">
			                <label class="control-label" style="width: 90px;">
			                    首月回款比例：
			                </label>
			                <div class="controls">
			                    <input class="control-text" id="backRatio" name="backRatio" style="width: 120px;" type="text">
			                    </input>
			                </div>
			            </div>
			            <div class="control-group span8">
			                <label class="control-label" style="width: 90px;">
			                    月付费留存：
			                </label>
			                <div class="controls">
			                    <input class="control-text" id="monthPayRemain" name="monthPayRemain" style="width: 100px;" type="text">
			                    </input>
			                </div>
			            </div>

			            <div class="control-group span8">
			                <div class="controls">
			                    <button class="button button-warning" id="btnSearch" type="button">
			                        生成
			                    </button>
			                </div>		                
			            </div>
			        </div>
			    </form>
			</div>
			<div style="color:red;margin-left:10px;">
			    <p>首月LTV和预付费留存,否则会自动选择最近的数据;</p>
			    <p>若不填入数据,则【我方分成比例】默认为55,【投放单价】默认为60;</p>
			    <p>首月回款比例=首月LTV/投放单价,若填写【首月回款比例】则不采用该公式;</p>
			</div>
			<div class="search-grid-container span25">
			    <div id="grid"></div>
			</div>
			<div  class="search-grid-container span25" style="margin-top: 50px">
			    <div id="periodData">
			    </div>
			</div>
			<div class="search-grid-container span25" style="margin-top: 20px">
			    <div id="priceData">
			    </div>
			</div>
			<!-- 弹窗 -->
			<div class="hide" id="content"></div>
		</div>

	
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

	    $('#btnSearch').click(function(){
	    	$("#periodData").empty();
	    	$("#priceData").empty();
	    	var game_id = $('#game_id').val();
	    	var advteruser_id = $('#advteruser_id').val();
	    	var agent_p = $('#agent_p').val();
	    	var agent = $('#agent').val();
	    	var startDate = $('#startDate').val();
	    	var endDate = $('#endDate').val();
	    	var lookType = $('#lookType').val();
	    	var putPrice = $('#putPrice').val();
	    	var firstLtv = $('#firstLtv').val();
	    	var ourRatio = $('#ourRatio').val();
	    	var backRatio = $('#backRatio').val();
	    	var monthPayRemain = $('#monthPayRemain').val();
	    	_data = {game_id:game_id,advteruser_id:advteruser_id,agent_p:agent_p,agent:agent,
	    		startDate,startDate,endDate:endDate,lookType:lookType,putPrice:putPrice,
	    		firstLtv:firstLtv,ourRatio:ourRatio,backRatio:backRatio,monthPayRemain};
	    	
	    	var _periodHtml = '<table border="1px" style="width:100%;border:1px solid #C5C5C5;line-height: 25px;">'+ 
	    					  '<tr style="background-color: #FCFCFC">'+
	    					  '<td></td>'+
			    			  '<td>游戏名称</td>'+
			    			  '<td>投放单价</td>'+
			    			  '<td>我方分成比例</td>'+
			    			  '<td>回本指标</td>'+
			    			  '<td>首月回款比例</td>'+
			    			  '<td>月付费留存</td>'+
			    			  '<td>第1月</td>'+
			    			  '<td>第2月</td>'+
			    			  '<td>第3月</td>'+
			    			  '<td>第4月</td>'+
			    			  '<td>第5月</td>'+
			    			  '<td>第6月</td>'+
			    			  '<td>回本周期</td>'+
			    			  '<td>毛利率</td>'+
			    			  '</tr>';
	    	var _priceHtml = '<table border="1px" style="width:100%;border:1px solid #C5C5C5;line-height: 25px;">'+ 
	    					 '<tr style="background-color: #FCFCFC">'+
	    					 '<td></td>'+
			    			 '<td>游戏名称</td>'+
			    			 '<td>回本周期</td>'+
			    			 '<td>投放单价</td>'+
			    			 '<td>我方分成比例</td>'+
			    			 '<td>首月LTV</td>'+
			    			 '<td>首月回款比例</td>'+
			    			 '<td>月付费留存</td>'+
			    			 '<td>第1月</td>'+
			    			 '<td>第2月</td>'+
			    			 '<td>第3月</td>'+
			    			 '<td>第4月</td>'+
			    			 '<td>第5月</td>'+
			    			 '<td>第6月</td>'+
			    			 '<td>最高单价</td>'+
			    			 '</tr>';
	    	$.post("<?php echo U('AdvterData/getPeriodDataByAjax');?>",_data,function(res){
	    		var res = eval('('+res+')');
	    		var periodData = res.backPeriod;
	    		var priceData = res.maxPrice;

	    		_periodHtml += '<tr>'+
			    			  '<td>当月回款比例</td>'+
			    			  '<td rowspan="2" >'+periodData.gameName+'</td>'+
			    			  '<td rowspan="2" >'+periodData.putPrice+'</td>'+
			    			  '<td rowspan="2" >'+periodData.ourRatio+'</td>'+
			    			  '<td rowspan="2" >'+periodData.backTarget+'</td>'+
			    			  '<td rowspan="2" >'+periodData.backRatio+'</td>'+
			    			  '<td rowspan="2" >'+periodData.monthPayRemain+'</td>'+
			    			  '<td>'+periodData.currentBackRatio.month1+'</td>'+
			    			  '<td>'+periodData.currentBackRatio.month2+'</td>'+
			    			  '<td>'+periodData.currentBackRatio.month3+'</td>'+
			    			  '<td>'+periodData.currentBackRatio.month4+'</td>'+
			    			  '<td>'+periodData.currentBackRatio.month5+'</td>'+
			    			  '<td>'+periodData.currentBackRatio.month6+'</td>'+
			    			  '<td rowspan="2">'+periodData.month+'</td>'+
			    			  '<td rowspan="2"></td>'+
			    		      '</tr>'+
			    			  '<tr>'+
			    			  '<td>累计回款比例</td>'+
			    			  '<td>'+periodData.totalBackRatio.month1+'</td>'+
			    			  '<td>'+periodData.totalBackRatio.month2+'</td>'+
			    			  '<td>'+periodData.totalBackRatio.month3+'</td>'+
			    			  '<td>'+periodData.totalBackRatio.month4+'</td>'+
			    			  '<td>'+periodData.totalBackRatio.month5+'</td>'+
			    			  '<td>'+periodData.totalBackRatio.month6+'</td>'+
			    		      '</tr>';
			   	_priceHtml += '<tr>'+
			    			 '<td>当月LTV</td>'+
			    			 '<td rowspan="2" >'+priceData.gameName+'</td>'+
			    			 '<td rowspan="2" >'+priceData.month+'</td>'+
			    			 '<td rowspan="2" >'+priceData.putPrice+'</td>'+
			    			 '<td rowspan="2" >'+priceData.ourRatio+'</td>'+
			    			 '<td rowspan="2" >'+priceData.firstLtv+'</td>'+
			    			 '<td rowspan="2" >'+priceData.backRatio+'</td>'+
			    			 '<td rowspan="2" >'+priceData.monthPayRemain+'</td>'+
			    			 '<td>'+priceData.currentLtv.month1+'</td>'+
			    			 '<td>'+priceData.currentLtv.month2+'</td>'+
			    			 '<td>'+priceData.currentLtv.month3+'</td>'+
			    			 '<td>'+priceData.currentLtv.month4+'</td>'+
			    			 '<td>'+priceData.currentLtv.month5+'</td>'+
			    			 '<td>'+priceData.currentLtv.month6+'</td>'+
			    			 '<td rowspan="2">'+priceData.maxPrice+'</td>'+
			    		     '</tr>'+
			    		     '<tr>'+
			    			 '<td>累计LTV</td>'+
			    			 '<td>'+priceData.totalLtv.month1+'</td>'+
			    			 '<td>'+priceData.totalLtv.month2+'</td>'+
			    			 '<td>'+priceData.totalLtv.month3+'</td>'+
			    			 '<td>'+priceData.totalLtv.month4+'</td>'+
			    			 '<td>'+priceData.totalLtv.month5+'</td>'+
			    			 '<td>'+priceData.totalLtv.month6+'</td>'+
			    		     '</tr>';

			    		     _periodHtml += '</table>'; 
			    		     _priceHtml += '</table>';

			    		     $("#periodData").html(_periodHtml);
			    		     $("#priceData").html(_priceHtml);
	    	});
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

	BUI.use('common/search',function(Search){
		columns = [
		    {title:'游戏名称',dataIndex:'gameName',width:100,elCls:'center'},
		    {title:'一日付费留存率',dataIndex:'day1',width:100,elCls:'center'},
		    {title:'三日付费留存率',dataIndex:'day3',width:100,elCls:'center'},
		    {title:'七日付费留存率',dataIndex:'day7',width:100,elCls:'center'},
		    {title:'十五付费留存率',dataIndex:'day15',width:100,elCls:'center'},
		    {title:'三十日付费留存',dataIndex:'day30',width:100,elCls:'center'},
		    {title:'一日Ltv',dataIndex:'ltv1',width:100,elCls:'center'},
		    {title:'三日Ltv',dataIndex:'ltv3',width:100,elCls:'center'},
		    {title:'七日Ltv',dataIndex:'ltv7',width:100,elCls:'center'},
		    {title:'十五日Ltv',dataIndex:'ltv15',width:100,elCls:'center'},
		    {title:'三十日Ltv',dataIndex:'ltv30',width:100,elCls:'center'},
		];
		store = Search.createStore('<?php echo U("AdvterData/backPeriod");?>', {
		    proxy: {
		        save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
		        },
		        method: 'POST'
		    },
		    autoSync: true //保存数据后，自动更新
		});
		gridCfg = Search.createGridCfg(columns, {
		    forceFit : true,
		    bbar : {pagingBar : false},
		    plugins: [BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
		});

		var search = new Search({
		        store: store,
		        gridCfg: gridCfg
		    }),
		grid = search.get('grid');
	});
</script>

<script type="text/javascript">
        var Grid = BUI.Grid,
        Data = BUI.Data;
        var Store = Data.Store,
        columns = [
            {title:'',dataIndex:'a',width:100,elCls:'center'},
            {title:'游戏名称',dataIndex:'gameName',width:100,elCls:'center'},
            {title:'投放单价',dataIndex:'',width:100,elCls:'center'},
            {title:'我方分成比例',dataIndex:'',width:100,elCls:'center'},
            {title:'回本指标',dataIndex:'',width:100,elCls:'center'},
            {title:'首月回款比例',dataIndex:'',width:100,elCls:'center'},
            {title:'月付费留存',dataIndex:'',width:100,elCls:'center'},
            {title:'第1月',dataIndex:'',width:100,elCls:'center'},
            {title:'第2月',dataIndex:'',width:100,elCls:'center'},
            {title:'第3月',dataIndex:'',width:100,elCls:'center'},
            {title:'第4月',dataIndex:'',width:100,elCls:'center'},
            {title:'第5月',dataIndex:'',width:100,elCls:'center'},
            {title:'第6月',dataIndex:'',width:100,elCls:'center'},
            {title:'回本周期',dataIndex:'',width:100,elCls:'center'},
            {title:'毛利率',dataIndex:'',width:100,elCls:'center'},
        ];

      data = [{a:'当月回款比例'},{a:'累计回款比例',b:'edd'}];

    var store = new Store({
        data : data
      }),
      grid = new Grid.Grid({
        render:'#grid1',
        width:'100%',//如果表格使用百分比，这个属性一定要设置
        columns : columns,
        idField : 'a',
        store : store
      });

    grid.render();
</script>

<script type="text/javascript">
        var Grid = BUI.Grid,
        Data = BUI.Data;
        var Store = Data.Store,
        columns = [
            {title:'',dataIndex:'a',width:100,elCls:'center'},
            {title:'游戏名称',dataIndex:'gameName',width:100,elCls:'center'},
            {title:'回本周期',dataIndex:'',width:100,elCls:'center'},
            {title:'投放单价',dataIndex:'',width:100,elCls:'center'},
            {title:'我方分成比例',dataIndex:'',width:100,elCls:'center'},
            {title:'首月LTV',dataIndex:'',width:100,elCls:'center'},
            {title:'首月回款比例',dataIndex:'',width:100,elCls:'center'},
            {title:'月付费留存',dataIndex:'',width:100,elCls:'center'},
            {title:'第1月',dataIndex:'',width:100,elCls:'center'},
            {title:'第2月',dataIndex:'',width:100,elCls:'center'},
            {title:'第3月',dataIndex:'',width:100,elCls:'center'},
            {title:'第4月',dataIndex:'',width:100,elCls:'center'},
            {title:'第5月',dataIndex:'',width:100,elCls:'center'},
            {title:'第6月',dataIndex:'',width:100,elCls:'center'},
            {title:'最高单价',dataIndex:'',width:100,elCls:'center'}
        ];

      data = [
      			{a:'当月LTV'},
      			{a:'累计LTV'}
      		];

    var store = new Store({
        data : data
      }),
      grid = new Grid.Grid({
        render:'#grid2',
        width:'100%',//如果表格使用百分比，这个属性一定要设置
        columns : columns,
        idField : 'a',
        store : store
      });

    grid.render();
</script>