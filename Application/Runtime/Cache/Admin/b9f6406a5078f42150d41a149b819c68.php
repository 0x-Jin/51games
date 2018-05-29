<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            付费帐户留存统计
        </title>
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
    </head>
    
        <style>
            tfoot .bui-grid-cell-text{text-align: center;}
          .btn-default {height:25px;}
          .filter-option {margin-top: -4px;}
          .bs-searchbox .form-control {height:25px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
        </style>
    
    <body>
        <!-- 搜索区 -->
        <div class="container">
            <div class="row">
                <form class="form-horizontal span48" id="searchForm" method="post">
                    <!-- <input type="hidden" value=1 name="lookType" id="lookType"/> -->
                    <div class="row">
                        <div class="control-group span8">
                            <label class="control-label" style="width: 100px;">
                                游戏名称：
                            </label>
                            <div class="controls">
                                <select id="game_id" name="game_id">
                                </select>
                            </div>
                        </div>
                        <?php if(session('admin.role_id') != 3): ?><div class="control-group span8">
                                <label class="control-label" style="width: 60px;">
                                    母包：
                                </label>
                                <div class="controls" id="p_agent_contain">
                                    <select class="selectpicker" data-actions-box="true" data-live-search="true" id="agent_p" multiple="" name="agent_p[]">
                                    </select>
                                </div>
                            </div><?php endif; ?>
                        <?php if(session('admin.role_id') != 3): ?><div class="control-group span8">
                                <label class="control-label" style="width: 60px;">
                                    子包：
                                </label>
                                <div class="controls" id="agent_contain">
                                    <select class="selectpicker" data-actions-box="true" data-live-search="true" id="agent" multiple="" name="agent[]">
                                    </select>
                                </div>
                            </div><?php endif; ?>
                        <div class="control-group span8">
                            <label class="control-label" style="width: 60px;">
                                区服：
                            </label>
                            <div class="controls">
                                <select class="selectpicker" data-actions-box="true" data-live-search="true" id="serverId" name="serverId">
                                </select>
                            </div>
                        </div>
                        <div class="control-group span8">
                            <label class="control-label" style="width: 100px;">
                                统计日期：
                            </label>
                            <div class="controls">
                                <input class="calendar" id="startDate" name="startDate" type="text" value="<?php echo date('Y-m-d',strtotime(date('Y-m-d').' -7 day'));?>">
                                    <span>
                                        -
                                    </span>
                                    <input class="calendar" id="endDate" name="endDate" type="text" value="<?php echo date('Y-m-d');?>">
                                    </input>
                                </input>
                            </div>
                        </div>
                        <div class="control-group span8">
                            <div class="controls">
                                <button class="button button-warning" id="btnSearch" type="button">
                                    搜索
                                </button>
                            </div>
                            <div class="controls">
                                <button class="button button-info" id="export" type="button">
                                    导出
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <form action='<?php echo U("Data/firstPayRemain");?>' id="subfm" method="post">
                    <input name="game_id" type="hidden" value=""/>
                    <input name="agent" type="hidden" value=""/>
                    <input name="agentName" type="hidden" value=""/>
                    <input name="serverId" type="hidden" value=""/>
                    <input name="startDate" type="hidden" value=""/>
                    <input name="endDate" type="hidden" value=""/>
                    <input name="agent_p" type="hidden" value=""/>
                    <input name="export" type="hidden" value="1"/>
                </form>
            </div>
            <div style="color:red;margin-left:10px;">
                付费帐户留存数据15分钟更新一次，留存数据每天更新一次
            </div>
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
                $('#btnSearch').click(function(){
                    $('#grid').show();
                });

            $('.selectpicker').selectpicker({
                    selectAllText: '全选',
                    deselectAllText: '不选',
                    liveSearchPlaceholder: '搜索关键字',
                    noneSelectedText: '',
                    multipleSeparator: ',',
                    liveSearch: true,
                    actionsBox: true
                });
            $('#export').click(function(){
                $("#subfm input[name=game_id]").val($("#game_id").val());
                $("#subfm input[name=agent]").val($('#agent').val());
                $("#subfm input[name=agent_p]").val($('#agent_p').val());
                $("#subfm input[name=agentName]").val($("#agentName").val());
                $("#subfm input[name=startDate]").val($("#startDate").val());
                $("#subfm input[name=endDate]").val($("#endDate").val());
                $('#subfm').submit();
            })
            gameLists();
            $('#game_id').change(function() {
                var game_id = $(this).val();
                getPAgentByGame(game_id);
                serverList();
            });

            $('#agent_p').change(function() {
                getAgentByGame();
                serverList();
            });
            
        });

        //获取母包渠道号
        function getPAgentByGame(game_id){
            var _html = '';
            $.post("<?php echo U('Ajax/getAgent');?>",{game_id:game_id},function(ret){
                _html += "<option>--请选择母包--</option>";
                var ret = eval('('+ret+')');
                $(ret).each(function(i,v){
                    _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
                });
                $('#agent_p').html(_html);
                $('#agent_p').selectpicker('refresh');
                $('#agent_p').selectpicker('val', '--请选择母包--');
            });
        }

        //获取子包渠道号
        function getAgentByGame() {
            var agent = $("#agent_p").val();
            if (agent != "--请选择渠道号--" && agent != null) {
                var _html = '';
                $.post("<?php echo U('Ajax/getChildAgentByAgent');?>", {agent:agent}, function(ret){
                    _html += "<option>--请选择子包--</option>";
                    var ret = eval('('+ret+')');
                    $(ret).each(function(i,v){
                        _html += "<option value="+v.agent+">"+v.agentAll+"</option>";
                    });
                    $('#agent').html(_html);
                    $('#agent').selectpicker('refresh');
                    $('#agent').selectpicker('val', '--请选择子包--');
                });
            }
        }


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

        /**
         * 获取区服列表
         * @return {[type]} [description]
         */
         function serverList(){
          var _html = '';
          var game_id = $("#game_id").val();
          var agent = $("#agent_p").val();
          $.post("<?php echo U('Ajax/getServerList');?>",{game_id:game_id,agent:agent},function(ret){
              _html += "<option value='0'>--全部--</option>";
              var ret = eval('('+ret+')');
              $(ret).each(function(i,v){
                  _html += "<option value="+v.serverId+">"+v.serverName+'['+v.serverId+']'+"</option>";
              });
              $('#serverId').html(_html);
              $('#serverId').selectpicker('refresh');
          })
         }


        function userRemainArray(){

            var userRemainArray = [
                    {title:'日期',dataIndex:'dayTime',width:100,elCls:'center'},
                    {title:'新增付费帐号',dataIndex:'allFirstPay',width:150,elCls:'center',summary: true},
                    {title:'当日注册并付费',dataIndex:'newFirstPay',width:150,elCls:'center',summary: true},
                    {title:'老用户首次付费',dataIndex:'oldFirstPay',width:150,elCls:'center',summary: true},
                    {title:'次日留存',dataIndex:'day1',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                            if (value == '0.00%') {
                                return value;
                            } else {
                                return '<span style="color:red;">'+value+'</span>';
                            }
                        }
                    },
                    {title:'三日留存',dataIndex:'day2',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                            if (value == '0.00%') {
                                return value;
                            } else {
                                return '<span style="color:red;">'+value+'</span>';
                            }
                        }
                    },
                    {title:'七日留存',dataIndex:'day6',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                            if (value == '0.00%') {
                                return value;
                            } else {
                                return '<span style="color:red;">'+value+'</span>';
                            }
                        }
                    },
                    {title:'十四日留存',dataIndex:'day13',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                            if (value == '0.00%') {
                                return value;
                            } else {
                                return '<span style="color:red;">'+value+'</span>';
                            }
                        }
                    },
                    {title:'三十日留存',dataIndex:'day29',width:100,elCls:'center',summary: true,renderer: function(value, obj) {
                            if (value == '0.00%') {
                                return value;
                            } else {
                                return '<span style="color:red;">'+value+'</span>';
                            }
                        }
                    },
                ];

            return userRemainArray;
        }


        BUI.use('common/search',function (Search) {
        
        Summary = new BUI.Grid.Plugins.Summary(),
        editing = new BUI.Grid.Plugins.DialogEditing({
            contentId : 'content', //设置隐藏的Dialog内容
            autoSave : true, //添加数据或者修改数据时，自动保存
            triggerCls : 'btn-edit'
        }),
        columns = userRemainArray();
        store = Search.createStore('<?php echo U("Data/firstPayRemain");?>', {
                proxy: {
                    save: { //也可以是一个字符串，那么增删改，都会往那么路径提交数据，同时附加参数saveType
                    },
                    method: 'POST'
                },
                pageSize : 9999,
                autoSync: true //保存数据后，自动更新
            }),
            gridCfg = Search.createGridCfg(columns, {
                plugins: [editing, Summary, BUI.Grid.Plugins.AutoFit] // 插件形式引入多选表格
            });

        var search = new Search({
                store: store,
                gridCfg: gridCfg
            }),
            grid = search.get('grid');
        });
        </script>
        <include file="Public/loading">
        </include>
    </body>
</html>