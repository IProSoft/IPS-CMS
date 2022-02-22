(function(factory){
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }
})(function ($){
  'use strict';
	var CanvasInit = function() {
		this.options = {
			loaded : false,
			lastAction : false
		}
		return this;
	};
	
	 // CanvasInit methods and shared properties
	CanvasInit.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  CanvasInit,
		init : function( options, canvas_wrap ) {
			
			this.id = IpsApp._rand( 10, 'string' );
			this.options = $.extend( true, {}, this.options, options );
			this.options.hash = IpsApp._rand( 10, 'string' );
			
			this.elements( canvas_wrap );
			
			var $this = this;

			this.lastPush = $.fn.UploadStack.push( $.proxy( $this.storeCanvas, this ) ).lastPush;
		},
		elements : function( canvas_wrap ){
			
			this.wrap = canvas_wrap;
		
			this.editorElement = this.wrap.find('.canvas_editor');

			this.canvas = this.editorElement.find('canvas');

			this.img = this.wrap.find('.img-container').find('img');
			
			this.shadow = this.wrap.find('.container-shadow');
			
			this.undo = this.wrap.find('.canvas_action.undo');
			this.redo = this.wrap.find('.canvas_action.redo');
			
			this.canvas_size = this.wrap.find('.canvas_size_wrap');
		},
		getCanvas : function(){
			return this.wrap.get(0).querySelector( 'canvas' );
		},
		reload : function(){
			this.shadow.show();
			this.img.attr( 'src', '' ).attr( 'data-src', '' );
			if( this.options.user_bg == 0 )
			{
				this.defaultBg();
			}
		},
		loadEditor : function( callback ){
			
			var $this = this;
			this.carota = this.getCarota();
				
			var elem = this.editorElement.get(0)
		
			this.canvasEditor = this.carota.editor.create( elem );
			
			this.intrval = this.carota.editor.intrval;
			
			this.getCanvas().getContext('2d').textBaseline = 'middle';
			
			this.canvasEditor.setVerticalAlignment('middle');
			
			/* console.log( this.carota.runs ); */
			
			// Wire up undo command
			if( this.undo.length > 0 )
			{
				this.carota.dom.handleEvent( this.undo.get(0), ips_click, function() {
					if( $this.options.lastAction == 'size' )
					{
						$this.fontSize( $this.options.lastSize - 2 );
					}
					$this.canvasEditor.performUndo( false );
					return false;
				});
			}
			
			// Wire up redo command
			if( this.redo.length > 0 )
			{
				this.carota.dom.handleEvent( this.redo.get(0), ips_click, function() {
					if( $this.options.lastAction == 'size' )
					{
						$this.fontSize( $this.options.lastSize - 2 );
					}
					$this.canvasEditor.performUndo( true );
					return false;
				});
			}
			
			this.wrap.find('.size_change').on( ips_click, function(){
				var current = parseInt( $this.options.font_size ),
					set = $(this).hasClass( 'size_plus' ) ? current + 1 : current - 1;
				
				$this.options.lastSize = current;
				
				return $this.fontSize( set )
			});
			
			this.wrap.find('.canvas_font_wrap').find('ul').on( ips_click, 'a', function(){
				
				var set = $(this).attr( 'data-font' );
				
				$('.canvas_font_wrap').children('a').find('span').css( 'font-family', set.replace(/-/g, ' ') );
				
				$this.options.font = set;
				
				if( $this.canvasEditor.documentRange().plainText() == '' )
				{
					$this.carota.format.setOption( 'font', set.replace(/-/g, ' ') );
				}
				
				$this.canvasEditor.selectedRange().setFormatting( 'font', set.replace(/-/g, ' ') );
				
				$(this).parents('.responsive-slide').hide().off('clickout');
				$(this).parents('.ui-active').first().removeClass('ui-active');
				
				return false;
			});
			
			this.wrap.find( '.canvas_color').on( 'change', function(){
				
				$this.canvasEditor.selectedRange().setFormatting( 'color', $(this).val() );
						
				return false;
			});

			['bold', 'italic', 'underline', 'strikeout'].forEach(function(id) {
				
				var c_action = $this.wrap.find( '.canvas_action.' + id );
				
				if( c_action.length > 0 )
				{
					var elem = c_action.get(0);
					
					$this.carota.dom.handleEvent( elem, ips_click, function() {
					
						var val = elem.getAttribute('data-action') == 'true';
						
						elem.setAttribute('data-action', ( val ? 'false' : 'true' ) );
						
						$this.canvasEditor.selectedRange().setFormatting( id, !val );
						
						return false;
						
					});
				}
			});
			
			['left', 'center', 'right', 'justify'].forEach(function(id) {
				
				var c_action = $this.wrap.find( '.canvas_action.' + id );
				
				if( c_action.length > 0 )
				{
					var elem = c_action.get(0);
				
					$this.carota.dom.handleEvent( elem, ips_click, function() {
						
						$this.canvasEditor.selectedRange().setFormatting( 'align', id );
						
						return false;
					});
				}
			});

			this.canvasEditor.contentChanged( function(){
				$this.preserveHeight();
				if( typeof $this.onChange == 'function' )
				{
					$this.onChange();
				}
			} );
			
			this.wrap.find('.canvas_action.help').on( ips_click, function(){
				IpsApp._showMessage( $(this).attr('data-title'), 'info', 10 );
			});
			
			this.shadow.on( ips_click, function(){
				$(this).hide();
				$this.canvasEditor.select( $this.canvasEditor.frame.length - 1, $this.canvasEditor.frame.length - 1 );
			});

			this.options.loaded = true;
			
			if( typeof callback == 'function' )
			{
				callback();
			}
			
			return this.canvasEditor
		},
		
		/* attachEvents : function(){
			var $this = this;
			
			$(window).on('up.subtype', function(event, type) {
				$this.reload();
			});
			
		}, */
		fontSize: function( set ){
			
			this.options.lastAction = 'size';
			
			this.options.font_size = set;
			this.canvas_size.html( set );
			
			if( this.canvasEditor.documentRange().plainText() == '' )
			{
				this.carota.format.setOption( 'size', set );
			}
			this.canvasEditor.selectedRange().setFormatting( 'size', set );
			
			return false;
			
		},

		getResized : function( width, font_size ){
			
			var canvas = this.getCanvas();
			
			this.fontSize( font_size );
			
			if( width != canvas.width )
			{
				var height = parseInt( ( width * canvas.height ) / canvas.width );

				var css = {
					'width': width + 'px',
					'max-width': width + 'px',
					//'height': height + 'px',
					//'opacity': 0,
					//'position': 'absolute'
				}
				if( ips_config.upload_action == 'text' )
				{
					css.height = height + 'px';
				}
				this.wrap.hide()
					.css( css )
					.find('.canvas_editor,.carota_spacer,.carota_spacer canvas')
					.css( css );
				
				this.wrap.show();

				canvas.width = width;
				canvas.height = height;
			}
			
			this.intrval();
			
			for (var i = 0; i < 10000; i++) {}
			
			var text_height = 0,
				dataUrl = this.getCanvas().toDataURL('image/png');
		
			jQuery.each( this.canvasEditor.frame.lines, function(i, val) {
				text_height = val.baseline;
			});
			
			return {
				img : dataUrl, 
				text_height: text_height
			}
		},
		calculateFontSize: function( current, width ){
			return parseInt( current * ( width / this.getCanvas().width ) );
		},
		getCarota: function(){
					
			var options = $.extend( {}, this.options, {
				size: this.options.font_size,
				font: this.options.font.family.replace( /-/g, ' '),
				color: this.options.font_color,
				shadow: this.options.shadow == 1,
				shadowColor: this.options.shadow_color,
				shadowBlur: this.options.shadow_blur,
				padding: 10,
				bold: false,
				italic: false,
				underline: false,
				strikeout: false,
				align: 'center',
				script: 'normal'
			} );
			
			return CanvasEditor( this.id, {}, options );
		}
	};
	
	$.fn.CanvasInitObject = new CanvasInit();
	
	$.fn.CanvasInit = function(options) {
		return new CanvasInit().init( options, $(this) );
	}
});