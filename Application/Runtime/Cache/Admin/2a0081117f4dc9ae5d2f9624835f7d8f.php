<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>财务订单列表</title>
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
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">游戏名称：</label>
                    <div class="controls">
                        <select name="game_id" id="game_id"></select>
                    </div>
                </div>
                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">部门：</label>
                    <div class="controls">
                        <select id="department" name="department" style="width: 100px;">
                            <option value="0">全部</option>
                            <option value="1">发行一部</option>
                            <option value="2">发行二部</option>
                            <!-- <option value="3">融合</option> -->
                        </select>
                    </div>
                </div>
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">支付渠道：</label>
                    <div class="controls">
                        <select id="type" name="type">
                            <option value="0">全部</option>
                            <option value="1">苹果</option>
                            <option value="2">支付宝</option>
                            <option value="3">微信</option>
                            <option value="4">银联</option>
                        </select>
                    </div>
                </div>
                <div class="control-group span10">
                    <label class="control-label" style="width: 60px;">日期：</label>
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
                        <button class="button button-info" id="orderExport" type="button">
                            导出
                        </button>
                    </div>
                </div>

            </div>
        </form>
        <form action='<?php echo U("Data/incomeOrder");?>' id="subfm" method="post">
            <input name="game_id"   type="hidden" value="" />
            <input name="agent"     type="hidden" value="" />
            <input name="type"      type="hidden" value="" />
            <input name="status"    type="hidden" value="" />
            <input name="orderType" type="hidden" value="" />
            <input name="department" type="hidden" value="" />
            <input name="startDate" type="hidden" value="" />
            <input name="endDate"   type="hidden" value="" />
            <input name="export"    type="hidden" value="1"/>
        </form>
    </div>
    <div class="search-grid-container">
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
        $('#orderExport').click(function(){
            $("#subfm input[name=game_id]").val($("#game_id").val());
            $("#subfm input[name=department]").val($("#department").val());
            $("#subfm input[name=type]").val($("#type").val());
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
                if(v.id != '104'){
                    _html += "<option value="+v.id+">"+v.gameName+"</option>";
                }
            });
            $('#game_id').html(_html);
            $('#game_id').comboSelect();
        });
    }

    //编辑
    function orderInfo(id,obj){
        $('.bui-dialog').remove();
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

    BUI.use('common/search',function (Search) {

        editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        });
        columns = [
            {title:'主体名称',dataIndex:'mainBody',width:150,elCls:'center'},
            {title:'部门',dataIndex:'department',width:150,elCls:'center'},
            {title:'游戏名称',dataIndex:'gameName',width:150,elCls:'center'},
            {title:'包名称',dataIndex:'agentName',width:200,elCls:'center'},
            {title:'渠道名称',dataIndex:'channelName',width:80,elCls:'center'},
            {title:'支付渠道',dataIndex:'payType',width:60,elCls:'center'},
            {title:'充值金额',dataIndex:'amount',width:100,elCls:'center',summary: true,
              renderer: function(value, obj) {
                return fmoney(value,2);
                // return value + '元';
              }
            },
            {title:'实收金额',dataIndex:'realAmount',width:100,elCls:'center',summary: true,
              renderer: function(value, obj) {
                return fmoney(value,2);
              }
            },
            {title:'手续费',dataIndex:'poundage',width:100,elCls:'center',summary: true,
              renderer: function(value, obj) {
                return fmoney(value,2);
              }
            },
        ];
        store = Search.createStore('<?php echo U("Data/incomeOrder");?>',{
            proxy : {
                save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method : 'POST'
            },
            pageSize:1000,
            autoSync : true //保存数据后，自动更新
        });
        gridCfg = Search.createGridCfg(columns,{
            plugins : [editing,BUI.Grid.Plugins.Summary,BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
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