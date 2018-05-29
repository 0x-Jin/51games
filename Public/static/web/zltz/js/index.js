var init = function(){
	ZLslide();  /* 轮播*/
	ZLlist();   /*各个文章列表切换*/
	ZLnature();/*武将模块图片的跳转切换*/
	Zlfloot();
}	

function ZLslide () {
	$('#marquee').bxSlider({  
      mode:'horizontal', //默认的是水平 
	  displaySlideQty:1,//显示li的个数 
	  infiniteLoop: true,//无限循环
	  moveSlideQty: 3,//移动li的个数 
	  auto: true, 
	  // controls: true,//是否隐藏左右按钮 
	  pause: 2000,
	  autoHover: true,
	  autoControls: false,
	  // pager:true,
  });
}

function ZLlist() {
	var news_nav = $('.news-nav li');
    var news_con = $('.news-list');
 	$(".news-nav li").mouseover(function() {
 		var num = $(".news-nav .on").index();
	    $(".news-nav li").removeClass("on");
	    $(this).addClass("on");
	    var index= $(".news-nav .on").index();
	    news_nav.removeClass('on').eq(index).addClass('on');
	    
        	news_con.addClass('dn').eq(index).removeClass('dn')
	    	// news_con.eq(index).removeClass('dn')
	    	// news_con.eq(num).removeClass('dn')
		    // news_con.eq(num).animate({opacity:"0"},300);
		    // news_con.eq(index).animate({opacity:'1'},500)
	   
	});
    // 清除第一个列表的边框
    $('.news-list').each(function(index,item){
    	$(this).find('li:first-child').find('div').hide();
    })
}


