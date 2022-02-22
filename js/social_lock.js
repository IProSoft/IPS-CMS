$(document).ready(function() {
	IpsApp._FB.afterInit( function(){
		FB.Event.subscribe('edge.create', function ( url ) {
			IpsApp._ajaxAsync( '/ajax/api_facebook/', {
				ui_action: 'user_social_lock',
				url : url
			}, 'POST', 'json', false, function( response ){
				if( response.content )
				{
					//window.location.reload();
				}
			} );
		});
	});
});