<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>操作日志</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/static/admin/css/combo.select.css" type="text/css">
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
          <div class="control-group span8">
            <label class="control-label">操作人姓名：</label>
            <div class="controls">
              <input name="author" type="text" class="input-normal control-text">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">操作类型：</label>
            <div class="controls">
              <select name="type">
                <option value="0">全部</option>
                <option value="1">登录</option>
                <option value="2">删除</option>
                <option value="3">修改</option>
                <option value="4">新增</option>
              </select>
            </div>
          </div>

          <div class="control-group span8">
              <label class="control-label" style="width: 60px;">日志日期：</label>
              <div class="controls">
                  <input type="text" class="calendar" name="startDate" value="<?php echo date('Y-m-d');?>"><span> - </span><input type="text" class="calendar" name="endDate" value="<?php echo date('Y-m-d');?>">
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

  //详情
  function detail(id,obj){
    $('.bui-dialog').remove();
    if(!id) return false;
    $.get("<?php echo U('System/edit');?>",{id:id,table:'operation_log',tpl:'operationDetail'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();    
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
          {title:'控制器',dataIndex:'controller',width:100,elCls:'center'},
          {title:'方法',dataIndex:'action',width:100,elCls:'center'},
          // {title:'参数',dataIndex:'params',width:500,elCls:'center'},
          {title:'操作类型',dataIndex:'type',width:100,elCls:'center'},
          {title:'操作记录',dataIndex:'record',width:800,elCls:'center',renderer : function (value,obj) {
                return '<div style="overflow:hidden; margin:auto; width:750px; white-space:nowrap; text-overflow:ellipsis; word-break:keep-all">'+value+'</div>';
            }
          },
          {title:'操作人',dataIndex:'author',width:100,elCls:'center'},
          {title:'IP',dataIndex:'ip',width:130,elCls:'center'},
          {title:'创建时间',dataIndex:'create_time',width:300,elCls:'center'},
          {title:'操作',dataIndex:'opt',width:300,elCls:'center'}
        ],
      store = Search.createStore('<?php echo U("System/operationList");?>?table=operation_log',{
        proxy : {
          save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
            // removeUrl : '<?php echo U("System/delete");?>?table=operation_log'
          },
          method : 'POST'
        },
        autoSync : true //保存数据后，自动更新
      }),
      gridCfg = Search.createGridCfg(columns,{
        tbar : {
          items : [
            // {text : '<i class="icon-plus"></i>新增负责人',btnCls : 'button button-small opt-btn',handler:addFunction},
            // {text : '<i class="icon-remove"></i>删除广告',btnCls : 'button button-small opt-btn',handler : delFunction}
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
      editing.add(newData,'name'); //添加记录后，直接编辑*/
      $('.bui-dialog').remove();
      $.get('<?php echo U("System/add");?>',{table:'principal',tpl:'principalAdd'},function(ret){
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
        BUI.Message.Confirm('确认要删除选中的记录么？',function(ret){
          console.log(ret);
          store.save('remove',{id : id});
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