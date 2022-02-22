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
	if( !defined('IPS_VERSION') ) die();
	
	$_REQUEST = array_merge( $_GET, $_POST, $_COOKIE );
	
	$set_provider = has_value( 'provider', $_GET, 'facebook' );
	 
	$connect = new Connect();
	$provider = $connect->getProvider( $set_provider );

	if( $provider )
	{
		if( get_input('verified') != ''  && $user = $provider->getUser() )
		{
			$user_id = $connect->isConnected( $user['uid'] );
			
			/* User not connected */
			if(  $user_id )
			{
				/* User already connected - login */
				$login = new Users();
				return $login->setLogged( $user_id );
			}
			
			$user_data = $provider->getUserData( $user );
			
			if( $connect->setParams( $user_data ) )
			{
				if( Config::GET( 'apps_facebook_app', 'auto_user_name' ) && $connect->canAutoCreate( $user_data ) )
				{
					/** Return id or false, to display form */
					$user_id = $connect->makeAutoCreate();
					
					if( $user_id )
					{
						return $connect->login( $user_id );
					}
				}
				
				Session::set( 'connect', array_merge( array(
					'provider' => $connect->provider
				), $user_data ) );
			
				return $connect->form();
			}
		}
		
		/* User redirect url to authenticate */
		$redirect = $provider->getRedirect();

		if( $redirect && !Cookie::get( 'ips_connect_redirect' ) )
		{
			Cookie::set( 'ips_connect_redirect', 'true', 60 );
			
			/* User redirect */
			return ips_redirect( $redirect );
		}

		Cookie::destroy();
	}
	
	
	return ips_error_redirect( Cookie::get( 'ips-redir', 'index.html' ), array(
		'alert' => 'connect_error'
	), 'connect-' . $set_provider . '.log', true );
