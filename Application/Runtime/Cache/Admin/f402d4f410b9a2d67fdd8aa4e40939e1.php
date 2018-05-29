<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            推广活动管理
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
                            <label class="control-label" style="width: 80px;">
                                游戏名称：
                            </label>
                            <div class="controls">
                                <select id="agent" name="agent">
                                </select>
                            </div>
                        </div>

                        <div class="control-group span7">
                            <label class="control-label" style="width: 80px;">
                                渠道商：
                            </label>
                            <div class="controls">
                                <select id="advteruser_id" name="advteruser_id">
                                </select>
                            </div>
                        </div>

                        <div class="control-group span7">
                            <label class="control-label" >
                                推广活动组：
                            </label>
                            <div class="controls">
                                <select data-rules="{required:true}" id="groupId" name="groupId">
                                </select>
                            </div>
                        </div>

                        <div class="control-group span7">
                            <label class="control-label" >
                                推广活动名称：
                            </label>
                            <div class="controls">
                                <input type="text" name="events_name" class="input-normal"/>
                            </div>
                        </div>

                        <div class="control-group span7">
                            <label class="control-label" >
                                推广活动ID：
                            </label>
                            <div class="controls">
                                <input type="text" name="id" class="input-normal"/>
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

            <div style="color:red;margin-left:10px;">PS：目前可以用的渠道商有：今日头条，UC头条，百度信息流，爱奇艺，广点通，微信，快手，多盟，百度移动，vungle，unityAds，360渠道CPC，芒果TV，Xmob</div>

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
    $(function () {
        gameLists();
        advteruser_id();
        groupList();
    });

    
    //获取游戏
    function gameLists(){
        var _html = '<option value="0">--全部--</option>';
        $.post("<?php echo U('Ajax/getAgent');?>",{gameType:2},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
            });
            $('#agent').html(_html);
            $('#agent').comboSelect();
        });
    }

    //获取广告商
    function advteruser_id(){
        var _html = '';
        $.post("<?php echo U('Ajax/adv_company');?>",{all:1},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.company_name+"</option>";
            });
            $('#advteruser_id').html(_html);
            $('#advteruser_id').comboSelect();
        });
    }

    //获取推广组
    function groupList(){
        var _html = '<option value="0">请选择推广组</option>';
        $.post("<?php echo U('Ajax/getEventGroup');?>",'',function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.groupName+"</option>";
            });
            $('#groupId').html(_html);
            $('#groupId').comboSelect();
        });
    }

  //编辑
  function eventsEdit(id,obj){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('IOS/edit');?>",{table:'events',id:id,tpl:'eventsEdit'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();
    });
  }

  //配置
  function eventsConfig(id,obj){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('IOS/edit');?>",{table:'events',id:id,tpl:'eventsConfig'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();
    });
  }

  //停用、启用
  function eventsStop(id,status){
    $('.bui-dialog').remove();
    
    if(!id) return false;
    var info = '停用';
    if(status == 0){
        info = '停用';
    }else if(status == 1){
        info = '启用';
    }

    if(confirm('确定'+info+'？')){
        $('.mark').show();
        $('.spinner').show();
        $.get("<?php echo U('IOS/eventsStop');?>",{id:id,status:status},function(ret){
          alert(ret.info);
          if(ret.status == 1){
            window.location.reload();
          }
          $('.mark').hide();
          $('.spinner').hide();
        });
    }
  }

  //复制
  function copyUrl(id)
  {
    $('#'+id).select();
    document.execCommand('Copy');
    alert('复制成功');
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
          {title:'推广活动名称',dataIndex:'events_name',width:200,elCls:'center',sortable : false},
          {title:'推广活动组',dataIndex:'groupName',width:200,elCls:'center',sortable : false},
          {title:'游戏',dataIndex:'gameName',width:180,elCls:'center',sortable : false},
          {title:'包号',dataIndex:'agent',width:100,elCls:'center',sortable : false},
          {title:'渠道商',dataIndex:'advterUser',width:180,elCls:'center',sortable : false},
          {title:'监控链接',dataIndex:'monitor_link',width:80,elCls:'center',sortable : false,renderer : function (value,obj) {
                  if(value != ''){
                    num += 1;
                    return '<span onclick=copyUrl("t'+num+'") class="copy_that" title="点击复制">点击复制&nbsp;&nbsp;&nbsp;&nbsp;<textarea style="width:0px;height:0px;" id="t'+num+'">'+value+'</textarea></span>';
                  }
              }
          },
          {title:'回调类型',dataIndex:'callback',width:80,elCls:'center',sortable : false},
          {title:'创建时间',dataIndex:'createTime',width:200,elCls:'center',sortable : false},
          {title:'操作',dataIndex:'opt',width:300,elCls:'center',sortable : false}
        ],
      store = Search.createStore('<?php echo U("IOS/eventsList");?>',{
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
            {text : '<i class="icon-plus"></i>新建推广活动',btnCls : 'button button-small opt-btn',handler:addFunction}
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
      /*var newData = {isNew : true}; //标志是新增加的记录
      editing.add(newData,'name'); //添加记录后，直接编辑*/
      $('.bui-dialog').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("IOS/add");?>',{table:'events',tpl:'eventsAdd'},function(ret){
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
    </body>
</html>