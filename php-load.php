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

	if ( !defined('IPS_INSTALLING') && !file_exists( dirname(__FILE__) . '/install/installed.lock' ) )
	{
		header('Location: '.substr( $_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], 'index.php' ) ).'/install/');
		die('0');
	}
	
	/**
	* DB, Paths
	*/	
	require_once( dirname( __FILE__ ) . '/config.php');
	
	/*
	* Debug
	*/
	if( IPS_DEBUG )
	{
		require( LIBS_PATH . '/Handlers/Handler.php' );
		require( CLASS_PATH . '/class.Benchmark.php' );
		IPS_Benchmark::getCI()->start();
		IPS_Benchmark::getCI()->sessionUser();
	}
	
	
	/*
	* Autoload
	*/	
	require( CLASS_PATH . '/class.IpsAutoloader.php');
	Ips_Autoloader::register();

	Session::start();
	
	/*
	* Include main functions
	*/
	require( ABS_PATH . '/functions.php' );

	
	/**
	* PDO
	*/
	try {
		$PD = new PD();
	} catch ( Exception $e ) {
		die( $e->getMessage() );
	};
	

	global $config;
			
	$config = array();
	
	$config['allowed_types'] = array( 'image', 'video', 'text', 'article', 'gallery', 'animation', 'ranking' );
	
	if( isset( $_SERVER["QUERY_STRING"] ) )
	{
		parse_str( $_SERVER["QUERY_STRING"], $config['query_string'] );
	}
	
	/** Config options */
	new Config();
	
	require_once( ABS_PATH . '/php-constants.php' );
	
	if( !defined('IPS_ADMIN_PANEL') )
	{
		if( !USER_ADMIN && Config::get('site_in_maintenance')  )
		{
			die( '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . Config::get('site_in_maintenance_text') );
		}
		
		if( !defined('IPS_AJAX') )
		{
			/**
			* In vines template main page in first file | before php-constants
			*/
			if( IPS_VERSION == 'vines' )
			{
				Vines::redirectMain();
			}

			/**
			* Site only for logged in users
			*/
			if( !USER_LOGGED && Config::getArray( 'user_guest_option', 'view_site' ) && !defined( 'IPS_CONNECT' ) )
			{
				if( strpos( $_SERVER['HTTP_USER_AGENT'], 'facebook' ) === false && !in_array( IPS_ACTION, array( 'login', 'register', 'post', 'connect' ) ) )
				{
					return ips_redirect( 'login/' );
				}
			}
		}
	}
	
	/**
	* Loanguage load
	*/
	$languages = new Translate();
	${IPS_LNG} = $languages->getTranslations();
	unset( $languages );
	
	define( 'USER_LOGIN', Session::get( 'user_name', __( 'anonymous_login' ) ) );
	
	/**
	* Chec if user was remembered
	*/
	if( !USER_LOGGED && Cookie::exists( 'ssid_autologin' ) )
	{
		Ips_Registry::get( 'Users' )->isRemember();
	}
	
	/**
	* Display side block setting
	*/
	if( IPS_VERSION == 'gag' || IPS_VERSION == 'bebzol' || IPS_VERSION == 'vines' )
	{
		Config::tmp( 'display_side_block', !in_array( IPS_ACTION, array( 'login', 'mem', 'register', 'contact', 'moderator', 'edit_profile', 'page', 'up' ) ) );
	}
	
	require_once( ABS_PATH . '/php-hooks.php' );
	
	do_action( 'load' );
	