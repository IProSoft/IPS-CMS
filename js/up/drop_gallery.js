/** 
* Ad gallery images
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
	
	var DropGallery = function( drop_wrap ) {
		return this;
	};
	
	DropGallery.prototype = $.extend( {}, $.fn.UploadMultipleObject, {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropGallery,
		drop_url: '/ajax/drop_upload/',
		initialize: function(){
			return this.init();
		},
		getDropOptions: function( options, extendOptions ){
			return this.pushOptions( $.extend( {}, options, {
				upload_images: $('input[name="upload_images"]'),
				local: ips_config.up_gallery_options.local
			} ), extendOptions );
		},
		
		intro: function( element ){
			
			$('.image-thumbs-preview').find('.activ').not( element ).removeClass('activ');
			
			var img = element.toggleClass('activ').parent().find('img').attr('src');
			
			this.options.upload_url.val( img );
		},
	});
	
	$.fn.DropGallery = function() {
		return new DropGallery().initialize( $(this) );
	}
});