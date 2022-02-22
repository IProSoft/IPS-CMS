<?php
/**
 * IPS-CMS
 *
 * Copyright (c) IPROSOFT
 * Licensed under the Commercial License
 * http://www.iprosoft.pro/ips-license/	
 *
 * Project home: http://iprosoft.pro
 *
 * Version:  2.0
 */ 
			
	require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/php-load.php');
	if( !USER_ADMIN ) die ("Hakier?");
	
	$_REQUEST = array_merge( $_GET, $_POST, $_COOKIE );
	
	if( !isset( $_GET['fanpage_id'] ) )
	{
		ips_admin_redirect( 'fanpage', 'action=settings', array(
			'info' => 'Wybierz Fanpage z listy'
		));
	}
	
	if( !empty( $_REQUEST['code'] ) )
	{
		$url = 'https://graph.facebook.com/oauth/access_token?';
		$url .= 'client_id=' . Config::get('apps_facebook_app', 'app_id');
		$url .= '&redirect_uri=' . ABS_URL.'inc/plugins/api-facebook.php?fanpage_id=' . $_GET['fanpage_id'];
		$url .= '&client_secret=' . Config::get('apps_facebook_app', 'app_secret');
		$url .= '&code=' . $_REQUEST['code'];
			
		$json = curlIPS( $url, array(
			'timeout' => 10, 
		) );
		
		try {
			
			$decoded = json_decode( $json, true );
			
			if( is_array( $decoded ) )
			{
				ips_admin_redirect( 'fanpage', 'action=settings', array(
					'alert' => $decoded['error']['message']
				));
			}
			
			$fanpage_token = str_replace( 'access_token=', "", $json );
			
			if( strpos( $fanpage_token, '&expires' ) !== false  )
			{
				$fanpage_token = substr( $fanpage_token, 0, strpos( $fanpage_token, '&expires' ) );
			}
			
			$count = Facebook_Fanpage::assignAccesTokens( $fanpage_token );
			
			$fanpages = Config::getArray( 'apps_fanpage_array' );

			if( is_array( $fanpages ) )
			{
				$fanpage = ips_search_array( 'fanpage_id', $_GET['fanpage_id'], $fanpages );
				
				if( isset( $fanpage['api_token'] ) )
				{
					ips_admin_redirect( 'fanpage', 'action=settings', array(
						'info' => 'API Facebook zaktualizowane dla <b>' . $count . '</b> stron.'
					));
				}
			}
			
			ips_admin_redirect( 'fanpage', 'action=settings', array(
				'alert' => 'Aby skonfigurować API musisz posiadać uprawnienia administracyjne dla Fanpage: ' . Config::get('apps_fanpage_default')
			));
		  
		} catch(Exception $ex) {
			var_dump($ex);
			exit;
		}
		
			
	}
	else
	{	
		header( 'Location: https://graph.facebook.com/oauth/authorize?' 
		. 'client_id='.Config::get('apps_facebook_app', 'app_id') 
		. '&scope=manage_pages,publish_pages' 
		. '&redirect_uri='.ABS_URL.'inc/plugins/api-facebook.php?fanpage_id=' . $_GET['fanpage_id'] ); 
	}

?>