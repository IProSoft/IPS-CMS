/**
* Pobieranie komentarzy z serwera
*/

function loadComments( file_id )
{
	if( typeof file_id == 'undefined' )
	{
		var file_id = ips_config.file_id;
	}
	
	if ( !$('#comments_wrapper').hasClass('reveal') )
	{
		$('.comments-visibility').html( ips_i18n.__( 'js_load' ) );
		/* Loader */	
		$("#comment-load").slideDown(500, function(){
			IpsApp._ajax( '/ajax/comment_load/' + file_id, {}, 'GET', 'json', true, function( response ){
				
				$('#comments').html( response.content );
				
				$("#comment-load").slideUp(500, function(){
					$('#comments_wrapper').toggleClass('reveal');
					$('.comments-visibility').html( ips_i18n.__( 'js_comments_hide' ) );
				});
				
				if( typeof response.best_comments != 'undefined' && response.best_comments.length > 0 )
				{
					$(".comments-best").html( response.best_comments ).parent().removeClass('display_none');
				}
			});
		});		
	}
	else
	{
		$('#comments_wrapper').toggleClass('reveal');
		$('.comments-visibility').html( ips_i18n.__( 'js_comments_show' ) );
		comments_visible_alltime = true;
	}
	
	$('#comments_status').toggleClass('display_none');
}

