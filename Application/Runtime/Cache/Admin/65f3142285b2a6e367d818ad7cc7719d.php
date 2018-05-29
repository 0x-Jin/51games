<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1, minimum-scale=1.0, maximum-scale=1, user-scalable=no"/>
    <meta content="yes" name="apple-mobile-web-app-capable"> 
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection" />
    <meta content="email=no" name="format-detection">
    <title>[title]</title>
    <link href="/static/admin/css/main-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/dpl-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/bui-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/page-min.css" rel="stylesheet" type="text/css" />
    <link href="/static/admin/css/easyui/themes/default/easyui.css" rel="stylesheet" type="text/css" />
    <!-- <link href="/static/admin/css/easyui/themes/default/icon.css" rel="stylesheet" type="text/css" /> -->
    <script type="text/javascript" src="/static/admin/js/jquery-1.8.1.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/easyui/jquery.min.js"></script>
	<script type="text/javascript" src="/static/admin/js/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="/static/admin/js/bui.js"></script>
    <script type="text/javascript" src="/static/admin/js/config.js"></script>
</head>
<script src="/static/admin/js/jquery-ui.min.js" type="text/javascript"></script>
<script src="/static/admin/js/evol-colorpicker.min.js" type="text/javascript"></script>
<script src="/static/admin/js/jquery.combo.select.js"></script>
<link href="/static/admin/css/evol-colorpicker.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="/static/admin/css/combo.select.css">
<style type="text/css">
    input{border-radius: 4px;}
    .combo-select{width:200px;}
    .combo-select .text-input{width:200px;}
</style>
<div class="pad_lr_10">

    <!-- 搜索区 -->
    <?php if(session('admin.role_id') != 19): ?><div class="row" style="margin:20px;">
       <div class="control-group span8">
          <div class="controls">
            <a href="javascript:;" id="addmaterial" class="button button-primary">添加素材</a>
          </div>
        </div>
      </div><?php endif; ?>
  <div class="container">
    <div class="row">
      <form id="searchForm" class="form-horizontal span68" method="post" action="<?php echo U('Advter/material');?>">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">素材ID：</label>
            <div class="controls">
              <input type="text" value="<?php echo ($search["material_id"]); ?>" name="material_id" class="control-text">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">素材名称：</label>
            <div class="controls">
              <input type="text" value="<?php echo ($search["material_name"]); ?>" name="material_name" class="control-text">
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">制作人：</label>
            <div class="controls">
              <input type="text" value="<?php echo ($search["author"]); ?>" name="author" class="control-text">
            </div>
          </div>
          <div class="control-group span15">
            <label class="control-label">制作日期：</label>
            <div class="controls">
              <input type="text" class="calendar calendar-time" value="<?php echo ($search["begin"]); ?>" name="begin"><span> - </span><input name="end" type="text" value="<?php echo ($search["end"]); ?>" class="calendar calendar-time">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">单页显示：</label>
            <div class="controls">
              <select name="spage" class="input-normal"> 
                <option value="8" <?php if(($spage) == "8"): ?>selected<?php endif; ?>>8</option>
                <option value="18" <?php if(($spage) == "18"): ?>selected<?php endif; ?>>18</option>
                <option value="27" <?php if(($spage) == "27"): ?>selected<?php endif; ?>>27</option>
              </select>
            </div>
          </div>
          <div class="control-group span8">
            <label class="control-label">素材分类：</label>
            <div class="controls">
              <select name="material_type_id" class="input-normal"> 
                <option value="">全部分类</option>
                <?php if(is_array($materialTypeId)): $i = 0; $__LIST__ = $materialTypeId;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["material_type_id"]); ?>" <?php if($search['material_type_id'] == $val['material_type_id']) echo 'selected';?> ><?php echo ($val["mtype_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </div>
          </div>

          <div class="control-group span8">
            <label class="control-label">落地页类型：</label> <!-- 1:banner 2:banner+幻灯片混合 3:banner+一屏落地页 -->
            <div class="controls">
              <select id="_page_type" name="page_type" class="input-normal">
                  <option value="0">全部</option>
                  <?php if(is_array($tplList)): $k = 0; $__LIST__ = $tplList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($k % 2 );++$k;?><option value="<?php echo ($k); ?>" <?php if($search["page_type"] == $k): ?>selected<?php endif; ?> ><?php echo ($val); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> 
                </select>
            </div>
          </div>

          <div class="control-group span8">
            <div class="controls">
              <button  type="submit" id="btnSearch" class="button button-primary">搜索</button>
            </div>
          </div>
        </div>
        </div>
      </form>
    </div>
  </div>

    <div class="J_tablelist item_imglist table_list clearfix" style="height: 600px; overflow: auto;"> 
      <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><div class="item fl">
            <label>
            <div class="img clearfix"><a target="_blank" href="<?php echo U('Advter/view',array('material_id'=>$val['material_id'],'page_type'=>$val['page_type']));?>"><img src="<?php echo ($path_url); echo ($files[$val['material_id']]['url']); ?>"></a></div>
            </label>
            <span class="line_x" title="<?php echo ($val["material_name"]); ?>">&nbsp;&nbsp;&nbsp;ID：<a target="_blank" href="<?php echo U('Advter/view',array('material_id'=>$val['material_id'],'page_type'=>$val['page_type']));?>"><?php echo ($val["material_id"]); ?></a>&nbsp;&nbsp;&nbsp;模板类型：<?php echo ($val["tpl_name"]); ?>&nbsp;&nbsp;&nbsp;名称：<?php echo ($val["material_name"]); ?>&nbsp;&nbsp;&nbsp;大小：<?php echo ($val["material_file_size"]); ?>K</span>
            <span class="line_x">&nbsp;&nbsp;&nbsp;上传者：<?php echo ($authors[$val['author']]['real']); ?>&nbsp;&nbsp;&nbsp;日期：<?php echo (date("Y-m-d", $val["create_time"])); ?>&nbsp;&nbsp;&nbsp;</span>
            <ul>
                <li><a class="button button-success" href="<?php echo U('Advter/view',array('material_id'=>$val['material_id'],'page_type'=>$val['page_type']));?>" target="_blank">预览</a></li>
                <?php if(session('admin.role_id') != 19): ?><li><a data-title="素材复制 - <?php echo ($val["material_name"]); ?>" onclick="copyMaterial(<?php echo ($val['material_id']); ?>)" class="medit button button-primary">复制</a></li>

                <li><a data-title="编辑素材 - <?php echo ($val["material_name"]); ?>" data-uri="<?php echo U('Advter/materialEdit', array('material_id'=>$val['material_id'],'page_type'=>$val['page_type']));?>" class="medit button button-primary">编辑</a></li>
                <li><a href="javascript:;" onclick="deleteMaterial(<?php echo ($val['material_id']); ?>,'<?php echo ($val['material_name']); ?>')" data-msg="确定要删除“<?php echo ($val["material_name"]); ?>”吗？" class="button button-danger">删除</a></li><?php endif; ?>
            </ul>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
    <div class="btn_wrap_fixed">
        <div id="pages"><?php echo ($page); ?></div>
    </div>
