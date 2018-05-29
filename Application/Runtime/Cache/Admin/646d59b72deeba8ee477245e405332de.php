<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            推广活动组管理
        </title>
        <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
        <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/bui-min2.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
        <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
        <script src="/static/admin/js/bootstrap/jquery.min.js">
        </script>
        <script src="/static/admin/js/bootstrap/bootstrap.min.js">
        </script>
        <script src="/static/admin/js/bootstrap/bootstrap-select.js">
        </script>
        <script src="/static/admin/js/jquery.combo.select.js" type="text/javascript">
        </script>
        <script src="/static/admin/js/bui.js" type="text/javascript">
        </script>
        <script src="/static/admin/js/config.js" type="text/javascript">
        </script>
        <script src="/static/admin/js/echart/echarts.min.js" type="text/javascript">
        </script>
    </head>
    <style>
        tfoot .bui-grid-cell-text{text-align: center;}
      .btn-default {height:25px;}
      .filter-option {margin-top: -4px;}
      .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
     
      .combo-dropdown{
        z-index: 1999;
      }

      .copy_that{
        width: 50px;
        height: 100%;
        color: rgb(51, 102, 204);
        cursor: pointer;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        margin: 0 auto;
      }
      
    </style>
    <body>
        <!-- 搜索区 -->
        <div class="container">
            <div class="row">
                <form class="form-horizontal span48" id="searchForm" method="post">
                    <div class="row">
                        <div class="control-group span7">
                            <label class="control-label" >
                                推广活动组名称：
                            </label>
                            <div class="controls">
                                <input type="text" name="groupName" class="input-normal"/>
                            </div>
                        </div>
                        <div class="control-group span7">
                            <label class="control-label" style="width: 80px;">
                                创建人：
                            </label>
                            <div class="controls">
                                <input type="text" name="creater" class="input-normal"/>
                            </div>
                        </div>
                        <div class="control-group span8">
                            <div class="controls">
                                <button class="button button-primary" id="btnSearch" type="button">
                                    搜索
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="search-grid-container span25">
                <div id="grid">
                </div>
            </div>
        </div>
        <!-- 弹窗 -->
        <div class="hide" id="content">
        </div>
        <script type="text/javascript">
            BUI.use('common/page');
        </script>
<script type="text/javascript">
  //编辑
  function groupEdit(id,obj){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('IOS/edit');?>",{table:'events_group',id:id,tpl:'groupEdit'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();
    });
  }

  //配置
  function groupConfig(id,obj){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('IOS/edit');?>",{table:'events_group',id:id,tpl:'groupConfig'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();
    });
  }

  var num = 0;

  BUI.use('common/search',function (Search) {
    
      editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
      }),
      columns = [
          {title:'ID',dataIndex:'id',width:100,elCls:'center',sortable : false},
          {title:'推广活动组名',dataIndex:'groupName',width:200,elCls:'center',sortable : false},
          {title:'创建人',dataIndex:'creater',width:180,elCls:'center',sortable : false},
          {title:'创建时间',dataIndex:'createTime',width:200,elCls:'center',sortable : false}, 
          {title:'操作',dataIndex:'opt',width:300,elCls:'center',sortable : false}
        ],
      store = Search.createStore('<?php echo U("IOS/groupList");?>',{
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
            {text : '<i class="icon-plus"></i>新建推广活动组',btnCls : 'button button-small opt-btn',handler:addFunction}
          ]
        },
        plugins : [editing,BUI.Grid.Plugins.AutoFit,BUI.Grid.Plugins.ColumnResize] // 插件形式引入多选表格
      });

    var  search = new Search({
        store : store,
        gridCfg : gridCfg
      }),
      grid = search.get('grid');

    function addFunction(){
      $('.bui-dialog').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("IOS/add");?>',{table:'events_group',tpl:'groupAdd'},function(ret){
        $('#content').html(ret._html);
        $('#content').show();
        $('.mark').hide();
        $('.spinner').hide();
      });
    }

    //删除操作
    function delFunction(){
      var selections = grid.getSelection();
      delItems(selections);
    }

    function delItems(items){
      var ids = [];
      BUI.each(items,function(item){
        ids.push(item.id);
      });

      if(ids.length){
        BUI.Message.Confirm('确认要删除选中的记录么？',function(){
          store.save('remove',{ids : ids});
        },'question');
      }
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