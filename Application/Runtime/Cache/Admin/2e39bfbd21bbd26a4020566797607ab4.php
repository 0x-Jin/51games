<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html> 
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>落地页列表</title>
    <link href="/static/admin/css/bootstrap/bootstrap.min.css" rel="stylesheet"/>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css"/>
    <link href="/static/admin/css/bootstrap/bootstrap-select.css" rel="stylesheet"/>
    <link href="/static/admin/css/pagestyle.css" rel="stylesheet"/>
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
    <script type="text/javascript" src="/static/admin/js/jquery.qrcode.min.js">
    </script>
</head>

<style>
  .copy_that{
    width: 50px;
    height: 100%;
    color: rgb(51, 102, 204);
    cursor: pointer;
    display: block;
    white-space: nowrap;
    overflow: hidden;
  }
  #QR{position:absolute;}
</style>

 <body>
  <!-- 搜索区 -->
  <div class="container">
    <div class="row">
      <form id="searchForm" class="form-horizontal span48">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">广告ID：</label>
            <div class="controls">
              <input type="text" class="control-text" name="id">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">广告名称：</label>
            <div class="controls">
              <input type="text" class="control-text" name="adv_name">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">广告标题：</label>
            <div class="controls">
              <input type="text" class="control-text" name="adv_title">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">素材ID：</label>
            <div class="controls">
              <input type="text" class="control-text" name="material_id">
            </div>
          </div>
          
          <div class="control-group span8">
            <label class="control-label">创建人：</label>
            <div class="controls">
              <select name='creater'>
                <option value="0">全部</option>
                <?php echo ($creater); ?>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">广告模板：</label>
            <div class="controls">
              <select name='adv_tpl_id'>
                <option value="0">全部</option>
                <?php echo ($tpl_list); ?>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">素材状态:</label>
            <div class="controls">
              <select name='status'>
                <option value="-1">全部</option>
                <option value="0">正常</option>
                <option value="1">素材有变</option>
              </select>
            </div>
          </div>

          <!-- <div class="control-group span8">
            <label class="control-label">广告商：</label>
            <div class="controls">
              <input type="text" class="control-text" name="advteruser_id" id="advteruser_id">
            </div>
          </div> -->

          <div class="control-group span8">
            <label class="control-label">所属包编号：</label>
            <div class="controls">
              <input type="text" class="control-text" name="agent_id" id="agent_id">
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
    <div style="color:red;margin-left:10px;">PS：创建落地页和修改落地页需要点击该落地页右边的重新生成按钮</div>
    <div class="search-grid-container">
      <div id="grid"></div>
    </div>

  </div>
  
  <div id="QR" ></div>
  <!-- <img src="" alt="" id="QR" style="display: none;position: absolute;" /> -->

  <!-- 弹窗 -->
  <div id="content" class="hide">
     
  </div>

  <script type="text/javascript">
    BUI.use('common/page');
  </script>

