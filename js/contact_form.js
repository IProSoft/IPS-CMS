$(document).ready(function() {
	var form_inputs = $(".contact_form > form input, .contact_form > form textarea");
	form_inputs.on( 'focus', function(){
		$(this).addClass('zaznacz');
	});
	form_inputs.on( 'blur', function(){
		$(this).removeClass('zaznacz');
	});
});

function sendContact()
{

	var email_filter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;
	var valid_form = true;
	$('.contact_form input,.contact_form textarea').each(function(){
		var error = false;
		
		if( $(this).attr('name') == 'email' )
		{
			if( !email_filter.test( $(this).val() ) )	
			{
				var error = true;
				valid_form = false;
			}
		}
		else if( $(this).val().length == 0 || ( $(this).attr('name') == 'captcha' && $(this).val() != captcha_c ) )
		{
			var error = true;
			valid_form = false;
		}

		if( error )
		{
			$(this).parent().find('p').slideDown( 500 );
			$(this).focus();
			
			return false;
		}
		
		$(this).parent().find('p').slideUp( 500 );
	});

	if( valid_form )
	{
		var data = $(".contact_form > form").serialize();
		
		$.ajax({
			type: "POST",
			url: "/ajax/contact_form/",
			data: data,
			cache: false,
			success: function(msg){
				$(".contact_form").fadeOut(1000, function() {
					$("#message_sent").slideDown( 500 );
				});
			}
		});
	}
	return false;
}

var captcha_a = Math.ceil(Math.random() * 10);
var captcha_b = Math.ceil(Math.random() * 10);       
var captcha_c = captcha_a + captcha_b;

function generate_captcha( id )
{
	$( "#" + id ).html(captcha_a + " + " + captcha_b + " = ");
}
