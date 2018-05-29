<?php if (!defined('THINK_PATH')) exit();?><body>
<style type="text/css">
    input{border-radius: 4px;}
    .controls td {border: 1px solid #e4e4e4; line-height: 24px; padding: 4px;}
</style>
<div class="demo-content">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Game/serverImport');?>" enctype="multipart/form-data" >
        <input type="hidden" value="advter_cost" name="table"/>
        <div class="row">
            <div class="control-group span14">
                <label class="control-label">区服文件：</label>
                <div class="controls">
                    <input type="file" name="serverFile" id="serverFile"><span style="color: red;">支持Excel格式为：'xls', 'csv', 'xlsx'</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span14">
                <label class="control-label">文件模板：</label>
                <div class="controls">
                    <a href="https://<?php echo $_SERVER['SERVER_NAME'];?>/Uploads/server.xlsx">server.xlsx</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span14">
                <div class="controls">
                    <p style="color: red">&nbsp;&nbsp;&nbsp;&nbsp;温馨提示：第一行不会写入数据库，请不要删除该行，游戏名称可以填游戏ID或游戏名称，但务必不要填错，手机系统可以填“IOS”、“安卓”、“全部”或数字“1”、“2”、“0”，母包可以填母包号或“*”、如果指定母包号则手机系统会自动匹配，日期必须为“2017-09-01 00:00:00”格式</p>
                    <p style="position: relative; left: 300px">青云诀开服列表.xls</p>
                    <div style="border: 1px solid #d5dfe8; width: 700px; overflow: auto; position: relative;">
                        <table width="100%" cellspacing="0">
                            <tbody>
                            <tr>
                                <td align="center">游戏名称</td>
                                <td align="center">母包</td>
                                <td align="center">区服ID</td>
                                <td align="center">区服名称</td>
                                <td align="center">手机系统</td>
                                <td align="center">开服时间</td>
                            </tr>
                            <tr>
                                <td align="center">青云诀</td>
                                <td align="center">ceshiIOS</td>
                                <td align="center">S001</td>
                                <td align="center">青云诀1服</td>
                                <td align="center">IOS</td>
                                <td align="center">2017-09-01 00:00:00</td>
                            </tr>
                            <tr>
                                <td align="center">101</td>
                                <td align="center">*</td>
                                <td align="center">S002</td>
                                <td align="center">青云诀2服</td>
                                <td align="center">2</td>
                                <td align="center">2017-09-01 09:00:00</td>
                            </tr>
                            <tr>
                                <td align="center">青云诀</td>
                                <td align="center">*</td>
                                <td align="center">S003</td>
                                <td align="center">青云诀3服</td>
                                <td align="center">全部</td>
                                <td align="center">2017-09-01 10:00:00</td>
                            </tr>
                            <tr>
                                <td align="center">101</td>
                                <td align="center">*</td>
                                <td align="center">S004</td>
                                <td align="center">青云诀4服</td>
                                <td align="center">0</td>
                                <td align="center">2017-09-01 11:00:00</td>
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

    BUI.use(['bui/overlay', 'bui/form'], function(Overlay, Form) {
        var form = new Form.HForm({
            srcNode: '#J_Form'
        }).render();

        var dialog = new Overlay.Dialog({
            title: '区服导入',
            width: 800,
            height: 380,
            //配置DOM容器的编号
            contentId: 'content',
            success: function() {
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