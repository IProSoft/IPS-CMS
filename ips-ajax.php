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
	parse_str( $_GET['build_query'], $output );

	$_GET = array_merge( $_GET, $output );

	$routes = explode( '/', $_GET['build_query'] );
	
	$function_name	= isset( $_GET['function_name'] ) ? $_GET['function_name'] : false;
	
	if( !$function_name )
	{
		return false;
	}
	/**
	* Ex /ajax/function_name/1 : IPS_ACTION_GET_ID == 1
	* /ajax/function_name/another_name : IPS_ACTION_GET_ID == another_name
	*/
	
	if( $function_name == 'pinit' )
	{
		$function_name = 'pinit_' . $_GET['id'];
		
		require_once( CLASS_PATH . '/pinit/functions.php');
		
		if( function_exists( $function_name ) )
		{
			echo ips_json( $function_name() );
		}
		
		echo ips_json( array(
			'error' => 'Function ' . $function_name . ' is not callable'
		) );
	}
	
	require_once( dirname( __FILE__ ) . '/php-load.php');
	require_once( ABS_PATH . '/functions-ajax.php');
	
	if( function_exists( 'ajax_' . $function_name ) )
	{
		$function_name = 'ajax_' . $function_name;
		
		echo ips_json( $function_name( ) );
	}
?>