<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            投放数据概况统计
        </title>
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
        <script src="/static/admin/js/echart/echarts.min.js" type="text/javascript"></script>
    </head>
    
        <style>
            tfoot .bui-grid-cell-text{text-align: center;}
  .btn-default {height:25px;}
  .filter-option {margin-top: -4px;}
  .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
  .helpTipWrap{
    width: 250px;
    margin: -10px 0px 0px -340px;
    padding:10px;
    position: absolute;
    z-index: 9999;
    background-color: #fff;
    border: 1px solid #00a9e8;
  }
  .combo-dropdown{
    z-index: 1999;
  }
  .helpTipWrap:after {
    position: absolute;
    display: block;
    width: 0;
    height: 0;
    border-color: transparent transparent transparent #00a9e8;
    border-style: solid;
    border-width: 5px;
    bottom: 100%;
    left: initial;
    content: '';
    right: -4%;
    top: 50%;
    transform: translateY(-50%);
}

}
        </style>
        <style>
        #typebut{
            width: 60px;
            padding: 4px;
            background-color: #407686;
            border-color: #357ebd;
            color: #fff;
            -moz-border-radius: 10px;
            -webkit-border-radius: 10px;
            border-radius: 4px;
            -khtml-border-radius: 10px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid transparent;
            font-size: 13px;
            cursor: pointer;
            margin-bottom: 15px;
            margin-left: 14px;
            font-family: 微软雅黑;  
            }
            #typetoggle{width: 600px; display: none; margin-left: 4px; margin-bottom: 15px;}
            .butcheck {color: #428bca;padding: 4px;border: 1px solid #a9c9e2;background: #e8f5fe;}
            .checkover {width: 100%;height: 80px;overflow: auto;}
            .checklabel {padding: 5px 0;width: 110px;position: relative;text-indent:22px}
            input[type="checkbox"]{appearance: none; -webkit-appearance: none;outline: none;display:none}
            label{display:inline-block;cursor:pointer;}
            label input[type="checkbox"] + span{width:20px;height:20px;display:inline-block;position:absolute;top:4px;left:0;background:url(/static/admin/img/checkbox_01.gif)  no-repeat;background-position:0 0;}
            label input[type="checkbox"]:checked + span{background-position:0 -21px}
        </style>
    
    <body>
        <!-- 搜索区 -->
        <div class="container">
            <div class="row">
                <form class="form-horizontal span48" id="searchForm" method="post">
                    <div class="row">
                        <div class="control-group span8">
                            <label class="control-label" style="width: 80px;">
                                游戏名称：
                            </label>
                            <div class="controls">
                                <select id="game_id" class="selectpicker" data-actions-box="true" data-live-search="true" multiple="" name="game_id[]">
                                </select>
                            </div>
                        </div>
                        <div class="control-group span8">
                            <label class="control-label" style="width: 60px;">
                                母包：
                            </label>
                            <div class="controls" id="p_agent_contain">
                                <select class="selectpicker" data-actions-box="true" data-live-search="true" id="agent_p" multiple="" name="agent_p[]">
                                </select>
                            </div>
                        </div>
                        <div class="control-group span9">
                            <label class="control-label" style="width: 100px;">
                                渠道商：
                            </label>
                            <div class="controls">
                                <select class="selectpicker" data-actions-box="true" data-live-search="true" multiple="" id="advteruser_id" name="advteruser_id[]">
                                </select>
                            </div>
                        </div>
                        <div class="control-group span9">
                            <label class="control-label" style="width: 100px;">
                                推广活动组：
                            </label>
                            <div class="controls">
                                <select class="selectpicker" data-actions-box="true" data-live-search="true" id="events_groupId" multiple="" name="events_groupId[]">
                                </select>
                            </div>
                        </div>
                        <div class="control-group span8" id="advter">
                            <label class="control-label" style="width: 80px;">
                                推广活动：
                            </label>
                            <div class="controls">
                                <select class="selectpicker" data-actions-box="true" data-live-search="true" id="advter_id" multiple="" name="advter_id[]">
                                </select>
                            </div>
                        </div>
                        <div class="control-group span9">
                            <label class="control-label" style="width: 120px;">
                                渠道包创建人：
                            </label>
                            <div class="controls">
                                <select id="creater" name="creater">
                                </select>
                            </div>
                        </div>
                        <div class="control-group span9">
                            <label class="control-label" style="width: 80px;">
                                渠道号：
                            </label>
                            <div class="controls" id="agent_contain">
                                <select class="selectpicker" data-actions-box="true" data-live-search="true" id="agent_id" multiple="" name="agent[]">
                                </select>
                            </div>
                        </div>
                        <?php if(session('admin.partment') == 0): ?><div class="control-group span6">
                                <label class="control-label" style="width: 60px;">
                                    部门：
                                </label>
                                <div class="controls">
                                    <select id="department" name="department">
                                        <option selected="selected" value="0">
                                            全部
                                        </option>
                                        <?php echo ($tplPartment); ?>
                                    </select>
                                </div>
                            </div><?php endif; ?>
                        <div class="control-group span6">
                            <label class="control-label" style="width: 100px;">
                                是否汇总：
                            </label>
                            <div class="controls">
                                <select id="isCount" name="isCount" style="width: 80px;">
                                    <option value="2">
                                        否
                                    </option>
                                    <option value="1">
                                        是
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group span6">
                            <label class="control-label" style="width: 80px;">
                                系统：
                            </label>
                            <div class="controls">
                                <select id="system" name="system" style="width: 80px;">
                                    <option value="0">
                                        全部
                                    </option>
                                    <option value="1">
                                        安卓
                                    </option>
                                    <option value="2">
                                        IOS
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group span7">
                            <label class="control-label" style="width: 60px;">
                                统计日期：
                            </label>
                            <div class="controls">
                                <input class="calendar" id="startDate" name="startDate" type="text" value="<?php echo date('Y-m-d',strtotime(date('Y-m-d').' -3 day'));?>">
                                    <span>
                                        -
                                    </span>
                                    <input class="calendar" id="endDate" name="endDate" type="text" value="<?php echo date('Y-m-d');?>">
                                    </input>
                                </input>
                            </div>
                        </div>
                        <div class="control-group span8">
                            <label class="control-label" style="width: 100px;">
                                <div id="typebut">数据显示</div>
                            </label>
                        <div class="controls" style="height: auto;">
                            <div id="typetoggle">  
                                <input type="button" class="butcheck" id="btn1" value="全选"> 
                                <input type="button" class="butcheck" id="btn2" value="反选">
                                <div class="checkover">
                                    <div class="checkscroll">
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="dayTime_0">日期<span></span></label> 
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="gameName_1">游戏名称<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="agent_2">包编号<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="advteruserName_3">所属广告商<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="cost_4">成本<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="newDevice_5">新增设备数<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="disUdid_6">唯一注册数<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="newUser_7">新增账号数<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="regRate_8">注册转化率<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="regCost_9">注册单价<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="newPay_10">新增充值金额<span></span></label>
                                        <label class="checklabel"><input type="checkbox" checked="checked" name="displayColumn[]" value="newPayUser_11">新增充值人数<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="newPayRate_12">新增付费率<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="newARPU_13">新增ARPU<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="newARPPU_14">新增ARPPU<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="allPay_15">当天充值金额<span></span></label> 
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="allPayUser_16">充值人数<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="actPayRate_17">活跃付费率<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="ARPU_18">活跃ARPU<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="ARPPU_19">活跃ARPPU<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="day1_20">次留<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="oldUserLogin_21">老用户活跃数<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="DAU_22">DAU<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="dayRate_23">1日回本率<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="allPayNow_24">区间充值总额<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="allPayRate_25">区间回本率<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="totalPay_26">至今充值金额<span></span></label>
                                        <label class="checklabel"><input type="checkbox" name="displayColumn[]" value="totalPayRate_27">至今回本率<span></span></label>
                                    </div>
                                </div>
                            </div>
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
                        </div>
                    </div>
                </form>
                <form action='<?php echo U("AdvterData/advData");?>' id="subfm" method="post">
                    <input name="game_id" type="hidden" value=""/>
                    <input name="events_groupId" type="hidden" value=""/>
                    <input name="advter_id" type="hidden" value=""/>
                    <input name="department" type="hidden" value=""/>
                    <input name="agent_p" type="hidden" value=""/>
                    <input name="agent" type="hidden" value=""/>
                    <input name="creater" type="hidden" value=""/>
                    <input name="advteruser_id" type="hidden" value=""/>
                    <input name="startDate" type="hidden" value=""/>
                    <input name="endDate" type="hidden" value=""/>
                    <input name="export" type="hidden" value="1"/>
                    <input name="isCount" type="hidden" value=""/>
                    <input name="system" type="hidden" value=""/>
                </form>
            </div>
            <div style="color:red;margin-left:10px;">
                PS：投放数据15分钟更新一次,至今充值一天更新一次(当天充值金额（不分新老用户充值），区间充值总额和至今充值金额以注册日期算)
            </div>
            <div class="search-grid-container span25">
                <div id="grid">
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
            $("#subfm input[name=department]").val($("#department").val());
            $("#subfm input[name=agent_p]").val($('#agent_p').val());
            $("#subfm input[name=events_groupId]").val($('#events_groupId').val());
            $("#subfm input[name=advter_id]").val($('#advter_id').val());
            $("#subfm input[name=agent]").val($('#agent_id').val());
            $("#subfm input[name=creater]").val($('#creater').val());
            $("#subfm input[name=isCount]").val($('#isCount').val());
            $("#subfm input[name=advteruser_id]").val($("#advteruser_id").val());
            $("#subfm input[name=startDate]").val($("#startDate").val());
            $("#subfm input[name=endDate]").val($("#endDate").val());
            $("#subfm input[name=system]").val($("#system").val());
            $('#subfm').submit();
        })
        gameLists();
        advteruser_id();
        getAgentCreater();
        groupList();

        $('#events_groupId').change(function() {
            var events_groupId = $(this).val();
            var game_id        = $('#game_id').val();
            var agent_p         = $('#agent_p').val();
            var advteruser_id = $('#advteruser_id').val();

            getEventByGroup(events_groupId,game_id,agent_p,advteruser_id);
        });

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
            var events_groupId = $('#events_groupId').val();
            var agent_p = $('#agent_p').val();

            getAgentByGame(game_id,advteruser_id,creater);
            getEventByGroup(events_groupId,game_id,agent_p,advteruser_id);

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
            var events_groupId = $('#events_groupId').val();
            getAgentByGame(game_id,advteruser_id,creater);
            getEventByGroup(events_groupId,game_id,$(this).val(),advteruser_id);

        });
        
    });

    //获取母包渠道号
    function getPAgentByGame(game_id,advteruser_id,creater,gameType){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgent');?>",{game_id:game_id},function(ret){
            _html += "<option>--全部--</option>";
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
            });
            $('#agent_p').html(_html);
            $('#agent_p').selectpicker('refresh');
            $('#agent_p').selectpicker('val', '--全部--');
        });
    }

    //获取渠道号
    function getAgentByGame(game_id,advteruser_id,creater,gameType){
        var agent = $('#agent_p').val();
        var _html = '';
        _data = {game_id:game_id,advteruser_id:advteruser_id,creater:creater,agent:agent}
        $.post("<?php echo U('Ajax/getAgentByGame');?>",_data,function(ret){
            _html += "<option>--全部--</option>";
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.agents+">"+v.agent+"</option>";
            });
            $('#agent_id').html(_html);
            $('#agent_id').selectpicker('refresh');
            $('#agent_id').selectpicker('val', '--全部--');
        });
    }

    //获取游戏
    function gameLists(){
        var _html = '';
        $.post("<?php echo U('Ajax/getGameList');?>",{},function(ret){
            _html += '<option>--全部--</option>';
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.gameName+"</option>";
            });
            $('#game_id').html(_html);
            $('#game_id').selectpicker('refresh');
            $('#game_id').selectpicker('val', '--全部--');
        });
    }

    //获取推广组
    function groupList(){
        var _html = '';
        $.post("<?php echo U('Ajax/getEventGroup');?>",'',function(ret){
            _html += '<option>--全部--</option>';
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.groupName+"</option>";
            });
            $('#events_groupId').html(_html);
            $('#events_groupId').selectpicker('refresh');
        });
    }

    //获取推广活动列表
    function getEventList(){
        var _html = '';
        $.post("<?php echo U('Ajax/getEventList');?>",{all:1},function(ret){
            var _html = '<option>--全部--</option>';
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.events_name+"</option>";
            });
            $('#advter_id').html(_html);
            $('#advter_id').selectpicker('refresh');
            $('#advter_id').selectpicker('val', '--全部--');
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
        $.post("<?php echo U('Ajax/adv_company');?>",{all:0},function(ret){
            var _html = '<option>--全部--</option>';
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.company_name+"</option>";
            });
            $('#advteruser_id').html(_html);
            $('#advteruser_id').selectpicker('refresh');
            $('#advteruser_id').selectpicker('val', '--全部--');
        });
    }

    //通过组获取推广活动
    function getEventByGroup(events_groupId,game_id,agent_p,advteruser_id){
        var _html = '';
        $.post("<?php echo U('Ajax/getEventByGroup');?>",{events_groupId:events_groupId,game_id:game_id,agent_p:agent_p,advteruser_id:advteruser_id},function(ret){
            _html += '<option>--全部--</option>';
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.events_name+"</option>";
            });
            $('#advter_id').html(_html);
            $('#advter_id').selectpicker('refresh');
            $('#advter_id').selectpicker('val', '--全部--');
        });
    }

    //金额格式化
    function fmoney(s, n) {
      n = n > 0 && n <= 20 ? n : 2;
      s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
      var l = s.split(".")[0].split("").reverse(),
        r = s.split(".")[1];
      t = "";
      for (i = 0; i < l.length; i++) {
        t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
      }
      return t.split("").reverse().join("") + "." + r;
    }
 //获得事件
