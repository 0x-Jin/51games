<?php if (!defined('THINK_PATH')) exit();?><body>
<style type="text/css">
    input{border-radius: 4px;}
</style>
<div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" enctype="multipart/form-data" action="<?php echo U('Backstage/exeAdd');?>">
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">名称：</label>
                <div class="controls">
                    <input name="name" type="text" data-rules="{required:true}" class="input-normal control-text">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">版本：</label>
                <div class="controls">
                    <input name="ver" type="text" data-rules="{required:true}" class="input-normal control-text">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span10">
                <label class="control-label">MAC：</label>
                <div class="controls">
                    <input type="file" name="mac">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span10">
                <label class="control-label">WIN：</label>
                <div class="controls">
                    <input type="file" name="win">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span10">
                <label class="control-label"><span style="color: red;">提示：</span></label>
                <div class="controls">
                    <span style="color: red;">请以压缩包rar或zip的格式上传</span>
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
            width:450,
            height:250,
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