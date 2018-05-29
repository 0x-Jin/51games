
/* 审计起源事半功倍 */

/*这里是一切的起源要干嘛记得从起源找起  start*/
var init = function(){
	Jylist(); /*各个文章列表切换*/
	Jyswidth(); /*轮播*/
	XHbanner(); /* 游戏特色轮播 */
	XHskin(); /* 贴身校花hover点击效果*/
	XHprint(); /* 图片专区切换效果*/
	XHweel();  /*福利中心*/
	XHvideo(); /* 视频模块*/
}
/*这里是一切的起源要干嘛记得从起源找起  end*/


function Jylist(){
    var news_nav = $('.news-nav li');
    var news_con = $('.news-list');
 	$(".news-nav li").mouseover(function() {
	    $(".news-nav li").removeClass("on");
	    $(this).addClass("on");
	    var index= $(".news-nav .on").index();
	    news_nav.removeClass('on').eq(index).addClass('on');
        news_con.addClass('dn').eq(index).removeClass('dn')
        $('.xh-news-pic').stop().animate({"left": index*120+58+ "px"})
        // if($('.xh-news-pic').is(":animated")){

        // }
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
    $("#btnBg").append(btn);
    $("#btnBg .btnBg").css("opacity",0.8);
    //$("#btnBg .btn span").css("backgroundColor","#1ecbfd");

    //为小按钮添加鼠标滑入事件，以显示相应的内容
    $("#btnBg .btn span").css("opacity",1).mouseenter(function() {
        index = $("#btnBg .btn span").index(this);
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
        $("#btnBg .btn span").removeClass("on").eq(index).addClass("on"); //为当前的按钮切换到选中的效果
        $("#btnBg .btn span").stop(true,false).animate({"opacity":"1"},300).eq(index).stop(true,false).animate({"opacity":"1"},300); //为当前的按钮切换到选中的效果
    }
}


function XHbanner () {
	var i=0;
	// var clone=$(".xh-banner .xh-img li").first().clone();
	// $(".xh-banner .xh-img").append(clone);
	var bannerWidth = ($(".xh-banner").width()-40)/2
	var widthLi = $(".xh-banner .xh-img li").width(bannerWidth)
	var size = $(".xh-banner .xh-img li").length;
	var widthUl = $(".xh-banner .xh-img li").width()*size+(size-1)*20;
	$(".xh-banner .xh-img").css({width : widthUl})
	var fend = $(".xh-banner").width()/2;
	$('.xh-banner').height(0.479*bannerWidth)
	$('.xh-banner .xh-img').css({left:-0.521*bannerWidth})
	$('.xh-banner-centent').css({
		left:bannerWidth-360
	})

	var a = false;  
	/*自动轮播*/
	var t=setInterval(function(){
	    i++;
	    move();
	},2000);
	
	//对xh-banner定时器的操作
	$(".xh-banner").hover(function(){
	    clearInterval(t);
	},function(){
		    t=setInterval(function(){
		    i++;
		    move();
		},2000);
	})
	 
	function move(){
		$(".xh-banner .xh-banner-centent ul").stop().animate({left:-i*610},500,function(){
			if(i >=size-3) {
		    	$(".xh-banner .xh-list").css({left:0});
		    }
		});
		$(".xh-banner .xh-img").stop().animate({left:-0.521*bannerWidth-fend*i},500,function(){
			if(i >=size-3) {
		    	i=0;
		    	$(".xh-banner .xh-img").css({left:-0.521*bannerWidth});
		    }
		});
		 
	}
}

function XHskin () {
	var numtwo = ['双儿','沐剑屏','方怡','建宁公主']
	var cententNum = ['拥有倾国倾城的美貌，从小爱诗文，相貌艳丽，可谓才貌双全翩若惊鸿，婉若游龙，荣曜秋菊，华茂春松彷佛兮若轻云之蔽月，飘廷兮若流风之回雪，远而望之，皎若太阳升朝霞；迫而察之，灼若芙蓉出渌波真是形象鲜明，色彩艳丽，令人目不暇接。',
		'超凡脱俗的绝世美人，容貌秀美若仙、生性清冷一生爱穿白衣，当真如风拂玉树，雪裹琼苞，白锦无纹香烂漫，玉树琼苞堆雪。静夜沉沉，浮光霭霭，冷浸溶溶月武功轻灵飘逸，于婀娜妩媚中击敌制胜在终南山修炼仙门绝学，一直在寻找相守一生的伴侣。',
		'她翩然而来，有若雪山的仙子，恍如隔世的精灵，美得清澈灵动，美得倾世绝尘秋波流转之间，容光惊世，让天下佳丽黯然失色只如粪土。率性而为的小公主，将武林搅得天翻地覆博古通今，精通琴棋书画、厨艺了得。冰雪聪明的一朵解语花，她会用一千种方法告诉你她爱你、在乎你',
		'任性刁蛮，王朝说一不二的公主任何人如果拿鄙视女人的眼光来看她，必将自尝苦果为爱痴狂的仙子，对清心寡欲的仙规视若无睹，能为爱情不顾一切。'
	];
	var natural = [34,35,34,36];
	var natural2 = [40,40,40,42];
	var savvy = [10720,10932,10329,11183]
	var savvy2 = [12000,11825,12000,12558]
	var xhNumSkinIndex = 0;
	var num = $(".xh-skin-bpic li.active").index()+1;
	$('.xh-skin-bpic li').bind({
		mouseenter: function(e) {
			var index = $(this).index()+1;
			if(num != index) {
				$(this).find('img').attr("src","https://img.chuangyunet.net/static/web/xhdtsgs/images/xh-skin-img"+index+".png")
			}
		},
		mouseleave: function(e) {
			var index = $(this).index()+1;
			if(num != index) {
				$(this).find('img').attr("src","https://img.chuangyunet.net/static/web/xhdtsgs/images/xh-skin-an-img"+index+".png")
			}
		},
		click : function(e){
			xhNumSkinIndex = $(this).index();
			var index = $(this).index()+1;
			var numbel = num-1;
			$('.xh-skin-bpic li').eq(numbel).find('img').attr("src","https://img.chuangyunet.net/static/web/xhdtsgs/images/xh-skin-an-img"+num+".png")
			num = index
			$(this).siblings().removeClass('active')
			$(this).addClass('active');
			$(this).find('img').attr("src","https://img.chuangyunet.net/static/web/xhdtsgs/images/xh-skin-img"+index+".png")

			$('.xh-detail-left h4').text(numtwo[xhNumSkinIndex])
			$('.xh-detail-left p').text(cententNum[xhNumSkinIndex])
			$('.xh-natural').text(natural[xhNumSkinIndex])
			$('.xh-savvy').text(savvy[xhNumSkinIndex])
			if(xhNumSkinIndex == 0) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-two-img1')
				$('#xh-element').removeClass().addClass('xh-element-two1')
				$('.xh-bust-icon1').width(125)
				$('.xh-bust-icon2').width(110)
				$('.xh-bust-icon3').width(140)
			} else if(xhNumSkinIndex == 1) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-wood-img1')
				$('#xh-element').removeClass().addClass('xh-element-wood1')
				$('.xh-bust-icon1').width(140)
				$('.xh-bust-icon2').width(140)
				$('.xh-bust-icon3').width(110)
			} else if(xhNumSkinIndex == 2) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-square-img1')
				$('#xh-element').removeClass().addClass('xh-element-square1')
				$('.xh-bust-icon1').width(125)
				$('.xh-bust-icon2').width(125)
				$('.xh-bust-icon3').width(110)
			} else if(xhNumSkinIndex == 3) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-build-img1')
				$('#xh-element').removeClass().addClass('xh-element-build1')
				$('.xh-bust-icon1').width(125)
				$('.xh-bust-icon2').width(125)
				$('.xh-bust-icon3').width(125)
			}
		}
	})
	$('.xh-button-first').on("click",function(){
		$('.xh-natural').text(natural[xhNumSkinIndex])
		$('.xh-savvy').text(savvy[xhNumSkinIndex])
		if(xhNumSkinIndex == 0) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-two-img1')
				$('#xh-element').removeClass().addClass('xh-element-two1')
				$('.xh-bust-icon1').width(125)
				$('.xh-bust-icon2').width(110)
				$('.xh-bust-icon3').width(140)
			} else if(xhNumSkinIndex == 1) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-wood-img1')
				$('#xh-element').removeClass().addClass('xh-element-wood1')
				$('.xh-bust-icon1').width(140)
				$('.xh-bust-icon2').width(140)
				$('.xh-bust-icon3').width(110)
			} else if(xhNumSkinIndex == 2) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-square-img1')
				$('#xh-element').removeClass().addClass('xh-element-square1')
				$('.xh-bust-icon1').width(125)
				$('.xh-bust-icon2').width(125)
				$('.xh-bust-icon3').width(110)
			} else if(xhNumSkinIndex == 3) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-build-img1')
				$('#xh-element').removeClass().addClass('xh-element-build1')
				$('.xh-bust-icon1').width(125)
				$('.xh-bust-icon2').width(125)
				$('.xh-bust-icon3').width(125)
			}
	})
	$('.xh-button-last').on("click",function(){
		$('.xh-natural').text(natural2[xhNumSkinIndex])
		$('.xh-savvy').text(savvy2[xhNumSkinIndex])
		if(xhNumSkinIndex == 0) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-two-img2')
				$('#xh-element').removeClass().addClass('xh-element-two2')
				$('.xh-bust-icon1').width(125)
				$('.xh-bust-icon2').width(110)
				$('.xh-bust-icon3').width(160)
			} else if(xhNumSkinIndex == 1) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-wood-img2')
				$('#xh-element').removeClass().addClass('xh-element-wood2')
				$('.xh-bust-icon1').width(140)
				$('.xh-bust-icon2').width(160)
				$('.xh-bust-icon3').width(120)
			} else if(xhNumSkinIndex == 2) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-square-img2')
				$('#xh-element').removeClass().addClass('xh-element-square2')
				$('.xh-bust-icon1').width(140)
				$('.xh-bust-icon2').width(125)
				$('.xh-bust-icon3').width(140)
			} else if(xhNumSkinIndex == 3) {
				$('#xh-skin-big').removeClass().addClass('xh-skin-build-img2')
				$('#xh-element').removeClass().addClass('xh-element-build2')
				$('.xh-bust-icon1').width(160)
				$('.xh-bust-icon2').width(125)
				$('.xh-bust-icon3').width(160)
			}
	})
}