</div>
<!-- 弹窗 -->
<div id="content" class="hide">
 
</div>
<style type="text/css">
.btn_wrap_fixed{
    text-align: right;
    margin: 20px 50px 0px 0px;
}
.container{
    background-color: #f5f5f5;
    margin-bottom: 20px;
}
.J_tablelist{
    margin-left: 30px;
}
.item_imglist .item {
    display: inline-block;
    border: 1px solid #e0e0e0;
    margin: 0 15px 15px 0;
    padding: 5px;
    position: relative;
    width: 350px;
}
.clearfix:after {
    clear: both;
    content: " ";
    display: block;
    height: 0;
    visibility: hidden;
}
.item_imglist .item .img {
    height: 213px;
    overflow: hidden;
    width: 350px;
}
.clearfix {
    clear: both;
}

.item_imglist .item .img img {
    width: 350px;
}
.item_imglist .item span {
    color: #444;
    display: block;
    height: 35px;
    line-height: 35px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.item_imglist .item ul {
    margin-top: 5px;
}
.item_imglist .item ul li {
    float: left;
    margin-top: 5px;
    text-align: center;
    width: 25%;
}
</style>
<script type="text/javascript">

    //复制素材
    function copyMaterial(mid){
      $.post("<?php echo U('Advter/copyMaterial');?>",{mid:mid},function(ret){
        alert(ret.info);
        if(ret.status == 1){
          window.location.reload();
        }
      })
    }

    function deleteMaterial(id,material_name){
      if(confirm('确定要删除“'+material_name+'”吗？')){
        $.get("<?php echo U('Advter/materialDelete');?>",{material_id:id},function(ret){
          alert(ret.info);
          if(ret.status == 1){
            window.location.reload();
          }
        });
      }
    }
    $('.medit').click(function(){
      $('.bui-dialog').remove();
      $('.mark').show();
      $('.spinner').show();
      var url = $(this).attr('data-uri');
      $.get(url,'',function(ret){
        $('#content').html(ret._html);
        $('#content').show(); 
        $('.mark').hide();
        $('.spinner').hide();   
      });
    });

    $('#addmaterial').click(function(){
        $('.bui-dialog').remove();
        $('.mark').show();
        $('.spinner').show();
        $.get("<?php echo U('Advter/materialAdd');?>",'',function(ret){
          $('#content').html(ret._html);
          $('#content').show();
          $('.mark').hide();
          $('.spinner').hide();
        });
    });
    BUI.use('bui/calendar',function(Calendar){
        var datepicker = new Calendar.DatePicker({
          trigger:'.calendar-time',
          // showTime:true,
          autoRender : true
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