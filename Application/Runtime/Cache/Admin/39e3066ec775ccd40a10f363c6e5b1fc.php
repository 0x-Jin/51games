<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>渠道号列表</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/static/admin/css/combo.select.css" type="text/css">
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
    <script type="text/javascript" src="/static/admin/js/jquery.combo.select.js"></script>
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
    </style>
</head>
 <body>
  <!-- 搜索区 -->
  <div class="container">
    <div class="row">
      <form id="searchForm" class="form-horizontal span48">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">包编号：</label>
            <div class="controls">
              <input name="agent" type="text" class="input-normal control-text">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">游戏：</label>
            <div class="controls">
              <select name="game_id" id="gameId"></select>
            </div>
          </div>
          
          <div class="control-group span8">
            <label class="control-label">包编号模板：</label>
            <div class="controls">
              <select name="agentTpl" id="agentTpl">
                <option value="0">全部母包</option>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">渠道分类：</label>
            <div class="controls">
              <select name="channel_id" id="channelId"></select>
            </div>
          </div>


          <div class="control-group span8">
            <label class="control-label">渠道商：</label>
            <div class="controls">
              <select name="advteruser_id" id="advteruserId"></select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">代理商：</label>
            <div class="controls">
              <select name="proxyId" id="proxyId"></select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">广告账户：</label>
            <div class="controls">
              <select name="advterAccountId" id="advterAccountId"></select>
            </div>
          </div>

          <!-- <div class="control-group span8">
            <label class="control-label">负责人：</label>
            <div class="controls">
              <select name="principal_id" id="principalId"></select>
            </div>
          </div> -->

          <div class="control-group span8">
            <label class="control-label">部门：</label>
            <div class="controls">
              <select name="departmentId" id="departmentId">
                <option value="0">全部</option>
                <?php echo ($tplPartment); ?>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">创建人：</label>
            <div class="controls">
              <select name="creater" id="creater">
                <?php echo ($creater); ?>
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
$(function(){
  //数据加载
  // principal();
  adv_company();
  gameLists();
  channelType();
  agentTpl();
  getProxy();
  getAdvterAccount();
  //获取负责人
  /*function principal(){
    var _html = '<option value=0>--全部--</option>';
      $.post("<?php echo U('Ajax/principals');?>",'',function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.name+"</option>";
        });
        $('#principalId').html(_html);
        $('#principalId').comboSelect();
      });
  }*/

  //获取渠道分类
  function channelType(){
    var _html = '';
      $.post("<?php echo U('Ajax/getChannelList');?>",{all:1},function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.channelName+"</option>";
        });
        $('#channelId').html(_html);
        $('#channelId').comboSelect();
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

  //获取代理商
  function getProxy(){
      var _html = '';
      $.post("<?php echo U('Ajax/getProxy');?>",{all:1},function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
          if(v.id == 0){
            _html += "<option value=0>--全部--</option>";
          }else{
            _html += "<option value="+v.id+">"+v.proxyName+"</option>";
          }
        });
        $('#proxyId').html(_html);
        $('#proxyId').comboSelect();
      });
  }

  //获取代理商
  function getAdvterAccount(){
      var _html = '';
      $.post("<?php echo U('Ajax/getAdvterAccount');?>",{all:1},function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            if(v.id == 0){
              _html += "<option value=0>--全部--</option>";
            }else{
              _html += "<option value="+v.id+">"+v.account+"</option>";
            }
        });
        $('#advterAccountId').html(_html);
        $('#advterAccountId').comboSelect();
      });
  }

  //获取游戏
  function gameLists(){
    var _html = '';
      $.post("<?php echo U('Ajax/getGameList');?>",{all:1},function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.gameName+"</option>";
        });
        $('#gameId').html(_html);
        $('#gameId').comboSelect();
      });
  }

  //获取包编号模板
  function agentTpl(game_id){
    var _html = '<option value="0">全部母包</option><option value="-1">全部子包</option>';
      $.post("<?php echo U('Ajax/getAgent');?>",{all:1,game_id:game_id},function(ret){
        var ret = eval('('+ret+')');
        $(ret).each(function(i,v){
            _html += "<option value="+v.id+">"+v.agent+"</option>";
        });
        $('#agentTpl').html(_html);
        $('#agentTpl').comboSelect();
      });
  }

  $('#gameId').change(function(event) {
    agentTpl($(this).val());
  });

});

  function set_status(status,id,obj,mtype){
    var data1;
    var data2;
    var _url = "<?php echo U('Advter/agentEdit');?>";
    if(mtype == 'mstatus'){
      _url = "<?php echo U('Advter/agentStatusEdit');?>";
      data1 = {status:1,id:id,type:'setAgentStatus'};
      data2 = {status:0,id:id,type:'setAgentStatus'};
    }else if(mtype == "loginstatus"){
      data1 = {loginStatus:1,id:id,type:'setAgentStatus'};
      data2 = {loginStatus:0,id:id,type:'setAgentStatus'};
    }else if(mtype == 'paystatus'){
      data1 = {payStatus:1,id:id,type:'setAgentStatus'};
      data2 = {payStatus:0,id:id,type:'setAgentStatus'};
    }else if(mtype == 'advterStaus'){
      data1 = {advterStaus:1,id:id,type:'setAgentStatus'};
      data2 = {advterStaus:0,id:id,type:'setAgentStatus'};
    }
      if(status == 0){
        //关闭
        $.post(_url,data1,function(ret){
          if(ret.status == 1){
            $(obj).attr({src:'/static/admin/img/toggle_disabled.gif',onclick:'set_status(1,'+id+',this,"'+mtype+'")'});
          }else{
            alert(ret.info);
            return false;
          }
        });
      }else if(status == 1){
        //开启
        $.post(_url,data2,function(ret){
          if(ret.status == 1){
            $(obj).attr({src:'/static/admin/img/toggle_enabled.gif',onclick:'set_status(0,'+id+',this,"'+mtype+'")'});
          }else{
            alert(ret.info);
            return false;
          }
        });
      }
    
  }

  //删除
  function agentDelete(id){
    $('.bui-dialog:not(.bui-message)').remove();
    if(!id) return false;
    if(confirm('确定删除该记录吗？')){
      $.post("<?php echo U('Advter/agentDelete');?>",{id:id,table:'agent'},function(ret){
        alert(ret.info);
        if(ret.status == 1){
          window.location.reload();
        }
      });
    }
  }

  //编辑
  function agentEdit(id){
    $('.bui-dialog:not(.bui-message)').remove();
    $('.mark').show();
    $('.spinner').show();
    if(!id) return false;
    $.get("<?php echo U('Advter/agentEdit');?>",{id:id,table:'agent',tpl:'agentEdit'},function(ret){
      $('#content').html(ret._html);
      $('#content').show();
      $('.mark').hide();
      $('.spinner').hide();    
    });
  }

    //商品
    function goodsShow(agent, obj){
        $('.bui-dialog:not(.bui-message)').remove();
        $('.mark').show();
        $('.spinner').show();
        if(!agent) return false;
        $.get("<?php echo U('Advter/goodsShow');?>",{agent:agent},function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    //配置
    function agentConfig(agent,obj){
      $('.bui-dialog:not(.bui-message)').remove();
      $('.mark').show();
      $('.spinner').show();
      if(!agent) return false;
      $.get("<?php echo U('Advter/agentConfig');?>",{agent:agent},function(ret){
        $('#content').html(ret._html);
        $('#content').show();
        $('.mark').hide();
        $('.spinner').hide();
      });
    }

    //复制
    function copyUrl(id)
    {
        $('#'+id).select();
        document.execCommand('Copy');
        alert('复制成功');
    }
  num = 0;

  BUI.use('common/search',function (Search) {
    
    // var enumObj = {"1":"禁用","0":"正常"},
      editing = new BUI.Grid.Plugins.DialogEditing({
        contentId : 'content', //设置隐藏的Dialog内容
        autoSave : true, //添加数据或者修改数据时，自动保存
        triggerCls : 'btn-edit'
      }),
      columns = [
          {title:'ID',dataIndex:'id',width:50,elCls:'center'},
          {title:'渠道分类',dataIndex:'channelTypeName',width:80,elCls:'center'},
          {title:'包编号',dataIndex:'agent',width:120,elCls:'center'},
          {title:'包体名称',dataIndex:'agentName',width:180,elCls:'center'},
          {title:'包名',dataIndex:'bundleId',width:180,elCls:'center'},
          {title:'推广游戏',dataIndex:'gameName',width:100,elCls:'center'},
          {title:'游戏类型',dataIndex:'gameType',width:80,elCls:'center'},
          {title:'所属渠道商',dataIndex:'advteruserName',width:80,elCls:'center'},
          // {title:'所属代理商',dataIndex:'proxyName',width:200,elCls:'center'},
          // {title:'所属广告账户',dataIndex:'accountName',width:200,elCls:'center'},
          {title:'部门',dataIndex:'departmentId',width:80,elCls:'center'},
          {title:'操作人',dataIndex:'creater',width:80,elCls:'center'},
          {title:'广告回调开关',dataIndex:'advterStaus',width:90,elCls:'center'},
          {title:'下载地址',dataIndex:'packageStatusName',width:60,elCls:'center',renderer : function (value,obj) {
              if(value == '-1'){
                  //正常
                  return '<span style="color: green">----</span>';
              }else if(value == 0){
                  //正常
                  return '<span style="color: grey">未打包</span>';
              }else if(value == 1){
                  //禁止
                  return '<span style="color: red">打包中</span>';
              }else{
                  num += 1;
                  return '<span onclick=copyUrl("t'+num+'") class="copy_that" title="点击复制">点击复制&nbsp;&nbsp;&nbsp;&nbsp;<textarea style="width:0px;height:0px;" id="t'+num+'">'+value+'</textarea></span>';
              }
          }
          },
          {title:'最新',dataIndex:'isNew',width:40,elCls:'center'},
              <?php if(session('admin.role_id') == 1 or session('admin.role_id') == 3 or session('admin.role_id') == 6 or session('admin.role_id') == 9 or session('admin.role_id') == 13 or session('admin.role_id') == 14): ?>{title:'渠道状态',dataIndex:'status',width:60,elCls:'center',renderer : function (value,obj) {
              if(value == 0){
                //正常
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick=set_status('+value+','+obj.id+',this,"mstatus") data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_enabled.gif">';
              }else if(value == 1){
                //禁止
                return '<img data-tdtype="toggle" data-field="status" class="status_btn" onclick=set_status('+value+','+obj.id+',this,"mstatus") data_status='+value+' data_id='+obj.id+' src="/static/admin/img/toggle_disabled.gif">';
              }
            }
          },

          {title:'渠道充值回调地址',dataIndex:'agentCallbackUrl',width:350,elCls:'center'},<?php endif; ?>
          {title:'创建时间',dataIndex:'createTime',width:150,elCls:'center'},
          <?php if(in_array(session('admin.role_id'),array(1,3,9,13,14,35))): ?>{title:'操作',dataIndex:'opt',width:100,elCls:'center'}
          <?php elseif(session('admin.role_id') == 6): ?>
            {title:'操作',dataIndex:'opt2',width:100,elCls:'center'}<?php endif; ?>
        ],
      store = Search.createStore('<?php echo U("Advter/agentList");?>?table=agent',{
        proxy : {
          save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
            removeUrl : '<?php echo U("Advter/delete");?>?table=agent'
          },
          method : 'POST'
        },
        autoSync : true //保存数据后，自动更新
      }),
      gridCfg = Search.createGridCfg(columns,{
        tbar : {
          items : [
            {text : '<i class="icon-plus"></i>新增母包渠道号',btnCls : 'button button-small opt-btn',handler:addFunction},
            {text : '<i class="icon-plus"></i>批量增加子包渠道号',btnCls : 'button button-small opt-btn',handler : addAllFunction},
            {text : '<i class="icon-plus"></i>重打渠道包',btnCls : 'button button-small opt-btn',handler : packageFunction},
            {text : '<i class="icon-plus"></i>重打母包',btnCls : 'button button-small opt-btn',handler : packageAgentFunction}
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
      $('.bui-dialog:not(.bui-message)').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("Advter/agentAdd");?>','',function(ret){
        $('#content').html(ret._html);
        $('#content').show();
        $('.mark').hide();
        $('.spinner').hide();
      });
    }

    function addAllFunction(){
      $('.bui-dialog:not(.bui-message)').remove();
      $('.mark').show();
      $('.spinner').show();
      $.get('<?php echo U("Advter/batchAddAgent");?>','',function(ret){
        $('#content').html(ret._html);
        $('#content').show();
        $('.mark').hide();
        $('.spinner').hide();
      });
    }

      function packageFunction(){
          $('.bui-dialog:not(.bui-message)').remove();
          $('.mark').show();
          $('.spinner').show();
          $.get('<?php echo U("Advter/packageAgent");?>','',function(ret){
              $('#content').html(ret._html);
              $('#content').show();
              $('.mark').hide();
              $('.spinner').hide();
          });
      }
      
      function packageAgentFunction() {
          $('.bui-dialog:not(.bui-message)').remove();
          $('.mark').show();
          $('.spinner').show();
          $.get('<?php echo U("Advter/packageAll");?>','',function(ret){
              $('.mark').hide();
              $('.spinner').hide();
              if (ret.status != 1) {
                  BUI.Message.Alert('您无权进行此操作！','','info');
              } else {
                  $('#content').html(ret._html);
                  $('#content').show();
              }
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