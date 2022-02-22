/** 
* Ad demotywator lines - extends drop_lines.js
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
	
	var DropDemotywator = function( drop_wrap ) {
		
		$(this).one( 'lines.trigger', $.proxy( this.afterTrigger, this ) );
		
		return this;
	};
	
	DropDemotywator.prototype = $.extend( {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropDemotywator,
		drop_url: '/ajax/drop_upload_demotywator/',
		afterTrigger: function( e, top_line, bottom_line ){
			
			top_line.input.on( 'keyup.ips', function(){
				top_line.object.setText( $(this).val() );
			}).trigger('keyup.ips').parents('.input-wrap').show();
			
			$('.up_title_hide').find('input[name="up_title_hide"]').on( 'change', function(){
				if( top_line.element.is(':visible') )
				{
					top_line.input.show();
					return top_line.element.slideUp();
				}
				top_line.input.hide();
				return top_line.element.slideDown();
			});
			
		},
		getClass: function( element, options ){
			return element.CanvasDemotywator( options );
		}
	}, $.fn.DropLinesObject );
	
	$.fn.DropDemotywator = function() {
		return new DropDemotywator().initialize( $(this) );
	}
});