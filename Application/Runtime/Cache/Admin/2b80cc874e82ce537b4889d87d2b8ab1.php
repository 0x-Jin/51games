<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>合同列表</title>
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap.min.css">
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
    <link href="/static/admin/css/viewer.min.css" rel="stylesheet"/>

    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap-select.css">

    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script src="/static/admin/js/viewer.min.js"></script>

    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script src="/static/admin/js/echart/echarts.min.js" type="text/javascript"></script>
</head>

    <style>
        tfoot .bui-grid-cell-text{text-align: center;}
        .btn-default {height:25px;}
        .filter-option {margin-top: -4px;}
        .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
        .bootstrap-select .dropdown-toggle {z-index: auto;}
        .combo-select .width170 {width: 170px;}

        .br_line {
            height:1px;
            width:255px;
            margin-left:-5px;
            /*background:#c5c5c5;*/
            overflow:hidden;
            border-bottom:1px solid #c5c5c5;
        }

        .br_span1 {
            width: 55%;
            text-align: right;
            padding-right: 5%;
            border-right:1px solid #c5c5c5;
        }

        .br_span2 {
            width: 30%;
            text-align: left;
        }
    </style>

<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span6">
                    <label class="control-label" style="width: 50px;">部门：</label>
                    <div class="controls">
                        <select name="partment" id="partment">
                            <option value="">全部</option>
                            <option value="1">发行一部</option>
                            <option value="2">发行二部</option>
                        </select>
                    </div>
                </div>

                <div class="control-group span6">
                    <label class="control-label" style="width: 50px;">跟进人：</label>
                    <div class="controls">
                        <select name="followAdmin" id="followAdmin">
                            <option value="">全部</option>
                            <?php if(is_array($admin)): $i = 0; $__LIST__ = $admin;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["real"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">是否生效：</label>
                    <div class="controls">
                        <select name="status" id="status">
                            <option value="">全部</option>
                            <option value="1">是</option>
                            <option value="2">否</option>
                            <option value="3">空号</option>
                            <option value="4">作废</option>
                        </select>
                    </div>
                </div>

                <div class="control-group span6">
                    <label class="control-label" style="width: 50px;">签订人：</label>
                    <div class="controls">
                        <select name="principalId" id="principal_id"></select>
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">游戏名称：</label>
                    <div class="controls">
                        <input type="text" class="input-normal" name="game" id="game" />
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">合同名称：</label>
                    <div class="controls">
                        <input type="text" class="input-normal" name="contract" id="contract" />
                    </div>
                </div>

                <div class="control-group span6">
                    <label class="control-label" style="width: 50px;">类别：</label>
                    <div class="controls">
                        <select name="type" id="type">
                            <option value="">全部</option>
                            <?php if(is_array($type)): $i = 0; $__LIST__ = $type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["type"]); ?>"><?php echo ($val["type"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 80px;">合同编号：</label>
                    <div class="controls">
                        <select name="contractNo" id="contractNo" style="width: 180px;">
                            <option value="">全部</option>
                            <?php if(is_array($contractNo)): $i = 0; $__LIST__ = $contractNo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["contractNo"]); ?>"><?php echo ($val["contractNo"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>

                <div class="control-group span9">
                    <label class="control-label" style="width: 120px;">（信息/签或）编号：</label>
                    <div class="controls">
                        <select name="childNo" id="childNo" style="width: 180px;">
                            <option value="">全部</option>
                            <?php if(is_array($childNo)): $i = 0; $__LIST__ = $childNo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["childNo"]); ?>"><?php echo ($val["childNo"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">签订单位：</label>
                    <div class="controls">
                        <input type="text" class="input-normal" name="company" id="company" />
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">合同排序：</label>
                    <div class="controls">
                        <select name="order">
                            <option value="DESC">编号倒序</option>
                            <option value="ASC">编号顺序</option>
                        </select>
                    </div>
                </div>

                <div class="control-group span11">
                    <label class="control-label" style="width: 60px;">显示：</label>
                    <div class="controls">
                        <select name="show[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true">
                            <option value="0" <?php if($option['0'] == '0'): ?>selected="selected"<?php endif; ?>>全部</option>
                            <option value="1" <?php if($option['1'] == '1'): ?>selected="selected"<?php endif; ?>>部门</option>
                            <option value="2" <?php if($option['2'] == '2'): ?>selected="selected"<?php endif; ?>>跟进人</option>
                            <option value="3" <?php if($option['3'] == '3'): ?>selected="selected"<?php endif; ?>>是否生效</option>
                            <option value="4" <?php if($option['4'] == '4'): ?>selected="selected"<?php endif; ?>>签订日期</option>
                            <option value="5" <?php if($option['5'] == '5'): ?>selected="selected"<?php endif; ?>>生效日期</option>
                            <option value="6" <?php if($option['6'] == '6'): ?>selected="selected"<?php endif; ?>>失效日期</option>
                            <option value="7" <?php if($option['7'] == '7'): ?>selected="selected"<?php endif; ?>>有效天数</option>
                            <option value="8" <?php if($option['8'] == '8'): ?>selected="selected"<?php endif; ?>>签订人</option>
                            <option value="9" <?php if($option['9'] == '9'): ?>selected="selected"<?php endif; ?>>类别</option>
                            <option value="10" <?php if($option['10'] == '10'): ?>selected="selected"<?php endif; ?>>游戏名称</option>
                            <option value="11" <?php if($option['11'] == '11'): ?>selected="selected"<?php endif; ?>>合同名称</option>
                            <option value="12" <?php if($option['12'] == '12'): ?>selected="selected"<?php endif; ?>>合同编号</option>
                            <option value="13" <?php if($option['13'] == '13'): ?>selected="selected"<?php endif; ?>>（信息/签或）编号</option>
                            <option value="14" <?php if($option['14'] == '14'): ?>selected="selected"<?php endif; ?>>对应的充值账号</option>
                            <option value="15" <?php if($option['15'] == '15'): ?>selected="selected"<?php endif; ?>>合同签订单位</option>
                            <option value="16" <?php if($option['16'] == '16'): ?>selected="selected"<?php endif; ?>>主要条款</option>
                            <option value="17" <?php if($option['17'] == '17'): ?>selected="selected"<?php endif; ?>>结算方式</option>
                            <option value="18" <?php if($option['18'] == '18'): ?>selected="selected"<?php endif; ?>>总金额</option>
                            <option value="19" <?php if($option['19'] == '19'): ?>selected="selected"<?php endif; ?>>已付金额</option>
                            <option value="20" <?php if($option['20'] == '20'): ?>selected="selected"<?php endif; ?>>未付金额</option>
                            <option value="21" <?php if($option['21'] == '21'): ?>selected="selected"<?php endif; ?>>票据号</option>
                            <option value="22" <?php if($option['22'] == '22'): ?>selected="selected"<?php endif; ?>>收到发票金额</option>
                            <option value="23" <?php if($option['23'] == '23'): ?>selected="selected"<?php endif; ?>>未收票据金额</option>
                            <option value="24" <?php if($option['24'] == '24'): ?>selected="selected"<?php endif; ?>>备注</option>
                            <option value="25" <?php if($option['25'] == '25'): ?>selected="selected"<?php endif; ?>>附件</option>
                            <option value="26" <?php if($option['26'] == '26'): ?>selected="selected"<?php endif; ?>>更新时间</option>
                            <option value="27" <?php if($option['27'] == '27'): ?>selected="selected"<?php endif; ?>>操作</option>
                        </select>
                    </div>
                    &nbsp;&nbsp;<button type="button" onclick="saveOption()" class="button button-success">保存至个人设置</button>
                </div>

                <div class="control-group span6">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                        <button  type="button" id="btnCreate" class="button button-info">录入</button>
                        <button  type="button" id="btnExport" class="button button-warning">导出</button>
                    </div>
                </div>

            </div>
        </form>
        <br />
        <p style="color: red">&nbsp;&nbsp;温馨提示：您可以在“显示”的搜索栏选择所需要的查看的栏目，并保存至个人设置</p>

        <form  method="post" action='<?php echo U("AdvterData/contractList");?>' id="subfm">
            <input name="partment" value="" type="hidden">
            <input name="followAdmin" value="" type="hidden">
            <input name="status" value="" type="hidden">
            <input name="principalId" value="" type="hidden">
            <input name="game" value="" type="hidden">
            <input name="contract" value="" type="hidden">
            <input name="contractNo" value="" type="hidden" />
            <input name="childNo" value="" type="hidden" />
            <input name="type" value="" type="hidden" />
            <input name="company" value="" type="hidden" />
            <input type="hidden" name="export" value=1 />
        </form>
    </div>
    <div class="search-grid-container span48">
        <div id="grid"></div>
    </div>

