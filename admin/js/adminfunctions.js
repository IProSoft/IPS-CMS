adminAjaxFile = 'admin-ajax.php';


function updateQueryStringParameter(uri, key, value) {
  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
  if (uri.match(re)) {
    return uri.replace(re, '$1' + key + "=" + value + '$2');
  }
  else {
    return uri + separator + key + "=" + value;
  }
}

function loadStats( ajax_function, div_id )
{
	
	$.ajax({
		async: "true",
		type: "GET",
		url: adminAjaxFile,
		data : { ajax_action : ajax_function },
		success: function( data ) {
			if( data != '' )
			{
				$( '#'+div_id ).html( data );
			}
		}
	});
}
function checkUpdatesCount()
{	
	$('#updates-number').html( '<img src="images/system-loading.gif" style="margin: -5px auto 2px;">' );	

	$.ajax({
		async: "true",
		type: "GET",
		url: adminAjaxFile,
		data : { ajax_action : 'updates_count' },
		dataType: 'json',
		success: function( data ) {
			if( typeof data.count != 'undefined' &&  data.count > 0  )
			{
				$('#updates-number').fadeOut('fast', function(){
					$('#updates-number').html( '<span class="nice-number">' + data.info + '</span>' ).fadeIn();
					$('#popup_admin_cover').fadeIn();
				});
			}
			else
			{
				$('#updates-number').slideUp();
			}
		}
	});
	
}
function edycjaZmienPlik( selectId )
{
	var file = selectId.options[selectId.selectedIndex].value;
	if ( file ) 
	{
		if( typeof input != "undefined" )
		{
			var theme = input.options[input.selectedIndex].innerHTML;
			window.location.href='/admin/admin.php?route=template&action=files&file=' + file + '#' + theme;
		}
		else
		{
			window.location.href='/admin/admin.php?route=template&action=files&file=' + file;
		}
		
	}
}


function loadjscssfile(filename)
{
  var fileref=document.createElement("link")
  fileref.setAttribute("rel", "stylesheet")
  fileref.setAttribute("type", "text/css")
  fileref.setAttribute("href", filename)
  document.getElementsByTagName("head")[0].appendChild(fileref)
}
    var d = document;
    var safari = (navigator.userAgent.toLowerCase().indexOf('safari') != -1) ? true : false;
    var gebtn = function(parEl,child) { return parEl.getElementsByTagName(child); };
	var gebqs = function(parEl,child) { return parEl.querySelectorAll(child); };

    window.onload = function() {
        
        var body = gebtn(d,'body')[0];
        body.className = body.className && body.className != '' ? body.className + ' has-js' : 'has-js';
        
        if (!d.getElementById || !d.createTextNode) return;
        var ls = gebqs(d,'.label_radio');
        for (var i = 0; i < ls.length; i++) {
            var l = ls[i];
            if (l.className.indexOf('label_') == -1) continue;
            var inp = gebtn(l,'input')[0];
            if (l.className == 'label_check') {
                l.className = (safari && inp.checked == true || inp.checked) ? 'label_check c_on' : 'label_check c_off';
                l.onclick = check_it;
            };
            if (l.className == 'label_radio') {
                l.className = (safari && inp.checked == true || inp.checked) ? 'label_radio r_on' : 'label_radio r_off';
                l.onclick = turn_radio;
            };
        };
    };
    var check_it = function() {
        var inp = gebtn(this,'input')[0];
        if (this.className == 'label_check c_off' || (!safari && inp.checked)) {
            this.className = 'label_check c_on';
            if (safari) inp.click();
        } else {
            this.className = 'label_check c_off';
            if (safari) inp.click();
        };
    };
    var turn_radio = function() {
        var inp = gebtn(this,'input')[0];
        if (this.className == 'label_radio r_off' || inp.checked) {
            var ls = gebtn(this.parentNode,'label');
            for (var i = 0; i < ls.length; i++) {
                var l = ls[i];
                if (l.className.indexOf('label_radio') == -1)  continue;
                l.className = 'label_radio r_off';
            };
            this.className = 'label_radio r_on';
            if (safari) inp.click();
        } else {
            this.className = 'label_radio r_off';
            if (safari) inp.click();
        };
    };


function checkAll( inp ){
	var checked_status = inp.checked;
	$('body input[type=checkbox]').each(function(){
		this.checked = checked_status;
	});
}
	function menuSlide(tab){
		$(".active_tabs").removeClass("active_tabs");
		$('[title^="'+tab+'"]').addClass("active_tabs");
		$(".content_tabs").slideUp();
		$("#"+tab).slideDown();
	}
	
function showLoadWait()
{
	var opts = {
	  lines: 17, // The number of lines to draw
	  length: 9, // The length of each line
	  width: 3, // The line thickness
	  radius: 15, // The radius of the inner circle
	  corners: 1, // Corner roundness (0..1)
	  rotate: 0, // The rotation offset
	  direction: 1, // 1: clockwise, -1: counterclockwise
	  color: '#000', // #rgb or #rrggbb or array of colors
	  speed: 0.9, // Rounds per second
	  trail: 100, // Afterglow percentage
	  shadow: false, // Whether to render a shadow
	  hwaccel: false, // Whether to use hardware acceleration
	  className: 'spinner', // The CSS class to assign to the spinner
	  zIndex: 2e9, // The z-index (defaults to 2000000000)
	  top: 'auto', // Top position relative to parent in px
	  left: 'auto' // Left position relative to parent in px
	};
	var target = document.getElementById('spinner_loader');
	var spinner = new Spinner(opts).spin(target);

	$('#spinner_loader').show();
}
function hideLoadWait()
{
	$('#spinner_loader').fadeOut( 'fast', function(){
		actionAdminMessage( '' );
	});
}
function actionAdminMessage( msg )
{	
	$('#spinner_loader').html('<div id="popup_admin_cover"><div id="popup_admin"><div id="popup_admin_content"><div id="popup_message">' + msg + '</div></div></div></div>');
}
function actionAdminError( message ){
	var message = typeof message !== 'undefined' ? message : 'Wystąpił błąd' ;
	
	$('#spinner_loader .spinner').fadeOut( 'fast', function(){
		$('#spinner_loader').html('<div id="popup_admin_cover"><div id="popup_admin"><div id="popup_admin_content"><div id="popup_message">' + message + '</div><br><div id="popup_panel"><a href="#" id="close_spinner" class="simple-blue-button">Ok</a></div></div></div></div>');
	});
}
function actionAdmin( option )
{
	var ids = [];
	var ajax_additional = [];
	
	$(".checkoption").each( function(){
		if( $(this).is(':checked') ){
			if( $(this).val() != 'on' )
			{
				if( option == 'delete_hook' )
				{
					ajax_additional.push( $(this).attr( 'data-hook' ) );
					ids.push( $(this).val() );
				}
				else
				{
					ids.push( parseInt( $(this).val() ) );
				}
			}
		}
	});
	
	if( option == 'save_hook_position' )
	{
		var ids = [];
		$(".hook_option").each( function(){
			
			var priority = {};
			priority.id = $(this).val();
			priority.priority = $(this).parents('tr').find('.item-priority').text();
			ids.push( priority );
			
		});
	}
	
	if( ids.length > 0 )
	{
		showLoadWait();
		if( option == 'userban' )
		{
			ajax_additional = $("#userban").val();
		}
		else if( option == 'category_change' )
		{
			ajax_additional = $("#category_id").val();
		}
		
		$.post( adminAjaxFile, { ajax_actions:option, ajax_data: ids, ajax_additional : ajax_additional } ,function(data){
		
		}).done(function( data ) {
			if( data == 'true' )
			{
				$('#spinner_loader .spinner').fadeOut( 'fast', function(){
					actionAdminMessage( 'Akcja wykonana poprawnie. Za chwilę nastąpi przekierowanie.' );
					setTimeout("location.reload(true);",1000);
				});
			}
			else{
				actionAdminError();
			}
		})
		.fail(function() {
			actionAdminError();
		});
	}
	else
	{
		alert('Nic nie zaznaczono');
	}
	
	return false;
}

function webFont( element ){

	var font = element.find('a').attr('data-font').replace( /-/g, ' ' );
	
	$.post( adminAjaxFile, { ajax_actions: 'get_font', ajax_data: font }, function( data ){
		
		var css = $("<link>", {
		  "rel" : "stylesheet",
		  "type" :  "text/css",
		  "href" : data.url
		})[0];

		css.onload = function(){
			setTimeout(function(){
				element.find( 'span' ).html( $('.font_prewiew_input').val() );
			},1000);
		};

		document
		  .getElementsByTagName("head")[0]
		  .appendChild(css);
		
	}, "json");
}

function importFont(){
	
	$("#dialog").html('<form action="admin-save.php" enctype="multipart/form-data" method="post" id="fontimport">Wybierz plik czcionki w formacie TTF <br /> <input type="file" name="fontimport" value=""/></form>');
	$("#dialog").dialog({
	 	title: "Import Czcionki",
	 	autoOpen: true,
		width: 600,
		resizable: false,
		buttons: {
		"Dodaj": function(){ 
				$("#fontimport").submit();
			},
		"Zamknij": function(){ 
				$(this).dialog("close");
			}
		}
	});
	return false;
}
function getHookPriority( event, ui )
{
	if( typeof hooks_priority == 'undefined' )
	{
		hooks_priority = [];
	}
	
	var hook = ui.item.attr('data-hook');
	
	if( typeof hooks_priority[hook] == 'undefined' )
	{
		hooks_priority[hook] = [];
		hooks_priority[hook]['priority'] = [];
		hooks_priority[hook]['tr'] = ui.item.parents('tbody').find('tr[data-hook="' + hook + '"]');
		
		hooks_priority[hook]['tr'].each(function(){
			hooks_priority[hook]['priority'].push( parseInt( $(this).find('.item-priority').text() ) );
		})
	}
}
function updateHookStatus( event, ui )
{
	if( $(".hook_save_selected:visible").length == 0 )
	{
		$(".hook_save_selected").fadeIn();
	}
	
	var hook = ui.item.attr('data-hook');
	
	ui.item.parents('tbody').find('tr[data-hook="' + hook + '"]').each(function( index ){
		$(this).find('.item-priority').html( hooks_priority[hook]['priority'][index] );
	})
}


