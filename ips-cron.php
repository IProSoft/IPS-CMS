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
	/**
	* Kontunuacja po zakończeniu żądania
	*/
	ignore_user_abort(true);

define( 'IPS_CRON', true );
include( dirname( __FILE__ ) . '/php-load.php');
include( IPS_ADMIN_PATH .'/admin-functions.php');
include( IPS_ADMIN_PATH .'/cron-functions.php');

if( !isset($_GET['cron']) || $_GET['cron'] != md5(AUTH_KEY) )
{
	die();
}
$crons = ips_cron_array();

if ( $crons === false ) die();

$keys = array_keys( $crons );
$current_time = time();

if ( !isset($_GET['key']) && isset($keys[0]) && $keys[0] > $current_time )
	die();
/**
 * Umozliwiamy wywołanie tylko jednego
 * zadania prosto z panelu Admina
 */
if( isset($_GET['key']) && isset($_GET['timestamp']) )
{
	if( isset( $crons[$_GET['timestamp']][$_GET['key']] ) )
	{
		$crons = array( $_GET['timestamp'] => array( 
			$_GET['key'] => $crons[$_GET['timestamp']][$_GET['key']]
		));
	}
}
/**
* Call system cron 
*/
ips_cron_system();

$available_func = ips_cron_available_func();
foreach ( $crons as $timestamp => $functions ) 
{
	
	if ( !isset($_GET['key']) && $timestamp > $current_time ) 
		break;

	foreach ( $functions as $func )
	{
		
		$schedule = $func['schedule'];
		if ( $schedule != false )
		{
			$func['args']['last-activity'] = time();
			$new_args = array( $timestamp, $schedule, $func['function-name'], $func['args'] );
			call_user_func_array( 'ips_cron_change_timestamp', $new_args );
		}   

		ips_cron_delete( $timestamp, $func['id'] );

		if( function_exists($available_func[$func['function-name']]['call-function']) )
		{
			call_user_func( $available_func[$func['function-name']]['call-function'], $func['args'], $func['function-name'] );
		}
		elseif( function_exists( $func['function-name'] ) )
		{
			call_user_func( $func['function-name'], $func['args'], $func['function-name'] );
		}
		if( isset($_GET['key']) && USER_ADMIN)
		{
			ips_admin_redirect('cron', false, 'Zadanie ' . $available_func[$func['function-name']]['text'] . ' zostało wykonane' );
		}
	}
}

?>