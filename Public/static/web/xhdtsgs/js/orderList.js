var init = function(){
	Jychange();/* 内容列表切换 */
    RXclose(); /* 左侧客服left */
    XHweel(); /*福利中心*/
}
function Jychange (){
	var RXchangetabsIndex = 0;
	$('#dttab-title span').click(function(){
        $('.dttab-icon').eq(0).removeClass('dttab-setting-color1')
        $('.dttab-icon').eq(1).addClass('dttab-setting-color2')
        $('.dttab-page').html('');
        $(this).addClass("selected").siblings().removeClass("selected");
        $("#dttab-content > div").hide().eq($('#dttab-title span').index(this)).show();
        RXchangetabsIndex = $(this).index();
        $('#dttab-title i').stop(true,false).animate({left:RXchangetabsIndex*125+8},300)
        var RXlilength = $('.dtnews_ul').eq(RXchangetabsIndex).find('li').length;
        if(RXlilength <= 6){
            $('.dttab-setting').hide();
        } else {
            $('.dttab-setting').show();
            var RXdttabpage = Math.ceil(RXlilength/6);
            for(var i = 0;i<RXdttabpage;i++){
                var num = i+1;
                $(".dttab-page").append(" <span>" +num+ "</span>"); 
            }
            $('.dtnews_ul').eq(RXchangetabsIndex).find('li').hide()
            for ( var i=0;i<6;i++){
                $('.dtnews_ul').eq(RXchangetabsIndex).find('li').eq(i).show();
            }


            $(".dttab-page span").first().addClass('dttab-cursor');
            $('.dttab-page span').click(function(){
                $(this).addClass('dttab-cursor').siblings().removeClass('dttab-cursor');
                $('.dtnews_ul').eq(RXchangetabsIndex).find('li').hide()
                var ind = $(this).index() *6
                for ( var i=ind;i<ind+6;i++){
                    $('.dtnews_ul').eq(RXchangetabsIndex).find('li').eq(i).show();
                }
            })
        }
        // var RXpathIndex = $(this).index()
        // var dtnewsindex = $('.dtnews_ul').eq(RXpathIndex).find('li').length;
        // var RXpathpage = Math.ceil(dtnewsindex/6);
        // $('.dttab-icon').click(function(){
        //     var iconNum = parseInt($('.dttab-cursor').text())
        //     var dtindex = $(this).index();
        //     if(dtindex == 2 && iconNum < RXpathpage) {
        //         var ind = iconNum * 6;
        //         $('.dtnews_ul').eq(0).find('li').hide()
        //         for ( var i=ind;i<ind+6;i++){
        //             $('.dtnews_ul').eq(0).find('li').eq(i).show();
        //         }
        //         $('.dttab-page span').eq(iconNum).addClass('dttab-cursor').siblings().removeClass('dttab-cursor');
                

        //         iconNum++
        //     } else if(dtindex == 1 && iconNum >1){
        //         iconNum--
        //         var ind = iconNum * 6;
        //         $('.dtnews_ul').eq(0).find('li').hide()
        //         for ( var i=ind-6;i<ind;i++){
        //             $('.dtnews_ul').eq(0).find('li').eq(i).show();
        //         }
        //         $('.dttab-page span').eq(iconNum-1).addClass('dttab-cursor').siblings().removeClass('dttab-cursor');
                
        //     }
        //     if(iconNum <= 1) {
        //         $('.dttab-icon').eq(0).removeClass('dttab-setting-color1')
        //         $('.dttab-icon').eq(1).addClass('dttab-setting-color2')
        //     } else if(1 < iconNum && iconNum < RXpathpage) {
        //         $('.dttab-icon').eq(0).addClass('dttab-setting-color1')
        //         $('.dttab-icon').eq(1).addClass('dttab-setting-color2')
        //     } else if (iconNum >= RXpathpage) {
        //         $('.dttab-icon').eq(0).addClass('dttab-setting-color1')
        //         $('.dttab-icon').eq(1).removeClass('dttab-setting-color2')
        //     }
        // })

    });
    var firstIndex =  $('.dtnews_ul').first().find('li').length;
    if(firstIndex <= 6){
        $('.dttab-setting').hide();
    } else {
        $('.dttab-setting').show();
        var RXfirstpage = Math.ceil(firstIndex/6);
        $('.dtnews_ul').eq(0).find('li').hide()
        for ( var i=0;i<6;i++){
            $('.dtnews_ul').eq(0).find('li').eq(i).show();
        }
        for(var i = 0;i<RXfirstpage;i++){
            var nums = i+1;
            $(".dttab-page").append(" <span>" +nums+ "</span>"); 
        }

        $(".dttab-page span").first().addClass('dttab-cursor');
        $('.dttab-page span').click(function(){
            $(this).addClass('dttab-cursor').siblings().removeClass('dttab-cursor');
            var ind = $(this).index() * 6;
            $('.dtnews_ul').eq(0).find('li').hide()
            for ( var i=ind;i<ind+6;i++){
                $('.dtnews_ul').eq(0).find('li').eq(i).show();
            }
        })
    }

    // 左右箭头切换页数
    $('.dttab-icon').click(function(){
        var iconNum = parseInt($('.dttab-cursor').text())
        var dtindex = $(this).index();
        if(dtindex == 2 && iconNum < RXfirstpage) {
            var ind = iconNum * 6;
            $('.dtnews_ul').eq(0).find('li').hide()
            for ( var i=ind;i<ind+6;i++){
                $('.dtnews_ul').eq(0).find('li').eq(i).show();
            }
            $('.dttab-page span').eq(iconNum).addClass('dttab-cursor').siblings().removeClass('dttab-cursor');
            

            iconNum++
        } else if(dtindex == 1 && iconNum >1){
            iconNum--
            var ind = iconNum * 6;
            $('.dtnews_ul').eq(0).find('li').hide()
            for ( var i=ind-6;i<ind;i++){
                $('.dtnews_ul').eq(0).find('li').eq(i).show();
            }
            $('.dttab-page span').eq(iconNum-1).addClass('dttab-cursor').siblings().removeClass('dttab-cursor');
            
        }
        if(iconNum <= 1) {
            $('.dttab-icon').eq(0).removeClass('dttab-setting-color1')
            $('.dttab-icon').eq(1).addClass('dttab-setting-color2')
        } else if(1 < iconNum && iconNum < RXfirstpage) {
            $('.dttab-icon').eq(0).addClass('dttab-setting-color1')
            $('.dttab-icon').eq(1).addClass('dttab-setting-color2')
        } else if (iconNum >= RXfirstpage) {
            $('.dttab-icon').eq(0).addClass('dttab-setting-color1')
            $('.dttab-icon').eq(1).removeClass('dttab-setting-color2')
        }
    })
}

function RXclose () {
    $('.rx-close').on("click",function(){
        if(!$(this).hasClass("sideactive")){
            $('.side-nav-left').animate({right:"-186px"},function(){
                $('.rx-close').addClass('sideactive')
                $('.xh-smallicon').addClass('xh-smallicon-img')
            })
        } else {
            $('.side-nav-left').animate({right:"0px"},function(){
                $('.rx-close').removeClass('sideactive')
                $('.xh-smallicon').removeClass('xh-smallicon-img')
            })
        }
        
    })


    //监听滚动条事件
    $(window).scroll(function () {
        if ($(window).scrollTop() >= $('.xh-mian').height() -250) {
            $('.side-nav-left').show();
        } else {
            $('.side-nav-left').hide();
        }
    });
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
$(document).ready(function(){
	init()
});