function testEmail( email, subject, message, footer )
{
	var data = { 
		email : email, 
		subject : subject,
		message : $("<div>").text(message).html(),
		footer: footer
	};
	if( email.length > 0 )
	{
		var test_status = $( ".test_mail_input_status" );
		
		$.post( adminAjaxFile, { ajax_actions: 'test_mail', ajax_data: data } ,function(data){
		
		}).done(function( data ) {
			
			if( data == 'true' )
			{
				test_status.attr('src', 'images/icons/update-success.png');
			}
			else
			{
				test_status.attr('src', 'images/icons/update-error.png');
			}
		})
		.fail(function() {
			test_status.attr('src', 'images/icons/update-error.png');
		});
		test_status.fadeIn();
		setTimeout(function(){
			test_status.fadeOut();
		}, 3000	);
	}
	
	return false;
}





$(document).ready(function() {
	
	$('body').on( 'click', '.fancy_radio_check label', function () {
		$(this).parent().find('label').removeClass('active');
		$(this).addClass('active').find( 'input' ).attr( 'checked', 'checked' );
	});
	
	$('body').on( 'click', '.checkoption', function () {
		$(this).parents( 'tr' ).toggleClass('is_checked')
	})
	
	
	
	$('body').on( 'click', '.featured-hover > td', function (i) {

		if( $(i.target).is('td') )
		{
			if( $(this).parent( 'tr' ).hasClass('featured-hover') )
			{
				var box = $(this).parents( 'tr' ).toggleClass('is_checked').find('.checkoption');
				if( box.is(':checked'))
				{
					box.removeAttr("checked")
				}
				else
				{
					box.attr("checked", "checked")
				}
			}
		}
	});
	

	
	
	
	
	$('.ads_form').on( 'click', '.save', function () {
		$.post( adminAjaxFile, { ajax_actions: 'ads_edit_save', ajax_data: $(this).parents('form').formParams() }, function( data ){
			alertify.log( data.info, data.status, 4000 );
			
			if( data.status == 'success' )
			{
				var form = $('.ads_form'),
					ad_unique_name = form.find('[name="ad_unique_name"]').val();
				
				form.slideUp();
				
				$('.' + ad_unique_name).find('[name="ad_content"]').val( data.ad );
				
				$('.' + ad_unique_name).find('.ads_edit').attr('data-activ', data.activ );
				
			}
		}, "json");
		return false;
	});
	
	$('body').on( 'click', '.ads_edit', function () {
			var form = $('.ads_form'),
				activ = $(this).attr('data-activ'),
					parent = $(this).parents('.features-table-actions');
			
			form.find('.on,.off').removeAttr('checked');
			
			form.find( ( activ == 1 ? '.on' : '.off' ) ).attr( 'checked', 'checked' ).parent().addClass('active');
			
			form.find('.ads_top').val( parent.find('[name="ads_title"]').val() );
			form.find('[name="ad_content"]').val( parent.find('[name="ad_content"]').val() );
			form.find('[name="ad_unique_name"]').val( parent.find('[name="ads_unique_name"]').val() );
			
			form.find('.div-info-message p').html( parent.find('[name="ads_sizes"]').val() );
			
			form.show();
			
		return false;
	});
	

	$('.font_delete').on( 'click', function () {
		$.post( adminAjaxFile, { ajax_actions: 'font_delete', ajax_data: $(this).attr('data-font').replace( /-/g, ' ') }, function( data ){
			alertify.log( data.info, "", 4000 );
		}, "json");
		
		$(this).parents('.option-cnt').fadeOut();
		
		return false;
	});
	
	$('.font-import').on( 'click', $.debounce( 5, function () {
		$.post( adminAjaxFile, { ajax_actions: 'add_font', ajax_data: $(this).attr('data-font').replace( /-/g, ' ') }, function( data ){
			alertify.log( data.info, "success", 4000 );
		}, "json");
		
		$(this).parents('.option-cnt').fadeOut();
		
		return false;
	}));
	
	$('.font_prewiew_input').on('input', $.debounce( 5, function () {
		$('.font-loaded .font-preview').html( $(this).val() );
	}));
	
	$(window).on('scroll.fonts', $.debounce( 5, function () {
		$('.font-load').each(function(){
			if( $(this).isOnScreen() )
			{
				webFont( $(this) );
				$(this).removeClass('font-load').addClass('font-loaded')
			}
		});
	})).trigger( 'scroll.fonts' );
	
	
		
	$('.ddslick').ddslick({
		onSelected: function( selected ){
			$(selected.selectedItem).parents('.option-inputs').find('input.ddslick-value').val( selected.selectedData.value );
		}   
	});
	
	$(".ips-confirm").on("click" , function( e ){
		e.preventDefault();
		var link = $(this).attr('href');
		$( "#ips-confirm-system" ).find('strong').html( $(this).text() );
		$( "#ips-confirm-system" ).dialog({
			resizable: false,
			height:240,
			width: 500,
			modal: true,
			buttons: [
				{
					text: "Ok",
					click: function() {
						$( this ).dialog( 'close' );
						window.location.href = link;
					}
				},
				{
					text: ips_i18n.__( 'js_common_cancel' ),
					click: function() {
						$( this ).dialog( 'close' );
						return false;
					}
				}
			]
		});
		return false;
	});
	
	$(".plugin-buy-top").on("click", '.close', function( e ){
		$(this).parent().fadeOut();
	});
	
	
	$(".css-lightbox-a").on("click", function( e ){
		$(this).toggleClass('activ');
		return false;
	});
	$(".css-lightbox").on("click", function( e ){
		$(this).parent().toggleClass('activ');
		return false;
	});
	
	$(".admin-thumb-delete").on("click", function( e ){
		
		$.get( $(this).attr('href'), {});
		
		$(this).parents('.admin-thumb').animate({
			width: 'toggle',
		}, 2000, function() {
			$(this).remove();
		});
		
		return false;
	});
	$(".admin-thumb-activ").on("click", function( e ){
		
		$.get( $(this).attr('href'), {});
		
		$(this).parents('.admin-thumb').toggleClass('activ');
		
		$(this).text( ( $(this).parents('.admin-thumb').hasClass('activ') ? $(this).attr('data-off') : $(this).attr('data-on') ) )
		
		return false;
	});
	
	
	$(".plugin-buy-top").on("click", '.enter', function( e ){
		e.preventDefault();
		$(this).parent().find('form').fadeIn();
	});
	
	$(".plugin-buy-form").on( 'submit', function( e ){
		e.preventDefault();
		
		var form = $(this);
		var license = form.find('.license').val();
		var plugin_hash = form.find('.plugin_hash').val();
		
		form.find('.button').addClass('loading');
		
		
		$.post( adminAjaxFile, { license_number: license, plugin_hash : plugin_hash, ajax_data : true, ajax_actions: 'check_license' }, function( data ){
			if( data.response == 'installed' )
			{
				window.location.href='/admin/route-plugins';
				return true;
			}
			form.find('.button').removeClass('loading');
			form.find('.msg').html( data.response );
		}, "json");
		
	});
	
	$("#watermark_remove").on("change", function( e ){
		if ( $(this).is(":checked") )
		{
			$("#watermark_remove_cnt").css("display", "block");
		}
		else
		{ 
			$("#watermark_remove_cnt").css("display", "none");
		}
	});

	$( ".import_pages_limit" ).on("click", function( e ){
		
		var limit = parseInt( $("#import_pages_limit").val() );
		
		var value = 'up';
		if( $(this).hasClass( 'down' ) )
		{
			var value = 'down';
		}
		
		var limit = ( value == 'down' ? limit - 1 : limit + 1 );
	
		if( limit > 1 || ( value == 'down' &&  limit > 0 ))
		{
			$("#import_pages_limit").val( limit );
			
			if ( limit > 1 )
			{
				$("#import_pages_other").show();
			}
			else
			{ 
				$("#import_pages_other").hide();
			}
		}
	})	
	
	if( $( ".range_cnt" ).length > 0 )
	{
		$( ".range_cnt" ).each(function(){
			var slider = $(this).find('.range_slider'),
				input = $(this).find('input'),
				text = $(this).find('.range_text'),
				min = $(this).find('.range_text.min'),
				max = $(this).find('.range_text.max'),
				split = input.val().split(',');
			
			slider.slider({
				max: parseInt( input.attr('data-max') ),
				min: parseInt( input.attr('data-min') ),
				range: true,
				step: 1,
				values: split,
				slide: function( event, ui ) {
					input.val( ui.values[ 0 ] + "," + ui.values[ 1 ] );
					
					min.html( ui.values[ 0 ] );
					max.html( ui.values[ 1 ] );
				}
			});
			min.html( input.val().replace( ',', ' - ' ) );
			
			min.html( split[ 0 ] );
			max.html( split[ 1 ] );
		});
	}
	
	
	if( $( ".number_ranger" ).length > 0 )
	{
		$( ".number_ranger" ).each(function(){
			$(this).spinner({
				max: $(this).attr('data-max'),
				min: $(this).attr('data-min')
			})
		});
	}
	
	$( ".sub_menu_items" ).on("click", function( e ){
		
		e.preventDefault();
		var el = $(this).parent();
		if( el.hasClass('activ') )
		{
			el.removeClass('activ');
			$(this).removeClass('activ');
		}
		else
		{
			$( ".sub_menu_items" ).each(function(){
				$(this).removeClass('activ').parent().removeClass('activ');
			});
			
			el.addClass('activ');
			$(this).addClass('activ');
		}
		
	});
		
	if( $( '.test_mail' ).length > 0 )
	{
		$( '.test_mail' ).on("click", function( e ){
			e.preventDefault();
			editor.post();
			
			var subject = $(this).parents('form').find('input').first().val(),
				message = $(this).parents('form').find('textarea').val(),
				email = $(this).parents('form').find('input.test_mail_input').val(),
				footer = $(this).parents('form').find('[name="mailing[footer]"]').val();
			
			return testEmail( email, subject, message, footer );
		});
	}
	
	if( $(".ips-suggest").length > 0 )
	{
		$(".ips-suggest").fadeOut( 'slow', function(){
			$(".ips-suggest").fadeIn( 'slow')
		})
		$(".ips-suggest").on("click", function( e ){
			e.preventDefault();

			var page_info = $('.container-main > #content > .title_caption > .caption')
			
			if( page_info.length > 0 )
			{
				$( "#ips-suggest-box" ).find('input').val(page_info.html());
				
			}
			
			$( "#ips-suggest-box" ).find('textarea').val();
			
			$( "#ips-suggest-box" ).dialog({
				title: 'Masz sugestię dotyczącą skryptu ?',
				resizable: false,
				height:280,
				width: 500,
				modal: true,
				buttons: [
					{
						text: "Wyślij",
						click: function() {
							var thisDialog = $( this );
							var msg = $( "#ips-suggest-box" ).find('textarea').val();
							if( msg != '' )
							{
								var data_post = $( "#ips-suggest-box" ).find('form').formParams();
								
								$.post( adminAjaxFile, { ajax_actions: 'send_suggest', ajax_data: data_post } ,function( data ){
				
								}).done(function( data ) {
									
									$( "#ips-suggest-box" ).find('form').fadeOut( 'fast', function(){
										$( "#ips-suggest-box" ).html('<br/>Wiadomośc została wysłana<br/><br/>Na sugestie nie zostają udzielone odpowiedzi, jeśli funkcja/pomysł zostanie uwzględniony pojawi się w aktualizacji.');
									})
									
									setTimeout(function(){
										thisDialog.dialog( 'close' );
									}, 5000)
		
								})
								.fail(function() {
									alert( 'Wystąpił błąd' );
									thisDialog.dialog( 'close' );
								});
							}
							else
							{
								alert( 'Wpisz wiadomość' );
							}
						}
					},
					{
						text: ips_i18n.__( 'js_common_cancel' ),
						click: function() {
							$( this ).dialog( 'close' );
							return false;
						}
					}
				]
			});
		});
	}
	
	

	
	if( $("#change_hook").length > 0 )
	{
		$("#change_hook").on( 'change', function(){
			window.location.href = updateQueryStringParameter( window.location.href, 'force_hook',  $(this).val() );
		}); 
	}
	
	if( $(".widget-settings .widget-tab").length > 0 )
	{
		$(".widget-settings .widget-tab").each(function(){
			
			var active = $(this).hasClass('active');
			
			var buttons = [];
			
			if( active )
			{
				if( $(this).find('.widget-tab-sub-options').length > 0 )
				{
					buttons.push('<button class="button widget-tab-save" data-option="' + ( active ? 1 : 0 )+ '">Zapisz</button>');
				}
				buttons.push('<button class="button widget-tab-save" data-option="0">Wyłącz</button>');
			}
			else
			{
				buttons.push('<button class="button widget-tab-save activate-widget" data-option="1">Włącz</button>');
			}
			
			$(this).find('.widget-options').append('<div class="option-cnt">' + buttons.join( '' ) + '</div>');
		});
		
	
		
		$("body").on( 'click', '.widget-tab-save', function(e){
			
			e.preventDefault();
			
			showLoadWait();
			
			var widget_id = $(this).parents('.widget-tab').attr('id');
			
			var widget = $(this);
			
			var data_post = $('#' + widget_id + ' *').formParams();

			data_post[widget_id] = widget.attr('data-option');
			
			$.post( adminAjaxFile, { ajax_actions: 'save-settings', ajax_data: data_post } ,function( data ){
			
			}).done(function( data ) {
				if( data == 'true' )
				{
					if( typeof data_post[widget_id] != 'undefined' && !widget.hasClass('activate-widget') )
					{
						actionAdminMessage( 'Ustawienia zapisane' );
						
						setTimeout( function(){
							
							$('#spinner_loader').fadeOut( 'fast', function(){
								if( data_post[widget_id] == 0 )
								{
									widget.attr('data-option', false);
									$('#' + widget_id + ' .widget-caption').trigger('click');
								}
								$(this).html('');
							});
						
						}, 1000 );
					}
					else
					{
						actionAdminMessage( 'Akcja wykonana poprawnie. Za chwilę nastąpi przekierowanie.' );
						
						setTimeout(function(){
							
							$('#spinner_loader').fadeOut( 'fast', function(){
								window.location = '/admin/admin.php?route=options&action=widget#' + widget_id;
								//location.reload(true);
							});
						
						}, 1000);
					}
				}
				else
				{
					actionAdminError( data );
				}
			})
			.fail(function() {
				actionAdminError();
			});
		
		});
	}
	
	$(".change_input").click(function(e) {
		e.preventDefault();
		sprawdzPola( $(this).val(), $(this) );
	});
	
	if( $("#link:visible").length > 0 )
	{
		sprawdzPola( "link" );
		$("#link").on('change', function(){
			sprawdzPola( "link" );
		});
	}

	$('.content-opcje .option-cnt select[name="dialog"]').on( 'change', function() {
		var dial = $(this).val();
		loadjscssfile('../css/dialogs/'+dial+'/dialog.css');
		$('#dialog_wybor').attr('rel', dial);
	});
	
	$(function() {
		if( $("#admin-multiupload").length > 0 )
		{
			$("#admin-multiupload").uploadify({
				buttonClass   : 'button',
				buttonText	  : $("#admin-multiupload").attr('data-text'),
				height        : 27,
				swf           : '/libs/Uploadify/uploadify.swf',
				width         : 150,
				uploader      : '/ajax/file_upload_import/',
				progressData  : 'speed',
				onUploadSuccess : function(file, data, response) {
					$('input[name="folder"]').val('upload/import/import_folder/');
				},
				onUploadError : function(file, errorCode, errorMsg, errorString) {
					console.log(errorCode);
					console.log(errorMsg);
					console.log(errorString);
				} 
			});
		}
	});
	
	$('.translation.search').on('click', '.button', function( e ){
		
		var data_post = $('.translation.search').formParams();
		
		$.post( adminAjaxFile, { ajax_actions: 'language-search', ajax_data: data_post } ,function( data ){
			
			if( data.length > 0 )
			{
				$('.translation.main-form .tabbed_area').slideUp( 'fast', function(){
					$(this).html( data ).slideDown('fast');
				})
			}
			else
			{
				var border = $('.search-phrase').css( 'border-color' );
				$('.search-phrase').css( 'border-color', '#ff0000' );
				setTimeout(function(){
					$('.search-phrase').css( 'border-color', border );
				}, 3000);
			}
		})
		
		return false;
	});
	$('body').on('click', '#close_spinner', function( e ){
		hideLoadWait();
	});
	
	
	
	
	$('#users_delete, #clear_system,#update_disable').on('click', function( e ){
		e.preventDefault();
		link = $(this).attr('href');
		
		$( "#" + $(this).attr('id') + "_confirm" ).dialog({
			resizable: false,
			height:240,
			width: 500,
			modal: true,
			buttons: [
				{
					text: "Ok",
					click: function() {
						$( this ).dialog( 'close' );
						window.location.href = link;
					}
				},
				{
					text: ips_i18n.__( 'js_common_cancel' ),
					click: function() {
						$( this ).dialog( 'close' );
						return false;
					}
				}
			]
		});
		return false;
	});
	
	$('.widget-tab .widget-caption').click(function(){
		$(this).parent().toggleClass( 'active' );
	});
	
	if( $("select[name=contest_type]").length > 0 )
	{
		$("select[name=contest_type]").on( 'change', function(){
			if( jQuery.inArray( $(this).val(), [ "demotywator", "normalny" ] ) > -1  )
			{
				$("select[name=file_category]").parents('.option-cnt').hide();
				return;
			}
			$("select[name=file_category]").parents('.option-cnt').show();
		}); 
	}
	
	$(".tabs_roll .title_caption").click(function(){
		var slide = $(this).next('div');
		
		if( slide.hasClass('show') )
		{
			slide.slideUp().removeClass('show');
		}
		else
		{
			slide.slideDown().addClass('show');
		}
	});
	
	/** Edycja menu **/
	$(".item-edit").click(function(){
		var div = $(this).parent().find('div');
		
		if( div.hasClass('settings-show') == false )
		{
			
			div.slideDown().addClass('settings-show');
		}
		else
		{
			div.slideUp().removeClass('settings-show');
		}
		return false;
	});
	
	$(".reset-menu").click(function(){
		var menu_id = $(this).attr( "data-id" );
		
		$.post( adminAjaxFile, { ajax_action: 'reset-menu', menu_id: menu_id  } ,function(data){
			if( data == 'true' )
			{
				window.location.reload();
			}
			else
			{
				alert('Wystąpił błąd');
			}
		});
		return false;
	});
	
	
	$('.form-menu-edit').submit(function() {
		
		var id = $(this).attr('id');
		var menu_id = id.replace( "form-menu-edit-", "" );
		
		var ids = $( "#ips-menu-edit-" + menu_id ).sortable( 'toArray' );

		var menu_array = [];
		
		for (var i = 0; i < ids.length; i++ )
		{
			menu_array[i] = {};
			menu_array[i]['item_id'] = ids[i].replace( "menu-", "" );
			menu_array[i]['item_url'] = $("#" + id + ' [name="'+ids[i]+'-url"]').val();
			menu_array[i]['item_target'] = $("#" + id + ' [name="'+ids[i]+'-target"]').val();
			menu_array[i]['item_class'] = $("#" + id + ' [name="'+ids[i]+'-class"]').val();
			menu_array[i]['item_activ'] = $("#" + id + ' [name="'+ids[i]+'-activ"]').val();
			menu_array[i]['item_anchor'] = $("#" + id + ' [name="'+ids[i]+'-anchor"]').val();
			menu_array[i]['item_title'] = $("#" + id + ' [name="'+ids[i]+'-title"]').val();
		};
		
		$.post( adminAjaxFile, { ajax_action: 'save-menu', menu_id: menu_id, saved_menu: menu_array  } ,function(data){
			if( data == 'true' )
			{
				$('html, body').animate({
					 scrollTop: $('#menu-messages').offset().top
				 }, 2000);
				$('#menu-messages').html('Ustawienia menu zostały zapisane').fadeIn();
				setTimeout(function(){
					$('#menu-messages').html('').fadeOut();
				}, 4000 );
			}
			else
			{
				alert('Wystąpił błąd');
			}
		});
		return false;
	});
	/**
	* Nowa pozycja menu
	*/
	$('#form-menu-add').submit(function() {
		
		$.post( adminAjaxFile, { ajax_action: 'save-menu-item', saved_item: $(this).serialize()  } ,function(data){
			if( data == 'true' )
			{
				$('html, body').animate({
					 scrollTop: $('#menu-messages').offset().top
				 }, 2000);
				$('#menu-messages').html('Element menu został zapisany').fadeIn();
				setTimeout(function(){
					location.reload(true);
				}, 2000 );
			}
			else
			{
				alert('Wystąpił błąd');
			}
		});
		return false;
	});
	
	$(".item-edit-activ").click(function(){
		
		var id = $(this).parent().parent().attr('id');
		
		var sub_id = $(this).parent().parent().attr('id');
		
		if( $('#' + sub_id+ " #item-activ-" + id).val() == '0' )
		{
			$(this).html('<span style="color:red">Wyłącz</span>');
			$('#' + sub_id+ " #item-activ-" + id).val('1')
		}
		else
		{
			$(this).html('<span style="color:green">Włącz</span>');
			$('#' + sub_id+ " #item-activ-" + id).val('0')
		}
		return false;
	});
	/**
	* Delete menu item
	*/
	$(".item-edit-delete").click(function(){
		
		id = $(this).parent().parent().attr('id');
		
		menu_id = $(this).parent().parent().parent().attr('id').replace( "ips-menu-edit-", "" );
		
		$.post( adminAjaxFile, { ajax_action: 'delete-menu-item', menu_id: menu_id, menu_item: id.replace( "menu-", "" )  } ,function(data){
			if( data == 'true' )
			{
				$('html, body').animate({
					 scrollTop: $('#menu-messages').offset().top
				 }, 2000);
				$('#menu-messages').html('Element menu został usunięty').fadeIn();
				
				$("#"+id).animate({
					 height: 'toggle',
				}, 2000, function() {
					$("#"+id).remove();
					$('#menu-messages').html('').fadeOut();
				});
			}
			else
			{
				alert('Wystąpił błąd');
			}
		});
		return false;
	});
	
	/** Edycja menu KONIEC **/
	
	
	$(".widget-tab .option-cnt:first-child .label_radio input").each( function(){
		if( $(this).is(':checked') )
		{
			if( $(this).val() != 0 )
			{
				$(this).parents('.widget-tab').addClass('active').find('.widget-options').slideDown();
			};
		}
		return
	});
	
	

	$("a.tab_tabs").click(function () {
		var id = $(this).attr("title")
		menuSlide( id );
	});

});

