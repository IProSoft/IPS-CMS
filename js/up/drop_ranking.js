/** 
* Ad ranking images
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
	
	var DropRanking = function( drop_wrap ) {
		return this;
	};
	
	DropRanking.prototype = $.extend( {}, $.fn.UploadMultipleObject, {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropRanking,
		drop_url: '/ajax/drop_upload/',
		initialize: function(){
			return this.init();
		},
		
		beforeSubmit: function(){
			if( $('.image-thumbs-preview').find('.activ') == 0 && !this.options.create_image )
			{
				$this.options.upload_url.val( $this.options.images[Math.floor(Math.random()*$this.options.images.length)]);
			}
			if( this.options.create_image )
			{
				$('input[name=upload_url]').remove();
			}
		},
		getDropOptions: function( options, extendOptions ){
			return this.pushOptions( $.extend( {}, options, {
				upload_images: $('input[name="upload_images"]'),
				local: ips_config.up_ranking_options.local
			} ), extendOptions );
		},
		
		beforeAppend: function( current ){
			if( this.options.create_image )
			{
				current.find('.intro').remove()
			}
			return current;
		},
		intro: function( element ){
			
			$('.image-thumbs-preview').find('.activ').not( element ).removeClass('activ');
			
			var img = element.toggleClass('activ').parent().find('img').attr('src');
			if( !this.options.create_image )
			{
				this.options.upload_url.val( img );
			}
		},
	});
	
	$.fn.DropRanking = function() {
		return new DropRanking().initialize( $(this) );
	}
});