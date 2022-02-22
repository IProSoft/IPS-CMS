$(document).ready(function(){
	$(".register-email").on( ips_click, function(){
		if( ips_config.version != 'pinestic' )
		{
			$('.register-email-container').slideDown();
			$(this).remove();
		}
		else
		{
			$(this).parent().slideUp('fast', function(){
				$(this).slideDown().parent().find('.register-email-container, h3,.button-red,.action-submit').show();
			}).children().hide();
		}
		return false;
	});
	
	head.load( "//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js", function(){
		jQuery('.validate-form').validate({
			rules: {
				login: {
					minlength: 1,
					required: true
				},
				first_name: {
					minlength: 1,
					required: true
				},
				last_name: {
					minlength: 1,
					required: true
				},
				email: {
					minlength: 5,
					required: true,
					email: true
				},
				pass: {
					minlength: 3,
					required: true
				},
				password: {
					minlength: 3,
					required: true
				},
				password_p: {
					minlength: 3,
					required: true,
					equalTo: 'input[name="password"]'
				},
				birth_date: {
					minlength: 2,
					required: true
				},
				accept_rules: "required"
			},
			errorPlacement: function(error, element) {
			},
			invalidHandler: function(form, validator) {
				var errors = validator.numberOfInvalids();
				if (errors) {
					//alert( ips_i18n.__( 'js_form_validate_all' ) );
				}
			},
			highlight: function(label) {
				
				$(label).parents('.input-wrap').addClass('error-validate');
				$('.action-submit').prop('disabled', true );
			},
			unhighlight : function(label) {
				$(label).parents('.input-wrap').find('.error-msg').each(function(){
					$(this).slideUp( 'slow', function(){
						$(label).parents('.input-wrap').removeClass('error-validate');
						$(this).removeAttr('style');
					} );
				});
			},
			submitHandler: function( form ) {
				
				var url = $(form).attr('data-validation');
				
				IpsApp._formSpinner.add( $(form) );
				
				IpsApp._ajax( '/ajax/' + ( url ? url : 'validate' ) + '/', $(form).serialize(), 'POST', 'json', false, function( response ){ 

					if( typeof response.error !== 'undefined' )
					{
						IpsApp._formSpinner.remove( $(form) );
						
						if( $('.g-recaptcha').length > 0 )
						{
							grecaptcha.reset();
						}
						
						if( ips_config.version == 'pinestic' )
						{
							return $('body').modalAlert( response.error );
						}
						
						showDialog( false, response.error, true, 'no', 'msg-dialog' );
					}
					else if( typeof response.success !== 'undefined' )
					{
						if( typeof response.modal_redirect !== 'undefined' )
						{
							return window.location.href = response.modal_redirect;
						}
						
						window.location.href = '/index.html';
					}
				});
			},
			success: function(label) {
				if( $('.action-submit').is(':disabled') && $('.error-validate').length == 0 )
				{
					$('.action-submit').prop('disabled', false);
				}
			}
		});

	});
	
});