function changeFanpageAlbums( timeout )
{
	
	if( timeout )
	{
		$('.album_id_container .option-inputs').html( '<img width="22" height="22" src="/images/svg/spinner.svg">' );
		
		return setTimeout(function(){
			return changeFanpageAlbums( false )
		}, 500);
	}
	
	var fanpage_id = [];

	$('.fanpage_id_change .search-choice a').each(function(){
		var option_key = $(this).attr('data-option-array-index');
		fanpage_id.push($('.fanpage_id_change select option').eq( option_key ).val());
	});
	
	$.post( adminAjaxFile, { ajax_action: 'albums', fanpage_id : fanpage_id }, function( data ){

		var response = jQuery.parseJSON( data );

		if( typeof response.error != 'undefined' )
		{
			$('select[name="fanpage_data[form]"]').hide();
			all_html = response.error;
		}
		else
		{
			$('select[name="fanpage_data[form]"]').show();

			var all_html = '';
			var input_name = typeof $('#fanpage_album_input').val() == 'undefined' ? 'album_id' : $('#fanpage_album_input').val();
			
			jQuery.each( response, function(i, albums) {
				var html = '';
				jQuery.each( albums, function(i, album) {
					html = html + '<option value="' + album.id + '">' + album.name + '</option>';
				});
				all_html = all_html + '<select name="' + input_name + '[' + i + ']">' +  html + '</select><br/><br/>';
			});

			var all_html = $( '<div>' + all_html + '</div>' );
			
			if( typeof album_ids_edit != 'undefined' && album_ids_edit )
			{
				jQuery.each( album_ids_edit, function( fanpage_id, album_id ){
					all_html.find( 'select[name="' + input_name + '[' + fanpage_id + ']"]' ).val( album_id );
				});
			}
			
		}
		
		$('.album_id_container .option-inputs').html( all_html );
		chosenWrap( $('.album_id_container .option-inputs').find('select') )
	});
}
$(document).ready(function() {
	
	$( "#slider-range" ).slider({
      range: true,
      min: 0,
      max: 500,
      values: [ 75, 300 ],
      slide: function( event, ui ) {
        $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
      }
    });
	
	$('.dotpay_change select').on( 'change', function(){
		var visible = $(".sms-codes:visible");
		var hidden = $(".sms-codes:hidden");
		
		
		visible.slideUp(); 
		hidden.slideDown();
		
	});
	
	
	$('.apps_fanpage_auto_check select').on( 'change', function(){
		
		var val = $(this).val();
		var div = $(this).parents('.option-cnt').next('.option-cnt');
		
		if( val == 'off' || val == 'orginal' )
		{
			
			if( !div.hasClass('display_none') )
			{
				div.addClass('display_none');
			}
			
		}
		else
		{
			if( div.hasClass('display_none') )
			{
				div.removeClass('display_none');
			}
		}
		if( $(this).parents('.option-cnt').hasClass('close_all') )
		{
			if( val == 'off' )
			{
				$('.apps_fanpage_auto_input,.apps_fanpage_auto_select').addClass('display_none_fp');
			}
			else
			{
				$('.apps_fanpage_auto_input,.apps_fanpage_auto_select').removeClass('display_none_fp');
			}
			
		}
			
	});
	
	if( $('.option-cnt.close_all').length > 0 )
	{
		$('.option-cnt.close_all select').trigger('change');
		$('.apps_fanpage_auto_select select option[value=user]:selected').parents('.option-cnt').next('.option-cnt').removeClass('display_none');
	}
	
	$('.fanpage_id_change select').chosen().on( 'change', function(e){
		changeFanpageAlbums( true );
	});
	
	if( typeof album_ids_edit != 'undefined' && album_ids_edit )
	{
		changeFanpageAlbums( true );
	}
	/*
	* Zmiana pól dla fanpage
	*/
	$('.fanpage_post_type select,#fanpage_post_type').on( 'change', function(){
		var val = $(this).val();
		
		if( val == 'upload' )
		{
			$('.fanpage_type_post').slideUp();
			$('.fanpage_type_upload').slideDown();
			changeFanpageAlbums( true );
		}
		else
		{
			$('select[name="fanpage_data[form]"]').show();
			sprawdzPola( "link" );
			$('.fanpage_type_post').slideDown();
			$('.fanpage_type_upload').slideUp();
		}
	});
	
	if( $(".watermark_inputs").length > 0 )
	{
		$(".watermark_inputs input").on( 'change', function(){
			var val = $(this).val(),
				checked = $(this).prop('checked');
			
			if( val == 0 ){
				$(".watermark_input").hide();
			}
			else{
				$(".watermark_input").show();
			}
		});
	}
	
	if( $(".tiny_editor textarea").length > 0 )
	{
		var id = 'tiny_editor' + Math.random().toString(36).substring(7);
		
		$(".tiny_editor textarea").attr("id", id);
		
		var editor = new TINY.editor.edit('editor', {
			id: id,
			width: '60%',
			height: 175,
			cssclass: 'tinyeditor',
			controlclass: 'tinyeditor-control',
			rowclass: 'tinyeditor-header',
			dividerclass: 'tinyeditor-divider',
			controls: ['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|',
				'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|', 'leftalign',
				'centeralign', 'rightalign', 'blockjustify', '|', 'unformat', '|', 'undo', 'redo', 'n',
				'font', 'size', 'style', '|', 'image', 'hr', 'link', 'unlink', '|', 'print'],
			footer: true,
			fonts: ['Verdana','Arial','Georgia','Trebuchet MS'],
			xhtml: true,
			bodyid: 'editor',
			footerclass: 'tinyeditor-footer',
			toggle: {text: 'source', activetext: 'wysiwyg', cssclass: 'toggle'},
			resize: {cssclass: 'resize'}
		});
		
		
		$(".tiny_editor textarea").parents('form').find(".tiny_editor_save").on("click", function(e){
			editor.post();
		});
	
	}
	
	
	
	
	
	
	
	
	
	/*
	* Aktualizacja wszystkich pól dla wybranej czcionki
	*/
	$('.ips-fonts-selectable').on( 'change', function() {
		
		var element = $(this).attr("id");
		var fontName = $(this).val();
		var group_name = element.substring( 0, element.lastIndexOf("-") );
		
		/**
		* Odznaczamy wszystkie zaznaczenia
		*/
		var selections = $('#' + group_name + ' .ips-fonts-additional .is-checked');
		selections.attr( 'checked', false );
		
		if( fontName != '' && fontName != 'disabled' )
		{			
			pokazAtrybutyCzcionki(fontName, group_name);
			
			$('#' + group_name + ' .ips-fonts-action').toggleClass("display_none");
			$('#' + group_name + ' .ips-fonts-action').html('Ukryj opcje');
			
			selectFirst(fontName, group_name);
		}
		/**
		* Opcja wyłączona
		*/
		else
		{
			ukryjAtrybutyCzcionki(fontName, group_name);
		}
	});
});	
	
	/**
	* Akcja dla przycisku Rozwiń opcje.
	*/
	function showAdvancedOptions( ref, group_name )
	{
		var fontName = $('#webfonts_' + group_name + '-select').val(),
		group_name = 'webfonts_' + group_name;
		
		if ( ref.hasClass( "display_none" ) )
		{
			ukryjAtrybutyCzcionki( group_name );
			ref.html('Rozwiń opcje');
		}
		else
		{
			pokazAtrybutyCzcionki( fontName, group_name );
			ref.html('Ukryj opcje');
		}
		ref.toggleClass("display_none");
	}
	/**
	* "Odkrywanie" aktywnych atrybutów wybranej czcionki.
	*/
	function pokazAtrybutyCzcionki( fontName, group_name )
	{
		
		var subsets = $('#' + group_name + ' .subsets-' + fontName + ' .is-checked');
		
		$('#' + group_name + ' .div-wariant:not(.font-variant-' + fontName + ')').hide();
		$('#' + group_name + ' .div-subsets:not(.subsets-' + fontName + ')').hide();
		

		if( subsets.length > 1 )
		{
			$('#' + group_name + ' .ips-fonts-coding').show(); 
		}
		else
		{
			$('#' + group_name + ' .ips-fonts-coding').hide(); 
		}
		$('#' + group_name + ' .ips-fonts-additional').show(600);
		$('#' + group_name + ' .font-variant-' + fontName + '.div-wariant').show(600); 
		$('#' + group_name + ' .subsets-' + fontName + '.div-subsets').show(600); 		
		
	}
	
	function ukryjAtrybutyCzcionki( group_name )
	{
		$('#' + group_name + ' .ips-fonts-additional').hide(600);
	}
	/**
	* Zaznaczanie pierwszego elementu z grupy
	*/
	function selectFirst( fontName, group_name )
	{
		var variants = $('#' + group_name + ' .font-variant-' + fontName + ' .is-checked');
		variants.first().attr('checked',true)
		
		var subsets = $('#' + group_name + ' .coding-' + fontName + ' .is-checked');
		subsets.first().attr( 'checked',true )
	}

