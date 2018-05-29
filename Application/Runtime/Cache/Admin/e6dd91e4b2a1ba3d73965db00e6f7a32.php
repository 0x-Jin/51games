<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>广告成本</title>
    <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bui-min2.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
    <link href="/static/admin/css/easyui/themes/default/easyui.css" rel="stylesheet" type="text/css" />


    <script src="/static/admin/js/bootstrap/jquery.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/admin/js/bootstrap/bootstrap-select.js"></script>
    <script src="/static/admin/js/jquery.combo.select.js" type="text/javascript"></script>
    <script src="/static/admin/js/bui.js" type="text/javascript"></script>
    <script src="/static/admin/js/config.js" type="text/javascript"></script>

    <script type="text/javascript" src="/static/admin/js/easyui/jquery.easyui.min.js"></script>

</head>
    
        <style>
          tfoot .bui-grid-cell-text{text-align: center;}
          .btn-default {height:25px;}
          .filter-option {margin-top: -4px;}
          .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
          .combo-dropdown{
            z-index: 1999;
          }
        .bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn){width: 140px;}
        .panel,.datagrid-view2{overflow: inherit;}
        .datagrid-header .datagrid-cell{overflow: inherit;position: relative;}
        .icon-question-sign{cursor: pointer;}
        </style>
        <style>
            #typebut{
                width: 70px;
                background-color: #407686;
                border-color: #357ebd;
                color: #fff;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 4px;
                -khtml-border-radius: 10px;
                text-align: center;
                vertical-align: middle;
                border: 1px solid transparent;
                font-size: 13px;
                cursor: pointer;
                margin-bottom: 15px;
                margin-left: 25px;
                font-family: 微软雅黑;  
            }
            #typetoggle{width: 600px; display: none; margin-left: 4px; margin-bottom: 15px;}
            .butcheck {color: #428bca;padding: 4px;border: 1px solid #a9c9e2;background: #e8f5fe;}
            .checkover {width: 100%;height: 100%;overflow: auto;}
            .checklabel {padding: 5px 0;width: 110px;position: relative;text-indent:22px}
            .inputSelet,.checkscroll{margin: 0 auto;text-align: center;}
            .checklabel{margin: 0 auto;text-align: left;}
            input[type="checkbox"]{appearance: none; -webkit-appearance: none;outline: none;display:none}
            label{display:inline-block;cursor:pointer;}
            label input[type="checkbox"] + span{width:20px;height:20px;display:inline-block;position:absolute;top:4px;left:0;background:url(/static/admin/img/checkbox_01.gif)  no-repeat;background-position:0 0;}
            label input[type="checkbox"]:checked + span{background-position:0 -21px}
        </style>
    
 <body>
  <!-- 搜索区 -->
  <div class="container">
    <div class="row">
      <form id="searchForm" class="form-horizontal span48">
        <div class="row">
          <div class="control-group span10">
            <label class="control-label">日期:</label>
            <div class="controls">
              <input type="text" id="startMonth" value="<?php echo date('Y-m-d',strtotime(date('Y-m-01')));?>" class="calendar" name="startMonth" /> - <input type="text" id="endMonth" value="<?php echo date('Y-m-d');?>" class="calendar" name="endMonth" />
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">负责人：</label>
            <div class="controls">
              <select name="principal" id="principal_id">
                <?php echo ($principal_list); ?>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label" style="width: 80px;">游戏：</label>
            <div class="controls">
              <select id="game_id" class="selectpicker" data-actions-box="true" data-live-search="true" multiple="" name="game_id[]">
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label" style="width: 80px;">母包：</label>
            <div class="controls">
              <select id="agent_p" class="selectpicker" data-actions-box="true" data-live-search="true" multiple="" name="gameName[]">
              </select>
            </div>
          </div>
          
          <div class="control-group span8">
            <label class="control-label">系统：</label>
            <div class="controls">
              <select name="gameType" id="gameType">
                <?php echo ($gameType_list); ?>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">广告商：</label>
            <div class="controls">
              <select name="media" id="media">
                <option value="">全部</option>
                <?php if(is_array($media)): $i = 0; $__LIST__ = $media;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["name"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">渠道账号：</label>
            <div class="controls">
              <input type="text" name="channelAccount" id="channelAccount" class="input-normal" />
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">包编号：</label>
            <div class="controls">
              <input type="text" class="input-normal" name="agent" id="agent" />
            </div>
          </div>

          <div class="control-group span5">
            <div class="controls">
              <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
            </div>

            <div class="controls">
                <button  type="button" id="export" class="button button-info">导出</button>
            </div>
          </div>

        </div>
      </form>
      <form  method="post" action='<?php echo U("AdvterData/advCost");?>' id="subfm"><input name="table" value="advter_cost" type="hidden"><input name="startMonth" value="" type="hidden"><input name="endMonth" value="" type="hidden"><input type="hidden" name="principal" /><input type="hidden" name="game_id" /><input type="hidden" name="agent_p" /><input type="hidden" name="media" /><input type="hidden" name="channelAccount" /><input type="hidden" name="agent" /><input type="hidden" name="export" value=1 /><input type="hidden" name="gameType" value='' /></form>
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
function costEdit(id){
  $('.bui-dialog').remove();
  if(!id) return false;
  $('.mark').show();
  $('.spinner').show();
  $.get("<?php echo U('AdvterData/edit');?>",{id:id,table:'advter_cost',tpl:'costEdit'},function(ret){
    $('#content').html(ret._html);
    $('.mark').hide();
    $('.spinner').hide();
    $('#content').show();    
  });
}

//删除
function costDelete(id){
  $('.bui-dialog').remove();
  if(!id) return false;
  if(confirm('确定删除该记录吗？')){
    $.post("<?php echo U('Advter/delete');?>",{id:id,table:'advter_cost'},function(ret){
      alert(ret.info);
      if(ret.status == 1){
        loadData();
      }
    });
  }
  
}

$(function() {
  $('.selectpicker').selectpicker({
          selectAllText: '全选',
          deselectAllText: '不选',
          liveSearchPlaceholder: '搜索关键字',
          noneSelectedText: '',
          multipleSeparator: ',',
          liveSearch: true,
          actionsBox: true
      });
  loadData();
  gameLists();
  agentLists();
  $('#media').comboSelect();
  $('#export').click(function(){
      $("#subfm input[name=startMonth]").val($("#startMonth").val());
      $("#subfm input[name=endMonth]").val($("#endMonth").val());
      $("#subfm input[name=principal]").val($("#principal_id").val());
      $("#subfm input[name=game_id]").val($("#game_id").val());
      $("#subfm input[name=gameName]").val($("#agent_p").val());
      $("#subfm input[name=gameType]").val($("#gameType").val());
      $("#subfm input[name=media]").val($("#media").val());
      $("#subfm input[name=channelAccount]").val($("#channelAccount").val());
      $("#subfm input[name=agent]").val($("#agent").val());
      $('#subfm').submit();
  });

});

//金额格式化
function fmoney(s, n) {
  n = n > 0 && n <= 20 ? n : 2;
  s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
  var l = s.split(".")[0].split("").reverse(),
    r = s.split(".")[1];
  t = "";
  for (i = 0; i < l.length; i++) {
    t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
  }
  return t.split("").reverse().join("") + "." + r;
}


//获取游戏
function gameLists(){
    var _html = '';
    $.post("<?php echo U('Ajax/getGameList');?>",{},function(ret){
        _html += '<option value=0>--全部--</option>';
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.gameName+"</option>";
        });
        $('#game_id').html(_html);
        $('#game_id').selectpicker('refresh');
        $('#game_id').selectpicker('val', '--全部--');
    });
}

