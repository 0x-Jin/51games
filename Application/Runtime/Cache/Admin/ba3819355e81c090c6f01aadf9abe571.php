<?php if (!defined('THINK_PATH')) exit();?><body>
    <style type="text/css">
        input{border-radius: 4px;}
    </style>
    <div class="demo-content">
        <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
        <form action="<?php echo U('IOS/edit');?>" class="form-horizontal" id="J_Form" method="post">
            <input name="table" type="hidden" value="events_group"/>
            <input type="hidden" value="<?php echo I('id',0,'trim');?>" name="id"/>
            <input type="hidden" value="<?php echo ($info["groupName"]); ?>" name="groupName">
            <input type="hidden" value="groupConfig" name="gconfig">

            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        苹果应用ID：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" value="<?php echo ($info["config_appid"]); ?>" name="config_appid" type="text">
                        </input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        广告主ID：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" value="<?php echo ($info["config_advertiser_id"]); ?>" name="config_advertiser_id" type="text">
                        </input>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        sign_key：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" value="<?php echo ($info["config_sign_key"]); ?>" name="config_sign_key" type="text">
                        </input>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        encrypt_key：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" value="<?php echo ($info["config_encrypt_key"]); ?>" name="config_encrypt_key" type="text">
                        </input>
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
            title:'参数-配置',
            width:500,
            height:220,
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