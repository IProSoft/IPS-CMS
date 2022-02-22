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
	if( !USER_ADMIN && !USER_MOD)
	{
		ips_redirect( 'index.html', 'user_permissions');
	}
	
	$routes = App::routes( array(
		'get_type', 'sort_by', 'page'
	));
	
	if( is_int( $routes['sort_by'] ) )
	{
		$routes['page'] = $routes['sort_by'];
	}
	
	$routes['sort_by']  = preg_match( '/(date_add|votes_opinion|votes_count|comments|comment_opinion|comment_votes)/', $routes['sort_by'] ) ? $routes['sort_by'] : 'date_add';

	$routes['get_type']  = preg_match( '/(main|waiting|archive|comments)/', $routes['get_type'] ) ? $routes['get_type'] : 'main';
		
	$variables = array(
		'pagination' => '/moderator/'. $routes['get_type'] . '/' . $routes['sort_by'] . '/',
		'sorting' => $routes['sort_by'],
		'user_mod' => true
	);
	if( $routes['get_type'] != 'comments' )
	{
		if( $routes['get_type'] != 'archive' )
		{
			$variables['condition'] = array(
				'upload_status' => 'public',
				'upload_activ' => ( $routes['get_type'] == 'main' ? 1 : 0 )
			);
		}
		else
		{
			$variables['condition'] = array(
				'upload_status' => 'archive'
			);
		}
	}
	

	add_action('before_files_display', 'ips_template_helper', array( 'moderator_menu.html', array(
		'mod_action' => $routes['get_type']
	)), 10 );
	
	$display = new Core_Query();
	
	return $display->init( 'mod_' . ( $routes['get_type'] == 'comments' ? 'comments' : 'files' ), $variables );
	
?>