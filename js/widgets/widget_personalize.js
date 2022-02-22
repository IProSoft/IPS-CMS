var personalize_cookie = $.cookie('user_personalize'),
	personalize_form = $('#ajax-personalize-form');

if( personalize_cookie )
{
	personalize_cookie = JSON.parse( personalize_cookie );

	if( personalize_cookie.auto_animated == 1 || personalize_cookie.auto_video == 1 )
	{
		$('.file-container.file-video,.file-container.file-animation').bind('inview', function(event, isInView, visiblePartX, visiblePartY) {
			
			var $this = $(this),
				gif = $(this).find('.gif_player');
			
			if( personalize_cookie.auto_video == 1 )
			{	
				var iframe = $(this).find('iframe'),
					player_ajax = $(this).find('.video_player'),
					player_jquery = $(this).find('.controlDiv');
			
				if( player_ajax.length > 0 )
				{
					var parent = player_ajax.parent();
					if (isInView)
					{
						if( parent.find('iframe').length == 0 )
						{
							player_ajax.trigger( 'click' );
						}
					}
					else
					{
						player_ajax.show();
						parent.find( 'iframe' ).remove();
					}
				}
				else if( iframe.length > 0 )
				{
					var attr = !iframe.hasClass('async_iframe') ? 'src' : 'data-src' ;

					if (isInView)
					{
						if( !iframe.hasClass('p-playing'))
						{
							iframe.attr( attr, iframe.attr( attr ) + '&autoplay=1' ).addClass('p-playing');
						}
					}
					else
					{
						iframe.attr( attr, iframe.attr( attr ).replace('\&autoplay\=1', '') ).removeClass('p-playing');
					}
				}
				else if( player_jquery.length > 0 )
				{
					player_jquery.trigger( 'click' );
				}
			}
			
			if( personalize_cookie.auto_animated == 1 && gif.length > 0 )
			{
				if( !gif.hasClass('p-playing') )
				{
					gif.trigger( 'click' ).addClass('p-playing');
				}
			}
		});
		
	}
}
/**
* Personalize form on/off
*/
personalize_form.on( 'click', 'a', function ( event ) {
	event.preventDefault();
	
	var form = $(this).closest("form");
	
	IpsApp._ajax( '/ajax/user_personalize/', ( $(this).hasClass('btn-reset') ? { reset : true } : form.serialize() ), 'POST', 'html', false, function( response ){
		form.slideUp();
		form.parent().find('.widget-personalize-title').addClass('off');
	});
});

personalize_form.parent().find('.widget-personalize-title').on( 'click', function(){
	if( $(this).hasClass('off') )
	{
		$(this).removeClass('off').parent().find('form').slideDown();
	}
	else
	{
		$(this).addClass('off').parent().find('form').slideUp();
	}
});

if( $('.toggle_chbx').length > 0 )
{
	$('.toggle_chbx span').on( 'click', function ( event ) {
		var cnt = $(this).parent();
		return cnt.toggleClass('on').find('input').val( ( cnt.hasClass('on') ? 1 : 0 ) );
	});
}