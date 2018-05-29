<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>渠道分类</title>
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
            <label class="control-label">渠道分类名称：</label>
            <div class="controls">
              <input type="text" class="control-text" name="channelName">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">所属广告商：</label>
            <div class="controls">
              <select name="advteruser_id" id="advteruserId"></select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">负责人：</label>
            <div class="controls">
              <select name="principal_id" id="principalId"></select>
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
$(function(){
  //数据加载
  principal();
  adv_company();
  //获取负责人
  function principal(){
    var _html = '<option value=0>--全部--</option>';
      $.post("<?php echo U('Ajax/principals');?>",'',function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.name+"</option>";
        });
        $('#principalId').html(_html);
        $('#principalId').comboSelect();
      });
  }

  //获取广告商
  function adv_company(){
      var _html = '<option value=0>--全部--</option>';
      $.post("<?php echo U('Ajax/adv_company');?>",'',function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.company_name+"</option>";
        });
        $('#advteruserId').html(_html);
        $('#advteruserId').comboSelect();
      });
  }
});

  function set_status(status,id,obj){
    if(status == 0){
      //关闭
      $.post("<?php echo U('Advter/channelTypeEdit');?>",{channelStatus:1,id:id,type:'setchannelStatus'},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_disabled.gif',onclick:'set_status(1,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }else if(status == 1){
      //开启
      $.post("<?php echo U('Advter/channelTypeEdit');?>",{channelStatus:0,id:id,type:'setchannelStatus'},function(ret){
        if(ret.status == 1){
          $(obj).attr({src:'/static/admin/img/toggle_enabled.gif',onclick:'set_status(0,'+id+',this)'});
        }else{
          alert(ret.info);
          return false;
        }
      });
    }
    
  }


  //删除
  function advListDelete(id){
    $('.bui-dialog').remove();
    if(!id) return false;
    if(confirm('确定删除该记录吗？')){
      $.post("<?php echo U('Advter/delete');?>",{id:id,table:'channel'},function(ret){
        alert(ret.info);
        if(ret.status == 1){
          window.location.reload();
        }
      });
    }
    
  }

  //编辑
  function channelTypeEdit(id){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('Advter/channelTypeEdit');?>",{id:id,table:'channel',tpl:'channelTypeEdit'},function(ret){
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
          {title:'渠道分类ID',dataIndex:'id',width:100,elCls:'center'},
          {title:'渠道类型名称',dataIndex:'channelName',width:100,elCls:'center'},
          {title:'所属广告商',dataIndex:'advteruserName',width:200,elCls:'center'},
          {title:'负责人',dataIndex:'principalName',width:100,elCls:'center'},
          {title:'状态',dataIndex:'channelStatus',width:100,elCls:'center',renderer : function (value,obj) {
              if(value == 0){
                //正常
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_status('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
              }else if(value == 1){
                //禁止
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick="set_status('+value+','+obj.id+',this)" data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
              }
            }
          },
          {title:'费率',dataIndex:'rate',width:100,elCls:'center'},
          {title:'参数一',dataIndex:'param1',width:200,elCls:'center'},
          {title:'参数二',dataIndex:'param2',width:200,elCls:'center'},
          {title:'参数三',dataIndex:'param3',width:200,elCls:'center'},
          {title:'参数四',dataIndex:'param4',width:200,elCls:'center'},
          {title:'参数五',dataIndex:'param5',width:200,elCls:'center'},
          {title:'参数六',dataIndex:'param6',width:200,elCls:'center'},
          {title:'参数七',dataIndex:'param7',width:200,elCls:'center'},
          {title:'参数八',dataIndex:'param8',width:200,elCls:'center'},
          {title:'参数九',dataIndex:'param9',width:200,elCls:'center'},
          {title:'参数十',dataIndex:'param10',width:200,elCls:'center'},
          {title:'创建时间',dataIndex:'createTime',width:200,elCls:'center'},
          {title:'操作',dataIndex:'opt',width:300,elCls:'center'}
        ],
      store = Search.createStore('<?php echo U("Advter/channelTypeList");?>?table=channel',{
        proxy : {
          save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
            removeUrl : '<?php echo U("Advter/delete");?>?table=channel'
          },
          method : 'POST'
        },
        autoSync : true //保存数据后，自动更新
      }),
      gridCfg = Search.createGridCfg(columns,{
        tbar : {
          items : [
            {text : '<i class="icon-plus"></i>新增渠道分类',btnCls : 'button button-small opt-btn',handler:addFunction},
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
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("Advter/channelTypeAdd");?>','',function(ret){
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