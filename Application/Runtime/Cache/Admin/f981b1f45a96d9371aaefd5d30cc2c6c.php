<?php if (!defined('THINK_PATH')) exit();?><body>
  <style type="text/css">
    input{border-radius: 4px;}
  </style>
  <div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Advter/edit');?>">
        <input type="hidden" value="<?php echo I('id',0,'trim');?>" name="id"/>
        <input type="hidden" value="template" name="table"/>
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">模板名称：</label>
            <div class="controls">
                <input name="tpl_name" value="<?php echo ($info["tpl_name"]); ?>" class="input-normal control-text" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">模板内容称：</label>
            <div class="controls">
              <textarea name="tpl_text" style="width:775px;height:500px;"><?php echo htmlspecialchars($info['tpl_text']); ?></textarea>
            </div>
          </div>
        </div>
      </form>
  </div>
 
<!-- script start --> 
    <script type="text/javascript">
        BUI.use(['bui/overlay','bui/form'],function(Overlay,Form){
    
      var form = new Form.HForm({
        srcNode : '#J_Form'
      }).render();
 
      var dialog = new Overlay.Dialog({
            title:'修改模板',
            width:850,
            height:620,
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