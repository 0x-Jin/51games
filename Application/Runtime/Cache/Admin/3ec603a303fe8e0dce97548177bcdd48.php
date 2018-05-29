<?php if (!defined('THINK_PATH')) exit();?>
<body>
  
  <div class="demo-content" style="overflow:auto;height:620px;    ">
    <!-- 此节点内部的内容会在弹出框内显示,默认隐藏此节点-->
    <form id="J_Form" class="form-horizontal" method="post" action="<?php echo U('Advter/materialAdd');?>" enctype="multipart/form-data">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">素材名称：</label>
            <div class="controls">
              <input name="material_name" type="text"  data-rules="{required:true}" class="input-normal control-text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">素材分类：</label>
            <div class="controls">
              <select name="material_type_id" class="input-normal">
                  <?php echo ($material_type_id); ?>
                </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span12">
            <label class="control-label">母包编号：</label>
            <div class="controls">
              <select name="agent_id" id="agent_id">

              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">落地页类型：</label> <!-- 1:banner 2:banner+幻灯片混合 3:banner+一屏落地页 -->
            <div class="controls">
              <select id="page_type" name="page_type" class="input-normal">
                <?php if(is_array($tplList)): $k = 0; $__LIST__ = $tplList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($k % 2 );++$k;?><option value="<?php echo ($k); ?>"><?php echo ($val); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>    
              </select>
            </div>
          </div>
        </div>

      <div id="slideOpt">
        <div class="row">
          <div class="control-group span8">
            <label class="control-label">幻灯片的宽度：</label>
            <div class="controls">
              <input name="hdp_width" id="hdp_width" disabled="true" type="text" placeholder="默认值为88" class="input-normal control-text">%
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">幻灯片上边距：</label>
            <div class="controls">
              <input name="hdp_top" id="hdp_top" disabled="true" type="text" placeholder="默认值为0" class="input-normal control-text">%
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">幻灯片播放间隔：</label>
            <div class="controls">
              <input name="hdp_time" id="hdp_time" disabled="true" type="text" placeholder="幻灯片播放间隔，默认5" class="input-normal control-text">秒
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span8">
            <label class="control-label">幻灯片的位置：</label>
            <div class="controls">
              <input name="slide_position" id="slide_position" disabled="true" type="text" placeholder="幻灯片放在哪个图片后面" class="input-normal control-text">
            </div>
          </div>
        </div>
    </div>

        <div class="row" id="bgcolor_main" style="display:none;">
          <div class="control-group span8">
            <label class="control-label">背景颜色：</label>
            <div class="controls">
              <input name="bgcolor" type="text" id="bgcolor"  placeholder="颜色为rgb,默认100, 103, 213"  class="input-normal control-text" />
            </div>
          </div>
        </div>

        <div class="row" >
          <div class="control-group span11">
            <label class="control-label">背景音乐：</label>
            <div class="controls">
              <input type="file" class="input-normal" name="file[]" onchange="fileChange(this)" /> &nbsp;<span style="color:red">音乐格式:.aac,.mp3</span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="control-group span24">
            <table>
              <tr>
                <th style="width: 101px;" align="right">文件上传 :</th>
                <td>
                  <table id="file">
                    <tr>
                      <td align='center'>图片</td>
                      <td align='center'>排序</td>
                      <td align='center'>是否幻灯片</td>
                      <td align='center'>是否弹窗</td>
                      <!-- <td align='center'>是否视频</td> -->
                    </tr>
                    <?php $__FOR_START_29831__=0;$__FOR_END_29831__=21;for($i=$__FOR_START_29831__;$i < $__FOR_END_29831__;$i+=1){ ?><tr>
                        <td align='center'><input type="file" class="input-normal" name="file[]"/></td>
                        <td align='center'><input type="text" name="order_num[]" class="input-small" style="text-align:center;width:30px;" value=<?php echo ($i); ?> ></td>
                        <td align='center'><input type="checkbox" name="file_check[]" value="0" class="mchecked input-text order_input"></td>
                        <td align='center'><input type="checkbox" name="pop_check[]" value="0" class="mchecked input-text order_input"></td>
                        <!-- <td align='center'><input type="checkbox" name="video_check[]" value="0" class="mchecked input-text order_input"></td> -->
                      </tr><?php } ?>
                  </table>
                </td>
              </tr>
            </table>

          </div>
        </div>

      </form>
  </div>
    
 <style type="text/css">
   #file td{width:100px;}
 </style>
 