<script type="text/javascript">
  var num = 0;
  $(function(){
    loadData();
  });
  //重新生成
  function recreate(adv_id,mid,cdnId){
    $.post("<?php echo U('Advter/recreate');?>",{adv_id:adv_id,mid:mid,cdnId:cdnId},function(ret){
      if(ret.status == 1){
        if(confirm(ret.info+',是否需要刷新？')){
          loadData();
        }else{
          return false;
        }
      }
    })
  }

  //删除
  function advListDelete(id){
    $('.bui-dialog').remove();
    if(!id) return false;
    if(confirm('确定删除该记录吗？')){
      $.post("<?php echo U('Advter/delete');?>",{id:id,table:'advter_list'},function(ret){
        alert(ret.info);
        if(ret.status == 1){
          if(confirm(ret.info+',是否需要刷新？')){
            loadData();
          }else{
            return false;
          }
        }
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

  //编辑
  function advListEdit(id){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('Advter/edit');?>",{id:id,table:'advter_list',tpl:'advListEdit'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();
    });
  }

  //复制落地页
  function advListCopy(id){
    $('.bui-dialog').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('Advter/edit');?>",{id:id,table:'advter_list',tpl:'advListCopy'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();
    });
  }

  function showQR(address,e){
      $('#QR canvas').remove();
      var w = $(window).width();
      var h = $(window).height();
      if($(e).offset().left > w/2){
          var l = $(e).offset().left + document.body.scrollLeft - 300;
      }else{
          var l = $(e).offset().left + document.body.scrollLeft + 25;
      }
      if($(e).offset().top > h/2){
          var t = $(e).offset().top + document.body.scrollLeft - 250;
      }else{
          var t = $(e).offset().top + document.body.scrollLeft +  60;
      }

      document.getElementById("QR").style.left = l + "px";
      document.getElementById("QR").style.top = t + "px";
      document.getElementById("QR").style.display = "block";

      $('#QR').qrcode({width: 200,height: 200,text: address});
  }

  function hideQR(){
      document.getElementById("QR").style.display = "none";
  }

  function loadData(){
    $('#grid').html('');
    BUI.use('common/search',function (Search) {
      
      // var enumObj = {"1":"禁用","0":"正常"},
        editing = new BUI.Grid.Plugins.DialogEditing({
          contentId : 'content', //设置隐藏的Dialog内容
          autoSave : true, //添加数据或者修改数据时，自动保存
          triggerCls : 'btn-edit'
        }),
        columns = [
            {title:'广告ID',dataIndex:'id',width:50,elCls:'center'},
            {title:'素材ID',dataIndex:'material_id',width:50,elCls:'center'},
            {title:'广告名称',dataIndex:'adv_name',width:200,elCls:'center'},
            {title:'广告标题',dataIndex:'adv_title',width:170,elCls:'center'},
            {title:'所属包编号',dataIndex:'agent',width:100,elCls:'center'},
            {title:'所属渠道商',dataIndex:'advteruser',width:80,elCls:'center'},
            {title:'游戏名',dataIndex:'game_name',width:80,elCls:'center'},
            {title:'链接地址',dataIndex:'html_filename',width:380,elCls:'center'},
            {title:'广告监控链接',dataIndex:'monitor_link',width:60,elCls:'center',renderer : function (value,obj) {
                  if(value != ''){
                    num += 1;
                    return '<span onclick=copyUrl("t'+num+'") class="copy_that" title="点击复制">点击复制&nbsp;&nbsp;&nbsp;&nbsp;<textarea style="width:0px;height:0px;" id="t'+num+'">'+value+'</textarea></span>';
                  }
              }
            },
            {title:'创建人',dataIndex:'creater',width:60,elCls:'center'},
            {title:'广告模板',dataIndex:'tpl_name',width:100,elCls:'center'},
            {title:'素材状态',dataIndex:'materialStatus',width:60,elCls:'center'},
            {title:'创建时间',dataIndex:'create_time',width:140,elCls:'center'},
            {title:'操作',dataIndex:'opt',width:150,elCls:'center'}
          ],
        store = Search.createStore('<?php echo U("Advter/advList");?>?table=advter_list',{
          proxy : {
            save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
              removeUrl : '<?php echo U("Advter/delete");?>?table=advter_list'
            },
            method : 'POST'
          },
          // pageSize : 300,
          autoSync : true //保存数据后，自动更新
        }),
        gridCfg = Search.createGridCfg(columns,{
          tbar : {
            items : [
              {text : '<i class="icon-plus"></i>新增广告',btnCls : 'button button-small opt-btn',handler : addFunction},
              {text : '<i class="icon-plus"></i>批量新增广告',btnCls : 'button button-small opt-btn',handler : batchAddFunction},
              {text : '<i class="icon-refresh"></i>批量刷新',btnCls : 'button button-small opt-btn',handler : createFunction}
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
        /*var newData = {isNew : true}; //标志是新增加的记录
        editing.add(newData,'name'); //添加记录后，直接编辑*/
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        $.get('<?php echo U("Advter/add");?>',{table:'advter_list',tpl:'advListAdd'},function(ret){
          $('#content').html(ret._html);
          $('.mark').hide();
          $('.spinner').hide();
          $('#content').show();
        });
      }

      function batchAddFunction(){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        $.get('<?php echo U("Advter/add");?>',{table:'advter_list',tpl:'advListBatchAdd'},function(ret){
          $('#content').html(ret._html);
          $('.mark').hide();
          $('.spinner').hide();
          $('#content').show();
        });
      }

      //批量生成
      function createFunction(){
        var selections = grid.getSelection();
        var id = createItems(selections);
        console.log(id);
        $.post("<?php echo U('Advter/recreate');?>",{adv_id:id},function(ret){
          alert(ret.info);
          if(ret.status == 1){
            if(confirm(ret.info+',是否需要刷新？')){
              loadData();
            }else{
              return false;
            }
          }
        });
      }

      function createItems(items){
        var id = [];
        BUI.each(items,function(item){
          id.push(item.id);
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