/**
* Automatyczne ładowanie komentarzy
*/
comments_visible_alltime = false;
$(document).ready(function(){
	
	var add_root = $("#comment_add_cnt");
	
	if ( $(".comments_form").length !== 0 && !$(".comments_form").hasClass('visible') )
	{
		$(".comments_form textarea").on( 'click', function(){
			$(this).parents('.comments_form').addClass('visible');
		});
	}
	/**
	* Zakładki komentarzy w szablonie demotywatory
	*/
	if ( $(".comments-nav li").length !== 0 )
	{
		$(".comments-nav li").first().addClass('active-comments');
		
		$(".comments-nav li").click(function(){
			$(".comments-nav li").removeClass('active-comments');
			
			$(this).addClass('active-comments');
			
			$("#comments_status").addClass('display_none');
			
			if( $(this).attr('rel') == 'normal' )
			{
				if( !$('#comments_wrapper').hasClass('reveal') )
				{
					loadComments();
				}
				$(".comments-fb").hide();
				$(".comments-ajax").slideDown();
			}
			else
			{
				$(".comments-ajax").hide();
				$(".comments-fb").slideDown();
			}
			
		});
	}
	
	if( $(".comments-ajax").length > 0 && ( $.cookie('comments-visibility') == 'show' || $('#comments_wrapper').hasClass('comments-set-visible') ) )
	{
		loadComments( ips_config.file_id );
		comments_visible_alltime = true;
	}
	
	function showHiddenComment(id){
		console.log(this);
		$("#comment_div_"+id).slideToggle();
		if( $(this).html() == ips_i18n.__( 'js_com_hidden_show' ) )
		{
			$(this).html( ips_i18n.__( 'js_com_hidden_hide' ) );
		}
		else
		{
			$(this).html( ips_i18n.__( 'js_com_hidden_show' ) );
		}
	}
	
	$("body").on( 'click', ".is_negative a", function(){
		
		$(this).parent().next('div').slideToggle();
		
		$(this).html( ( $(this).html() == ips_i18n.__( 'js_com_hidden_show' ) ? ips_i18n.__( 'js_com_hidden_hide' ) : ips_i18n.__( 'js_com_hidden_show' ) ) );

		return false;
	});
	
	$("body").on( ips_click, ".comment-vote", function(){
		
		if ( ips_user.is_logged == false )
		{
			userAuth('login');
			return false;
		}
		
		var this_cnt = $(this);
		
		var vote_action = this_cnt.hasClass('up') ? 1 : 0;
		
		IpsApp._ajax( '/ajax/vote_comment/', { comment_id: this_cnt.parents('.comment').attr('data-id'), vote_action: vote_action }, 'GET', 'json', false, function( response )
		{ 
			if( typeof response.error !== 'undefined' )
			{
				showDialog( false, response.error, true);
			}
			else if( typeof response.success !== 'undefined' )
			{
				this_cnt.parent().find(".comment-vote-response").html( response.success );
				this_cnt.parent().find(".comment-opinion").html( response.comment_opinion );
				this_cnt.parent().find(".comment-votes").html( response.comment_votes );
			}
		});
	});

	$("#comment_field").on( ips_click, function(u) {
		u.stopPropagation();
		
		var s = $(this).parent().addClass("selected");
		$(this).focus();

		if (!mobileT()) {
			$("body").not('#comment_add_cnt').one( ips_click, function(x) {
				var y = x.target;
				if ($(y).parents('#comment_add_cnt').length == 0) {
					s.removeClass("selected");
				}
			})
		}
	});
	$('#comments_wrapper').on( ips_click, '.ips-reply-comment', function(u) {
		
		var self = $(this).parents('.comment');
		$("form#comment_moderate").remove();	
	
		IpsApp._ajax( '/ajax/get_template/' , { template_name : 'comments_reply_js', comment_id : self.attr('data-id'), upload_id : self.attr('data-upload-id')  }, 'POST', 'json', false, function( response ){
			self.after( $( response.content ) );
			$(".comment-reply-form").fadeIn();
		});
	});
	
	
	$('#comments_wrapper').on( ips_click, '.ips-edit-comment', function(u) {
		
		var comment_id = $(this).parents('.comment').attr('data-id');
		
		$('#comment_moderate').find('textarea').html('');
		$("#comment_moderate").remove();
		
		var comment = $( "#comment_div_" + comment_id + ' .comment-content' ).html();
		
		IpsApp._ajax( '/ajax/get_template/' , { template_name : 'comments_js_mod', comment_id : comment_id }, 'POST', 'json', false, function( response ){
			
			var comment_moderate = $( response.content );
			
			var comment_modified = '<br><br><span class\="comment_modified">([^<]+)</span>';
			
			comment_moderate.find('textarea').html( comment.replace( new RegExp( comment_modified, 'g'), '' ) )
			
			$("#comment_div_" + comment_id).after( comment_moderate );
		
		} );
	});
	
	
	$('#comments_wrapper').on( ips_click, '.ips-change-comment', function(u) {
		
		u.preventDefault();

		var content = $('#comment_moderate').find('textarea').val();
		
		var comment_id = $('#comment_moderate').find('input').val();
		
		var comment_modified = $('#comment_moderate .comment_modified_input').is(':checked');
		
		var response = IpsApp._ajax( '/ajax/moderate_comment/' + comment_id, { action : 'edit', content : content, comment_modified : comment_modified  }, 'POST' );
		
		
		if( typeof response.error !== 'undefined' )
		{
			var dialog = overflowMsg( response.error );
		}
		else if( typeof response.success !== 'undefined' )
		{
			var dialog = overflowMsg( response.success );
			
			$("#comment_div_" + comment_id).find('.comment-content').html( response.content );
		}
		
		closeOverflowMsg( dialog, 1000 );
		modCommentClose();

	});
	
	$('#comments_wrapper').on( ips_click, '.ips-delete-comment', function(u) {
		
		var self = $(this).parents('.comment');
		
		var comment_id = self.attr('data-id');

		var response = IpsApp._ajax( '/ajax/moderate_comment/' + comment_id, { action : 'delete'  }, 'POST' );
		
		
		if( typeof response.error !== 'undefined' )
		{
			var dialog = overflowMsg( response.error );
		}
		else if( typeof response.success !== 'undefined' )
		{
			var dialog = overflowMsg( response.success );
			
			self.fadeOut( 'slow' );
		}
		
		closeOverflowMsg( dialog, 1000 );
		modCommentClose();

	});
	
	$('#comments_wrapper').on( ips_click, '.comment-go', function(e) {
		
		e.preventDefault();
		
		var comment_id = $(this).attr('data-id');
		
		$('html, body').animate({
			scrollTop: $( "#comment_div_" + comment_id ).offset().top - 200
		}, 1000);

	});
});	
/**
* Automatyczne ładowanie komentarzy wł/wył
*/
function saveComments()
{
	if( $.cookie('comments-visibility') !== 'show' || comments_visible_alltime )
	{
		$.cookie('comments-visibility', 'show', { expires: 7, path: '/' });
		loadComments();
		$('.comments-visibility-alltime').html( ips_i18n.__( 'js_comments_show_hidden' ) );     
		$('#comments_wrapper').toggleClass('reveal');
		comments_visible_alltime = false;
	}
	else if ( $.cookie('comments-visibility') == 'show' )
	{
		$.removeCookie('comments-visibility', { expires: -7, path: '/' });
		$('#comments_wrapper').toggleClass('reveal');
		$('.comments-visibility').html( ips_i18n.__( 'js_comments_show' ) );
		$('.comments-visibility-alltime').html( ips_i18n.__( 'js_comments_show_visible' ) );		
	}
}


