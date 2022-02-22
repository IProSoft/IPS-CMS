(function(factory){
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }
})(function ($){
  'use strict';
	var MemGenerator = function(  ) {
		
		this.container = $('.mem_generator_cnt');
		var s = $('#generator_search');
		this.search_trigger = s.find('button');
		this.search_field = s.find('.form-control');
		this.form_loader = s.find('.input-group-loader');
		this.img_list = $('ul.mem_generator');

		this.upload = this.container.find('.generator_upload');

		this.form = this.container.find('.mem_generator_form');
		
		return this;
	};

	 // MemGenerator methods and shared properties
	MemGenerator.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  MemGenerator,
		bind: function() {
			
			var $this = this;
			/** Search field for images */
			$this.search_trigger.on( 'click', function(){
				
				$this.form_loader.css( { width: $this.search_field.outerWidth(), left : $this.search_field.position().left } ).show();
				
				return IpsApp._ajax( '/ajax/generator_search/', {
					search : $this.search_field.val(), 
					category : $this.search_field.attr('data-category')
				}, 'GET', 'json', true, function( response ){
					if( response.content.length > 0 )
					{
						$this.img_list.find('li:not(.suggest)').remove();
						$this.img_list.prepend( response.content );
					}
					$this.form_loader.hide();
				});
			});
			
			this.loadEditor();
			
			/** Trigger mem upload **/
			this.upload.on( 'click', function(){
				
				IpsApp._shadow.loader();
				
				if ( ips_user.is_logged == false )
				{
					return userAuth('login');
				}
				
				var title = $this.form.find( 'input[name=top_line]' ).val(),
					bottom_line = $this.form.find( 'input[name=bottom_line]' ).val()
				
				if( title.length == 0 )
				{
					var title = $this.form.find( 'input[name=bottom_line]' ).val();
				}
				
				$this.form.find( 'input[name=title]' ).val( title );
				if( $(this).hasClass( 'download' ) )
				{
					$('<input />').attr( {
						name: 'ajax_img_return',
						type: 'hidden'
					} ).val('large').appendTo( $this.form );
				}
				else
				{
					$this.form.find( 'input[name=ajax_img_return]' ).remove();
				}
				return true;
			});
			
	
			/** Start mem add with new image */
			this.img_list.on( 'click', 'a', function(e){
				
				if( $(this).parent().hasClass('suggest') )
				{
					return true;
				}

				e.preventDefault();
				
				var src = $(this).attr('data-image');
				
				$this.form.find( 'input[name=up_generator]' ).val( $(this).attr('data-id') );
				$this.form.find( 'input[name=upload_url]' ).val( src );
					
				$this.container.slideDown( 'fast', function() {
					/** tutaj jakiś loader **/
					$this.canvas.loadFile( $('<img />').attr('src', src ) );
					$this.form.width( $this.canvas.file_preview.width() );
					$this.upload.show();
					$this.download.show();
				});
				
				return false;
			});
		},
		/** Load canvas editor **/
		loadEditor: function(){
			this.canvas = $('#upload_form').DropMem();
			this.canvas.options = $.extend( ips_config.up_generator_options, {
				vertical_align : 'division',
			});
		}
	}
	$(document).ready(function() {
		new MemGenerator().bind();
	});
});

