(function(factory){
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }
})(function ($){
  'use strict';
	
	
	var IpsDropzone = function() {
		this.options = {
			loaded : false,
			lastAction : false,
			loadPreview : false,
			url : '/ajax/drop_upload/',
			onShow: function(){},
			onHide: function(){},
			onfileChange: function(){}
		};
		
		$('#item-select').on('upload.change', $.proxy( this.setMimeType, this ) );
		
		return this;
	};
	
	 // IpsDropzone methods and shared properties
	IpsDropzone.prototype = $.extend( {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  IpsDropzone,
		init : function( options, dropzone_wrap ) {

			var $this = this;

			this.options = $.extend( this.options, options );
			
			this.elements( dropzone_wrap );
			this.initPreview( this.dropzone );
			return this.initDropzone();
		},
		elements : function( dropzone_wrap ){
			
			this.dropzone = dropzone_wrap;
			this.id = '#' + this.dropzone.attr('id');
			this.dropUrl = this.dropzone.find('.dropzone-url');
			this.dropChild = this.dropzone.find('.dropzone,.dropzone-url');
			this.dropCover = this.dropzone.find('.up_video_cover');

			this.form = $('#upload_form');
			
			if( this.options.fileChange === true )
			{
				this.options.fileChange = this.dropzone.find('.up_file_change');
			}
		},
		initDropzone : function(){
		
			var $this = this;
			
			this.drop = {
				message	: this.dropzone.find('.up-message'),
				progress	: this.dropzone.find('.progress'),
				progressLine: this.dropzone.find('.progress-line'),
				error		: this.dropzone.find('.error'),
				circle		: new ProgressBar.Circle( this.dropzone.find('.progress').get(0), {
					color: '#fff',
					strokeWidth: 5,
					trailWidth: 5,
					duration: 1500,
					text: {
						value: '0',
					},
					step: function(state, bar) {
						bar.setText((bar.value() * 100).toFixed(0));
					}
				}),
				line		: new ProgressBar.Line( this.dropzone.find('.progress-line').get(0), {
					color: '#0064a6'
				})
			}
			
			head.load( "/js/dropzone.js", function(){
				
				Dropzone.autoDiscover = false;
				
				$this.dropzoneObject = new Dropzone( $this.dropzone.get(0), {
					url: $this.options.url,
					paramName: "Filedata",
					uploadMultiple: typeof $this.options.multiple != 'undefined',
					acceptedMimeTypes: ( $this.options.mime ? $this.options.mime : 'image/*,video/*' ),
				});
				
				$this.dropzoneObject.on( 'sending', function(file, dataUrl) {
					if( typeof $this.options.thumbnail != 'function' )
					{
						$this.drop.message.hide();
						return $this.drop.progress.fadeIn();
					}
					$this.drop.progressLine.show();
				});

				$this.dropzoneObject.on( 'totaluploadprogress', function(uploadProgress) {
					if( typeof $this.options.thumbnail != 'function' )
					{
						return $this.drop.circle.animate( parseInt( uploadProgress, 0)/100 );
					}
					$this.drop.line.animate( parseInt( uploadProgress, 0)/100 );
				});

				$this.dropzoneObject.on( 'successmultiple', function(file, response) {
					$.each(response.content, function(name) {
						if( typeof this.error == 'undefined' )
						{
							$this.parseResponse( this );
						}
					});
				});
				
				$this.dropzoneObject.on( 'success', function(file, response) {
					
					if( typeof response.content == 'object' )
					{
						return true;
					}
					
					if( typeof response !== 'object' )
					{
						IpsApp._showMessage( ips_i18n.__( 'js_alert_jquery' ), 'alert' );
						IpsApp._timeout( function(){
							//window.location.reload();
						}, 5 )
						return false;
					}
					
					if( typeof response.error != 'undefined' )
					{
						return $this.dropzoneError( response.error ); 
					}

					return $this.parseResponse( response );
				});
				
				$this.dropzoneObject.on( 'complete', function( file, response) {
					if( file.status != 'success' )
					{
						$this.dropzoneError();
					}
				});
				
				$this.dropzoneObject.on( 'thumbnail', function( file, response) {
					if( typeof $this.options.thumbnail == 'function' )
					{
						$this.previewFileReader( file, $this.options.thumbnail );
					}
				});
				
				$this.dropzoneObject.on("processingmultiple", function(file) {
					if( file.type == 'video/mp4' )
					{
						
					}
				});
				
				$this.dropzone.find('.dropzone-url').on( ips_click, function(){
					$(this).find('.url-input').show();
				});
				
				$this.dropzone.on( ips_click, '.dropzone-url-button', function(){
					var url = $this.dropUrl.find('input').val();

					$this.dropzoneObject.emit( 'addedfile', {});
					
					$this.dropUrl.find('.url-actions').slideUp( 100, function(){
						
						$this.dropUrl.find('.url-loader').slideDown( 50 );
						
						if( typeof $this.options.thumbnail == 'function' )
						{
							$this.previewFileUrl( url, $this.options.thumbnail );
						}
						
						IpsApp._ajax( $this.options.url, { upload_url: url, upload_subtype: $('input[name="upload_subtype"]').val() }, 'POST', 'json', false, function( response ){
							
							if( typeof response.error != 'undefined' )
							{
								return $this.dropzoneError( response.error );
							}
							
							if( typeof response.content != 'undefined' )
							{
								return $this.parseResponse( $.extend( response, { url: url } ) );
							}
							
							alert( ips_i18n.__( 'js_alert_jquery' ) );
						});
					});
				});
				
				if( typeof $this.options.on == 'object' )
				{
					for (var key in $this.options.on ) {
						var f = $this.options.on[key];
						$this.dropzoneObject.on( key, f );
					}
				}
			});
			
			
			this.options.fileChange.on( 'click', function(e){
				$this.options.elements.slideUp( 100, function(){
					$this.options.onfileChange();
					$this.reset( true );
				});
				return false;
			});
			
			return this;
		},
		onceTrigger: function(){
			triggerEvent('upload-dropped');
			this.onceTrigger = function(){};
		},
		dropzoneError: function( message ){
			var $this = this;
			
			if( typeof message == 'undefined' )
			{
				var message = message
			}
			
			this.drop.error.html( message ).slideDown('slow');
			
			setTimeout( function () {
				$this.reset( true );
			}, 2800 );
		},
		
		reset: function( show_drop ){
			
			this.drop.error.hide();
			this.drop.circle.set( 0 );
			this.drop.progress.hide();
			this.drop.line.animate( 0 );
			this.drop.progressLine.hide();
			this.drop.message.show();
			
			this.dropUrl.find('.url-loader,.url-input').hide();
			this.dropUrl.find('.url-label,.url-actions').show();
			this.dropUrl.find('input').val('');
			
			this.preview.hide();
			this.options.fileChange.hide();

			if( typeof show_drop !== 'undefined' )
			{
				this.dropChild.show();
				this.dropzone.slideDown();
			}
			
		},
		parseResponse: function( response ){
			
			this.reset();
			
			if( this.options.loadPreview )
			{
				this.getPreview( response );
			}
			
			this.storeInputs( response );
			
			this.onceTrigger();
			
			this.options.fileChange.show();
			
			return this.options.callback( response, this.dropzone );
		},
		storeInputs: function( response ){
			if( response.title && $('#title').val() == '' )
			{
				$('#title').val( response.title );
			}
			
			if( response.url && ( response.ext == 'swf' || response.ext == 'mp4' ) )
			{
				if( response.ext == 'swf' )
				{
					this.dropCover.show();
				}
				
				this.form.find('input[name="upload_subtype"]').val( response.ext );

				var name = 'upload_' + response.ext + '_url',
					input = this.form.find( 'input[name="' + name + '"]' ),
					file = response.url;
			}
			else
			{
				this.dropCover.hide();
				
				var name = 'upload_url',
					input = this.form.find( 'input[name=upload_url]' ),
					file = response.content;
			}
			
			var name = 'upload_url',
				input = this.form.find( 'input[name=upload_url]' );
				
			if( input.length == 0 )
			{
				input = $('<input />').attr( {
					'name': name,
					'type': 'hidden'
				} ).appendTo( this.form );
			}
			
			input.val( response.content );
		},
		captureImage: function( video ){
			
			var canvas = $('#canvas_video_cover');
			
			if( canvas.length == 0 )
			{
				var canvas = $('<canvas />').attr( 'id', 'canvas_video_cover' ).hide().appendTo( this.dropzone );
			}

			var canvasElement = this.dropzone.get(0).querySelector('canvas'),
				context = canvasElement.getContext('2d'),
				vid = video.get(0),
				$this = this;
			
			if( vid.canPlayType && vid.canPlayType('video/mp4').replace(/no/, '') )
			{
				vid.onloadeddata = function() {
					
					canvasElement.width  = video.width();
					canvasElement.height = video.height();
					canvasElement.style.width  = video.width() + 'px';
					canvasElement.style.height = video.height() + 'px';
					
					context.drawImage( vid, 0, 0, canvasElement.width, canvasElement.height);
		 
					IpsApp._ajax( '/ajax/canvas_store/', { upload_canvas: canvasElement.toDataURL() }, 'POST', 'json', false, function(response){
						if( response.url )
						{
							return $this.form.find('input[name="upload_cover_url"]').val( response.url );
						}
						$this.dropCover.show();
					});
				};
				if( vid.readyState === 4 )
				{
					vid.onloadeddata();
				}
			}
			else
			{
				this.dropCover.show();
			}	
		},
		setMimeType: function( e, upload_subtype ){
			
			if( typeof this.mime == 'undefined' )
			{
				this.mime = this.dropzoneObject.options.acceptedFiles;
			}

			if( upload_subtype == 'swf' )
			{
				this.dropzoneObject.options.acceptedFiles = 'application/x-shockwave-flash,.swf';
				this.dropzoneObject.hiddenFileInput.setAttribute("accept", 'application/x-shockwave-flash,.swf');
				return;
			}
			
			this.dropzoneObject.options.acceptedFiles = this.mime;
			this.dropzoneObject.hiddenFileInput.setAttribute("accept", this.mime);
		}
	}, $.fn.IpsPreview );
	
	$.fn.IpsDropzone = function(options) {
		if( $(this).length > 0 )
		{
			return new IpsDropzone().init( options, $(this) );
		}
	}
});

	