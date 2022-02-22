/**
 * Modal window
*/
IPSconf = {
	modal: '#ips-modal .ips-modal',
	modal_height : 0,
	loader : '<div class="ias_loader"><div class="loading spinner"></div></div>',
	called_from_list : false
};
(function( $ ){
	
	$.fn.moduleOverflow = function( callback, url ) {
		
		if( typeof url === 'undefined' )
		{
			var element = $(this).clone();
		}
		else
		{
			console.log(url);
			var element = doTCompile( { 
				compile : url.template,
				modal_content : url.data
			} );
		}
		
		
		var module = $('<div/>').addClass('module-fixed').append( element.show().css( 'top', '-100%') );
		
		module.find('.ips-upload-file').createUpload();
		
		module.prepend( $('<div/>').addClass('modal-backdrop fade shadow in') );
		module.appendTo('body').addClass('in');
		
		element.animate({ "top": "0%" }, "fast");
		
		module.find('.module-close').on('click', function(){
			callback();
			element.animate({ "top": "-100%" }, "fast", function(){
				module.remove();
			})
		});
		
		return module;
	};
	
	$.fn.modalAlert = function( data, destroy_modal ) {
		
		if ( $("#ips-modal-alert.ips-modal-container").length > 0 )
		{
			$("#ips-modal-alert.ips-modal-container").fadeOut('fast');
		}
		
		var form_name = "alertForm";
		
		if( data.modal_buttons )
		{
			var form_name = "alertFormWithoutButton";
		}

		var response = ips_storage( {
			name : form_name,
			url : '/ajax/pinit/alert',
			data : { 
				form_name :form_name
			},
			compile: false
		}, 'POST' );

		if( response.modal_content )
		{
			if( $('#ips-modal-alert').length !== 0 )
			{
				$('#ips-modal-alert').fadeOut('slow', function(){
					$(this).remove();
					modalAlertContent( data, response );
				});
			}
			else
			{
				modalAlertContent( data, response );
			}
		}
		else
		{
			console.log('Modal Alert error');
		}
		
		if( typeof data.modal_wait == 'undefined' )
		{
			modalAlertHide( 5000, 1000 );
		}
	},
	modalAlertHide = function( timeout_1, timeout_2 )
	{
		setTimeout(function(){
			$('#ips-modal-alert .ips-modal-close').trigger('click');
			setTimeout(function(){
				$('#ips-modal-alert').fadeOut('slow');
			}, timeout_2 );
			$('body').trigger('ips-alert-destroy');
		}, timeout_1 );
	},
	modalAlertContent = function( data, response )
	{

		var template = doT.template( response.modal_content );

		$('body').append( template( {
			title: ( typeof data.modal_info_title == 'string' ? data.modal_info_title : 'Woops' ),
			message: ( typeof data == 'string' ? data : ( /<[a-z][\s\S]*>/i.test( data.modal_info ) ? data.modal_info :  '<div class="modal-padding">' + data.modal_info + '</div>' )  )
		} ) );
		
		$("#ips-modal-alert.ips-modal-container .ips-modal").animate( {top:'6%', opacity:1}, 800, function() {});
	}
	
	$.fn.modalWindow = function( title, content, only_load ) {
		
		IPSconf.modal_resize_top = true;
		IPSconf.animate = false;
		
		/* Set no resize TOP for Pin show */
		if( $(this).hasClass('no-resize') )
		{
			IPSconf.modal_resize_top = false;
		}
		
		if( typeof only_load !== 'undefined' || calledFromModal( this ) )
		{
			IPSconf.animate = true;
			return setContent( $(this).attr('data-href'), getContent( content ) );
		}
		
		/*
		data-type="wide"
		*/
		/**
		* Initialize
		*/
		if( modalInit() )
		{
			/**
			* Set modal window title
			*/
			setTitle( getTitle( title ), $(this).attr('data-type') );
			
			/**
			* Show modal window
			*/
			modalSetSize( false );
			
			$(IPSconf.modal).on('shown.bs.modal', function () {
				console.log('shown.bs.modal');
				if ( $(IPSconf.modal).height() > $(window).height() )
				{
					IPSconf.modal_resize_top = false;
				}
				modalSetSize( true );
			});
			
			$(IPSconf.modal).on('hidden.bs.modal', function () {
				console.log('hidden.bs.modal');
				modalRemove();
			})
		
			
			
			
			
			if( $(this).hasClass('no-resize') )
			{
				$(IPSconf.modal).addClass('ips-detailed');
			}
			
			/**
			* Load modal window content from server, or string
			*/
			setContent( $(this).attr('data-href'), getContent( content ) );
			
			$( IPSconf.modal ).modal();
		}
	},
	modalInit = function ()
	{
		var template = ips_storage( {
			name : 'modal_tpl',
			url : '/ajax/get_template/',
			data : { 
				template_name : '/modals/modal',
				compile : false
			},
			compile: true
		}, 'POST' );
			
		var modal_div = template( {} );
	
		if ( $(IPSconf.modal).length !== 0 )
		{
			modalDestroy();
		}
		
		$('body').append( modal_div );
		
		return true;
	},
	calledFromModal = function( controller )
	{
		return $( controller ).hasClass('ips-from-modal');
	},
	getTitle = function( title )
	{
		if( typeof title == 'string' && ( title.substr( 0,1 ) == '#' || title.substr( 0,1 ) == '.' ) )
		{
			return $(title).html();
		}
		else if( typeof title == 'string' )
		{
			return title;
		}
		return $(this).attr('data-title');
	},
	getContent = function( content )
	{
		if( typeof content == 'string' && ( content.substr( 0,1 ) == '#' || content.substr( 0,1 ) == '.' ) )
		{
			return $(content).html();
		}
		return content;
	},
	setTitle = function ( title, type ) {
		
		if( typeof type == 'string' && type == 'wide' )
		{
			$( IPSconf.modal ).append('<button class="red-hover-button ips-modal-close" data-dismiss="modal"><i></i></button>');
		}
		
		if( typeof title == 'string' )
		{
			if( $( IPSconf.modal + ' div.modal-header h3').length == 0 )
			{
				$('<div class="modal-header"><span class="ips-modal-close" data-dismiss="modal"><i></i></span><h3>' + title + '</h3></div>').insertBefore( IPSconf.modal + ' .modal-body');
			}
			else
			{
				$( IPSconf.modal + ' .modal-header h3').html( title );
			}
		}
		else
		{
			$( IPSconf.modal + ' .modal-header').remove();
		}
	},
	setContent = function ( ajax_url, content )
	{
		if( typeof content == 'string' )
		{
			htmlContent( content );
			return;
		}
		if( typeof ajax_url == 'undefined' )
		{
			return false;
		}
			
		var data = IpsApp._ajax( ajax_url, {}, 'GET' )	
			
		if( data.modal_info )
		{
			$('body').modalAlert( data );
		}
		else
		{
		
			if( data.modal_content )
			{
				htmlContent( data.modal_content );
			}
			
			if( data.modal_title )
			{
				setTitle( data.modal_title );
			}
			
			if( data.modal_footer )
			{
				htmlFooter( data.modal_footer );
			}
			return true;
		}
		
		return false;
	},
	htmlContent = function( content, animation )
	{
		if( IPSconf.animate === true )
		{
			$( IPSconf.modal + ' div.modal-body').fadeOut('fast', function() {
				$(this).html( content ).find('.ips-modal-show').each(function(i,e) {
					$(this).addClass('ips-from-modal');
				});
				
				modalSetSize( false );
				
				$(this).fadeIn('fast', function() {
					$( IPSconf.modal + ' div.modal-body .ips-upload-file').createUpload();
				});
			});
			return;
		}
		
		$( IPSconf.modal + ' div.modal-body').html( content ).find('.ips-modal-show').each(function(i,e) {
			$(this).addClass('ips-from-modal');
		});
		
		$( IPSconf.modal + ' div.modal-body .ips-upload-file').createUpload();
		
		modalSetSize( true );
	},
	htmlFooter = function( content )
	{
		if( $( IPSconf.modal + ' div.modal-footer').length == 0 )
		{
			$('<div class="modal-footer">' + content + '</div>').insertAfter( IPSconf.modal + ' div.modal-body');
		}
		else
		{
			$( IPSconf.modal + ' div.modal-footer').html( content );
		}
	},
	modalSetSize = function( top )
	{

		if( top && IPSconf.modal_resize_top )
		{
			if( IPSconf.modal_height > $(IPSconf.modal).height() )
			{
				var maxTopOffset = ( $(window).height() - $(IPSconf.modal).height() ) / 3;
				$(IPSconf.modal).animate( { 'margin-top': maxTopOffset } );
			}
		}
		else
		{
			$(IPSconf.modal).animate( { 'margin-top': '5%' } );
		}
		IPSconf.modal_height = $(IPSconf.modal).height();
	},
	modalRemove = function(){
		$('#ips-modal.ips-modal-container,.modal-backdrop').remove();
		$('body').trigger('ips-modal-removed');
	},
	modalDestroy = function(){

		$(IPSconf.modal).modal('hide');
		//$('#ips-modal.ips-modal-container,.modal-backdrop').remove();
		//$('body').trigger('ips-modal-removed');
	},
	modalFadeOut = function(){
		$('#ips-modal.ips-modal-container,.modal-backdrop').fadeOut( 'fast', function(){
			modalDestroy();
			$(IPSconf.modal).on('hidden.bs.modal', function () {
				$('body').trigger('ips-modal-destroyed');
			});
		});
	};
})( jQuery );



/**
* Wrapper arround jStorage
*/
function ips_storage( stored, send_method )
{
	try{
		var data = $.jStorage.get( stored.name );
			
		if( !data ){

			var data = IpsApp._ajax( stored.url, stored.data, ( typeof send_method == 'undefined' ? 'GET' : send_method ) );
			
			if( stored.compile )
			{
				var data = doT.template( ( typeof data.modal_content != 'undefined' ? data.modal_content : data.content ) )
			}
			
			$.jStorage.set( stored.name, data );
		}
	}catch(n){
		console.log(n);
	}
	return data;
}

/**
* Pin in modal window
*/
(function($) {
    $.fn.modalPin = function( callback ) {
		
		var from_modal_pin = $(this).hasClass('in-modal');
		
		ips_config.file_id = $(this).attr('data-file-id');
		
		if( !ips_config.file_id || from_modal_pin )
		{
			modalFadeOut();
		
			var pin = IpsApp._ajax( '/ajax/pinit/pin/' + ips_config.file_id , {}, 'GET' );
			
			if( pin.modal_info )
			{
				return $('body').modalAlert( pin );
			}
		}

		if( ips_config.file_id || pin.modal_content )
		{
			modal_pin = true;
			
			if( !ips_config.file_id )
			{
			
				$('body').addClass('modal-open');
				
				if( $('#ips-pinover').length == 0  )
				{
					$('body').append( '<div id="ips-pinover"><button class="red-hover-button ips-modal-close" data-dismiss="modal"><i></i></button><div id="ips-pinover-container"></div></div>' );
				}
			}
			
			if( !ips_config.file_id || from_modal_pin )
			{
			
				$('#ips-pinover #ips-pinover-container').html( pin.modal_content );
				
				var title = $('#ips-pinover .pin-description .small-block-content').text();
				
				if( title.length < 1 )
				{
					var title = document.title;
				}
			}
			
			$.historyExtend().update( {
				id	: 'pin',
				title : title
			})

			$('#ips-pinover').fadeIn('fast').pinActions();
			
			checkIsLiked( $('#ips-pinover').find('.ips-like-it') );
			
			loadComments( ips_config.file_id );

			if( !ips_config.file_id )
			{
				$('#ips-pinover').on('click', function(e){
					if ( !$('#ips-pinover #ips-pinover-container').has( e.target ).length 
					&& !$(e.target).parents('#ips-pinover-container').length 
					&& !$(e.target).parents('.user-send-seggest-wrapper').length )
					{
						
						$(this).fadeOut('fast', function(){
							$(this).off().remove();
							History.back();
							$('body').removeClass('modal-open');
						});
					}
				});
			}
			
			$('#ips-pinover').find('.ips-async-load').asyncLoad();
			
			if( typeof callback == 'function' )
			{
				callback();
			}
			
			return false;
		}
		
		return true;
		
		$('body').modalAlert( ips_i18n.__( 'js_alert_jquery' ) );
	}

    $.fn.pagePin = function( element ) {
		
		$(this).modalPin(function(){
			var header = $("#top");
			
			var pin_bars = $("#ips-pinover-container .pin-page-bar");
			
			var containerOffsetTop = $("#ips-pinover-container").offset().top - header.height();
			
			$(window).scroll(function(event){

				if( $(this).scrollTop() >= containerOffsetTop ){
					if( header.css('position') == 'fixed' )
					{
						header.css( {position : 'absolute', top : $(this).scrollTop() } );
					}
				} else {
					header.removeAttr('style');
				}

				if ( $(this).scrollTop() >= ( containerOffsetTop + header.height() ) ) {
					if( !pin_bars.hasClass('pos-fixed') )
					{
						$("#ips-pinover-container").css( {'padding-top' : ( pin_bars.first().height() + 19 ) + 'px' } );
						/** Each for exact width */
						pin_bars.each(function(){
							$(this).css({'top': 0, width: $(this).width(), position : 'fixed'}).addClass('pos-fixed');
						});
					}
				} else if ( $(this).scrollTop() <= ( containerOffsetTop + header.height() ) ) {
					pin_bars.removeAttr('style').removeClass('pos-fixed');	
					$("#ips-pinover-container").removeAttr('style')
				}
			});
		});

	} 
	
	$.fn.paddPin = function() {
		var self = $(this);
		IpsApp.imgPreload( self, function( img ){
			if( img && img.width < self.width() )
			{
				var margin = ( self.width() - img.width ) / 2;
				var margin = margin > 20 ? 20 : margin;
				
				$(img).css( {
					'padding-top': margin+'px',
					'padding-bottom': margin+'px'
				} );
			}
			
			$(window).trigger('paddPin');
		} );
	}
	
	$.fn.pinActions = function(){
		
		$('#ips-pinover .pin-image-block').paddPin();
		
		/* if ( window.addthis )
		{
			window.addthis = null;
			window._adr = null;
			window._atc = null;
			window._atd = null;
			window._ate = null;
			window._atr = null;
			window._atw = null;
		} */
		head.load("http://s7.addthis.com/js/250/addthis_widget.js", function(){
			
			addthis.init();
		});
		
		
	}
	
	
})( jQuery );



/**
*
*/
function doTCompile( response ){
	
	if( typeof response.modal_content !== 'object' )
	{
		return;
	}
	
	var template = ips_storage( {
		name : response.compile,
		url : '/ajax/get_template/',
		data : { 
			template_name	: response.compile,
			compile			: true
		},
		compile: true
	}, 'POST' );
	
	return $('<div/>').html( response.modal_content.map( function ( vars ) {
		return template( vars );
	} ).join(' ') ).children();
}
/**
*
*/
/* function hoganCompile( response, template_name ){
		
	
	if( typeof response.modal_content == 'string' )
	{
		response.modal_content = $('<div/>').html( response.modal_content );
		return response; 
	}
	
	var template = $.jStorage.get( response.hogan );

	if( !template ){

		var template = IpsApp._ajax( '/ajax/get_template/' , { template_name : '/hogan/' + response.hogan }, 'POST' );

		$.jStorage.set( response.hogan, template );
	}

	var tpl = Hogan.compile( template.content );
	
	response.modal_content = $('<div/>').html( response.modal_content.map( function ( vars ) {
		return tpl.render( vars );
	} ).join(' ') )
	
	return response;
} */


