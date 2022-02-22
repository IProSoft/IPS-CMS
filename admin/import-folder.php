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

	if( !defined('USER_ADMIN') ) 
	{
		require_once( 'config.php' );
	}

if( (bool)ini_get('safe_mode') == false )
{
	@set_time_limit(6000);
}	
if( isset($_GET['clear_session']) )
{
	Session::set( 'inc', array() );
}

include( IPS_ADMIN_PATH .'/import-functions.php' );


if( isset( $_POST['import']['import_directory'] ) )
{
	$import_directory = $_POST['import']['import_directory'];
	
	if( strpos( $import_directory, ",") !== false)
	{
		$import_directory	= explode( ',', $import_directory );
	}
	
	$default_name	= $_POST['import']['import_default_name'];
	$upload_tags    = $_POST['import']['upload_tags'];
	$count			= $_POST['import']['import_pages_limit'];
	$import_category= Session::getChild( 'inc', 'import_category', false );
	$authors		= Session::getChild( 'inc', 'authors' );


	$msg = importFolderImages( $import_directory, $default_name, $upload_tags, $count, $import_category, $authors, Session::getChild( 'inc', 'import_watermark_cut' ); );

	if( isset( $msg['error'] ) )
	{
		echo $msg['error'] . admin_msg( array(
			'success' => __s( 'import_clear_session', admin_url( 'import-folder', 'clear_session=start' ) )
		) );
	}
	elseif( isset( $msg['success'] ) )
	{
		echo implode( '', $msg['files'] );
		
		echo $msg['success'];
		
		echo admin_msg( array(
			'info' => __s( 'import_clear_session', admin_url( 'import-folder', 'clear_session=start' ) )
		) );
	}
	
}
	echo addImportForm( false, 'folder' );

?>