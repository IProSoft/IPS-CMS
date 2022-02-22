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
	
	var DropVideo = function( drop_wrap ) {
		
		$('#item-select').on('upload.change', $.proxy( this.setVideoType, this ) );
		
		return this;
	};
	
	DropVideo.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  DropVideo,
		drop_url: '/ajax/drop_upload_video/',
		initialize: function( drop_wrap ){
			this.drop_wrap = drop_wrap;
			this.video_url = this.drop_wrap.find('#video_url');
			this.dropzone = this.drop_wrap.find('.dropzone');
			this.drop_wrap.find('.dropzone-preview').addClass('up_video');
			return this;
		},
		getDropOptions: function( options, extendOptions ){
			
			this.options = options;
			
			this.extendOptions = $.extend({}, options, extendOptions, {
				url: this.drop_url
			});
			
			
			return this.extendOptions;
		},
		setVideoType: function( e, upload_subtype ){
			this.video_url.attr( 'name', 'upload_' + upload_subtype + '_url').val('');
			if( upload_subtype == 'video' )
			{
				this.dropzone.slideUp();
			}
			else
			{
				this.dropzone.slideDown();
			}
		},
	};
	
	$.fn.DropVideo = function() {
		return new DropVideo().initialize( $(this) );
	}
});