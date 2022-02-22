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
	
	var DropAnimation = function( drop_wrap ) {
		return this;
	};
	
	DropAnimation.prototype = $.extend( {}, $.fn.UploadMultipleObject, {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropAnimation,
		drop_url: '/ajax/drop_upload/',
		initialize: function(){
			var $this = this;
			
			$('#generate_animation').on( ips_click, function( event ){
				
				event.preventDefault();
				
				if( $this.options.images.length < 2 )
				{
					return IpsApp._showMessage( ips_i18n.__( 'js_up_add_images' ), 'alert' );
				}
				
				$this.generate();
			});
			
			$('#upload_form').find('.animation-delete').on( ips_click, function(e){
				e.preventDefault();
				return $this.reset();;
			} );
			
			
			return this.init();
		},
		getDropOptions: function( options, extendOptions ){
			return this.pushOptions( $.extend( {}, options, {
				upload_images: $('input[name="upload_images"]')
			} ), extendOptions );
		},
		
		beforeAppend: function( current ){
			current.find('.intro').remove();
			return current;
		},
		pushImage: function( src ){
			this.options.images.push( src );
		},
		generate: function ()
		{
			var form = $('#upload_form'),
				fps = form.find('input[name="fps"]'),
				container = form.find('.animation-add');
			
				container.show().find('.animation-preview').html('<img width="48" height="48" src="/images/svg/spinner.svg">');
				
				IpsApp._ajax( '/ajax/animation/', {
					images : JSON.stringify( this.options.images ), 
					fps: fps.val()
				}, 'POST', 'json', false, function( response ){
					container.find('.animation-preview').html( '<img src="/upload/tmp/' + response.image + '" />' );
					
					form.find('.submit').removeAttr( 'disabled' );
				});
			this.options.images
			return false;
		},
		reset: function(){
			
			$('#upload_form').find('.animation-preview').slideUp( 500, function(){
				$(this).html( '' );
			})

			$('.submit').attr('disabled');
			
			IpsApp._ajaxAsync( '/ajax/animation/', { reset: true }, 'POST' );
			
			return false;
		}
	});
	
	$.fn.DropAnimation = function() {
		return new DropAnimation().initialize( $(this) );
	}
});