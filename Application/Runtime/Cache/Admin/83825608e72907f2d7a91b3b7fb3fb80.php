<?php if (!defined('THINK_PATH')) exit();?><body>
  <style type="text/css">
    input{border-radius: 4px;}
  </style>
  <div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Advter/add');?>">
        <input type="hidden" value="<?php echo I('table','','trim');?>" name="table"/>
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">主体名称：</label>
            <div class="controls">
              <input name="mainBody" type="text"  data-rules="{required:true}" class="input-normal control-text">
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
            title:'添加主体',
            width:350,
            height:80,
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