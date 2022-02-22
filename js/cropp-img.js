var current_index = 0;

if( covers.length > 0 )
{
	$('.cropp-next').fadeIn();
}


$.each( covers , function(val) { 
	var img = new Image();
	img.src = this['upload_image_url'];
	img.onload = function() {
	
	}
});	

var picture = $("#cropp-img .guillotine-frame > img");

$('#cropp-img span.nav').on( 'click', function (e) {
	
	current_index = $(this).hasClass('cropp-prev') ? current_index - 1 : current_index + 1;
	
	if( current_index + 1 >= covers.length )
	{
		$('.cropp-next').fadeOut();
	}
	else if( !$('.cropp-next').is(':visible'))
	{
		$('.cropp-next').fadeIn();
	}
	
	if( current_index - 1 < 0 )
	{
		$('.cropp-prev').fadeOut();
	}
	else if( !$('.cropp-prev').is(':visible') )
	{
		$('.cropp-prev').fadeIn();
	}

	$('.guillotine-wrapper').animate({
		opacity: 0
	}, 100, function () {
		picture.guillotine('remove');
		picture.attr( 'src', covers[current_index].upload_image_url );
		load_cropp();
	});
});

function load_cropp(){
	
	$('input[name="upload_image"]').val( covers[current_index].upload_image );
	
	$('.guillotine-frame').css({
		height: '150px',
		width: '216px',
	});

	picture.guillotine({
		width: 216,
		height: 150,
		onChange: function(data, action){
			$('input[name="image_cropp"]').val( $.toJSON( data ) );
		}
	});
	
	picture.guillotine( 'fit' );
	
	$('.guillotine-wrapper').animate({
		opacity: 1.0
	}, 100);
}
head.load(["/libs/jQueryCropp/jquery.guillotine.js", "//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css","/libs/jQueryCropp/jquery.guillotine.css"], function(){
	picture.guillotine('remove');
	load_cropp();
});
jQuery('#guillotine_controls span').on( 'click', function (e) {
	e.preventDefault();
	picture.guillotine( $(this).attr('data-action') );
});