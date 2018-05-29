var init = function(){
	Jywelfare();/* 福利中心弹窗 */
	Jyhover();/* 导航hover效果 */
    // Jyqrcode(); /*二维码生成*/
    Jylist(); /*各个文章列表切换*/
    Jyswidth(); /*轮播*/
    Jysprog(); /*ios/android切换*/
    // Jyvideo("http://img.ksbbs.com/asset/Mon_1605/0ec8cc80112a2d6.mp4")/*视频模块*/
    Jyoccupation();/* 职业介绍切换模块*/
    Jynature();/*属性模块图片的跳转切换*/
    //JypreloadImg();/* 图片预加载处理*/
    Jychange();/* 内容列表切换 */

    function phone_type(){
		var u = navigator.userAgent;
		var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
		if(isiOS){
		return 1
		}else{
		return 0
		}
	}
	if(phone_type()==1){
		$('.jy-shade').css("width","1200px");
	}
	var writelnLeft = $('.jy-shade').width()/2 - $('.jy-welfare-div').width()/2 + "px";
	$('.jy-welfare-div').css("left",writelnLeft)
}

function Jywelfare (){
	$('.jy-welfare').on("click",function(){
		$('.jy-welfare-div').removeClass('jy-none');
		$('.jy-shade').removeClass('jy-none');
	})
	$('.jy-welfare-close').on("click",function(){
		$('.jy-welfare-div').addClass('jy-none');
		$('.jy-shade').addClass('jy-none');
	})
}


function Jyhover (){/* 导航hover效果 */
	$('.jy-nav ul li').hover(function(){
		if(!$(this).find('i').is('.jy-pitch-color')){
			$(this).find('i').addClass('pitHover');
		};
	},function(){
		$(this).find('i').removeClass('pitHover');
	})
}

// 二维码生成
// function Jyqrcode (){
// 	var link = 'www.baidu.com';
// 	var _data = {width: 150,height: 150,text: link};
// 	$('#QR').qrcode(_data);
// }

function Jylist(){
	
    var news_nav = $('.news-nav li');
    var news_con = $('.news-list');

    $(".news-nav li").on("click",function() {
	    $(".news-nav li").removeClass("on");
	    $(this).addClass("on");
	    var index= $(".news-nav .on").index();
	    news_nav.removeClass('on').eq(index).addClass('on');
        news_con.addClass('jy-none').eq(index).removeClass('jy-none')
	});
    // 清除第一个列表的边框
    $('.news-list').each(function(index,item){
    	$(this).find('li:first-child').find('div').hide();
    })
}

function Jyswidth(){
	//轮播
	var sWidth = $("#focus").width(); //获取焦点图的宽度（显示面积）
    var len = $("#focus ul li").length; //获取焦点图个数
    var index = 0;
    var picTimer;
    
    //以下代码添加数字按钮和按钮后的半透明条，还有上一页、下一页两个按钮
    var btn = "<div class='btnBg'></div><div class='btn'>";
    for(var i=0; i < len; i++) {
        btn += "<span></span>";
    }
    //btn += "</div><div class='preNext pre'></div><div class='preNext next'></div>";
    $("#focus").append(btn);
    $("#focus .btnBg").css("opacity",0.8);
    //$("#focus .btn span").css("backgroundColor","#1ecbfd");

    //为小按钮添加鼠标滑入事件，以显示相应的内容
    $("#focus .btn span").css("opacity",1).mouseenter(function() {
        index = $("#focus .btn span").index(this);
        showPics(index);
    }).eq(0).trigger("mouseenter");

    //本例为左右滚动，即所有li元素都是在同一排向左浮动，所以这里需要计算出外围ul元素的宽度
    $("#focus ul").css("width",sWidth * (len));
    
    //鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
    $("#focus").hover(function() {
        clearInterval(picTimer);
    },function() {
        picTimer = setInterval(function() {
            showPics(index);
            index++;
            if(index == len) {index = 0;}
        },2000); //此4000代表自动播放的间隔，单位：毫秒
    }).trigger("mouseleave");
    
    //显示图片函数，根据接收的index值显示相应的内容
    function showPics(index) { //普通切换
        var nowLeft = -index*sWidth; //根据index值计算ul元素的left值
        $("#focus ul").stop(true,false).animate({"left":nowLeft},300); //通过animate()调整ul元素滚动到计算出的position
        $("#focus .btn span").removeClass("on").eq(index).addClass("on"); //为当前的按钮切换到选中的效果
        $("#focus .btn span").stop(true,false).animate({"opacity":"1"},300).eq(index).stop(true,false).animate({"opacity":"1"},300); //为当前的按钮切换到选中的效果
    }
}