</div>

<!-- 弹窗 -->
<div id="content" class="hide">

</div>

<img id="imgShow" src="" class="hide">

<script type="text/javascript">
    $(function() {
        //数据加载
        principal();

        $("#type").comboSelect({
            inputClass: ""
        });
        $("#childNo").comboSelect({
            inputClass: "width170"
        });
        $("#contractNo").comboSelect({
            inputClass: "width170"
        });

        //获取负责人
        function principal() {
            var _html = '<option value=0>全部</option>';
            $.post("<?php echo U('Ajax/principals');?>", '', function(ret) {
                var ret = eval('(' + ret + ')');
                $(ret).each(function(i, v) {
                    _html += "<option value=" + v.id + ">" + v.name + "</option>";
                });
                $('#principal_id').html(_html);
                $('#principal_id').comboSelect({
                    inputClass: ""
                });
            });
        }

        $('.selectpicker').selectpicker({
            selectAllText: '全选',
            deselectAllText: '不选',
            liveSearchPlaceholder: '搜索关键字',
            noneSelectedText: '',
            multipleSeparator: ',',
            width: "180px",
            liveSearch: false,
            actionsBox: false
        });

//        doSearch();
    });
    
    function saveOption() {
        $.post("<?php echo U('Ajax/saveContractOption');?>", {option: $('.selectpicker').val()}, function(ret) {
            var ret = eval('(' + ret + ')');
            if (ret.code) {
                BUI.Message.Show({
                    msg : '保存成功',
                    icon : 'success',
                    buttons : [],
                    autoHide : true,
                    autoHideDelay : 2000
                });
            } else {
                BUI.Message.Show({
                    msg : '报存失败',
                    icon : 'warning',
                    buttons : [],
                    autoHide : true,
                    autoHideDelay : 2000
                });
            }
        });
    }

    function getTreeData(_data){
        BUI.use(['bui/extensions/treegrid'], function (TreeGrid) {
            var data    = _data;
            var show    = $('.selectpicker').val();
            var key     = 0;
            var columns = [
                {title : 'ID',dataIndex :'id', width:120,elCls:'center',sortable:false},
            ];
            for(var i in show){
                if (show[i] == 1) {
                    columns.push({title : '部门',dataIndex :'partmentName', width:60,elCls:'center',sortable:false});
                } else if (show[i] == 2) {
                    columns.push({title : '跟进人',dataIndex :'follow', width:60,elCls:'center',sortable:false});
                } else if (show[i] == 3) {
                    columns.push({title : '是否生效',dataIndex :'statusName', width:60,elCls:'center',sortable:false});
                } else if (show[i] == 4) {
                    columns.push({title : '签订日期',dataIndex :'file', width:70,elCls:'center',sortable:false});
                } else if (show[i] == 5) {
                    columns.push({title : '生效日期',dataIndex :'start', width:70,elCls:'center',sortable:false});
                } else if (show[i] == 6) {
                    columns.push({title : '失效日期',dataIndex :'end', width:70,elCls:'center',sortable:false});
                } else if (show[i] == 7) {
                    columns.push({title : '有效天数',dataIndex :'day', width:60,elCls:'center',sortable:false});
                } else if (show[i] == 8) {
                    columns.push({title : '签订人',dataIndex :'principal', width:60,elCls:'center',sortable:false});
                } else if (show[i] == 9) {
                    columns.push({title : '类别',dataIndex :'type', width:120,elCls:'center',sortable:false});
                } else if (show[i] == 10) {
                    columns.push({title : '游戏名称',dataIndex :'game', width:150,elCls:'center',sortable:false});
                } else if (show[i] == 11) {
                    columns.push({title : '合同名称',dataIndex :'contract', width:200,elCls:'center',sortable:false});
                } else if (show[i] == 12) {
                    columns.push({title : '合同编号',dataIndex :'contractNo', width:130,elCls:'center',sortable:false});
                } else if (show[i] == 13) {
                    columns.push({title : '（信息/签或）编号',dataIndex :'childNo', width:150,elCls:'center',sortable:false});
                } else if (show[i] == 14) {
                    columns.push({title : '对应的充值账号',dataIndex :'accountStr', width:250,elCls:'center',sortable:false});
                } else if (show[i] == 15) {
                    columns.push({title : '合同签订单位',dataIndex :'company', width:250,elCls:'center',sortable:false});
                } else if (show[i] == 16) {
                    columns.push({title : '主要条款',dataIndex :'infoExt', width:300,elCls:'center',sortable:false});
                } else if (show[i] == 17) {
                    columns.push({title : '结算方式',dataIndex :'payTypeName', width:60,elCls:'center',sortable:false});
                } else if (show[i] == 18) {
                    columns.push({title : '总金额',dataIndex :'amount', width:80,elCls:'center',sortable:false});
                } else if (show[i] == 19) {
                    columns.push({title : '已付金额',dataIndex :'amountTime', width:250,elCls:'center',sortable:false});
                } else if (show[i] == 20) {
                    columns.push({title : '未付金额',dataIndex :'unpaidAmount', width:80,elCls:'center',sortable:false});
                } else if (show[i] == 21) {
                    columns.push({title : '票据号',dataIndex :'receipt', width:120,elCls:'center',sortable:false});
                } else if (show[i] == 22) {
                    columns.push({title : '收到发票金额',dataIndex :'invoiceAmount', width:80,elCls:'center',sortable:false});
                } else if (show[i] == 23) {
                    columns.push({title : '未收票据金额',dataIndex :'unInvoiceAmount', width:80,elCls:'center',sortable:false});
                } else if (show[i] == 24) {
                    columns.push({title : '备注',dataIndex :'ext', width:300,elCls:'center',sortable:false});
                } else if (show[i] == 25) {
                    columns.push({title : '附件',dataIndex :'atta', width:60,elCls:'center',sortable:false});
                } else if (show[i] == 26) {
                    columns.push({title : '更新时间',dataIndex :'update', width:150,elCls:'center',sortable:false});
                } else if (show[i] == 27) {
                    columns.push({title : '操作',dataIndex :'opt', width:180,elCls:'center',sortable:false});
                } else if (show[i] == 0) {
                    key = 1;
                }
            }
            if (key == 1) {
                columns = [
                    {title : 'ID',dataIndex :'id', width:120,elCls:'center',sortable:false},
                    {title : '部门',dataIndex :'partmentName', width:60,elCls:'center',sortable:false},
                    {title : '跟进人',dataIndex :'follow', width:60,elCls:'center',sortable:false},
                    {title : '是否生效',dataIndex :'statusName', width:60,elCls:'center',sortable:false},
                    {title : '签订日期',dataIndex :'file', width:70,elCls:'center',sortable:false},
                    {title : '生效日期',dataIndex :'start', width:70,elCls:'center',sortable:false},
                    {title : '失效日期',dataIndex :'end', width:70,elCls:'center',sortable:false},
                    {title : '有效天数',dataIndex :'day', width:60,elCls:'center',sortable:false},
                    {title : '签订人',dataIndex :'principal', width:60,elCls:'center',sortable:false},
                    {title : '类别',dataIndex :'type', width:120,elCls:'center',sortable:false},
                    {title : '游戏名称',dataIndex :'game', width:150,elCls:'center',sortable:false},
                    {title : '合同名称',dataIndex :'contract', width:200,elCls:'center',sortable:false},
                    {title : '合同编号',dataIndex :'contractNo', width:130,elCls:'center',sortable:false},
                    {title : '（信息/签或）编号',dataIndex :'childNo', width:150,elCls:'center',sortable:false},
                    {title : '对应的充值账号',dataIndex :'accountStr', width:250,elCls:'center',sortable:false},
                    {title : '合同签订单位',dataIndex :'company', width:250,elCls:'center',sortable:false},
                    {title : '主要条款',dataIndex :'infoExt', width:300,elCls:'center',sortable:false},
                    {title : '结算方式',dataIndex :'payTypeName', width:60,elCls:'center',sortable:false},
                    {title : '总金额',dataIndex :'amount', width:80,elCls:'center',sortable:false},
                    {title : '已付金额',dataIndex :'amountTime', width:250,elCls:'center',sortable:false},
                    {title : '未付金额',dataIndex :'unpaidAmount', width:80,elCls:'center',sortable:false},
                    {title : '票据号',dataIndex :'receipt', width:120,elCls:'center',sortable:false},
                    {title : '收到发票金额',dataIndex :'invoiceAmount', width:80,elCls:'center',sortable:false},
                    {title : '未收票据金额',dataIndex :'unInvoiceAmount', width:80,elCls:'center',sortable:false},
                    {title : '备注',dataIndex :'ext', width:300,elCls:'center',sortable:false},
                    {title : '附件',dataIndex :'atta', width:60,elCls:'center',sortable:false},
                    {title : '更新时间',dataIndex :'update', width:150,elCls:'center',sortable:false},
                    {title : '操作',dataIndex :'opt', width:180,elCls:'center',sortable:false}
                ];
            }
            //由于这个树，不显示根节点，所以可以不指定根节点
            var tree = new TreeGrid({
                render : '#grid',
                nodes : data,
                columns : columns,
                height:520
            });

            tree.render();
            $('.mark').css('display','none');
            $('.spinner').css('display','none');
        });
    }

    $('#btnSearch').click(function(){
        doSearch();
    });

    //录入
    $('#btnCreate').click(function(){
        $('.bui-dialog').remove();
        $.get("<?php echo U('AdvterData/contractAdd');?>", "", function(ret){
            $('#content').html(ret._html);
            $('#content').show();
        });
    });

    //导出
    $('#btnExport').click(function(){
        $("#subfm input[name=partment]").val($("#partment").val());
        $("#subfm input[name=followAdmin]").val($("#followAdmin").val());
        $("#subfm input[name=status]").val($("#status").val());
        $("#subfm input[name=principalId]").val($("#principal_id").val());
        $("#subfm input[name=game]").val($("#game").val());
        $("#subfm input[name=contract]").val($('#contract').val());
        $("#subfm input[name=contractNo]").val($("#contractNo").val());
        $("#subfm input[name=childNo]").val($("#childNo").val());
        $("#subfm input[name=type]").val($("#type").val());
        $("#subfm input[name=company]").val($("#company").val());
        $('#subfm').submit();
    });

    function doSearch() {
        var _data = $('#searchForm').serialize();
        $.post('<?php echo U("AdvterData/contractList");?>',_data,function(ret){
            var ret = eval('('+ret+')');
//            if(ret.rows.length > 0){
            $('#grid').html('');
            $('.mark').css('display','block');
            $('.spinner').css('display','block');
            getTreeData(ret.rows);
//            }else{
//                alert('无搜索结果');
//                return false;
//            }
        });
    }
    
    function showExt(id) {
        $.get("<?php echo U('Ajax/getContractInfo');?>", {id: id}, function(ret){
            var ret = eval('('+ret+')');
            BUI.use('bui/overlay',function(Overlay){
                var dialog = new Overlay.Dialog({
                    title:'主要条款',
                    mask:false,
                    buttons:[],
                    bodyContent: '<div style="max-height: 500px; min-width: 500px; max-width: 1000px;">'+ret.info+'</div>'
                });
                dialog.show();
            });
        });
    }

    var viewer_k = new Viewer(document.getElementById('imgShow'), {
        url: 'data-original'
    });

    function openImg(str) {
        $("#imgShow").attr("src", str);
        viewer_k.show();
    }
    
    function addChild(id) {
        $('.bui-dialog').remove();
        $.get("<?php echo U('AdvterData/contractChildAdd');?>", {id: id}, function(ret){
            $('#content').html(ret._html);
            $('#content').show();
        });
    }
    
    function doEdit(id) {
        $('.bui-dialog').remove();
        $.get("<?php echo U('AdvterData/contractEdit');?>", {id: id}, function(ret){
            $('#content').html(ret._html);
            $('#content').show();
        });
    }

    function doInfo(id) {
        $('.bui-dialog').remove();
        $.get("<?php echo U('AdvterData/contractInfo');?>", {id: id}, function(ret){
            $('#content').html(ret._html);
            $('#content').show();
        });
    }