function sprawdzPola ( val, element )
{
	var podglad_div = $(".podglad_url");	
	if( val == "link" )
	{
		$("#file, .change_input_file").hide();
		$("#link, .change_input_link").fadeIn();
		if( typeof element !== "undefined" )
		{
			var url = $(".change_input_link input").val();
		}
		else{
			var url = $("#link").val();
		}
		

			if ('' != url)
			{
				var podglad_image = $('<img alt="" src="' + url + '" />');
				podglad_image.attr(
					"src",
					url
				).load(function() {
					podglad_div.html(podglad_image);

				}).error(function() {
				});

			}
			else
			{
				podglad_div.html('&nbsp;');
			}
	}
	else
	{
		$("#link, .change_input_link").hide();
		$("#file, .change_input_file").fadeIn();
		podglad_div.html('&nbsp;');
		
	}
	$("#image_type").val( val );
}
function podglad_dialog()
{
	  $("#dialog").html('Tak wygląda treść wiadomosci');
	  $("#dialog").dialog({
		title: "Belka tytułowa",
		autoOpen: true,
		width: 600,
		resizable: false,
		buttons: {
		"Zamknij": function(){ 
				$(this).dialog("close");
			}
		}
	  });
};
//fgnass.github.com/spin.js#v1.3.3
!function(a,b){"object"==typeof exports?module.exports=b():"function"==typeof define&&define.amd?define(b):a.Spinner=b()}(this,function(){"use strict";function a(a,b){var c,d=document.createElement(a||"div");for(c in b)d[c]=b[c];return d}function b(a){for(var b=1,c=arguments.length;c>b;b++)a.appendChild(arguments[b]);return a}function c(a,b,c,d){var e=["opacity",b,~~(100*a),c,d].join("-"),f=.01+c/d*100,g=Math.max(1-(1-a)/b*(100-f),a),h=k.substring(0,k.indexOf("Animation")).toLowerCase(),i=h&&"-"+h+"-"||"";return m[e]||(n.insertRule("@"+i+"keyframes "+e+"{0%{opacity:"+g+"}"+f+"%{opacity:"+a+"}"+(f+.01)+"%{opacity:1}"+(f+b)%100+"%{opacity:"+a+"}100%{opacity:"+g+"}}",n.cssRules.length),m[e]=1),e}function d(a,b){var c,d,e=a.style;for(b=b.charAt(0).toUpperCase()+b.slice(1),d=0;d<l.length;d++)if(c=l[d]+b,void 0!==e[c])return c;return void 0!==e[b]?b:void 0}function e(a,b){for(var c in b)a.style[d(a,c)||c]=b[c];return a}function f(a){for(var b=1;b<arguments.length;b++){var c=arguments[b];for(var d in c)void 0===a[d]&&(a[d]=c[d])}return a}function g(a){for(var b={x:a.offsetLeft,y:a.offsetTop};a=a.offsetParent;)b.x+=a.offsetLeft,b.y+=a.offsetTop;return b}function h(a,b){return"string"==typeof a?a:a[b%a.length]}function i(a){return"undefined"==typeof this?new i(a):(this.opts=f(a||{},i.defaults,o),void 0)}function j(){function c(b,c){return a("<"+b+' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',c)}n.addRule(".spin-vml","behavior:url(#default#VML)"),i.prototype.lines=function(a,d){function f(){return e(c("group",{coordsize:k+" "+k,coordorigin:-j+" "+-j}),{width:k,height:k})}function g(a,g,i){b(m,b(e(f(),{rotation:360/d.lines*a+"deg",left:~~g}),b(e(c("roundrect",{arcsize:d.corners}),{width:j,height:d.width,left:d.radius,top:-d.width>>1,filter:i}),c("fill",{color:h(d.color,a),opacity:d.opacity}),c("stroke",{opacity:0}))))}var i,j=d.length+d.width,k=2*j,l=2*-(d.width+d.length)+"px",m=e(f(),{position:"absolute",top:l,left:l});if(d.shadow)for(i=1;i<=d.lines;i++)g(i,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(i=1;i<=d.lines;i++)g(i);return b(a,m)},i.prototype.opacity=function(a,b,c,d){var e=a.firstChild;d=d.shadow&&d.lines||0,e&&b+d<e.childNodes.length&&(e=e.childNodes[b+d],e=e&&e.firstChild,e=e&&e.firstChild,e&&(e.opacity=c))}}var k,l=["webkit","Moz","ms","O"],m={},n=function(){var c=a("style",{type:"text/css"});return b(document.getElementsByTagName("head")[0],c),c.sheet||c.styleSheet}(),o={lines:12,length:7,width:5,radius:10,rotate:0,corners:1,color:"#000",direction:1,speed:1,trail:100,opacity:.25,fps:20,zIndex:2e9,className:"spinner",top:"auto",left:"auto",position:"relative"};i.defaults={},f(i.prototype,{spin:function(b){this.stop();var c,d,f=this,h=f.opts,i=f.el=e(a(0,{className:h.className}),{position:h.position,width:0,zIndex:h.zIndex}),j=h.radius+h.length+h.width;if(b&&(b.insertBefore(i,b.firstChild||null),d=g(b),c=g(i),e(i,{left:("auto"==h.left?d.x-c.x+(b.offsetWidth>>1):parseInt(h.left,10)+j)+"px",top:("auto"==h.top?d.y-c.y+(b.offsetHeight>>1):parseInt(h.top,10)+j)+"px"})),i.setAttribute("role","progressbar"),f.lines(i,f.opts),!k){var l,m=0,n=(h.lines-1)*(1-h.direction)/2,o=h.fps,p=o/h.speed,q=(1-h.opacity)/(p*h.trail/100),r=p/h.lines;!function s(){m++;for(var a=0;a<h.lines;a++)l=Math.max(1-(m+(h.lines-a)*r)%p*q,h.opacity),f.opacity(i,a*h.direction+n,l,h);f.timeout=f.el&&setTimeout(s,~~(1e3/o))}()}return f},stop:function(){var a=this.el;return a&&(clearTimeout(this.timeout),a.parentNode&&a.parentNode.removeChild(a),this.el=void 0),this},lines:function(d,f){function g(b,c){return e(a(),{position:"absolute",width:f.length+f.width+"px",height:f.width+"px",background:b,boxShadow:c,transformOrigin:"left",transform:"rotate("+~~(360/f.lines*j+f.rotate)+"deg) translate("+f.radius+"px,0)",borderRadius:(f.corners*f.width>>1)+"px"})}for(var i,j=0,l=(f.lines-1)*(1-f.direction)/2;j<f.lines;j++)i=e(a(),{position:"absolute",top:1+~(f.width/2)+"px",transform:f.hwaccel?"translate3d(0,0,0)":"",opacity:f.opacity,animation:k&&c(f.opacity,f.trail,l+j*f.direction,f.lines)+" "+1/f.speed+"s linear infinite"}),f.shadow&&b(i,e(g("#000","0 0 4px #000"),{top:"2px"})),b(d,b(i,g(h(f.color,j),"0 0 1px rgba(0,0,0,.1)")));return d},opacity:function(a,b,c){b<a.childNodes.length&&(a.childNodes[b].style.opacity=c)}});var p=e(a("group"),{behavior:"url(#default#VML)"});return!d(p,"transform")&&p.adj?j():k=d(p,"animation"),i});
	


//jquery.dom.js



//jquery.dom.form_params.js

(function( $ ) {
	var radioCheck = /radio|checkbox/i,
		keyBreaker = /[^\[\]]+/g,
		numberMatcher = /^[\-+]?[0-9]*\.?[0-9]+([eE][\-+]?[0-9]+)?$/;

	var isNumber = function( value ) {
		if ( typeof value == 'number' ) {
			return true;
		}

		if ( typeof value != 'string' ) {
			return false;
		}

		return value.match(numberMatcher);
	};

	$.fn.extend({
		/**
		 * @parent dom
		 * @download http://jmvcsite.heroku.com/pluginify?plugins[]=jquery/dom/form_params/form_params.js
		 * @plugin jquery/dom/form_params
		 * @test jquery/dom/form_params/qunit.html
		 * 
		 * Returns an object of name-value pairs that represents values in a form.  
		 * It is able to nest values whose element's name has square brackets.
		 * 
		 * When convert is set to true strings that represent numbers and booleans will
		 * be converted and empty string will not be added to the object. 
		 * 
		 * Example html:
		 * @codestart html
		 * &lt;form>
		 *   &lt;input name="foo[bar]" value='2'/>
		 *   &lt;input name="foo[ced]" value='4'/>
		 * &lt;form/>
		 * @codeend
		 * Example code:
		 * 
		 *     $('form').formParams() //-> { foo:{bar:'2', ced: '4'} }
		 * 
		 * 
		 * @demo jquery/dom/form_params/form_params.html
		 * 
		 * @param {Object} [params] If an object is passed, the form will be repopulated
		 * with the values of the object based on the name of the inputs within
		 * the form
		 * @param {Boolean} [convert=false] True if strings that look like numbers 
		 * and booleans should be converted and if empty string should not be added 
		 * to the result. Defaults to false.
		 * @return {Object} An object of name-value pairs.
		 */
		formParams: function( params, convert ) {

			// Quick way to determine if something is a boolean
			if ( !! params === params ) {
				convert = params;
				params = null;
			}

			if ( params ) {
				return this.setParams( params );
			} else if ( this[0].nodeName.toLowerCase() == 'form' && this[0].elements ) {
				return jQuery(jQuery.makeArray(this[0].elements)).getParams(convert);
			}
			return jQuery("input[name], textarea[name], select[name]", this).getParams(convert);
		},
		setParams: function( params ) {

			// Find all the inputs
			this.find("[name]").each(function() {
				
				var value = params[ $(this).attr("name") ],
					$this;
				
				// Don't do all this work if there's no value
				if ( value !== undefined ) {
					$this = $(this);
					
					// Nested these if statements for performance
					if ( $this.is(":radio") ) {
						if ( $this.val() == value ) {
							$this.attr("checked", true);
						}
					} else if ( $this.is(":checkbox") ) {
						// Convert single value to an array to reduce
						// complexity
						value = $.isArray( value ) ? value : [value];
						if ( $.inArray( $this.val(), value ) > -1) {
							$this.attr("checked", true);
						}
					} else {
						$this.val( value );
					}
				}
			});
		},
		getParams: function( convert ) {
			var data = {},
				current;

			convert = convert === undefined ? false : convert;

			this.each(function() {
				var el = this,
					type = el.type && el.type.toLowerCase();
				//if we are submit, ignore
				if ((type == 'submit') || !el.name ) {
					return;
				}

				var key = el.name,
					value = $.data(el, "value") || $.fn.val.call([el]),
					isRadioCheck = radioCheck.test(el.type),
					parts = key.match(keyBreaker),
					write = !isRadioCheck || !! el.checked,
					//make an array of values
					lastPart;

				if ( convert ) {
					if ( isNumber(value) ) {
						value = parseFloat(value);
					} else if ( value === 'true') {
						value = true;
					} else if ( value === 'false' ) {
						value = false;
					}
					if(value === '') {
						value = undefined;
					}
				}

				// go through and create nested objects
				current = data;
				for ( var i = 0; i < parts.length - 1; i++ ) {
					if (!current[parts[i]] ) {
						current[parts[i]] = {};
					}
					current = current[parts[i]];
				}
				lastPart = parts[parts.length - 1];
				
				//now we are on the last part, set the value
				if (current[lastPart]) {
					if (!$.isArray(current[lastPart]) ) {
						//current[lastPart] = current[lastPart] === undefined ? [] : [current[lastPart]];
					}
					if ( write ) {
						
						current[lastPart] = value;
					}
				} else if ( write || !current[lastPart] ) {
					current[lastPart] = write ? value : undefined;
				}

			});
			return data;
		}
	});

})(jQuery);

/** designwithpc.com/plugins/ddslick **/
(function (a) { function g(a, b) { var c = a.data("ddslick"); var d = a.find(".dd-selected"), e = d.siblings(".dd-selected-value"), f = a.find(".dd-options"), g = d.siblings(".dd-pointer"), h = a.find(".dd-option").eq(b), k = h.closest("li"), l = c.settings, m = c.settings.data[b]; a.find(".dd-option").removeClass("dd-option-selected"); h.addClass("dd-option-selected"); c.selectedIndex = b; c.selectedItem = k; c.selectedData = m; if (l.showSelectedHTML) { d.html((m.imageSrc ? '<img class="dd-selected-image' + (l.imagePosition == "right" ? " dd-image-right" : "") + '" src="' + m.imageSrc + '" />' : "") + (m.text ? '<label class="dd-selected-text">' + m.text + "</label>" : "") + (m.description ? '<small class="dd-selected-description dd-desc' + (l.truncateDescription ? " dd-selected-description-truncated" : "") + '" >' + m.description + "</small>" : "")) } else d.html(m.text); e.val(m.value); c.original.val(m.value); a.data("ddslick", c); i(a); j(a); if (typeof l.onSelected == "function") { l.onSelected.call(this, c) } } function h(b) { var c = b.find(".dd-select"), d = c.siblings(".dd-options"), e = c.find(".dd-pointer"), f = d.is(":visible"); a(".dd-click-off-close").not(d).slideUp(50); a(".dd-pointer").removeClass("dd-pointer-up"); if (f) { d.slideUp("fast"); e.removeClass("dd-pointer-up") } else { d.slideDown("fast"); e.addClass("dd-pointer-up") } k(b) } function i(a) { a.find(".dd-options").slideUp(50); a.find(".dd-pointer").removeClass("dd-pointer-up").removeClass("dd-pointer-up") } function j(a) { var b = a.find(".dd-select").css("height"); var c = a.find(".dd-selected-description"); var d = a.find(".dd-selected-image"); if (c.length <= 0 && d.length > 0) { a.find(".dd-selected-text").css("lineHeight", b) } } function k(b) { b.find(".dd-option").each(function () { var c = a(this); var d = c.css("height"); var e = c.find(".dd-option-description"); var f = b.find(".dd-option-image"); if (e.length <= 0 && f.length > 0) { c.find(".dd-option-text").css("lineHeight", d) } }) } a.fn.ddslick = function (c) { if (b[c]) { return b[c].apply(this, Array.prototype.slice.call(arguments, 1)) } else if (typeof c === "object" || !c) { return b.init.apply(this, arguments) } else { a.error("Method " + c + " does not exists.") } }; var b = {}, c = { data: [], keepJSONItemsOnTop: false, width: 260, height: null, background: "#eee", selectText: "", defaultSelectedIndex: null, truncateDescription: true, imagePosition: "left", showSelectedHTML: true, clickOffToClose: true, onSelected: function () { } }, d = '<div class="dd-select"><input class="dd-selected-value" type="hidden" /><a class="dd-selected"></a><span class="dd-pointer dd-pointer-down"></span></div>', e = '<ul class="dd-options"></ul>', f = '<style id="css-ddslick" type="text/css">' + ".dd-select{ border-radius:2px; border:solid 1px #ccc; position:relative; cursor:pointer;}" + ".dd-desc { color:#aaa; display:block; overflow: hidden; font-weight:normal; line-height: 1.4em; }" + ".dd-selected{ overflow:hidden; display:block; padding:10px; font-weight:bold;}" + ".dd-pointer{ width:0; height:0; position:absolute; right:10px; top:50%; margin-top:-3px;}" + ".dd-pointer-down{ border:solid 5px transparent; border-top:solid 5px #000; }" + ".dd-pointer-up{border:solid 5px transparent !important; border-bottom:solid 5px #000 !important; margin-top:-8px;}" + ".dd-options{ border:solid 1px #ccc; border-top:none; list-style:none; box-shadow:0px 1px 5px #ddd; display:none; position:absolute; z-index:2000; margin:0; padding:0;background:#fff; overflow:auto;}" + ".dd-option{ padding:10px; display:block; border-bottom:solid 1px #ddd; overflow:hidden; text-decoration:none; color:#333; cursor:pointer;-webkit-transition: all 0.25s ease-in-out; -moz-transition: all 0.25s ease-in-out;-o-transition: all 0.25s ease-in-out;-ms-transition: all 0.25s ease-in-out; }" + ".dd-options > li:last-child > .dd-option{ border-bottom:none;}" + ".dd-option:hover{ background:#f3f3f3; color:#000;}" + ".dd-selected-description-truncated { text-overflow: ellipsis; white-space:nowrap; }" + ".dd-option-selected { background:#f6f6f6; }" + ".dd-option-image, .dd-selected-image { vertical-align:middle; float:left; margin-right:5px; max-width:64px;}" + ".dd-image-right { float:right; margin-right:15px; margin-left:5px;}" + ".dd-container{ position:relative;}​ .dd-selected-text { font-weight:bold}​</style>"; if (a("#css-ddslick").length <= 0) { a(f).appendTo("head") } b.init = function (b) { var b = a.extend({}, c, b); return this.each(function () { var c = a(this), f = c.data("ddslick"); if (!f) { var i = [], j = b.data; c.find("option").each(function () { var b = a(this), c = b.data(); i.push({ text: a.trim(b.text()), value: b.val(), selected: b.is(":selected"), description: c.description, imageSrc: c.imagesrc }) }); if (b.keepJSONItemsOnTop) a.merge(b.data, i); else b.data = a.merge(i, b.data); var k = c, l = a('<div id="' + c.attr("id") + '"></div>'); c.replaceWith(l); c = l; c.addClass("dd-container").append(d).append(e); var i = c.find(".dd-select"), m = c.find(".dd-options"); m.css({ width: b.width }); i.css({ width: b.width, background: b.background }); c.css({ width: b.width }); if (b.height != null) m.css({ height: b.height, overflow: "auto" }); a.each(b.data, function (a, c) { if (c.selected) b.defaultSelectedIndex = a; m.append("<li>" + '<a class="dd-option">' + (c.value ? ' <input class="dd-option-value" type="hidden" value="' + c.value + '" />' : "") + (c.imageSrc ? ' <img class="dd-option-image' + (b.imagePosition == "right" ? " dd-image-right" : "") + '" src="' + c.imageSrc + '" />' : "") + (c.text ? ' <label class="dd-option-text">' + c.text + "</label>" : "") + (c.description ? ' <small class="dd-option-description dd-desc">' + c.description + "</small>" : "") + "</a>" + "</li>") }); var n = { settings: b, original: k, selectedIndex: -1, selectedItem: null, selectedData: null }; c.data("ddslick", n); if (b.selectText.length > 0 && b.defaultSelectedIndex == null) { c.find(".dd-selected").html(b.selectText) } else { var o = b.defaultSelectedIndex != null && b.defaultSelectedIndex >= 0 && b.defaultSelectedIndex < b.data.length ? b.defaultSelectedIndex : 0; g(c, o) } c.find(".dd-select").on("click.ddslick", function () { h(c) }); c.find(".dd-option").on("click.ddslick", function () { g(c, a(this).closest("li").index()) }); if (b.clickOffToClose) { m.addClass("dd-click-off-close"); c.on("click.ddslick", function (a) { a.stopPropagation() }); a("body").on("click", function () { a(".dd-click-off-close").slideUp(50).siblings(".dd-select").find(".dd-pointer").removeClass("dd-pointer-up") }) } } }) }; b.select = function (b) { return this.each(function () { if (b.index) g(a(this), b.index) }) }; b.open = function () { return this.each(function () { var b = a(this), c = b.data("ddslick"); if (c) h(b) }) }; b.close = function () { return this.each(function () { var b = a(this), c = b.data("ddslick"); if (c) i(b) }) }; b.destroy = function () { return this.each(function () { var b = a(this), c = b.data("ddslick"); if (c) { var d = c.original; b.removeData("ddslick").unbind(".ddslick").replaceWith(d) } }) } })(jQuery);
	
/**
 * Featherlight - ultra slim jQuery lightbox
 * Version 1.3.4 - http://noelboss.github.io/featherlight/
 *
 * Copyright 2015, NoĂ«l Raoul Bossart (http://www.noelboss.com)
 * MIT Licensed.
**/
(function($) {
	"use strict";

	if('undefined' === typeof $) {
		if('console' in window){ window.console.info('Too much lightness, Featherlight needs jQuery.'); }
		return;
	}

	/* Featherlight is exported as $.featherlight.
	   It is a function used to open a featherlight lightbox.

	   [tech]
	   Featherlight uses prototype inheritance.
	   Each opened lightbox will have a corresponding object.
	   That object may have some attributes that override the
	   prototype's.
	   Extensions created with Featherlight.extend will have their
	   own prototype that inherits from Featherlight's prototype,
	   thus attributes can be overriden either at the object level,
	   or at the extension level.
	   To create callbacks that chain themselves instead of overriding,
	   use chainCallbacks.
	   For those familiar with CoffeeScript, this correspond to
	   Featherlight being a class and the Gallery being a class
	   extending Featherlight.
	   The chainCallbacks is used since we don't have access to
	   CoffeeScript's `super`.
	*/

	function Featherlight($content, config) {
		if(this instanceof Featherlight) {  /* called with new */
			this.id = Featherlight.id++;
			this.setup($content, config);
			this.chainCallbacks(Featherlight._callbackChain);
		} else {
			var fl = new Featherlight($content, config);
			fl.open();
			return fl;
		}
	}

	var opened = [],
		pruneOpened = function(remove) {
			opened = $.grep(opened, function(fl) {
				return fl !== remove && fl.$instance.closest('body').length > 0;
			} );
			return opened;
		};

	// structure({iframeMinHeight: 44, foo: 0}, 'iframe')
	//   #=> {min-height: 44}
	var structure = function(obj, prefix) {
		var result = {},
			regex = new RegExp('^' + prefix + '([A-Z])(.*)');
		for (var key in obj) {
			var match = key.match(regex);
			if (match) {
				var dasherized = (match[1] + match[2].replace(/([A-Z])/g, '-$1')).toLowerCase();
				result[dasherized] = obj[key];
			}
		}
		return result;
	};

	/* document wide key handler */
	var eventMap = { keyup: 'onKeyUp', resize: 'onResize' };

	var globalEventHandler = function(event) {
		$.each(Featherlight.opened().reverse(), function() {
			if (!event.isDefaultPrevented()) {
				if (false === this[eventMap[event.type]](event)) {
					event.preventDefault(); event.stopPropagation(); return false;
			  }
			}
		});
	};

	var toggleGlobalEvents = function(set) {
			if(set !== Featherlight._globalHandlerInstalled) {
				Featherlight._globalHandlerInstalled = set;
				var events = $.map(eventMap, function(_, name) { return name+'.'+Featherlight.prototype.namespace; } ).join(' ');
				$(window)[set ? 'on' : 'off'](events, globalEventHandler);
			}
		};

	Featherlight.prototype = {
		constructor: Featherlight,
		/*** defaults ***/
		/* extend featherlight with defaults and methods */
		namespace:    'featherlight',         /* Name of the events and css class prefix */
		targetAttr:   'data-featherlight',    /* Attribute of the triggered element that contains the selector to the lightbox content */
		variant:      null,                   /* Class that will be added to change look of the lightbox */
		resetCss:     false,                  /* Reset all css */
		background:   null,                   /* Custom DOM for the background, wrapper and the closebutton */
		openTrigger:  'click',                /* Event that triggers the lightbox */
		closeTrigger: 'click',                /* Event that triggers the closing of the lightbox */
		filter:       null,                   /* Selector to filter events. Think $(...).on('click', filter, eventHandler) */
		root:         'body',                 /* Where to append featherlights */
		openSpeed:    250,                    /* Duration of opening animation */
		closeSpeed:   250,                    /* Duration of closing animation */
		closeOnClick: 'background',           /* Close lightbox on click ('background', 'anywhere' or false) */
		closeOnEsc:   true,                   /* Close lightbox when pressing esc */
		closeIcon:    '&#10005;',             /* Close icon */
		loading:      '',                     /* Content to show while initial content is loading */
		persist:      false,									/* If set, the content persist and will be shown again when opened again. 'shared' is a special value when binding multiple elements for them to share the same content */
		otherClose:   null,                   /* Selector for alternate close buttons (e.g. "a.close") */
		beforeOpen:   $.noop,                 /* Called before open. can return false to prevent opening of lightbox. Gets event as parameter, this contains all data */
		beforeContent: $.noop,                /* Called when content is loaded. Gets event as parameter, this contains all data */
		beforeClose:  $.noop,                 /* Called before close. can return false to prevent opening of lightbox. Gets event as parameter, this contains all data */
		afterOpen:    $.noop,                 /* Called after open. Gets event as parameter, this contains all data */
		afterContent: $.noop,                 /* Called after content is ready and has been set. Gets event as parameter, this contains all data */
		afterClose:   $.noop,                 /* Called after close. Gets event as parameter, this contains all data */
		onKeyUp:      $.noop,                 /* Called on key down for the frontmost featherlight */
		onResize:     $.noop,                 /* Called after new content and when a window is resized */
		type:         null,                   /* Specify type of lightbox. If unset, it will check for the targetAttrs value. */
		contentFilters: ['jquery', 'image', 'html', 'ajax', 'iframe', 'text'], /* List of content filters to use to determine the content */

		/*** methods ***/
		/* setup iterates over a single instance of featherlight and prepares the background and binds the events */
		setup: function(target, config){
			/* all arguments are optional */
			if (typeof target === 'object' && target instanceof $ === false && !config) {
				config = target;
				target = undefined;
			}

			var self = $.extend(this, config, {target: target}),
				css = !self.resetCss ? self.namespace : self.namespace+'-reset', /* by adding -reset to the classname, we reset all the default css */
				$background = $(self.background || [
					'<div class="'+css+'-loading '+css+'">',
						'<div class="'+css+'-content">',
							'<span class="'+css+'-close-icon '+ self.namespace + '-close">',
								self.closeIcon,
							'</span>',
							'<div class="'+self.namespace+'-inner">' + self.loading + '</div>',
						'</div>',
					'</div>'].join('')),
				closeButtonSelector = '.'+self.namespace+'-close' + (self.otherClose ? ',' + self.otherClose : '');

			self.$instance = $background.clone().addClass(self.variant); /* clone DOM for the background, wrapper and the close button */

			/* close when click on background/anywhere/null or closebox */
			self.$instance.on(self.closeTrigger+'.'+self.namespace, function(event) {
				var $target = $(event.target);
				if( ('background' === self.closeOnClick  && $target.is('.'+self.namespace))
					|| 'anywhere' === self.closeOnClick
					|| $target.closest(closeButtonSelector).length ){
					event.preventDefault();
					self.close();
				}
			});

			return this;
		},

		/* this method prepares the content and converts it into a jQuery object or a promise */
		getContent: function(){
			if(this.persist !== false && this.$content) {
				return this.$content;
			}
			var self = this,
				filters = this.constructor.contentFilters,
				readTargetAttr = function(name){ return self.$currentTarget && self.$currentTarget.attr(name); },
				targetValue = readTargetAttr(self.targetAttr),
				data = self.target || targetValue || '';

			/* Find which filter applies */
			var filter = filters[self.type]; /* check explicit type like {type: 'image'} */

			/* check explicit type like data-featherlight="image" */
			if(!filter && data in filters) {
				filter = filters[data];
				data = self.target && targetValue;
			}
			data = data || readTargetAttr('href') || '';

			/* check explicity type & content like {image: 'photo.jpg'} */
			if(!filter) {
				for(var filterName in filters) {
					if(self[filterName]) {
						filter = filters[filterName];
						data = self[filterName];
					}
				}
			}

			/* otherwise it's implicit, run checks */
			if(!filter) {
				var target = data;
				data = null;
				$.each(self.contentFilters, function() {
					filter = filters[this];
					if(filter.test)  {
						data = filter.test(target);
					}
					if(!data && filter.regex && target.match && target.match(filter.regex)) {
						data = target;
					}
					return !data;
				});
				if(!data) {
					if('console' in window){ window.console.error('Featherlight: no content filter found ' + (target ? ' for "' + target + '"' : ' (no target specified)')); }
					return false;
				}
			}
			/* Process it */
			return filter.process.call(self, data);
		},

		/* sets the content of $instance to $content */
		setContent: function($content){
			var self = this;
			/* we need a special class for the iframe */
			if($content.is('iframe') || $('iframe', $content).length > 0){
				self.$instance.addClass(self.namespace+'-iframe');
			}

			self.$instance.removeClass(self.namespace+'-loading');

			/* replace content by appending to existing one before it is removed
			   this insures that featherlight-inner remain at the same relative
				 position to any other items added to featherlight-content */
			self.$instance.find('.'+self.namespace+'-inner')
				.not($content)                /* excluded new content, important if persisted */
				.slice(1).remove().end()			/* In the unexpected event where there are many inner elements, remove all but the first one */
				.replaceWith($.contains(self.$instance[0], $content[0]) ? '' : $content);

			self.$content = $content.addClass(self.namespace+'-inner');

			return self;
		},

		/* opens the lightbox. "this" contains $instance with the lightbox, and with the config.
			Returns a promise that is resolved after is successfully opened. */
		open: function(event){
			var self = this;
			self.$instance.hide().appendTo(self.root);
			if((!event || !event.isDefaultPrevented())
				&& self.beforeOpen(event) !== false) {

				if(event){
					event.preventDefault();
				}
				var $content = self.getContent();

				if($content) {
					opened.push(self);

					toggleGlobalEvents(true);

					self.$instance.fadeIn(self.openSpeed);
					self.beforeContent(event);

					/* Set content and show */
					return $.when($content)
						.always(function($content){
							self.setContent($content);
							self.afterContent(event);
						})
						.then(self.$instance.promise())
						/* Call afterOpen after fadeIn is done */
						.done(function(){ self.afterOpen(event); });
				}
			}
			self.$instance.detach();
			return $.Deferred().reject().promise();
		},

		/* closes the lightbox. "this" contains $instance with the lightbox, and with the config
			returns a promise, resolved after the lightbox is successfully closed. */
		close: function(event){
			var self = this,
				deferred = $.Deferred();

			if(self.beforeClose(event) === false) {
				deferred.reject();
			} else {

				if (0 === pruneOpened(self).length) {
					toggleGlobalEvents(false);
				}

				self.$instance.fadeOut(self.closeSpeed,function(){
					self.$instance.detach();
					self.afterClose(event);
					deferred.resolve();
				});
			}
			return deferred.promise();
		},

		/* Utility function to chain callbacks
		   [Warning: guru-level]
		   Used be extensions that want to let users specify callbacks but
		   also need themselves to use the callbacks.
		   The argument 'chain' has callback names as keys and function(super, event)
		   as values. That function is meant to call `super` at some point.
		*/
		chainCallbacks: function(chain) {
			for (var name in chain) {
				this[name] = $.proxy(chain[name], this, $.proxy(this[name], this));
			}
		}
	};

	$.extend(Featherlight, {
		id: 0,                                    /* Used to id single featherlight instances */
		autoBind:       '[data-featherlight]',    /* Will automatically bind elements matching this selector. Clear or set before onReady */
		defaults:       Featherlight.prototype,   /* You can access and override all defaults using $.featherlight.defaults, which is just a synonym for $.featherlight.prototype */
		/* Contains the logic to determine content */
		contentFilters: {
			jquery: {
				regex: /^[#.]\w/,         /* Anything that starts with a class name or identifiers */
				test: function(elem)    { return elem instanceof $ && elem; },
				process: function(elem) { return this.persist !== false ? $(elem) : $(elem).clone(true); }
			},
			image: {
				regex: /\.(png|jpg|jpeg|gif|tiff|bmp|svg)(\?\S*)?$/i,
				process: function(url)  {
					var self = this,
						deferred = $.Deferred(),
						img = new Image(),
						$img = $('<img src="'+url+'" alt="" class="'+self.namespace+'-image" />');
					img.onload  = function() {
						/* Store naturalWidth & height for IE8 */
						$img.naturalWidth = img.width; $img.naturalHeight = img.height;
						deferred.resolve( $img );
					};
					img.onerror = function() { deferred.reject($img); };
					img.src = url;
					return deferred.promise();
				}
			},
			html: {
				regex: /^\s*<[\w!][^<]*>/, /* Anything that starts with some kind of valid tag */
				process: function(html) { return $(html); }
			},
			ajax: {
				regex: /./,            /* At this point, any content is assumed to be an URL */
				process: function(url)  {
					var self = this,
						deferred = $.Deferred();
					/* we are using load so one can specify a target with: url.html #targetelement */
					var $container = $('<div></div>').load(url, function(response, status){
						if ( status !== "error" ) {
							deferred.resolve($container.contents());
						}
						deferred.fail();
					});
					return deferred.promise();
				}
			},
			iframe: {
				process: function(url) {
					var deferred = new $.Deferred();
					var $content = $('<iframe/>')
						.hide()
						.attr('src', url)
						.css(structure(this, 'iframe'))
						.on('load', function() { deferred.resolve($content.show()); })
						// We can't move an <iframe> and avoid reloading it,
						// so let's put it in place ourselves right now:
						.appendTo(this.$instance.find('.' + this.namespace + '-content'));
					return deferred.promise();
				}
			},
			text: {
				process: function(text) { return $('<div>', {text: text}); }
			}
		},

		functionAttributes: ['beforeOpen', 'afterOpen', 'beforeContent', 'afterContent', 'beforeClose', 'afterClose'],

		/*** class methods ***/
		/* read element's attributes starting with data-featherlight- */
		readElementConfig: function(element, namespace) {
			var Klass = this,
				regexp = new RegExp('^data-' + namespace + '-(.*)'),
				config = {};
			if (element && element.attributes) {
				$.each(element.attributes, function(){
					var match = this.name.match(regexp);
					if (match) {
						var val = this.value,
							name = $.camelCase(match[1]);
						if ($.inArray(name, Klass.functionAttributes) >= 0) {  /* jshint -W054 */
							val = new Function(val);                           /* jshint +W054 */
						} else {
							try { val = $.parseJSON(val); }
							catch(e) {}
						}
						config[name] = val;
					}
				});
			}
			return config;
		},

		/* Used to create a Featherlight extension
		   [Warning: guru-level]
		   Creates the extension's prototype that in turn
		   inherits Featherlight's prototype.
		   Could be used to extend an extension too...
		   This is pretty high level wizardy, it comes pretty much straight
		   from CoffeeScript and won't teach you anything about Featherlight
		   as it's not really specific to this library.
		   My suggestion: move along and keep your sanity.
		*/
		extend: function(child, defaults) {
			/* Setup class hierarchy, adapted from CoffeeScript */
			var Ctor = function(){ this.constructor = child; };
			Ctor.prototype = this.prototype;
			child.prototype = new Ctor();
			child.__super__ = this.prototype;
			/* Copy class methods & attributes */
			$.extend(child, this, defaults);
			child.defaults = child.prototype;
			return child;
		},

		attach: function($source, $content, config) {
			var Klass = this;
			if (typeof $content === 'object' && $content instanceof $ === false && !config) {
				config = $content;
				$content = undefined;
			}
			/* make a copy */
			config = $.extend({}, config);

			/* Only for openTrigger and namespace... */
			var namespace = config.namespace || Klass.defaults.namespace,
				tempConfig = $.extend({}, Klass.defaults, Klass.readElementConfig($source[0], namespace), config),
				sharedPersist;

			$source.on(tempConfig.openTrigger+'.'+tempConfig.namespace, tempConfig.filter, function(event) {
				/* ... since we might as well compute the config on the actual target */
				var elemConfig = $.extend(
					{$source: $source, $currentTarget: $(this)},
					Klass.readElementConfig($source[0], tempConfig.namespace),
					Klass.readElementConfig(this, tempConfig.namespace),
					config);
				var fl = sharedPersist || $(this).data('featherlight-persisted') || new Klass($content, elemConfig);
				if(fl.persist === 'shared') {
					sharedPersist = fl;
				} else if(fl.persist !== false) {
					$(this).data('featherlight-persisted', fl);
				}
				elemConfig.$currentTarget.blur(); // Otherwise 'enter' key might trigger the dialog again
				fl.open(event);
			});
			return $source;
		},

		current: function() {
			var all = this.opened();
			return all[all.length - 1] || null;
		},

		opened: function() {
			var klass = this;
			pruneOpened();
			return $.grep(opened, function(fl) { return fl instanceof klass; } );
		},

		close: function() {
			var cur = this.current();
			if(cur) { return cur.close(); }
		},

		/* Does the auto binding on startup.
		   Meant only to be used by Featherlight and its extensions
		*/
		_onReady: function() {
			var Klass = this;
			if(Klass.autoBind){
				/* Bind existing elements */
				$(Klass.autoBind).each(function(){
					Klass.attach($(this));
				});
				/* If a click propagates to the document level, then we have an item that was added later on */
				$(document).on('click', Klass.autoBind, function(evt) {
					if (evt.isDefaultPrevented()) {
						return;
					}
					evt.preventDefault();
					/* Bind featherlight */
					Klass.attach($(evt.currentTarget));
					/* Click again; this time our binding will catch it */
					$(evt.target).click();
				});
			}
		},

		/* Featherlight uses the onKeyUp callback to intercept the escape key.
		   Private to Featherlight.
		*/
		_callbackChain: {
			onKeyUp: function(_super, event){
				if(27 === event.keyCode) {
					if (this.closeOnEsc) {
						this.$instance.find('.'+this.namespace+'-close:first').click();
					}
					return false;
				} else {
					return _super(event);
				}
			},

			onResize: function(_super, event){
				if (this.$content.naturalWidth) {
					var w = this.$content.naturalWidth, h = this.$content.naturalHeight;
					/* Reset apparent image size first so container grows */
					this.$content.css('width', '').css('height', '');
					/* Calculate the worst ratio so that dimensions fit */
					var ratio = Math.max(
						w  / parseInt(this.$content.parent().css('width'),10),
						h / parseInt(this.$content.parent().css('height'),10));
					/* Resize content */
					if (ratio > 1) {
						this.$content.css('width', '' + w / ratio + 'px').css('height', '' + h / ratio + 'px');
					}
				}
				return _super(event);
			},

			afterContent: function(_super, event){
				var r = _super(event);
				this.onResize(event);
				return r;
			}
		}
	});

	$.featherlight = Featherlight;

	/* bind jQuery elements to trigger featherlight */
	$.fn.featherlight = function($content, config) {
		return Featherlight.attach(this, $content, config);
	};

	/* bind featherlight on ready if config autoBind is set */
	$(document).ready(function(){ Featherlight._onReady(); });
}(jQuery));
