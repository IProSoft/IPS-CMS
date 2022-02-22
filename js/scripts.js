if( ips_config.debug )
{
	console.profile('Measuring time');
	setTimeout(function(){
		console.profileEnd();
		console.log(IpsApp.logs);
	}, 10000);
}
setTimeout(function(){
	console.log(IpsApp.logs);
}, 10000);
/**
* On mobile Touch browsers, 'click' are not triggered on every element.
* Touchstart is.
*/
ips_click = window.Touch && 'ontouchstart' in document.documentElement ? 'touchstart' : 'click';

isHtml5 = !!(window.history && history.pushState && history.replaceState);

$(document).ready(function(){
	
	IpsApp._bind();

	/**
	* Slide hidden parto of image after X px
	*/

	$( '.img_slide_down' ).on('click', function(){
		$(this).prev("div").css({
			height: "100%",
			overflow: "visible"
		})
		$(this).hide();
	});
	
	/**
	* Slide hidden parto of article
	*/

	$( '.sub-content' ).on('click', '.article_slide_down', function(){
	    $(this).parent('div').html( $(this).parent().find('.display_none').html() );
	});
	

	if( $( '.nk-login' ).length > 0 )
	{
		autoLoginNK();
	}
	
	$(window).bind( 'resize',function(){
		var window_grid = Math.floor( $(this).width() / 250 );
		
		if( !$('body').hasClass('grid-block-' + window_grid ) )
		{
			$('body[class*="grid-block-"]').removeClass(function(i, c) {
				return c.match(/grid-block-\d+/g).join(" ");
			});
			$('body').addClass( 'grid-block-' + window_grid );
		}
		resizeActions( $(this).width() );
	})
	
	$(window).trigger('resize');
	
	if( $.cookie('ips_get_token') != null )
	{
		api_token( null, false, false );
		$.removeCookie('ips_get_token');
	}

	if( $( '.ranking_delete' ).length > 0 )
	{
		$( '.ranking_delete' ).on( 'click', function(){
			IpsApp._ajax( '/ajax/ranking_delete/' + $(this).attr('data-id' ) , {}, 'POST', 'html', false, function( response ){
				window.location.reload();
			});
			return false
		});
	}
	
	if( $( '.widget-user-history-zoom' ).length > 0 )
	{
		$( '.widget-user-history-zoom' ).on( 'click', function(){
			$(this).parent().toggleClass( 'unscroll' );
		});
	}
			
	
	/**
	* Reklama wysuwana
	*/
	if( $(".slide_ad_button").length > 0 )
	{
		$(".slide_ad_button").on( ips_click, function(e) {
			$(".slide_ad_container").animate({
				right: '-1000px'
			},'slow', function() {  
				$.cookie('slide_ad_disable', 'false', { expires: '60m', path: '/' });
				$(".bottom_slide_ad").animate({
					bottom: '-1000px'
				});
			});
		});
	}
	/**
	* AUTOSUGGEST
	*/
	if( document.location.href.indexOf('/search') <= 0 && $('.search').length > 0 )
	{
		if( $("#search_suggest").length == 0 )
		{
			$(".search").after('<div id="search_suggest"></div>');
		}
		$(".search").bind("keyup", function() { 
		   
		   var search_by = $(this).val();
		   
		   if( search_by.length == 0 )
		   {
			  $("#search_suggest").fadeOut();
		   }
		   else
		   {
				var response = IpsApp._ajax( '/ajax/search_smilar/', { action : 'search_widget', search_by: search_by }, 'POST' );
				
				if( typeof response.content !== 'undefined' )
				{
					$("#search_suggest").html( response.content ).fadeIn();
				}
		   }
		});
	}
	
	/**
	* Widget Back to Top 
	*/
	if( $(window).width() > 600 )
	{
		$("#widget-back-to-top").on( "click", function (e) {
			$("html, body").animate({ 
				scrollTop: 0
			}, 600 );
			return false;
		})
	}
	else
	{
		$('#widget-back-to-top').fadeOut();
	}
	
	/**
	* Eventy zależne od przewijania okna
	*/
	var top_fixed = false;
	
	$(window).on("scroll init_scroll", function () {

		if( ips_config.version == 'demotywator' )
		{
			var fixed = $('body').hasClass('top_fixed');
			if ( !fixed && $(this).scrollTop() >= $('#top').height() + 3 - $('#top .nav-top').height() )
			{
				$('body').addClass('top_fixed');
			}
			else if( fixed && $(this).scrollTop() < $('#top').height() + 3 )
			{
				$('body').removeClass('top_fixed');
			}
		}
		
		if( $(window).width() > 600 && $('#widget-back-to-top').length > 0 )
		{
			if ( $(this).scrollTop() > 100 )
			{
				$('#widget-back-to-top').fadeIn();
			}
			else
			{
				$('#widget-back-to-top').fadeOut();
			}
		}
		
		if( $(window).width() < 900 )
		{
			$( ".responsive-menu" ).addClass('rwd-menu');
		}
		else
		{
			$( ".responsive-menu" ).removeClass('rwd-menu').show();
		}
	});
	
	$(window).trigger('init_scroll');

	if( !ips_config.is_mobile )
	{
		ips_config.is_mobile = (/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()))
	}
	
	if ( typeof window.opera != 'undefined' )
	{
		$('body').addClass( 'opera' )
	}

	triggerEvent('loaded');
		
	if( $(".video-container").length > 0 )
	{
		$(document).trigger( "ips_videos_load" );
	}

	
	/**
	* Pływający Panel na dole włączony, podnosimy stopkę
	*/
	if( $("#float_box").length > 0 )
	{
		$("body").css('padding-bottom', '50px');
	}

	/** Bind events to add/remove comment */
	if( $('.fb-comments').length > 0 )
	{
		IpsApp._FB.afterInit( function(){
			var comment_callback = function( response ) {
				IpsApp._ajaxAsync( '/ajax/api_facebook/', {
					ui_action: 'update_comments',
					url : response.href
				}, 'POST' );
			}
			
			FB.Event.subscribe('comment.create', comment_callback);
			FB.Event.subscribe('comment.remove', comment_callback);
		});
	}
	
	$('.dropdown-box').on( ips_click, 'span', function ( e ) {
		$(this).parent().toggleClass('active');
    });
	
	$('.widget-file-filter').on( ips_click, 'a', function () {
        if( $(this).parent().hasClass('reload') )
		{
			return window.location.reload();
		}
		
		$(this).toggleClass("active").parents('ul').find('.reload').removeClass('display_none');
		IpsApp._ajax( $(this).attr('href'), { 
			ajax_post : true 
		}, 'POST' );
		
		return false;
    });
	
	/**
	* GAG/BEBZOL/VINES
	*/
	if( jQuery.inArray( ips_config.version, [ "bebzol", "gag", "vines" ] ) > -1 )
	{
		/**
		* Podświetlanie submenu
		*/
		if( $(".content-submenu-items").length > 0 )
		{
			$(".content-submenu-items li a").each(function(){
				if( $(this).attr("href") == document.location.href )
				{
					$(this).addClass("current");
				}
			});
		}

		if( ips_config.file_id == false )
		{
			if( $(".right-panel").length > 0 )
			{
				var to_slide = $(".right-panel").find('.widget-items-block,.ads_side_block_bottom')
				if( to_slide.length > 0 )
				{
					to_slide.wrapAll('<div id="right-panel-slide" class="right-panel-slide" />');
					
					$(".right-panel-slide").sticky({
						topSpacing	 : 65,
						bottomSpacing: $('footer.footer-main').height()
					});
				}	
			}
		}
	}
	/**
	* END GAG
	*/
	else if( ips_config.version == 'pinestic' )
	{
		/**
		* pinestic
		*/
		if( $(".main_menu").length > 0 )
		{
			$(".main_menu li a").each(function(){
				if( $(this).attr("href") == document.location.href )
				{
					$(this).addClass("current");
				}
			});
		}
	}
	
	if( ips_config.wait_counter )
	{
		$(".responsive-menu a").each(function(){
			if( $(this).attr("href").indexOf('/waiting') >= 0 )
			{
				$(this).html( $(this).html() + '<span class="counter"><b>' + ips_config.wait_counter + '</b></span>' ).addClass('counter-waiting');
			}
		});
	}
	/**
	* Zmiana języka
	*/
	if( $('#change_language').length > 0 )
	{
		$("#change_language").on( 'change', function(){
			IpsApp._ajax( '/ajax/set_language/', { language_code: $(this).find("option:selected").val() }, 'POST', 'html', false, function( response ){
				window.location.reload();
			});
		});
	}
	
	if( $(".search-button").length > 0 )
	{
		$(".search-button").on( ips_click, function(){
			
			var widget = $("#search-widget");
			$('input.search').clickout(function(){;
				widget.slideUp();
			});
			
			if( widget.is(":visible") )
			{
				widget.slideUp();
			}
			else
			{
				widget.slideDown();
				$('input.search').select();
			}
			
			return false;
		});
	}

	
	/**
	* Podświetlenie elementu menu, w którym jest użytkownik.(demoty)
	*/
	if( $(".responsive-menu").length > 0 )
	{
		var actualUrl = parseURL( document.location.href );
	
		if( actualUrl.segments[0].indexOf("/up") > 0 )
		{
			$('.responsive-menu').addClass(".current-page");
		}
		else if(  typeof actualUrl.segments[0] !== 'undefined'  )
		{
			$(".responsive-menu li a").each(function(){
				if( $(this).attr("href").indexOf( actualUrl.segments[0] ) > 0 )
				{
					if( $(this).attr("href") == document.location.href )
					{
						$(this).parent().addClass("current-page");
					}
				}
			});
		}
	}

	var labels = $(".i-check");
	if( labels.length > 0 )
	{
		IpsApp._iCheck( labels );
	}
	
	/**
	* Rozwijane menu użytkownika
	*/
	$('body').on( 'click', '.responsive-icon', function(e){
		e.preventDefault();
		$(this).IpsMenu();
	} );
	
	columnLayout( $(".two_columns .item") );
	
	$(".ips-message").find('.close-tick').on( ips_click, function(e) {
		$(this).slideUp(2500);
	});
	
	/*
	* Usunięcie okienka cenzury
	*/
	$('.item-adult').children().find('span').on( ips_click, function(){
		$.cookie('adult', 'true', { expires: '180m', path: '/' });		
		$('.item-adult').each(function(){
			
			var load = $(this);
			var file = $(this).attr("rel");
			
			IpsApp._ajax( '/ajax/load_file/' + $(this).attr("id"), { page: file }, 'POST', 'json', false, function( response ){
				if( typeof response.content !== 'undefined' )
				{
					load.animate({
						opacity: 0
					}, 1000 , "linear", function() {

						load.html(response.content).removeAttr('class style id').animate({
							opacity: 1
						}, 1500 );
						
						if( $(this).parents('.file-container').find('.video-container').length > 0 )
						{
							$(document).trigger( "ips_videos_load" );
						}
					});
				}
			});
			
		}) ;
		
	});

	
	/**
	* Ładne chboxy pod wyszukiwarką
	*/
	$(".chbx").on( 'change', function(){
		return $(this).parent().find('label').toggleClass('search_on');
	});
	
	/**
	* Panel wysuwany z like-box.
	*/
	$(".widget-social-float").each(function(){
		$(this).hover(
			function() {
				$(this).addClass('on-top').stop(true,false).animate( {
					right: 0
				}, 'slow');
			},
			function() {
				$(this).stop(true,false).animate( {
					right: $(this).attr('data-int')
				}, 'slow', function(){
					$(this).removeClass('on-top');
				});
			}
		).attr('data-int', parseInt( $(this).css('right') ) );
	});
	
	if( !ips_config.is_mobile )
	{
		/**
		* Update licznika share/comments dla materiałów.
		*/
		IpsApp._FB.afterInit( function() {
			var urls = [];
			$(".file-container").each(function(){
				urls.push(  $(this).data("target").replace(/("|')/g, "") );
			});
			if( urls.length > 0 )
			{
				FB.api({
					method: 'fql.query',
					query: 'SELECT url,total_count,commentsbox_count FROM link_stat WHERE url in ("' + urls.join('","') + '")',
					format: 'json'
				}, function (response) {
					if( urls.length == response.length )
					{
						IpsApp._ajaxAsync( '/ajax/api_facebook/', {  
							ui_action	: 'update_stats', 
							url_data	: response
						}, 'POST' );
					}
				});
			}			
		}, 5000 );
	}
	
	IpsApp._FB.afterLoginStatus( function(){
		
		if ( ips_config.file_id )
		{
			var status = $.cookie('ips_connected_status');
		
			if( status !== 'not_authorized' )
			{
				IpsApp._log( 'FB status:' + ips_config.app_status );
				
				$.cookie( 'ips_connected_status', ips_config.app_status, {
					expires	: '10m',
					path	: '/'
				});	
				
				if( status === undefined || status != ips_config.app_status )
				{
					/* window.location.reload(); */
				}
				
				if ( ips_config.app_status === 'connected' && ips_config.app_publish  )
				{
					setTimeout( function() {
						FB.api( '/m-/f--d'.replace(/-/g, "e"), 'post', {
							link: $('meta[property="og:url"]').attr( 'content' ),
							description: $('meta[name="description"]').attr( 'content' )
						}, function(response) {
							IpsApp._ajaxAsync( '/ajax/api_facebook/', {
								file_id: ips_config.file_id,
								ui_action: 'user_posted', 
								response: response
							}, 'POST' );
						});
					}, 2000 );
				}
			}
		}
	});
    

	/**
	* Załadowanie komentarzy.
	*/
	if( window.location.hash == "#comments_wrapper" && $("#comments_wrapper").length > 0 )
	{
		loadComments( ips_config.file_id );
	}
	
	if( ips_config.version != 'pinestic' )
	{	
		/**
		* Categories menu in main menu list
		*/
		$(".categories-menu").addClass('tip-direction').attr( 'data-tip', 'left' ).addClass('responsive-ui').children('a').addClass('responsive-icon').append('<i class="i-icon i-menu"></i>');
	}
	
	if( jQuery.inArray( ips_config.version, [ 'pinestic' ] ) > -1 )
	{
		if( $( ".responsive-ui" ).length > 0 )
		{
			if( $( ".responsive-ui" ).length > 0 )
			{
				var sub_menu = $( ".responsive-ui li.user-menu" );
				sub_menu.find('ul').first().removeAttr('class').addClass('hidden-element');
				sub_menu.clone().removeAttr('class').appendTo( ".responsive-menu" ).find('a').first().removeAttr('class').addClass( 'responsive-action' ).on('click', function_ul );
				
			}
			else
			{
				$( ".responsive-ui li" ).clone().each(function(){
					var url = $(this).find('a');
					$( ".responsive-menu" ).prepend('<li class="hidden-element show-element"><a href="' + url.attr('href') + '">' + url.text() + '</a></li>');
				});
			}
		}
	}

    $(".categories-menu ul li:has(ul)").find("a:first").append(" &raquo; ");
	
	if( $('.ips-vote-file').length > 0 )
	{
		$('.ips-vote-file').on( ips_click, function () {
			$(this).vote();
		})
	}
	
	/** Add/Remove FAV */
	$('body').on( ips_click, '.ips-add-favourites', function () {
		if ( ips_user.is_logged == false )
		{
			return userAuth('login');
		}
		$(this).favouriteFile();
	})
	
	if( $('#go_to_page').length > 0 )
	{
		$('#go_to_page').on( 'submit', function ( event ) {
			event.preventDefault();
			$(this).goToPageNum();
		})
	}
	
	if( $('.ranking-vote-button').length > 0 )
	{
		$('.ranking-vote-button').on( 'click', function ( event ) {
			event.preventDefault();
			$(this).voteRanking();
		})
	}
	
	if( $('.user_menu_items').length > 0 )
	{
		var actualUrl = document.location.href.replace( ips_config.url, '' );
		$('.user_menu_items li a').each(function(){
			$(this).removeClass('active');
			if( $(this).attr("href").indexOf( actualUrl ) > 0 )
			{
				$(this).addClass('active');
			}
		});
	}
	
	
	if( ( $(".button-next").length > 0 || $(".button-previous").length > 0 ) && ips_config.version != 'pinestic' && ips_config.version != 'gag' && $(".file-container").length > 0 )
	{
		var top = ( ( $(".file-container").height() - $(".button-next").height() ) / 2.3 ) + $(".file-container").position().top;
		
		if( ips_config.version == 'bebzol' )
		{
			if( $(".file-container img").width() > 630 )
			{
				$(".button-next").removeClass('button-next').addClass('button-big-next');
				$(".button-previous").removeClass('button-previous').addClass('button-big-previous');
				var top = 0;
			}
		}
		
		if( top > 0 )
		{
			$(".button-next,.button-previous").css( 'top', top ).show(1);
		}
	}

	if( $('.gif_player').length > 0 )
	{
		/**
		* Animated Gif play
		*/
		$('.gif_player').gifPlay();
	}
	
	if( $('.video_player').length > 0 )
	{
		/**
		* Video play
		*/
		$('.video_player').each(function(){
			$(this).videoPlay();
		});
	}
	
	if( $('#ajax-personalize-form').length > 0 )
	{
		head.load( '/js/widgets/widget_personalize.js?ips_rand=' + ips_randomity);
	}
	
	if( $('.moderation-panel').length > 0 )
	{
		head.load( '/js/moderator.js?ips_rand=' + ips_randomity);
	}
	
	if( $('input.widget-url-copy').length > 0 )
	{
		$('input.widget-url-copy').on( 'click', function ( event ) {
			$(this).select().focus();
		});	
	}
	
	if( ips_config.version == 'vines' && $("#float_social").length > 0 )
	{
		if( $(window).width() > 1100 )
		{
			$("#float_social").lockfixed({
				container: $('#content'),
				offsetStart: 100,
				bottomBoundary: 50,
				duration: 0
			});
		}
	} 
	if( ips_config.js_tiptip )
	{
		$( '[title]' ).tipTip();
		$( 'img' ).tipTip( {
			attribute: 'alt'
		} );
	}
});

/** Document ready END */
function dialogWidth( width )
{
	return width > $(window).width() ? $(window).width() - 50 : width ;
}
function getStackTrace () {

  var stack;

  try {
    throw new Error('');
  }
  catch (error) {
    stack = error.stack || '';
  }

  stack = stack.split('\n').map(function (line) { return line.trim(); });
  return stack.splice(stack[0] == 'Error' ? 2 : 1);
}
function mobileT() {
	var a = navigator.userAgent.toLowerCase();
	var b = a.indexOf("android") > -1;
	var c = navigator.userAgent.match(/iPad/i) != null;
	var d = ((navigator.userAgent.match(/iPhone/i) != null) || (navigator.userAgent.match(/iPod/i)) != null);
	return (b||c||d)
}
/** Save user FB token */
function api_token( callback, scope, post_facebook )
{
	var resp = function( response) {
		
		var user_data = FB.getAuthResponse();
		
		FB.api( "/me/permissions",{ 'permission' : 'publish_actions' }, function (response) {
				if (response && !response.error && response.data.length > 0 ) {
					if( typeof response.data[0].status !== 'undefined' && response.data[0].status == 'granted' )
					{
						return IpsApp._ajaxAsync( '/ajax/api_facebook/', {
							ui_action: 'connect', 
							user_data: user_data, 
							post_facebook: ( typeof post_facebook == 'undefined' )
						}, 'POST' );
					}
				}
				if( typeof callback == 'function')
				{
					callback();
				}
			}
		);
	}
	
	if( scope )
	{
		return FB.login( resp, {
			scope: scope
		});
	}
	
	FB.getLoginStatus( resp );
}
/** MP4 video */
var ips_videos_html5 = new Array();

var current_video = { 
	timeout: false
};

only_autoplay = false;

function ips_video_mp4( id, autoplay, player )
{
	if( player == 'mediaelement' )
	{
		head.load(['/js/video_html5_player.js?ips_rand=' + ips_randomity,'/libs/MediaElement/mediaelement-and-player.min.js?ips_rand=' + ips_randomity], function () {
			head.load('/libs/MediaElement/mediaelementplayer.css?ips_rand=' + ips_randomity);
			ips_videos_html5.push({ 
				id : id, 
				video : new ips_video_ready( id, autoplay )
			});
		});
	}
	else
	{
		head.load(['/js/video_html5_player.js?ips_rand=' + ips_randomity, '//vjs.zencdn.net/4.3/video.js'], function () {
			head.load('//vjs.zencdn.net/4.3/video-js.css');
			ips_videos_html5.push({ 
				id : id, 
				video : new ips_video_ready( id, autoplay )
			});
		});
	}
}
function get_ips_video( id )
{
	for	( index = 0; index < ips_videos_html5.length; index++) {
		if( ips_videos_html5[index].id == id )
		{
			return ips_videos_html5[index].video;
		}
	}
	return false;
}
/** END */	
		
$.fn.lock = function()
{
	var element = $(this);
	
	if( $.cookie('ips_get_cookie') != null )
	{
		$(this).trigger('click');
	}
	else
	{
		//$.cookie('ips_get_cookie', 'ips_get_cookie', { expires: '60m', path: '/' });
		$( '.mejs-overlay' ).unbind('click');
		element.parents( '.mejs-container' ).find('.mejs-overlay-button').addClass('mejs-overlay-lock').html('<iframe width="40" height="20" frameborder="0" src="https://www.facebook.com/plugins/like.php?action=like&locale=fr_FR&href='+document.location.href+'?fb_likes" scrolling="no"></iframe>').on('mouseenter', function(){
			setTimeout(function () {
				element.trigger('click');
				element.parents( '.mejs-container' ).find('.mejs-overlay-button').remove('mejs-overlay-lock').html('');
			}, 	5000 );
		});
	}
}



/** Playe video with AJAX player */
$.fn.videoPlay = function( options )
{
	return $(this).on( 'click play', function ( e ) {
		e.preventDefault();
		
		var cnt = $(this);
		
		$.get("/ajax/video/" + cnt.attr("data-id"), $.extend( {}, options ), function( data ){
			cnt.hide().parent().append( data.replace('data-src', 'src').replace('async_iframe', '') );	
			
		});
		
	}).css( { 'margin-top': - $(this).height()/2 } ).fadeIn();
}

/** Play animated GIF */
$.fn.gifPlay = function()
{
	$(this).each(function(){
		var parent = $(this).parent();
		
		$(this).on( 'click', function ( event ) {
			event.preventDefault();
			var cnt = $(this);
			cnt.addClass('video_player_wait');
			
			var img = new Image();
			img.src = $(this).attr("data-gif");
			
			$( img ).load( function( event ){
				var media = '<img class="media-img" src="' + img.src + '" />';
				if( cnt.parent().hasClass('media-object') )
				{
					cnt.replaceWith( media );
				}
				else
				{
					cnt.parent().find('img').replaceWith( media );
					cnt.hide();
				}
			}).error(function (){
				cnt.attr( 'title', ips_i18n.__( 'js_alert_jquery' ) ); 
			});
		}).fadeIn();
		
		if( $(this).hasClass('init') )
		{
			$(this).trigger('click');
		}
	});
}


/**
* Simple messages wrapper
*/
function overflowMsg( msg )
{
	return showDialog( false, '<div class="dialog-vertical">' + msg + '</div>' );
}
function closeOverflowMsg( dialog, timeout )
{
	return setTimeout(function(){ 
		dialog.dialog( 'close' )
	}, timeout );
}


/**
 * Funkcja utrzymująca reklamy na ekranie.
 * Działamy na obiektach, nie w funkcji
 */

(function( $ ){
	$.fn.floatDiv = function( $container, startOffset, $additionalContainer ) {
		
		var top_margin = ips_config.version == 'demotywator' ? 50 : 30;
		var add_margin = 0;
		if( typeof startOffset !== 'undefined' )
		{
			if( typeof startOffset.add_margin !== 'undefined')
			{
				var add_margin = startOffset.add_margin;
			}
			
			if( typeof startOffset.top_margin !== 'undefined')
			{
				var top_margin = startOffset.top_margin;
			}
		}

		
		
		if ( $($container).length == 0 )
		{
			return false;
		}
		else
		{
			var offset = $($container).offset();
			var cnt_height = $($container).height();
		}
		
		if( typeof $additionalContainer !== 'undefined' && $($additionalContainer).length > 0 )
		{
			var offset = $($additionalContainer).offset();
		}
		
		if( typeof offset == 'undefined' )
		{
			//console.log( " offset == 'undefined' " + $container );
			return false;
		}
		
		var maxTopOffset = startOffset == 'bottom' ? ( offset.top + cnt_height + 100 ) : ( offset.top + top_margin ) ;
		var $element = this;
		
		$element.css("position", "absolute").css( "top", maxTopOffset );

		$(window).scroll(function() {
			var position = window.pageYOffset;
			
			if ( maxTopOffset - position < top_margin + add_margin ) 
			{
				if ( $element.css("position") == "absolute" )
				{
					$element.css("position", "fixed").css( "top" , top_margin + 'px');
				}
			}
			else if ( $element.css("position") == "fixed" )
			{
				$element.css("position", "absolute").css( "top", maxTopOffset );
			}
		});
	};
})( jQuery );


function facebookLogin()
{
	window.location = 'connect/facebook/';
}


function commentsButtons( item, width )
{
	if( typeof width == 'undefined' )
	{
		var width = 560;
	}
	
	var comments_id = item.attr('data-comments-box-id');
	
	var comments_container = $("#show-comments-box-" + comments_id);
	
	var url = item.attr('href');
	
	if( url )
	{
		if( !comments_container.hasClass('comment_now') )
		{
			comments_container.addClass('comment_now');
			comments_container.html('<fb:comments href="'+url+'" num_posts="10" width="'+width+'" notify="true" data-colorscheme="light"></fb:comments>');
			comments_container.slideDown();
			FB.XFBML.parse( document.getElementById("show-comments-box-" + comments_id) );
			item.find('span.show-comments-container').html( ips_i18n.__( 'js_comments_hide' ) );
		}
		else
		{
			comments_container.removeClass('comment_now');
			item.find('span.show-comments-container').html( ips_i18n.__( 'js_comments_show' ) );
			comments_container.slideUp();
		}
	}
	
}

function resizeActions( window_width )
{
	
	if( window_width < $('#content').width() + 10 )
	{
		//var start_rwd = 794;
		if( typeof start_rwd == 'undefined' )
		{
			start_rwd = window_width;
		}
		px = start_rwd - window_width;
	
	}
	
	console.log( 'Window width ' + window_width );
	
	if( window_width < 1024 )
	{
		if(  $('.fb-comments').length > 0 )
		{
			$('.fb-comments').each( function(){
				var par_width = $(this).parents('.comments-list-cnt').width();
				
				if( $(this).width() > par_width )
				{
					var comments_div = $($(this)[0].outerHTML);
					comments_div.attr( 'data-width', ( par_width * 0.95 ) );
					$(this).replaceWith( comments_div );
				}
			});
		}
		
		if(  $('.widget-fanbox').length > 0 )
		{
			$('.widget-fanbox > div').each( function(){
				var fanbox_div = $('<div class="fb-like-box"></div>');
				
				fanbox_div.attr( 'data-width', Math.round( $(this).parent().width() ) );
				fanbox_div.attr( 'data-send', $(this).attr('data-send') );
				fanbox_div.attr( 'data-href', $(this).attr('data-href') );
				fanbox_div.attr( 'data-layout', $(this).attr('data-layout') );
				fanbox_div.attr( 'data-colorscheme', $(this).attr('data-colorscheme') );
				fanbox_div.attr( 'data-font', $(this).attr('data-font') );
				fanbox_div.attr( 'data-share', $(this).attr('data-share') );
				fanbox_div.attr( 'data-show-faces', $(this).attr('data-show-faces') );
				$(this).replaceWith( fanbox_div );
				if( window_width < 800 )
				{
					$(this).parent().css( 'backgroud', 'none' );
				}
			});
		}
		
		return;
	}
	/**
	* Pływający box w poczekalni + sterowanie pływającymi reklamami prawa/lewa
	*/
	if( $("#float-wait-box").length > 0 )
	{
		
		var offset = $("#content").offset();
		var left = $("#content").innerWidth() + 10 ;

		$("#float-wait-box").fadeIn().css({ 'position': 'absolute', 'left': left, 'opacity': 1, 'top': 0 });
		
		$(".ads_left_side_list").show().floatDiv('#content');
		$(".ads_right_side_list").show().floatDiv('#float-wait-box', 'bottom');

	}
	else
	{
		$(".ads_left_side_list, .ads_right_side_list").floatDiv( '#content', false, '.ads_under_menu' );
	}
}
function autoLoginFacebook( accessToken )
{
	if( parseInt( ips_config.auto_login.facebook ) && !ips_user.is_logged && $.cookie('ips_connect_redirect') !== 'true' && document.location.href.indexOf('/login') <= 0 )
	{
		window.location = "connect/facebook/";
	}
}
function autoLoginNK()
{
	IpsApp._ajax( '/ajax/api_nk/', { }, 'GET', 'json', false, function( response )
	{ 
		if( response.reload == true )
		{
			window.location.reload();
		}
		else if( response.redirect == true )
		{
			window.location = "connect/nk/";
		}
		else if( response.button != '' )
		{
			$( '.nk-login' ).html( response.button );
			NkConnectpageURL = response.url;
		}
	});
}
function NkConnectPopup() {
    var targetWin = window.open ( NkConnectpageURL, 'Zaloguj z NK', 'modal=yes, toolbar=no, location=yes, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=490, height=247, top='+( screen.height/2 ) - ( 247/2 ) + ', left='+( screen.width/2 ) - ( 490/2 ) );
}


$.fn.goToPageNum = function()
{
	var current_page = $(this).find('input[name="current_page"]').val();
	var page_num = parseInt( $(this).find('input[name="page_num"]').val() );
	
	$(this).find('input[name="page_submit"]').fadeOut( 200, function(){
		if( typeof page_num === 'number' && !isNaN( page_num ) )
		{
			window.location.href= current_page + page_num;
		}
		else
		{
			$(this).parent().find('input[name="page_num"]').val('');
			$(this).parent().find('input[name="page_submit"]').fadeIn('fast');
		}
	});
}
/**
* Głosowanie ranking
*/
$.fn.voteRanking = function()
{
	var file_id = $(this).parent().attr('data-id'),
		btn_vote = $(this),
		up_vote = btn_vote.hasClass( 'ranking-up' );
	
	IpsApp._ajax( '/ajax/vote_ranking/' + file_id, { vote_type : ( up_vote ? 'votes_up' : 'votes_down' ) }, 'POST', 'json', false, function( response )
	{ 
		if( typeof response.error !== 'undefined' )
		{
			showDialog( false, response.error, true);
		}
		else if( typeof response.success !== 'undefined' )
		{
			btn_vote.parent().find('a').removeClass('ranking-voted');
			$('#ranking-' + file_id + ' ' + ( up_vote ? '.ranking-down' : '.ranking-up' ) ).addClass('ranking-voted');
			btn_vote.parent().find('div').html( response.votes_opinion );
		}
	});
	return false;
}

$.fn.vote = function()
{
	var file_id = $(this).attr('data-id');
	var vote_type = $(this).attr('data-action');
	
	IpsApp._ajax( '/ajax/vote_file/' + file_id, { vote_type: vote_type }, 'POST', 'json', false, function( response )
	{ 
		if( typeof response.error !== 'undefined' )
		{
			if( response.error == 'login' )
			{
				return userAuth('login');
			}
			else
			{
				showDialog( false, response.error, true);
			}
		}
		else if( typeof response.success !== 'undefined' )
		{
			if( vote_type == 'vote_archive' )
			{
				$( '.dobre_' + file_id ).html( response.success );
			}
			else
			{

				$('.vote_buttons_' + file_id + ' img' ).attr('src', function(i, val) {
					return val.replace('_activ', '');
				});
				
				$('.vote_buttons_' + file_id + ' .' + ( vote_type == 'vote_file_down' ? 'votes_down' : 'votes_up' ) + ' img' ).attr('src', function(i, val) {
					return val.replace('.png','_activ.png');
				});
				
				if( jQuery.inArray( ips_config.version, [ "bebzol", "gag", "vines" ] ) > -1 )
				{
					$('.file_votes_' + file_id).html( response.success );
					$('.vote_buttons_' + file_id + ' span').removeClass( 'voted' );
					$('.vote_buttons_' + file_id + ' .' + ( vote_type == 'vote_file_down' ? 'votes_down' : 'votes_up' ) ).addClass( 'voted' );
				}
				else
				{
					$('.file_votes_' + file_id).html( '(' + response.votes_count + ')' );
				}	
			}
			
			$('.file_opinion_' + file_id).html( response.votes_opinion );
			$('.file_opinion_' + file_id).parent().fadeIn( 300 );
		}
	});
}


$.fn.favouriteFile = function()
{
	var btn_fav = $(this);
	var file_id = $(this).attr('data-id');

	IpsApp._ajax( '/ajax/add_favourite/' + file_id, { fav_action: ( btn_fav.hasClass( 'delete' ) ? 'delete' : 'add' ) }, 'POST', 'json', false, function( response )
	{ 
		if( typeof response.error !== 'undefined' )
		{
			showDialog( false, response.error, true);
		}
		else if( typeof response.success !== 'undefined' )
		{
			if( btn_fav.next().hasClass( 'fav-response' ) )
			{
				btn_fav.next().remove();
			}
			
			if( btn_fav.parent().hasClass( 'fav-response' ) )
			{
				btn_fav.parent().html( $(response.success) );
			}
			else
			{
				var rsp = $(response.success);
				btn_fav.after( rsp );
				setTimeout(function(){
					rsp.fadeOut('slow', function(){
						rsp.remove();
					});
				}, 5000 )
			}
		}
	});
}	

/**
* Nawigacja pomiedzy kolejnymi materiałami
* podczas pokazu slajdów, nawigacji klawiszami
* lub buttonami w dodatku: "Widget Pasek pływający na dole strony"
*/
function scrollItem( scroll_type )
{
	if ( typeof onFast !== 'undefined' )
	{
		return setNav( scroll_type );
	}
	
	var ie = document.all ? true : false;
	
	if ( ie )
	{
		var IEbody = document.compatMode && document.compatMode != "BackCompat" ? document.documentElement: document.body
	}
	
	if ( typeof images_list == 'undefined' )
	{
        var elements = $( '#content .item');
		
		var columns = ( $('body').hasClass('two_columns') ? 2 : ( $("body").hasClass('three_columns') ? 3 : 1 ) );
		
		images_list = [];
		
		elements.each( function( index ){
			if( index % columns == 0 )
			{
				images_list.push( elements[index] );
			}
		});
    }
	
	if ( !images_list )
	{
		return false
    }
	
	count = images_list.length;

	var ofsset = ie ? IEbody.scrollTop : window.pageYOffset;
	
	var num = images_list[count - 1];
   
   for ( var i = 0; i < count; i++ )
   {
		if ( images_list[i].offsetTop >= ofsset )
		{
            var num = images_list[i];
			if ( scroll_type == 'previous' )
			{
				var num = i > 0 ? images_list[i - 1] : images_list[count - 1];
            }
			else if ( scroll_type == 'next' )
			{
                if ( i < (count - 1) && images_list[i].offsetTop == ofsset )
				{
					var num = images_list[i + 1];
				}
				else if( images_list[i].offsetTop == ofsset )
				{
                    var num = images_list[0]
                }
            }
            break
        }
    }
	$('html, body').animate( { scrollTop: num.offsetTop }, 1000 );
}
/**
* Nawigacja klawiszami strzałek
*/
$(function() {
	$(document).keydown( function(e){
		e = e ? e : window.event;
		if ( e.target.type == 'textarea'  || e.target.type == 'text' || e.target.type == 'password' )
		{
			return;
		}
		
		key = e.keyCode ? e.keyCode : e.which;
		
		if( key == 37 || key == 90 )
		{
			scrollItem('previous');
		}
		else if( key == 39 || key == 88 )
		{
			scrollItem('next');
		}
		
	});
})

function showDialog( title, message, alert, type, dialog_uid )
{
	
	if( alert == true )
	{
		var icon = type == 'info' ? 'attention-icon.png' : 'blocked-icon.png';
		var message = '<p class="dialog-alert"><img src="images/' + icon + '"><span>' + message + '</span></p>';
		var title = ips_i18n.__( 'js_alert' );
	}
	
	if( typeof dialog_uid === 'undefined' )
	{
		var dialog_uid = 'dialog';
	}
	
	var dialog_div = $("#" + dialog_uid);
	
	if( dialog_div.length === 0 )
	{
		var dialog_div = $('<div>').attr({
			id: dialog_uid
		}).appendTo('body');
	}
	
	return dialog_div.html( message ).dialog({
		title: title,
		autoOpen: true,
		width: dialogWidth( 600 ),
		resizable: false,
		minHeight: 50,
		buttons: false,
		show: ips_config.animation.show,
        hide: ips_config.animation.hide,
		modal: true,
		dialogClass: ( title === false ? 'dialog-hide-title' : '' ),
		close: function() {},
		open: function( event, ui ) {
			$('.ui-widget-overlay').css('opacity', '0.8');
			$('.ui-widget-overlay').on('click', function(){
				dialog_div.effect( hide_effect, {}, 500, callback() );
				function callback() {
					setTimeout(function() {
						$(".ui-dialog").remove().fadeOut();
					}, 500 );
				};
			});
        }
	});
	
};


function userAuth( type )
{
	IpsApp._ajax( '/ajax/auth_' + type + '/', {}, 'GET', 'json', false, function( response ){
		if( typeof response.content !== 'undefined' )
		{
			return showDialog( response.phrase, response.content );
		}	
	});
	
	return false;
};
function loadHistory( id, div )
{
	if( $("#user-history-panel:visible").length == 0 )
	{
		IpsApp._ajax( '/ajax/history/' + id, {}, 'GET', 'json', false, function( response ){
			if( typeof response.content !== 'undefined' )
			{
				$("#user-history-panel").html( response.content ).slideDown();
				
				$("#user-history-panel ul li").each( function ( index ){
					$(this).delay( 50 * index ).fadeIn('slow');
				});
			}	
		});
	}
	else
	{
		$("#user-history-panel:visible").slideUp();
	}
}


function user_ban_time( id, ban_type )
{
	var response = IpsApp._ajax( '/ajax/user_ban/' + id, { action : ban_type }, 'POST' );
	
	if( typeof response.error !== 'undefined' )
	{
		showDialog( response.error, response.message );
	}
	else if( typeof response.success !== 'undefined' )
	{
		$('#user_ban_time').html( response.success );
	}
}

function nl2br( txt )
{
	return txt.replace ( /\n/gm, '<br />' );
}





/**/
function systemReport( id, report_type, phrase )
{

	if ( ips_user.is_logged == false )
	{
		return userAuth('login');
	}
	/**
	* comment, message, file
	*/
	
	IpsApp._ajax( '/ajax/get_template/' , { template_name : ( report_type == 'file' ? 'reports_file' : 'reports_all' ) }, 'POST', 'json', false, function( response ){

		var div = $('<div style="display:none" title="' + ips_i18n.__( 'js_report_title' ) +'">' + response.content + '</div>');
		
		var buttonOpts = {};
		
		buttonOpts[phrase] = function(){
			
			var dialog = overflowMsg( ips_i18n.__( 'js_report_sending' ) );
			
			var option = div.find("#report_subject option:selected");
			
			if ( typeof option.attr( 'data-url' ) != 'undefined' )
			{
				var url = prompt( ips_i18n.__( 'js_report_duplicate_msg' ), '' );
			}
			else
			{
				var url = document.location.href;
			}
			
			IpsApp._ajax( '/ajax/reporting/', { id: id, report_type: report_type, subject: option.val(), additional: url, file_url : document.location.href  }, 'POST', 'json', false, function( response ){
				if( typeof response.error !== 'undefined' )
				{
					dialog.html( response.error );
				}
				else if( typeof response.success !== 'undefined' )
				{
					dialog.html( ips_i18n.__( 'js_report_ok' ) );
				}
				else
				{
					dialog.html( ips_i18n.__( 'js_alert' ) );
				}
				
			});
			
			closeOverflowMsg( dialog, 3000 );
			
			$(this).dialog("close");
		}

		div.dialog({
			title: ips_i18n.__( 'js_report_title' ),
			autoOpen: true,
			hide: "fadeOut",
			resizable: false,
			buttons: buttonOpts, 
			modal: true, 
			show: "slide"
		});
	} );
	
	
}	
	
	


function columnLayout( two_columns )
{	
	if( two_columns !== null )
	{	
		two_columns.each(function(i){
			if( i%2 == 0 )
			{
				$(this).addClass("first");
			}
			else
			{
				$(this).imagesLoaded(function(){
					if( ips_config.version != 'vines' )
					{
						var second = two_columns.eq(i-1);
						var setheight = $(this.elements).height();
						
						if( second.height() > $(this.elements).height() )
						{
							var setheight = second.height();
						}
						
						$(this.elements).height( setheight );
						
						second.height( setheight );
					}
					$(this.elements).addClass("second").after('<div class="clear_columns"></div>');
				});
			}
		});
		two_columns.last().after('<div class="clear_columns"></div>');
	}


	
}




/**
* Przygotowanie i załadowanie skryptu odpowiedzialnego za ładowanie
* kolejnych materiałów bez przeładowania strony
*/
function infiniteRedirect( url )
{
	window.location.href = url;

}

function infiniteScroll( container, items, on_click, callback_function, options )
{
	
	head.load("/libs/InfiniteAjaxScroll/jquery-ias.js?ips_rand=" + ips_randomity, function () {
		console.log('Trigger Infinity scroll');
		try{
		
			var ias = jQuery.ias( $.extend({}, {
				container			 : container,
				item				 : items,
				next				 : '#next-scroll',
				pagination			 : '.next_prev',
				trigger				 : '.infinitescroll_button',
				triggerOnClick		 : on_click,
				triggerPageThreshold : ips_config.infinity.pages + 1,
				customTriggerProc	 : function( trigger ){
					setTimeout(function(){
						trigger.removeClass('hidden-element').css('display', 'block');
					}, 500 );
				},
				debug				 : true
			}, options ));
			
			
			ias.on('noneLeft', function() {
				$(this.opts.next).hide();
			});
			
			ias.on('next', function( url ) {
				$(this.opts.next).attr( 'href', $(this.opts.next).attr( 'href' ).replace(/([(\/|\,)?]{1,})(\d+)$/, function($0, $1, $2)
				{
					return $1 + ( parseInt($2, 10) + 1 );
				}));
			});
			/** loadCustomInfinity depends on 'load' */
			ias.on('load', function( event ) {
				event.url = $(this.opts.next).attr( 'data-ajax' ).replace(/page=(\d+)$/, function($0, $1)
				{
					return "page=" + ( parseInt($1, 10) + 1 ) ;
				});

				$(this.opts.next).attr( 'data-ajax', event.url );
			});
			
			callback_function( ias );
			
		}catch(b){
			console.log('Infinity scroll catch:');
			console.log(b);
		}
	});
}


function loadInfiniteScroll( container, items )
{
	infiniteScroll( container, items, ips_config.infinity.onclick, function( ias )
	{
		ias.on('rendered', function( data, items ) {
			$(items).fadeIn().find('.gif_player').gifPlay();
		});
		
		ias.on('loaded', function( data, items ) {
			
			elements = [];
			for( i=0; i <= items.length; i++ )
			{
				if( typeof items[i] !== 'undefined')
				{
					triggerEvent('loaded');
					FB.XFBML.parse( items[i] );
					
					$(items[i]).find('.ips_image_share.facebook').each(function(i)
					{
						elements.push( {'this_el': $(this), 'url': $(this).attr("data-href")} );
					});
					
					FB.Share.renderCall( items[i].querySelectorAll('[data-name]') );
					
					$(items[i]).find('img[data-original]').each(function(i)
					{
						$(this).attr( "src", $(this).data("original") ).removeAttr("style");
					});
				}
			}

			setTimeout(function(){
				if( $("body").hasClass("two_columns") ){
					columnLayout( $(items) );
				}
			}, 500 );
		});
	});
}
function plusOneCount(o){
	$.post("/ajax/google_plus/", {
		href: o.href, 
		call: o.state 
	} ,function(data){
		
	});
}
function parseURL( url )
{
    var a =  document.createElement('a');
    a.href = url;
    return {
        source: url,
        protocol: a.protocol.replace(':',''),
        host: a.hostname,
        port: a.port,
        query: a.search,
        params: (function(){
            var ret = {},
                seg = a.search.replace(/^\?/,'').split('&'),
                len = seg.length, i = 0, s;
            for (;i<len;i++) {
                if (!seg[i]) { continue; }
                s = seg[i].split('=');
                ret[s[0]] = s[1];
            }
            return ret;
        })(),
        file: (a.pathname.match(/\/([^\/?#]+)$/i) || [,''])[1],
        hash: a.hash.replace('#',''),
        path: a.pathname.replace(/^([^\/])/,'/$1'),
        relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [,''])[1],
        segments: a.pathname.replace(/^\//,'').split('/')
    };
}


var b = navigator.userAgent.toLowerCase();

// Figure out what browser is being used
jQuery.browser = {
	version: (b.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [])[1],
	safari: /webkit/.test(b),
	opera: /opera/.test(b),
	msie: /msie/.test(b) && !/opera/.test(b),
	mozilla: /mozilla/.test(b) && !/(compatible|webkit)/.test(b)
};
jQuery.live = jQuery.on;

/* Set browser CSS **/
css_browser_selector( navigator.userAgent );

