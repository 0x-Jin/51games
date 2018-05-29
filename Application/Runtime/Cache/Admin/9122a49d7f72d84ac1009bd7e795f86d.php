<?php if (!defined('THINK_PATH')) exit();?><body>
<style type="text/css">
    input{border-radius: 4px;}
</style>
<div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Game/gameAdd');?>">

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">游戏分类：</label>
                <div class="controls">
                    <input name="gameName" type="text" data-rules="{required:true}" class="input-normal control-text">
                </div>
            </div>
        </div>

        <!-- <div class="row">
            <div class="control-group span8">
                <label class="control-label">所属部门：</label>
                <div class="controls">
                    <select name="department">
                        <?php if(is_array($partment)): $i = 0; $__LIST__ = $partment;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["partment_id"]); ?>"><?php echo ($val["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div> -->

        <div class="row">
            <div class="control-group span15">
                <label class="control-label">回调地址：</label>
                <div class="controls">
                    <input name="callbackUrl" type="text" style="width: 250px;" class="input-normal control-text">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">游戏币单位：</label>
                <div class="controls">
                    <input name="unit" type="text" class="input-normal control-text">
                </div>
            </div>
        </div>


        <div class="row">
            <div class="control-group span8">
                <label class="control-label">比率：</label>
                <div class="controls">
                    <input name="ratio" type="text" class="input-normal control-text">
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