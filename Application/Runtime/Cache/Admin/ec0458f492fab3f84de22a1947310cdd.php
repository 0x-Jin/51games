<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>游戏分类</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
</head>
<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <input type="hidden" name="table" value="<?php echo I('table');?>" />
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">游戏ID：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="id">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label">游戏分类：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="gameName">
                    </div>
                </div>

                <div class="control-group span8">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                    </div>
                </div>

            </div>
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
    //编辑
    function gameEdit(id,obj){
        $('.bui-dialog:not(.bui-message)').remove();
        $('.mark').show();
        $('.spinner').show();
        if(!id) return false;
        $.get("<?php echo U('Game/gameEdit');?>",{id:id},function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    //游戏渠道信息
    function gameAgent(id,obj){
        $('.bui-dialog:not(.bui-message)').remove();
        if(!id) return false;
        $.get("<?php echo U('Game/gameAgent');?>",{id:id},function(ret){
            if(ret.status == 0) {alert('该游戏没有对应渠道信息'); return false;}
            $('#content').html(ret._html);
            $('#content').show();
        });
    }

    //商品
    function goodsEdit(id,obj){
        $('.bui-dialog:not(.bui-message)').remove();
        $('.mark').show();
        $('.spinner').show();
        if(!id) return false;
        $.get("<?php echo U('Game/goods');?>",{id:id},function(ret){
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
        }),
            columns = [
                {title:'游戏ID',dataIndex:'id',width:60,elCls:'center'},
                {title:'游戏分类',dataIndex:'gameName',width:200,elCls:'center'},
                {title:'游戏密钥',dataIndex:'gameKey',width:300,elCls:'center'},
                {title:'支付密钥',dataIndex:'payKey',width:300,elCls:'center'},
                {title:'游戏币单位',dataIndex:'unit',width:80,elCls:'center'},
                {title:'比率',dataIndex:'ratio',width:60,elCls:'center'},
                {title:'登陆状态',dataIndex:'loginStatus',width:60,elCls:'center',/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer : function (value,obj) {
                    if(value == 0){
                        //正常
                        return '<span style="color: #3c8b3c;">开启登陆</span>';
                    }else if(value == 1){
                        //禁止
                        return '<span style="color: #991F1F;">关闭登陆</span>';
                    }else if(value == 2){
                        //禁止
                        return '<span style="color: #00AAFF;">关闭新增</span>';
                    }
                }
                },
                {title:'充值状态',dataIndex:'payStatus',width:60,elCls:'center',/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer : function (value,obj) {
                    if(value == 0){
                        //正常
                        return '<span style="color: #3c8b3c;">开启充值</span>';
                    }else if(value == 1){
                        //禁止
                        return '<span style="color: #991F1F;">关闭充值</span>';
                    }else if(value == 2){
                        //禁止
                        return '<span style="color: #00AAFF;">切充值</span>';
                    }
                }
                },
                {title:'创建时间',dataIndex:'createTime',width:150,elCls:'center'},
                {title:'操作',dataIndex:'opt',width:80,elCls:'center'}
            ],
            store = Search.createStore('<?php echo U("Game/index");?>',{
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
                        {text : '<i class="icon-plus"></i>新增游戏',btnCls : 'button button-small opt-btn',handler:addFunction}
                    ]
                },
                plugins : [editing,BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
            });

        var  search = new Search({
                store : store,
                gridCfg : gridCfg
            }),
            grid = search.get('grid');

        function addFunction(){
            $('.bui-dialog:not(.bui-message)').remove();
            $('.mark').show();
            $('.spinner').show();
            $.get('<?php echo U("Game/gameAdd");?>','',function(ret){
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

<body>
</html>