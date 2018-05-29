<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>计划消耗</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/static/admin/css/combo.select.css" type="text/css">
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
</head>
<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span7">
                    <label class="control-label" style="width: 60px;">日期:</label>
                    <div class="controls">
                        <input type="text" id="startDate" value='<?php echo date("Y-m-d");?>' class="calendar" name="startDate"/> - <input type="text" id="endDate" value='<?php echo date("Y-m-d");?>' class="calendar" name="endDate"/>
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">主体：</label>
                    <div class="controls">
                        <select name="mainbody" id="mainbody">
                            <option value="">全部</option>
                            <?php if(is_array($mainbody)): $i = 0; $__LIST__ = $mainbody;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["mainBody"]); ?>"><?php echo ($val["mainBody"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">代理：</label>
                    <div class="controls">
                        <select name="proxyName" id="proxy">
                            <option value="">全部</option>
                            <?php if(is_array($proxy)): $i = 0; $__LIST__ = $proxy;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["proxyName"]); ?>"><?php echo ($val["proxyName"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">渠道：</label>
                    <div class="controls">
                        <select name="companyName" id="companyName">
                            <option value="">全部</option>
                            <?php if(is_array($advteruser)): $i = 0; $__LIST__ = $advteruser;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["company_name"]); ?>"><?php echo ($val["company_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">账号：</label>
                    <div class="controls">
                        <input type="text" class="input-normal" name="account" id="account" />
                    </div>
                </div>
                <div class="control-group span6">
                    <div class="controls">
                        <button type="button" id="btnSearch" class="button button-primary">搜索</button>
                        <button type="button" id="btnCreate" class="button button-info">录入</button>
                        <button type="button" id="btnExport" class="button button-warning">导出</button>
                    </div>
                </div>
            </div>
        </form>
        <br />
        <form method="post" action='<?php echo U("AdvterData/financeCost");?>' id="subfm">
            <input name="startDate" value="" type="hidden">
            <input name="endDate" value="" type="hidden">
            <input name="account" value="" type="hidden">
            <input name="companyName" value="" type="hidden">
            <input type="hidden" name="export" value=1 />
        </form>
    </div>
    <input type="button" hidden id="edit_key">
    <div class="search-grid-container span48">
        <div id="grid"></div>
    </div>