function ZLnature () {
	jynavHover();
	var jyAnimaTabActive = 0;
	var jyMountTabActive = 0;
	var jyMagicTabActive = 0;
	var jyWingTabActive = 0;
	var jyAnimaTabIndex = 2;
	var jyMountTabIndex = 2;
	var jyMagicTabIndex = 2;
	var jyWingTabIndex = 2;

	//灵宠详情
	var arrmount = ["曹操","郭嘉","司马懿","张辽","许褚","典韦","张郃","徐晃","乐进","于禁","甄宓","荀彧"];
	var arrmountbold = ["孟德","奉孝","仲达","文选","仲康","令明","文选","公明","文谦","文则","甄宓","文若"];
	var arrmountblack = ["乱世枭雄","奇谋鬼才","神鬼莫测","横扫千军","霸王卸甲","古之恶来","乱战先锋","东征西讨","每战先登","无坚不陷","洛神顷赋","王佐之才"]
	var arrmountdetails = ["对敌方全体造成95%的计策伤害，并且眩晕其1回合","提升己方后排武将伤害提升50%,提升己方前排50%伤害减免,持续2回合","对敌方全体造成160%的计策伤害,并15%概率会使敌人进入暴走状态,持续1回合","对敌方前排造成145%物理伤害,并附带20%吸血效果","自身立即回复最大血量的35%,并且给己方全体武将增加反击状态,持续1回合","对敌方全体造成85%的物理伤害,并产生嘲讽效果,并且增加自身50%的伤害减免,持续1回合","对敌方随机三个武将造成145%的物理伤害，当己方阵营武将少于4名时,此技能效果将获得50%伤害加成","对敌方前排造成150%的物理伤害,当自身血量低于30%时,该武将造成的所有伤害提升30%","对敌方后排造成125%的物理伤害,此技能可以触发追击","消耗自身当前20%血量,对敌方前排造成220%物理伤害","恢复我方全体武将80%的计策生命,并附加一个回血BUFF,每回合恢复30%计策生命,持续2回合","恢复己方前排120%的计策生命，并且给其施加一个20%的吸血效果,持续2回合"]
	var arrmountnum = [3,2,3,3,3,2,3,3,2,3,3,3]

	//法器详情
	var arranima = ["刘备","关羽","张飞","诸葛亮","赵子龙","马超","黄忠","魏延","庞统","姜维","徐庶","黄月英"];
	var arranimabold = ["玄德","云长","翼德","孔明","子龙","孟起","汉升","文长","士元","伯约","元直","婉贞"];
	var arranimablack = ["仁德在心","狂龙出海","真狂虎式","运筹帷幄","七探盘龙","西凉铁骑","百步穿杨","断情七绝","铁索连环","智勇双全","昭烈无言","才貌双全"]
	var arranimadetails = ["治疗我方前排120%的计策生命,并移除所有武将减益状态,并有20%概率发动法术连击,对后排恢复120%血量","敌方后排造成105%的物理伤害,并使其眩晕1回合","对敌方前排造成125%的物理伤害，有30%概率再次触发一次技能","对敌方全体造成105%的计策伤害,30%回复自身1点怒气","对敌方智力最高的英雄造成270%的物理伤害，30%恢复自身2点怒气","对敌方前排造成145%的物理伤害,当技能造成敌方武将阵亡时将再次触发一次技能","对敌方前排造成125%伤害，下回合休息一回合，并提升自身物理伤害25%，持续到战斗结束，此buff可累加","对敌方前排造成150%的物理伤害，并且恢复自身1点怒气","随机对敌方3个武将造成190%计策伤害,并使其产生连锁效果,持续1回合","对敌方竖排造成145%的计策伤害,并增加全队50%的识破率,持续2回合","对敌方后排造成145%的计策伤害并从以下效果随机出现:<br/>1.恢复敌方后排10%最大生命值<br/>2.对敌方后排附加沉默效果,持续1回合<br/>3.对敌方后排附加束缚效果,持续1回合","对敌方前排造成140%的物理伤害,有20%概率恢复自身1点怒气值"]
	var arranimanum = [3,2,2,2,2,3,2,3,3,3,3,2]

	//灵骑详情
	var arrmagic = ["孙权","孙策","大乔","小乔","周瑜","太史慈","吕蒙","陆逊","孙尚香","甘宁","黄盖","鲁肃"];
	var arrmagicbold = ["仲谋","伯符","朝容","夕颜","公瑾","子义","子明","伯言","尚香","兴霸","公覆","子敬"];
	var arrmagicblack = ["安定江东","东吴霸主","沉鱼落雁","闭月羞花","火烧赤壁","猿臂弯弓","白衣渡江","火烧连营","弓腰并射","百骑袭营","老当益壮","天降甘霖"]
	var arrmagicdetails = ["对敌方前排造成120%计策伤害,当己方武将少于4人时,技能伤害提升至180%,并使自身免疫控制,持续2回合","随机对敌方武力最高的武将连续攻击3次,伤害依次累加,并且每次攻击附加20%吸血效果","对敌方武力最高的武将造成240%的计策伤害，30%概率恢复自身2点怒气","对敌方全体造成95%的计策伤害,有20%概率使目标进入暴乱状态,持续1回合","对敌方血量最低的武将造成210%计策伤害,如果敌方死亡则再次触发一次技能","对敌方竖排造成160%的物理伤害，并使其减少1点怒气值","对敌方后排造成105%的物理伤害，并使其束缚，持续1回合","随机对敌方三名武将造成160%计策伤害，并使其晕眩，持续1回合","对敌方后排造成125%的物理伤害,并且附带流血效果,每回合损失当前血量的10%,持续2回合","对敌方前排造成120%的物理伤害，并有20%概率恢复自身3点怒气","对自身施加嘲讽并附加反击效果，持续1回合。每回合回复最大生命20%血量，持续两回合","恢复已方全体90%的计策生命，并净化全体队员。"]
	var arrmagicnum = [3,2,2,3,2,2,2,3,2,3,3,3]

	//仙羽详情
	var arrwing = ["吕布","貂蝉","董卓","左慈","于吉","童渊","司马徽","张角","颜良","文丑","陈宫","贾诩"];
	var arrwingbold = ["奉先","红昌翼","仲颖","元放","元放","雄付","德操","孟凌","文恒","不俊","公台","文和"];
	var arrwingblack = ["天下无敌","惊鸿闭月","西凉铁骑","仙雷降世","魔道术咒","蓬莱枪神","奇门遁甲","黄巾妖术","乾坤圣斩","勇冠三军","智计才绝","算无遗策"]
	var arrwingdetails = ["对敌方血量最少的武将造成275%的物理伤害,每次普通攻击或技能击杀武将时,伤害提升20%,可以叠加持续至战斗结束","恢复我方全体单位80%的计策生命，并且为武力最高的将领恢复2点怒气","对敌方前排造成135%物理伤害,并眩晕1回合","对敌方全体造成80%的计策伤害,并将敌方全部武将减少1点怒气值","恢复己方血量最低和武力最高的武将125%的计策生命,并驱散其减益效果,并使其受到伤害减免50%,持续2回合","损失20%当前生命,对敌方前排造成180%的物理伤害,此后每次击败敌方武将,自身血量恢复最大生命的15%","对敌方随机三个单位造成145%的计策伤害，并使其沉默,持续1回合","对敌方随机三个单位造成145%的计策伤害,并提使其沉默，持续1回合","驱散敌方后排身上增益状态，并对其造成140%的物理伤害,附加20%吸血效果","对敌方随机1个武将造成230%物理伤害,并使其减少2点怒气","对敌方随机三个武将造成125%的计策伤害,并将自己和己方当前血量最低的武将进行连锁,持续2回合","对敌方血量最少的武将造成270%的计策伤害，并50%概率恢复自身一点怒气。"]
	var arrwingnum = [2,3,3,3,3,2,3,2,2,2,2,2]

	//右边环绕切换
	$(".jy-surround-icon li").bind({//hover效果
		mouseenter: function(e) {
			var indexIcon = $(this).index() + 1;
			if(!$(this).is('.active')){
				$(this).find('div.jy-surround-pic').addClass('jy-surround-color-icon' + indexIcon);
				$(this).find('div.surround-icon').addClass('dn');
				
			}
		},
		mouseleave: function(e) {
			var indexIcon = $(this).index() + 1;
			if(!$(this).is('.active')) {
				$(this).find('div.jy-surround-pic').removeClass('jy-surround-color-icon' + indexIcon);
				$(this).find('div.surround-icon').removeClass('dn');
			}
		},
		click: function(e) {
			$(this).siblings().removeClass('active');
			$(this).siblings().find('div.surround-icon').removeClass('dn');
			$(this).addClass('active');
			var indexIcon = $(this).index() + 1;
			$('.jy-nature-stting').addClass('dn');
			$('.jy-nature-stting').eq($(this).index()).removeClass('dn');
			$('.jy-nature-surround').attr('class','jy-surround-img' + indexIcon);
			$('.jy-surround-img' + indexIcon).addClass("jy-nature-surround");
			$(".jy-surround-icon li").eq(0).find('div.jy-surround-pic').removeClass('jy-surround-color-icon1');
			$(".jy-surround-icon li").eq(1).find('div.jy-surround-pic').removeClass('jy-surround-color-icon2');
			$(".jy-surround-icon li").eq(2).find('div.jy-surround-pic').removeClass('jy-surround-color-icon3');
			$(".jy-surround-icon li").eq(3).find('div.jy-surround-pic').removeClass('jy-surround-color-icon4');
			$(this).find('div.jy-surround-pic').addClass('jy-surround-color-icon' + indexIcon);
			$(this).find('div.surround-icon').addClass('dn');
			jynavHover(); /* 执行左边hover点击函数 */
			if(indexIcon == 1) {
				jyMountTabSame();
				$('.jy-military-title span').text("魏")
			} else if(indexIcon == 2) {
				jyAnimaTabSame();
				$('.jy-military-title span').text("蜀")
			} else if(indexIcon == 3) {
				jyMagicTabSame();
				$('.jy-military-title span').text("吴")
			} else if(indexIcon == 4) {
				jyWingTabSame();
				$('.jy-military-title span').text("群")
			}
		}
	})


	//文字详情切换函数
	function jyMountTabSame () {
		$('.jy-picture-img1').css("background","url(https://img.chuangyunet.net/static/web/zltz/images/military/jy-picture-mount" +jyMountTabIndex+ ".png) no-repeat center");
		$('.zl-name-last').text(arrmountbold[jyMountTabIndex-1]);
		$('.zl-codelist').text(arrmountblack[jyMountTabIndex-1]);
		$('.jy-nature-title p').text(arrmountdetails[jyMountTabIndex-1]);
		$('.zl-name-title').text(arrmount[jyMountTabIndex-1]);
		$('.jy-nature-leit i').css("width",arrmountnum[jyMountTabIndex-1]*24)
	}
	function jyAnimaTabSame () {
		$('.jy-picture-img1').css("background","url(https://img.chuangyunet.net/static/web/zltz/images/military/jy-picture-anima" +jyAnimaTabIndex+ ".png) no-repeat center");
		$('.zl-name-last').text(arranimabold[jyAnimaTabIndex-1]);
		$('.zl-codelist').text(arranimablack[jyAnimaTabIndex-1]);
		$('.jy-nature-title p').html(arranimadetails[jyAnimaTabIndex-1]);
		$('.zl-name-title').text(arranima[jyAnimaTabIndex-1]);
		$('.jy-nature-leit i').css("width",arranimanum[jyAnimaTabIndex-1]*24)
	}
	function jyMagicTabSame () {
		$('.jy-picture-img1').css("background","url(https://img.chuangyunet.net/static/web/zltz/images/military/jy-picture-magic" +jyMagicTabIndex+ ".png) no-repeat center");
		$('.zl-name-last').text(arrmagicbold[jyMagicTabIndex-1]);
		$('.zl-codelist').text(arrmagicblack[jyMagicTabIndex-1]);
		$('.jy-nature-title p').text(arrmagicdetails[jyMagicTabIndex-1]);
		$('.zl-name-title').text(arrmagic[jyMagicTabIndex-1]);
		$('.jy-nature-leit i').css("width",arrmagicnum[jyMagicTabIndex-1]*24)
	}
	function jyWingTabSame () {
		$('.jy-picture-img1').css("background","url(https://img.chuangyunet.net/static/web/zltz/images/military/jy-picture-wing" +jyWingTabIndex+ ".png) no-repeat center");
		$('.zl-name-last').text(arrwingbold[jyWingTabIndex-1]);
		$('.zl-codelist').text(arrwingblack[jyWingTabIndex-1]);
		$('.jy-nature-title p').text(arrwingdetails[jyWingTabIndex-1]);
		$('.zl-name-title').text(arrwing[jyWingTabIndex-1]);
		$('.jy-nature-leit i').css("width",arrwingnum[jyWingTabIndex-1]*24)
	}

	//左侧hover/click啪啪啪切换
	function jynavHover (){
		var index= $(".jy-surround-icon li.active").index();
		$('.jy-nature-stting ul').eq(index).find('li').bind({
			mouseenter: function(e) {
				
			},
			mouseleave: function(e) {
				
			},
			click : function(e){
				var indexLi = index;
				var num = $(this).index()+1;
				var ulIndex = $('.jy-nature-stting ul').eq(indexLi).find('li');
				if(indexLi == 0) {
					jyMountTabIndex = num;
					jyMountTabSame();
					
					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({top:'-50px',opacity:'0.5'},"slow");
					    div.animate({top:'0px',opacity:'0.8'},"slow");
					    div.animate({width:'1200px',opacity:'1'},"slow");
					}
				} else if (indexLi == 1) {
					jyAnimaTabIndex = num;
					jyAnimaTabSame()
					
					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({top:'-50px',opacity:'0.5'},"slow");
					    div.animate({top:'0px',opacity:'0.8'},"slow");
					    div.animate({width:'1200px',opacity:'1'},"slow");
					}

				} else if (indexLi == 2) {
					jyMagicTabIndex = num;
					jyMagicTabSame();
					
					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({top:'-50px',opacity:'0.5'},"slow");
					    div.animate({top:'0px',opacity:'0.8'},"slow");
					    div.animate({width:'1200px',opacity:'1'},"slow");
					}
				} else if (indexLi == 3) {
					jyWingTabIndex = num;
					jyWingTabSame();

					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({top:'-50px',opacity:'0.5'},"slow");
					    div.animate({top:'0px',opacity:'0.8'},"slow");
					    div.animate({width:'1200px',opacity:'1'},"slow");
					}
				}
				$(this).siblings().removeClass('active');
				$(this).addClass('active');
			}
		})
	}
}


