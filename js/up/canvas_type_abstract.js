/** 
* Ad demotywator - extends canvas_init
*/
(function(factory){
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }
})(function ($){
  'use strict';
	
	var CanvasTypeAbstract = function() {
		
		this.shadowClicked = false;
		this.lastText = '';

		return this;
	};
	
	CanvasTypeAbstract.prototype = $.extend( {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  CanvasTypeAbstract,
		abstractInit: function( options, canvas_wrap ){
			var $this = this;
			
			this.line_pos = canvas_wrap.hasClass('top_line') ? 'top_line' : 'bottom_line';
			
			this.init( this.parseOptions( options ), canvas_wrap );
			
			this.wrap.find( '.canvas_wrap' ).css( {
				'min-height': ( this.options.font_size * 2.1 ) + 'px'
			} ).find( '.container-shadow' ).children('span').css( {
				'font-size': this.options.font_size + 'px',
				'padding-top':( this.options.font_size / 3.2 ) + 'px'
			} )
			
			this.loadEditor();
			
			this.canvasEditor.setVerticalAlignment('top');
			
			this.shadow.one( ips_click, function(){
				
				var range = $this.canvasEditor.selectedRange();
				
				$this.canvasEditor.select( 0, $this.canvasEditor.frame.length );
				
				range.doc.modifyInsertFormatting( 'font', $this.options.font.family.replace( /-/g, ' ') );
				range.doc.modifyInsertFormatting( 'color', $this.options.font_color );
				range.doc.modifyInsertFormatting( 'size', ( $this.line_pos == 'top_line' ? $this.options.font_size_top : $this.options.font_size_bottom ) );
				range.doc.modifyInsertFormatting( 'shadow', $this.options.shadow );
				
				$this.shadow.parents('.canvas_wrap_line').addClass('clicked');
				
			});
			
			this.wrap.find( '.canvas_editor' ).one( ips_click, '*', function(){
				$this.wasClicked = true;
			});
			
			if ( this.options.onChangeInput.length > 0 )
			{
				if ( this.options.onChangeInput.val().length > 0 )
				{
					this.canvasEditor.load( JSON.parse( this.options.onChangeInput.val() ), function(){
						$this.shadow.trigger('click');
					} );	
				}
			}
			
			return this;
		},
		onChange: function(){
			if( typeof this.options.onChangeInputPlain != 'undefined' )
			{
				this.options.onChangeInputPlain.val( this.canvasEditor.documentRange().plainText() )
			}
			return this.options.onChangeInput.val( JSON.stringify( this.canvasEditor.save(), null, 4 ) );
		},
		parseOptions: function( options )
		{
			var font_size = ( this.line_pos == 'top_line' ? options.font_size_top : options.font_size_bottom );
			
			return $.extend( {}, options, {
				font_size: font_size,
				color: options.font_color,
				shadow: false,
				shadowBlur: 0,
				shadowColor: '',
				baselineRatio: 0,
				topPadding: 1,
				bgFillColor: this.bgFillColor( options ),
			}, this.shadowOptions( options ), this.borderOptions( options ) );
		},
		preserveHeight : function(){
			var $this = this;
			
			if( typeof this.canvasEditor.frame._bounds == 'undefined' )
			{
				return setTimeout( function(){
					$this.preserveHeight();
				}, 100 );
			}
			
			var h = this.canvasEditor.frame._bounds.h + 5;
			
			return this.wrap.find('.canvas_wrap').css( {
				'height': h + 'px',
				'max-height': h + 'px'
			} );
		},
		storeCanvas : function(){
			
			this.canvasEditor.saving = true;
			this.canvasEditor.select( 0, this.canvasEditor.frame.length - 1 );
			
			var $this = this,
				current_size = parseInt( this.options.font_size ),
				large = this.getResized( this.options.size.large_canvas, current_size ),
				medium = this.getResized( this.options.size.medium_canvas, this.calculateFontSize( current_size, this.options.size.medium_canvas ) );
			 
			/** Reset font size */
			this.canvasEditor.select( 0, this.canvasEditor.frame.length - 1 );
			this.fontSize( current_size );
			
			this.canvasEditor.saving = false;
			
			return IpsApp._ajax( this.upload_url, { 
				func: 'storeLayers',
				args : [
					medium,
					large,
					this.line_pos
				]
			}, 'POST', 'json', false, function( response ){
				
				$this.layersInput.val( response.content.layers );
				
				$.fn.UploadStack.queue.next();
				
			} );
		}
	}, $.fn.CanvasInitObject );
	
	$.fn.CanvasTypeAbstractObject = new CanvasTypeAbstract();
	
	$.fn.CanvasTypeAbstract = function(options) {
		return new CanvasTypeAbstract().abstractInit( options, $(this) );
	}
});