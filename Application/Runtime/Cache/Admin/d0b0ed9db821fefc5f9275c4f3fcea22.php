<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ocpa控制开关</title>
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
    <!-- <span style="margin-left: 10px;color:red;">添加百度信息流OCPA配置时，应用id填入百度信息流的计划id</span> -->
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

	//编辑
	function ocpaEdit(id,obj){
	  $('.bui-dialog').remove();
	  $('.mark').show();
	  $('.spinner').show();
	  if(!id) return false;
	  $.get("<?php echo U('Assist/ocpaEdit');?>",{id:id},function(ret){
        if(ret.status == -1){
            $('.mark').hide();
            $('.spinner').hide();
            alert(ret.info);
            return false;
        }
	    $('#content').html(ret._html);
	    $('#content').show();
	    $('.mark').hide();
	    $('.spinner').hide();
	  });
	}

    BUI.use('common/search',function (Search) {
        var editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        }),
        columns = [
            {title:'账号ID',dataIndex:'advertiser_id',width:250,elCls:'center'},
            {title:'应用ID',dataIndex:'appid',width:250,elCls:'center'},
            {title:'设定报送记录数',dataIndex:'Num',width:250,elCls:'center'},
            {title:'已报送记录数',dataIndex:'reportNum',width:250,elCls:'center'},
            {title:'状态',dataIndex:'status',width:100,elCls:'center'},
            {title:'渠道商',dataIndex:'ocpaType',width:100,elCls:'center'},
            {title:'创建人',dataIndex:'creater',width:100,elCls:'center'},
            {title:'创建时间',dataIndex:'createTime',width:150,elCls:'center'},
            {title:'操作',dataIndex:'opt',width:150,elCls:'center'},
        ],
        store = Search.createStore('<?php echo U("Assist/ocpaSwitch");?>',{
            proxy : {
                save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                },
                method : 'POST'
            },
            autoSync : true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns,{
            tbar : {
                items : [
                    {text : '<i class="icon-plus"></i>新增OCPA配置',btnCls : 'button button-small opt-btn',handler:addFunction}
                ]
            },
            plugins : [] // 插件形式引入多选表格
        }),
        search = new Search({
            store : store,
            gridCfg : gridCfg
        }),
        grid = search.get('grid');

        function addFunction(){
            $('.bui-dialog:not(.bui-message)').remove();
            $('.mark').show();
            $('.spinner').show();
            $.get('<?php echo U("Assist/ocpaAdd");?>','',function(ret){
                $('#content').html(ret._html);
                $('#content').show();
                $('.mark').hide();
                $('.spinner').hide();
            });
        }
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