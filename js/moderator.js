head.load( 'libs/Select2/css/select2.min.css' );
head.load( [
	'libs/Select2/js/select2.min.js', 
	'/libs/Select2/js/i18n/' + ips_config.locale.shorten + '.js'
], function(){
	$('[name="file_category"]', '.moderator').select2({
		language: ips_config.locale.shorten,
		tags: false,
		placeholder: 'Zmień kategorię',
		minimumResultsForSearch: Infinity
	}).on( 'change', function(){
		
		var form = $(this).parents('form');
		
		IpsApp._formSpinner.add( form );
				
		IpsApp._ajax( 'ajax/moderation/' + $(this).attr('data-id'), {
			action: 'category_change',
			category_id: $(this).val(),
		}, 'POST', 'json', false, function( response ){
			console.log(response);IpsApp._formSpinner.response( form, response, 3 );
		});
	});
});

$(document).ready(function() {
	$('.moderation-panel').on( 'click', 'a', function(){
		var el = $(this),
			panel = el.parent(),
			action = el.attr('data-action');
		
		if( action.length == 0 )
		{
			return true;
		}
		
		if( $(this).hasClass('confirmation') )
		{
			return IpsApp._confirm(function(){
				el.removeClass('confirmation').trigger('click');
			})
		}
		
		IpsApp._formSpinner.add( panel );
		
		IpsApp._ajax( 'ajax/moderation/' + $(this).attr('data-id'), { 
			action: $(this).attr('data-action')
		}, 'POST', 'json', false, function( response ){
			
			IpsApp._formSpinner.response( panel, response, 3 );
			
			IpsApp._timeout( function(){
				
				if( ips_config.file_id && typeof response.url !== 'undefined' )
				{
					return window.location.href = response.url
				}
				
				if( ( ips_config.file_id || ips_config.ips_action == 'moderator' ) && typeof response.remove !== 'undefined' )
				{
					var panel = el.parents('.moderation-panel');
					panel.slideUp( 'fast', function(){
						var remove = panel.parents('.table-block');
						if( remove.length == 0 )
						{
							remove = panel.prev('article');
						}
						remove.slideUp('fast', function(){
							$(this).remove();
						});
						$(this).remove();
					});
				}
			}, 3 );
		});
		
		return false;
	});
});
/* function mod( id, action, extra )
{
	var div = $("div#mod_" + id );
	
	div.animate({
		opacity: 0.25,
		height: 'toggle'
	}, 1000, function(){});
  
	var response = IpsApp._ajax( '/ajax/moderation/' + id, { 
		action: action
	}, 'POST' );
	
	if( typeof response.error !== 'undefined' )
	{
		var msg = response.error;
	}
	else if( typeof response.content !== 'undefined' )
	{
		var msg = response.content;
	}
	
	setTimeout(function(){
		div.html( msg );
		div.animate({
			opacity: 0.90,
			height: '50px'
		}, 500, function(){
			div.slideDown();
			setTimeout(function(){
				div.slideUp();
			}, 3000 );
		});
	}, 1000 );
} */
				
