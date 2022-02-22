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
	
	var DropMultiple = function( drop_wrap ) {
		return this;
	};
	
	DropMultiple.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropMultiple,
		drop_url: '/ajax/drop_upload/',
		init: function(){
			var $this = this;
			$.fn.UploadStack.push( function(){
				if( $this.options.images.length == 0 )
				{
					return IpsApp._showMessage( ips_i18n.__( 'js_up_add_images' ), 'alert' );
				}
				
				$this.beforeSubmit()

				$.fn.UploadStack.queue.next();
			});

			return this;
		},
		pushOptions: function( options, extendOptions ){
			
			var $this = this;
			
			this.options = $.extend( {
				local: true
			}, options, {
				images: new Array(),
				count_verify : 100,
				upload_url: $('input[name=upload_url]')
			} );
			
			this.extendOptions = $.extend({}, extendOptions, {
				url: this.drop_url,
				loadPreview: $.proxy( this.loadFile, this ),
				hideDrop: function(){},
				thumbnail: function(){},
				multiple: true,
				on: {
					addedfile: $.proxy( this.addPreloader, this ),
					drop: function(){
						console.log(arguments);
						console.log('drop');
					},
					queuecomplete: function(){
						
					},
					error: function(){
						$('.image-thumbs-preview').find('.empty').last().remove();
					},
					success: function(){
						$this.options.preloaded--;
					},
					sendingmultiple: function(){
						console.log(arguments);
						console.log('sendingmultiple');
					},
					completemultiple: function(){
						console.log(arguments);
						console.log('completemultiple');
					}
				}
			});
			
			return this.extendOptions;
		},
		addPreloader: function(){

			var p = $('.image-thumbs-preview');
			
			if( p.length == 0 )
			{
				p = $('<div class="image-thumbs-preview" />').insertAfter( '#up_image_file' );
				var $this = this;
				p.on( 'click', '.delete', function( event ){
					$this.deleteImage( $(this).parent() );
				});
				p.on( 'click', '.intro', function( event ){
					$this.intro( $(this) );
				});
			}
			
			var current = $('<span class="ips-scale empty"><img src="/images/svg/spinner.svg" /><i class="delete"></i><i class="intro">Intro</i></span>'),
				img = current.find('img');
			
			current = this.beforeAppend( current );
			
			p.append( current ).fadeIn( 'slow' );
		},
		loadFile: function( file, response ){
			
			var p = $('.image-thumbs-preview').find('.empty').first().removeClass('empty'),
				img = p.find('img');
			
			
			img.animate({
				opacity: 0
			}, 300, function() {
				img.attr( 'src', file.attr( 'src' ) ).animate({
					opacity: 1
				}, 800)
			});
			
			if( this.options.local || typeof response.url == 'undefined' )
			{
				return this.pushImage( file.attr('src') );
			}
			
			return this.pushImage( response.url );
		},
		beforeAppend: function(current){
			return current;
		},
		beforeSubmit: function(){
			if( $('.image-thumbs-preview').find('.activ') == 0 )
			{
				$this.options.upload_url.val( $this.options.images[Math.floor(Math.random()*$this.options.images.length)]);
			}
			this.options.upload_images.val( $.toJSON( this.options.images ) );
		},
		pushImage: function( src ){
			this.options.images.push( src );
			this.options.upload_images.val( $.toJSON( this.options.images ) );
		},
		deleteImage: function ( element )
		{
			var removeItem = element.find('img').attr('src');

			element.animate({
				width: 0
			}, 2000, function(){
				element.remove();
			});	
			
			this.options.images.splice( $.inArray( removeItem, this.options.images ), 1 );	
		},
	};
	
	$.fn.UploadMultipleObject = new DropMultiple();
});