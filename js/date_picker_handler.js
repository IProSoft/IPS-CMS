jQuery(function($) {
	
	$.datepicker.regional['pl'] = {
		closeText: ips_i18n.__( 'js_close' ),
		prevText: ips_i18n.__( 'js_datepicker_prev' ),
		nextText: ips_i18n.__( 'js_datepicker_next' ),
		currentText: ips_i18n.__( 'js_datepicker_today' ),
		monthNames: ips_i18n.__( 'js_datepicker_months' ).split('|'),
		monthNamesShort: ips_i18n.__( 'js_datepicker_short_months' ).split('|'),
		dayNames: ips_i18n.__( 'js_datepicker_day_names' ).split('|'),
		dayNamesShort: ips_i18n.__( 'js_datepicker_day_names_short' ).split('|'),
		dayNamesMin: ips_i18n.__( 'js_datepicker_day_names_minshort' ).split('|'),
		weekHeader: ips_i18n.__( 'js_datepicker_week' ),
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	};
		
	$.datepicker.setDefaults( $.datepicker.regional['pl'] );
	console.log($( "#birth_date" ).val());
	$( "#birth_date" ).datepicker({
		dateFormat: 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		yearRange: "-100:-1",
		defaultDate: $( "#birth_date" ).val()
	});
});