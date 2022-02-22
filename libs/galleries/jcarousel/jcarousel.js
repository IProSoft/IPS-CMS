head.load( "/libs/galleries/jcarousel/jquery.jcarousel-core.min.js", function(){
	
	var jcarousel = $('.jcarousel').jcarousel();
	var jcarousel_img = $('.pin-image-block img');
	var parent = jcarousel_img.parent();
		
	$(window).bind('paddPin', function(){
		parent.css( { width: jcarousel_img.width(), height: jcarousel_img.height() } )
	});	
	
	$('.jcarousel-control-prev').on('jcarouselcontrol:active', function() {
		$(this).removeClass('inactive');
	}).on('jcarouselcontrol:inactive', function() {
		$(this).addClass('inactive');
	}).jcarouselControl({
		target: '-=1'
	});

	$('.jcarousel-control-next').on('jcarouselcontrol:active', function() {
		$(this).removeClass('inactive');
	}).on('jcarouselcontrol:inactive', function() {
		 $(this).addClass('inactive');
	}).jcarouselControl({
		target: '+=1'
	});
	
	
	$('.jcarousel li a').on('click', function() {
		var self = $(this);
		var thumb = self.find('img');
		IpsApp._preloader.load( self );
		var img = new Image;
		img.src = this.href;
		thumb.addClass('half-transparent');
		$(img).load(function() {
			jcarousel_img.animate({opacity: 0.5}, 150, function() {
				jcarousel_img.removeAttr('width').removeAttr('height');
				jcarousel_img.attr('src', img.src).animate({opacity: 1}, 150, function() {
					IpsApp._preloader.remove( self );
					thumb.removeClass('half-transparent');
				});
			})
		});
		return false;
	});
	
});
