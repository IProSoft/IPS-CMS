/** 
* Ad mem - extends canvas_init
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
	
	var CanvasMem = function() {
		
		this.shadowClicked = false;
		this.lastText = '';

		return this;
	};
	
	CanvasMem.prototype = $.extend( {}, $.fn.CanvasTypeAbstractObject, {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  CanvasMem,
		upload_url: '/ajax/up_mem/',
		bgFillColor: function( options ){
			return parseInt( options.add_background ) ? options.background_color : false;
		},
		shadowOptions: function( options ){
			var opts = {};
			if( parseInt( options.add_shadow ) )
			{
				var opts = {
					shadow: true,
					shadowBlur: options.shadow_blur,
					shadowColor: options.shadow_color
				};
			}
			return opts;
		},
		borderOptions: function( options ){
			var opts = {};
			if( parseInt( options.add_border ) )
			{
				var opts = {
					border: true,
					borderColor: options.border_color
				};
			}
			return opts;
		}
	}, $.fn.CanvasInitObject );
	
	$.fn.CanvasMem = function(options) {
		return new CanvasMem().abstractInit( options, $(this) );
	}
});