function addComment( upload_id, normal_comment, reply_to_id )
{
		if ( ips_user.is_logged == false && ips_user.guest_comment == false )
		{
			userAuth('login');
			return false;
		}
		
		var comment_content = normal_comment == '1' ? $('textarea#comment_field').val() : $('textarea#comment_field_answer').val();
		var comment_type = normal_comment == '1' ? 'comment' : 'reply';
		
		if ( comment_content == '' )
		{
			showDialog(false, ips_i18n.__( 'js_com_empty' ), true, 'info');	
		}
		else
		{
			if ( normal_comment == '0' )
			{
				$(".comment-reply-form").fadeOut('fast', function(){
					$(".comment-reply-form").remove();
				});
			}
			
			if ( ("#comments:visible").length == 0 )
			{
				IpsApp._ajax( '/ajax/comment_load/' + upload_id, {}, 'GET', 'json', true, function( response ){
					$('#comments').html( response.content );
					$('#comments, #comments-form-box').slideDown('slow');
					$('.comments-visibility').html( ips_i18n.__( 'js_comments_hide' ) );
					hideLoad('comment-load')
				});	
			}
			
			var dialog = overflowMsg( ips_i18n.__( 'js_com_adding' ) );

			IpsApp._ajax( '/ajax/comment_add/' + upload_id, { comment_field: comment_content, reply_to_id: reply_to_id }, 'POST', 'json', false, function( response ){
				
				if( typeof response.content == 'undefined' || typeof response.error !== 'undefined' )
				{
					dialog.text( ( typeof response.error !== 'undefined' ? response.error : ips_i18n.__( 'js_alert_jquery' ) ) );
				}
				else if( typeof response.content !== 'undefined' )
				{
					$("#comments h3").slideUp('fast', function(){
						$(this).remove();
					})
					
					dialog.text( response.message );
					
					if( comment_type == 'comment' )
					{
						$("#comments").prepend( response.content );
						$("#comment_field").val(''); 
					}
					else
					{
						$("#comment_field_answer").val('');
						$(".comment_div_" + reply_to_id ).after( response.content );
					}
				}
				
				closeOverflowMsg( dialog, 2000 );
				
				if ( $("#comments:visible").length == 0 )
				{
					loadComments()
				}

				setTimeout(function(){
					if( typeof reply_to_id != 'undefined' )
					{
						var scroll = $( "#comment_div_" + reply_to_id );
					}
					else if( typeof response.insert_id != 'undefined' )
					{
						var scroll = $('#comments');
					}
					else
					{
						var scroll = $('#comment_field');
					}
					
					$('html, body').animate({
						scrollTop: $( scroll ).offset().top - 100
					}, 1000);
				}, 1000 );
				
			} );
			
			
		};
		
		return false;
	};



function usun_comment( comment_id )
{
		var dialog = overflowMsg( ips_i18n.__( 'js_delete_comment' ) )
			
		var response = IpsApp._ajax( '/ajax/comment_delete/' + comment_id, {}, 'POST' );
		
		if( typeof response.error !== 'undefined' )
		{
			dialog.text( response.error );
		}
		else if( typeof response.success !== 'undefined' )
		{
			dialog.text( response.success );
			
			$("#comment_div_"+comment_id).fadeOut( 'slow' );	
				
		}
		
		closeOverflowMsg( dialog, 2500 );
			
		return false;	
}

function modCommentClose()
{
	$("#comment_moderate").fadeOut();
}


function closeAnswerForm(){
	$(".comment-reply-form").fadeOut();
	setTimeout(function(){ 
		$(".comment-reply-form").remove();
	}, 800 )
	return false;
}



function doTag(tag1,tag2)
{
	textarea = document.getElementById('comment_field');

	if (document.selection) 
	{
		textarea.focus();
		var sel = document.selection.createRange();
		sel.text = tag1 + sel.text + tag2;
	}
	else 
    {  
		var len = textarea.value.length;
	    var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		var scrollTop = textarea.scrollTop;
		var scrollLeft = textarea.scrollLeft;

        var sel = textarea.value.substring(start, end);
	    //alert(sel);
		var rep = tag1 + sel + tag2;
        textarea.value =  textarea.value.substring(0,start) + rep + textarea.value.substring(end,len);
		
		textarea.scrollTop = scrollTop;
		textarea.scrollLeft = scrollLeft;
		
		
	}
}

function makeEmot( emot_type )
{
	textarea = document.getElementById('comment_field');
	if (document.selection) {
		textarea.focus();
			sel = document.selection.createRange();
			sel.text = emot_type;
	}
	else if (textarea.selectionStart || textarea.selectionStart == '0') {
			var startPos = textarea.selectionStart;
			var endPos = textarea.selectionEnd;
				textarea.value = textarea.value.substring(0, startPos)+ emot_type+ textarea.value.substring(endPos, textarea.value.length);
	}
	else
	{
		textarea.value += emot_type;
	}
	textarea.focus();
}

function alertVideo( )
{
	var n = prompt( ips_i18n.__( 'js_bbcode_video_enter_url' ), ips_i18n.__( 'js_bbcode_video_enter' ) );
	textarea = document.getElementById('comment_field');
	var only_yt = n.match(/^(https?:\/\/)?(www\.)?youtube.*watch\?v=([0-9a-zA-Z\-_]+)/);
	if ( only_yt )
	{
		if ( document.selection )
		{
			textarea.focus();
			sel = document.selection.createRange();
			sel.text = '[video]' + n + '[/video]';
		}
		else if (textarea.selectionStart || textarea.selectionStart == '0')
		{
			var startPos = textarea.selectionStart;
			var endPos = textarea.selectionEnd;
			textarea.value = textarea.value.substring(0, startPos)+ '[video]' + n + '[/video]' + textarea.value.substring(endPos, textarea.value.length);
		}
		else
		{
			textarea.value += '[video]' + n + '[/video]';
		}
	}
	else
	{
		alert( ips_i18n.__( 'js_bbcode_video' ) );
	}
}