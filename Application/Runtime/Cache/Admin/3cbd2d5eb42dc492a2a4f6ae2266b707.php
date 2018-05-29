<?php if (!defined('THINK_PATH')) exit();?><body>
  <style type="text/css">
    input{border-radius: 4px;}
  </style>
  <div class="demo-content" style="overflow:auto;height:620px;">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Advter/edit');?>">
        <input type="hidden" value="principal" name="table"/>
        <input type="hidden" value="<?php echo I('id',0,'trim');?>" name="id"/>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">负责人名称：</label>
            <div class="controls">
                <input name="principal_rolename" data-rules="{required:true}" value="<?php echo ($info["principal_rolename"]); ?>"  class="input-normal control-text" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">负责人真名：</label>
            <div class="controls">
              <input name="principal_name" data-rules="{required:true}" value="<?php echo ($info["principal_name"]); ?>" class="input-normal control-text" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">邮箱：</label>
            <div class="controls">
              <input name="email" data-rules="{required:true}" value="<?php echo ($info["email"]); ?>" class="input-normal control-text" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">手机号：</label>
            <div class="controls">
              <input name="mobile" data-rules="{required:true}" value="<?php echo ($info["mobile"]); ?>" class="input-normal control-text" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">所属部门：</label>
            <div class="controls">
              <select name="department">
                <?php if(is_array($partment)): $i = 0; $__LIST__ = $partment;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["partment_id"]); ?>" <?php if($val['partment_id'] == $info['department']) echo 'selected';?> ><?php echo ($val["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">状态：</label>
            <div class="controls">
              <select name="status">
                <option value="1" <?php if($info["status"] == 1): ?>selected<?php endif; ?> >开启</option>
                <option value="0" <?php if($info["status"] == 0): ?>selected<?php endif; ?> >关闭</option>
              </select>
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
      title:'修改负责人',
      width:450,
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