<!-- script start --> 
    <script type="text/javascript">

    var isIE = /msie/i.test(navigator.userAgent) && !window.opera;   
    function fileChange(target,id) {   
      var fileSize = 0;   
      var filetypes =[".aac",".mp3"];   
      var filepath = target.value;   
      var filemaxsize = 1024;//1M   
      if(filepath){   
      var isnext = false;   
      var fileend = filepath.substring(filepath.lastIndexOf("."));  
      if(filetypes && filetypes.length>0){   
        for(var i =0; i<filetypes.length;i++){   
          if(filetypes[i]==fileend){   
            isnext = true;   
            break;   
          }   
        }   
      }   
      if(!isnext){   
      alert("不接受此文件类型！");   
      target.value ="";   
      return false;   
      }   
      }else{   
      return false;   
      }   
      if (isIE && !target.files) {   
      var filePath = target.value;   
      var fileSystem = new ActiveXObject("Scripting.FileSystemObject");   
      if(!fileSystem.FileExists(filePath)){   
      alert("附件不存在，请重新输入！");   
      return false;   
      }   
      var file = fileSystem.GetFile (filePath);   
      fileSize = file.Size;   
      } else {   
      fileSize = target.files[0].size;   
      }   
        
      var size = fileSize / 1024;   
      if(size>filemaxsize){   
      alert("音乐文件大小不能大于"+filemaxsize/1024+"M！");   
      target.value ="";   
      return false;   
      }   
      if(size<=0){   
      alert("附件大小不能为0M！");   
      target.value ="";   
      return false;   
      }   
    }   

    //获取母包编号
    function agent(){
        var _html = '';
        $.post("<?php echo U('Ajax/getAgent');?>?power=1",'',function(ret){
          var ret = eval('('+ret+')');
          $(ret).each(function(i,v){
              _html += "<option value="+v.id+">"+v.agentAll+"</option>";
          });
          $('#agent_id').html(_html);
          $('#agent_id').comboSelect();
      });
    }
    $(function(){
      agent();
      //复选框选中赋值
      $('.mchecked').click(function(){
          $(this).val(1);
      });

      $('#page_type').change(function(){
        var type_id = $(this).val();
        if(type_id == 5){
          $('#bgcolor_main').show();
          $('#slideOpt').hide();
        }else{
          $('#bgcolor_main').hide();
          $('#slideOpt').show();
        }

        if(type_id == 2 || type_id == 7 || type_id == 9){
          $('#hdp_width').attr('placeholder','默认值为88');
        }else if(type_id == 3 || type_id == 6 || type_id == 8 || type_id == 11){
          $('#hdp_width').attr('placeholder','默认值为100');
        }else{
          $('#hdp_width').attr('placeholder','');
        }
        //幻灯片位置控制，幻灯片宽、高度控制，幻灯片播放间隔控制
        if(type_id == 2 || type_id == 3 || type_id == 6 || type_id == 7 || type_id == 8 || type_id == 9 || type_id == 11){
          $('#slide_position').attr('disabled',false);
          $('#hdp_width').attr('disabled',false);
          $('#hdp_top').attr('disabled',false);
          $('#hdp_time').attr('disabled',false);
        }else{
          $('#slide_position').attr('disabled',true).val('');
          $('#hdp_width').attr('disabled',true).val('');
          $('#hdp_top').attr('disabled',true).val('');
          $('#hdp_time').attr('disabled',true).val('');
        }
      });

    });

    BUI.use(['bui/overlay','bui/form'],function(Overlay,Form){
      var form = new Form.HForm({
        srcNode : '#J_Form'
      }).render();
 
      var dialog = new Overlay.Dialog({
            title:'新增-素材',
            width:750,
            height:730,
            //配置DOM容器的编号
            contentId:'content',
            success:function () {
              $('.mchecked').prop('checked',true);
              $('#J_Form').submit();
            }
          });
        dialog.show();
      });
    </script>
<!-- script end -->
  </div>
</body>
</html>