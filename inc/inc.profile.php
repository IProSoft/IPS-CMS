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
	/** ALL versions without pinestic */
	if( !defined('IPS_VERSION') ) die();
	
	$login = Sanitize::cleanSQL( $_GET['login'] );
		
	if( empty( $login ) )
	{	
		if( USER_LOGGED )
		{
			$login = USER_LOGIN;
		}
		else
		{
			ips_redirect( 'index.html', 'user_error_not_exists' );
		}
	}

	add_filter( 'init_js_files', function( $array ){
		return add_static_file( $array, array(
			'js/user_profile.js'
		)  );
	}, 10 );
	
	global $row_user;
	
	$row_user = getUserInfo( false, false, $login );
	
	if( !empty( $row_user ) )
	{
		$users = new Users();
		
		$row_user_count = $users->getStats( $row_user['id'] );
		
		$row_user = array_merge( $row_user, $row_user_count );

		$row_user['avatar'] = ips_user_avatar( $row_user['avatar'], 'url' );	
			
		$row_user['user_last_visit'] = User_Data::get( $row_user['id'], 'user_last_visit' );
		$row_user['user_last_visit'] = formatDate( ( $row_user['user_last_visit'] ? $row_user['user_last_visit'] : $row_user['date_add'] ) );	
		
		$row_user['date_add'] = formatDate( $row_user['date_add'] );
		$row_user['about_me'] = User_Data::get( $row_user['id'], 'about_me' );
		
		if( empty( $row_user['about_me'] ) && USER_LOGIN == $row_user['login'] )
		{
			$row_user['about_me'] = __('user_profile_add_about_me');
		}
		
		$row_user['moderator'] = ( USER_MOD ? Operations::banUser( $row_user['id'], $row_user['user_banned'] ) : '' );
		$row_user['users_follow_user'] = PD::getInstance()->cnt( 'users_follow_user', array( 'user_followed_id' => $row_user['id'] ));


		$user = new User_Files( $login );
		$row_user['users_files_action'] = $user->action;
		
		$row_user['users_files_list'] = Ips_Registry::get('Core_Query')->init( 'user_files' );
		
		if( IPS_VERSION == 'gag' && Config::getArray('template_settings', 'side_stats' ) )
		{
			add_action('at_side_block', 'Gag::profileStats', array( $row_user ), null, 0 );
		}
		
		return Templates::getInc()->getTpl( 'user_profile.html', $row_user );
	}
	else
	{
		ips_redirect('index.html', 'user_error_not_exists');
	}
	
?>