function XHprint () {
	var num = 0;
	$('.xh-print-nav li').on("click",function(){
		num = $(this).index();
		if(!$(this).find('i').hasClass("icon")) {
			$('.xh-print-nav').find('i').removeClass('icon')
			$(this).find('i').addClass('icon')
		}
		$('.xh-print-div ul').addClass('dn')
		$('.xh-print-div ul').eq(num).removeClass('dn')
	})
	
}


function XHweel () {
	var xhweelwidth = ($('body').width()-709)/2
	$('.xh-welfare-div').css({left:xhweelwidth})
	$('.xh-last-nav').on("click",function(){
		$('.xh-welfare-div').removeClass('dn')
		$('.xh-shade').removeClass('dn')
	})
	$('.xh-welfare-close').on("click",function(){
		$('.xh-welfare-div').addClass('dn')
		$('.xh-shade').addClass('dn')
	})
}


// 视频模块
function XHvideo () {
	var video = $('#video');
	var xhweelwidth = ($('body').width()-896)/2;
	$('.pop_video').css({left:xhweelwidth})
	$('.xh-play-btn').click(function(){
		video.html("<video id='video_id' class='vjs-default-skin vjs-big-play-centered' autoplay controls='controls' preload='none' width='896px' height='504px'  data-setup='{}'>"+
			"<source src='https://img.chuangyunet.net/static/web/xhdtsgs/1.mp4' type='video/mp4' />"+
			"</video> "
			)
		$('.xh-shade').show()
		$('.pop_video').show()
	})
	$('.pop_close').click(function(){
		video.html("")
		$('.xh-shade').hide()
		$('.pop_video').hide()
	})
}

$(document).ready(function(){
	init()
});