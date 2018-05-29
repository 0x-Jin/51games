<?php if (!defined('THINK_PATH')) exit();?><body>
<style type="text/css">
    input{border-radius: 4px;}
</style>
<div class="demo-content" style="overflow-y:auto; overflow-x:hidden; height:460px;">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('AdvterData/contractAdd');?>" enctype="multipart/form-data">
        <div class="row">
            <div class="control-group span8">
                <label class="control-label">部门：</label>
                <div class="controls">
                    <select name="partment">
                        <option value="1">发行一部</option>
                        <option value="2">发行二部</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span10">
                <label class="control-label">合同签订单位：</label>
                <div class="controls">
                    <input type="text" id="company" class="input-normal" style="width: 200px;" data-rules="{required:true}" name="company" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span10">
                <label class="control-label">合同名称：</label>
                <div class="controls">
                    <input type="text" id="contract" class="input-normal" style="width: 240px;" data-rules="{required:true}" name="contract" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">合同编号：</label>
                <div class="controls">
                    <input type="text" id="contractNo" class="input-normal" data-rules="{required:true}" name="contractNo" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span10">
                <label class="control-label">游戏名称：</label>
                <div class="controls">
                    <input type="text" id="game" class="input-normal" style="width: 240px;" name="game" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">签订人：</label>
                <div class="controls">
                    <select name="principalId">
                        <?php if(is_array($principal)): $i = 0; $__LIST__ = $principal;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["principal_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span12">
                <label class="control-label">充值账号：</label>
                <div class="controls" id="account_1">
                    <input type="text" class="input-normal" name="accountA1" />&nbsp;&nbsp;金额：<input type="text" class="input-normal" name="accountM1" style="width: 80px;" />
                </div>
                <label class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="button" value="添加账号" id-name="1" class="button-info" onclick="addAccount(this)" />
                    &nbsp;&nbsp;<span style="color: red">注意，只有金额，没有输入账号的数据无效</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">跟进人：</label>
                <div class="controls">
                    <select name="followAdmin">
                        <?php if(is_array($follow)): $i = 0; $__LIST__ = $follow;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["real"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span9">
                <label class="control-label">合同类别：</label>
                <div class="controls">
                    <select id="type_select" onclick="makeType()">
                        <option value="">请选择类别</option>
                        <?php if(is_array($type)): $i = 0; $__LIST__ = $type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["type"]); ?>"><?php echo ($val["type"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                    <input type="text" id="type_input" class="input-normal" name="type" style="display: none" />
                    <input type="button" id="change_type" value="新增类别" class="button-info" onclick="changeType()" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span16" style="height: 200px;">
                <label class="control-label">主要条款：</label>
                <div class="controls">
                    <textarea name="info" id="info" cols="30" rows="5" style="width: 450px; height: 180px;"></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">签订日期：</label>
                <div class="controls">
                    <input type="text" id="fileTime" class="calendar" name="fileTime" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">是否生效：</label>
                <div class="controls">
                    <select name="status">
                        <option value="0">是</option>
                        <option value="1">否</option>
                        <option value="2">空号</option>
                        <option value="3">作废</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">生效日期：</label>
                <div class="controls">
                    <input type="text" id="startTime" class="calendar" name="startTime" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">终止日期：</label>
                <div class="controls">
                    <input type="text" id="endTime" class="calendar" name="endTime" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">结算方式：</label>
                <div class="controls">
                    <select name="payType">
                        <option value="1">日结</option>
                        <option value="2">月结</option>
                        <option value="3">预付</option>
                        <option value="4">垫付</option>
                        <option value="5">分期</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">总金额：</label>
                <div class="controls">
                    <input type="text" class="input-normal" name="amount" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span12">
                <label class="control-label">已付金额：</label>
                <div class="controls" id="payAmountTime_1">
                    <input type="text" class="input-normal" name="payAmountTimeA1" style="width: 80px;" />&nbsp;&nbsp;付款时间：<input type="text" class="calendar" name="payAmountTimeT1" id="payAmountTimeT1" />
                </div>
                <label class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="button" value="添加付款金额" id-name="1" class="button-info" onclick="addPayAmountTime(this)" />
                    &nbsp;&nbsp;<span style="color: red">注意，只有时间，没有金额的数据无效</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">票据号：</label>
                <div class="controls">
                    <input type="text" class="input-normal" name="receipt" />
                </div>
            </div>
        </div>

        <!--<div class="row">-->
            <!--<div class="control-group span8">-->
                <!--<label class="control-label">未付金额：</label>-->
                <!--<div class="controls">-->
                    <!--<input type="text" class="input-normal" name="unpaidAmount" />-->
                <!--</div>-->
            <!--</div>-->
        <!--</div>-->

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">收到发票金额：</label>
                <div class="controls">
                    <input type="text" class="input-normal" name="invoiceAmount" />
                </div>
            </div>
        </div>

        <!--<div class="row">-->
            <!--<div class="control-group span8">-->
                <!--<label class="control-label">未收票据金额：</label>-->
                <!--<div class="controls">-->
                    <!--<input type="text" class="input-normal" name="unInvoiceAmount" />-->
                <!--</div>-->
            <!--</div>-->
        <!--</div>-->

        <div class="row">
            <div class="control-group span16">
                <label class="control-label">备注：</label>
                <div class="controls">
                    <input type="text" class="input-normal" style="width: 450px;" name="ext" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="control-group span8">
                <label class="control-label">附件：</label>
                <div class="controls">
                    <input type="file" class="input-normal" name="file"/>
                </div>
            </div>
        </div>

    </form>
</div>

<!-- script start -->
<script type="text/javascript">
    BUI.use(['bui/overlay', 'bui/form', 'bui/calendar'], function(Overlay, Form, Calendar) {
        var form = new Form.HForm({
            srcNode: '#J_Form'
        }).render();

        var dialog = new Overlay.Dialog({
            title: '合同录入',
            width: 700,
            height: 520,
            //配置DOM容器的编号
            contentId: 'content',
            success: function() {
                var formData = new FormData($("#J_Form")[0]);
                $.ajax({
                    url: "<?php echo U('AdvterData/contractAdd');?>" ,
                    type: 'POST',
                    data: formData,
                    async: false,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (ret) {
                        dialog.hide();
                        var ret = eval('(' + ret + ')');
                        if (ret.code) {
                            BUI.Message.Show({
                                msg : '合同录入成功',
                                icon : 'success',
                                buttons : [],
                                autoHide : true,
                                autoHideDelay : 2000
                            });
                            doSearch();
                        } else {
                            BUI.Message.Show({
                                msg : '合同录入失败',
                                icon : 'warning',
                                buttons : [],
                                autoHide : true,
                                autoHideDelay : 2000
                            });
                        }
                    },
                    error: function () {
                        dialog.hide();
                        BUI.Message.Show({
                            msg : '合同录入失败',
                            icon : 'warning',
                            buttons : [],
                            autoHide : true,
                            autoHideDelay : 2000
                        });
                    }
                });
            }
        });

        var datepicker = new Calendar.DatePicker({
            trigger: '#costMonth',
            dateMask: 'yyyy/mm/dd',
            autoRender: true
        });

        dialog.show();
    });

    function changeType() {
        var _val = $("#change_type").val();
        if (_val == "新增类别") {
            $("#type_input").val("").show();
            $("#type_select").val("").hide();
            $("#change_type").val("选择类别");
        } else {
            $("#type_input").val("").hide();
            $("#type_select").val("").show();
            $("#change_type").val("新增类别");
        }
    }
    
    function addAccount(obj) {
        var id  = $(obj).attr("id-name");
        var nid = parseInt(id) + 1;
        $("#account_"+id).after(
            '<label class="control-label">充值账号：</label>'+
            '<div class="controls" id="account_'+nid+'">'+
                '<input type="text" class="input-normal" name="accountA'+nid+'" />'+
                '&nbsp;&nbsp;金额：'+
                '<input type="text" class="input-normal" name="accountM'+nid+'" style="width: 80px;" />'+
            '</div>'
        );
        $(obj).attr("id-name", nid);
    }
    
    function addPayAmountTime(obj) {
        var id  = $(obj).attr("id-name");
        var nid = parseInt(id) + 1;
        $("#payAmountTime_"+id).after(
            '<label class="control-label">已付金额：</label>'+
            '<div class="controls" id="payAmountTime_'+nid+'">'+
            '<input type="text" class="input-normal" name="payAmountTimeA'+nid+'" style="width: 80px;" />'+
            '&nbsp;&nbsp;付款时间：'+
            '<input type="text" class="calendar" id="payAmountTimeT'+nid+'" name="payAmountTimeT'+nid+'" />'+
            '</div>'
        );
        $(obj).attr("id-name", nid);

        BUI.use(['bui/calendar'],function (Calendar) {
            var datepicker = new Calendar.DatePicker({
                trigger: '#payAmountTimeT'+nid,
                autoRender: true
            });
        });
    }

    function makeType() {
        $("#type_input").val($("#type_select").val());
    }
</script>
<!-- script end -->
</div>
</body>
</html>