$(document).ready(function() {
	
	if( $("body").hasClass('two_columns') || $("body").hasClass('three_columns') )
	{
		$("#float_box_navi,#float_box_slideshow").remove();
	}
	
	$(".float_box_button_link").click(function(){
	   	$('.float_box_slider').sliderIPS({
			prevId:'#float_box_p',
			nextId:'#float_box_n'
		});
		
		var proposition = $("#float_box_proposition");
		
		$(".float_box_button_link span").toggleClass("icon_slide");
		
		proposition.animate({
			"height": ( proposition.height() == 0 ? 124 : 0 )
		});
	});
	
	
	$("#float_box_navi .slide_prev").click(function(){
		scrollItem('previous');
	});
	$("#float_box_navi .slide_next").click(function(){
		scrollItem('next');
	});
});

function clikSlide()
{
	var slide = $("#float_box_slideshow").find('button');
	
	if( slide.hasClass("active") )
	{
		clearTimeout(timeout);
	}
	else
	{
		slideshow();
	}
	
	slide.toggleClass("active");
};
function slideshow()
{
	scrollItem('next');
	timeout = setTimeout( 'slideshow()', 6000 ); 
}

$.fn.sliderIPS = function (options) {
	var defaults = {			
		auto	:	true,
		prevId	:	'prevBtn',
		nextId	:	'nextBtn'	
	}; 
	var options = $.extend(defaults, options); 
	
    return this.each(function () {
        var $mainContainer = $('> div', this).css('overflow', 'hidden'),
            $slider = $mainContainer.find('> ul'),
            $items = $slider.find('> li'),
            $single = $items.filter(':first'),
            singleWidth = $single.outerWidth(), 
            visible = Math.ceil($mainContainer.innerWidth() / singleWidth),
            currentPage = 1,
            pages = Math.ceil($items.length / visible);            

        $items = $slider.find('> li'); 
        
        $mainContainer.scrollLeft(singleWidth * visible);
        
        function slidePage(page) {
            var dir = page < currentPage ? -1 : 1,
                n = Math.abs(currentPage - page),
                left = singleWidth * dir * visible * n;
            $mainContainer.filter(':not(:animated)').animate({
                scrollLeft : '+=' + left
            }, 500, function () {
                if (page == 0) {
                    $mainContainer.scrollLeft(singleWidth * visible * pages);
                    page = pages;
                } else if (page > pages) {
                    $mainContainer.scrollLeft(singleWidth * visible);
                    page = 1;
                } 

                currentPage = page;
            });                
            return false;
        }
        $(options.prevId).click(function () {
			return slidePage(currentPage - 1);                
        });
        $(options.nextId).click(function () {
            return slidePage(currentPage + 1);
        });
        $(this).bind('goto', function (event, page) {
            slidePage(page);
        });
    });  
};