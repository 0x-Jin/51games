<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            广告账号列表
        </title>
        <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
        <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
        <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
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
                <form class="form-horizontal span48" id="searchForm">
                    <input name="table" type="hidden" value="advter_account">
                        <div class="row">
                            <div class="control-group span8">
                                <label class="control-label">
                                    广告账号ID：
                                </label>
                                <div class="controls">
                                    <input class="control-text" name="id" type="text">
                                    </input>
                                </div>
                            </div>
                            <div class="control-group span8">
                                <label class="control-label">
                                    广告账号名称：
                                </label>
                                <div class="controls">
                                    <input class="control-text" name="account" type="text">
                                    </input>
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
                    </input>
                </form>
            </div>
            <div class="search-grid-container">
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
  function advterAccountEdit(id,obj){
    $('.bui-dialog:not(.bui-message)').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('Advter/edit');?>",{table:'advter_account',id:id,tpl:'advterAccountEdit'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();    
    });
  }

  BUI.use('common/search',function (Search) {
    
    // var enumObj = {"1":"禁用","0":"正常"},
      editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
      }),
      columns = [
          {title:'广告账号ID',dataIndex:'id',width:50,elCls:'center'},
          {title:'广告账号名称',dataIndex:'account',width:200,elCls:'center'},
          {title:'所属部门',dataIndex:'department',width:100,elCls:'center'},
          {title:'所属广告商',dataIndex:'advterUser',width:200,elCls:'center'},
          {title:'所属代理商',dataIndex:'proxy',width:300,elCls:'center'},
          {title:'状态',dataIndex:'status',width:50,elCls:'center',renderer : function (value,obj) {
              if(value == 1){
                  //开启
                  return '<img data-tdtype="toggle" data-field="status" class="status_btn"  data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
              }else if(value == 0){
                  //关闭
                  return '<img data-tdtype="toggle" data-field="status" class="status_btn"  data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
              }
          }},
          {title:'监控',dataIndex:'controlStatus',width:50,elCls:'center',renderer : function (value,obj) {
              if(value == 1){
                  //开启
                  return '<img data-tdtype="toggle" data-field="status" class="status_btn"  data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
              }else if(value == 0){
                  //关闭
                  return '<img data-tdtype="toggle" data-field="status" class="status_btn"  data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
              }
          }},
          {title:'创建时间',dataIndex:'createTime',width:150,elCls:'center'},
          {title:'操作',dataIndex:'opt',width:100,elCls:'center'}
        ],
      store = Search.createStore('<?php echo U("Advter/advterAccountList");?>',{
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
            {text : '<i class="icon-plus"></i>新增广告账号',btnCls: 'button button-small opt-btn', handler:addFunction},
            {text : '<i class="icon-plus"></i>批量修改返点',btnCls: 'button button-small', handler:rebateFunction}
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
      /*var newData = {isNew : true}; //标志是新增加的记录
      editing.add(newData,'name'); //添加记录后，直接编辑*/
      $('.bui-dialog:not(.bui-message)').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("Advter/add");?>',{table:'advter_account',tpl:'advterAccountAdd'},function(ret){
        $('#content').html(ret._html);
        $('#content').show();
        $('.mark').hide();
        $('.spinner').hide();
      });
    }

    function rebateFunction() {
        $('.bui-dialog:not(.bui-message)').remove();
        $('.mark').show();
        $('.spinner').show();
        $.get('<?php echo U("Advter/rebateEdit");?>', {}, function(ret) {
            $('.mark').hide();
            $('.spinner').hide();
            if (ret.status == 1) {
                $('#content').html(ret._html).show();
            } else {
                BUI.Message.Alert(ret._msg, "", "info");
            }
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
        <body>
        </body>
    </body>
</html>