$(document).ready(function() {
	
	$( 'ul.up-button-items' ).on( ips_click, '.clickable a', function( e ){
		
		e.preventDefault();
		
		var type = $(this).data('type');
		var url = $(this).attr('href');
		
		if ( ips_user.is_logged == false )
		{
			userAuth('login');
			return false;
		}
		 
		var buttons = {
				'up':{
					text: 'Upload',
					'class': '',
					click : function(){

						var loader = IpsApp._formSpinner.add( $dialog.parent() );
						
						IpsApp._ajax( url , new FormData( document.getElementById('upload_form') ), 'UPLOAD', 'json', false, function( response ){
							
							loader.fadeOut();
							
							if( typeof response.error != 'undefined' )
							{
								return $('#upload_form .error-span').html( response.error );
							}

							$('#upload_form').replaceWith( response.content );
							
							buttons.up.text = response.text;
							
							buttons.up.click = function() {
								window.location.href = 	response.url;
							};
							
							$dialog.dialog('option', 'buttons', buttons );
							
						});
					}
				},
				'cancel':{
					text: 'Cancel',
					'class': 'cancel',
					click: function() {
						$dialog.dialog( 'close' );
					}
				}
			};
		
		var $dialog = $( '<div style="display:none"><img width="48" height="48" src="/images/svg/spinner.svg"></div>' ).dialog({
			resizable: false,
			width: dialogWidth( 500 ),
			modal: true,
			position: { my: 
				"center", 
				at: "top+25%", 
				of: window
			},
			buttons: buttons
		});
		
		IpsApp._ajax( '/ajax/get_template/' , { template_name : 'upload_simple', up_type : type }, 'POST', 'json', false, function( response ){
			$dialog.dialog( 'option', 'title', $(response.content).find('h2').html() ).html( response.content );
		});

	})
});