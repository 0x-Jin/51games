<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>部门日报数据统计</title>
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap.min.css">
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>

    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap-select.css">
    
    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>

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
</style>

<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" method="post" class="form-horizontal span48">
            <div class="row">

               
                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">统计日期：</label>
                    <div class="controls">
                        <input type="text" class="calendar" name="startDate" id="startDate" value="<?php echo date('Y-m-d',strtotime(date('Y-m-d').' -3 day'));?>"><span> - </span><input type="text" class="calendar" name="endDate" id="endDate" value="<?php echo date('Y-m-d');?>">
                    </div>
                </div>
                
                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">系统：</label>
                    <div class="controls">
                        <select name="os" id="os">
                            <option value="0">全部</option>
                            <option value="1">Android</option>
                            <option value="2">Ios</option>
                        </select>
                    </div>
                </div>

                <div class="control-group span5">
                    <label class="control-label" style="width: 60px;">部门：</label>
                    <div class="controls">
                        <select name="departmentId" id="departmentId" class="input-small">
                            <?php if(in_array(session('admin.role_id'),array(1,32,35))): ?><option value="0">--全部--</option>
                                <option value="1">发行一部</option>
                                <option value="2">发行二部</option>
                            <?php elseif(session('admin.partment') == 1): ?>
                                <option value="1">发行一部</option>

                            <?php elseif(session('admin.partment') == 2): ?>
                                <option value="2">发行二部</option>
                                
                            <?php else: ?>
                                <option value="-1">全部</option><?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="control-group span5">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                    </div>

                    <div class="controls">
                        <button  type="button" id="export" class="button button-info">导出</button>
                    </div>
                </div>
                   
                </div>
            </div>
        </form>
        <form  method="post" action='<?php echo U("Data/departmentDayReport");?>' id="subfm"><input name="startDate" value="" type="hidden"><input name="endDate" value="" type="hidden"><input type="hidden" name="departmentId" /><input type="hidden" name="os" /><input type="hidden" name="export" value=1 /></form>
    </div>

    <div style="color:red;margin-left:10px;">PS：部门日报数据一天更新一次</div>
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
            $("#subfm input[name=departmentId]").val($("#departmentId").val());
            $("#subfm input[name=os]").val($("#os").val());
            $("#subfm input[name=startDate]").val($("#startDate").val());
            $("#subfm input[name=endDate]").val($("#endDate").val());
            $('#subfm').submit();
        })
        
    });

    BUI.use('common/search',function (Search) {
    Summary = new BUI.Grid.Plugins.Summary(),
    editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
    }),
    columns = [
        {title : '日期',dataIndex :'dayTime', width:150,elCls:'center'},
        {title : '激活设备数',dataIndex :'newDevice', width:150,elCls:'center',summary: true},
        {title : '新增账户数',dataIndex :'newUser', width:120,elCls:'center',summary: true},
        {title : '活跃账户数',dataIndex :'actUser', width:150,elCls:'center',summary: true},
        {title : '充值金额',dataIndex :'allPay', width:100,elCls:'center',summary: true},
        {title : '充值账户数',dataIndex :'allPayUser', width:100,elCls:'center',summary: true},
        {title : '付费率',dataIndex :'payRate', width:100,elCls:'center',renderer: function(value, obj) {
            if(value > 0){
                return value+'%';
            }else{
                return 0+'%';
            }
        }},
    ],
    store = Search.createStore('<?php echo U("Data/departmentDayReport");?>', {
            proxy: {
                save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method: 'POST'
            },
            pageSize:1000,
            autoSync: true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns, {
            forceFit : true,
            plugins: [editing, Summary, BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
        });

    var search = new Search({
            store: store,
            gridCfg: gridCfg
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