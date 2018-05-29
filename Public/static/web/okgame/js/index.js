var init = function(){
    GSswidth(); /* 轮播 */
    GSnavchang(); /* 头部导航切换 */
    GSchange(); //新闻列表分页 page效果
    // GSslide(); //游戏中心轮播
    GSrecuit(); //招贤纳士切换
}

function GSswidth () {
	//轮播
    var sWidth = $("#focus").width(); //获取焦点图的宽度（显示面积）
    var len = $("#focus ul li").length; //获取焦点图个数
    var index = 0;
    var picTimer;
    // $('#focus ul li img').width($(document.body).width())
    //以下代码添加数字按钮和按钮后的半透明条，还有上一页、下一页两个按钮
    var btn = "<div class='btnBg'></div><div class='btn'>";
    for(var i=0; i < len; i++) {    
        btn += "<span></span>";
    }
    btn += "</div><div class='preNext focusbtn pre'></div><div class='preNext focusbtn next'></div>";
    $("#btnBg").append(btn);
    $("#btnBg .btnBg").css("opacity",0.8);
    //$("#btnBg .btn span").css("backgroundColor","#1ecbfd");

    //为小按钮添加鼠标滑入事件，以显示相应的内容
    $("#btnBg .btn span").css("opacity",1).mouseenter(function() {
        index = $("#btnBg .btn span").index(this);
        console.log(index)
        showPics(index);
    }).eq(0).trigger("mouseenter");

    //点击左右按钮，显示相应内容
    $("#btnBg .pre").on("click",function(){
    	var ind = $('.btn span.on').index()-1;
    	if(ind < 0) {
    		ind = len-1
    	}
    	showPics(ind);
    })
    $("#btnBg .next").on("click",function(){
    	var ind = $('.btn span.on').index()+1;
    	if(ind > len-1) {
    		ind = 0
    	}
    	showPics(ind);
    })
    $("#btnBg .preNext").hover(function() {
        clearInterval(picTimer);
    });

    //本例为左右滚动，即所有li元素都是在同一排向左浮动，所以这里需要计算出外围ul元素的宽度
    $("#focus ul").css("width",1920 * (len));
    
    //鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
    $("#focus").hover(function() {
        clearInterval(picTimer);
    },function() {
        picTimer = setInterval(function() {
            showPics(index);
            index++;
            if(index == len) {index = 0;}
        },5000); //此4000代表自动播放的间隔，单位：毫秒
    }).trigger("mouseleave");
    
    //显示图片函数，根据接收的index值显示相应的内容
    function showPics(index) { //普通切换
        var lenwid = -(1920-sWidth)/2
        var nowLeft = -index*1920+lenwid; //根据index值计算ul元素的left值
        $("#focus ul").stop(true,false).animate({"left":nowLeft},300); //通过animate()调整ul元素滚动到计算出的position
        $("#btnBg .btn span").removeClass("on").eq(index).addClass("on"); //为当前的按钮切换到选中的效果
        $("#btnBg .btn span").stop(true,false).animate({"opacity":"1"},300).eq(index).stop(true,false).animate({"opacity":"1"},300); //为当前的按钮切换到选中的效果
    }
}

function GSnavchang () {

	$('.gs-nav li').on("click",function(){
        if($('.gs-nav li').hasClass("active")) {
            $('.gs-nav li').removeClass("active");
            $(this).addClass("active");
        }
    })
    var index = $(".gs-nav .active").index();
    $('.gs-nav li').hover(function(){
        if($(this).index() != index ) {
            $(this).addClass("active");
        }
    },function(){
        if($(this).index() != index ) {
            $(this).removeClass("active");
        }
    });
    $('.ggao-list li').on("click",function(){
    	$('.ggao-list li').removeClass("active");
        $(this).addClass("active");
        var left = -529*$(this).index()
        $('.ggao-img ul').animate({
            left: left + "px"
        })
    })
}

