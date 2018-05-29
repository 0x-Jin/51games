<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>账户列表</title>
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap.min.css">
      <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
      <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
      <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/static/admin/css/bootstrap/bootstrap-select.css">
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css" />

    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    
    <style>
      tfoot .bui-grid-cell-text{text-align: center;}
      .btn-default {height:25px;}
      .filter-option {margin-top: -4px;}
      .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
    </style>
    
</head>
 <body>
  <!-- 搜索区 -->
  <div class="container">
    <div class="row">
      <form id="searchForm" class="form-horizontal span48">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">账户：</label>
            <div class="controls">
              <input type="text" class="control-text" name="name">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">真实姓名：</label>
            <div class="controls">
              <input type="text" class="control-text" name="real">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">账户ID：</label>
            <div class="controls">
              <input type="text" class="control-text" name="id">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">账户类型：</label>
            <div class="controls">
              <select name="user_type">
                <option value="0">全部</option>
                <option value="1">我方账户</option>
                <option value="2">广告商账户</option>
                <option value="3">CP账户</option>
              </select>
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
  function set_status(status,id,obj){
    if(status == 0){
      //关闭
      $.post("<?php echo U('System/userEdit');?>",{status:1,id:id},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_disabled.gif',onclick:'set_status(1,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }else if(status == 1){
      //开启
      $.post("<?php echo U('System/userEdit');?>",{status:0,id:id},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_enabled.gif',onclick:'set_status(0,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }
    
  }

  //编辑
  function userEdit(id,obj){
    $('.bui-dialog:not(.bui-message)').remove();
    if(!id) return false;
    var manager_id = $(obj).attr('manager_id');
    $.get("<?php echo U('System/userEdit');?>",{manager_id:manager_id,id:id},function(ret){
      $('#content').html(ret._html);
      $('#content').show();    
    });
  }

  //清除缓存
  function clearCache(id,obj){
    $('.bui-dialog:not(.bui-message)').remove();
    if(!id) return false;
    $.post("<?php echo U('System/clearCache');?>",{id:id},function(ret){
        if(ret.status == 1){
          showMsg('success',ret.info);
        }else{
          showMsg('success',ret.info);
        }   
    });
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
    
    // var enumObj = {"1":"禁用","0":"正常"},
      editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
      }),
      columns = [
          {title:'ID',dataIndex:'id',width:100,elCls:'center'},
          {title:'账户',dataIndex:'name',width:100,elCls:'center'},
          {title:'真实姓名',dataIndex:'real',width:100,elCls:'center'},
          {title:'所属角色',dataIndex:'role_name',width:100,elCls:'center'},
          {title:'所属部门',dataIndex:'department',width:100,elCls:'center'},
          {title:'最后登陆时间',dataIndex:'lastLogin',width:200,elCls:'center'},
          {title:'最后登陆IP',dataIndex:'lastIP',width:200,elCls:'center'},
          {title:'状态',dataIndex:'status',width:100,elCls:'center',/*renderer:BUI.Grid.Format.enumRenderer(enumObj),*/renderer : function (value,obj) {
              if(value == 0){
                //正常
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_status('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
              }else if(value == 1){
                //禁止
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_status('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
              }
            }
          },
          {title:'操作',dataIndex:'opt',width:300,elCls:'center'}
        ],
      store = Search.createStore('<?php echo U("System/user");?>',{
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
            {text : '<i class="icon-plus"></i>新增账号',btnCls : 'button button-small opt-btn',handler:addFunction}
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
      $('.bui-dialog').remove();
      $.get('<?php echo U("System/userAdd");?>','',function(ret){
        $('#content').html(ret._html);
        $('#content').show();
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

<body>
</html>