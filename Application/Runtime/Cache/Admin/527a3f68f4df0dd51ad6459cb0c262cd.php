<?php if (!defined('THINK_PATH')) exit();?><body>
  <style type="text/css">
    input{border-radius: 4px;}
  </style>
  <div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Advter/materialTypeAdd');?>">

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">素材分类名称：</label>
            <div class="controls">
              <input name="mtype_name" type="text" data-rules="{required:true}" class="input-normal control-text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">状态：</label>
            <div class="controls">
              <select  name="status">
                <option value="1">开启</option>
                <option value="0">关闭</option>
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
            title:'添加分类',
            width:500,
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