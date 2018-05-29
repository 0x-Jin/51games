<?php if (!defined('THINK_PATH')) exit();?><div class="demo-content" style="overflow:hidden;height:620px;">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('AdvterData/financeCostAdd');?>" enctype="multipart/form-data" >
        <div class="row">
            <div class="control-group span14">
                <label class="control-label">录入文件：</label>
                <div class="controls">
                    <input type="file" name="costFile" id="costFile"><span style="color: red;">支持Excel格式为：'xls', 'csv', 'xlsx'</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span14">
                <label class="control-label">文件模板：</label>
                <div class="controls">
                    <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/Uploads/financeCost.xlsx">financeCost.xlsx</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span14">
                <div class="controls">
                    <p style="color: red">&nbsp;&nbsp;&nbsp;&nbsp;温馨提示：第一行不会写入数据库，请不要删除第一行，日期必须为<b style="font-size: 20px;">“2017-09-01”</b>格式，不能为“2017/09/01”，IOS的话子包号填入母包号，有活动ID则填入ID，没有则留空</p>
                    <p style="color: red">&nbsp;&nbsp;&nbsp;&nbsp;温馨提示：第一行不会写入数据库，请不要删除第一行，日期必须为<b style="font-size: 20px;">“2017-09-01”</b>格式，不能为“2017/09/01”，IOS的话子包号填入母包号，有活动ID则填入ID，没有则留空</p>
                    <p style="color: red">&nbsp;&nbsp;&nbsp;&nbsp;温馨提示：第一行不会写入数据库，请不要删除第一行，日期必须为<b style="font-size: 20px;">“2017-09-01”</b>格式，不能为“2017/09/01”，IOS的话子包号填入母包号，有活动ID则填入ID，没有则留空</p>
                    <p style="position: relative; left: 300px"><b>示例：</b>计划消耗列表.xls</p>
                    <div style="border: 1px solid #d5dfe8; width: 700px; overflow: auto; position: relative;">
                        <table width="100%" cellspacing="0">
                            <tbody>
                            <tr>
                                <td align="center">日期</td>
                                <td align="center">渠道</td>
                                <td align="center">账号</td>
                                <td align="center">子包号</td>
                                <td align="center">活动ID</td>
                                <td align="center">消耗</td>
                            </tr>
                            <tr>
                                <td align="center">2017-09-01</td>
                                <td align="center">UC</td>
                                <td align="center">账号B</td>
                                <td align="center">qyj001</td>
                                <td align="center">11</td>
                                <td align="center">100</td>
                            </tr>
                            <tr>
                                <td align="center">2017-09-02</td>
                                <td align="center">百度</td>
                                <td align="center">账号D</td>
                                <td align="center">qyjAND001</td>
                                <td align="center"></td>
                                <td align="center">200</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- script start -->
<script type="text/javascript">
    BUI.use(["bui/overlay", "bui/form"], function(Overlay, Form) {
        var form = new Form.HForm({
            srcNode: "#J_Form"
        }).render();
        var dialog = new Overlay.Dialog({
            title: "消耗导入",
            width: 800,
            height: 400,
            //配置DOM容器的编号
            contentId: "content",
            success: function() {
                $("#J_Form").submit();
            }
        });
        dialog.show();
    });
</script>
<!-- script end -->