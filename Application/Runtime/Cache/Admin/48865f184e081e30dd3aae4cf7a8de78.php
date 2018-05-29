<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>落地页数据统计</title>
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap.min.css">
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min2.css" rel="stylesheet" type="text/css" />
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
  .helpTipWrap{
    width: 250px;
    margin: -10px 0px 0px -340px;
    padding:10px;
    position: absolute;
    z-index: 9999;
    background-color: #fff;
    border: 1px solid #00a9e8;
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
.combo-dropdown{z-index: 999;}
</style>

<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
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
                        <select name="gameType" id="gameType">
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
                            <select name="department" id="department">
                                <option value="0" selected="selected">全部</option>
                                <?php echo ($tplPartment); ?>
                            </select>
                        </div>
                    </div><?php endif; ?>

                <div class="control-group span9">
                    <label class="control-label" style="width: 80px;">落地页名称：</label>
                    <div class="controls" id="agent_contain">
                        <select id="adv_id" name="adv_id[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">
                            
                        </select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">统计时间：</label>
                    <div class="controls">
                        <input type="text" class="calendar" name="startDate" id="startDate" value="<?php echo date('Y-m-d',strtotime(date('Y-m-d').'-1 day'));?>"><span> - </span><input type="text" class="calendar" name="endDate" id="endDate" value="<?php echo date('Y-m-d');?>" />
                    </div>
                </div>

                <div class="control-group span8">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                    </div>
                    <div class="controls">
                        <button  type="button" id="importSearch" class="button button-primary">SEM数据导出</button>
                    </div>
                </div>
            </div>
        </form>
        <form action='<?php echo U("AdvterData/exportSemInfo");?>' id="subfm" method="post">
            <input name="game_id" type="hidden" value=""/>
            <input name="department" type="hidden" value=""/>
            <input name="agent_p" type="hidden" value=""/>
            <input name="agent" type="hidden" value=""/>
            <input name="creater" type="hidden" value=""/>
            <input name="advteruser_id" type="hidden" value=""/>
            <input name="startDate" type="hidden" value=""/>
            <input name="endDate" type="hidden" value=""/>
            <input name="adv_id" type="hidden" value=""/>
            <input name="gameType" type="hidden" value=""/>
            <input name="export" type="hidden" value="1"/>
        </form>
    </div>
    <div style="color:red;margin-left:10px;">PS：落地页数据15分钟更新一次</div>
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
        $('.selectpicker').selectpicker({
            selectAllText: '全选',
            deselectAllText: '不选',
            liveSearchPlaceholder: '搜索关键字',
            noneSelectedText: '',
            multipleSeparator: ',',
            liveSearch: true,
            actionsBox: true
        });

        $('#importSearch').click(function(){
            $("#subfm input[name=game_id]").val($("#game_id").val());
            $("#subfm input[name=department]").val($("#department").val());
            $("#subfm input[name=agent_p]").val($('#agent_p').val());
            $("#subfm input[name=agent]").val($('#agent_id').val());
            $("#subfm input[name=creater]").val($('#creater').val());
            $("#subfm input[name=advteruser_id]").val($("#advteruser_id").val());
            $("#subfm input[name=startDate]").val($("#startDate").val());
            $("#subfm input[name=endDate]").val($("#endDate").val());
            $("#subfm input[name=adv_id]").val($("#adv_id").val());
            $("#subfm input[name=gameType]").val($("#gameType").val());
            $('#subfm').submit();
        })

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
            getFallByGame(game_id,advteruser_id,creater);
        });

        $('#creater').change(function() {
            var creater = $(this).val();
            var game_id = $('#game_id').val();
            var advteruser_id = $('#advteruser_id').val();
            getAgentByGame(game_id,advteruser_id,creater);
            getFallByGame(game_id,advteruser_id,creater);
        });

        $('#agent_p').change(function() {
            var creater = $('#creater').val();
            var game_id = $('#game_id').val();
            var advteruser_id = $('#advteruser_id').val();
            getAgentByGame(game_id,advteruser_id,creater);
            getFallByGame(game_id,advteruser_id,creater);
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

    //获取落地页名称
    function getFallByGame(game_id,advteruser_id,creater){
        var agent = $('#agent_p').val();
        var _html = '';
        _data = {game_id:game_id,advteruser_id:advteruser_id,creater:creater,agent:agent}
        $.post("<?php echo U('Ajax/getFallByGame');?>",_data,function(ret){
            _html += "<option>--全部--</option>";
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.adv_name+"</option>";
            });
            $('#adv_id').html(_html);
            $('#adv_id').selectpicker('refresh');
            $('#adv_id').selectpicker('val', '--全部--');
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

    //落地页sem信息
    function semInfo(id,dayTime){
        $('.bui-dialog:not(.bui-message)').remove();
        if(!id) return false;
        $.get("<?php echo U('AdvterData/semInfo');?>",{advid:id,dayTime:dayTime},function(ret){
            if(ret.status == 0) {alert(ret._html); return false;}
            $('#content').html(ret._html);
            $('#content').show();
        });
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
        $(obj).children('.helpTipWrap').fadeIn("fast");
    }

    function hideTip(obj){
        var e = getEvent();
        //一般用在鼠标或键盘事件上
          if((e && e.stopPropagation) || e.preventDefault){
              //W3C取消冒泡事件
              e.stopPropagation();
          }else if (window.event){
              //IE取消冒泡事件
              window.event.cancelBubble = true;
          }
        $(obj).children('.helpTipWrap').fadeOut("fast").mouseover(function(event) {
            return false;
        });
    }


    BUI.use('common/search',function (Search) {

        editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        }),
            columns = [
                {title:'统计日期',dataIndex:'dayTime',width:100,elCls:'center'},
                {title:'游戏名称',dataIndex:'gameName',width:100,elCls:'center'},
                {title:'广告商名称',dataIndex:'advteruser',width:100,elCls:'center'},
                {title:'渠道号',dataIndex:'agent',width:100,elCls:'center'},
                {title:'落地页广告名称',dataIndex:'advname',width:250,elCls:'center'},
                {title:'打开数<span class="icon-question-sign" onmouseover="showTip(this)" onmouseout="hideTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>打开数：</h6>选择日期范围内，每个落地页的打开总数。</div></span>',dataIndex:'openNum',width:100,elCls:'center'},
                {title:'唯一打开数<span class="icon-question-sign" onmouseover="showTip(this)" onmouseout="hideTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>唯一打开数：</h6>选择日期范围内，每个落地页的排重打开总数。</div></span>',dataIndex:'disOpenNum',width:100,elCls:'center'},
                {title:'下载数<span class="icon-question-sign" onmouseover="showTip(this)" onmouseout="hideTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>下载数：</h6>选择日期范围内，每个落地页的点击下载总数。</div></span>',dataIndex:'downloadNum',width:100,elCls:'center'},
                {title:'唯一下载数<span class="icon-question-sign" onmouseover="showTip(this)" onmouseout="hideTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>唯一下载数：</h6>选择日期范围内，每个落地页的排重（IP）点击下载总数。</div></span>',dataIndex:'disDownloadNum',width:100,elCls:'center'},
                {title:'下载率<span class="icon-question-sign" onmouseover="showTip(this)" onmouseout="hideTip(this)" style="z-index: 22222;"><div class="helpTipWrap" style="display: none; "><h6>下载率：</h6>选择日期范围内，唯一下载数/唯一打开数。</div></span>',dataIndex:'rate',width:100,elCls:'center'}
            ],
            store = Search.createStore('<?php echo U("AdvterData/fallData");?>',{
                proxy : {
                    save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                    },
                    method : 'POST'
                },
                autoSync : true //保存数据后，自动更新
            }),
            gridCfg = Search.createGridCfg(columns,{
                plugins : [editing,BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
            });

        var  search = new Search({
                store : store,
                gridCfg : gridCfg
            }),
            grid = search.get('grid');
    });
</script>

</body>
</html>