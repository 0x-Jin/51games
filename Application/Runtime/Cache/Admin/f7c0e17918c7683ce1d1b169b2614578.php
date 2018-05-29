<?php if (!defined('THINK_PATH')) exit();?><body>
  <style type="text/css">
    input{border-radius: 4px;}
  </style>
  <div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('System/roleAdd');?>">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">角色名称：</label>
            <div class="controls">
              <input name="name" id="name" type="text" data-rules="{required:true}" class="input-normal control-text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span15">
            <label class="control-label">角色描述：</label>
            <div class="controls  control-row4">
              <textarea name="remark" class="input-large bui-form-field" type="text" aria-disabled="false" aria-pressed="false"></textarea>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">是否启用：</label>
            <div class="controls">
              <input name="status" type="radio" value="0" checked=checked/>是
              <input name="status" type="radio" value="1" />否
            </div>
          </div>
        </div>
        
        <!-- <div class="row">
          <div class="control-group span15">
            <label class="control-label">部门：</label>
            <div class="controls control-row4">
              <textarea name="department" class="input-large bui-form-field" type="text" aria-disabled="false" aria-pressed="false"></textarea>
            </div>
          </div>
        </div> -->

      </form>
  </div>
    
 
 
<!-- script start --> 
    <script type="text/javascript">
        BUI.use(['bui/overlay','bui/form'],function(Overlay,Form){
    
      var form = new Form.HForm({
        srcNode : '#J_Form'
      }).render();
 
      var dialog = new Overlay.Dialog({
            title:'添加-角色信息',
            width:500,
            height:420,
            //配置DOM容器的编号
            contentId:'content',
            success:function () {
              var name = $('#name').val();
              if(name == ''){
                alert('角色名称不能为空');
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