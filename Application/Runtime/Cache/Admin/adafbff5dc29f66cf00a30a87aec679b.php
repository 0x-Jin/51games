<?php if (!defined('THINK_PATH')) exit();?><div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Gift/giftAdd');?>">
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">礼包名称：</label>
                <div class="controls">
                    <input type="text" name="gift" data-rules="{required:true}" class="input-normal control-text" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">礼包内容：</label>
                <div class="controls">
                    <input type="text" name="content" class="input-normal control-text" />
                </div>
            </div>
        </div>
        <!--<div class="row">-->
            <!--<div class="control-group span8">-->
                <!--<label class="control-label">用户限制：</label>-->
                <!--<div class="controls">-->
                    <!--<select name="type">-->
                        <!--<option value="1">限制</option>-->
                        <!--<option value="0">不限</option>-->
                    <!--</select>-->
                <!--</div>-->
            <!--</div>-->
        <!--</div>-->
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">开始时间：</label>
                <div class="controls">
                    <input type="text" id="startTime" class="calendar calendar-time" name="startTime" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">结束时间：</label>
                <div class="controls">
                    <input type="text" id="endTime" class="calendar calendar-time" name="endTime" />
                </div>
            </div>
        </div>
    </form>
</div>
<!-- script start -->
<script type="text/javascript">
    BUI.use(["bui/overlay", "bui/form", "bui/calendar"], function (Overlay, Form, Calendar){
        var form = new Form.HForm({
            srcNode: "#J_Form"
        }).render();
        var datepicker1 = new Calendar.DatePicker({
            trigger: "#startTime",
            dateMask: "yyyy-mm-dd HH:00:00",
            showTime: true,
            autoRender: true
        });
        var datepicker2 = new Calendar.DatePicker({
            trigger: "#endTime",
            dateMask: "yyyy-mm-dd HH:00:00",
            showTime: true,
            autoRender: true
        });
        var dialog = new Overlay.Dialog({
            title: "新增礼包",
            width: 350,
            height: 200,
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