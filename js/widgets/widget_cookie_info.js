$(document).ready( function(){
	if ( navigator.cookieEnabled && $('.cookie-policy-inform-content').length > 0 )
	{
		$('.cookie-policy-inform-content').html( ips_i18n.__( 'js_cookie_policy_info' ) );
		$('.cookie-policy-inform-close a').html( ips_i18n.__( 'js_close' ) );
		$('.cookie-policy-inform').fadeIn();
		
		$('.cookie-policy-inform-close a').on( ips_click, function(e) {
			e.preventDefault();
			$('.cookie-policy-inform').fadeOut();
			$.cookie( 'ips_cookie_policy', 'checked', {
				expires	: '1y',
				path	: '/'
			});
		});
	}
});