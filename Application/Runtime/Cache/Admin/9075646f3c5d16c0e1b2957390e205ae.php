<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>广告商列表</title>
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
        <input type="hidden" value="advteruser" name="table">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">广告商ID：</label>
            <div class="controls">
              <input type="text" class="control-text" name="id">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">广告商名称：</label>
            <div class="controls">
              <input type="text" class="control-text" name="company_name">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">负责人：</label>
            <select name="principal_name">
              <option value="">--全部--</option>
              <?php if(is_array($aprincipals)): $i = 0; $__LIST__ = $aprincipals;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["principal_name"]); ?>" <?php if(($val["principal_name"]) == $search['principal_name']): ?>selected<?php endif; ?>><?php echo ($val["principal_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
            </select>
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
  function set_status(status,id,obj,type){
    if(status == 1){
      //关闭
      $.post("<?php echo U('Advter/edit');?>",{status:0,id:id,table:'advteruser'},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_disabled.gif',onclick:'set_status(0,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }else if(status == 0){
      //开启
      $.post("<?php echo U('Advter/edit');?>",{status:1,id:id,table:'advteruser'},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_enabled.gif',onclick:'set_status(1,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }
    
  }

  function set_actually(status,id,obj,type){
    if(status == 0){
      //关闭
      $.post("<?php echo U('Advter/edit');?>",{is_actually:1,id:id,table:'advteruser'},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_disabled.gif',onclick:'set_actually(1,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }else if(status == 1){
      //开启
      $.post("<?php echo U('Advter/edit');?>",{is_actually:0,id:id,table:'advteruser'},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_enabled.gif',onclick:'set_actually(0,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }
    
  }


  //编辑
  function advterUserEdit(id,obj){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('Advter/edit');?>",{table:'advteruser',id:id,tpl:'advterUserEdit'},function(ret){
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
          {title:'广告商ID',dataIndex:'id',width:100,elCls:'center'},
          {title:'广告商名称',dataIndex:'company_name',width:100,elCls:'center'},
          {title:'负责人',dataIndex:'principal_name',width:100,elCls:'center'},
          {title:'安卓监控链接参数',dataIndex:'param',width:600,elCls:'center'},
          {title:'IOS监控链接参数',dataIndex:'iosParam',width:600,elCls:'center'},
          {title:'网址',dataIndex:'url',width:400,elCls:'center'},
          <?php if(session('admin.role_id') == 1): ?>{title:'广告商数据实时',dataIndex:'is_actually',width:100,elCls:'center',renderer : function (value,obj) {
              if(value == 0){
                //正常
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_actually('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
              }else if(value == 1){
                //禁止
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_actually('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
              }
            }
          },
          
          {title:'状态',dataIndex:'status',width:100,elCls:'center',renderer : function (value,obj) {
              if(value == 1){
                //正常
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_status('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
              }else if(value == 0){
                //禁止
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_status('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
              }
            }
          },
          {title:'创建时间',dataIndex:'create_time',width:200,elCls:'center'},
          {title:'操作',dataIndex:'opt',width:300,elCls:'center'}<?php endif; ?>
        ],
      store = Search.createStore('<?php echo U("Advter/advterUserList");?>',{
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
            {text : '<i class="icon-plus"></i>新增广告商',btnCls : 'button button-small opt-btn',handler:addFunction}
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
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("Advter/add");?>',{table:'advteruser',tpl:'advterUserAdd'},function(ret){
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
</html>