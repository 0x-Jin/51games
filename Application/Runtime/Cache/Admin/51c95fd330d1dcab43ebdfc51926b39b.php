<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>资讯列表</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
</head>
<body>
<!-- 搜索区 -->
<div class="container">
    <div class="row">
        <form id="searchForm" class="form-horizontal span48">
            <div class="row">
                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">资讯ID：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="id">
                    </div>
                </div>

                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">官网名称：</label>
                    <div class="controls">
                        <select name="home_id" id="home"></select>
                    </div>
                </div>

                <div class="control-group span6">
                    <label class="control-label" style="width: 60px;">文章标题：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="title">
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

    $(function () {
        homeLists();
    });

    //获取官网
    function homeLists(){
        var _html = '';
        $.post("<?php echo U('Ajax/getWebsiteHome');?>", {all:1}, function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i, v){
                _html += "<option value="+v.id+">"+v.name+"</option>";
            });
            $('#home').html(_html).comboSelect();
        });
    }

    //资讯静态化
    function statis_html(abbr,id){
        $('.mark').show();
        $('.spinner').show();
        $.get("<?php echo U('Web/read');?>",{abbr:abbr,id:id,cache:1},function(ret){
            alert(ret.info);
            $('.mark').hide();
            $('.spinner').hide();
          });
    }

    //删除
    function articleDelete(id){
        $('.bui-dialog').remove();
        if(!id) return false;
        if(confirm('确定删除该记录吗？')){
          $.post("<?php echo U('Website/articleDelete');?>",{id:id},function(ret){
            alert(ret.info);
            if(ret.status == 1){
              window.location.reload();
            }
          });
        }
    }

    BUI.use('common/search',function (Search) {
        editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        }),
        columns = [
            {title:'资讯ID',dataIndex:'id',width:60,elCls:'center'},
            {title:'资讯标题',dataIndex:'title',width:200,elCls:'center'},
            {title:'官网缩写',dataIndex:'abbr',width:100,elCls:'center',visible:false},
            {title:'官网名称',dataIndex:'homeName',width:100,elCls:'center'},
            {title:'栏目',dataIndex:'columnName',width:100,elCls:'center'},
            {title:'状态',dataIndex:'status',width:60,elCls:'center',/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer : function (value,obj) {
                if(value == 1){
                    //正常
                    return '<img src="/static/admin/img/toggle_enabled.gif">';
                }else if(value == 0){
                    //禁止
                    return '<img src="/static/admin/img/toggle_disabled.gif">';
                }
            }},
            {title:'发布时间',dataIndex:'release',width:150,elCls:'center'},
            {title:'操作',dataIndex:'opt',width:70,elCls:'center'}
        ],
        store = Search.createStore('<?php echo U("Website/article");?>',{
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
                    {text : '<i class="icon-plus"></i>新增资讯',btnCls : 'button button-small opt-btn',handler:addFunction},
                    {text : '<i class="icon-refresh"></i>批量静态',btnCls : 'button button-small opt-btn',handler : statisFunction}
                ]
            },
            plugins : [editing,BUI.Grid.Plugins.CheckSelection,BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
        });

        var  search = new Search({
            store : store,
            gridCfg : gridCfg
        }),
        grid = search.get('grid');

        function addFunction(){
            if(top.topManager){
                //打开左侧菜单中配置过的页面
                top.topManager.openPage({
                    id : "articleAdd",
                    href : "<?php echo U('Website/articleAdd');?>",
                    title : "新增资讯",
                    reload : true
                });
            }
        }

        //资讯静态化
        function statisFunction(){
            $('.mark').show();
            $('.spinner').show();
            var selections = grid.getSelection();
            console.log(selections);
            var id = createItems(selections);
            $.get("<?php echo U('Web/read');?>",{type:'batchStatic',id:id},function(ret){
                alert(ret.info);
                $('.mark').hide();
                $('.spinner').hide();
              });
        }

        function createItems(items){
            var id = [];
            BUI.each(items,function(item){
              id.push(item.id+'_'+item.abbr);
            });
            return id;
          }

          //监听事件，删除一条记录
          grid.on('cellclick',function(ev){
            var sender = $(ev.domTarget); //点击的Dom
            if(sender.hasClass('btn-del')){
              var record = ev.record;
              delItems([record]);
            }
          });
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