function Jysprog(){
	//进入页面给第一个li里面动态添加om/变颜色
	$('.jy-sprog-div').eq(0).find('li:first').find('div').addClass('om');
	$('.jy-sprog-div').eq(1).find('li:first').find('div').addClass('om');
	$('.jy-sprog-div').eq(2).find('li:first').find('div').addClass('om');


	$('.jy-sprog-nav ul li').on("click",function(){
		var ulIndex = $(this).index();
		var indexImg = ulIndex + 1 ;
		$('.jy-sprog-nav ul li').eq(0).find('div').removeClass("jy-color-img1");
		$('.jy-sprog-nav ul li').eq(1).find('div').removeClass("jy-color-img2");
		$('.jy-sprog-nav ul li').eq(2).find('div').removeClass("jy-color-img3");
		$(this).find('div').addClass("jy-color-img"+indexImg);
		$('.jy-sprog-div').addClass('jy-none').removeClass('active');
		$('.jy-sprog-div').eq(ulIndex).removeClass('jy-none').addClass('active');
	})
	$('.jy-sprog-div li').on("click",function(){
		var Index = $('.jy-sprog-div.active').index();
		if( Index == 1){
			$('.jy-sprog-ios li').find('div').removeClass("om");
			$(this).find('div').addClass("om");
		} else if(Index == 2){
			$('.jy-sprog-android li').find('div').removeClass("om");
			$(this).find('div').addClass("om");
		} else if (Index == 3) {
			$('.jy-sprog-news li').find('div').removeClass("om");
			$(this).find('div').addClass("om");
		}
	})

}

//视频模块
// function Jyvideo(path){
//     $('#source_id').attr('src',path);
//     $('#source_id').attr('type','video/mp4');
// }

// 职业介绍切换模块
function Jyoccupation(){
	$('.jy-pro-context-ada').each(function(index){
		var Liindex = index;
		$(this).on("click",function(index){
			var flag = $('.jy-pro-context-setting').eq(Liindex).is('.jy-pro-context-replace');
			var div_massk = $('.jy-occu-massk');
			if(flag){
				$('.jy-pro-context-setting').removeClass('jy-pro-context-replace');
				$('.jy-pro-context-setting').eq(Liindex).siblings().addClass('jy-pro-context-replace');
				$('.jy-datile-writ').addClass('jy-none');
				$('.jy-datile-writ').eq(Liindex).removeClass('jy-none');
			}
			if(Liindex == 0){
				$('.jy-occu-massk-img2').removeClass('jy-occu-show');
				$('.jy-occu-massk-img1').addClass('jy-occu-show');
			} else if (Liindex == 1) {
				$('.jy-occu-massk-img1').removeClass('jy-occu-show');
				$('.jy-occu-massk-img2').addClass('jy-occu-show');
			}
		})
	})
}