function getEvent(){
     if(window.event)    {return window.event;}
     func=getEvent.caller;
     while(func!=null){
         var arg0=func.arguments[0];
         if(arg0){
             if((arg0.constructor==Event || arg0.constructor ==MouseEvent
                || arg0.constructor==KeyboardEvent)
                ||(typeof(arg0)=="object" && arg0.preventDefault
                && arg0.stopPropagation)){
                 return arg0;
             }
         }
         func=func.caller;
     }
     return null;
}

function showTip(obj){
    var e = getEvent();
    //一般用在鼠标或键盘事件上
      if((e && e.stopPropagation) || e.preventDefault){
          //W3C取消冒泡事件
          e.stopPropagation();
      }else if (window.event){
          //IE取消冒泡事件
          window.event.cancelBubble = true;
      }
    $(obj).children('.helpTipWrap').fadeToggle("fast","linear");
}

$('body').click(function(){
    $('.helpTipWrap').hide();
})

function getTreeData(_data){
    BUI.use(['bui/extensions/treegrid'],function (TreeGrid) {
      var data = _data;
      var chk =[]; 
      $('input[name="displayColumn[]"]:checked').each(function(){ 
            chk.push(Number($(this).val().split('_')[1])); 
      }); 
      var temp = [
            {title : '日期',dataIndex :'dayTime', width:150,elCls:'center',sortable : false},
            {title : '游戏名称',dataIndex :'gameName', width:200,elCls:'center',sortable : false},
            {title : '包编号',dataIndex :'agent', width:200,elCls:'center',sortable : false},
            {title : '所属广告商',dataIndex :'advteruserName', width:100,elCls:'center',sortable : false}, 
            {title : '成本<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>成本：</h6>选择日期范围内，每天每个渠道号的成本。</div></span>',dataIndex :'cost', width:100,elCls:'center',sortable : false},
            {title : '新增设备数<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>新增设备数：</h6>选择日期范围内，每天新增加的设备数。</div></span>',dataIndex :'newDevice', width:100,elCls:'center',sortable : false},
            {title : '唯一注册数<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>唯一注册数：</h6>选择日期范围内，每天新增加并注册的设备数。</div></span>',dataIndex :'disUdid', width:100,elCls:'center',sortable : false},
            {title : '新增账号数<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>新增账号数：</h6>选择日期范围内，每天新增加的账号数。</div></span>',dataIndex :'newUser', width:100,elCls:'center',sortable : false},
            {title : '注册转化率<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>注册转化率：</h6>选择日期范围内，唯一注册数/新增设备数。</div></span>',dataIndex :'regRate', width:100,elCls:'center',sortable : false},
            {title : '注册单价<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>注册转化率：</h6>选择日期范围内，成本/新增账号数。</div></span>',dataIndex :'regCost', width:100,elCls:'center',sortable : false},
            {title : '新增充值金额<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>新增充值金额：</h6>选择日期范围内，新增玩家在当日的充值金额。</div></span>',dataIndex :'newPay', width:100,elCls:'center',sortable : false},
            {title : '新增充值人数<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>新增充值人数：</h6>选择日期范围内，新增当天注册并充值人数。</div></span>',dataIndex :'newPayUser', width:100,elCls:'center',sortable : false},
            {title : '新增付费率<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>新增付费率：</h6>选择日期范围内，新增充值人数/新增账号数。</div></span>',dataIndex :'newPayRate', width:100,elCls:'center',sortable : false},
            {title : '新增ARPU<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>新增ARPU：</h6>选择日期范围内，新增充值金额/新增账号数。</div></span>',dataIndex :'newARPU', width:100,elCls:'center',sortable : false},
            {title : '新增ARPPU<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>新增ARPPU：</h6>选择日期范围内，新增充值金额/新增充值人数。</div></span>',dataIndex :'newARPPU', width:100,elCls:'center',sortable : false},
            {title : '当天充值金额<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>当天充值金额：</h6>选择日期范围内，每天的总充值金额。</div></span>',dataIndex :'allPay', width:100,elCls:'center',sortable : false},
            {title : '充值人数<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>充值人数：</h6>选择日期范围内，每天的总充值人数。</div></span>',dataIndex :'allPayUser', width:100,elCls:'center',sortable : false},
            {title : '活跃付费率<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>活跃付费率：</h6>选择日期范围内，去重活跃充值人数/DAU。</div></span>',dataIndex :'actPayRate', width:100,elCls:'center',sortable : false},
            {title : '活跃ARPU<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>活跃ARPU：</h6>选择日期范围内，当天充值金额/DAU。</div></span>',dataIndex :'ARPU', width:100,elCls:'center',sortable : false},
            {title : '活跃ARPPU<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>活跃ARPU：</h6>选择日期范围内，当天充值金额/充值人数。</div></span>',dataIndex :'ARPPU', width:100,elCls:'center',sortable : false},
            {title : '次留<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>次留：</h6>选择日期范围内，次日有登录玩家数/新增账号数。</div></span>',dataIndex :'day1', width:100,elCls:'center',sortable : false},
            {title : '老用户活跃数<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>老用户活跃数：</h6>选择日期范围内，非新增活跃玩家去重汇总。</div></span>',dataIndex :'oldUserLogin', width:100,elCls:'center',sortable : false},
            {title : 'DAU<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>DAU：</h6>选择日期范围内，活跃玩家去重汇总。</div></span>',dataIndex :'DAU', width:100,elCls:'center',sortable : false},
            {title : '1日回本率<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>1日回本率：</h6>选择日期范围内，新增充值金额/成本。</div></span>',dataIndex :'dayRate', width:100,elCls:'center',sortable : false},
            {title : '区间充值总额<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>区间充值总额：</h6>当天注册并且在选定日期内的充值金额（实时）。</div></span>',dataIndex :'allPayNow', width:100,elCls:'center',sortable : false},
            {title : '区间回本率<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>区间回本率：</h6>选择日期范围内，区间充值总额/成本（实时）。</div></span>',dataIndex :'allPayRate', width:100,elCls:'center',sortable : false},
            {title : '至今充值金额<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>至今充值金额：</h6>选择日期范围内，每个包历史总充值金额（非实时，每天凌晨更新前一天充值）。</div></span>',dataIndex :'totalPay', width:100,elCls:'center',sortable : false},
            {title : '至今回本率<span class="icon-question-sign" onclick="showTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>至今回本率：</h6>选择日期范围内，至今充值金额/成本。</div></span>',dataIndex :'totalPayRate', width:100,elCls:'center',sortable : false}
          ];
          var columns = [];
          for (var i=0;i<chk.length;i++){
                columns[i] = temp[chk[i]];
          }
      //由于这个树，不显示根节点，所以可以不指定根节点
      var tree = new TreeGrid({
        render : '#grid',
        nodes : data,
        columns : columns,
        plugins : [BUI.Grid.Plugins.ColumnResize], // 插件形式引入多选表格

        height:520
      });
      tree.render();
      
  });

}

