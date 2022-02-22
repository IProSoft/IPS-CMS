$.fn.UploadStack = {
	current: false,
	queue: new Queue(false,false),
	call : function(){
		this.queue.next();
	},
	push : function(f){
		var $this = this;
		return this.queue.add( function(){
			f();
			return false;
		});
	},
	remove : function(index){
		return this.queue.remove( index );
	}
};
$(document).ready(function(){

	
	var options = {
		url: '/ajax/drop_upload/',
		callback: function( response, dropzone ){},
		loadPreview: true
	}
	
	try{
		
		
		if( ips_config.upload_action == 'demotywator' )
		{
			var drop = $('#upload_form').DropDemotywator(),
				options = drop.getDropOptions( ips_config.up_demotywator_options, options )
				canvas_text = $('#canvas_text').CanvasText( ips_config.up_text_options );	
			
			if( canvas_text )
			{
				canvas_text.demotDrop = drop;
			}
		}
		else if( ips_config.upload_action == 'mem' )
		{
			var options = $('#upload_form').DropMem().getDropOptions( $.extend( ips_config.up_mem_options, {
				vertical_align : 'division'
			}), options );
		}
		else if( ips_config.upload_action == 'video' )
		{
			var options = $('#upload_form').DropVideo().getDropOptions( ips_config.up_video_options, options );
		}
		else if( ips_config.upload_action == 'gallery' )
		{
			var options = $('#upload_form').DropGallery().getDropOptions( ips_config.up_gallery_options, options );
		}
		else if( ips_config.upload_action == 'ranking' )
		{
			var options = $('#upload_form').DropRanking().getDropOptions( ips_config.up_ranking_options, options );
		}
		else if( ips_config.upload_action == 'animation' )
		{
			var options = $('#upload_form').DropAnimation().getDropOptions( ips_config.up_animation_options, options );
		}
		else
		{
			var options = $.extend( ips_config.up_image_options, options);
		}
		
	}catch(e){
		console.log(e);
	}
	
	//Zmnienione kolejność w extend	
	$('#up_image_file').IpsDropzone( $.extend( {
		elements : $('.up_image'),
		fileChange : $('#up_image_file').find('.up_file_change'),
		mime: 'image/*'
	}, options ) );
	
	$('#up_video_file').IpsDropzone( $.extend( {
		elements : $('.up_video'),
		fileChange : $('#up_video_file').find('.up_file_change'),
		mime: 'video/*'
	}, options ) );

	$('#upload_form').on( 'submit.canvas', function(e){
		e.preventDefault()
		
		var upload_url = ips_config.upload_action ? '/upload/' + ips_config.upload_action + '/' : $('#upload_form').attr('action'),
			tags = $('.upload_tags')
		
		if( tags.length > 0 )
		{
			var value = tags.val();
			if( value !== null )
			{
				tags.parent().find('input[name="upload_tags"]').val( value.join(',') );
			}
		}
		
		$.fn.UploadStack.push( function(){
			
			IpsApp._submit( $('#upload_form').get(0), {
				url: upload_url,
				success: function( response ){
					if( typeof response.error !== 'undefined' )
					{
						IpsApp._showMessage( response.error, false );
					}
					else if( typeof response.content !== 'undefined' )
					{
						IpsApp._showMessage( response.content, false );
						return IpsApp._timeout( function(){
							window.location.href = response.url;
						}, 2 );
					}
					
					if( typeof response.token !== 'undefined' )
					{
						$('#upload_form').find('input[name="_token"]').val( response.token );
					}
				}
			} );
		} );

		$.fn.UploadStack.call();

		return false;
	});
		
	
	
	$('#upload-reset').on( ips_click, function(){
		
		IpsApp._shadow.loader();
		
		var form = $('#upload_form');
		
		form.find('input,textarea').val('');
		
		IpsApp._ajax( '/ajax/upload_clear/', {}, 'POST', 'html', false, function(){
			window.location.reload();
		} );
		
		return false;
	});
	$('.up_long_text').one( 'mouseover', function(){
		$(this).find('.long_text_description').animate({
			left: '-1000px',
			opacity: 0
		});
	})
	if( $('.upload_tags').length > 0 )
	{
		head.load( "/libs/Select2/js/i18n/" + ips_config.locale.shorten + '.js', function(){
			$('.upload_tags').select2( {
				language: ips_config.locale.shorten,
				tags: true,
				tokenSeparators: [','],
				placeholder: "",
				ajax: {
					url: "/ajax/tags",
					type: 'POST',
					dataType: 'json',
					delay: 250,
					data: function ( params ) {
						return {
							q: params.term,
							action: 'autocomplete'
						};
					},
					results: function (data, page) {
						return {
							results: data.results
						};
					},
					cache: true
				},
				minimumInputLength: 1,
			} );
		})
	}
	/** Upload form submit fields verify */
	$('#add-form-submit').on( ips_click, function( e ){
		IpsApp._shadow.loader();
		
		e.preventDefault();
		
		var submit = true;
		
		$('#upload_form').find('.up-verify').each(function(){
			$(this).removeClass('verify-false');
			if( $(this).val() == '' || ( $(this).attr('type') == 'checkbox' && !$(this).is(':checked') ) )
			{
				$(this).addClass('verify-false');
				
				var alert = $(this).attr( 'data-alert' );
				
				if( alert.length > 0 )
				{
					if( alert == 'true' )
					{
						var alert = ips_i18n.__( 'js_form_error' )
					}
					
					IpsApp._showMessage( alert, 'alert' );
				}
				submit = false;
				
				return false;
			}
		})
		
		if( submit )
		{
			$('#upload_form').submit();
		}
	});
	
	
	
	

	$('.up-select span').on( ips_click, function () {	
			$('.up-select span').removeClass('active');
			$(this).addClass('active');
			$(window).trigger( 'up.subtype', [ $(this).attr('data-id') ]);
		return false;
	});		
	
	$('#add_smilar').on( ips_click, function () {
		smilarFiles();
	})
	
	$("#gif_fps").on( 'change', function(){
		$('#upload_form').find('input[name="fps"]').val( $(this).val() );
	});
	
	if( $("#upload_form").length > 0 )
	{
		if( $(".fancy-list").length > 0 )
		{
			$(".fancy-list li a").each(function(){
				if( document.location.href.indexOf( $(this).attr('href' ) ) > 0 )
				{
					$(this).parent().addClass('active');
				}
			});
			if( $(".fancy-list li.active").length == 0 )
			{
				$(".fancy-list li a").first().parent().addClass('active');
			}
		}
		
		
		$("body").on( ips_click, '.change_input', function(e) {
			e.preventDefault();
			checkAddFields( $(this).parent(), $(this).val() );
		});
		
		$('.add-file-input input[type="text"]').each(function(){
			
			var containter = $(this).parent().parent();
			
			$(this).on( 'change', function(){
				checkAddFields( containter, 'link' );
			});
			
			if( $(this).length > 0 && $(this).val() != '' )
			{
				$(this).trigger( 'change' );
			}
			
		});
		
		$('.change_input_auto').on( ips_click, function () {	
			
			var container = $(this).parent();
			
			container.find('input[type="text"]').val('').hide();
			container.find('input[type="file"]').remove();
			
			$('<input>').attr({
				type: 'file',
				id: 'file',
				name: 'file'
			}).insertBefore( container.find('input[type="text"]') );
			
			$(this).remove();
		});	
		
		$('#item-select span').on( ips_click, function(){
			
			var upload_subtype = $(this).attr("data-id");
			
				$("#item-select span").each(function(){
					$(".up_" + $(this).attr("data-id") ).hide();
				});
				
				$(".up_" + upload_subtype ).fadeIn(2000);
				
				$('input[name="upload_subtype"]').val( upload_subtype );
				
				$('#item-select span').removeClass('active');
				$(this).addClass('active');
				
				$('#item-select').trigger('upload.change', [upload_subtype] );
				
			return false; 
		}); 
		
		ipsAddEvent( 'upload-dropped', function (){
			$('#item-select').slideUp('slow', function(){
				$(this).remove();
			});
		});			
	}
});

