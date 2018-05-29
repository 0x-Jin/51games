<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>订单列表</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
</head>

<style>
  tfoot .bui-grid-cell-text{text-align: center;}
</style>

<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label" style="width: 80px;">用户标识符：</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="userCode" name="userCode" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">用户账号：</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="userName" name="userName" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">角色名称：</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="roleName" name="roleName" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">订单号：</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="orderId" name="orderId" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 80px;">渠道订单号：</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="tranId" name="tranId" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 80px;">游戏订单号：</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="billNo" name="billNo" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">游戏名称：</label>
                    <div class="controls">
                        <select name="game_id" id="game_id"></select>
                    </div>
                </div>

                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">渠道号：</label>
                    <div class="controls">
                        <input type="text" class="control-text" id="agent" name="agent" style="width: 100px;">
                    </div>
                </div>

                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">订单状态：</label>
                    <div class="controls">
                        <select id="status" name="status" style="width: 100px;">
                            <option value="0">全部</option>
                            <option value="1">待充值</option>
                            <option value="2">未发货</option>
                            <option value="3">已完成</option>
                            <option value="4">充值成功</option>
                        </select>
                    </div>
                </div>

                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">支付类型：</label>
                    <div class="controls">
                        <select id="payType" name="payType" style="width: 100px;">
                            <option value="0">--全部--</option>
                            <option value="1">支付宝</option>
                            <option value="2">微信</option>
                            <option value="3">银联</option>
                            <option value="4">苹果</option>
                        </select>
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 80px;">充值区服ID：</label>
                    <div class="controls">
                        <input type="text" name="serverId" class="input-small control-text" />
                    </div>
                </div>

                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">订单类型：</label>
                    <div class="controls">
                        <select id="orderType" name="orderType" style="width: 100px;">
                            <option value="0">--全部--</option>
                            <option value="1">正式订单</option>
                            <option value="2">测试订单</option>
                        </select>
                    </div>
                </div>

                <div class="control-group span10">
                    <label class="control-label" style="width: 60px;">下单时间：</label>
                    <div class="controls">
                        <input type="text" class="calendar calendar-time" id="startDate" name="startDate" value="<?php echo date('Y-m-d 00:00:00');?>"><span> - </span><input type="text" class="calendar calendar-time" id="endDate" name="endDate" value="<?php echo date('Y-m-d 00:00:00',strtotime(date('Y-m-d').' +1 day'));?>">
                    </div>
                </div>

                <div class="control-group span5">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">
                            搜索
                        </button>
                    </div>
                    <div class="controls">
                        <button class="button button-info" id="export" type="button">
                            导出
                        </button>
                    </div>
                </div>

            </div>
        </form>
        <form action='<?php echo U("Data/order");?>' id="subfm" method="post">
            <input name="userCode"  type="hidden" value="" />
            <input name="userName"  type="hidden" value="" />
            <input name="roleName"  type="hidden" value="" />
            <input name="orderId"   type="hidden" value="" />
            <input name="tranId"    type="hidden" value="" />
            <input name="billNo"    type="hidden" value="" />
            <input name="game_id"   type="hidden" value="" />
            <input name="agent"     type="hidden" value="" />
            <input name="status"    type="hidden" value="" />
            <input name="orderType" type="hidden" value="" />
            <input name="payType"   type="hidden" value="" />
            <input name="startDate" type="hidden" value="" />
            <input name="endDate"   type="hidden" value="" />
            <input name="export"    type="hidden" value="1"/>
        </form>
    </div>
    <div class="search-grid-container span25">
        <div id="grid"></div>
    </div>

</div>

<!-- 弹窗 -->
<div id="content" class="hide">

</div>

<script type="text/javascript">
    BUI.use('common/page');
</script>

