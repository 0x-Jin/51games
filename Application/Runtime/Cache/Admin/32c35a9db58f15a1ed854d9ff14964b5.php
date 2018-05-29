<?php if (!defined('THINK_PATH')) exit();?><body>
    <style type="text/css">
        input{border-radius: 4px;}
    </style>
    <div class="demo-content">
        <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
        <form action="<?php echo U('IOS/edit');?>" class="form-horizontal" id="J_Form" method="post">
            <input name="table" type="hidden" value="events_group"/>
            <input type="hidden" value="<?php echo I('id',0,'trim');?>" name="id"/>

            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        推广活动组名称：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" value="<?php echo ($info["groupName"]); ?>" data-rules="{required:true}" name="groupName" type="text">
                        </input>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        BUI.use(['bui/overlay','bui/form'],function(Overlay,Form){
        
          var form = new Form.HForm({
            srcNode : '#J_Form'
          }).render();
        
          var dialog = new Overlay.Dialog({
                title:'编辑-推广活动组',
                width:500,
                height:100,
                //配置DOM容器的编号
                contentId:'content',
                success:function () {
                  $('#J_Form').submit();
                }
              });
            dialog.show();
          });
    </script>
</body>