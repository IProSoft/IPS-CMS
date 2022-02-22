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

	date_default_timezone_set("Europe/Warsaw");
	
	define( 'IMG_UP', ( IPS_VERSION == 'pinestic' ? 'upload/pinit' : 'upload/images' ));
	/**
	* Adres strony w formacie: http://(www.|?)site.com/ 
	*/
	define( 'ABS_URL', ips_site_url() );
	
	define( 'IPS_ADMIN_URL', ABS_URL . 'admin' ); 
	define( 'IPS_ADMIN_PATH', ABS_PATH . '/admin' );
	
	$upload_url = Config::tmp( 'ips_upload_url' );
	
	define( 'IPS_DNS_URL', ( $upload_url ? $upload_url : substr( ABS_URL, 0, -1 ) ) );

	define( 'IMG_LINK', IPS_DNS_URL . '/' . IMG_UP );
	
	
	
	/**
	* Detect if user use Mobile Device 
	*/
	define( 'IPS_IS_MOBILE', isMobile() );
	
	/***
	* Ustalamy dane identyfikujące użytkownika.
	* @USER_LOGIN		: określia Login/Nazwę użytkownika
	* @USER_ID			: określia ID usera
	* @USER_LOGGED		: zalogowany user
	* @USER_ADMIN		: uprawnienia administracyjne
	* @USER_MOD			: uprawnienia moderatora
	*/
	define( 'USER_ID',		ips_check_user_id() );
	
	define( 'USER_LOGGED',	(bool)USER_ID );
	
	define( 'USER_ADMIN',	ips_check_user_role( 'admin' ) );
	
	define( 'USER_MOD',		ips_check_user_role( 'moderator' ) );

	/**
	* Ścieżka dla katalogu z szablonem
	**/
	define( 'ABS_TPL_PATH', ABS_PATH . '/templates/' . IPS_VERSION );

	/**
	* Zapisywanie kontrolera globalnej akcji wykonywanej w skrypcie.
	*/
	if( !defined('IPS_ACTION') )
	{
		$action = 'main';
		if( isset( $_GET['route'] ) && $_GET['route'] != 'index' && !empty( $_GET['route'] ) )
		{
			$action = strval( $_GET['route'] );
		}
		
		define( 'IPS_ACTION', $action );
	}

	if( !defined('IPS_GET_ACTION') )
	{
		$action = false;
		if( isset( $_GET['action'] ) && !empty( $_GET['action'] ) )
		{
			$action = strval( $_GET['action'] );
		}
		
		define( 'IPS_GET_ACTION', $action );
	}
	
	define( 'IPS_POST_PAGE', ( IPS_ACTION == 'file_page'  || IPS_ACTION == 'pin' ) );
	
		/**
	* Zapisywanie aktualnego numeru strony.
	*/
	if( !defined('IPS_ACTION_PAGE') )
	{
		$page = floatval( get_input( 'page' ) );
		
		define( 'IPS_ACTION_PAGE', ( is_float( $page ) && $page > 0 ? $page : 1 ) );
	}

	/**
	* Zapisywanie globalnego ID z tablicy $_GET
	*/
	if( !defined('IPS_ACTION_GET_ID') )
	{
		define( 'IPS_ACTION_GET_ID', ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ? (int)$_GET['id'] : false ) );
	}
	
	/**
	* Definiowanie stałych określającej cachowanie szablonów HTML i plików
	*/
	$cache_config = Config::getArray('system_cache');
	
	define( 'IPS_CACHE_PATH',		CACHE_PATH . '/ips_cache' );
	define( 'IPS_CACHE_IMG_PATH',	CACHE_PATH . '/img_cache' );
	define( 'IPS_CACHE_LIFETIME',	$cache_config['files_expiry'] );
	define( 'IPS_CACHE_EXT',		'.cache' );
	define( 'IPS_CACHE_DEBUG',		IPS_DEBUG );
	
	define( 'IPS_TPL_CACHE',		$cache_config['templates'] && $cache_config['templates_expiry'] > 0 && !USER_LOGGED ? true : false );
	define( 'IPS_FILE_CACHE',		$cache_config['files'] && $cache_config['files_expiry'] > 0 && !USER_MOD ? true : false  );
	
	unset( $cache_config );
	/**
	* Zapisywanie wersji layoutu do odwołań w skrypcie.
	* Archiwum i losowy wyświetlane są w normalnym layoucie.
	*/
	if( !defined('IPS_ACTION_LAYOUT') )
	{
		$layout = 'one';
		if( !in_array( IPS_ACTION, array( 'file_page', 'archive', 'random' ) ) && ( !isset( $_GET['action'] ) || $_GET['action'] != 'archive' ) )
		{
			$layout = Config::getArray( 'template_settings', 'layout');
		}
		define( 'IPS_ACTION_LAYOUT', $layout );
	}
	
	/**
	* Definiowanie linków z końcówką/bez końcówki .html
	*/
	define( 'IPS_LINK_FORMAT', ( Config::get('seo_links_format') == 0 ? '' : '.html' ) );
	define( 'IPS_LINK_FORMAT_PCT', Config::get('seo_links_format_pct') );
	
	
	/** System Database CONSTANTS **/
	
	if( !defined('DB_PREFIX') )
	{
		define( 'DB_PREFIX', '' );
	}
	
	define( 'IPS__FILES', ( IPS_VERSION == 'pinestic' ? 'pinit_pins' : 'upload_post' )  );

	/**
	* PinIt cache PATH Normal
	*/
	define( 'IPS_PINIT_C_BOARD',			'/pinit_cache/boards/' );
	define( 'IPS_PINIT_C_PIN',				'/pinit_cache/pins/' );
	define( 'IPS_PINIT_C_USER',				'/pinit_cache/user/' );
	define( 'IPS_PINIT_C_NOTIFY',			'/pinit_cache/notifications/' );
	
	/**
	* PinIt cache PATH Images
	*/

	define( 'IPS_PINIT_C_PIN_IMG',			'cache/ips_cache/pinit_cache/pins' );
	define( 'IPS_PINIT_C_PIN_IMG_URL',		IPS_DNS_URL	. '/' . IPS_PINIT_C_PIN_IMG );
	define( 'IPS_PINIT_C_USER_IMG',			'cache/ips_cache/pinit_cache/user' );
	define( 'IPS_PINIT_C_USER_IMG_URL',		IPS_DNS_URL	. '/' . IPS_PINIT_C_USER_IMG );
	
	/**
	* System image path's
	*/
	define(	'IMG_PATH',				ABS_PATH . '/' . IMG_UP );
	

	define( 'IMG_PATH_LARGE',		IMG_PATH . '/large' );
	define( 'IMG_PATH_MEDIUM',		IMG_PATH . '/medium' );
	define( 'IMG_PATH_THUMB',		IMG_PATH . '/thumb' );
	define( 'IMG_PATH_THUMB_SMALL',	IMG_PATH . '/thumb-small' );
	define( 'IMG_PATH_THUMB_MINI',	IMG_PATH . '/thumb-mini' );
	define( 'IMG_PATH_OG_THUMB',	IMG_PATH . '/og-thumb' );
	define( 'IMG_PATH_SQUARE',		IMG_PATH . '/square' );
	define( 'IMG_PATH_GIF',			IMG_PATH . '/gif' );
	define( 'IMG_PATH_MEDIA_POSTER',IMG_PATH . '/media_poster' );
	
	define( 'IMG_PATH_BOARD_CROP',	IMG_PATH . '/board/cropp' );
	
	define( 'IMG_PATH_BACKUP',		ABS_PATH . '/upload/img_backup' );
	
	/**
	* Ranking images
	*/
	define( 'IPS_RANKING_IMG',				IPS_DNS_URL	. '/upload/upload_ranking' );
	define( 'IPS_RANKING_IMG_PATH',			ABS_PATH	. '/upload/upload_ranking' );
	
	/**
	* Gallery images
	*/
	define( 'IPS_GELLERY_IMG',				IPS_DNS_URL . '/upload/upload_gallery' );
	define( 'IPS_GELLERY_IMG_PATH',			ABS_PATH	. '/upload/upload_gallery' );
	
	/**
	* Video files
	*/
	define( 'IPS_VIDEO_URL',				IPS_DNS_URL . '/upload/upload_video' );
	define( 'IPS_VIDEO_PATH',				ABS_PATH	. '/upload/upload_video' );
	
	/**
	* Temporary files folder
	*/
	define( 'IPS_TMP_URL',					ABS_URL . 'upload/tmp' );
	define( 'IPS_TMP_FILES',				ABS_PATH . '/upload/tmp' );
	
	/**
	* Current date constant
	*/
	define( 'IPS_CURRENT_DATE',				date("Y-m-d H:i:s") );
	
	/**
	* 
	*/
	define( 'AUTH_KEY_UPDATE',				'aHR0cDovL3VwZGF0ZS5pcHJvc29mdC5wbC8=');
	
	/**
	* 
	*/
	define( 'ABS_PLUGINS_PATH', ABS_PATH . '/inc/plugins' );
	