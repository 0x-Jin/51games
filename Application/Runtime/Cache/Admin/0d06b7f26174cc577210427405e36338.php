<?php if (!defined('THINK_PATH')) exit();?><body>
<style type="text/css">
tfoot .bui-grid-cell-text{text-align: center;}
  .btn-default {height:25px;}
  .filter-option {margin-top: -4px;}
  .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
  .combo-dropdown {
        z-index: 9999;
    }
    input{border-radius: 4px;}
</style>
<div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Game/serverAdd');?>">
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">游戏名称：</label>
                <div class="controls">
                    <select name="game_id" id="game_id_add"></select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">渠道分类：</label>
                <div class="controls">
                    <select name="channel_id" id="channelId"></select>
                 </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span10">
                <label class="control-label">母包：</label>
                <div class="controls">
                    <select class="selectpicker" data-actions-box="true" data-live-search="true" id="agent_p" multiple="" name="agent_p[]"></select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">区服ID：</label>
                <div class="controls">
                    <input type="text" name="serverId" data-rules="{required:true}" class="input-normal control-text" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">区服名称：</label>
                <div class="controls">
                    <input type="text" name="serverName" data-rules="{required:true}" class="input-normal control-text" />
                </div>
            </div>
        </div>

        <!-- <div class="row">
            <div class="control-group span8">
                <label class="control-label">手机系统：</label>
                <div class="controls">
                    <select name="serverType">
                        <option value="0">全类型</option>
                        <option value="1">IOS</option>
                        <option value="2">安卓</option>
                    </select>
                </div>
            </div>
        </div> -->

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">开服时间：</label>
                <div class="controls">
                    <input type="text" id="openTime" class="calendar calendar-time" name="openTime" />
                </div>
            </div>
        </div>
    </form>
</div>

<!-- script start -->
<script type="text/javascript">
    $(function(){
        //数据加载
        gameList();
        channelType();
        $('#game_id_add').change(function(){
            var channel_id = $('#channelId').val();
            getPAgentByGame($(this).val(),channel_id);
        });
        $('#channelId').change(function(){
            var game_id = $('#game_id_add').val();
            getPAgentByGame(game_id,$(this).val());
        })
        $('.selectpicker').selectpicker({
                selectAllText: '全选',
                deselectAllText: '不选',
                liveSearchPlaceholder: '搜索关键字',
                noneSelectedText: '',
                multipleSeparator: ',',
                liveSearch: true,
                actionsBox: true
            });
    });

    //获取游戏
    function gameList(){
        var _html = '';
        $.post("<?php echo U('Ajax/getGameList');?>",'',function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i, v){
                _html += "<option value="+v.id+">"+v.gameName+"</option>";
            });
            $('#game_id_add').html(_html).comboSelect();
        });
    }

    //获取母包渠道号
    function getPAgentByGame(game_id,channel_id){
        var _html = '';
        var power = 0;
        if(game_id == 112){
            power = 1;
        }
        $.post("<?php echo U('Ajax/getAgent');?>",{game_id:game_id,power:power,channel:channel_id},function(ret){
            _html += "";
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.agentAll+"</option>";
            });
            $('#agent_p').html(_html);
            $('#agent_p').selectpicker('refresh');
        });
    }

    //获取渠道分类
    function channelType(){
      var _html = '';
        $.post("<?php echo U('Ajax/getChannelList');?>",{all:1},function(ret){
          var ret = eval('('+ret+')');
          $(ret).each(function(i,v){
              _html += "<option value="+v.id+">"+v.channelName+"</option>";
          });
          $('#channelId').html(_html);
          $('#channelId').comboSelect();
        });
    }

    BUI.use(['bui/overlay','bui/form', 'bui/calendar'],function(Overlay, Form, Calendar){

        var form = new Form.HForm({
            srcNode : '#J_Form'
        }).render();

        var datepicker = new Calendar.DatePicker({
            trigger: '#openTime',
            dateMask: 'yyyy-mm-dd HH:MM:ss',
            showTime: true,
            autoRender: true
        });

        var dialog = new Overlay.Dialog({
            title:'新增区服',
            width:520,
            height:320,
            //配置DOM容器的编号
            contentId:'content',
            success:function () {
                $('#J_Form').submit();
            }
        });
        dialog.show();
    });
</script>
<!-- script end -->
</div>
</body>
</html>