/*属性模块图片的跳转切换*/
function Jynature (){
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
	var arrmount = ["逍遥子","火灵子","梅花小童","黑焰灵童","圣雪灵童","琉璃仙童","紫月仙子","碧焰仙子","齿焱仙子"];
	var arrmountbold = ["一阶灵宠-逍遥子","二阶灵宠-火灵子","三阶灵宠-梅花小童","四阶灵宠-黑焰灵童","五阶灵宠-圣雪灵童","六阶灵宠-琉璃仙童","七阶灵宠-紫月仙子","八阶灵宠-碧焰仙子","九阶灵宠-齿焱仙子"];
	var arrmountblack = ["刁蛮任性我最行","我的热情就像一团火","依人伴君坠红尘","白玉镶珠不足比其容色","玫瑰初露不能方其清丽","天涯咫尺两茫茫","上天给了她美丽","堪比心头宝","惹火了你的心"]
	var arrmountdetails = ["有一种女人，世间男人都想和他一起疯狂","有一种女人，世间男人都会想把她娶回家","满园春色关不住，一枝梅花出墙来","有一种女人，世间男人皆拜服，因为她美貌与智慧并存","有一种女人，世间男人都想宠爱","有一种女人，世间男人都有过的回忆","有一种女人，世间男人只敢若即若离","有一种女人，世间男人均会为之着迷","樱桃樊素口，杨柳小蛮腰"]
	
	//法器详情
	var arranima = ["焚阳","阴阳烛","玄阳燈","太阴轮","乾坤梭","魇龙魔梭","神武魔梭","镇魂鼓","罗摩秘宝"];
	var arranimabold = ["一阶法器-焚阳","二阶法器-阴阳烛","三阶法器-玄阳燈","四阶法器-太阴轮","五阶法器-乾坤梭","六阶法器-魇龙魔梭","七阶法器-神武魔梭","八阶法器-镇魂鼓","九阶法器-罗摩秘宝"];
	var arranimablack = ["是陀螺还是飞镖","烛中残影两阴阳","照亮你夜行的路","幽幽冥火空中荡","火中之梭现乾坤","封印千年终再现","逍遥传说的遗物","鼓中魂魄是为谁","罗摩的秘密在此"]
	var arranimadetails = ["一道残阳铺水中，半江瑟瑟半江红","寻寻觅觅，冷冷清清，凄凄惨惨戚戚","星星电燈，照亮我的前程，让迷失的孩子找到来时的路","月圆之夜，夜黑风高，哥舒夜带小刀","让这一飞梭，燃尽世间的乾坤","道高一尺，魔高一丈","青春相伴，快乐神武魔梭","嘈嘈切切错杂弹，大珠小珠落玉盘","我愿化身石桥 受五百年风吹雨打"]

	//灵骑详情
	var arrmagic = ["嘟角兽","仙羚羊","冥狼王","青火幼牛","金毛幼狮","青麟幼龟","赤焰幼麟","紫嫣幼鸾","红焰幼龙"];
	var arrmagicbold = ["一阶灵骑-嘟角兽","二阶灵骑-仙羚羊","三阶灵骑-冥狼王","四阶灵骑-青火幼牛","五阶灵骑-金毛幼狮","六阶灵骑-青麟幼龟","七阶灵骑-赤焰幼麟","八阶灵骑-紫嫣幼鸾","九阶灵骑-红焰幼龙"];
	var arrmagicblack = ["勇猛矫健","温顺可爱","荒野霸主","忠诚坚毅","山中之王","远古神兽","灵巧动人","翱翔天地","神奇宝贝"]
	var arrmagicdetails = ["一种极其温顺的坐骑，骑乘舒适，爆发力极强","我从仙山来，你要带我到哪里去","是谁带来，远古的呼唤，是谁留下，千年的期盼","我在这儿等着你回来，等着你回来看那桃花开","告诉我，光明顶谁说了算","神龟虽寿，没有竟时","洞若观火，你看到的是我吗","我愿冰封千里，只为等你回眸","给我点时间，我还能进化成喷火龙"]
	
	//仙羽详情
	var arrwing = ["金凤翼","玲珑翼","朱雀翎","冥王翎","琉璃仙翎","噬神","神武圣羽","金刚罗羽","魅影魔翼"];
	var arrwingbold = ["一阶仙羽-金凤翼","二阶仙羽-玲珑翼","三阶仙羽-朱雀翎","四阶仙羽-冥王翎","五阶仙羽-琉璃仙翎","六阶仙羽-噬神","七阶仙羽-神武圣羽","八阶仙羽-金刚罗羽","九阶仙羽-魅影魔翼"];
	var arrwingblack = ["金光闪闪羡煞人","玲珑梦中永不灭","梧桐林中烈焰生","阴阳相隔冥界开","扶摇直上三千尺","谁都不能阻挡我","羽翼渐丰无人见","铜墙铁壁大金刚","银烛魅影冷画屏"]
	var arrwingdetails = ["这仙羽简直亮瞎我的千里眼","佩之可通过随意穿梭，出现在想出现的地方","穿梭于梧桐林间，如烈焰燎原般迅猛","帘卷倚屏双影聚，镜开朱户九条悬","此翎只应填上有，人间能得几回见","神挡杀神，佛挡杀佛","万千羽毛汇聚而成如翼般，唯有缘人方可遇到","轻于鸿毛，稳如泰山","武道极致境界，可生魅影魔翼，达神通之境界"]

	//右边环绕切换
	$(".jy-surround-icon li").bind({//hover效果
		mouseenter: function(e) {
			var indexIcon = $(this).index() + 1;
			if(!$(this).is('.active')){
				$(this).find('div.jy-surround-pic').addClass('jy-surround-color-icon' + indexIcon);
				//$(this).find('div.surround-icon').removeClass('jy-surround-icon' + indexIcon);
			}
		},
		mouseleave: function(e) {
			var indexIcon = $(this).index() + 1;
			if(!$(this).is('.active')) {
				$(this).find('div.jy-surround-pic').removeClass('jy-surround-color-icon' + indexIcon);
				//$(this).find('div.surround-icon').addClass('jy-surround-icon' + indexIcon);
			}
		},
		click: function(e) {
			$(this).siblings().removeClass('active');
			$(this).addClass('active');
			var indexIcon = $(this).index() + 1;
			$('.jy-nature-stting').addClass('jy-none');
			$('.jy-nature-stting').eq($(this).index()).removeClass('jy-none');
			$('.jy-nature-surround').attr('class','jy-surround-img' + indexIcon);
			$('.jy-surround-img' + indexIcon).addClass("jy-nature-surround");
			$(".jy-surround-icon li").eq(0).find('div.jy-surround-pic').removeClass('jy-surround-color-icon1');
			$(".jy-surround-icon li").eq(1).find('div.jy-surround-pic').removeClass('jy-surround-color-icon2');
			$(".jy-surround-icon li").eq(2).find('div.jy-surround-pic').removeClass('jy-surround-color-icon3');
			$(".jy-surround-icon li").eq(3).find('div.jy-surround-pic').removeClass('jy-surround-color-icon4');
			$(this).find('div.jy-surround-pic').addClass('jy-surround-color-icon' + indexIcon);
			jynavHover(); /* 执行左边hover点击函数 */
			if(indexIcon == 1) {
				jyMountTabSame();
				if(jyMountTabActive <= 0){
					$(".jy-prev").removeClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				} else if(jyMountTabActive >= 4) {
					$(".jy-next").removeClass('jy-next-color');
					$(".jy-prev").addClass('jy-prev-color');
				} else {
					$(".jy-prev").addClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				}
			} else if(indexIcon == 2) {
				jyAnimaTabSame();
				if(jyAnimaTabActive <= 0){
					$(".jy-prev").removeClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				} else if(jyAnimaTabActive >= 4) {
					$(".jy-next").removeClass('jy-next-color');
					$(".jy-prev").addClass('jy-prev-color');
				} else {
					$(".jy-prev").addClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				}
			} else if(indexIcon == 3) {
				jyMagicTabSame();
				if(jyMagicTabActive <= 0){
					$(".jy-prev").removeClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				} else if(jyMagicTabActive >= 4) {
					$(".jy-next").removeClass('jy-next-color');
					$(".jy-prev").addClass('jy-prev-color');
				} else {
					$(".jy-prev").addClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				}
			} else if(indexIcon == 4) {
				jyWingTabSame();
				if(jyWingTabActive <= 0){
					$(".jy-prev").removeClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				} else if(jyWingTabActive >= 4) {
					$(".jy-next").removeClass('jy-next-color');
					$(".jy-prev").addClass('jy-prev-color');
				} else {
					$(".jy-prev").addClass('jy-prev-color');
					$(".jy-next").addClass('jy-next-color');
				}
			}
		}
	})

	//左右箭头点击切换
	//jyTabsBtnClickPrev
	$(".jy-prev").on("click",function() {
		var index= $(".jy-surround-icon li.active").index();
		if(index == 0){
			jyMountBtnClickPrev();
		} else if(index == 1){
			jyAnimaBtnClickPrev();
		} else if(index == 2){
			jyMagicBtnClickPrev();
		} else if(index == 3){
			jyWingBtnClickPrev();
		}
	})
	$(".jy-next").on("click",function() {
		var index= $(".jy-surround-icon li.active").index();
		if(index == 0){
			jyMountBtnClickNext();
		} else if(index == 1){
			jyAnimaBtnClickNext();
		} else if(index == 2){
			jyMagicBtnClickNext();
		} else if(index == 3){
			jyWingBtnClickNext();
		}
	})
	function jyMountBtnClickPrev (){
		jyMountTabActive--;
	    var index= $(".jy-surround-icon li.active").index();
		if(jyMountTabActive <= 0){
			jyMountTabActive = 0;
			$(".jy-prev").removeClass('jy-prev-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyMountTabActive;
		$('.jy-nature-stting .jy-mount').css("left",leftRange+"px");
	};
	function jyMountBtnClickNext (){
		jyMountTabActive++;
		if(jyMountTabActive >= 3){
			jyMountTabActive = 3;
			$(".jy-next").removeClass('jy-next-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyMountTabActive;
		$('.jy-nature-stting .jy-mount').css("left",leftRange+"px");
	};
	function jyAnimaBtnClickPrev (){
		jyAnimaTabActive--;
	    var index= $(".jy-surround-icon li.active").index();
		if(jyAnimaTabActive <= 0){
			jyAnimaTabActive = 0;
			$(".jy-prev").removeClass('jy-prev-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyAnimaTabActive;
		$('.jy-nature-stting .jy-anima').css("left",leftRange+"px");
	};
	function jyAnimaBtnClickNext (){
		jyAnimaTabActive++;
		if(jyAnimaTabActive >= 3){
			jyAnimaTabActive = 3;
			$(".jy-next").removeClass('jy-next-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyAnimaTabActive;
		$('.jy-nature-stting .jy-anima').css("left",leftRange+"px");
	}
	function jyMagicBtnClickPrev (){
		jyMagicTabActive--;
	    var index= $(".jy-surround-icon li.active").index();
		if(jyMagicTabActive <= 0){
			jyMagicTabActive = 0;
			$(".jy-prev").removeClass('jy-prev-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyMagicTabActive;
		$('.jy-nature-stting .jy-magic').css("left",leftRange+"px");
	}
	function jyMagicBtnClickNext (){
		jyMagicTabActive++;
		if(jyMagicTabActive >= 3){
			jyMagicTabActive = 3;
			$(".jy-next").removeClass('jy-next-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyMagicTabActive;
		$('.jy-nature-stting .jy-magic').css("left",leftRange+"px");
	}
	function jyWingBtnClickPrev (){
		jyWingTabActive--;
	    var index= $(".jy-surround-icon li.active").index();
		if(jyWingTabActive <= 0){
			jyWingTabActive = 0;
			$(".jy-prev").removeClass('jy-prev-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyWingTabActive;
		$('.jy-nature-stting .jy-wing').css("left",leftRange+"px");
	}
	function jyWingBtnClickNext (){
		jyWingTabActive++;
		if(jyWingTabActive >= 3){
			jyWingTabActive = 3;
			$(".jy-next").removeClass('jy-next-color');
		} else {
			$(".jy-prev").addClass('jy-prev-color');
			$(".jy-next").addClass('jy-next-color');
		}
		var leftRange = -54 * jyWingTabActive;
		$('.jy-nature-stting .jy-wing').css("left",leftRange+"px");
	}

	//文字详情切换函数
	function jyMountTabSame () {
		$('.jy-picture-img1').css("background","url(http://img.chuangyunet.net/static/web/jyjh/images/jy-picture-mount" +jyMountTabIndex+ ".png) no-repeat center");
		$('.jy-nature-bold').text(arrmountbold[jyMountTabIndex-1]);
		$('.jy-nature-block').text(arrmountblack[jyMountTabIndex-1]);
		$('.jy-nature-title p').text(arrmountdetails[jyMountTabIndex-1]);
		$('.jy-picture-title').text(arrmount[jyMountTabIndex-1]);
		if(arrmount[jyMountTabIndex-1].length == 2) {
			$('.jy-picture-clone').addClass('jy-picture-img2').removeClass('jy-picture-img3 jy-picture-img4')
		} else if(arrmount[jyMountTabIndex-1].length == 3) {
			$('.jy-picture-clone').addClass('jy-picture-img3').removeClass('jy-picture-img2 jy-picture-img4')
		} else if (arrmount[jyMountTabIndex-1].length == 4) {
			$('.jy-picture-clone').addClass('jy-picture-img4').removeClass('jy-picture-img3 jy-picture-img2')
		}
	}
	function jyAnimaTabSame () {
		$('.jy-picture-img1').css("background","url(http://img.chuangyunet.net/static/web/jyjh/images/jy-picture-anima" +jyAnimaTabIndex+ ".png) no-repeat center");
		$('.jy-nature-bold').text(arranimabold[jyAnimaTabIndex-1]);
		$('.jy-nature-block').text(arranimablack[jyAnimaTabIndex-1]);
		$('.jy-nature-title p').text(arranimadetails[jyAnimaTabIndex-1]);
		$('.jy-picture-title').text(arranima[jyAnimaTabIndex-1]);
		if(arranima[jyAnimaTabIndex-1].length == 2) {
			$('.jy-picture-clone').addClass('jy-picture-img2').removeClass('jy-picture-img3 jy-picture-img4')
		} else if(arranima[jyAnimaTabIndex-1].length == 3) {
			$('.jy-picture-clone').addClass('jy-picture-img3').removeClass('jy-picture-img2 jy-picture-img4')
		} else if (arranima[jyAnimaTabIndex-1].length == 4) {
			$('.jy-picture-clone').addClass('jy-picture-img4').removeClass('jy-picture-img3 jy-picture-img2')
		}
	}
	function jyMagicTabSame () {
		$('.jy-picture-img1').css("background","url(http://img.chuangyunet.net/static/web/jyjh/images/jy-picture-magic" +jyMagicTabIndex+ ".png) no-repeat center");
		$('.jy-nature-bold').text(arrmagicbold[jyMagicTabIndex-1]);
		$('.jy-nature-block').text(arrmagicblack[jyMagicTabIndex-1]);
		$('.jy-nature-title p').text(arrmagicdetails[jyMagicTabIndex-1]);
		$('.jy-picture-title').text(arrmagic[jyMagicTabIndex-1]);
		if(arrmagic[jyMagicTabIndex-1].length == 2) {
			$('.jy-picture-clone').addClass('jy-picture-img2').removeClass('jy-picture-img3 jy-picture-img4')
		} else if(arrmagic[jyMagicTabIndex-1].length == 3) {
			$('.jy-picture-clone').addClass('jy-picture-img3').removeClass('jy-picture-img2 jy-picture-img4')
		} else if (arrmagic[jyMagicTabIndex-1].length == 4) {
			$('.jy-picture-clone').addClass('jy-picture-img4').removeClass('jy-picture-img3 jy-picture-img2')
		}
	}
	function jyWingTabSame () {
		$('.jy-picture-img1').css("background","url(http://img.chuangyunet.net/static/web/jyjh/images/jy-picture-wing" +jyWingTabIndex+ ".png) no-repeat center");
		$('.jy-nature-bold').text(arrwingbold[jyWingTabIndex-1]);
		$('.jy-nature-block').text(arrwingblack[jyWingTabIndex-1]);
		$('.jy-nature-title p').text(arrwingdetails[jyWingTabIndex-1]);
		$('.jy-picture-title').text(arrwing[jyWingTabIndex-1]);
		if(arrwing[jyWingTabIndex-1].length == 2) {
			$('.jy-picture-clone').addClass('jy-picture-img2').removeClass('jy-picture-img3 jy-picture-img4')
		} else if(arrwing[jyWingTabIndex-1].length == 3) {
			$('.jy-picture-clone').addClass('jy-picture-img3').removeClass('jy-picture-img2 jy-picture-img4')
		} else if (arrwing[jyWingTabIndex-1].length == 4) {
			$('.jy-picture-clone').addClass('jy-picture-img4').removeClass('jy-picture-img3 jy-picture-img2')
		}
	}

	//左侧hover/click啪啪啪切换
	function jynavHover (){
		var index= $(".jy-surround-icon li.active").index();
		$('.jy-nature-stting ul').eq(index).find('li').bind({
			mouseenter: function(e) {
				var indexLi = index;
				var num = $(this).index()+1;
				if(!$(this).is('.active')){
					if(indexLi == 0) {
						$(this).find('div').addClass('jy-mount-color-img' + num);
					} else if (indexLi == 1) {
						$(this).find('div').addClass('jy-anima-color-img' + num);
					} else if (indexLi == 2) {
						$(this).find('div').addClass('jy-magic-color-img' + num);
					} else if (indexLi == 3) {
						$(this).find('div').addClass('jy-wing-color-img' + num);
					}
				}
			},
			mouseleave: function(e) {
				var indexLi = index;
				var num = $(this).index()+1;
				if(!$(this).is('.active')){
					if(indexLi == 0) {
						$(this).find('div').removeClass('jy-mount-color-img' + num);
					} else if (indexLi == 1) {
						$(this).find('div').removeClass('jy-anima-color-img' + num);
					} else if (indexLi == 2) {
						$(this).find('div').removeClass('jy-magic-color-img' + num);
					} else if (indexLi == 3) {
						$(this).find('div').removeClass('jy-wing-color-img' + num);
					}
				}
			},
			click : function(e){
				var indexLi = index;
				var num = $(this).index()+1;
				var ulIndex = $('.jy-nature-stting ul').eq(indexLi).find('li');
				if(indexLi == 0) {
					jyMountTabIndex = num;
					jyMountTabSame();
					$('.jy-nature-stting ul').eq(indexLi).find('li').each(function(index,e){
						if($(this).is('.active')){
							var imgIndex = index+1;
							$(this).find('div').removeClass('jy-mount-color-img' + imgIndex);
						}
					})
					$(this).find('div').addClass('jy-mount-color-img' + jyMountTabIndex);
					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({height:'300px',opacity:'0.5'},"slow");
					    div.animate({height:'339px',opacity:'0.8'},"slow");
					    div.animate({width:'590px',opacity:'1'},"slow");
					}
				} else if (indexLi == 1) {
					jyAnimaTabIndex = num;
					jyAnimaTabSame()
					$('.jy-nature-stting ul').eq(indexLi).find('li').each(function(index,e){
						if($(this).is('.active')){
							var imgIndex = index+1;
							$(this).find('div').removeClass('jy-anima-color-img' + imgIndex);
						}
					})
					$(this).find('div').addClass('jy-anima-color-img' + jyAnimaTabIndex);
					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({height:'300px',opacity:'0.5'},"slow");
					    div.animate({height:'339px',opacity:'0.8'},"slow");
					    div.animate({width:'590px',opacity:'1'},"slow");
					}

				} else if (indexLi == 2) {
					jyMagicTabIndex = num;
					jyMagicTabSame();
					$('.jy-nature-stting ul').eq(indexLi).find('li').each(function(index,e){
						if($(this).is('.active')){
							var imgIndex = index+1;
							$(this).find('div').removeClass('jy-magic-color-img' + imgIndex);
						}
					})
					$(this).find('div').addClass('jy-magic-color-img' + jyMagicTabIndex);
					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({height:'300px',opacity:'0.5'},"slow");
					    div.animate({height:'339px',opacity:'0.8'},"slow");
					    div.animate({width:'590px',opacity:'1'},"slow");
					}
				} else if (indexLi == 3) {
					jyWingTabIndex = num;
					jyWingTabSame();
					$('.jy-nature-stting ul').eq(indexLi).find('li').each(function(index,e){
						if($(this).is('.active')){
							var imgIndex = index+1;
							$(this).find('div').removeClass('jy-wing-color-img' + imgIndex);
						}
					})
					$(this).find('div').addClass('jy-wing-color-img' + jyWingTabIndex);
					var div = $('.jy-picture-img1');
					if(!div.is(":animated")){
						div.animate({height:'300px',opacity:'0.5'},"slow");
					    div.animate({height:'339px',opacity:'0.8'},"slow");
					    div.animate({width:'590px',opacity:'1'},"slow");
					}
				}
				$(this).siblings().removeClass('active');
				$(this).addClass('active');
			}
		})
	}
}

function Jychange (){
	var JychangetabsIndex = 0;
	$('#dttab-title span').click(function(){
		JychangetabsIndex = $(this).index()/2;
        $(this).addClass("selected").siblings().removeClass("selected");
        $("#dttab-content > div").hide().eq($('#dttab-title span').index(this)).show();
        $('.dttab-list span').eq(JychangetabsIndex).addClass('active').siblings().removeClass('active');
        if(JychangetabsIndex == 0) {
        	$('.dttab-setting-img2').addClass('dttab-setting-color2');
        	$('.dttab-setting-img1').removeClass('dttab-setting-color1');
        } else if(JychangetabsIndex == 4) {
        	$('.dttab-setting-img2').removeClass('dttab-setting-color2');
        	$('.dttab-setting-img1').addClass('dttab-setting-color1');
        } else {
        	$('.dttab-setting-img1').addClass('dttab-setting-color1');
        	$('.dttab-setting-img2').addClass('dttab-setting-color2');
        }
    });

    // 下方左右箭头切换
    $('.dttab-setting-img1').click(function(){
    	JychangetabsIndex = JychangetabsIndex == 0 ? 0 : JychangetabsIndex - 1;
    	$('#dttab-title span').eq(JychangetabsIndex).addClass("selected").siblings().removeClass("selected");
        $("#dttab-content > div").hide().eq(JychangetabsIndex).show();
        $('.dttab-list span').eq(JychangetabsIndex).addClass('active').siblings().removeClass('active');
    	if(JychangetabsIndex == 0) {
        	$('.dttab-setting-img2').addClass('dttab-setting-color2');
        	$('.dttab-setting-img1').removeClass('dttab-setting-color1');
        } else if(JychangetabsIndex == 4) {
        	$('.dttab-setting-img2').removeClass('dttab-setting-color2');
        	$('.dttab-setting-img1').addClass('dttab-setting-color1');
        } else {
        	$('.dttab-setting-img1').addClass('dttab-setting-color1');
        	$('.dttab-setting-img2').addClass('dttab-setting-color2');
        }
    })
     $('.dttab-setting-img2').click(function(){
    	JychangetabsIndex = JychangetabsIndex ==4 ? 4 : JychangetabsIndex + 1;
    	$('#dttab-title span').eq(JychangetabsIndex).addClass("selected").siblings().removeClass("selected");
        $("#dttab-content > div").hide().eq(JychangetabsIndex).show();
        $('.dttab-list span').eq(JychangetabsIndex).addClass('active').siblings().removeClass('active');
    	if(JychangetabsIndex == 0) {
        	$('.dttab-setting-img2').addClass('dttab-setting-color2');
        	$('.dttab-setting-img1').removeClass('dttab-setting-color1');
        } else if(JychangetabsIndex == 4) {
        	$('.dttab-setting-img2').removeClass('dttab-setting-color2');
        	$('.dttab-setting-img1').addClass('dttab-setting-color1');
        } else {
        	$('.dttab-setting-img1').addClass('dttab-setting-color1');
        	$('.dttab-setting-img2').addClass('dttab-setting-color2');
        }
    })

}


$(document).ready(function(){
	init()
});