<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>用户列表</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/combo.select.css" rel="stylesheet" type="text/css">
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
                    <label class="control-label" style="width: 80px;">用户标识符：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="userCode" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 60px;">用户账号：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="userName" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 80px;">渠道标识符：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="channelUserCode" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 80px;">渠道名称：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="channelUserName" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span8">
                    <label class="control-label" style="width: 80px;">手机号码：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="mobile" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span7">
                    <label class="control-label" style="width: 90px;">注册时的游戏：</label>
                    <div class="controls">
                        <select name="game_id" id="game_id"></select>
                    </div>
                </div>

                <div class="control-group span6">
                    <label class="control-label">注册时的渠道号：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="agent" style="width: 100px;">
                    </div>
                </div>

                <!--<div class="control-group span9">-->
                    <!--<label class="control-label">注册时的UDID：</label>-->
                    <!--<div class="controls">-->
                        <!--<input type="text" class="control-text" name="udid" style="width: 200px;">-->
                    <!--</div>-->
                <!--</div>-->

                <div class="control-group span9">
                    <label class="control-label">注册时的imei/idfa：</label>
                    <div class="controls">
                        <input type="text" class="control-text" name="imei" style="width: 200px;">
                    </div>
                </div>

                <div class="control-group span10">
                    <label class="control-label" style="width: 60px;">注册时间：</label>
                    <div class="controls">
                        <input type="text" class="calendar calendar-time" name="startDate" value="<?php echo date('Y-m-d 00:00:00');?>"><span> - </span><input type="text" class="calendar calendar-time" name="endDate" value="<?php echo date('Y-m-d 00:00:00',strtotime(date('Y-m-d').' +1 day'));?>">
                    </div>
                </div>

                <div class="control-group span3">
                    <div class="controls">
                        <button  type="button" id="btnSearch" class="button button-primary">搜索</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
    <div class="search-grid-container span25">
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
    $(function () {
        gameLists();
    });

    //获取游戏
    function gameLists(){
        var _html = '';
        $.post("<?php echo U('Ajax/getGameList');?>",{all:1},function(ret){
            var ret = eval('('+ret+')');
            $(ret).each(function(i,v){
                _html += "<option value="+v.id+">"+v.gameName+"</option>";
            });
            $('#game_id').html(_html);
            $('#game_id').comboSelect();
        });
    }

    //编辑
    function userInfo(id, obj){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show(); 
        if(!id) return false;
        $.get("<?php echo U('Data/userInfo');?>",{userCode:id},function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    //改密
    function editPassword(id, obj){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        if(!id) return false;
        $.get("<?php echo U('Data/editPassword');?>",{userCode:id},function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    //改名
    function editName(id, obj){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        if(!id) return false;
        $.get("<?php echo U('Data/editName');?>",{userCode:id},function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    //手机号码绑定
    function editMobile(id, obj){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        if(!id) return false;
        $.get("<?php echo U('Data/editMobile');?>",{userCode:id},function(ret){
            $('#content').html(ret._html);
            $('#content').show();
            $('.mark').hide();
            $('.spinner').hide();
        });
    }

    //角色
    function roleInfo(id, obj){
        if(top.topManager){
            //打开左侧菜单中配置过的页面
            top.topManager.openPage({
                id : "role",
                href : "<?php echo U('Data/role');?>?userCode="+id,
                title : "角色信息",
                reload : true
            });
        }
    }

    BUI.use('common/search',function (Search) {

        editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        }),
            columns = [
                {title:'用户标识符',dataIndex:'userCode',width:180,elCls:'center'},
                {title:'用户账号',dataIndex:'userName',width:180,elCls:'center'},
//                {title:'渠道标识符',dataIndex:'channelUserCode',width:180,elCls:'center'},
//                {title:'渠道用户账号',dataIndex:'channelUserName',width:180,elCls:'center'},
                {title:'渠道号',dataIndex:'agent',width:100,elCls:'center'},
                {title:'游戏名称',dataIndex:'agentName',width:150,elCls:'center'},
                {title:'游戏分类',dataIndex:'gameName',width:150,elCls:'center'},
                {title:'渠道名称',dataIndex:'channelName',width:80,elCls:'center'},
                {title:'UDID',dataIndex:'udid',width:250,elCls:'center'},
                {title:'创建时间',dataIndex:'create',width:150,elCls:'center'},
                {title:'最后登录时间',dataIndex:'login',width:150,elCls:'center'},
                {title:'操作',dataIndex:'opt',width:200,elCls:'center'}
            ],
            store = Search.createStore('<?php echo U("Data/user");?>',{
                proxy : {
                    save : { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                    },
                    method : 'POST'
                },
                autoSync : true //保存数据后，自动更新
            }),
            gridCfg = Search.createGridCfg(columns,{
                plugins : [editing,BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
            });

        var  search = new Search({
                store : store,
                gridCfg : gridCfg
            }),
            grid = search.get('grid');
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