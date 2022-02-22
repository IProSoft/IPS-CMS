(function(factory){
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }
})(function ($){
  'use strict';
	
	var CanvasText = function() {
		return this;
	};
	
	CanvasText.prototype = $.extend( {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  CanvasText,
		
		initialize: function( options, canvas_wrap ){
			
			var $this = this;

			this.init( this.parseOptions( options ), canvas_wrap );
			
			this.demotDrop = false;
			this.IpsDrop = false;
			this.active = true;
			this.dropzone = $('#up_text_dropzone');
			this.imgInput = this.wrap.find('input[name="text_bg_link"]');
			this.layersInput = this.wrap.find('input[name="upload_text_layers"]');
			this.textInput = $('#upload_text');
			this.form = $('#upload_form');
			
			if( this.options.user_bg == 1 && this.dropzone.length > 0 )
			{
				this.IpsDrop = $this.dropzone.IpsDropzone({
					url: '/ajax/drop_upload_text/',
					callback: function( response ){
						$this.imgInput.val( response.content );
						$this.loadImg( response.shadow );
					},
					elements : $this.wrap.find('.canvas_element'),
					fileChange : $this.wrap.find('.up_file_change'),
					onfileChange: function(){
						$this.shadow.show();
					}
				});
			}
			else if( this.imgInput.val().length > 0 )
			{
				this.loadImg( $this.imgInput.val() );
			}
			else if( ips_config.upload_action != 'demotywator' )
			{
				this.defaultBg();
			}
			
			if( !this.IpsDrop )
			{
				this.wrap.find('.up_file_change').remove();
			}
			
			$('.up_image_rand').on( ips_click, function(){
				$this.reset( function(){
					$this.dropzone.slideUp( 150, function(){
						$this.defaultBg();
					})
				} );
				return false;
			});
			
			this.textInput.one( 'change', function(){
				$this.shadow.hide();
			});
			
			var upload_text = this.textInput.val();
			
			if ( upload_text.length > 0 )
			{
				this.canvasEditor.load( JSON.parse( upload_text ), function(){
					$this.shadow.hide();
				} );	
			}
			
			ipsAddEvent( 'upload-dropped', function (){
				if( $this.form.find('input[name="upload_subtype"]').val() !== 'text' )
				{
					$.fn.UploadStack.remove( $this.lastPush );
				}
			});
			
			return this;
		},
		onChange: function(){
			
			this.undo.attr( 'disabled', !this.canvasEditor.canUndo(false) );
			this.redo.attr( 'disabled', !this.canvasEditor.canUndo(true) );
				
			this.textInput.val( JSON.stringify( this.canvasEditor.save(), null, 4 ) );
			
		},
		reset: function( callback ){
			callback();
			if( typeof this.IpsDrop != 'undefined' && this.IpsDrop )
			{
				this.IpsDrop.reset();
			}
		},
		parseOptions: function( options )
		{
			return $.extend( options, {
				baselineRatio: 0,
				topPadding: 0
			});
		},
		onChange: function(){
			this.preserveHeight();
				
			this.undo.attr( 'disabled', !this.canvasEditor.canUndo(false) );
			this.redo.attr( 'disabled', !this.canvasEditor.canUndo(true) );
			
			this.textInput.val( JSON.stringify( this.canvasEditor.save(), null, 4 ) );
		},
		loadImg: function( src )
		{
			var $this = this;
			
			var img = new Image();

			img.src = src;

			$( img ).load( function( event ){
				
				$this.img.attr( 'src', img.src );
				
				$this.dropzone.slideUp( 'slow', function(){
					
					if( !$this.options.loaded )
					{
						$this.loadEditor(function(){
							if( $this.demotDrop )
							{
								var img = $('<img src="' +  IpsApp.svg_loader + '" class="spinner" width="100px"  />')
								$this.demotDrop.loadFile( img );
								img.replaceWith( $this.wrap );
							}
						});
					}
					
					$this.img.attr( 'data-src', img.src );
					
					$this.wrap.find('.canvas_loader').slideUp( 'fast', function(){
						$this.wrap.find('.canvas_element').slideDown( 100 );
					});
				});
				
			}).error(function (){});
			
			if( this.IpsDrop )
			{
				this.onceTrigger();
			}
		},
		onceTrigger: function(){
			triggerEvent('upload-dropped');
			this.onceTrigger = function(){};
		},
		defaultBg : function(){
			var $this = this;
			$('.canvas_loader').slideDown( 'fast', function(){
				IpsApp._ajax( '/ajax/up_text/', { 
					func: 'getBg', 
					args: [ 
						$this.options.bg 
					] 
				}, 'POST', 'json', false, function( response ){
					if( typeof response.content !== false )
					{
						$this.loadImg( response.content );
					}
				});
			} );
			
		},
		preserveHeight : function(){

	 		var $this = this;
			
			if( typeof this.canvasEditor.frame._bounds == 'undefined' )
			{
				return setTimeout(function(){
					$this.preserveHeight();
				}, 100);
			}
			
			var max_height = this.canvasEditor.frame._bounds.h,
				img_cnt = this.img.parent(),
				container = this.wrap.find('.canvas_wrap');
		
			if( img_cnt.height() > 0 )
			{
				if( max_height <= img_cnt.height() )
				{
					return container.css( 'height', img_cnt.height() + 'px' );
				}
				
				switch( this.options.user_bg_fit )
				{
					case 'full':
						
						if( !container.hasClass('canvas-bg-ready') )
						{
							container.css( {
								'background-image' : 'url(' + this.img.attr( 'data-src' ) + ')',
								'background-size' : 'contain',
							} );
						}
						
						container.css( 'height', max_height );
						
					break;
					
					case 'fit_text':
						
						$('.canvas_action.undo').trigger( ips_click );
					
					break;
					
					case 'fill_color':
						
						if( !container.hasClass('canvas-bg-ready') )
						{
							img_cnt.css( 'opacity', 0 );
							container.css( {
								'background-image' : 'url(' + this.img.attr( 'data-src' ) + ')',
								'background-size' : 'contain',
								'background-repeat' : 'no-repeat',
								'background-position' : 'center center',
								'background-color' : this.options.user_bg_fit_fill_color,
							} );
							
						}
						container.css( 'height', max_height );
					break;
				}
				
				return false;
			}
		},
		getDimensions: function(){
			
			if( this.demotDrop )
			{
				return this.demotDrop.options.size
			}
			
			return {
				medium: this.options.size.medium,
				large: this.img.get(0).naturalWidth
			}
		},
		storeCanvas : function(){
			
			if( !this.active )
			{
				return $.fn.UploadStack.call();
			}
			
			this.canvasEditor.saving = true;
			this.canvasEditor.select( 0, this.canvasEditor.frame.length - 1 );

			var $this = this,
				dims = this.getDimensions(),
				current_size = parseInt( this.options.font_size ),
				large = this.getResized( dims.large, current_size ),
				medium = this.getResized( dims.medium, this.calculateFontSize( current_size, this.options.size.medium ) );
			 
			/** Reset font size */
			this.canvasEditor.select( 0, this.canvasEditor.frame.length - 1 );
			this.fontSize( current_size );
			
			return IpsApp._ajax( '/ajax/up_text/', { 
				func: 'storeText',
				args : [
					medium,
					large,
					$this.img.attr( 'src' )
				]
			}, 'POST', 'json', false, function( response ){
				
				$this.imgInput.val( response.content.img );
				$this.layersInput.val( response.content.layers );
				
				$.fn.UploadStack.call();
			} );
		},
	}, $.fn.CanvasInitObject );
	
	$.fn.CanvasText = function(options) {
		if( $(this).length == 0 )
		{
			return false
		}
		return new CanvasText().initialize( options, $(this) );
	}
});