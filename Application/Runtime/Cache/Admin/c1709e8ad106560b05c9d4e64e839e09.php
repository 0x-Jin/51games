<?php if (!defined('THINK_PATH')) exit();?><body>
<style type="text/css">
    input{border-radius: 4px;}
</style>
<div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Game/gameEdit');?>">
        <input type="hidden" value="<?php echo ($game["id"]); ?>" name="id"/>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">游戏分类：</label>
                <div class="controls">
                    <input name="gameName" type="text" value="<?php echo ($game["gameName"]); ?>" data-rules="{required:true}" class="input-normal control-text">
                </div>
            </div>
        </div>

        <!-- <div class="row">
            <div class="control-group span8">
                <label class="control-label">所属部门：</label>
                <div class="controls">
                    <select name="department">
                        <?php if(is_array($partment)): $i = 0; $__LIST__ = $partment;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["partment_id"]); ?>" <?php if($val['partment_id'] == $game['department']) echo 'selected';?> ><?php echo ($val["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div> -->

        <div class="row">
            <div class="control-group span15">
                <label class="control-label">回调地址：</label>
                <div class="controls">
                    <input name="callbackUrl" type="text" value="<?php echo ($game["callbackUrl"]); ?>" style="width: 250px;" class="input-normal control-text">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">游戏币单位：</label>
                <div class="controls">
                    <input name="unit" type="text" value="<?php echo ($game["unit"]); ?>" class="input-normal control-text">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">比率：</label>
                <div class="controls">
                    <input name="ratio" type="text" value="<?php echo ($game["ratio"]); ?>" class="input-normal control-text">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">登陆状态：</label>
                <div class="controls">
                    <select  name="loginStatus">
                        <option value="0" <?php if($game["loginStatus"] == 0): ?>selected<?php endif; ?>>开启登陆</option>
                        <option value="1" <?php if($game["loginStatus"] == 1): ?>selected<?php endif; ?>>关闭登陆</option>
                        <option value="2" <?php if($game["loginStatus"] == 2): ?>selected<?php endif; ?>>关闭新增</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">充值状态：</label>
                <div class="controls">
                    <select  name="payStatus">
                        <option value="0" <?php if($game["payStatus"] == 0): ?>selected<?php endif; ?>>开启充值</option>
                        <option value="1" <?php if($game["payStatus"] == 1): ?>selected<?php endif; ?>>关闭充值</option>
                        <option value="2" <?php if($game["payStatus"] == 2): ?>selected<?php endif; ?>>切充值</option>
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
            title:'编辑-<?php echo ($game["gameName"]); ?>',
            width:500,
            height:340,
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