</script>

<style>
  .spinner {display:none; position: absolute;top: 50%; left: 50%; /* margin: 100px auto; */ width: 20px; height: 20px; position: absolute; } .container1 > div, .container2 > div, .container3 > div {width: 6px; height: 6px; background-color: #333; border-radius: 100%; position: absolute; -webkit-animation: bouncedelay 1.2s infinite ease-in-out; animation: bouncedelay 1.2s infinite ease-in-out; -webkit-animation-fill-mode: both; animation-fill-mode: both; } .spinner .spinner-container {position: absolute; width: 100%; height: 100%; } .container2 {-webkit-transform: rotateZ(45deg); transform: rotateZ(45deg); } .container3 {-webkit-transform: rotateZ(90deg); transform: rotateZ(90deg); } .circle1 { top: 0; left: 0; } .circle2 { top: 0; right: 0; } .circle3 { right: 0; bottom: 0; } .circle4 { left: 0; bottom: 0; } .container2 .circle1 {-webkit-animation-delay: -1.1s; animation-delay: -1.1s; } .container3 .circle1 {-webkit-animation-delay: -1.0s; animation-delay: -1.0s; } .container1 .circle2 {-webkit-animation-delay: -0.9s; animation-delay: -0.9s; } .container2 .circle2 {-webkit-animation-delay: -0.8s; animation-delay: -0.8s; } .container3 .circle2 {-webkit-animation-delay: -0.7s; animation-delay: -0.7s; } .container1 .circle3 {-webkit-animation-delay: -0.6s; animation-delay: -0.6s; } .container2 .circle3 {-webkit-animation-delay: -0.5s; animation-delay: -0.5s; } .container3 .circle3 {-webkit-animation-delay: -0.4s; animation-delay: -0.4s; } .container1 .circle4 {-webkit-animation-delay: -0.3s; animation-delay: -0.3s; } .container2 .circle4 {-webkit-animation-delay: -0.2s; animation-delay: -0.2s; } .container3 .circle4 {-webkit-animation-delay: -0.1s; animation-delay: -0.1s; } @-webkit-keyframes bouncedelay {0%, 80%, 100% { -webkit-transform: scale(0.0) } 40% { -webkit-transform: scale(1.0) } } @keyframes bouncedelay {0%, 80%, 100% {transform: scale(0.0); -webkit-transform: scale(0.0); } 40% {transform: scale(1.0); -webkit-transform: scale(1.0); } } 
  .mark{background-color: #fff;opacity: .5;top: 0; height: 100%; width: 100%; position: absolute;display: none;} 
</style>

<?php if(session('admin.role_id') == 17 or session('admin.role_id') == 25): ?>
    <style>
      .opt-btn,#export{display: none;}
    </style>
  <?php endif; ?>
<div class="mark"></div>
<div class="spinner">
  <div class="spinner-container container1">
    <div class="circle1"></div>
    <div class="circle2"></div>
    <div class="circle3"></div>
    <div class="circle4"></div>
  </div>
  <div class="spinner-container container2">
    <div class="circle1"></div>
    <div class="circle2"></div>
    <div class="circle3"></div>
    <div class="circle4"></div>
  </div>
  <div class="spinner-container container3">
    <div class="circle1"></div>
    <div class="circle2"></div>
    <div class="circle3"></div>
    <div class="circle4"></div>
  </div>
</div>
</body>
</html>