function tinyEditor( element_id ){
	var editor = new TINY.editor.edit('editor', {
		id: element_id,
		width: 'auto',
		height: 175,
		cssclass: 'tinyeditor',
		controlclass: 'tinyeditor-control',
		rowclass: 'tinyeditor-header',
		dividerclass: 'tinyeditor-divider',
		controls: ['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|',
			'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|', 'leftalign',
			'centeralign', 'rightalign', 'blockjustify', '|', 'undo', 'redo', 'n',
			'font', 'size', 'style', '|', 'image', 'hr', 'link', 'unlink'],
		footer: true,
		fonts: ['Verdana','Arial','Georgia','Trebuchet MS'],
		xhtml: true,
		bodyid: 'editor',
		footerclass: 'tinyeditor-footer',
		toggle: {text: 'source', activetext: 'wysiwyg', cssclass: 'toggle'},
		resize: {cssclass: 'resize'}
	});
	
	setInterval( function(){
		editor.post();
	}, 1000 );
	
	return editor;
}

function checkAddFields ( container, val )
{
	if( val == 'link' )
	{
		container.find('input[type="file"],.input-file-container').hide();
		container.find('input[type="text"],.input-link-container').fadeIn();
	}
	else
	{
		container.find('input[type="text"],.input-link-container').hide();
		container.find('input[type="file"],.input-file-container').fadeIn();
	}
}

/** Smilar files while add demot **/
function smilarFiles(){
	$("#add_smilar_result").show( 'blind', function() {
		IpsApp._ajax( '/ajax/search_smilar/', { action : 'upload', search_by: $('input[name="top_line"]').val() }, 'POST', 'json', false, function( response )
		{ 
			if( typeof response.content !== 'undefined' )
			{
				$("#add_smilar_result").html( response.content ).show("blind").slideDown( 2500 );
			}
			else
			{
				$("#add_smilar_result").hide("blind");
			}
		});
	});
};

this.previewSmilar = function(){	
	$("a.view").hover(function(e){
		this.t = this.title;
		this.title = '';	
		$("body").append( '<p id="preview_smilar"><img src="' + this.rel + '" />' + ( typeof this.t !== 'undefined' ? '<br/>' + this.t : '' ) + '</p>' );							 
		$("#preview_smilar").css("top",(e.pageY - 400) + 'px').css( 'left',( e.pageX - 360 ) + 'px').fadeIn( 500 );						
    },
	function(){
		this.title = this.t;	
		$("#preview_smilar").remove();
    });	
	
	$("a.view").mousemove(function(e){
		$("#preview_smilar").css( 'top',( e.pageY - 400 ) + 'px' ).css( 'left',( e.pageX - 120 ) + 'px' );
	});			
};
