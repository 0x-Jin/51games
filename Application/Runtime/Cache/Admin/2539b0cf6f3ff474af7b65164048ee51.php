<?php if (!defined('THINK_PATH')) exit();?><body>
  <style type="text/css">
    input{border-radius: 4px;}
  </style>
  <div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('System/userAdd');?>" style="height: 460px; overflow-y: auto; overflow-x: hidden;">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">管理员账号：</label>
            <div class="controls">
              <input name="name" id="username" type="text" data-rules="{required:true}" class="input-normal control-text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">真实姓名：</label>
            <div class="controls">
              <input name="real" id="real" type="text" data-rules="{required:true}" class="input-normal control-text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">密码：</label>
            <div class="controls">
              <input name="password" type="password" data-rules="{required:true}" placeholder="密码由字符开头、数字、字母或!@#$%组成" class="input-normal control-text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">确认密码：</label>
            <div class="controls">
              <input name="repassword" type="password" data-rules="{required:true}" placeholder="密码由字符开头、数字、字母或!@#$%组成" class="input-normal control-text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">所属角色：</label>
            <div class="controls">
                <select name="manager_id" id="manager_id" class="input-normal">
                  <?php echo ($role_list); ?>
                </select>
            </div>
          </div>
        </div>
        
        <div class="row"  style="display: none;" id="game_main">
          <div class="control-group span10">
            <label class="control-label">游戏名称：</label>
            <div class="controls" id="agent_contain">
                <select id="game_id" name="game_id[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">
                    
                </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span15">
            <label class="control-label">所属部门：</label>
            <div class="controls control-row4">
              <select name="partment" id="partment">
                <option value="0">全部</option>
                <?php if(is_array($partment)): $i = 0; $__LIST__ = $partment;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["partment_id"]); ?>"><?php echo ($val["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row" style="height:170px;">
          <div class="control-group span15">
            <label class="control-label">可查看的负责人：</label>
            <div class="controls control-row4">
              <select name="principal_id[]" id="principal_id" multiple="multiple" style="height:150px;">
                <option value="0">全部</option>
                <?php foreach($aprincipals as $k=>$v){?>
                <option value="<?php echo $v['id']?>"><?php echo $v['principal_name']?></option>
                <?php }?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
            <div class="control-group span15">
                <label class="control-label">绑定的后台账号：</label>
                <div>
                    <select data-actions-box="true" data-live-search="true" name="backstage_account_id[]" id="backstage_account_id" multiple="multiple" style="width: 280px;">
                        <option value="0">无</option>
                        <?php if(is_array($backstage)): $i = 0; $__LIST__ = $backstage;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["backstage_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div>

      </form>
  </div>
    
 
 
<!-- script start --> 
<script type="text/javascript">
  //获取游戏列表
  function getAgentByGame(){
      var _html = '';
        $.post("<?php echo U('Ajax/getGameList');?>",{all:0},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.gameName+"</option>";
            });
            $('#game_id').html(_html);
            $('.selectpicker').selectpicker('refresh');
        });
  }

  $(function(){
      $('#backstage_account_id').selectpicker({
          selectAllText: '全选',
          deselectAllText: '不选',
          liveSearchPlaceholder: '搜索关键字',
          noneSelectedText: '',
          multipleSeparator: ',',
          size: 8,
          liveSearch: true,
          actionsBox: true
      });
      $('#backstage_account_id').selectpicker('refresh');
    //部门负责人
    $('#partment').change(function(){
      var _html = '<option value="0">全部</option>';
      $.post('<?php echo U("Ajax/departmentPrincipals");?>',{departmentId:$(this).val()},function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.name+"</option>";
        });
        $('#principal_id').html(_html);
      });
    });

    $('#manager_id').change(function() {
      var val = $(this).val();
      if(val == 16){
        $('#game_main').show();
      }else{
        $('#game_main').hide();
      }
    });

    getAgentByGame();
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

  BUI.use(['bui/overlay', 'bui/form'], function(Overlay, Form) {

    var form = new Form.HForm({
      srcNode: '#J_Form'
    }).render();

    var dialog = new Overlay.Dialog({
      title: '新增-账号',
      width: 600,
      height: 520,
      //配置DOM容器的编号
      contentId: 'content',
      success: function() {
        var password = $('input[name=password]').val();
        var repassword = $('input[name=repassword]').val();
        var username = $('#username').val();
        var real = $('#real').val();
        if (username == '') {
          alert('账号不能为空');
          return false;
        }
        if (real == '') {
          alert('真实姓名不能为空');
          return false;
        }
        if (password != '' || repassword != '') {
          if (password != repassword) {
            alert('新密码和确认密码不一致');
            return false;
          }
          var patten = /^[a-zA-Z]{1}[\w!@#$%]{5,16}$/;
          if (!patten.test(password) || !patten.test(repassword)) {
            alert('新密码格式有误，请检查');
            return false;
          }
        } else if (password == '' || repassword == '') {
          alert('密码不能为空');
          return false;
        }
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