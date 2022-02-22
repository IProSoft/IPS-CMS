$(document).ready(function(){
	
	if( $.cookie('widget_best_files_disable') !== 'true' && typeof ips_config.widget_best_files != 'undefined' )
	{
		var i = 1,
			container = $('#best-files'),
			count = ( jQuery.inArray( ips_config.version, [ 'bebzol', 'gag', 'vines' ] ) > -1 ? 5 : 4 ),
			parsedFB = [],
			widgetBestFilesTimer = 0,
			tmp = '';
		
		$('.best-files-list', container ).addClass( 'files_count_' + count );
		
		
		
		/*
		* Kliknięcie tylko jeśli więcej niż count elementy
		*/
		
		if( $('.best-files-item', container ).size() > count )
		{
			$('.best-files-item', container ).each(function( index ){
				if( index%count == 0 )
				{
					tmp += '<li rel="' + i + '"></li>';
					i++;
				}
				$(this).attr('rel', index );
			});
			
			$('.best-files-control', container ).append( tmp );
		
			$( '.best-files-control li', container ).on( ips_click, function (e)
			{
				if ( ips_config.widget_best_files.interval && e && widgetBestFilesTimer )
				{
					clearInterval(widgetBestFilesTimer);
					widgetBestFilesTimer = 0;
				}
				
				$('.best-files-control li', container ).removeClass('on-screen');
				
				$(this).addClass('on-screen');
				
				var rel = $(this).attr('rel'),
					s = parseInt( rel - 1 ) * count,
					e = parseInt( rel ) * count;
				
				$('.best-files-item', container ).hide();
				
				for ( var i = s; i < e; i++ )
				{
					var item = $('.best-files-item[rel="' + i + '"]', container );
					
					item.show();
					
					if( $.inArray( i, parsedFB) < 0 )
					{
						IpsApp._FB.afterInit( function(){
							FB.XFBML.parse( item.get(0) );
							item.trigger('mouseover');
							parsedFB.push( i );
						});
					}
				}
				
				if ( ips_config.widget_best_files.interval && e && widgetBestFilesTimer == 0 )
				{
					widgetBestFilesTimer = setInterval( widgetBestFiles, ips_config.widget_best_files.interval );
				}
			});
			
			var widgetBestFiles = function ()
			{
				var controlEl = '.best-files-control li';
				if ( $( controlEl + '.on-screen', container ).next('li') )
				{
					$( controlEl + '.on-screen', container ).next('li').trigger('click');
				}
				else
				{
					$(controlEl ).trigger('click');
				}
			};
			
			if ( ips_config.widget_best_files.interval )
			{
				widgetBestFilesTimer = setInterval( widgetBestFiles, ips_config.widget_best_files.interval );
			}
			$('.best-files-control li', container ).first().trigger('click');
		}
		else
		{
			console.log($('.best-files-item', container ));
			$('.best-files-item', container ).show();
		}
		
		
		
		/**
		* Zamknięcie widgetu i zapisanie ciastka
		*/
		$('#widget_best_files_disable').on('click', function () {

			$.cookie('widget_best_files_disable', 'true', { expires: ips_config.widget_best_files.timer + 'm', path: '/' } );
			
			if ( widgetBestFilesTimer )
			{
				clearInterval( widgetBestFilesTimer );
				widgetBestFilesTimer = 0;
			}
			
			container.slideUp( 1000, function(){ 
				$(this).remove();
			} );
		});
	}
});