function Zlfloot () {
	var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
	var isOpera = userAgent.indexOf("Opera") > -1; //判断是否Opera浏览器 
	var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera; //判断是否IE浏览器
	if (isIE) {  
        var reIE = new RegExp("MSIE (\\d+\\.\\d+);");  
        reIE.test(userAgent);  
        var fIEVersion = parseFloat(RegExp["$1"]);  
        if(fIEVersion == 8) {
   			$('.gallery_left_middle').addClass('gallery-left')
   			$('.gallery_right_middle').addClass('gallery-right')
   		}
   	}//isIE end  
	$.fn.gallery_slider = function(options) {
	  var _ops = $.extend({
	      imgNum: 4 , //图片数量
	      gallery_item_left: '.prev' , //左侧按钮
	      gallery_item_right: '.next' , //右侧按钮
	      gallery_left_middle: '.gallery_left_middle', //左侧图片容器
	      gallery_right_middle: '.gallery_right_middle', //左侧图片容器
	      threeD_gallery_item: '.threeD_gallery_item' //图片容器
	  }, options);
	  var _this = $(this),
	  		_imgNum = _ops.imgNum, //图片数量
	  		_gallery_item_left = _ops.gallery_item_left, //左侧按钮
	  		_gallery_item_right = _ops.gallery_item_right, //右侧按钮
	  		_gallery_left_middle = _ops.gallery_left_middle, //左侧图片容器
	  		_gallery_right_middle = _ops.gallery_right_middle, //左侧图片容器
	  		_threeD_gallery_item = _ops.threeD_gallery_item; //图片容器
	  		
  	//左侧按钮绑定点击事件
  	_this.find(_gallery_item_left).on('click',function(){
			var idx = parseInt(_this.find(_gallery_left_middle).index());
			//当前展示图片逻辑
			_this.find(_threeD_gallery_item).eq(idx).removeClass('gallery_left_middle').addClass('front_side');
			//当idx值为0时，执行逻辑
			_this.find(_threeD_gallery_item).eq(idx == 0 ? idx + _imgNum - 1 : idx - 1).removeClass('gallery_out').addClass('gallery_left_middle');
			//当idx值为_imgNum - 3时，执行逻辑
			_this.find(_threeD_gallery_item).eq(idx == _imgNum - 3 ? idx + 2 : idx - _imgNum + 2).removeClass('gallery_right_middle').addClass('gallery_out');
			//当idx值为_imgNum - 2时，执行逻辑
			_this.find(_threeD_gallery_item).eq(idx == _imgNum - 2 ? idx + 1 : idx - _imgNum + 1).removeClass('front_side').addClass('gallery_right_middle'); 
			if (isIE) {   
		        if(fIEVersion == 8) {
		        		_this.find(_threeD_gallery_item).eq(idx).removeClass('gallery-left');
		        		_this.find(_threeD_gallery_item).eq(idx == 0 ? idx + _imgNum - 1 : idx - 1).addClass('gallery-left');
		        		_this.find(_threeD_gallery_item).eq(idx == _imgNum - 3 ? idx + 2 : idx - _imgNum + 2).removeClass("gallery-right")
		        		_this.find(_threeD_gallery_item).eq(idx == _imgNum - 2 ? idx + 1 : idx - _imgNum + 1).addClass('gallery-right'); 
		   		}
		   	} 
		})
		//右侧按钮绑定点击事件
		_this.find(_gallery_item_right).on('click',function(){
			var idx = parseInt(_this.find(_gallery_right_middle).index());
			//当前展示图片逻辑
			_this.find(_threeD_gallery_item).eq(idx).removeClass('gallery_right_middle').addClass('front_side');
			//当idx值为0时，执行逻辑
			_this.find(_threeD_gallery_item).eq(idx == 0 ? idx + _imgNum - 1 : idx - 1).removeClass('front_side').addClass('gallery_left_middle');
			//当idx值为1时，执行逻辑
			_this.find(_threeD_gallery_item).eq(idx == 1 ? idx + _imgNum - 2 : idx - 2).removeClass('gallery_left_middle').addClass('gallery_out');
			//当idx值为_imgNum - 2时，执行逻辑
			_this.find(_threeD_gallery_item).eq(idx == _imgNum - 2 ? idx + 1 : idx - _imgNum + 1).removeClass('gallery_out').addClass('gallery_right_middle');
			if (isIE) {   
		        if(fIEVersion == 8) {
	        		_this.find(_threeD_gallery_item).eq(idx).removeClass('gallery-right');
	        		_this.find(_threeD_gallery_item).eq(idx == 0 ? idx + _imgNum - 1 : idx - 1).addClass('gallery-right');
	        		_this.find(_threeD_gallery_item).eq(idx == _imgNum - 3 ? idx + 2 : idx - _imgNum + 2).removeClass("gallery-left")
	        		_this.find(_threeD_gallery_item).eq(idx == _imgNum - 2 ? idx + 1 : idx - _imgNum + 1).addClass('gallery-left'); 
		   		}
		   	} 
		})
	}
}
$(document).ready(function(){
    init()
});