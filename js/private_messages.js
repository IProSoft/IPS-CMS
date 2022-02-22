$.ips_messages = function( options )
{
	
	var opts  = $.extend({}, $.ips_messages.defaults, options);
	
	this.init = function()
	{
		$(opts.formTrigger).on( 'click', this.send_form );
		$(opts.sendTrigger).on( 'click', this.send );
		$(opts.moveTrigger).on( 'click', this.del );
		$(opts.deleteTrigger).on( 'click', this.del );
		$(opts.answerTrigger).on( 'click', this.answer );
	}

	this.send_form = function( e )
	{
		e.preventDefault();
		if( $(opts.messagesList).length > 0 )
		{
			$(opts.messagesList).fadeOut( 200, function(){
				$(opts.form).fadeIn();
			});
		}
		else
		{
			$(opts.form).fadeIn();
		}
		return false;
	}
	this.send = function( e )
	{
		e.preventDefault();
		
		$(opts.form).find('input, textarea').each(function(){
			$(this).removeClass( 'message_alert' );
		})
		
		var message_to = $(opts.form).find('[name="message_to"]');
		var message_subject = $(opts.form).find('[name="message_subject"]');
		var message_content = $(opts.form).find('[name="message_content"]');
		

		if( message_to.val().length < 3 )
		{
			message_to.addClass( 'message_alert' );
		}
		else if( message_subject.val().length < 3 )
		{
			message_subject.addClass( 'message_alert' );
		}
		else if( message_content.val().length < 3 )
		{
			message_content.addClass( 'message_alert' );
		}
		else
		{

			var dialog = overflowMsg( ips_i18n.__( 'js_pw_sending' ) );
			
			var response = IpsApp._ajax( '/ajax/private_messages/send/', $(opts.form).find('form').serialize(), 'POST' );
			
			if( typeof response.content == 'undefined' || typeof response.error !== 'undefined' )
			{
				dialog.text( ( typeof response.error !== 'undefined' ? response.error : ips_i18n.__( 'js_alert_jquery' ) ) );
			}
			else if( typeof response.content !== 'undefined' )
			{
				dialog.text( response.content );
				if( typeof response.success !== 'undefined' )
				{
					$(opts.form).find('input, textarea').each(function(){
						$(this).val( '' );
					});
					
					$(opts.form).fadeOut();
				}
			}
			
			closeOverflowMsg( dialog, 2000 );
			
		}
		return false;
	}
	this.del = function()
	{
		var message_id = $(this).attr('data-id');
		
		var response = IpsApp._ajax( '/ajax/private_messages/' + message_id, { message_delete : ( typeof $(this).attr( 'data-delete' ) !== 'undefined' ? 'delete' : 'move' ) }, 'POST' );
		
		if( typeof response.success !== 'undefined' )
		{
			$( "#message_" + message_id ).fadeOut();
			
			if( $(".message_view:visible").length > 0 )
			{
				$(".message_view").fadeOut( 200, function(){
					window.location = '/messages/';
				});
			}
		}
	}
	this.answer = function()
	{
		
		if( $(opts.form + ':visible').length > 0 )
		{
			$( opts.form ).fadeOut();
		}
		else
		{
			$( opts.form ).find('textarea').val(  $('input[name="reply_message"]').val().replace(/<br\s*[\/]?>/gi, "\n") + "\n" );
			$( opts.form ).find('input[name="message_subject"]').val( $('input[name="reply_subject"]').val() );
			$( opts.form ).find('input[name="message_to"]').val( $('input[name="reply_from"]').val() );
			$( opts.form ).slideDown(500);
		}
	}
	
	this.init();
}
$.ips_messages.defaults = {
	formTrigger : '#messages_form',
	form : '#messages_form_wrapper',
	sendTrigger : '#messages_send',
	moveTrigger : '.messages_move',
	deleteTrigger : '.messages_delete',
	messagesList : '#messages_list table',
	answerTrigger : '.messages_answer',
};

$(document).ready(function() {
	$.ips_messages({ });
});


