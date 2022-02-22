/** 
* Ad mem lines - extends drop_lines.js
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
	
	var DropMem = function( drop_wrap ) {
		$(this).one( 'lines.trigger', $.proxy( this.linesPosition, this ) );
		return this;
	};
	
	DropMem.prototype = $.extend( {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropMem,
		drop_url: '/ajax/drop_upload_mem/',
		getClass: function( element, options ){
			return element.CanvasMem( options );
		},
		linesPosition: function( e, top_line, bottom_line ){
			var margin = this.options.margin,
				css = {
					left: ( parseInt(margin.side) + parseInt(margin.border_width) ) + 'px',
					right:( parseInt(margin.side) + parseInt(margin.border_width) ) + 'px'
				};

			top_line.element.css( css ).css({
				top: ( parseInt(margin.top) + parseInt(margin.border_width) ) + 'px'
			});
			
			bottom_line.element.css( css ).css({
				bottom: ( parseInt(margin.top) + parseInt(margin.border_width) ) + 'px'
			});
		}
	}, $.fn.DropLinesObject );
	
	$.fn.DropMem = function() {
		return new DropMem().initialize( $(this) );
	}
});