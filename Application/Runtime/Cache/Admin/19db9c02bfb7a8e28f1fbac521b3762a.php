<?php if (!defined('THINK_PATH')) exit();?><style>
    tfoot .bui-grid-cell-text{text-align: center;}
    .btn-default {height:25px;}
    .filter-option {margin-top: -4px;}
    .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
    .helpTipWrap{
        width: 250px;
        margin: -10px 0px 0px -340px;
        padding:10px;
        position: absolute;
        z-index: 9999;
        background-color: #fff;
        border: 1px solid #00a9e8;
    }
    .combo-dropdown{
        z-index: 1999;
    }
    .helpTipWrap:after {
        position: absolute;
        display: block;
        width: 0;
        height: 0;
        border-color: transparent transparent transparent #00a9e8;
        border-style: solid;
        border-width: 5px;
        bottom: 100%;
        left: initial;
        content: '';
        right: -4%;
        top: 50%;
        transform: translateY(-50%);
    }
</style>
<div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" style="height: 400px; overflow-y: auto; overflow-x: hidden;" action="<?php echo U('Game/patchAdd');?>">
        <div class="row">
            <div class="control-group span10">
                <label class="control-label">选择游戏：</label>
                <div class="controls">
                    <select name="game_id" id="game_id" class="selectpicker">
                        <option value="">无</option>
                        <?php if(is_array($game)): $i = 0; $__LIST__ = $game;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["gameName"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span10">
                <label class="control-label">选择渠道：</label>
                <div class="controls">
                    <select name="channel_id" id="channel_id" class="selectpicker">
                        <option value="">无</option>
                        <?php if(is_array($channel)): $i = 0; $__LIST__ = $channel;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["channelName"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">渠道号：</label>
                <div class="controls">
                    <input name="agent" type="text" value="" class="input-normal control-text">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">设备类型：</label>
                <div class="controls">
                    <select name="type" id="type">
                        <option value="1">安卓</option>
                        <option value="2">IOS</option>
                        <option value="0">其他</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">SDK版本：</label>
                <div class="controls">
                    <input name="ver" type="text" value="" class="input-normal control-text">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span15">
                <label class="control-label">补丁版本：</label>
                <div class="controls">
                    <input name="patchVer" type="text" value="" class="input-normal control-text">&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: red">整型，不能加小数点</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span15">
                <label class="control-label">开启时间：</label>
                <div class="controls">
                    <input type="text" id="startTime" value="<?php echo date('Y-m-d H:00:00');?>" class="calendar calendar-time" name="startTime"/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: red">存储时会自动取整点</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span15">
                <label class="control-label">关闭时间：</label>
                <div class="controls">
                    <input type="text" id="endTime" value="<?php echo date('Y-m-d H:00:00', strtotime('+1 year'));?>" class="calendar calendar-time" name="endTime"/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: red">存储时会自动取整点</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">补丁状态：</label>
                <div class="controls">
                    <input type="radio" name="status" value="0" checked />&nbsp;&nbsp;<img src="/static/admin/img/toggle_enabled.gif">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="status" value="1" />&nbsp;&nbsp;<img src="/static/admin/img/toggle_disabled.gif">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span15">
                <label class="control-label">下载地址：</label>
                <div class="controls">
                    <input name="path" type="text" value="" style="width: 250px;" class="input-normal control-text">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span15">
                <label class="control-label">备注：</label>
                <div class="controls">
                    <input name="ext" type="text" value="" style="width: 250px;" class="input-normal control-text">
                </div>
            </div>
        </div>
    </form>
</div>

<!-- script start -->
<script type="text/javascript">
    $(".selectpicker").selectpicker({
        liveSearchPlaceholder: "搜索关键字",
        noneSelectedText: "",
        width: "150",
        liveSearch: true,
        actionsBox: true
    });

    BUI.use(["bui/overlay", "bui/form"], function (Overlay, Form) {
        var form = new Form.HForm({
            srcNode: "#J_Form"
        }).render();

        var dialog = new Overlay.Dialog({
            title: "添加",
            width: 500,
            height: 450,
            //配置DOM容器的编号
            contentId: "content",
            success: function () {
                $("#J_Form").submit();
            }
        });
        dialog.show();
    });
</script>
<!-- script end -->