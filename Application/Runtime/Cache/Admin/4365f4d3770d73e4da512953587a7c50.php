<?php if (!defined('THINK_PATH')) exit();?><body>
    <style type="text/css">
        input{border-radius: 4px;}
    </style>
    <div class="demo-content">
        <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
        <form action="<?php echo U('Data/add');?>" class="form-horizontal" id="J_Form" method="post">
            <input name="table" type="hidden" value="vip_user"/>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        游戏名称：
                    </label>
                    <div class="controls">
                        <select name="gameId" id="gameId"></select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        渠道号：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" data-rules="{required:true}" name="agent" type="text">
                        </input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        角色ID：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" data-rules="{required:true}" name="roleId" type="text">
                        </input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        区服：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" data-rules="{required:true}" name="serverName" type="text">
                        </input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        姓名：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" name="name" type="text">
                        </input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        电话：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" name="phone" type="text">
                        </input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">
                        QQ：
                    </label>
                    <div class="controls">
                        <input class="input-normal control-text" data-rules="{required:true}" name="qq" type="text">
                        </input>
                    </div>
                </div>
            </div>
        </form>

    </div>
    <script>
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
                $('#gameId').html(_html);
                $('#gameId').comboSelect();
            });
        }

        BUI.use(['bui/overlay','bui/form'],function(Overlay,Form){
        
          var form = new Form.HForm({
            srcNode : '#J_Form'
          }).render();
        
          var dialog = new Overlay.Dialog({
                title:'VIP用户录入',
                width:500,
                height:400,
                //配置DOM容器的编号
                contentId:'content',
                success:function () {
                  $('#J_Form').submit();
                }
              });
            dialog.show();
          });
    </script>
</body>