<?php
	ignore_user_abort(true);
	define( 'IPS_CRON', true );
	set_time_limit(0);
	
	require_once( dirname( dirname( dirname( dirname( dirname(__FILE__) ) ) ) )  . '/php-load.php' );
	error_log( 'IPS_CRON_AUTOPOST: ' . date("Y-m-d H:i:s") );
	
	include( ABS_PATH . '/admin/admin-functions.php');
	include( ABS_PATH . '/admin/cron-functions.php');
	include( ABS_PATH . '/admin/libs/plugins/Autoips/class.plugin.Autoips.php');
	
	$c_time = time();
	
	if ( date("H", $c_time) > 6 && date("H", $c_time) < 23 )
	{
		error_log( 'IPS_CRON_AUTOPOST: ' . date("Y-m-d H:i:s") );
		
		$autpost = new Autoips();
		$autpost->postFacebookData();
	} 

?>