<script type="text/javascript">
    $(function () {
        $('#export').click(function(){
            $("#subfm input[name=userCode]").val($("#userCode").val());
            $("#subfm input[name=userName]").val($("#userName").val());
            $("#subfm input[name=roleName]").val($("#roleName").val());
            $("#subfm input[name=orderId]").val($("#orderId").val());
            $("#subfm input[name=tranId]").val($('#tranId').val());
            $("#subfm input[name=billNo]").val($("#billNo").val());
            $("#subfm input[name=game_id]").val($("#game_id").val());
            $("#subfm input[name=agent]").val($("#agent").val());
            $("#subfm input[name=status]").val($("#status").val());
            $("#subfm input[name=orderType]").val($("#orderType").val());
            $("#subfm input[name=payType]").val($("#payType").val());
            $("#subfm input[name=startDate]").val($("#startDate").val());
            $("#subfm input[name=endDate]").val($("#endDate").val());
            
            $('#subfm').submit();
        });
        gameLists();
    });

    //金额格式化
    function fmoney(s, n) {
      n = n > 0 && n <= 20 ? n : 2;
      s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
      var l = s.split(".")[0].split("").reverse(),
        r = s.split(".")[1];
      t = "";
      for (i = 0; i < l.length; i++) {
        t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
      }
      return t.split("").reverse().join("") + "." + r;
    }

    //获取游戏
    function gameLists(){
        var _html = '';
        $.post("<?php echo U('Ajax/getGameList');?>",{all:1},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.gameName+"</option>";
            });
            $('#game_id').html(_html);
            $('#game_id').comboSelect();
        });
    }

    //编辑
    function orderInfo(id,obj){
        $(".bui-dialog:not(.bui-message)").remove();
        $('.mark').show();
        $('.spinner').show(); 
        if(!id) return false;
        $.get("<?php echo U('Data/orderInfo');?>",{orderId:id},function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide(); 
        });
    }

    //编辑
    function orderSupplement(id, obj){
        $(".bui-dialog:not(.bui-message)").remove();
        if(!id) {
            showMsg("warning", "ID缺失！");
        } else {
            $(".mark").show();
            $(".spinner").show();
            $.get("<?php echo U('Data/orderSupplement');?>", {orderId: id}, function (ret) {
                if (ret.status == 1) {
                    $("#content").html(ret.html).show();
                } else {
                    showMsg("warning", ret.msg);
                }
                $(".mark").hide();
                $(".spinner").hide();
            });
        }
    }

    //显示提示
    function showMsg(type, msg) {
        BUI.Message.Show({
            msg: msg,
            icon: type,
            buttons: [],
            autoHide: true,
            autoHideDelay: 2000
        });
    }

    BUI.use('common/search',function (Search) {

        editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        });
        columns = [
            {title:'用户标识符',dataIndex:'userCode',width:180,elCls:'center'},
            {title:'用户账号',dataIndex:'userName',width:180,elCls:'center'},
            {title:'渠道号',dataIndex:'agent',width:100,elCls:'center'},
            {title:'包名称',dataIndex:'agentName',width:150,elCls:'center'},
            {title:'游戏名称',dataIndex:'gameName',width:100,elCls:'center'},
            {title:'区服名称',dataIndex:'serverName',width:100,elCls:'center'},
            {title:'渠道名称',dataIndex:'channelName',width:80,elCls:'center'},
            {title:'充值金额',dataIndex:'amount',width:100,elCls:'center',summary: true},
            {title:'商品名称',dataIndex:'subject',width:60,elCls:'center'},
            {title:'订单状态',dataIndex:'status',width:60,elCls:'center'},
            {title:'支付方式',dataIndex:'payTypeName',width:60,elCls:'center'},
//            {title:'订单号',dataIndex:'orderId',width:180,elCls:'center'},
//            {title:'游戏订单号',dataIndex:'billNo',width:250,elCls:'center'},
            {title:'渠道订单号',dataIndex:'tranId',width:180,elCls:'center'},
            {title:'订单类型',dataIndex:'orderTypeName',width:60,elCls:'center'},
            {title:'下单时间',dataIndex:'create',width:130,elCls:'center'},
            {title:'注册时间',dataIndex:'regTime',width:130,elCls:'center'},
            {title:'充值时间',dataIndex:'payment',width:130,elCls:'center'},
            {title:'操作',dataIndex:'opt',width:80,elCls:'center'}
        ];
        store = Search.createStore('<?php echo U("Data/order");?>',{
            proxy : {
                save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method : 'POST'
            },
            autoSync : true //保存数据后，自动更新
        });
        gridCfg = Search.createGridCfg(columns,{
            plugins : [editing,BUI.Grid.Plugins.Summary,BUI.Grid.Plugins.AutoFit,BUI.Grid.Plugins.ColumnResize] // 插件形式引入多选表格
        });

        var  search = new Search({
            store : store,
            gridCfg : gridCfg
        }),
        grid = search.get('grid');
    });
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