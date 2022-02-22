/*
 * jQuery.upload v1.0.2
 *
 * Copyright (c) 2010 lagos
 * Dual licensed under the MIT and GPL licenses.
 *
 * http://lagoscript.org
 */
(function($) {

	var uuid = 0;

	$.fn.upload = function(url, data, callback, type) {
		var self = this, inputs, checkbox, checked,
			iframeName = 'jquery_upload' + ++uuid,
			iframe = $('<iframe name="' + iframeName + '" style="position:absolute;top:-9999px" />').appendTo('body'),
			form = '<form target="' + iframeName + '" method="post" enctype="multipart/form-data" />';

		if ($.isFunction(data)) {
			type = callback;
			callback = data;
			data = {};
		}

		checkbox = $('input:checkbox', this);
		checked = $('input:checked', this);
		form = self.wrapAll(form).parent('form').attr('action', url);

		// Make sure radios and checkboxes keep original values
		// (IE resets checkd attributes when appending)
		checkbox.removeAttr('checked');
		checked.attr('checked', true);

		inputs = createInputs(data);
		inputs = inputs ? $(inputs).appendTo(form) : null;

		form.submit(function() {
			iframe.load(function() {
				var data = handleData(this, type),
					checked = $('input:checked', self);

				form.after(self).remove();
				checkbox.removeAttr('checked');
				checked.attr('checked', true);
				if (inputs) {
					inputs.remove();
				}

				setTimeout(function() {
					iframe.remove();
					if (type === 'script') {
						$.globalEval(data);
					}
					if (callback) {
						callback.call(self, data);
					}
				}, 0);
			});
		}).submit();

		return this;
	};

	function createInputs(data) {
		return $.map(param(data), function(param) {
			return '<input type="hidden" name="' + param.name + '" value="' + param.value + '"/>';
		}).join('');
	}

	function param(data) {
		if ($.isArray(data)) {
			return data;
		}
		var params = [];

		function add(name, value) {
			params.push({name:name, value:value});
		}

		if (typeof data === 'object') {
			$.each(data, function(name) {
				if ($.isArray(this)) {
					$.each(this, function() {
						add(name, this);
					});
				} else {
					add(name, $.isFunction(this) ? this() : this);
				}
			});
		} else if (typeof data === 'string') {
			$.each(data.split('&'), function() {
				var param = $.map(this.split('='), function(v) {
					return decodeURIComponent(v.replace(/\+/g, ' '));
				});

				add(param[0], param[1]);
			});
		}

		return params;
	}

	function handleData(iframe, type) {
		var data, contents = $(iframe).contents().get(0);

		if ($.isXMLDoc(contents) || contents.XMLDocument) {
			return contents.XMLDocument || contents;
		}
		data = $(contents).find('body').html();

		switch (type) {
			case 'xml':
				data = parseXml(data);
				break;
			case 'json':
				data = window.eval('(' + data + ')');
				break;
		}
		return data;
	}

	function parseXml(text) {
		if (window.DOMParser) {
			return new DOMParser().parseFromString(text, 'application/xml');
		} else {
			var xml = new ActiveXObject('Microsoft.XMLDOM');
			xml.async = false;
			xml.loadXML(text);
			return xml;
		}
	}

})(jQuery);
	
$.fn.watchUser = function()
{
	var user_id = $(this).attr('data-id');
	var btn_watch = $(this);
	IpsApp._ajax( '/ajax/watch_user/' + user_id, { watch_action: ( btn_watch.hasClass( 'delete' ) ? 'delete' : 'add' ) }, 'POST', 'json', false, function( response )
	{ 
		if( typeof response.error !== 'undefined' )
		{
			showDialog( false, response.error, true);
		}
		else if( typeof response.success !== 'undefined' )
		{
			if( btn_watch.next().hasClass( 'watch-response' ) )
			{
				btn_watch.next().remove();
			}
			
			if( btn_watch.parent().hasClass( 'watch-response' ) )
			{
				btn_watch.parent().html( response.success );
			}
			else
			{
				btn_watch.after( response.success );
			}
		}
	});
}
$(document).ready(function() {
	
	/** Watch user only on profile */
	$('body').on( ips_click, '.ips-watch-user', function () {
		if ( ips_user.is_logged == false )
		{
			return userAuth('login');
		}
		$(this).watchUser();
	})
	
	var facebook_prev = $( '.edit_profile' ).find('.facebook_login_btn').find('.i-check');
	
	if( facebook_prev.length > 0 )
	{
		
		facebook_prev.on( 'ifToggled', function(e){
			var input = $(this).find('input:checked');
			
			if( input.length == 0  )
			{
				return false;
			}
			
			if( input.val() == 1 )
			{
				api_token( function(){
					input.icheck('toggle');
				}, 'email,user_birthday,publish_actions' );
			}
			else
			{
				FB.api({
					method: 'auth.revokeExtendedPermission',
					perm: 'publish_actions'
				},function(response) {
					return IpsApp._ajax( '/ajax/api_facebook/' , {
						ui_action: 'delete_connect', 
						connect_type : ( typeof input.attr('name') !== 'undefined' && input.attr('name') == 'user_data[post_facebook]' ? 'permission' : 'account' ) 
					}, 'POST' );
				}); 
			}
		});
	}
	
	
	head.load([
		"/libs/jQuery-File-Upload/js/vendor/jquery.ui.widget.js",
		"/libs/jQuery-File-Upload/ips/canvas-to-blob.min.js",
		"/libs/jQuery-File-Upload/ips/load-image.min.js",
		"/libs/jQuery-File-Upload/js/jquery.iframe-transport.js",
		"/libs/jQuery-File-Upload/js/jquery.fileupload.js",
		"/libs/jQuery-File-Upload/js/jquery.fileupload-process.js",
		"/libs/jQuery-File-Upload/js/jquery.fileupload-image.js"
	], function(){
		var cnt = $('.avatar_thumb'),
			avatar_thumb = cnt.find('img').attr( 'src' );
			
		cnt.find('input').fileupload({
			url: '/ajax/avatar_upload/',
			autoUpload: true,
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
			dataType: 'json'
		}).on('fileuploaddone', function (e, data) {
			
			if( data.result.error )
			{
				if( $('.user-profile-image').length > 0 )
				{
					$('body').modalAlert( data.result.error );
				}
				else
				{
					alert( data.result.error );
				}
				data.result.succes = avatar_thumb;
			}
			
			cnt.find('img').attr('src', data.result.succes );
			cnt.find('input').val('');
			
		}).on('fileuploadfail', function (e, data) {
			alert( ips_i18n.__( 'js_alert_jquery' ) );
		}).on('fileuploadadd', function (e, data) {
			cnt.find('img').attr('src', '/images/svg/spinner-grey.svg' );
		})
	});
	
	
	
	
	
	
	/* 
	
	$('#imageUpload').change(function(){
		
		
		
		
		
		
		
		var avatar_thumb = $('#avatar_thumb').attr( 'src' );
		
		$('#avatar_thumb').attr('src', '/images/avatar_preloader.gif' );

		$(this).upload('/ajax/avatar_upload/', function( data ) {
			data = $.parseJSON( data.replace(/<pre>/g, "").replace(/<\/pre>/g, "") );
			if( data.error )
			{
				if( $('.user-profile-image').length > 0 )
				{
					$('body').modalAlert( data.error );
				}
				else
				{
					alert( data.error );
				}
				data.succes = avatar_thumb;
			}
			$('#avatar_thumb').attr('src', data.succes );
			$('#imageUpload').val('');
		}, 'html');
	}); */
});