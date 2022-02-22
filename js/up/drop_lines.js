/** 
* Ad mem/demotywator lines main class - extends drop_lines.js
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
	
	var DropLines = function( drop_wrap ) {
		return this;
	};
	
	DropLines.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropLines,
		initialize: function( drop_wrap ){
			
			this.drop_wrap = drop_wrap;
			this.file_preview = $('.up_file_preview');
			
			return this;
		},
		getDropOptions: function( options, extendOptions ){
			
			this.options = options;
			
			this.extendOptions = $.extend({}, extendOptions, {
				url: this.drop_url,
				loadPreview: $.proxy( this.loadFilePreview, this ),
				thumbnail: $.proxy( this.loadFile, this ),
			});
			
			return this.extendOptions;
		},
		loadFilePreview: function( file, response ){
		
			if( !response.ext )
			{
				this.drop_wrap.find('input[name="upload_url"]').val( response.content );
			}
			
			this.loadFile( file );

		},
		loadFile: function( file ){
			
			this.triggerLines();
			
			var lines = this.file_preview.find('.canvas_lines');

			this.file_preview.css( 'width', file.width );
			
			if( lines.find('.file-cnt').length > 0  )
			{
				return lines.find('.file-cnt').html( file );
			}
			
			var file = $('<div />').attr( 'class', 'file-cnt' ).html( file ).css({
					'border': this.options.margin.border_width + 'px solid ' + this.options.margin.border_color
				}),
				div = $('<div />').attr( 'class', 'file-margin' ).html( file ).css({
					'padding': this.options.margin.top + 'px ' + this.options.margin.side + 'px',
					'background-color': this.options.margin.box_color
				});
			
			switch( this.options.vertical_align )
			{
				case 'top':
					lines.append( div );
				break;
				case 'bottom':
					lines.prepend( div );
				break;
				case 'division':
					lines.find('.top_line').after( div );
				break;
			}
			
			lines.addClass( this.options.vertical_align );
			
			lines.css({
				'background-color' : this.options.margin.box_color
			});
			this.file_preview.find('.loader').hide();
			this.file_preview.find('button').show();

			this.file_preview.addClass('active');
		},
		attach: function( name ){
					
			var element = $('.canvas_wrap_line.' + name + '_line'), 
				input = this.drop_wrap.find('[name="' + name + '_line"]');
			
			var object = this.getClass( element, $.extend( true, {}, {
				onChangeInput: this.drop_wrap.find('[name="' + name + '_line_json"]'),
				onChangeInputPlain: input
			}, this.options, {
				font_color : this.options['font_color_' + name]
			} ) );
			
			object.layersInput = this.drop_wrap.find('[name="' + name + '_line_layers"]');
			
			return {
				input: input,
				object: object,
				element: element
			};
		},
		triggerLines: function(){
			
			$(this).trigger( 'lines.trigger', [ this.attach( 'top' ), this.attach( 'bottom' ) ] );
			
			this.triggerLines = function(){};
			
		}
	};
	
	$.fn.DropLinesObject = new DropLines();
	
	$.fn.DropLines = function() {
		return new DropLines().initialize( $(this) );
	}
});