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
	
	var CanvasDemotywator = function() {
		
		this.shadowClicked = false;
		this.lastText = '';

		return this;
	};
	
	CanvasDemotywator.prototype = $.extend( $.fn.CanvasTypeAbstractObject, {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  CanvasDemotywator,
		upload_url: '/ajax/up_demotywator/',
		setText: function( text ){
			if( text.length > 0 )
			{
				//this.lastText = text;
				this.shadow.hide();
				this.canvasEditor.documentRange().clear();
				this.canvasEditor.select(0, 0, false);
				this.canvasEditor.insert( text, false);
			}
		},
		bgFillColor: function( options ){
			return options.margin.box_color;
		},
		shadowOptions: function( options ){
			return {};
		},
		borderOptions: function( options ){
			return {};
		}
	}, $.fn.CanvasInitObject );
	
	$.fn.CanvasDemotywator = function(options) {
		return new CanvasDemotywator().abstractInit( options, $(this) );
	}
});