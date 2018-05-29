<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>栏目菜单</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
  <script type="text/javascript" src="/static/admin/js/bui.js"></script>
  <script type="text/javascript" src="/static/admin/js/config.js"></script>
</head>
 <body>
<div class="container">
    <div class="row">
      <form id="searchForm" class="form-horizontal span24">
        <div class="row">

        </div>
        <div class="row">
         
        </div>
      </form>
    </div>
    <div class="search-grid-container">
      <div id="grid"></div>
    </div>

  </div>
  <div id="content" class="hide">

  </div>

<script type="text/javascript">

  function columnEdit(id){
      $('.bui-dialog').remove();
      $.get('<?php echo U("Website/columnEdit");?>',{id:id},function(ret){
        $('#content').html(ret._html);
        $('#content').show();
      });
  }

  function columnDelete(id){
      $('.bui-dialog').remove();
      if(confirm("确定要删除该数据吗？")){
        $.get('<?php echo U("Website/columnDelete");?>',{id:id},function(ret){
          alert(ret.info);
          if(ret.status == 1){
            window.location.reload();
          }
        });
      }
      
  }

  //子菜单添加
  function subColumnAdd(pid){
    $('.bui-dialog').remove();
    $.get('<?php echo U("Website/columnAdd");?>',{pid:pid},function(ret){
        $('#content').html(ret._html);
        $('#content').show();
      });
  }

  BUI.use('common/search',function (Search) {
    
      editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
      }),
      columns = [
          {title:'ID',dataIndex:'id',width:80,elCls:'center'},
          {title:'栏目名称',dataIndex:'columnName',width:600},
          {title:'创建时间',dataIndex:'createTime',width:100,elCls:'center'/*,renderer:BUI.Grid.Format.dateRenderer*/},
          {title:'操作',dataIndex:'str_manage',width:200,elCls:'center'}
        ],
      store = Search.createStore('<?php echo U("Ajax/columnList");?>',{
        proxy : {
          save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
            addUrl : '<?php echo U("Website/columnAdd");?>',
            removeUrl : '<?php echo U("Website/columnDelete");?>'
          },
          method : 'POST'
        },
        autoSync : true //保存数据后，自动更新
      }),
      gridCfg = Search.createGridCfg(columns,{
        tbar : {
          items : [
            {text : '<i class="icon-plus"></i>添加栏目',btnCls : 'button button-small opt-btn',handler:addFunction},
            // {text : '<i class="icon-remove"></i>删除菜单',btnCls : 'button button-small opt-btn',handler : delFunction}
          ]
        },
        plugins : [editing,/*BUI.Grid.Plugins.CheckSelection,*/BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
      });

    var  search = new Search({
        store : store,
        gridCfg : gridCfg
      }),
      grid = search.get('grid');

    function addFunction(){
      /*var newData = {isNew : true}; //标志是新增加的记录
      editing.add(newData,'id'); //添加记录后，直接编辑*/
      $.get('<?php echo U("Website/columnAdd");?>',{pid:0},function(ret){
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
      var id = [];
      BUI.each(items,function(item){
        id.push(item.id);
      });

      if(id.length){
        BUI.Message.Confirm('确认要删除选中的记录么？',function(){
          store.save('remove',{id : id});
          alert('删除成功');
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