//获取母包
function agentLists(){
    var _html = '';
    $.post("<?php echo U('Ajax/getCostGame');?>",{},function(ret){
        _html += '<option value=0>--全部--</option>';
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.gameName+">"+v.gameName+"</option>";
        });
        $('#agent_p').html(_html);
        $('#agent_p').selectpicker('refresh');
        $('#agent_p').selectpicker('val', '--全部--');
    });
}

function loadData()
{
  $('#grid').html('');

  BUI.use(['common/search', 'bui/calendar'], function(Search,Calendar) {

    // var enumObj = {"1":"禁用","0":"正常"},
    editing = new BUI.Grid.Plugins.DialogEditing({
        contentId: 'content', //设置隐藏的Dialog内容
        autoSave: true, //添加数据或者修改数据时，自动保存
        triggerCls: 'btn-edit'
      }),
      columns = [{
        title: '日期',
        dataIndex: 'costMonth',
        width: 100,
        elCls: 'center'
      }, {
        title: '负责人',
        dataIndex: 'principalName',
        width: 100,
        elCls: 'center'
      }, {
        title: '游戏',
        dataIndex: 'gameName',
        width: 200,
        elCls: 'center'
      }, {
        title: '系统',
        dataIndex: 'gameType',
        width: 80,
        elCls: 'center'
      },{
        title: '媒体',
        dataIndex: 'media',
        width: 100,
        elCls: 'center'
      }, {
        title: '包号',
        dataIndex: 'agent',
        width: 100,
        elCls: 'center'
      }, {
        title: '渠道账号',
        dataIndex: 'channelAccount',
        width: 200,
        elCls: 'center'
      },{
        title: '推广活动名称',
        dataIndex: 'eventName',
        width: 200,
        elCls: 'center'
      }, {
        title: '支出金额',
        dataIndex: 'cost',
        summary: true,
        width: 100,
        elCls: 'center',
        renderer: function(value, obj) {
          return fmoney(value,2);
          // return value + '元';
        }
      },{
        title: '操作',
        dataIndex: 'opt',
        width: 200,
        elCls: 'center'
      }],
      store = Search.createStore('<?php echo U("AdvterData/advCost");?>?sysType=android', {
        proxy: {
          save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
            removeUrl: '<?php echo U("AdvterData/delete");?>?sysType=android'
          },
          method: 'POST'
        },
        autoSync: true //保存数据后，自动更新
      }),
      gridCfg = Search.createGridCfg(columns, {
        tbar: {
          items: [
            // {text: '<i class="icon-plus"></i>成本录入', btnCls : 'button button-small opt-btn', handler : addFunction }, 
//            {text: '<i class="icon-plus"></i>安卓成本导入', btnCls : 'button button-small opt-btn', handler : importFunction},
//            {text: '<i class="icon-plus"></i>IOS成本导入', btnCls : 'button button-small opt-btn', handler : iosimportFunction}
          ]
        },
        plugins: [editing, BUI.Grid.Plugins.Summary, /*BUI.Grid.Plugins.CheckSelection,*/ BUI.Grid.Plugins.AutoFit,BUI.Grid.Plugins.ColumnResize] // 插件形式引入多选表格
      });

    var search = new Search({
        store: store,
        gridCfg: gridCfg
      }),
      grid = search.get('grid');

    function addFunction() {
      /*var newData = {isNew : true}; //标志是新增加的记录
      editing.add(newData,'name'); //添加记录后，直接编辑*/
      $('.bui-dialog').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("AdvterData/add");?>', {
        table: 'advter_cost',
        tpl: 'costAdd'
      }, function(ret) {
        $('#content').html(ret._html);
        $('.mark').hide();
        $('.spinner').hide();
        $('#content').show();
      });
    }

    //导入成本
    function importFunction() {
      $('.bui-dialog').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("AdvterData/importCost");?>', {
        table: 'advter_cost',
        tpl: 'importCost'
      }, function(ret) {
        $('#content').html(ret._html);
        $('.mark').hide();
        $('.spinner').hide();
        $('#content').show();
      });
    }

    //导入成本
    function iosimportFunction() {
      $('.bui-dialog').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("AdvterData/iosImportCost");?>', {
        table: 'advter_cost',
        tpl: 'iosImportCost'
      }, function(ret) {
        $('#content').html(ret._html);
        $('.mark').hide();
        $('.spinner').hide();
        $('#content').show();
      });
    }

    function delItems(items) {
      var id = [];
      BUI.each(items, function(item) {
        id.push(item.id);
      });

      if (id.length) {
        BUI.Message.Confirm('确认要删除选中的记录么？', function(ret) {
          console.log(ret);
          store.save('remove', {
            id: id
          });
        }, 'question');
      }
    }

    //监听事件，删除一条记录
    grid.on('cellclick', function(ev) {
      var sender = $(ev.domTarget); //点击的Dom
      if (sender.hasClass('btn-del')) {
        var record = ev.record;
        delItems([record]);
      }
    });

    var datepicker1 = new Calendar.DatePicker({
      trigger: '#startMonth',
      autoRender: true
    });

    var datepicker2 = new Calendar.DatePicker({
      trigger: '#endMonth',
      autoRender: true
    });
  });
}
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