/**
 * Convert number of bytes into human readable format
 *
 * @param integer bytes     Number of bytes to convert
 * @param integer precision Number of digits after the decimal separator
 * @return string
 */
function bytesToSize( bytes, precision )
{  
	var kilobyte = 1024;
	var megabyte = kilobyte * 1024;
	var gigabyte = megabyte * 1024;
	var terabyte = gigabyte * 1024;
   
	if ((bytes >= 0) && (bytes < kilobyte)) {
		return bytes + ' B';
 
	} else if ((bytes >= kilobyte) && (bytes < megabyte)) {
		return (bytes / kilobyte).toFixed(precision) + ' KB';
 
	} else if ((bytes >= megabyte) && (bytes < gigabyte)) {
		return (bytes / megabyte).toFixed(precision) + ' MB';
 
	} else if ((bytes >= gigabyte) && (bytes < terabyte)) {
		return (bytes / gigabyte).toFixed(precision) + ' GB';
 
	} else if (bytes >= terabyte) {
		return (bytes / terabyte).toFixed(precision) + ' TB';
 
	} else {
		return bytes + ' B';
	}
}

/**
*
*/
function loadSmallPanel( load_element, url, response )
{
	
	if( typeof response.compile != 'undefined')
	{
		response.modal_content = doTCompile( response );
	}
	var items = $(response.modal_content).filter('.item').detach()

	var cnt = load_element.parent();
	load_element.remove();
	
	cnt.appendToPage( items, { width: 65, gutter: 3, main_layout: false } );
	
	if( response.load_scroll )
	{
		cnt.parent().loadCustomInfinity( url );
	}
}

