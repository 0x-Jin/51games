<?php if (!defined('THINK_PATH')) exit();?><body>
    <style type="text/css">
        input{border-radius: 4px;}
    </style>
    <div class="demo-content">
        <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
        <form action="<?php echo U('IOS/event_add');?>" class="form-horizontal" id="J_Form" method="post">
            <input name="table" type="hidden" value="events"/>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        推广活动名称：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" data-rules="{required:true}" name="events_name" type="text">
                        </input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        推广活动组名称：
                    </label>
                    <div class="controls">
                        <select data-rules="{required:true}" id="events_groupId" name="events_groupId">
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        游戏名称：
                    </label>
                    <div class="controls">
                        <select data-rules="{required:true}" id="agents" name="agent">
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        推广活动渠道商：
                    </label>
                    <div class="controls">
                        <select data-rules="{required:true}" id="advteruserId" name="advteruser_id">
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        回调类型：
                    </label>
                    <div class="controls">
                        <select name="callBackStatus"> 
                          <!-- 0:全部 1:激活报送 2:充值完成报送 3:注册报送 -->
                          <option value="0">全部</option>
                          <option value="1">激活报送</option>
                          <option value="2">充值完成报送</option>
                          <option value="3">注册报送</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        生成的个数：
                    </label>
                    <div class="controls">
                        <input type="text" name="num" class="input-small control-text" data-rules="{required:true}" value="1" />
                    </div>
                </div>
            </div>

        </form>
    </div>
    <!-- script start -->
    <script type="text/javascript">
        $(function(){
      gameList();
      advteruserId();
      groupList();
    });

    //获取游戏
    function gameList(){
        var _html = '<option value="0">请选择游戏</option>';
        $.post("<?php echo U('Ajax/getAgent');?>",{gameType:2},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
            });
            $('#agents').html(_html);
            $('#agents').comboSelect();
        });
    }

    //获取广告商
    function advteruserId(){
        var _html = '';
        $.post("<?php echo U('Ajax/adv_company');?>",'',function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.company_name+"</option>";
            });
            $('#advteruserId').html(_html);
            $('#advteruserId').comboSelect();
        });
    }

    //获取推广组
    function groupList(){
        var _html = '<option value="0">请选择推广组</option>';
        $.post("<?php echo U('Ajax/getEventGroup');?>",'',function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.groupName+"</option>";
            });
            $('#events_groupId').html(_html);
            $('#events_groupId').comboSelect();
        });
    }

    BUI.use(['bui/overlay','bui/form'],function(Overlay,Form){
    
      var form = new Form.HForm({
        srcNode : '#J_Form'
      }).render();
 
      var dialog = new Overlay.Dialog({
            title:'新增-推广活动',
            width:500,
            height:300,
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
</body>