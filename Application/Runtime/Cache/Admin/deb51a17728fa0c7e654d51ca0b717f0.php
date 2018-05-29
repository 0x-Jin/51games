<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>角色授权</title>
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
  <script type="text/javascript" src="/static/admin/js/bui.js"></script>
  <script type="text/javascript" src="/static/admin/js/config.js"></script>
</head>
<link rel="stylesheet" type="text/css" href="/static/admin/css/easy.css" >
<div class="pad_10">
    <form action="<?php echo U('System/auth');?>" method="post">
    <div class="J_tablelist table_list">
    <table width="100%" cellspacing="0" id="J_auth_tree">
      <thead>
          <tr><th align="left"><?php echo L('权限');?> - <?php echo ($role["name"]); ?></th></tr>
        </thead>
      <tbody>
          <?php echo ($list); ?>
      </tbody>
    </table>
    <input type="hidden" name="role_id" value="<?php echo ($role["id"]); ?>"></input>
    <div>
    
    <div class="btn_wrap_fixed">
      <label class="select_all"><input type="checkbox" name="checkall" class="J_checkall"><?php echo L('全选');?>/<?php echo L('取消');?></label>
        <input type="submit" class="btn" name="dosubmit" value="<?php echo L('授权');?>"/>
    </div>
    </form>
</div>

<script src="/static/admin/js/jquery.treetable.js"></script>
<script>
$(function() {
    $("#J_auth_tree").treeTable({indent:20});

    $('.J_checkall').live('click', function(){
        $('.J_checkitem').attr('checked', this.checked);
        $('.J_checkall').attr('checked', this.checked);
    });

    $('.J_checkitem').live('click', function(){
        var chk = $("input[type='checkbox']"),
            count = chk.length,
            num = chk.index($(this)),
            level_top = level_bottom =  chk.eq(num).attr('level');
        for(var i=num; i>=0; i--){
            var le = chk.eq(i).attr('level');
            if(eval(le) < eval(level_top)){
                chk.eq(i).attr("checked", true);
                var level_top = level_top-1;
            }
        }
        for(var j=num+1; j<count; j++){
            var le = chk.eq(j).attr('level');
            if(chk.eq(num).attr("checked")) {
                if(eval(le) > eval(level_bottom)) chk.eq(j).attr("checked", true);
                else if(eval(le) == eval(level_bottom)) break;
            }else{
                if(eval(le) > eval(level_bottom)) chk.eq(j).attr("checked", false);
                else if(eval(le) == eval(level_bottom)) break;
            }
        }
    });
});
</script>
</body>
</html>