function loadRelatedBoards( load_element, url, response )
{
	load_element.parents('.block-layout').find('.ips-related-boards').relatedBoards( response, load_element );
}
/**
*
*/
function loadRelatedPins( load_element, url, response )
{
	load_element.showAsync();
	
	
	var data = doTCompile( response );
	
	var items = $(data).filter('.item').detach();
	
	var cnt = load_element.parent();
	
	load_element.remove();

	cnt.appendToPage( items, { 
		width: 250,
		gutter: 0,
		main_layout: false
	} );
	
	load_element.showAsync();

	if( response.load_scroll )
	{
		cnt.parent().loadCustomInfinity( url, {
			scrollContainer : ( !ips_config.file_id ? $('#ips-pinover') : $(window) ),
			dataTypeDecode : false,
			triggerPageThreshold : 10,
			thresholdMargin : -100
		} );
	}
}
(function($) {

	
	$.asyncLoad = function ( load_element ) {
	
		var url = load_element.attr('data-href'),
			callback = load_element.attr('data-callback');
		
		if( url )
		{
			IpsApp._ajax( url, jQuery.parseJSON( load_element.attr('data-get') ), 'GET', 'json', true, function( response, ajax ){
				if( response.modal_content == null ||  response.modal_content.lenght == 0 )
				{
					return load_element.parents('.small-item-layout, .ips-async-block').fadeOut('fast');
				}
				
				window[callback].apply( this, [load_element, ajax.url, response] )
			})
		}
	};
	
	$.fn.showAsync = function () {
		var block = $(this).parents('.ips-async-block');

		if( block && block.is(':hidden') )
		{
			block.show();
		}
	}
	
	$.fn.asyncLoad = function () {
		return this.each(function() {
			
			var timeout = $(this).attr('data-timeout');
			if( typeof timeout == 'undefined' )
			{	
				var timeout = 1;
			}

			setTimeout($.proxy( function () {
				new $.asyncLoad( $(this) );
			}, $(this) ), timeout);
		});
	};
	
	$.fn.loadCustomInfinity = function( url, options ){
		
		var uid = 'infinity_' + (new Date()).getTime(),
				$this = this,
				container = '.' + uid + ' .items-loaded';
		var loader = $( '<a></a>' )
		.attr( 'data-ajax', url + '&page=1' )
		.attr( 'href', url + '&page=1' )
		.addClass( uid + '_loader' );
		
		$(this).addClass( uid ).append( loader );
		
		$this.animate({ scrollTop: 0 }, 1 );
		$(this).css( { width : $(this).width() + 'px' });
		
		infiniteScroll( container, '.item', false, function( ias )
		{
			ias.on('noneLeft', function() {});
			
			ias.on('loaded', function( data, items ) {
				addItems( $( container ), items, 'appended' );
			});
			
		}, $.extend({ 
			next : '.' + uid + '_loader',
			history: false,
			dataType: 'json',
			scrollContainer : $this,
			loader : IPSconf.loader,
			customLoaderProc : function( loader ){
				$( container ).after( loader );
				loader.fadeIn();
			},
			triggerPageThreshold : 100,
			thresholdMargin : 10
		}, options ));
		
	}
	
})( jQuery );
/**
* File upload
*/
(function($) {

    $.fileUpload = function( element, options ) {

		var defaults = {
            endpoint    : '/ajax/pinit_upload/',
            onFoo		: function() {},
			multiple	: false,
			files		: []
        }

        var plugin = this;

        plugin.settings = {}

        var $element = $(element),
             element = element;
		

		if( typeof $element.attr('data-upload') !== 'undefined' )
		{
			defaults.endpoint = $element.attr('data-upload');
		}
		
		plugin.init = function() {
            plugin.settings = $.extend({}, defaults, options);
        }

        plugin.foo_public_method = function() {}
		
		
		plugin.render_input = function( element )
		{
			/**
			* For multiple file uploads
			*$( element ).append('<input type="file" multiple="" name="files_[]" />');
			*/
			if( plugin.settings.multiple )
			{
				$( element ).find('.ips-upload-multi').each(function( index ){
					$( this ).append('<input type="file" multiple="" name="multi_files_' + ( index + 1 ) + '[]" />');
				})
			}
			else
			{
				$( element ).prepend('<input type="file" name="files" />');
			}
			
			console.log($( element ));
		}
		plugin.render_upload = function( element )
		{
			
			plugin.render_input( element );
			
			$(element).find('input').fileupload({
				url: plugin.settings.endpoint,
				dataType: 'json',
				add: function (e, data) {
					$(element).addClass('disabled');
					data.submit();
				},
				done: function (e, data) {
					plugin.response_request( data.result, $(element).attr('data-response') );
				},
				progressall: function (e, data) {
					var progress = parseInt(data.loaded / data.total * 100, 10);
					$(element).parent().find('.upload-progress span').html(
						progress + '%'
					);
				}
			});
		}
		/** Multi upload functions */
		plugin.upload_multi_add = function( filesize, data )
		{
			$('.plupload_droptext').hide();
				
			$('.plupload_start').prop( 'disabled', false );
			
			$('.plupload_filelist li.success .plupload_file_action a').each(function(){
				$(this).trigger('click')
			});

			plugin.upload_multi_progress( { loaded:0, total:0 } );
			
			data.context = $('<li/>').appendTo('.plupload_filelist');

			$.each(data.files, function (index, file) {
				
				filesize += file.size;
				
				var file_name = $('<div/>')
							.addClass('plupload_file_name')
							.text( file.name );

				var file_size = $('<div/>')
							.addClass('plupload_file_size')
							.text( bytesToSize( file.size, 0 ) );

				var file_status = $('<div/>')
							.addClass('plupload_file_status')
							.text( '0%' );
				
				var file_action = $('<div/>')
							.addClass('plupload_file_action')
							.append( plugin.upload_multi_cancel_button().data( data ) );
				

							
				file_name.appendTo( data.context );
				file_size.appendTo( data.context );
				file_status.appendTo( data.context );
				file_action.appendTo( data.context );

			});
			
			return filesize;
		},
		/** Remove from quee */
		plugin.upload_multi_cancel = function( e )
		{
			e.data().abort();
					
			console.log(e.parents('ul').find('li'));
			if( e.parents('ul').find('li').length - 1 < 1 )
			{
				$('.plupload_start').prop( 'disabled', true );
			}
			
			var li = e.parents('li');
			
			if( !li.hasClass('success'))
			{
				plugin.upload_multi_count( -1 );
			}
			
			li.remove();
			
			return false;
		},
		/** Render button to remove from quee */
		plugin.upload_multi_cancel_button = function( )
		{
			return $('<a/>').attr('href', '#').on('click', function () {
				return plugin.upload_multi_cancel( $(this) )
			});
		},
		/** Set overall progres % */
		plugin.upload_multi_progress = function( data )
		{
			var progress = parseInt( data.loaded / data.total * 100, 10 );
			$(element).find('.plupload_total_status').html(
				( isNaN( progress )  ? 0 : progress ) + '%'
			);
			
			$(element).find('.plupload_progress .progress .progress-bar').css( 'width', ( isNaN( progress )  ? 0 : progress ) + '%' )
		},
		/** Set overall progres files count */
		plugin.upload_multi_count = function( count, uploaded )
		{
			var cnt = $('.plupload_upload_status'),
						progress = cnt.data();
			
			if( count !== false )
			{
				progress.count = count > 0 ? cnt.data().count + 1 : cnt.data().count - 1;
			}
			if( typeof uploaded != 'undefined' )
			{
				progress.uploaded++;
			}
			if( progress.uploaded > 0 && $('.plupload_next').prop('disabled') )
			{
				$('.plupload_next').prop( 'disabled', false ).removeClass('disabled');
			}
			cnt.data( progress ).find('span').html( progress.uploaded + '/' + progress.count );
		},
		plugin.render_upload_attach = function( element )
		{
			$('body').on('click', '.multiple-img', function () {
				
				$('.multiple-img-cover').removeClass('activ');
				
				$(this).find('.multiple-img-cover').addClass('activ');
				
				var img = $(this).find('img').attr('src');
				
				$(this).parents('form').find('input[name="upload_image"]').val( img );
				
			});
			
			$('.plupload_upload_status').data({
				count	: 0, 
				uploaded: 0
			})
			
			$('.plupload_start').on('click', function () {
				$('.text-danger').remove();
				$('.plupload_filelist li a').each(function(){
					$(this).data().submit();
				});
			});
			
			$('.plupload_next').on('click', function () {
				plugin.response_request( { files : plugin.settings.files }, $(element).attr('data-response') );
			});
		},
		plugin.render_upload_multi = function( element )
		{
			
			plugin.render_upload_attach( element );
			
			var filesize = 0;
			$(element).fileupload({
				url: plugin.settings.endpoint,
				autoUpload: false,
				acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
				disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
				dataType: 'json'
			}).on('fileuploadadd', function (e, data) {
				
				plugin.upload_multi_count( 1 );
				filesize = plugin.upload_multi_add( filesize, data );
				
			}).on('fileuploadprocessalways', function (e, data) {
				
				$('.plupload_filelist_footer .plupload_file_size').html( bytesToSize( filesize, 0 ) );

			}).on('fileuploadprogressall', function (e, data) {
				
				plugin.upload_multi_progress( data );
				
			}).on('fileuploadprogress', function (e, data) {
				
				$.each(data.files, function (index, file) {
					var progress = parseInt(data.loaded / data.total * 100, 10);
					$(data.context).children('.plupload_file_status').html(
						progress + '%'
					);
				});
				
			}).on('fileuploaddone', function (e, data) {
				
				plugin.upload_multi_count( false, 1 );
				
				$.each(data.files, function (index, file) {
					$(data.context).addClass('success');
				});
				
				$.each( data.result, function (index, files) {
					$.each( files, function (index, file) {
						plugin.settings.files.push( file );
					});
				});
				
				
			}).on('fileuploadfail', function (e, data) {
				$.each(data.files, function (index, file) {
					var error = $('<span class="text-danger"/>').text('File upload failed.');
					$(data.context.children()[index]).append(error);
				});
			}).on('fileuploadadd', function (e, data) {console.log( 'fileuploadadd'  ) })
				.on('fileuploadsubmit', function (e, data) {console.log( 'fileuploadsubmit'  ) })
				.on('fileuploadsend', function (e, data) {console.log( 'fileuploadsend'  ) })
				.on('fileuploaddone', function (e, data) {console.log( 'fileuploaddone'  ) })
				.on('fileuploadfail', function (e, data) {console.log( 'fileuploadfail'  ) })
				.on('fileuploadalways', function (e, data) {console.log( 'fileuploadalways'  ) })
				.on('fileuploadprogress', function (e, data) {console.log( 'fileuploadprogress'  ) })
				.on('fileuploadprogressall', function (e, data) {console.log( 'fileuploadprogressall'  ) })
				.on('fileuploadstart', function (e) {console.log( 'fileuploadstart'  ) })
				.on('fileuploadstop', function (e) {console.log( 'fileuploadstop'  ) })
				.on('fileuploadchange', function (e, data) {console.log( 'fileuploadchange'  ) })
				.on('fileuploadpaste', function (e, data) {console.log( 'fileuploadpaste'  ) })
				.on('fileuploaddrop', function (e, data) {console.log( 'fileuploaddrop'  ) })
				.on('fileuploaddragover', function (e) {console.log( 'fileuploaddragover'  ) })
				.on('fileuploadchunksend', function (e, data) {console.log( 'fileuploadchunksend'  ) })
				.on('fileuploadchunkdone', function (e, data) {console.log( 'fileuploadchunkdone'  ) })
				.on('fileuploadchunkfail', function (e, data) {console.log( 'fileuploadchunkfail'  ) })
				.on('fileuploadchunkalways', function (e, data) {console.log( 'fileuploadchunkalways'  ) })
				.on('fileuploadprocessstart', function (e) {console.log( 'fileuploadprocessstart'  ) })
				.on('fileuploadprocess', function (e, data) {console.log( 'fileuploadprocess'  ) })
				.on('fileuploadprocessdone', function (e, data) {console.log( 'fileuploadprocessdone'  ) })
				.on('fileuploadprocessfail', function (e, data) {console.log( 'fileuploadprocessfail'  ) })
				.on('fileuploadprocessalways', function (e, data) {console.log( 'fileuploadprocessalways'  ) })
				.on('fileuploadprocessstop', function (e) {console.log( 'fileuploadprocessstop'  ) });;
		
		}
		plugin.response_request = function( upload_data, response )
		{
			var files = [];
			
			$.each( upload_data.files, function (index, file) {
				files.push( file.name );
            });
			
			if( typeof window[response] === "function" )
			{
				return window[response]( upload_data.files )
			}
			
			$.ajax({
				url: response,
				data: { 
					'images'		: files, 
					'video'			: files, 
					'upload_info'	: upload_data.files
				},
				type: 'POST',
				success: function( data ) {
					var data = data;
					if( data.modal_content )
					{
						$('body').modalWindow( null, data.modal_content, true );
					}
					else if( data.modal_info )
					{
						$('body').modalAlert( data );
					}
					else
					{
						$('body').modalAlert( ips_i18n.__( 'js_alert_jquery' ) );
					}
				}
			});
		}
		
		try{
			
			var options = {
				multiple : $element.hasClass('upload-multiple')
			}
			plugin.init( options );
			plugin.render_input( element );
			return options.multiple ? plugin.render_upload_multi( $element ) : plugin.render_upload( $element ) ;
		
		}catch(n){
			console.log( n );
		}
    }

    $.fn.createUpload = function(options) {
		if( this.length == 0 || this.hasClass('async') )
		{
			return false;
		}
		
		var uploads = this;

		head.test({
			test    : typeof $.fileupload != 'undefined',
			success : [],
			failure : [
				"/libs/jQuery-File-Upload/js/vendor/jquery.ui.widget.js",
				"/libs/jQuery-File-Upload/ips/canvas-to-blob.min.js",
				"/libs/jQuery-File-Upload/ips/load-image.min.js",
				"/libs/jQuery-File-Upload/js/jquery.iframe-transport.js",
				"/libs/jQuery-File-Upload/js/jquery.fileupload.js",
				"/libs/jQuery-File-Upload/js/jquery.fileupload-process.js",
				"/libs/jQuery-File-Upload/js/jquery.fileupload-image.js"
			],
			callback: function() {
				return uploads.each(function() {
					new $.fileUpload( this, options );
				});
			}
		});
    }
})(jQuery);
	/**
	* User selected board to upload
	*/
	$.fn.setUploadBoard = function() {
		
		$('#create-pin').find('input[name="pin_board_id"]').val( $(this).attr('data-id') );
		$('#user-board-list .user-board-item-current').attr( 'data-id', $(this).attr('data-id') ).attr( 'data-privacy', $(this).attr('data-privacy') );
		$('#user-board-list .user-board-item-current-title').html( $(this).find('.user-board-item-title').html() );
		$('#user-board-list .user-board-list-main').fadeOut('fast');
		$('#user-board-list .user-board-list-main li').removeClass('item-activ');
		$(this).addClass('item-activ');
	}
	
	/**
	* User created new board while upload
	*/
	$.fn.newlyUploadedBoard = function( ) {
		
		$('#user-board-list .user-board-item-current').attr( 'data-id', $(this).attr('data-id') ).attr( 'data-privacy', $(this).attr('data-privacy') );
		$('#user-board-list .user-board-item-current-title').html( $(this).find('.user-board-item-title').html() );
		$('#user-board-list .user-board-list-main').fadeOut('fast');
	
	}
	
	window["url_match"] = /^(https?:\/\/){0,1}[a-z0-9_\-]+\..+/gi;
	window["email_match"] = /^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i;

	function formErrors( form_id )
	{
		var to_submit = true;

		switch( form_id )
		{
            case 'user_change_email':
			case 'user-send-pin':
				var inputs = '{"user_email":{"error_type":"normal","match":"email_match"}}';
            break;
			case 'create-pin':
				var inputs = '{"pin_board_id":{"error_type":"alert"},"pin_description":{"error_type":"normal"}}';
            break;
			case 'create-board':
               var inputs = '{"board_description":{"error_type":"alert"},"board_title":{"error_type":"normal"}}';
            break;
			case 'find_images':
               var inputs = '{"find_images_url":{"error_type":"normal","match":"url_match"}}';
            break;
			case 'upload_video':
               var inputs = '{"upload_video_url":{"error_type":"normal","match":"url_match"}}';
            break;
			case 'map_pin':
               var inputs = '{"pin_description":{"error_type":"normal"},"upload_image":{"error_type":"normal"}}';
            break;
			default:
				return true;
			break;
        }
		
		if( typeof inputs == 'undefined' )
		{
			return true;
		}
		
		$('#'+form_id+' .input-error-message').css( 'display', 'none' );

		$.each( jQuery.parseJSON( inputs ), function( name, info ){
			
			var el = $('#'+form_id+' [name="'+name+'"]');
		
			if( el.val().length == 0 || ( info.match && !el.val().match( window[ info.match ] ) ) )
			{
				if( info.error_type == 'normal' )
				{
					el.addClass('input-error');
					el.parent().find('.input-error-message').slideDown();
				}
				else
				{
					$('body').modalAlert( el.attr('data-required') );
				}
				to_submit = false;
				return false;
			}
			
		});
		return to_submit;
	}

	
	$.fn.appendToPage = function ( items, options )
	{
		if( typeof items == 'undefined' )
		{
			console.log( 'Empty items appendToPage' );
			return;
		}
		var $masonry_container = $( this );
		
		if( !$masonry_container.hasClass('items-loaded') )
		{
			if( items.length > 0 && items.first().hasClass('no-files') )
			{
				options = { width : $masonry_container.width() };
			}
			
			$masonry_container.hide( 10, function() {
				$masonry_container.addClass('items-loaded').html('').isotope({
					itemSelector		: '.item',
					getSortData: {
						sort_by_id		: function( itemElem ) {
							return parseInt( $( itemElem ).attr('data-sort') );
						}
					},
					sortBy				: 'sort_by_id',
					sortAscending		: true,
					
					transitionDuration	: 0,
					masonry: {
						columnWidth		: ( typeof options.width  === 'undefined' ? 236	: options.width ),
						gutter			: ( typeof options.gutter === 'undefined' ? 15	: options.gutter ),
						//isFitWidth		: true,
						//stamp			: '.stamp',
						//isOriginLeft	: true,
						//isInitLayout	: false
						//visibleStyle	: { 'opacity': 1, 'transform': 'scale(1)', 'visibility' :'visible' },
						//hiddenStyle	: { visibility : 'visible' },
						//containerStyle	: { position: 'relative' }
					}
				}).show( 0, function() {
					
					if( typeof options.main_layout === 'undefined' )
					{
						items = appendItems( items );
						items = prependItems( items );
					}
					
					addItems( $masonry_container, items, 'prepended' );
				});
				$masonry_container.isotope( 'once', 'layoutComplete', function() {
					$(window).trigger( 'isotope_loaded', [$masonry_container] );
				});
			
				
				/* 
				setTimeout(function(){
					addItems( $masonry_container, items, 'prepended' );
				}, 50 );
				*/
			});
			
		}
		else
		{
			addItems( $masonry_container, items, 'appended' );
		}

		modalDestroy();
	}

	function importLoader( $boxes )
	{
		
		if( $boxes.length > 0 )
		{
			if( typeof import_items === 'undefined' )
			{
				import_items = 0;
				imported_items = 0;
			}
			
			import_items += $boxes.length;
			var html = '<div class="progress active"><div style="width: 0%" class="bar progress-bar progress-bar-danger">0/'+$boxes.length+'</div></div>';
			
			$('#all_page').before( html );
		}
		else
		{
			var el = $('.progress').first();
			
			el.children().html( (imported_items + 1 ) + '/' + import_items ).css( 'width', ( imported_items + 1 ) / import_items * 100 + '%'  );
			imported_items++;
		}
	}
	
	
	function addItems( $masonry_container, items, method )
	{
		var $boxes = jQuery( items ).filter('.item');
		
		$boxes.css( { opacity : 0, visibility : 'visible' } );
		
		var lay_mode = $boxes.first().children().hasClass('pinned') && !$boxes.first().children().hasClass('from-website') && !$masonry_container.hasClass('block-layout-items');
		var imported = $boxes.first().children().hasClass('from-website');
		
		if( imported )
		{
			importLoader( $boxes );
		}
		/** Pins without waiting for images to load */
		if( lay_mode )
		{
			console.log('Pins without waiting for images to load');
			$masonry_container.append( $boxes ).isotope( method, $boxes ).layItem( ( $boxes.length - 1 ), $boxes );
		}
		/** Other items (.item) ex: board panels */
		else
		{
			if( $('body').hasClass('import-images-activ') )
			{
				/** While import its not necessary to sort */
				$boxes.each(function( index ) {
					$(this).imagesLoaded(function( instance ){
						$(this.elements).css( { opacity : 0 } );
						$masonry_container.append( $(this.elements) ).isotope( method, $(this.elements) ).layItem( index, $boxes );
						if( imported )
						{
							importLoader( false );
						}
					});
				}); 
			}
			else
			{
				$boxes.imagesLoaded(function( instance ){
					$(this.elements).css( { opacity : 0 } );
					$masonry_container.append( $(this.elements) ).isotope( method, $boxes ).layItem( ( $boxes.length - 1 ), $boxes );
					if( imported )
					{
						importLoader( false );
					}
				});
			}
		}
	
	}
	
	
	function appendItems( items )
	{
		if( $('.item-append').length > 0 )
		{
			return items.add( $('.item-append').detach() );
		}
		return items;
	}
	
	function prependItems( items )
	{
		if( $('.item-prepend').length > 0 )
		{
			return $('.item-prepend').detach().add( items );
		}
		return items;
	}
	
	
	function modal_function( modal_function, response )
	{
		var target_object = typeof response.modal_target == 'string' ? $(response.modal_target) : response.modal_target;
		
		switch( modal_function )
		{
            case 'load_content':
				target_object.appendToPage( response.modal_content, {} );
				$('body').removeClass('modal-open');
            break;
			case 'appendToPage':
				target_object.appendToPage( response.modal_content, {} );
            break;
			case 'importImages':
				target_object.appendToPage( $(response.modal_content), {} );
				$('body').removeClass('modal-open');
				$('body').addClass('import-images-activ');
            break;
        }
		modalDestroy();
	}

	$.fn.loadContent = function ( history )
	{
		var load_url = $(this).attr('data-href').split( '/' ),
			$this = $(this);
		
		var args = [ 
			'sort=' + load_url[1], 
			'sub_sort=' + ( typeof load_url[2] != 'undefined' ? load_url[2] : 'false' ),
			'user_profile=' + ( $('div.user-profile').length > 0 ),
			'page=1' 
		].join( '&' );
		

		$('.sub-content').removeClass('items-loaded').fadeOut( 10, function(){
			
			var sub_content = $(this).clone();
			
			$(this).replaceWith( sub_content );
			
			sub_content.html( IPSconf.loader ).fadeIn( 200, function(){
				
				IpsApp._ajax( '/ajax/pinit/items/'+ load_url[0] + '/', args, 'GET', 'json', true, function( response ){
					if( response )
					{
						if( typeof response.compile != 'undefined')
						{
							response.modal_content = doTCompile( response );
						}

						modal_function( 'load_content', {
							modal_target : '.sub-content',
							modal_content : $(response.modal_content).filter('.item').detach()
						} );
						
						var current_template = load_url.join( '/' );
						
						if( !history )
						{
							$.historyExtend().update( {
								id	:	'sortable',
								el	:	$this
							})
						}
						
						pinitInfiniteScroll( ".sub-content", ".item", function( ias ){
							
							$(ias.opts.next).attr( 'data-ajax', '/ajax/pinit/items/' + load_url[0] + '/?' + args );
							$(ias.opts.next).attr( 'href', current_template + '/2');
							
						}, {
							dataType : 'json'
						});
					}
				} );
				
			} );
		});
			
		
	}
	jQuery.fn.layItem = function( index, boxes ) { 
		if( index == ( boxes.length - 1 ) )
		{
			boxes.css( { opacity : 1 } ).find('img').css( { opacity: 1 } );
		}
	};
	/** User decides to upload video link/file **/
	$.fn.changeVideoUpload = function() {
		if( $(this).hasClass('video_url') )
		{
			$('#upload_video_file').slideUp( 'fast', function(){
				$('#upload_video_url').slideDown('fast');
			});
		}
		else
		{
			$('#upload_video_url').slideUp( 'fast', function(){
				$('#upload_video_file').slideDown('fast');
			});
		}
	};

	$.fn.formSubmit = function() {
		
		$(this).buttonLoader();
		
		IpsApp._formSpin();
		
		var form_status = false;
		var no_errors = formErrors( $(this).closest('form').attr('id') );
		
		if( no_errors )
		{
			var response = IpsApp._ajax( $(this).attr('data-href'), $(this).closest("form").serialize(), $(this).closest("form").attr('method')  )
			
			if( response.modal_redirect )
			{
				window.location.href = response.modal_redirect;
				return false;
			}
			
			if( response.modal_call )
			{
				if( typeof window[response.modal_call] === "function" )
				{
					window[response.modal_call]( response );
				}
				else
				{
					Function( response.modal_call )( );
				}
			}
			
			if( response.modal_set_success )
			{
				IpsApp._formSpinSuccess();
				return setTimeout( function(){ 
					modalFadeOut();
				}, 2000 );
			}
			
			if( response.modal_info )
			{
				$('body').modalAlert( response );
			}
			
			if( response.modal_replace && response.modal_content )
			{
				htmlContent( response.modal_content );
			}
			
			if( response.modal_function && response.modal_content )
			{
				modal_function( response.modal_function, response );
			}

			if( response.modal_content )
			{
				htmlContent( response.modal_content );
			}
			
			var form_status = true;
		}
		
		$(this).buttonLoader();
		IpsApp._formSpinRemove();
		
		return form_status;
	}
	$.fn.createPin = function() {
	
	}
	
	
	
	$.fn.createBoard = function() {
		
		if( $('#create-board').length > 0 )
		{
			
			var title = $('#create-board').find('input[name="board_title"]');
			
			if( title.val() == '' )
			{
				title.addClass('input-error');
				$('.board_title_error').slideDown();
				return;
			}
			
			if( $('#while-pin-upload').length > 0 )
			{
				$('#while-pin-upload').val('true');
			}
			
			var response = IpsApp._ajax( '/ajax/pinit/create_board', $('#create-board :input').serialize(), 'POST'  );

			/**
			* Error while crating Board
			*/
			if( response.modal_info )
			{
				if( response.modal_success )
				{
					modalDestroy();
					$(IPSconf.modal).on('hidden.bs.modal', function () {
						$('body').modalAlert( response );
					})
				}
				else
				{
					$('body').modalAlert( response );
				}

				return;
			}
			
			if( $('#while-pin-upload').length > 0 )
			{
				/**
				* Bard created while upload
				*/
				if( response.modal_content )
				{
					var cnt_id = $('#user-board-list').length > 0 ? '#user-board-list' : '#create-board' ;
					$( cnt_id ).replaceWith( response.modal_content.board_list );
					$( cnt_id ).parent().find('[data-id="' + response.modal_content.board_info.board_id + '"]').setUploadBoard();
				}
				return false;
			}
			
			/**
			* Redirect after creating Board
			*/
			if( response.modal_redirect )
			{
				window.location.href = response.modal_redirect;
				return false;
			}

			$('body').modalAlert( ips_i18n.__( 'js_alert_jquery' ) );
		}
	}
	
	/** Delete Board **/
	$.fn.deleteBoard = function() {
		
		var board_id = $(this).attr('data-id');
		var board_confirm = $(this).attr('data-confirm');
		var response = IpsApp._ajax( '/ajax/pinit/delete_board', { board_id: board_id, board_confirm: board_confirm }, 'POST'  );
		
		if( response.modal_success )
		{
			var sub_container = $("#board-block-" + board_id ).parents('.sub-content');
			if( sub_container.length !== 0 )
			{
				sub_container.isotope( 'remove', $("#board-block-" + board_id ) );
				sub_container.isotope( 'remove', $("#board-block-" + board_id ) ).isotope().isotope('reloadItems');
				$("#board-block-" + board_id ).remove();
			}
			
			if( typeof board_page !== 'undefined' )
			{
				window.location.href = ips_config.url;
			}
			else
			{
				modalFadeOut();
				$('body').on( 'ips-modal-destroyed', function( e ){
					$('body').modalAlert( response );
					$('body').off( 'ips-modal-destroyed' );
				});
				
			}
			return false;
		}
		
		if( response.modal_info )
		{
			$('body').modalAlert( response ); 
		}
	}
	/** Delete Pin **/
	$.fn.deletePin = function() {
		
		
		var pin_id = $(this).attr('data-id');
		
		var pin_confirm = $(this).attr('data-confirm');
		
		var response = IpsApp._ajax( '/ajax/pinit/delete_pin', { 
			pin_id: pin_id, 
			pin_confirm: pin_confirm
		}, 'POST'  );
		
		if( response.modal_success )
		{
			if( ips_config.file_id )
			{
				window.location.href = ips_config.url;
			}
			else
			{
				modalFadeOut();
				
				$('body').on( 'ips-modal-destroyed', function(){
					$('body').modalAlert( response );
					$('body').on( 'ips-alert-destroy', function(){
						$('#ips-pinover').trigger( 'click' );
					});
					$('body').off( 'ips-modal-destroyed' );
				});
				
				var item = $(".items-loaded").find('[data-file-id="' + pin_id + '"]')
				var parent = item.parents('.items-loaded');
				
				item.remove();
				parent.isotope().isotope('reloadItems');
			}
			
			return false;
		}
		
		if( response.modal_info )
		{
			$('body').modalAlert( response ); 
		}
	}
	
	/* Form for edit Pin action  */
	$.fn.editPinForm = function() {
		
		var pin_id = $(this).attr('data-id');
	
		var response = IpsApp._ajax( '/ajax/pinit/edit_pin_form', { pin_id: pin_id }, 'POST'  );
		
		modalDestroy();
		
		if( response.modal_info )
		{
			return $('body').modalAlert( response );
		}
		
		if( response.modal_content )
		{
			$('body').modalWindow( response.modal_title, response.modal_content );
			$('.user-board-list-main ul li[data-id="' + $('#ips-modal').find('input[name="pin_board_id"]').val() + '"]').setUploadBoard();
			
			return;
		}
		
		$('body').modalAlert( ips_i18n.__( 'js_alert_jquery' ) );
	}

	$.fn.editBoardCover = function() {
		
		var board_id = $(this).attr('data-id');	
		
		IpsApp._ajax( '/ajax/pinit/edit_cover', { board_id: board_id }, 'GET', 'json', true, function( response ){
			
			modalDestroy();
			
			if( response.modal_info )
			{
				return $('body').modalAlert( response );
			}
			if( response.modal_content )
			{
				return $('body').modalWindow( response.modal_title, response.modal_content );
			}
			
			$('body').modalAlert( ips_i18n.__( 'js_alert_jquery' ) );
		});
	}
	
	$.fn.editBoardForm = function() {
		
		////$('body').modalWindow( null, null );
		
		var board_id = $(this).attr('data-id');
		var response = IpsApp._ajax( '/ajax/pinit/edit_board_form', { board_id: board_id }, 'POST'  );
		
		modalDestroy();
		
		if( response.modal_info )
		{
			$('body').modalAlert( response );
			return;
		}
		
		if( response.modal_content )
		{
			$('body').modalWindow( response.modal_title, response.modal_content );
			
			$('#ips-modal').find('input.ui-typeahead').each( function(){
				$(this).typeahead( null, {
					valueKey : 'full_name',
					source: bloodhoundInitialize( 'full_name', $(this).attr('data-href')),
					templates: {
						suggestion: adaptdoT()
					}
				}).on('typeahead:selected', function( event, user, dataset ){
					
					$('#ips-modal').find('input.ui-typeahead').first().val('');
					$('#ips-modal').pinner( board_id, user, 'add' );
					$('body').trigger('click');
				});
			});
			return;
		}
		
		$('body').modalAlert( ips_i18n.__( 'js_alert_jquery' ) );
		
	}
	
	$.fn.pinner = function( board_id, user, pinner_action ) {
		
		if( $('[data-pinner-id="'+user.user_id+'"]').length > 0 )
		{
			return false;
		}
		else
		{

			var template = $('.board-pinner-item').first().clone();
			template.find('input').attr( 'data-pinner-id', user.user_id ).val( user.user_id );
			template.find('.small-h4').html( user.full_name );
			template.find('.small-user-info').html( 'invited by you');
			template.find('.image-container img').attr( 'src', user.image ).attr( 'alt', user.full_name );
			template.find('.small-user-thumb a').attr( 'href', user.login + '/boards' );
			template.find('.invite-pinner').attr( 'data-user-id', user.user_id ).removeClass('hidden-element');
			
			template.appendTo( '#board-pinners-wrapper' );
		}	
	}
	$.fn.removePinner = function() {
		
		$(this).parents('.board-pinner-item').fadeOut( 'fast', function(){
			$(this).remove();
		});
		
	}
	$.fn.invitePinner = function() {
		if( $(this).hasClass( 'remove-invited' ) )
		{
			$(this).parents('.board-pinner-item').fadeOut( 'fast', function(){
				$(this).remove();
			});

			
			$('#ips-modal').find('input.ui-typeahead').each( function(){
				//console.log($(this));
				//$(this).typeahead('val', '');
			});
			
			$(this).parents('.form-box').find('.modal-typeahead input').val('');
			/* 
			var parent = $( this ).parent();

			var response = IpsApp._ajax( '/ajax/pinit/pinner', { board_id:  $(this).attr( 'data-board-id'), user_id:  $(this).attr( 'data-user-id'), pinner_action : 'remove' }, 'POST'  );
			
			if( parent.hasClass('board-pinners') )
			{
				parent.fadeOut( 'fast', function(){
					$(this).remove();
				});
			}
			
			if( response.modal_info && !parent.hasClass('board-pinners') )
			{
				$('body').modalAlert( response );
				return;
			}
			*/
		}
	}
	$.fn.relatedBoards = function( response, load_element ) {
		
		$(this).buttonLoader();
		
		if( typeof related_boards_page == 'undefined' )
		{
			related_boards_page = 1;
		}
		related_boards_page++;
		
		if( typeof load_element === 'undefined' )
		{
			var response = IpsApp._ajax( '/ajax/pinit/related_boards', { page: related_boards_page, pin_id: ips_config.file_id }, 'GET'  );
		}
		
		if( response.modal_content )
		{
			var container = $(this).parents('.block-layout').find('.block-layout-items');

			container.appendToPage( $(response.modal_content), { width: 250, gutter: 0, main_layout: false } );
			
			if( response.modal_count < 4 )
			{
				$(this).fadeOut();
			}
			if( typeof load_element !== 'undefined' )
			{
				load_element.showAsync();
			}
		}
		else
		{
			$(this).fadeOut();
		}
		
		$(this).buttonLoader();
	}
	
	/**
	* Follow/Unfollow board function
	*/
	$.fn.followBoard = function( response ) {
		
		if( $(this).hasClass('disabled') )
		{
			return;
		}
		
		$(this).buttonLoader();
		
		
		var action = $(this).hasClass('ips-board-follow') ? { 
			ajax : 'follow_board',
			css_class : 'ips-board-unfollow'
		} : { 
			ajax : 'unfollow_board',
			css_class : 'ips-board-follow'
		};
		
		/** Not call function whill Follow/Unfollow All */
		if( typeof response == 'undefined' )
		{
			var response = IpsApp._ajax( '/ajax/pinit/' + action.ajax, { board_id: $(this).attr('data-id') }, 'POST'  );
		}
		
		if( response.modal_content && response.modal_content == 'true' )
		{
			$(this).removeClass('ips-board-follow ips-board-unfollow').addClass( action.css_class ).changeText();
		}
		else if( response.modal_info )
		{
			/**
			* Error while follow Board
			*/
			$('body').modalAlert( response );
		}
		$(this).buttonLoader();
	}
	

	/**
	* Follow/Unfollow all users boards function
	*/
	$.fn.followBoards = function() {
		
		$(this).buttonLoader();

		var user_id = $(this).attr('data-id');
		
		var response = IpsApp._ajax( $(this).attr('data-href'), { user_id: user_id }, 'POST'  );
		
		if( response.modal_content && response.modal_content == 'true' )
		{
			$(this).changeText();
			
			if( $(this).hasClass('button-red') )
			{
				$(this).removeClass('button-red').attr('data-href', '/ajax/pinit/unfollow_all' );
				var items = $('.ips-board-follow');
			}
			else
			{
				
				$(this).addClass('button-red').attr('data-href', '/ajax/pinit/follow_all' );
				var items = $('.ips-board-unfollow');
			}
			
			items.each(function(){
				$(this).followBoard( { modal_content : 'true' } )
			})
		}
		else if( response.modal_info )
		{
			/**
			* Error while follow Board
			*/
			$('body').modalAlert( response );
		}
		
		$(this).buttonLoader();
		
		if( $(this).hasClass('disabled') )
		{
			return;
		}
		
	}
	
	/**
	* Follow/Unfollow user function
	*/
	$.fn.followUser = function() {
		
		if( $(this).hasClass('disabled') )
		{
			return;
		}
		
		$(this).buttonLoader();
		
		if( $(this).hasClass('ips-user-follow') )
		{
			var action = { ajax : 'follow_user', css_class : 'ips-user-unfollow'};
		}
		else
		{
			var action = { ajax : 'unfollow_user', css_class : 'ips-user-follow', text : 'Follow'};
		}
		var user_id = $(this).attr('data-id');
		
		var response = IpsApp._ajax( '/ajax/pinit/' + action.ajax, { user_id: user_id }, 'POST'  );
		
		if( response.modal_content && response.modal_content == 'true' )
		{
			$(this).removeClass('ips-user-follow ips-user-unfollow').addClass( action.css_class ).changeText();
		}
		else if( response.modal_info )
		{
			/**
			* Error while follow User
			*/
			$('body').modalAlert( response );
		}
		
		$(this).buttonLoader();
	}
	
	/**
	* Enable/Disable button with text change
	*/
	$.fn.changeText = function() {
		
		var el = $(this).find( '.button-text' );

		var user_text = $(this).attr('data-change-text');
		
		$(this).attr('data-change-text', el.html() );
		
		el.html( user_text );
	}
	$.fn.buttonLoader = function() {
		
		if( $(this).hasClass('ajax-loader') )
		{
			$(this).find('.ajax-loader-button').remove();
			$(this).children('span').css( { opacity: 1 } );
			$(this).removeClass('ajax-loader disabled');
		}
		else
		{
			$(this).prepend('<div class="ajax-loader-button"><span></span></div>');
			$(this).addClass('ajax-loader disabled');
			
			$(this).children('span').css( { opacity: 0 } );
		}
	
	}
	
	/** Pin or repin **/
	$.fn.pinIt = function() {
		
		if( !ips_user.is_logged )
		{
			return onlyForLogged();
		}
		
		var item = $(this).parents('.item');
		var fields = {};
		var image = item.find('.pin-image').attr('src');
		if( item.find('.from-website').length > 0 )
		{
			if( item.find('.div-overflow-video').length > 0 )
			{
				var fields = { 
					images : encodeURI( item.find('.pin-video').attr('data-img') ),
					upload_video : encodeURI( item.find('.pin-video').attr('data-video') ),
					pin_from_url : item.find('.pin-video').attr('data-source')
				};
			}
			else
			{
				var fields = { 
					images : encodeURI( image ),
					pin_from_url : item.find('.pin-image').attr('data-source'),
					pin_title: item.find('.pin-image').attr('title')
				};
			}
		}
		else
		{
			var fields = {
				images : encodeURI( image.replace("thumb", "originals") ),
				repin_from : item.attr('data-file-id')
			};
		}
		
		var response = IpsApp._ajax( $(this).attr('data-href'), fields, 'POST'  );
		
		/**
		* Error
		*/
		if( response.modal_info )
		{
			$('body').modalAlert( response );
			return;
		}
		if( response.modal_title && response.modal_content )
		{
			$('body').modalWindow( response.modal_title, response.modal_content );
			return true;
		}
		$('body').modalAlert( ips_i18n.__( 'js_alert_jquery' ), true );
	}
	
	/** Check if Pin ws liked before */
	$.fn.checkLike = function() {
		
		$(this).removeClass('user-liked');

		var file_id = $(this).attr('data-id');
		var liked = jQuery.inArray( file_id, likes );

		$(this).removeClass('button-loading').addClass( ( liked < 0 ? 'user-not-liked' : 'user-liked' ) );
	}
	/**
	* Load user notifications in div block
	*/
	$.fn.notifications = function() {
		
		var container = $(this).parent().find( '.dropdown-menu' );
		if( container.length > 0 && !$(this).parent().hasClass( 'open' ) )
		{
			var response = IpsApp._ajax( '/ajax/pinit/notifications', {}, 'GET'  );
			if( response.modal_content )
			{
				container.html( response.modal_content );
			}
		}
	}
	
	/** 
	* Send to friend/email, show send form 
	*/
	$.fn.sendFriend = function() {
		$(this).clickover( { width: 340, global_close: true, placement:'bottom', html: true, content: content = function(){
				return $(this).sendAction();
			},
			onShown: function(){
				this.$element.parents('.pin-wrapper').addClass( 'has_visible' );
				return bindCalls( this.tip() );
			},
			onHidden: function(){
				this.$element.parents('.pin-wrapper').removeClass( 'has_visible' );
			}
		}).trigger('click');
	}
	
	$.fn.sendAction = function()
	{
		if( !ips_user.is_logged )
		{
			return onlyForLogged();
		}
		var formType = !$(this).hasClass('ips-send-board') ? 'send-pin' : 'send-board';
		/**
		* Send to friend form / Cached to all calls
		*/
		
		var sendForm = ips_storage( {
			name : formType,
			url : '/ajax/get_template/',
			data : { 
				template_name : 'doT/send'
			},
			compile: true
		}, 'POST' );

		return sendForm( { 
			send_type : formType,
			data_id: $(this).attr('data-send-id')
		} );
	}
	
	
	$.fn.likePin = function() {
		
		if( !ips_user.is_logged )
		{
			return onlyForLogged();
		}
		
		$(this).addClass('button-loading').removeClass('user-liked');
		
		var file_id = $(this).attr('data-id');
		
		var liked = jQuery.inArray( file_id, likes );

		if( liked < 0 ) {
			likes.push( file_id );
		}
		else {
			likes.splice( liked, 1 );
		}
		
		var css_class = liked < 0 ? 'user-liked' : 'user-not-liked';

		var response = IpsApp._ajax( $(this).attr('data-href'), { pin_id : file_id, pin_like : ( liked < 0 ) }, 'POST'  );
		
		/**
		* Error
		*/
		if( response.modal_info )
		{
			$('body').modalAlert( response );
			return;
		}
		/*
		* Update Pin counter on pin page/modal window
		*/
		if( $(this).next().hasClass('button-counter') )
		{
			var likes_count = $(this).next().find('span');
			likes_count.html( (parseInt( likes_count.text(), 10 ) + ( liked < 0 ? 1 : -1 ) ) );
		}
		
		$(this).removeClass('button-loading').addClass( css_class );
		$.jStorage.set( "pin-likes", likes );
	}
	
	$.fn.checkboxSwitch = function() {
		
		var div = $(this);
		var checkbox = div.find('.ui-checkbox');
		
		var checkbox_on = div.hasClass('is-on') || checkbox.attr("checked");
		
		div.removeClass( 'is-on' );
		
		if( !checkbox_on )
		{
			div.addClass( 'is-on' );
		}
		
		div.find('.ui-checkbox').attr("checked", !checkbox_on ).val( ( !checkbox_on ? 1 : 0 ) ).trigger('change');
		
		div.find('span').removeClass('is-activ');
		
		if( !checkbox_on )
		{
			div.find('span.checkbox-on').addClass( 'is-activ' );
		}
		else
		{
			div.find('span.checkbox-off').addClass( 'is-activ' );
		}
	}
	
	
	
	/**
	* Play/Stop GIF
	*/
	$.fn.gifPlay = function() {
		var img = $(this).parent().find('img'),
			src = img.attr( 'src' );

		if( !$(this).hasClass('gif-playing') )
		{
			var src = src.replace( 'thumb', 'gif' ),
				ext = 'gif';
			
			img.attr('data-src', src.slice( str.length-3, str.length ) )
		}
		else
		{
			var src = src.replace( 'gif', 'thumb' ),
				ext = img.attr('data-src');
		}
		
		img.attr( 'src',  src.slice( 0, - 3 ) + ext );
		$(this).toggleClass('gif-playing');
		
		return false;
	}
	
	$.fn.videoPlayPin = function() {
		
		var img = $(this).parent().find('img');
		
		return $(this).videoPlay({ 
			height: img.height(),
			width: img.width()
		}).trigger('play');
	}
	
	
	$.fn.callClickovers = function() {

		$(this).each( function(){
			
			var el = $(this);
			var content = function(){
				return '';
			};

			if( el.attr('data-wrapper') )
			{
				var content = $.trim( $( el.attr('data-wrapper') ).html() );
			}
			
			el.clickover( { 
				trigger: 'manual', 
				global_close: true, 
				placement:'bottom', 
				html: true, 
				content: content,
				onShown: function(){
					this.tip().find('.close-clickover').each( function(){
						$(this).on('click', function(){
							$('body').trigger('click');
						})
					});
				}
			}).trigger('click');
		});
	}
	$.fn.redirect = function() {
		
		var url = $(this).attr('data-redirect');
		if( url != '' )
		{
			window.location = url;
			window.location.href = url;
		}
		
	}
	$.fn.shareFacebook = function() {
		
		var meta = $.windowOpen.getMeta();
		meta['redirect_uri'] = ips_config.url + '#close_window';
		var link = 'https://www.facebook.com/dialog/feed?app_id=' + ips_config.app_id + '&display=popup&' + $.param(meta)
		
		$.windowOpen.open( link );
	}
	$.fn.shareTwitter = function() {
		
		var meta = $.windowOpen.getMeta();

		var link = 'https://twitter.com/intent/tweet?' + $.param(meta);
		
		$.windowOpen.open( link );
	}
	$.fn.shareNk = function() {
		
		var meta = $.windowOpen.getMeta();

		var link = 'http://nk.pl/sledzik?' + $.param(meta);
		
		$.windowOpen.open( link );
		
	}
	
	$.fn.sendConfirmation = function() {
		var div = $(this);
		IpsApp._ajax( '/ajax/pinit/confirm_email/', {}, 'POST', false, 'json', function( response ){
			div.parent().html( response.message );
		});
	}
	
	$.fn.chengeEmail = function() {
		IpsApp._ajax( '/ajax/get_template/' , { template_name : '/modals/user_email_change', phrases : ['pinit_update_email'] }, 'POST', 'json', true, function( response ){
			jQuery('body').modalWindow( response.phrases.pinit_update_email, response.content );
		} );
	}
	
	$.windowOpen = {
		
		open: function ( link ) {
            var options = this.getOptions();
            return window.open( link, null, options )
        },
        getMeta: function () {
            if( typeof params !== 'undefined' )
			{
				return params;
			}
			params = {};
			params['link'] = $('meta[property="og:url"]').attr("content");
			params['shout'] = params['link'];
			params['url'] = params['link'];
			params['name'] = $('meta[property="og:title"]').attr("content");
			params['text'] = params['name']
			params['caption'] = document.location.hostname;
			params['description'] = $('meta[name="og:description"]').attr("content");
			params['picture'] = $('meta[name="og:image"]').attr("content");
			
            return params
        },
        getOptions: function () {
            var a = 580,
                b = 400,
                c = screen.width,
                d = screen.height,
                e = Math.round((c - a) / 2),
                f = d > b ? Math.round((d - b) / 2) : 0;
            return "scrollbars=yes,resizable=yes,toolbar=no,location=yes,width=" + a + ",height=" + b + "," + "left=" + e + ",top=" + f
        }
		
	}

