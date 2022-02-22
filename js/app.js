function IpsApp()
{
	new Image().src = '/images/svg/spinner' +  ( ips_config.version == 'pinestic' ? '-pinit' : '' ) + '.svg';
	new Image().src = '/images/icons/pixel-white.png';

	return this;
}
IpsApp.prototype = {
	dom: {
		body: $('body')
	},
	iCheckLoaded:  false,
	preload_img:  [],
	svg_loader: '/images/svg/spinner' +  ( ips_config.version == 'pinestic' ? '-pinit' : '' ) + '.svg',
	cache: [],
	fb_queue: [],
	logs: [],
	inited: {
		fb: false,
		fb_status: false,
		shadow : false
	},
	_log: function( l )
	{
		this.logs.push(l);
	},
	_sprintf: function ( format )
	{
		for( var i=1; i < arguments.length; i++ ) {
			format = format.replace( /%s/, arguments[i] );
		}
		return format;
	},
	_preloader: {
		load: function( element )
		{
			element = $(element);
			
			var height = this.size( element.find('img').length > 0 ? element.children('img').first() : element );

			var loader = $('<img src="' +  IpsApp.svg_loader + '" class="spinner" height="' + height + '"  />').css(
			{
				'position': 'absolute',
				'top': '50%',
				'left': '50%',
				'margin-top': '-' + ( height / 2 ) + 'px',
				'margin-left': '-' + ( height / 2 ) + 'px'
			});

			element.data('style', element.attr('style') ).css({
				'position': element.css('position') == 'absolute' ? 'absolute' : 'relative'
			}).addClass('svg-transparent').append( loader ).attr( 'disabled', true );
		},
		remove: function( element )
		{
			element = $(element);
			element.removeClass('svg-transparent').removeAttr('style').attr( 'style', element.data('style') ).attr('disabled', false ).find('.spinner').remove();
		},
		size: function ( element )
		{
			var height = element.outerHeight();
			return Math.min( ( element.outerWidth() - 5), ( ( height === 0 ? element.outerWidth() : element.outerHeight() ) - 5 ) );
		}
	},
	imgPreload: function ( element, callback )
	{
		var preload = element.find('img').first();

		if( element.length > 0 )
		{
			IpsApp._preloader.load( element );

			var img = new Image();
			img.src = preload.attr('data-load');
			preload.addClass('half-transparent');

			$(img).load( function() {
				preload.animate({opacity: 0.5}, 150, function() {
					preload.attr('src', img.src).animate({opacity: 1}, 150, function() {

						IpsApp._preloader.remove( element );
						preload.removeClass('half-transparent');

						if( typeof callback == 'function' )
						{
							callback( img );
						}
					});
				});
			});
		}
		else if( typeof callback == 'function' )
		{
			callback( false );
		}
	},
	_share: function(e) {
		e.preventDefault();

		var btn = $(this);
		var url = btn.attr("data-href");

		if( btn.hasClass( 'facebook' ) )
		{
			return IpsApp._facebook( url );
		}
		else if( btn.hasClass( 'tweet' ) )
		{
			return IpsApp._twitter( url, btn );
		}
		else if( btn.hasClass( 'google' ) )
		{
			return IpsApp._google( url );
		}
		else if( btn.hasClass( 'nk' ) )
		{
			return IpsApp._nk( url, btn );
		}
	},
	_facebook: function( url ) {
		IpsApp._window( "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(url) );
	},
	_twitter: function( url, btn ) {
		IpsApp._window( "https://twitter.com/intent/tweet?text=" + encodeURIComponent( btn.attr("data-title") ) + "&url=" + encodeURIComponent(url) );
	},
	_google: function( url ) {
		IpsApp._window( "https://plus.google.com/share?url=" + encodeURIComponent(url) );
	},
	_nk: function( url, btn ) {
		IpsApp._window( "http://nk.pl/sledzik/widget?content=" + encodeURIComponent(url) + '&title='+encodeURIComponent( btn.attr("data-title") )+'&source=bookmark' );
	},
	_window: function(url) {
		var w = 640;
		var h = 460;
		var sTop = window.screen.height / 2 - (h / 2);
		var sLeft = window.screen.width / 2 - (w / 2);
		window.open(url, "Share", "status=1,height=" + h + ",width=" + w + ",top=" + sTop + ",left=" + sLeft + ",resizable=0");
	},
	_smallPosts: function( element ) {

		if( element.length > 0 )
		{
			IpsApp._cachedImg( element.find('.mask') );
			element.find('.loader').remove();
			element.find('.display_none').toggleClass('display_none');
		}
	},
	_cachedImg: function( imgs ){

		if( imgs.length > 0 )
		{
			imgs.each(function(){

				var _self = $(this),
					src = '/cache/img_cache/' + _self.attr('data-width') + 'x' + 
						( typeof _self.attr('data-height') !== 'undefined' ? _self.attr('data-height') : '' ) + 
						'/' + _self.attr('data-img'),
					img = new Image();
					
				_self.one('inview', function(event, isInView, visiblePartX, visiblePartY) {
					img.width = _self.attr('data-width');
					img.height = _self.attr('data-height');

					img.onload = function(){

						if( _self.attr('data-max-height') && this.height > _self.data('max-height') )
						{
							$(this).css( 'margin-top', (-( this.height - _self.attr('data-max-height') ) / 2) );
						}

						_self.append( $(this) );
					};

					img.src = src;
				});
			});
		}
	},
	_ticker : function(){

		$(this).append(
			'<div class="scrollbar">' +
				'<div class="track">' +
					'<div class="thumb">' +
						'<div class="end"></div>' +
					'</div>' +
				'</div>' +
			'</div>' +
				'<div class="viewport">' +
				'<div class="items-thumbs overview"></div>' +
			'</div>'
		);

		var $scrollbar  = $(this),
			$overview   = $scrollbar.find(".overview"),
			loadingData = false,
			template = $scrollbar.find(".item-template").removeClass('display_none').outerHTML();

		$scrollbar.find(".item-template").remove();

		$scrollbar.tinyscrollbar({
			wheelSpeed : 20
		});

		var scrollbarData = $scrollbar.data("plugin_tinyscrollbar");

		$scrollbar.bind( 'move', function(){
			var threshold = 0.9,
				positionCurrent = scrollbarData.contentPosition + scrollbarData.viewportSize,
				positionEnd = scrollbarData.contentSize * threshold;

			if( !loadingData && positionCurrent >= positionEnd )
			{
				loadingData = true;

				IpsApp._ajax( '/ajax/items_load/', { id: 'items', page : $scrollbar.attr('data-page'), on_page : 10 }, 'GET', 'json', true, function( response ){

					$scrollbar.attr('data-page', $scrollbar.attr('data-page') + 1 );

					loadingData = false;

					if( response.items.length > 0 )
					{
						$overview.append( $('<div/>').html( response.items.map( function ( vars ) {
							return template.replace(/{%([a-z_]+)?%}/g, function(e, match) {
								return vars[match];
							});
						} ).join(' ') ).children() );

						IpsApp._preloadImages( $overview.find("img.img-preload-this") );
					}
					else
					{
						$scrollbar.unbind( 'move' );
					}

					scrollbarData.update("relative");
					
					if( $scrollbar.find('.items-thumbs a').length === 0 )
					{
						$scrollbar.unbind( 'move' ).remove();
					}

				});
			}
		});

		var rand = IpsApp._rand( 5, 'string' );

		$(window).on('scroll.' + rand, $.debounce( 5, function () {
			if( $scrollbar.isOnScreen() )
			{
				$scrollbar.trigger('move');
				$(window).unbind('scroll.' + rand);
			}
		})).trigger( 'scroll.' + rand );

	},
	_rand: function( l, charSet ){
		charSet = charSet && charSet == 'string' ? 'abcdefghijklmnopqrstuvwxyz' : '0123456789';
		var randomString = '';
		for (var i = 0; i < l; i++) {
			var randomPoz = Math.floor(Math.random() * charSet.length);
			randomString += charSet.substring(randomPoz,randomPoz+1);
		}
		return randomString;
	},
	_preloadImages: function( images )
	{
		images.each(function(){
			IpsApp.preload_img.push( $(this) );
		}).lazyload({
			effect			: 'fadeIn',
			event  			: 'preload',
			skip_invisible	: false,
			load			: function(){
				$(this).parent().removeClass('img-preload');
			}
		});

		$(window).bind("scroll.preloaders", $.debounce( 250, function () {
			IpsApp.preload_img.map(function( i, e ){
				if( i.isOnScreen() )
				{
					i.trigger('preload');
					IpsApp.preload_img.splice(e,1);
				}
			});

			$(window).unbind("scroll.preloaders");

			setTimeout(function() {
				IpsApp.preload_img.map(function( i ){
					i.trigger('preload');
				});
			}, 3000);
		} ));
	},
	_iframe: function( elements ){

		// dodaj także podczas ładowania ajax materiałów
		elements.attr( 'data-src', function( i, attr ){
			setTimeout( $.proxy( function() {
				$(this).attr('src', attr).removeClass('async_iframe').removeAttr('data-src');
			}, this ), i * ( $(this).isOnScreen() ? 200 : 600 ) );
		});
	},
	_iCheck: function( elements ){

		if( !IpsApp.iCheckLoaded ){
			return IpsApp._iCheckLoad( function(){
				IpsApp._iCheck( elements );
			});
		}

		var settings = {
			checkboxClass: 'icheckbox_' + ips_config.icheck.skin + ips_config.icheck.css_class,
			radioClass: 'iradio_' + ips_config.icheck.skin + ips_config.icheck.css_class,
			uncheckedClass: 'i-unchecked',
			callbacks: {
				ifCreated: true
			}
		};

		elements.addClass( ips_config.icheck.skin ).each(function(){

			var self = $(this),
			label = self.is("label") ? self : self.find('label');

			if( ips_config.icheck.skin == 'line' )
			{
				settings.insert = '<div class="icheck_line-icon"></div>' + label.html();

				if( self.is("label") )
				{
					label.contents().filter(function(){
						return this.nodeType === 3;
					}).remove();
				}
				else
				{
					label.hide();
				}
			}

			self.on('ifCreated', function(event){
				$(this).removeClass('display_none');
			}).icheck( settings );
		});

	},
	_iCheckLoad: function( callback ){
		head.load('/libs/iCheck/icheck.min.js?ips_rand=' + ips_randomity, function () {
			head.load('/libs/iCheck/skins/' + ips_config.icheck.skin + '/' + ips_config.icheck.color_file + '.css?ips_rand=' + ips_randomity );

			IpsApp.iCheckLoaded = true;

			if( typeof callback == 'function' )
			{
				callback();
			}
		});
	},



	/** Form loader spinner **/
	_formSpinner: {
		add: function( container )
		{
			return this.get( container ).show();
		},
		get: function( container )
		{
			if( typeof container == 'undefined' || !container )
			{
				container = $( '#ips-modal .ips-modal' );
			}
			else if( typeof container == 'string' )
			{
				container = $( container );
			}

			var loader = container.find('.form-spin');

			if( loader.length === 0 )
			{
				return this.create( container );
			}

			return loader;
		},
		create: function( container )
		{
			var loader = $('<div class="form-spin"></div>');

			if( container.outerHeight() < 80 )
			{
				loader.addClass('contain');
			}

			return loader.hide().prependTo( container );
		},
		success: function( container, timeout )
		{
			var loader = this.get( container );

			if( loader.length > 0 )
			{
				if( typeof timeout !== 'undefined' )
				{
					IpsApp._timeout( function(){
						IpsApp._formSpinner.remove( container );
					}, timeout );
				}

				return loader.addClass('success').show();
			}
		},
		response: function( container, response, timeout )
		{
			var loader = this.get( container );

			if( loader.length > 0 )
			{
				if( typeof response.error !== 'undefined' )
				{
					loader.addClass('error').html( this.text(response.error) );
				}
				else if( typeof response.content !== 'undefined' )
				{
					loader.html( this.text( response.content ) );
				}

				loader.find('span').css( {
					'height': ( loader.height() - 1 ) + 'px',
					'line-height': loader.outerHeight() + 'px'
				} );

				if( typeof timeout !== 'undefined' )
				{
					IpsApp._timeout( function(){
						IpsApp._formSpinner.remove( container );
					}, timeout );
				}

				return loader.show();
			}
		},
		text: function( text )
		{
			return '<span>' + text + '</span>';
		},
		remove: function( container, callback )
		{
			var loader = this.get( container );

			if( loader.length > 0 )
			{
				loader.fadeOut( 'slow', function(){
					if( typeof callback == 'function' )
					{
						callback();
					}
				});
			}
		},
	},
	_confirm: function ( success, fail )
	{
		$( '<div style="display:none" title="' + ips_i18n.__( 'js_delete_confirm' ) +'"></div>' ).dialog({
			resizable: false,
			height:0,
			width: dialogWidth( 500 ),
			modal: true,
			buttons: [
				{
					text: "Ok",
					click: function() {
						$( this ).dialog( 'close' );

						if( typeof success == 'function' )
						{
							success();
						}
					}
				},
				{
					text: ips_i18n.__( 'js_common_cancel' ),
					click: function() {
						$( this ).dialog( 'close' );
						if( typeof fail == 'function' )
						{
							fail();
						}
					}
				}
			]
		});

		return false;
	},

	/** Submit any form and check for response*/
	_submit: function( form, options ){

		var params = new FormData( form );

		params.append( 'ajax_post', 'true' );

		$.ajax( $.extend( {}, {
			type: 'POST',
			data: params,
			processData: false,
			contentType: false,
			async: true,
			success: function(){}
		}, options ) );
	},
	_asyncScript: function ( url, success, id ) {
		var head = document.getElementsByTagName("head")[0], done = false;
		var script = document.createElement("script");
		script.src = url;
		if(typeof id === 'string')
		{
			script.id = id;
		}
		// Attach handlers for all browsers
		script.onload = script.onreadystatechange = function(){
		if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") ) {
		  done = true;
		  if (typeof success === 'function') success();
		}
		};
		head.appendChild(script);
	},
	/** FB after init functions **/
	_FB: {
		queue: new Queue( false, true ),
		queue_status: new Queue( false, true ),
		init: function(){
			var _this = this;
			FB.init({
				appId  : ips_config.app_id,
				status : true, // check login status
				cookie : true, // enable cookies to allow the server to access the session
				xfbml  : true, // parse XFBML
				channelURL : ips_config.url + 'channel_' + ips_config.locale.normal + '.html',
				version : ips_config.app_version
			});
			
			FB.getLoginStatus( function(response) {
				if(response.status == "connected") {
					autoLoginFacebook( response.authResponse.accessToken );
				}
				ips_config.app_status = response.status == 'unknown' ? 'not_logged' : response.status;
				IpsApp.inited.fb_status = true;
				_this.queue_status.next();
			});
			
			IpsApp.inited.fb = true;
			
			head.load( ips_config.ips_action == 'fast' ? '"http://static.ak.fbcdn.net/connect.php/js/FB.Share' : '/js/facebook_share.js' );

			_this.queue.next();
		},
		afterInit: function(f){
			if( IpsApp.inited.fb )
			{
				return f();
			}
			return this.queue.add( f );
		},
		afterLoginStatus: function(f){
			if( IpsApp.inited.fb_status )
			{
				return f();
			}
			return this.queue_status.add( f );
		}
	},
	

	_showMessage: function( text, type, timeout ){
		if( type )
		{
			text = '<div class="ips-message msg-' + type + '"><span>' + text + '</span></div>';
		}

		IpsApp._shadow.message( text, timeout );
	},

	_shadow: {
		cnt: '<div class="window-shadow"></div>',
		timeout: false,
		loadImg: '<img class="loader-cnt" src="" alt="Loading">',
		init: function()
		{

			this.cnt = $( this.cnt );
			this.cnt.appendTo('body');
		},
		loader: function()
		{
			this.cnt.html( $( this.loadImg ).attr( 'src', IpsApp.svg_loader ) ).off( ips_click );
			this.show();
		},
		show: function()
		{
			this.cnt.addClass('show');
		},
		hide: function()
		{
			this.cnt.children().fadeOut( 300, function(){
				$(this).remove();
			});
			this.cnt.removeClass('show');
		},
		message:function( text, timeout )
		{
			this.show();

			if( this.timeout )
			{
				clearTimeout( this.timeout );
			}

			this.cnt.find('img').slideUp('fast', function(){
				$(this).remove();
			});

			this.cnt.append( $(text).addClass('loader-cnt') ).on( ips_click, function(){
				IpsApp._shadow.hide();
			} );

			this.timeout = IpsApp._timeout( function(){
				IpsApp._shadow.cnt.trigger( ips_click );
			}, ( !timeout ? 4 : timeout ) );
		}
	},
	_outerHeight: function( elements ){
		elements.each(function(){
			$(this).css('height', $(this).parent().innerHeight());
		});
	},
	_timeout: function( fn, t){
		return setTimeout( fn, t*1000 );
	},
	_screenWidth: function(){
		var width = $.cookie('ips_screen_size');
		if( !width || $(window).width() != width )
		{
			$.cookie( 'ips_screen_size', $(window).width(), {
				expires: '1y',
				path: '/'
			} );
		}
	},
	_length: function( elements ) {
		return elements.length > 0 ? elements : false;
	},
	_ajaxAsync: function( response_url, data, call_type, data_type, cache, call_function )
	{
		return this._ajax( response_url, $.extend( {}, data, {async:true} ), call_type, data_type, cache, call_function );
	},
	_ajax: function( response_url, data, call_type, data_type, cache, call_function )
	{
		var returnValue;
			var send_data = {
				url: response_url,
				data: data,
				type: call_type,
				dataType : ( typeof data_type == 'string' ? 'json' : data_type ),
				cache: ( call_type == 'GET' && ( typeof cache == 'undefined' && cache ) ? true : false ),
				async: ( typeof call_function !== 'undefined' || typeof data.async !== 'undefined' ),
				success: function( data_response ) {
					if( typeof call_function !== 'undefined' )
					{
						call_function( data_response, this );
						return;
					}
					else
					{
						returnValue = data_response;
					}

				},
				error: function(xhr, textStatus, errorThrown) {
					console.log('Error!  Status = ' + xhr.status);
					returnValue = false;
				}
			};

			if( call_type == 'UPLOAD' )
			{
				send_data.processData = false;
				send_data.contentType = false;
				send_data.type = 'POST';
			}

			$.ajax( send_data );

		return returnValue;
	},
	_config: function(){
		
		$.each( ips_config, walker);

		function walker(key, value) {
			
			if (value !== null && typeof value === "object") {
				// Recurse into children
				$.each(value, walker);
			}
		}
	},
	_bind: function() {

		this._config();
		
		$("#all_page").on( ips_click, ".ips_image_share", IpsApp._share );

		IpsApp._outerHeight( $(".outer_height") );
		IpsApp._smallPosts( $(".small_posts") );
		IpsApp._cachedImg( $(".ips-img-cache") );
		IpsApp._preloadImages( $("img.img-preload-this") );
		IpsApp._iframe( $(".async_iframe") );
		IpsApp._screenWidth();
		IpsApp._shadow.init();
		
		$('.ips-ticker').each( IpsApp._ticker );
	},
};
var IpsApp = new IpsApp();