$('#btnSearch').click(function(){
    /*var game_id = $('#game_id').val();
    if(game_id == 0 || game_id == ''){
        alert('不选择只能导出看数据,不允许搜索');
        return false;
    }*/
    $('.mark').show();
    $('.spinner').show();
    var _data = $('#searchForm').serialize();
    $.post('<?php echo U("AdvterData/advData");?>',_data,function(ret){
        var ret = eval('('+ret+')');
        console.log(ret.rows.length);
        if(ret.rows.length > 0){
            $('#grid').html('');
            getTreeData(ret.rows);
            $('.mark').hide();
            $('.spinner').hide();
        }else{
            alert('无搜索结果');
            $('.mark').hide();
            $('.spinner').hide();
            return false;
        }
        
    });
});
        </script>
            <script type="text/javascript">
                jQuery(function($){ 
                    //全选 
                    $("#btn1").click(function(){ 
                        $("input[name='displayColumn[]']").prop("checked","true"); 
                    })
                    //反选 
                    $("#btn2").click(function(){ 
                        $("input[name='displayColumn[]']").each(function(){ 
                            if($(this).prop("checked")) { 
                                $(this).removeAttr("checked"); 
                            } 
                            else { 
                                $(this).prop("checked","true"); 
                            } 
                        }) 
                    })
                    $("#typebut").click(function(){
                        $("#typetoggle").toggle();
                    });
                }) 
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