function onlyForLogged()
{
	var response = ips_storage( {
		name : 'popuLoginForm',
		url : '/ajax/pinit/login_form/',
		data : {},
		compile: false
	} )

	if( response.modal_info )
	{
		$('body').modalAlert( response );
	}
	
	return false;
}

function pinitInfiniteScroll( container, items, additional_callback_function, options )
{
 	infiniteScroll( container, items, infinity_scroll_onclick, function( ias )
	{
		ias.on('noneLeft', function() {
			var loader_none = $( IPSconf.loader );
			loader_none.find('div').removeClass('spinner').before('<hr>')
			$('#content .sub-content').after( loader_none );
		});
		ias.on('rendered', function( data, items ) {
			if( items.length < ips_items_on_page)
			{
				setTimeout(function(){
					ias.fire('noneLeft', []);
					ias.unbind();
				}, 100 );
			}
		});
		ias.on('loaded', function( data, items ) {
			addItems( $('.sub-content'), items, 'appended' );
		});
		if( typeof additional_callback_function == 'function' )
		{
			additional_callback_function( ias );
		}
	},$.extend({ 
		loader : IPSconf.loader,
		customLoaderProc : function( loader ){
			$('#content .sub-content').after(loader);
			loader.fadeIn();
		},
		dataType : 'json'
	}, options ));
}