</div>
<!-- 弹窗 -->
<div id="content" class="hide"></div>
<script type="text/javascript">
    $(function() {
        $("#proxy").comboSelect();
        $("#mainbody").comboSelect();
        $("#companyName").comboSelect();
    });
    var _bui = BUI.use(["common/search", "bui/calendar"], function (Search, Calendar) {
        var editing = new BUI.Grid.Plugins.DialogEditing({
            contentId: "content", //设置隐藏的Dialog内容
            autoSave: true, //添加数据或者修改数据时，自动保存
            triggerCls: "btn-edit"
        }),
        columns = [
            {title:"日期", dataIndex:"date", width:100, elCls:"center"},
            {title:"主体", dataIndex:"mainbody", width:150, elCls:"center"},
            {title:"代理", dataIndex:"proxyName", width:200, elCls:"center"},
            {title:"渠道", dataIndex:"companyName", width:150, elCls:"center"},
            {title:"账号", dataIndex:"account", width:150, elCls:"center"},
            {title:"游戏", dataIndex:"gameName", width:100, elCls:"center"},
            {title:"母包", dataIndex:"agentName", width:150, elCls:"center"},
            {title:"消耗", dataIndex:"cost", width:80, elCls:"center", summary: true, renderer : function (value, obj) {
                return "<span id='cost_"+obj.id+"'>"+value+"</span>";
            }},
            {title:"折返", dataIndex:"rebateTypeName", width:60, elCls:"center"},
            {title:"比率", dataIndex:"rebateName", width:60, elCls:"center"},
            {title:"实际消耗", dataIndex:"realCost", width:80, elCls:"center", summary: true, renderer : function (value, obj) {
                return "<span id='realCost_"+obj.id+"'>"+value+"</span>";
            }},
            {title:"审核", dataIndex:"status", width:100, elCls:"center"},
            {title:"审核时间", dataIndex:"examineTime", width:130, elCls:"center"},
            {title:"操作", dataIndex:"opt", width:80, elCls:"center"}
        ],
        store = Search.createStore('<?php echo U("AdvterData/financeCost");?>', {
            proxy: {
                save: "",
//                    save: '<?php echo U("AdvterData/examineCost");?>'
                    //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                method: "POST"
            },
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            plugins: [editing, BUI.Grid.Plugins.AutoFit, BUI.Grid.Plugins.Summary] // 插件形式引入多选表格
        }),
        search = new Search({
            store: store,
            gridCfg: gridCfg
        }),
        grid = search.get("grid"),
        datepicker = new Calendar.DatePicker({
            trigger: "#date",
            autoRender: true
        });

        //监听事件，删除一条记录
        grid.on("cellclick", function(ev) {
            var sender = $(ev.domTarget); //点击的Dom
            if(sender.hasClass("btn-del")){
                var _id = sender.attr("data-id");
                var _date = sender.attr("data-date");
                var _account = sender.attr("data-account");
                BUI.Message.Confirm("是否确定删除账号“"+_account+"”"+_date+"的消耗？", function(){
                    $.post('<?php echo U("AdvterData/financeCostDelete");?>', {id: _id}, function(ret){
                        if (ret.Result) {
                            BUI.Message.Alert(ret.Msg, "success");
                            search.load();
                        } else {
                            BUI.Message.Alert(ret.Msg, "error");
                        }
                    });
                }, "question");
            }
        });

        $("#edit_key").on("click", function (ev) {
            search.load();
        })
    });
    //录入
    $("#btnCreate").click(function(){
        $(".bui-dialog:not(.bui-message)").remove();
        $(".mark").show();
        $(".spinner").show();
        $.get('<?php echo U("AdvterData/financeCostAdd");?>', "", function(ret){
            if (ret.Result) {
                $("#content").html(ret.Html);
                $("#content").show();
            } else {
                BUI.Message.Alert(ret.Msg, "error");
            }
            $(".mark").hide();
            $(".spinner").hide();
        });
    });
    //导出
    $("#btnExport").click(function(){
        $("#subfm input[name=startDate]").val($("#startDate").val());
        $("#subfm input[name=endDate]").val($("#endDate").val());
        $("#subfm input[name=account]").val($("#account").val());
        $("#subfm input[name=companyName]").val($("#companyName").val());
        $("#subfm").submit();
    });

    //审核
    function doExamine(id, account, date) {
        BUI.Message.Confirm("是否确定审核通过账号“"+account+"”"+date+"的消耗？", function(){
            $.post('<?php echo U("AdvterData/examineCost");?>', {id: id}, function(ret){
                if (ret.Result) {
                    BUI.Message.Alert(ret.Msg, "success");
                    if (ret.Code == 1) {
                        $("#examine_"+id).html("<a href='javascript:;' onclick='doExamine("+id+",\""+account+"\",\""+date+"\")'><span style='color:blue;'>投放审核</span></a>");
                        $("#time_"+id).html(ret.Time);
                        $("#opt_"+id).html("<a href='javascript:;' onclick='doEdit("+id+")'>编辑</a>&nbsp;<a href='javascript:;' class='btn-del' data-id='"+id+"' data-account='"+account+"' data-date='"+date+"'>删除</a>");
                    } else if (ret.Code == 2) {
                        $("#examine_"+id).html("<span style='color:green;'>财务审核</span>");
                        $("#time_"+id).html(ret.Time);
                        $("#opt_"+id).html("（无）");
                    }
                } else {
                    BUI.Message.Alert(ret.Msg, "error");
                }
            });
        }, "question");
    }

    function doEdit(id) {
        $(".bui-dialog:not(.bui-message)").remove();
        $(".mark").show();
        $(".spinner").show();
        if(!id) return false;
        $.get("<?php echo U('AdvterData/financeCostEdit');?>", {id:id}, function(ret){
            if (ret.Result) {
                $("#content").html(ret.Html);
                $("#content").show();
            } else {
                BUI.Message.Alert(ret.Msg, "error");
            }
            $(".mark").hide();
            $(".spinner").hide();
        });
    }

    function formatDate(time) {
        var now = new Date(time*1000);
        var year = now.getYear();
        var month = now.getMonth()+1;
        var date = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        var second = now.getSeconds();
        return "20"+year+"-"+month+"-"+date+" "+hour+":"+minute+":"+second;
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