function GSchange () {
    var firstIndex =  $('.gs-order-list').find('li').length;
    if(firstIndex <= 15){
        $(".dttab-page").append(" <span>1</span>"); 
        $('.gs-coop-code').height($('.gs-coop-box').height())
    } else {
        var RXfirstpage = Math.ceil(firstIndex/15);
        $('.dttab-setting-img2').addClass('dttab-setting-color2') //改变图标
        $('.gs-order-list').eq(0).find('li').hide()
        for ( var i=0;i<15;i++){
            $('.gs-order-list').eq(0).find('li').eq(i).show();
        }
        for(var i = 0;i<RXfirstpage;i++){
            var nums = i+1;
            $(".dttab-page").append(" <span>" +nums+ "</span>"); 
        }
        $(".dttab-page span").first().addClass('dttab-cursor');
        $('.gs-coop-code').height($('.gs-coop-box').height())
        $('.dttab-page span').click(function(){ //点击数字 分页
            $(this).addClass('dttab-cursor').siblings().removeClass('dttab-cursor');
            var ind = $(this).index() * 15;
            $('.gs-order-list').eq(0).find('li').hide()
            for ( var i=ind;i<ind+15;i++){
                $('.gs-order-list').eq(0).find('li').eq(i).show();
            }
            var letIndex = RXfirstpage-1
            if($(this).index() == 0) { //判断page样式
                $('.dttab-setting-img1').removeClass('dttab-setting-color1')
                $('.dttab-setting-img2').addClass('dttab-setting-color2')
            } else if($(this).index() < letIndex ) {
                $('.dttab-setting-img1').addClass('dttab-setting-color1')
                $('.dttab-setting-img2').addClass('dttab-setting-color2')
            } else if (letIndex == $(this).index()) {
                $('.dttab-setting-img1').addClass('dttab-setting-color1')
                $('.dttab-setting-img2').removeClass('dttab-setting-color2')
            }
            $('.gs-coop-code').height($('.gs-coop-box').height())
        })
        $('.dttab-icon').click(function(){
            var dttabIndex = parseInt($('.dttab-cursor').html())
            var cursorIndex = $(".dttab-page span").length
            if($(this).index() == 1){
                var ind = (parseInt($('.dttab-cursor').html())-2)*15
                var index = $('.gs-order-list').eq(0).find('li').length;
                if (dttabIndex >1 ) {
                    $('.gs-order-list').eq(0).find('li').hide()
                    for ( var i=ind;i<ind+15;i++){
                        if(i<index){
                            $('.gs-order-list').eq(0).find('li').eq(i).show();
                        } else {
                            return
                        }
                    }
                   var text = parseInt($('.dttab-cursor').html())-1;
                   if(text > 1){
                        $(".dttab-page span").removeClass('dttab-cursor')
                        $(".dttab-page span").eq(text-1).addClass('dttab-cursor')
                        $('.dttab-setting-img1').addClass('dttab-setting-color1')
                        $('.dttab-setting-img2').addClass('dttab-setting-color2')
                    }else if (text = 1) {
                        $(".dttab-page span").removeClass('dttab-cursor')
                        $(".dttab-page span").eq(text-1).addClass('dttab-cursor')
                        $('.dttab-setting-img1').removeClass('dttab-setting-color1')
                        $('.dttab-setting-img2').addClass('dttab-setting-color2')
                    }
                }
            } else if ($(this).index() == 2) {
                var ind = (parseInt($('.dttab-cursor').html()))*15
                var index = $('.gs-order-list').eq(0).find('li').length;
                if(dttabIndex < cursorIndex) {
                    $('.gs-order-list').eq(0).find('li').hide()
                    for ( var i=ind;i<ind+15;i++){
                        if(i<index){
                            $('.gs-order-list').eq(0).find('li').eq(i).show();
                        }
                    }
                   var text = parseInt($('.dttab-cursor').html())+1;
                   if(text < cursorIndex){
                        $(".dttab-page span").removeClass('dttab-cursor')
                        $(".dttab-page span").eq(text-1).addClass('dttab-cursor')
                        $('.dttab-setting-img1').addClass('dttab-setting-color1')
                        $('.dttab-setting-img2').addClass('dttab-setting-color2')
                    }else if (text === cursorIndex) {
                        $(".dttab-page span").removeClass('dttab-cursor')
                        $(".dttab-page span").eq(text-1).addClass('dttab-cursor')
                        $('.dttab-setting-img1').addClass('dttab-setting-color1')
                        $('.dttab-setting-img2').removeClass('dttab-setting-color2')
                    }
                }
            }
            $('.gs-coop-code').height($('.gs-coop-box').height())
        })
    }
}

// function GSslide () {
//     $('#marquee').bxSlider({ 
//       mode:'horizontal', //默认的是水平 
//       displaySlideQty:1,//显示li的个数 
//       infiniteLoop: true,//无限循环
//       moveSlideQty: 4,//移动li的个数 
//       auto: true, 
//       controls: false,//是否隐藏左右按钮 
//       pause: 2000,
//       autoHover: true,
//       autoControls: false,
//       pager:true,
//     }); 
// }

function GSrecuit () {
    // $('.gs-recuit-nav li').click(function(){
    //     var flag = $(this).hasClass('active')
    //     if(!flag){
    //         $(this).addClass('active').siblings().removeClass('active')
    //     }
    //     $('.gs-recuit-li').eq($(this).index()-1).addClass('dn').siblings().removeClass('dn')
    //     $('.gs-coop-code').height($('.gs-coop-box').height())
    // })
    $('.gs-recruit-sild').click(function(){
        // $('.gs-recuit-nav li').eq(1).addClass('active').siblings().removeClass('active')
        // $('.gs-recuit-li').eq(0).addClass('dn').siblings().removeClass('dn')
        var flag = $(this).hasClass('active')
        if(!flag){
            $('.gs-recruit-coop li div').removeClass('active')
            $(this).addClass('active')
            $('.gs-recuit-li ul li').eq($(this).parent().index()).removeClass('dn').siblings().addClass('dn')
        }
        $('.gs-coop-code').height($('.gs-coop-box').height())
        if($('.gs-coop-left').height() < 830) {
            $('.gs-coop-box').height(830)
            $('.gs-coop-code').height(830)
        } else {
            $('.gs-coop-box').height($('.gs-coop-left').height())
            $('.gs-coop-code').height($('.gs-coop-box').height())
        }

    })
}


$(document).ready(function(){
    init()
});