function bloodhoundInitialize( name, data_href)
{
	var engine = new Bloodhound({
		name: name,
		remote: data_href + '/%QUERY/',
		datumTokenizer: function(d) { 
			return Bloodhound.tokenizers.whitespace(d.val); 
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace
	});
		
	engine.initialize();
	
	return engine.ttAdapter();
}
function adaptdoT()
{
	return ips_storage( {
		name : 'doT/user_send_seggest',
		url : '/ajax/get_template/',
		data : { 
			template_name : 'doT/user_send_seggest'
		},
		compile: true
	}, 'POST' )
}
function bindCalls( element )
{

	element.find('input.ui-typeahead').each( function(){
		$(this).typeahead( null,{
			valueKey : 'full_name',
			source: bloodhoundInitialize( 'full_name', $(this).attr('data-href') ),
			templates: {
				suggestion: adaptdoT()
			}
		}).keyup(function(e){ 
			if( $(this).val().match( window[ 'email_match' ] ) )
			{
				if( $('.user-send-to').hasClass('is_hidden') )
				{
					$('.user-send-to').removeClass('is_hidden').fadeIn();
				}
				$('.user-send-to h2 b').html( $(this).val() );
			}
			else if( !$('.user-send-to').hasClass('is_hidden') )
			{
				$('.user-send-to').addClass('is_hidden').fadeOut();
			}
		}).on('typeahead:selected', function( a,b ){
			
			var message = $(this).parents('form').find('input[name="send_message"]').val();
			var pin_id = $(this).parents('form').find('input[name="pin_id"]').val();
			var board_id = $(this).parents('form').find('input[name="board_id"]').val();
			
			sendMessage( { user_id: b.user_id, message: message, pin_id: pin_id, board_id: board_id } );
			
			$('[data-clickover-open]').trigger('click');
		});
	});
}

/** Check if element was liked */
function checkIsLiked( elements )
{
	elements.each(function ( event ) {
		$(this).checkLike();
	});
}

function sendMessage( data )
{
	var response = IpsApp._ajax( '/ajax/pinit/send_message', data, 'POST'  )
	
	if( response.modal_redirect )
	{
		window.location.href = response.modal_redirect;
		return false;
	}
	
	if( response.modal_info )
	{
		$('body').modalAlert( response );
	}
}


$(document).ready(function () {
    
	if( $('#send_confirmation_email').length > 0)
	{
		$('#send_confirmation_email').on( 'click', function( event ){
			$(this).sendConfirmation();
		});
	}
	
	if( $('#change_email_adress').length > 0)
	{
		$('#change_email_adress').on( 'click', function( event ){
			$(this).chengeEmail();
		})
	}
	
	$('body').on('click', '.sortable-block a', function ( event ) {
		event.preventDefault();
		$(this).loadContent();
	});
	
	$('body').on('click', '[data-redirect]', function () {
		$(this).redirect();
	});
	/** ** ** ** ** ** ** ** ** ** **/
	if( ips_user.is_logged )
	{
		$.jStorage.flush();
	}
	/**
	* Call social share buttons
	*/
	$('body').on('click', '.share-nk', function ( event ) {
		$(this).shareNk();
	});
	$('body').on('click', '.share-twitter', function ( event ) {
		$(this).shareTwitter();
	});
	$('body').on('click', '.share-facebook', function ( event ) {
		$(this).shareFacebook();
	});
	
	/**
	* Load user notifications panel
	*/
	$('body').on('click', '.ips-load-notifications', function ( event ) {
		$(this).notifications();
	});
	/**
	* Clockover window to all elements with class .call-clickover, must have defined data-content or data-href
	*/
	$('body').on('click', '.call-clickover', function ( event ) {
		$(this).callClickovers();
	});

	/**
	* Like/Unlike pin Using local storage to check if liked
	*/
	likes = $.jStorage.get("pin-likes");
	if( !likes )
	{
		likes = [];
		$.jStorage.set( "pin-likes", likes );
	}
	checkIsLiked( $('.ips-like-it') );
	
	/**
	* Show Pin in modal window by modal
	*/
	$('body').on('click', '.ips-modal-pinover', function ( event ) {
		return $(this).modalPin();
	});
	/**
	* Show Pin on Pin page
	*/
	if( ips_config.file_id )
	{
		$( '#pin-page-' + ips_config.file_id ).pagePin();

	}
	

	
	$('body').on('click', '.ips-like-it', function ( event ) {
		$(this).likePin();
	});
	
	/** 
	* Send to friend, show send form 
	*/
	$('body').on('click', '.ips-send,.ips-send-manual', function ( event ) {
		$(this).sendFriend();
	});
	
	/**
	* Load related boards next page
	*/
	$('body').on('click', '.ips-related-boards', function ( event ) {
		$(this).relatedBoards();
	});
	
	/**
	* Load related pins next page
	*/
	$('body').on('click', '.ips-related-pins', function ( event ) {
		$(this).relatedPins();
	});
	
	/**
	* Show send message field
	*/
	$('body').on('click', '.ips-add-message', function ( event ) {
		$(this).parents('.user-send-pin-body').find('.user-send-pin-add-message').addClass('is_active');
		$(this).fadeOut();
		return false;
	});
	
	/*
	* Send message to email
	*/
	$('body').on('click', '.user-send-to', function ( event ) {
		//send_to_email	
		//$('body').trigger('click');
		if( $(this).formSubmit( ) !== false )
		{
			$('[data-clickover-open]').trigger('click');
		}
		return false;
	});

	if( typeof ips_items == 'object' && ips_items.length > 0 && $('.sub-content').length > 0 )
	{
		ips_items.map( function( items, key ) {
			
			modal_function( 'load_content', {
				modal_target : $('.sub-content').eq( key ),
				modal_content : ( typeof items == 'object' ? doTCompile( { 
					compile : items.template,
					modal_content : items.items 
				} ) : $(items).find('.item').detach() )
			} );
			
		});
	}
	/**
	* Pin image from import, repin image
	*/
	$('body').on('click', '.ips-pin-it', function ( event ) {
		$(this).pinIt();
	});
	
	/**
	* Buttons with href parameter to redirect
	*/
	$('body').on('click', '.ips-modal-href', function ( event ) {
		window.location.href = $(this).attr('data-href');
	});
	
	/**
	* Change checkbox value
	*/
	$('body').on('click', '.ui-checkbox-cnt .on-off', function ( event ) {
		$(this).checkboxSwitch();
	});
	
	/**
	* Submit modal forms
	*/
	$('body').on('click', '.ips-modal-submit', function ( event ) {
		$(this).formSubmit();
		return false;
	});

	/**
	* Delete modal ERROR window
	*/
	$('body').on('click', '#ips-modal-alert,.alert-modal-close,#ips-modal-alert .ips-modal-close', function ( event ) {
		$('#ips-modal-alert').fadeOut( 'slow', function(){ 
			$('#ips-modal-alert').remove()
		});
		return false;
	});
	
	/**
	* Show modal window
	*/
	$('body').on('click', '.ips-modal-show', function ( event ) {
		//$(this).modalWindow( '.inputs-wrap h3', '.inputs-wrap h3' );
		$(this).modalWindow();
		return false;
	});
	
	/**
	* Invite/Remove Pinner
	*/
	$('body').on('click', '.invite-pinner', function ( event ) {
		$(this).invitePinner();
	});
	
	/**
	* Create Board
	*/
	$('body').on('click', '.create-board', function ( event ) {
		$(this).createBoard();
	});
	
	/**
	* Edit Board
	*/
	$('body').on('click', '.ips-edit-board', function ( event ) {
		$(this).editBoardForm();
	});
	/**
	* Edit Board Cover
	*/
	$('body').on('click', '.ips-edit-cover', function ( event ) {
		event.preventDefault();
		$(this).editBoardCover();
	});
	/**
	* Delete Board
	*/
	$('body').on('click', '.ips-delete-board', function ( event ) {
		$(this).deleteBoard();
	});
	/**
	* Edit Board Form
	*/
	$('body').on('click', '.edit-board', function ( event ) {
		$(this).editBoard();
	});
	
	/**
	* Edit Pin
	*/
	$('body').on('click', '.ips-file-edit', function ( event ) {
		event.preventDefault();
		$(this).editPinForm();
	});
	
	/**
	* Edit Pin
	*/
	$('body').on('click', '.ips-delete-pin', function ( event ) {
		$(this).deletePin();
	});
	
	/**
	* Follow,Unfollow Board
	*/
	$('body').on('click', '.ips-board-unfollow,.ips-board-follow', function ( event ) {
		$(this).followBoard();
		return false;
	});
	
	/**
	* Follow/Unfollow all users boards function
	*/
	$('body').on('click', '.user-follow-all', function ( event ) {
		$(this).followBoards();
		return false;
	});
	
	$('body').on('click', '#ips-modal .video_url,#ips-modal .video_file', function ( event ) {
		$(this).changeVideoUpload();
	});
	/**
	* Show board list while upload
	*/
	$('body').on('click', '.user-board-item-selected', function ( event ) {
		$(this).parent().find('.user-board-list-main').fadeIn('fast', function(){
			$("body").bind('click', function(e)
			{
				if ( $(e.target).closest('.user-board-list').length === 0 )
				{
					$('.user-board-list-main').fadeOut('fast')
				}
			});
		});
	});
	
	/**
	* Follow/Unfollow User
	*/
	$('body').on('click', '.ips-user-follow, .ips-user-unfollow', function ( event ) {
		$(this).followUser();
		return false;
	});
	
	/**
	* Play/Stop GIF
	*/
	$('body').on('click', '.gif-wrapper', function ( event ) {
		$(this).gifPlay();
	});
	
	/**
	* Play/Stop Video
	*/
	$('body').on('click', '.video-wrapper', function ( event ) {
		$(this).videoPlayPin();
	});
	/**
	* Show board list while upload
	*/
	$('body').on('click', '.user-board-list-main ul li', function ( event ) {
		$(this).setUploadBoard();
	});
	
	$('.ips-upload-file').createUpload();
	
	if( !ips_user.is_logged )
	{
		$('#menu-navigation li a').each(function(){
			if( new RegExp("\\bfollowing\\b", "g").test( $(this).attr('href') ) )
			{
				$(this).on( 'click', function(){
					return onlyForLogged();
				});
			}
		})
	}
	
	if (window.location.hash == '#close_window') window.close();
});
/*
<div class="ips-modal-mask ips-modal fade" style="display:none;"><div class="ips-modal-first-container"><div class="modalContent"><div class="ips-modal-main-container modal-body"></div><span class="ips-modal-close" data-dismiss="modal"><i></i></span></div></div></div>

<div class="ips-modal-container">
	<div class="ips-modal modal fade" id="ips-modal">
		<div class="modal-header">
			<span class="ips-modal-close" data-dismiss="modal"><i></i></span>
			<h3></h3>
		</div>
		<div class="modal-body">
			<p>ips_i18n.__( 'js_load' )</p>
			<div class="progress progress-info progress-striped active">
			<div class="bar" style="width: 100%"></div>
			</div>
		</div>
	</div>
</div>

<div class="ips-modal-mask ips-modal">
	<div class="ips-modal-first-container">
		<div class="modalContent">
			<div class="ips-modal-main-container modal-body">
			</div>
			<span class="ips-modal-close" data-dismiss="modal"><i></i></span>
		</div>
	</div>
</div>
*/







/*************************************************************/
/*************************************************************/
/************************ LIBS *******************************/
/*************************************************************/
/*************************************************************/


/*!
* Base64
*/
(function(f){var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",l="",j=[256],e=[256],g=0;var d={encode:function(c){var i=c.replace(/[\u0080-\u07ff]/g,function(n){var m=n.charCodeAt(0);return String.fromCharCode(192|m>>6,128|m&63)}).replace(/[\u0800-\uffff]/g,function(n){var m=n.charCodeAt(0);return String.fromCharCode(224|m>>12,128|m>>6&63,128|m&63)});return i},decode:function(i){var c=i.replace(/[\u00e0-\u00ef][\u0080-\u00bf][\u0080-\u00bf]/g,function(n){var m=((n.charCodeAt(0)&15)<<12)|((n.charCodeAt(1)&63)<<6)|(n.charCodeAt(2)&63);return String.fromCharCode(m)}).replace(/[\u00c0-\u00df][\u0080-\u00bf]/g,function(n){var m=(n.charCodeAt(0)&31)<<6|n.charCodeAt(1)&63;return String.fromCharCode(m)});return c}};while(g<256){var h=String.fromCharCode(g);l+=h;e[g]=g;j[g]=b.indexOf(h);++g}function a(z,u,n,x,t,r){z=String(z);var o=0,q=0,m=z.length,y="",w=0;while(q<m){var v=z.charCodeAt(q);v=v<256?n[v]:-1;o=(o<<t)+v;w+=t;while(w>=r){w-=r;var p=o>>w;y+=x.charAt(p);o^=p<<w}++q}if(!u&&w>0){y+=x.charAt(o<<(r-w))}return y}var k=f.base64=function(i,c,m){return c?k[i](c,m):i?null:this};k.btoa=k.encode=function(c,i){c=k.raw===false||k.utf8encode||i?d.encode(c):c;c=a(c,false,e,b,8,6);return c+"====".slice((c.length%4)||4)};k.atob=k.decode=function(n,c){n=n.replace(/[^A-Za-z0-9\+\/\=]/g,"");n=String(n).split("=");var m=n.length;do{--m;n[m]=a(n[m],true,j,l,6,8)}while(m>0);n=n.join("");return k.raw===false||k.utf8decode||c?d.decode(n):n}}(jQuery));





/* ==========================================================
 * bootstrapx-clickover.js
 * https://github.com/lecar-red/bootstrapx-clickover
 * version: 1.0
 * ==========================================================
 *
 * Based on work from Twitter Bootstrap and 
 * from Popover library http://twitter.github.com/bootstrap/javascript.html#popover
 * from the great guys at Twitter.
 *
 * Untested with 2.1.0 but should worked with 2.0.x
 *
 * ========================================================== */
!function($) {
  "use strict"

  /* class definition */
  var Clickover = function ( element, options ) {
    // local init
    this.cinit('clickover', element, options );
  }

  Clickover.prototype = $.extend({}, $.fn.popover.Constructor.prototype, {

    constructor: Clickover

    , cinit: function( type, element, options ) {
      this.attr = {};

      // choose random attrs instead of timestamp ones
      this.attr.me = ((Math.random() * 10) + "").replace(/\D/g, '');
      this.attr.click_event_ns = "click." + this.attr.me;

      if (!options) options = {};

      options.trigger = 'manual';

      // call parent
      this.init( type, element, options );

      // setup our own handlers
      this.$element.on( 'click', this.options.selector, $.proxy(this.clickery, this) );

      // soon add click hanlder to body to close this element
      // will need custom handler inside here
    }
    , clickery: function(e) {
      // clickery isn't only run by event handlers can be called by timeout or manually
      // only run our click handler and  
      // need to stop progration or body click handler would fire right away
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }

      // set popover's dim's
      this.options.width  && this.tip().find('.popover-content').width(  this.options.width  );
      this.options.height && this.tip().find('.popover-content').height( this.options.height );
	  
	  this.options.width  && this.tip().width(  this.options.width  );
      this.options.height && this.tip().height( this.options.height );
	  
      // set popover's tip 'id' for greater control of rendering or css rules
      this.options.tip_id     && this.tip().attr('id', this.options.tip_id );

      // add a custom class
      this.options.class_name && this.tip().addClass(this.options.class_name);

	  // we could override this to provide show and hide hooks 
      this.toggle();

      // if shown add global click closer
      if ( this.isShown() ) {
        var that = this;

        // close on global request, exclude clicks inside clickover
        this.options.global_close &&
          $('body').on( this.attr.click_event_ns, function(e) {
			if ( !that.tip().has(e.target).length && !$(e.target).parents('div.user-send-seggest-wrapper').length ) { that.clickery(); }
          });

        this.options.esc_close && $(document).bind('keyup.clickery', function(e) {
            if (e.keyCode == 27) { that.clickery(); }
            return;
        });

        // first check for others that might be open
        // wanted to use 'click' but might accidently trigger other custom click handlers
        // on clickover elements 
        !this.options.allow_multiple &&
            $('[data-clickover-open=1]').each( function() { 
                $(this).data('clickover') && $(this).data('clickover').clickery(); });

        // help us track elements w/ open clickovers using html5
        this.$element.attr('data-clickover-open', 1);

        // if element has close button then make that work, like to
        // add option close_selector
        this.tip().on('click', '[data-dismiss="clickover"]', $.proxy(this.clickery, this));

        // trigger timeout hide
        if ( this.options.auto_close && this.options.auto_close > 0 ) {
          this.attr.tid = 
            setTimeout( $.proxy(this.clickery, this), this.options.auto_close );  
        }

        // provide callback hooks for post shown event
        typeof this.options.onShown == 'function' && this.options.onShown.call(this);
        this.$element.trigger('shown');
      }
      else {
        this.$element.removeAttr('data-clickover-open');

        this.options.esc_close && $(document).unbind('keyup.clickery');

        $('body').off( this.attr.click_event_ns ); 

        if ( typeof this.attr.tid == "number" ) {
          clearTimeout(this.attr.tid);
          delete this.attr.tid;
        }

		// provide some callback hooks
        typeof this.options.onHidden == 'function' && this.options.onHidden.call(this);
        this.$element.trigger('hidden');
      }
    }
    , isShown: function() {
      return this.tip().hasClass('in');
    }
    , resetPosition: function() {
        var $tip
        , inside
        , pos
        , actualWidth
        , actualHeight
        , placement
        , tp

      if (this.hasContent() && this.enabled) {
        $tip = this.tip()

        placement = typeof this.options.placement == 'function' ?
          this.options.placement.call(this, $tip[0], this.$element[0]) :
          this.options.placement

        inside = /in/.test(placement)

        pos = this.getPosition(inside)

        actualWidth = $tip[0].offsetWidth
        actualHeight = $tip[0].offsetHeight
		
        switch (inside ? placement.split(' ')[1] : placement) {
          case 'bottom':
            tp = {top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'top':
            tp = {top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'left':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth}
            break
          case 'right':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width}
            break
        }
		
        $tip.css(tp)
      }
    }
    , debughide: function() {
      var dt = new Date().toString();

      console.log(dt + ": clickover hide");
      this.hide();
    }
  })

  /* plugin definition */
  /* stolen from bootstrap tooltip.js */
  $.fn.clickover = function( option ) {
    return this.each(function() {
      var $this = $(this)
        , data = $this.data('clickover')
        , options = typeof option == 'object' && option

      if (!data) $this.data('clickover', (data = new Clickover(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.clickover.Constructor = Clickover

  // these defaults are passed directly to parent classes
  $.fn.clickover.defaults = $.extend({}, $.fn.popover.defaults, {
    trigger: 'manual',
    auto_close:   0, /* ms to auto close clickover, 0 means none */
    global_close: 1, /* allow close when clicked away from clickover */
    esc_close:    1, /* allow clickover to close when esc key is pressed */
    onShown:  null,  /* function to be run once clickover has been shown */
    onHidden: null,  /* function to be run once clickover has been hidden */
    width:  null, /* number is px (don't add px), null or 0 - don't set anything */
    height: null, /* number is px (don't add px), null or 0 - don't set anything */
    tip_id: null,  /* id of popover container */
    class_name: 'clickover', /* default class name in addition to other classes */
    allow_multiple: 0 /* enable to allow for multiple clickovers to be open at the same time */
  })

}( window.jQuery );


head.load("//cdn.jsdelivr.net/typeahead.js/0.10.4/typeahead.min.js", function(){
	jQuery(window).trigger('typeahead.js');
});

/* head.load("//cdn.jsdelivr.net/hogan.js/3.0.0/hogan.min.js", function(){
	jQuery(window).trigger('Hogan.js');
}); */



/* Laura Doktorova https://github.com/olado/doT */
(function(){function o(){var a={"&":"&#38;","<":"&#60;",">":"&#62;",'"':"&#34;","'":"&#39;","/":"&#47;"},b=/&(?!#?\w+;)|<|>|"|'|\//g;return function(){return this?this.replace(b,function(c){return a[c]||c}):this}}function p(a,b,c){return(typeof b==="string"?b:b.toString()).replace(a.define||i,function(l,e,f,g){if(e.indexOf("def.")===0)e=e.substring(4);if(!(e in c))if(f===":"){a.defineParams&&g.replace(a.defineParams,function(n,h,d){c[e]={arg:h,text:d}});e in c||(c[e]=g)}else(new Function("def","def['"+
e+"']="+g))(c);return""}).replace(a.use||i,function(l,e){if(a.useParams)e=e.replace(a.useParams,function(g,n,h,d){if(c[h]&&c[h].arg&&d){g=(h+":"+d).replace(/'|\\/g,"_");c.__exp=c.__exp||{};c.__exp[g]=c[h].text.replace(RegExp("(^|[^\\w$])"+c[h].arg+"([^\\w$])","g"),"$1"+d+"$2");return n+"def.__exp['"+g+"']"}});var f=(new Function("def","return "+e))(c);return f?p(a,f,c):f})}function m(a){return a.replace(/\\('|\\)/g,"$1").replace(/[\r\t\n]/g," ")}var j={version:"1.0.1",templateSettings:{evaluate:/\{\{([\s\S]+?(\}?)+)\}\}/g,
interpolate:/\{\{=([\s\S]+?)\}\}/g,encode:/\{\{!([\s\S]+?)\}\}/g,use:/\{\{#([\s\S]+?)\}\}/g,useParams:/(^|[^\w$])def(?:\.|\[[\'\"])([\w$\.]+)(?:[\'\"]\])?\s*\:\s*([\w$\.]+|\"[^\"]+\"|\'[^\']+\'|\{[^\}]+\})/g,define:/\{\{##\s*([\w\.$]+)\s*(\:|=)([\s\S]+?)#\}\}/g,defineParams:/^\s*([\w$]+):([\s\S]+)/,conditional:/\{\{\?(\?)?\s*([\s\S]*?)\s*\}\}/g,iterate:/\{\{~\s*(?:\}\}|([\s\S]+?)\s*\:\s*([\w$]+)\s*(?:\:\s*([\w$]+))?\s*\}\})/g,varname:"it",strip:true,append:true,selfcontained:false},template:undefined,
compile:undefined},q;if(typeof module!=="undefined"&&module.exports)module.exports=j;else if(typeof define==="function"&&define.amd)define(function(){return j});else{q=function(){return this||(0,eval)("this")}();q.doT=j}String.prototype.encodeHTML=o();var r={append:{start:"'+(",end:")+'",endencode:"||'').toString().encodeHTML()+'"},split:{start:"';out+=(",end:");out+='",endencode:"||'').toString().encodeHTML();out+='"}},i=/$^/;j.template=function(a,b,c){b=b||j.templateSettings;var l=b.append?r.append:
r.split,e,f=0,g;a=b.use||b.define?p(b,a,c||{}):a;a=("var out='"+(b.strip?a.replace(/(^|\r|\n)\t* +| +\t*(\r|\n|$)/g," ").replace(/\r|\n|\t|\/\*[\s\S]*?\*\//g,""):a).replace(/'|\\/g,"\\$&").replace(b.interpolate||i,function(h,d){return l.start+m(d)+l.end}).replace(b.encode||i,function(h,d){e=true;return l.start+m(d)+l.endencode}).replace(b.conditional||i,function(h,d,k){return d?k?"';}else if("+m(k)+"){out+='":"';}else{out+='":k?"';if("+m(k)+"){out+='":"';}out+='"}).replace(b.iterate||i,function(h,
d,k,s){if(!d)return"';} } out+='";f+=1;g=s||"i"+f;d=m(d);return"';var arr"+f+"="+d+";if(arr"+f+"){var "+k+","+g+"=-1,l"+f+"=arr"+f+".length-1;while("+g+"<l"+f+"){"+k+"=arr"+f+"["+g+"+=1];out+='"}).replace(b.evaluate||i,function(h,d){return"';"+m(d)+"out+='"})+"';return out;").replace(/\n/g,"\\n").replace(/\t/g,"\\t").replace(/\r/g,"\\r").replace(/(\s|;|\}|^|\{)out\+='';/g,"$1").replace(/\+''/g,"").replace(/(\s|;|\}|^|\{)out\+=''\+/g,"$1out+=");if(e&&b.selfcontained)a="String.prototype.encodeHTML=("+
o.toString()+"());"+a;try{return new Function(b.varname,a)}catch(n){typeof console!=="undefined"&&console.log("Could not create a template function: "+a);throw n;}};j.compile=function(a,b){return j.template(a,null,b)}})();
/************ HISTORY API *************/
typeof JSON!="object"&&(JSON={}),function(){"use strict";function f(e){return e<10?"0"+e:e}function quote(e){return escapable.lastIndex=0,escapable.test(e)?'"'+e.replace(escapable,function(e){var t=meta[e];return typeof t=="string"?t:"\\u"+("0000"+e.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+e+'"'}function str(e,t){var n,r,i,s,o=gap,u,a=t[e];a&&typeof a=="object"&&typeof a.toJSON=="function"&&(a=a.toJSON(e)),typeof rep=="function"&&(a=rep.call(t,e,a));switch(typeof a){case"string":return quote(a);case"number":return isFinite(a)?String(a):"null";case"boolean":case"null":return String(a);case"object":if(!a)return"null";gap+=indent,u=[];if(Object.prototype.toString.apply(a)==="[object Array]"){s=a.length;for(n=0;n<s;n+=1)u[n]=str(n,a)||"null";return i=u.length===0?"[]":gap?"[\n"+gap+u.join(",\n"+gap)+"\n"+o+"]":"["+u.join(",")+"]",gap=o,i}if(rep&&typeof rep=="object"){s=rep.length;for(n=0;n<s;n+=1)typeof rep[n]=="string"&&(r=rep[n],i=str(r,a),i&&u.push(quote(r)+(gap?": ":":")+i))}else for(r in a)Object.prototype.hasOwnProperty.call(a,r)&&(i=str(r,a),i&&u.push(quote(r)+(gap?": ":":")+i));return i=u.length===0?"{}":gap?"{\n"+gap+u.join(",\n"+gap)+"\n"+o+"}":"{"+u.join(",")+"}",gap=o,i}}typeof Date.prototype.toJSON!="function"&&(Date.prototype.toJSON=function(e){return isFinite(this.valueOf())?this.getUTCFullYear()+"-"+f(this.getUTCMonth()+1)+"-"+f(this.getUTCDate())+"T"+f(this.getUTCHours())+":"+f(this.getUTCMinutes())+":"+f(this.getUTCSeconds())+"Z":null},String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(e){return this.valueOf()});var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={"\b":"\\b"," ":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},rep;typeof JSON.stringify!="function"&&(JSON.stringify=function(e,t,n){var r;gap="",indent="";if(typeof n=="number")for(r=0;r<n;r+=1)indent+=" ";else typeof n=="string"&&(indent=n);rep=t;if(!t||typeof t=="function"||typeof t=="object"&&typeof t.length=="number")return str("",{"":e});throw new Error("JSON.stringify")}),typeof JSON.parse!="function"&&(JSON.parse=function(text,reviver){function walk(e,t){var n,r,i=e[t];if(i&&typeof i=="object")for(n in i)Object.prototype.hasOwnProperty.call(i,n)&&(r=walk(i,n),r!==undefined?i[n]=r:delete i[n]);return reviver.call(e,t,i)}var j;text=String(text),cx.lastIndex=0,cx.test(text)&&(text=text.replace(cx,function(e){return"\\u"+("0000"+e.charCodeAt(0).toString(16)).slice(-4)}));if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,"")))return j=eval("("+text+")"),typeof reviver=="function"?walk({"":j},""):j;throw new SyntaxError("JSON.parse")})}(),function(e,t){"use strict";var n=e.History=e.History||{},r=e.jQuery;if(typeof n.Adapter!="undefined")throw new Error("History.js Adapter has already been loaded...");n.Adapter={bind:function(e,t,n){r(e).bind(t,n)},trigger:function(e,t,n){r(e).trigger(t,n)},extractEventData:function(e,n,r){var i=n&&n.originalEvent&&n.originalEvent[e]||r&&r[e]||t;return i},onDomLoad:function(e){r(e)}},typeof n.init!="undefined"&&n.init()}(window),function(e,t){"use strict";var n=e.document,r=e.setTimeout||r,i=e.clearTimeout||i,s=e.setInterval||s,o=e.History=e.History||{};if(typeof o.initHtml4!="undefined")throw new Error("History.js HTML4 Support has already been loaded...");o.initHtml4=function(){if(typeof o.initHtml4.initialized!="undefined")return!1;o.initHtml4.initialized=!0,o.enabled=!0,o.savedHashes=[],o.isLastHash=function(e){var t=o.getHashByIndex(),n;return n=e===t,n},o.isHashEqual=function(e,t){return e=encodeURIComponent(e).replace(/%25/g,"%"),t=encodeURIComponent(t).replace(/%25/g,"%"),e===t},o.saveHash=function(e){return o.isLastHash(e)?!1:(o.savedHashes.push(e),!0)},o.getHashByIndex=function(e){var t=null;return typeof e=="undefined"?t=o.savedHashes[o.savedHashes.length-1]:e<0?t=o.savedHashes[o.savedHashes.length+e]:t=o.savedHashes[e],t},o.discardedHashes={},o.discardedStates={},o.discardState=function(e,t,n){var r=o.getHashByState(e),i;return i={discardedState:e,backState:n,forwardState:t},o.discardedStates[r]=i,!0},o.discardHash=function(e,t,n){var r={discardedHash:e,backState:n,forwardState:t};return o.discardedHashes[e]=r,!0},o.discardedState=function(e){var t=o.getHashByState(e),n;return n=o.discardedStates[t]||!1,n},o.discardedHash=function(e){var t=o.discardedHashes[e]||!1;return t},o.recycleState=function(e){var t=o.getHashByState(e);return o.discardedState(e)&&delete o.discardedStates[t],!0},o.emulated.hashChange&&(o.hashChangeInit=function(){o.checkerFunction=null;var t="",r,i,u,a,f=Boolean(o.getHash());return o.isInternetExplorer()?(r="historyjs-iframe",i=n.createElement("iframe"),i.setAttribute("id",r),i.setAttribute("src","#"),i.style.display="none",n.body.appendChild(i),i.contentWindow.document.open(),i.contentWindow.document.close(),u="",a=!1,o.checkerFunction=function(){if(a)return!1;a=!0;var n=o.getHash(),r=o.getHash(i.contentWindow.document);return n!==t?(t=n,r!==n&&(u=r=n,i.contentWindow.document.open(),i.contentWindow.document.close(),i.contentWindow.document.location.hash=o.escapeHash(n)),o.Adapter.trigger(e,"hashchange")):r!==u&&(u=r,f&&r===""?o.back():o.setHash(r,!1)),a=!1,!0}):o.checkerFunction=function(){var n=o.getHash()||"";return n!==t&&(t=n,o.Adapter.trigger(e,"hashchange")),!0},o.intervalList.push(s(o.checkerFunction,o.options.hashChangeInterval)),!0},o.Adapter.onDomLoad(o.hashChangeInit)),o.emulated.pushState&&(o.onHashChange=function(t){var n=t&&t.newURL||o.getLocationHref(),r=o.getHashByUrl(n),i=null,s=null,u=null,a;return o.isLastHash(r)?(o.busy(!1),!1):(o.doubleCheckComplete(),o.saveHash(r),r&&o.isTraditionalAnchor(r)?(o.Adapter.trigger(e,"anchorchange"),o.busy(!1),!1):(i=o.extractState(o.getFullUrl(r||o.getLocationHref()),!0),o.isLastSavedState(i)?(o.busy(!1),!1):(s=o.getHashByState(i),a=o.discardedState(i),a?(o.getHashByIndex(-2)===o.getHashByState(a.forwardState)?o.back(!1):o.forward(!1),!1):(o.pushState(i.data,i.title,encodeURI(i.url),!1),!0))))},o.Adapter.bind(e,"hashchange",o.onHashChange),o.pushState=function(t,n,r,i){r=encodeURI(r).replace(/%25/g,"%");if(o.getHashByUrl(r))throw new Error("History.js does not support states with fragment-identifiers (hashes/anchors).");if(i!==!1&&o.busy())return o.pushQueue({scope:o,callback:o.pushState,args:arguments,queue:i}),!1;o.busy(!0);var s=o.createStateObject(t,n,r),u=o.getHashByState(s),a=o.getState(!1),f=o.getHashByState(a),l=o.getHash(),c=o.expectedStateId==s.id;return o.storeState(s),o.expectedStateId=s.id,o.recycleState(s),o.setTitle(s),u===f?(o.busy(!1),!1):(o.saveState(s),c||o.Adapter.trigger(e,"statechange"),!o.isHashEqual(u,l)&&!o.isHashEqual(u,o.getShortUrl(o.getLocationHref()))&&o.setHash(u,!1),o.busy(!1),!0)},o.replaceState=function(t,n,r,i){r=encodeURI(r).replace(/%25/g,"%");if(o.getHashByUrl(r))throw new Error("History.js does not support states with fragment-identifiers (hashes/anchors).");if(i!==!1&&o.busy())return o.pushQueue({scope:o,callback:o.replaceState,args:arguments,queue:i}),!1;o.busy(!0);var s=o.createStateObject(t,n,r),u=o.getHashByState(s),a=o.getState(!1),f=o.getHashByState(a),l=o.getStateByIndex(-2);return o.discardState(a,s,l),u===f?(o.storeState(s),o.expectedStateId=s.id,o.recycleState(s),o.setTitle(s),o.saveState(s),o.Adapter.trigger(e,"statechange"),o.busy(!1)):o.pushState(s.data,s.title,s.url,!1),!0}),o.emulated.pushState&&o.getHash()&&!o.emulated.hashChange&&o.Adapter.onDomLoad(function(){o.Adapter.trigger(e,"hashchange")})},typeof o.init!="undefined"&&o.init()}(window),function(e,t){"use strict";var n=e.console||t,r=e.document,i=e.navigator,s=!1,o=e.setTimeout,u=e.clearTimeout,a=e.setInterval,f=e.clearInterval,l=e.JSON,c=e.alert,h=e.History=e.History||{},p=e.history;try{s=e.sessionStorage,s.setItem("TEST","1"),s.removeItem("TEST")}catch(d){s=!1}l.stringify=l.stringify||l.encode,l.parse=l.parse||l.decode;if(typeof h.init!="undefined")throw new Error("History.js Core has already been loaded...");h.init=function(e){return typeof h.Adapter=="undefined"?!1:(typeof h.initCore!="undefined"&&h.initCore(),typeof h.initHtml4!="undefined"&&h.initHtml4(),!0)},h.initCore=function(d){if(typeof h.initCore.initialized!="undefined")return!1;h.initCore.initialized=!0,h.options=h.options||{},h.options.hashChangeInterval=h.options.hashChangeInterval||100,h.options.safariPollInterval=h.options.safariPollInterval||500,h.options.doubleCheckInterval=h.options.doubleCheckInterval||500,h.options.disableSuid=h.options.disableSuid||!1,h.options.storeInterval=h.options.storeInterval||1e3,h.options.busyDelay=h.options.busyDelay||250,h.options.debug=h.options.debug||!1,h.options.initialTitle=h.options.initialTitle||r.title,h.options.html4Mode=h.options.html4Mode||!1,h.options.delayInit=h.options.delayInit||!1,h.intervalList=[],h.clearAllIntervals=function(){var e,t=h.intervalList;if(typeof t!="undefined"&&t!==null){for(e=0;e<t.length;e++)f(t[e]);h.intervalList=null}},h.debug=function(){(h.options.debug||!1)&&h.log.apply(h,arguments)},h.log=function(){var e=typeof n!="undefined"&&typeof n.log!="undefined"&&typeof n.log.apply!="undefined",t=r.getElementById("log"),i,s,o,u,a;e?(u=Array.prototype.slice.call(arguments),i=u.shift(),typeof n.debug!="undefined"?n.debug.apply(n,[i,u]):n.log.apply(n,[i,u])):i="\n"+arguments[0]+"\n";for(s=1,o=arguments.length;s<o;++s){a=arguments[s];if(typeof a=="object"&&typeof l!="undefined")try{a=l.stringify(a)}catch(f){}i+="\n"+a+"\n"}return t?(t.value+=i+"\n-----\n",t.scrollTop=t.scrollHeight-t.clientHeight):e||c(i),!0},h.getInternetExplorerMajorVersion=function(){var e=h.getInternetExplorerMajorVersion.cached=typeof h.getInternetExplorerMajorVersion.cached!="undefined"?h.getInternetExplorerMajorVersion.cached:function(){var e=3,t=r.createElement("div"),n=t.getElementsByTagName("i");while((t.innerHTML="<!--[if gt IE "+ ++e+"]><i></i><![endif]-->")&&n[0]);return e>4?e:!1}();return e},h.isInternetExplorer=function(){var e=h.isInternetExplorer.cached=typeof h.isInternetExplorer.cached!="undefined"?h.isInternetExplorer.cached:Boolean(h.getInternetExplorerMajorVersion());return e},h.options.html4Mode?h.emulated={pushState:!0,hashChange:!0}:h.emulated={pushState:!Boolean(e.history&&e.history.pushState&&e.history.replaceState&&!/ Mobile\/([1-7][a-z]|(8([abcde]|f(1[0-8]))))/i.test(i.userAgent)&&!/AppleWebKit\/5([0-2]|3[0-2])/i.test(i.userAgent)),hashChange:Boolean(!("onhashchange"in e||"onhashchange"in r)||h.isInternetExplorer()&&h.getInternetExplorerMajorVersion()<8)},h.enabled=!h.emulated.pushState,h.bugs={setHash:Boolean(!h.emulated.pushState&&i.vendor==="Apple Computer, Inc."&&/AppleWebKit\/5([0-2]|3[0-3])/.test(i.userAgent)),safariPoll:Boolean(!h.emulated.pushState&&i.vendor==="Apple Computer, Inc."&&/AppleWebKit\/5([0-2]|3[0-3])/.test(i.userAgent)),ieDoubleCheck:Boolean(h.isInternetExplorer()&&h.getInternetExplorerMajorVersion()<8),hashEscape:Boolean(h.isInternetExplorer()&&h.getInternetExplorerMajorVersion()<7)},h.isEmptyObject=function(e){for(var t in e)if(e.hasOwnProperty(t))return!1;return!0},h.cloneObject=function(e){var t,n;return e?(t=l.stringify(e),n=l.parse(t)):n={},n},h.getRootUrl=function(){var e=r.location.protocol+"//"+(r.location.hostname||r.location.host);if(r.location.port||!1)e+=":"+r.location.port;return e+="/",e},h.getBaseHref=function(){var e=r.getElementsByTagName("base"),t=null,n="";return e.length===1&&(t=e[0],n=t.href.replace(/[^\/]+$/,"")),n=n.replace(/\/+$/,""),n&&(n+="/"),n},h.getBaseUrl=function(){var e=h.getBaseHref()||h.getBasePageUrl()||h.getRootUrl();return e},h.getPageUrl=function(){var e=h.getState(!1,!1),t=(e||{}).url||h.getLocationHref(),n;return n=t.replace(/\/+$/,"").replace(/[^\/]+$/,function(e,t,n){return/\./.test(e)?e:e+"/"}),n},h.getBasePageUrl=function(){var e=h.getLocationHref().replace(/[#\?].*/,"").replace(/[^\/]+$/,function(e,t,n){return/[^\/]$/.test(e)?"":e}).replace(/\/+$/,"")+"/";return e},h.getFullUrl=function(e,t){var n=e,r=e.substring(0,1);return t=typeof t=="undefined"?!0:t,/[a-z]+\:\/\//.test(e)||(r==="/"?n=h.getRootUrl()+e.replace(/^\/+/,""):r==="#"?n=h.getPageUrl().replace(/#.*/,"")+e:r==="?"?n=h.getPageUrl().replace(/[\?#].*/,"")+e:t?n=h.getBaseUrl()+e.replace(/^(\.\/)+/,""):n=h.getBasePageUrl()+e.replace(/^(\.\/)+/,"")),n.replace(/\#$/,"")},h.getShortUrl=function(e){var t=e,n=h.getBaseUrl(),r=h.getRootUrl();return h.emulated.pushState&&(t=t.replace(n,"")),t=t.replace(r,"/"),h.isTraditionalAnchor(t)&&(t="./"+t),t=t.replace(/^(\.\/)+/g,"./").replace(/\#$/,""),t},h.getLocationHref=function(e){return e=e||r,e.URL===e.location.href?e.location.href:e.location.href===decodeURIComponent(e.URL)?e.URL:e.location.hash&&decodeURIComponent(e.location.href.replace(/^[^#]+/,""))===e.location.hash?e.location.href:e.URL.indexOf("#")==-1&&e.location.href.indexOf("#")!=-1?e.location.href:e.URL||e.location.href},h.store={},h.idToState=h.idToState||{},h.stateToId=h.stateToId||{},h.urlToId=h.urlToId||{},h.storedStates=h.storedStates||[],h.savedStates=h.savedStates||[],h.normalizeStore=function(){h.store.idToState=h.store.idToState||{},h.store.urlToId=h.store.urlToId||{},h.store.stateToId=h.store.stateToId||{}},h.getState=function(e,t){typeof e=="undefined"&&(e=!0),typeof t=="undefined"&&(t=!0);var n=h.getLastSavedState();return!n&&t&&(n=h.createStateObject()),e&&(n=h.cloneObject(n),n.url=n.cleanUrl||n.url),n},h.getIdByState=function(e){var t=h.extractId(e.url),n;if(!t){n=h.getStateString(e);if(typeof h.stateToId[n]!="undefined")t=h.stateToId[n];else if(typeof h.store.stateToId[n]!="undefined")t=h.store.stateToId[n];else{for(;;){t=(new Date).getTime()+String(Math.random()).replace(/\D/g,"");if(typeof h.idToState[t]=="undefined"&&typeof h.store.idToState[t]=="undefined")break}h.stateToId[n]=t,h.idToState[t]=e}}return t},h.normalizeState=function(e){var t,n;if(!e||typeof e!="object")e={};if(typeof e.normalized!="undefined")return e;if(!e.data||typeof e.data!="object")e.data={};return t={},t.normalized=!0,t.title=e.title||"",t.url=h.getFullUrl(e.url?e.url:h.getLocationHref()),t.hash=h.getShortUrl(t.url),t.data=h.cloneObject(e.data),t.id=h.getIdByState(t),t.cleanUrl=t.url.replace(/\??\&_suid.*/,""),t.url=t.cleanUrl,n=!h.isEmptyObject(t.data),(t.title||n)&&h.options.disableSuid!==!0&&(t.hash=h.getShortUrl(t.url).replace(/\??\&_suid.*/,""),/\?/.test(t.hash)||(t.hash+="?"),t.hash+="&_suid="+t.id),t.hashedUrl=h.getFullUrl(t.hash),(h.emulated.pushState||h.bugs.safariPoll)&&h.hasUrlDuplicate(t)&&(t.url=t.hashedUrl),t},h.createStateObject=function(e,t,n){var r={data:e,title:t,url:n};return r=h.normalizeState(r),r},h.getStateById=function(e){e=String(e);var n=h.idToState[e]||h.store.idToState[e]||t;return n},h.getStateString=function(e){var t,n,r;return t=h.normalizeState(e),n={data:t.data,title:e.title,url:e.url},r=l.stringify(n),r},h.getStateId=function(e){var t,n;return t=h.normalizeState(e),n=t.id,n},h.getHashByState=function(e){var t,n;return t=h.normalizeState(e),n=t.hash,n},h.extractId=function(e){var t,n,r,i;return e.indexOf("#")!=-1?i=e.split("#")[0]:i=e,n=/(.*)\&_suid=([0-9]+)$/.exec(i),r=n?n[1]||e:e,t=n?String(n[2]||""):"",t||!1},h.isTraditionalAnchor=function(e){var t=!/[\/\?\.]/.test(e);return t},h.extractState=function(e,t){var n=null,r,i;return t=t||!1,r=h.extractId(e),r&&(n=h.getStateById(r)),n||(i=h.getFullUrl(e),r=h.getIdByUrl(i)||!1,r&&(n=h.getStateById(r)),!n&&t&&!h.isTraditionalAnchor(e)&&(n=h.createStateObject(null,null,i))),n},h.getIdByUrl=function(e){var n=h.urlToId[e]||h.store.urlToId[e]||t;return n},h.getLastSavedState=function(){return h.savedStates[h.savedStates.length-1]||t},h.getLastStoredState=function(){return h.storedStates[h.storedStates.length-1]||t},h.hasUrlDuplicate=function(e){var t=!1,n;return n=h.extractState(e.url),t=n&&n.id!==e.id,t},h.storeState=function(e){return h.urlToId[e.url]=e.id,h.storedStates.push(h.cloneObject(e)),e},h.isLastSavedState=function(e){var t=!1,n,r,i;return h.savedStates.length&&(n=e.id,r=h.getLastSavedState(),i=r.id,t=n===i),t},h.saveState=function(e){return h.isLastSavedState(e)?!1:(h.savedStates.push(h.cloneObject(e)),!0)},h.getStateByIndex=function(e){var t=null;return typeof e=="undefined"?t=h.savedStates[h.savedStates.length-1]:e<0?t=h.savedStates[h.savedStates.length+e]:t=h.savedStates[e],t},h.getCurrentIndex=function(){var e=null;return h.savedStates.length<1?e=0:e=h.savedStates.length-1,e},h.getHash=function(e){var t=h.getLocationHref(e),n;return n=h.getHashByUrl(t),n},h.unescapeHash=function(e){var t=h.normalizeHash(e);return t=decodeURIComponent(t),t},h.normalizeHash=function(e){var t=e.replace(/[^#]*#/,"").replace(/#.*/,"");return t},h.setHash=function(e,t){var n,i;return t!==!1&&h.busy()?(h.pushQueue({scope:h,callback:h.setHash,args:arguments,queue:t}),!1):(h.busy(!0),n=h.extractState(e,!0),n&&!h.emulated.pushState?h.pushState(n.data,n.title,n.url,!1):h.getHash()!==e&&(h.bugs.setHash?(i=h.getPageUrl(),h.pushState(null,null,i+"#"+e,!1)):r.location.hash=e),h)},h.escapeHash=function(t){var n=h.normalizeHash(t);return n=e.encodeURIComponent(n),h.bugs.hashEscape||(n=n.replace(/\%21/g,"!").replace(/\%26/g,"&").replace(/\%3D/g,"=").replace(/\%3F/g,"?")),n},h.getHashByUrl=function(e){var t=String(e).replace(/([^#]*)#?([^#]*)#?(.*)/,"$2");return t=h.unescapeHash(t),t},h.setTitle=function(e){var t=e.title,n;t||(n=h.getStateByIndex(0),n&&n.url===e.url&&(t=n.title||h.options.initialTitle));try{r.getElementsByTagName("title")[0].innerHTML=t.replace("<","&lt;").replace(">","&gt;").replace(" & "," &amp; ")}catch(i){}return r.title=t,h},h.queues=[],h.busy=function(e){typeof e!="undefined"?h.busy.flag=e:typeof h.busy.flag=="undefined"&&(h.busy.flag=!1);if(!h.busy.flag){u(h.busy.timeout);var t=function(){var e,n,r;if(h.busy.flag)return;for(e=h.queues.length-1;e>=0;--e){n=h.queues[e];if(n.length===0)continue;r=n.shift(),h.fireQueueItem(r),h.busy.timeout=o(t,h.options.busyDelay)}};h.busy.timeout=o(t,h.options.busyDelay)}return h.busy.flag},h.busy.flag=!1,h.fireQueueItem=function(e){return e.callback.apply(e.scope||h,e.args||[])},h.pushQueue=function(e){return h.queues[e.queue||0]=h.queues[e.queue||0]||[],h.queues[e.queue||0].push(e),h},h.queue=function(e,t){return typeof e=="function"&&(e={callback:e}),typeof t!="undefined"&&(e.queue=t),h.busy()?h.pushQueue(e):h.fireQueueItem(e),h},h.clearQueue=function(){return h.busy.flag=!1,h.queues=[],h},h.stateChanged=!1,h.doubleChecker=!1,h.doubleCheckComplete=function(){return h.stateChanged=!0,h.doubleCheckClear(),h},h.doubleCheckClear=function(){return h.doubleChecker&&(u(h.doubleChecker),h.doubleChecker=!1),h},h.doubleCheck=function(e){return h.stateChanged=!1,h.doubleCheckClear(),h.bugs.ieDoubleCheck&&(h.doubleChecker=o(function(){return h.doubleCheckClear(),h.stateChanged||e(),!0},h.options.doubleCheckInterval)),h},h.safariStatePoll=function(){var t=h.extractState(h.getLocationHref()),n;if(!h.isLastSavedState(t))return n=t,n||(n=h.createStateObject()),h.Adapter.trigger(e,"popstate"),h;return},h.back=function(e){return e!==!1&&h.busy()?(h.pushQueue({scope:h,callback:h.back,args:arguments,queue:e}),!1):(h.busy(!0),h.doubleCheck(function(){h.back(!1)}),p.go(-1),!0)},h.forward=function(e){return e!==!1&&h.busy()?(h.pushQueue({scope:h,callback:h.forward,args:arguments,queue:e}),!1):(h.busy(!0),h.doubleCheck(function(){h.forward(!1)}),p.go(1),!0)},h.go=function(e,t){var n;if(e>0)for(n=1;n<=e;++n)h.forward(t);else{if(!(e<0))throw new Error("History.go: History.go requires a positive or negative integer passed.");for(n=-1;n>=e;--n)h.back(t)}return h};if(h.emulated.pushState){var v=function(){};h.pushState=h.pushState||v,h.replaceState=h.replaceState||v}else h.onPopState=function(t,n){var r=!1,i=!1,s,o;return h.doubleCheckComplete(),s=h.getHash(),s?(o=h.extractState(s||h.getLocationHref(),!0),o?h.replaceState(o.data,o.title,o.url,!1):(h.Adapter.trigger(e,"anchorchange"),h.busy(!1)),h.expectedStateId=!1,!1):(r=h.Adapter.extractEventData("state",t,n)||!1,r?i=h.getStateById(r):h.expectedStateId?i=h.getStateById(h.expectedStateId):i=h.extractState(h.getLocationHref()),i||(i=h.createStateObject(null,null,h.getLocationHref())),h.expectedStateId=!1,h.isLastSavedState(i)?(h.busy(!1),!1):(h.storeState(i),h.saveState(i),h.setTitle(i),h.Adapter.trigger(e,"statechange"),h.busy(!1),!0))},h.Adapter.bind(e,"popstate",h.onPopState),h.pushState=function(t,n,r,i){if(h.getHashByUrl(r)&&h.emulated.pushState)throw new Error("History.js does not support states with fragement-identifiers (hashes/anchors).");if(i!==!1&&h.busy())return h.pushQueue({scope:h,callback:h.pushState,args:arguments,queue:i}),!1;h.busy(!0);var s=h.createStateObject(t,n,r);return h.isLastSavedState(s)?h.busy(!1):(h.storeState(s),h.expectedStateId=s.id,p.pushState(s.id,s.title,s.url),h.Adapter.trigger(e,"popstate")),!0},h.replaceState=function(t,n,r,i){if(h.getHashByUrl(r)&&h.emulated.pushState)throw new Error("History.js does not support states with fragement-identifiers (hashes/anchors).");if(i!==!1&&h.busy())return h.pushQueue({scope:h,callback:h.replaceState,args:arguments,queue:i}),!1;h.busy(!0);var s=h.createStateObject(t,n,r);return h.isLastSavedState(s)?h.busy(!1):(h.storeState(s),h.expectedStateId=s.id,p.replaceState(s.id,s.title,s.url),h.Adapter.trigger(e,"popstate")),!0};if(s){try{h.store=l.parse(s.getItem("History.store"))||{}}catch(m){h.store={}}h.normalizeStore()}else h.store={},h.normalizeStore();h.Adapter.bind(e,"unload",h.clearAllIntervals),h.saveState(h.storeState(h.extractState(h.getLocationHref(),!0))),s&&(h.onUnload=function(){var e,t,n;try{e=l.parse(s.getItem("History.store"))||{}}catch(r){e={}}e.idToState=e.idToState||{},e.urlToId=e.urlToId||{},e.stateToId=e.stateToId||{};for(t in h.idToState){if(!h.idToState.hasOwnProperty(t))continue;e.idToState[t]=h.idToState[t]}for(t in h.urlToId){if(!h.urlToId.hasOwnProperty(t))continue;e.urlToId[t]=h.urlToId[t]}for(t in h.stateToId){if(!h.stateToId.hasOwnProperty(t))continue;e.stateToId[t]=h.stateToId[t]}h.store=e,h.normalizeStore(),n=l.stringify(e);try{s.setItem("History.store",n)}catch(i){if(i.code!==DOMException.QUOTA_EXCEEDED_ERR)throw i;s.length&&(s.removeItem("History.store"),s.setItem("History.store",n))}},h.intervalList.push(a(h.onUnload,h.options.storeInterval)),h.Adapter.bind(e,"beforeunload",h.onUnload),h.Adapter.bind(e,"unload",h.onUnload));if(!h.emulated.pushState){h.bugs.safariPoll&&h.intervalList.push(a(h.safariStatePoll,h.options.safariPollInterval));if(i.vendor==="Apple Computer, Inc."||(i.appCodeName||"")==="Mozilla")h.Adapter.bind(e,"hashchange",function(){h.Adapter.trigger(e,"popstate")}),h.getHash()&&h.Adapter.onDomLoad(function(){h.Adapter.trigger(e,"hashchange")})}},(!h.options||!h.options.delayInit)&&h.init()}(window)

window.addEventListener('popstate', function(event) {
	var HistoryState = History.getState();
	if( typeof HistoryState !== 'undefined' )
	{
		var lastState = History.getStateByIndex( History.getCurrentIndex() );
		
		if(  typeof HistoryState.data.pin !== 'undefined' )
		{
			var el = $('<a class="ips-modal-pinover in-modal" data-file-id="' + HistoryState.data.pin + '"></a>').appendTo('body');
			el.trigger('click').remove();
		}
		else if( typeof HistoryState.data !== 'undefined' )
		{
			if( $('#ips-pinover').length > 0 )
			{			
				$('#ips-pinover').fadeOut('fast', function(){
					$('#ips-pinover').off().remove();
					$('body').removeClass('modal-open');
				});
			}

			if( typeof lastState.data.clicked !== 'undefined' )
			{
				var el = $( '<div class="sortable-block">' + lastState.data.clicked + '</div>').appendTo('body');
				el.find('a').trigger('click').remove();
			}
			else
			{
				var el = $.historyExtend().getLast();
				if( el !== null )
				{
					el.find('a').first().loadContent( true );
					$.historyExtend().resetLast( true )
				}
			}
		}
		return true;
	}
});


(function ($) {

	// historyExtend default options
	var defaultOptions = {
		start : null,
		one_load : true		
	};

	// Main historyExt plugin class
	$.historyExtend = function(){

		this.update = function( options )
		{

			switch ( options.id )
			{
				case 'pin':
					
					History.pushState( { 
						pin : ips_config.file_id,
						id : ips_config.file_id
					}, options.title, 'pin/' + ips_config.file_id );
					
				break;
				case 'sortable':
					
					var load_url = options.el.attr('data-href');
					History.pushState(
					{
						id: load_url + '/1',
						clicked: options.el.prop('outerHTML')
					}, document.title, load_url + '/1');
					
				break;
				default:
					History.pushState({
						id: document - location.href
					}, document.getElementsByTagName("title")[0].innerHTML );
				break;
			};
			this.resetLast( false );
		};

		// Main method.
		this.init = function()
		{
			if ( $('.sortable-block').length > 0 )
			{
				$.extend(defaultOptions, {
					start: $('.sortable-block').first()
				});
			}
			else
			{
				this.update({
					id : 'page'
				});
			}
			
		};
		
		this.getLast = function()
		{
			return ( defaultOptions.one_load ? window.location.reload : defaultOptions.start )
		};
		
		this.resetLast = function( option )
		{
			$.extend(defaultOptions, {
				one_load: option
			});
		};

		return this;
	};
})(jQuery);

$(document).ready(function () {
	try{
		$.historyExtend().init();
	}catch(b){
		console.log(b);
	}

});

/* 
History.Adapter.bind(window,'statechange',function(e){ 
	// Note: We are using statechange instead of popstate
	if( typeof HistoryState !== 'undefined' )
	{
		console.log(HistoryState);
		console.log(History.getState());
		console.log(e);
		

		var current = History.getState();
		
		if( HistoryState.hash.substr( 1, 3 ) == 'pin' )
		{
			//var el = $('<a class="ips-modal-pinover in-modal" data-file-id="' + HistoryState.data.pin + '"></a>').appendTo('body');
			//el.trigger('click');
			//el.off().remove();
		}
		
		
		 
		if( before_hash == 'pin' )
		{
			if( current_hash != 'pin' )
			{
				$('#ips-pinover').fadeOut('fast', function(){
					$('#ips-pinover').off().remove();
				});
				console.log('HistoryState.hash: ' + HistoryState.hash);
			}
		}
		else if( current_hash == 'pin' )
		{
			$('.item[data-file-id="' + History.getState().data.id + '"]').find('.ips-modal-pinover').trigger('click')
			
			console.log(History.getState().data.id);
		} 
	}
	HistoryState = History.getState();
}); */

