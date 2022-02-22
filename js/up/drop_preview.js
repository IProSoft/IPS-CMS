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
	
	var IpsPreview = function( drop_wrap ) {
		return this;
	};
	
	IpsPreview.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  IpsPreview,
		initPreview: function( container ){
			this.preview = container.find('.dropzone-preview');
		},
		getPreview: function( response ){

			if( response.ext == 'swf' || response.ext == 'mp4' || response.ext == 'video' )
			{
				return this.previewVideo( response );
			}
			
			var $this = this,
				img = new Image();

			img.src = response.content;
			
			$( img ).load( function(){
				$this.previewShow( $( img ), response );
			}).error(function (){});
		},
		previewVideo: function( response ){
			
			switch( response.ext )
			{
				case 'mp4':
					var element = 
					'<video controls="controls">' +
						'<source src="'+ response.content +'" type="video/mp4">' +
					'</video>';
				break;
				case 'swf':
					var element = '<iframe src="' + response.content + '" width="' + response.width + 'px" height="' + response.height + 'px"></iframe>';
				break;
				case 'video':
					var element = response.upload_preview;
				break;
			}
			return this.previewShow( $( element ), response );
		},
		previewDimensions: function( element ){
			
			var input = $('#upload_form').find('input[name="upload_video_size"]');
			
			if( !this.inputDims || input.length == 0 )
			{
				this.inputDims = $( '<input />' ).attr( 'name', 'upload_video_size' ).attr( 'type', 'hidden' ).appendTo('#upload_form');
			};
			
			this.inputDims.val( JSON.stringify({
				width: element.width(),
				height: element.height()
			}) );
		},
		previewFileReader: function( file, callback ){

			this.hideDrop();
			
			var reader = new FileReader(),
				$this = this; 
			
			reader.onload = function( file )
			{ 
				callback( $('<img />').attr('src', file.target.result ) );
			}; 
			
			reader.readAsDataURL( file ); 
		},
		previewFileUrl: function( url, callback ){

			this.hideDrop();
			
			callback( $('<img />').attr('src', url ) ); 
		},
		previewShow: function( file, response ){
			
			var $this = this;
			
			this.hideDrop();
			
			IpsApp._timeout(function(){
				$this.previewDimensions( file );
				if( response.ext == 'mp4' )
				{
					$this.captureImage( file );
				}
			}, 2);
			
			if( typeof this.options.loadPreview == 'function' )
			{
				return this.options.loadPreview( file, response );
			}
			
			this.previewDisplay( file );
		},
		previewDisplay: function( file ){
			
			var $this = this;
			
			if( this.preview.find('.file-cnt').length == 0 )
			{
				this.preview.append( this.createPreview() )
			}
			
			this.preview.find('.file-cnt').hide().html( '' );
			this.preview.find('button').hide();
			
			this.preview.find('.loader').show();
			
			this.preview.slideUp( 'fast', function(){

				$(this).find('.file-cnt').html( file ).show();
				$(this).find('.loader').hide();
				$(this).find('button').show();
				
				$(this).css( 'display', 'inline-block' );
			});
			
		},
		createPreview: function(){
			
			var file = $('<div />').attr( 'class', 'file-cnt' ).css({
				'border': this.options.border_width + 'px solid ' + this.options.border_color
			});
			
			return $('<div />').attr( 'class', 'file-margin' ).html( file ).css({
				'padding': this.options.top + 'px ' + this.options.side + 'px',
				'background-color': this.options.box_color
			});
		},
		hideDrop: function(){
			if( typeof this.options.hideDrop == 'function' )
			{
				return this.options.hideDrop( this );
			}
			this.dropChild.hide();
		}
	};
	
	$.fn.IpsPreview = new IpsPreview();

});