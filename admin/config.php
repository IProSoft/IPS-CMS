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
	define( 'IPS_ADMIN_PANEL', true ); 

	if ( !class_exists('PD') || !defined('DB_HOST') )
	{
		require_once( dirname( dirname(__FILE__) ) . '/php-load.php' );
	}

	if( !USER_ADMIN && strpos( $_SERVER['SCRIPT_NAME'], 'login.php' ) === false )
	{
		ips_redirect('admin/login.php');
	}

	if( USER_ADMIN )
	{
		Config::restoreConfig();
	}
	
	require_once ( IPS_ADMIN_PATH .'/libs/class.Updates.php' );
	require_once ( IPS_ADMIN_PATH .'/libs/class.PaginTool.php' );
	require_once( ABS_PATH . '/functions-upload.php' );
?>