$(document).ready(function() {
	
	if( ips_config.upload_action == 'video' )
	{
		$('#item-select').find( '.up_item_' + $('input[name="upload_subtype"]').val() ).trigger('click')
	}
	
});