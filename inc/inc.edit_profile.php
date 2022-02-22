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
	if( !defined('IPS_VERSION') ) die();

	add_filter( 'init_js_files', function( $array ){
		return add_static_file( $array, array(
			'js/user_profile.js',
			'js/date_picker_handler.js'
		)  );
	}, 10 );

	if( USER_LOGGED )
	{
		$user = getUserInfo( USER_ID );
		
		$users = new Users();
		
		if( isset( $_POST['submit'] ) )
		{
			if( $users->edit( USER_ID, $_POST ) != false )
			{
				ips_redirect( 'profile/' . $user['login'] );
			}
			else
			{	
				ips_redirect( 'edit_profile' );
			}
		}
		
		$user['avatar'] = ips_user_avatar( $user['avatar'], 'url' );
		
		$user['birth_date'] = date("Y-m-d", strtotime( $user['user_birth_date'] ) );
		
		User_Data::get( USER_ID );
		
		$allow_login = User_Data::get( USER_ID, 'allow_login' );
		
		$user['user_data'] = array(
			'username_facebook' => User_Data::get( USER_ID, 'username_facebook' ),
			'username_twitter' => User_Data::get( USER_ID, 'username_twitter' ),
			'about_me' => strip_tags( User_Data::get( USER_ID, 'about_me' ) ),
			'facebook_uid' => User_Data::get( USER_ID, 'facebook_uid' ),
			'twitter_uid' => User_Data::get( USER_ID, 'twitter_uid' ),
			'nk_uid' => User_Data::get( USER_ID, 'nk_uid' ),
			'post_facebook' => User_Data::get( USER_ID, 'post_facebook' ),
			'gender' => User_Data::get( USER_ID, 'gender' ),
			'post_facebook_message' => User_Data::get( USER_ID, 'post_facebook_message' ),
			'newsletter' => User_Data::get( USER_ID, 'newsletter' ),
			'default_language' => User_Data::get( USER_ID, 'default_language' ),
			
			'allow_login' => array(
				'facebook'	=> ( isset( $allow_login['facebook'] ) ? $allow_login['facebook'] : 0 ),
				'nk'		=> ( isset( $allow_login['nk'] ) ? $allow_login['nk'] : 0 ),
				'twitter'	=> ( isset( $allow_login['twitter'] ) ? $allow_login['twitter'] : 0 ),
			),
			
		);
		
		$user['default_languages'] = Translate::codes();
		
		$template = IPS_GET_ACTION == 'password' ? 'user_edit_profile_password.html' : 'user_edit_profile.html';

		return Templates::getInc()->getTpl( $template, $user );

	} 
	else 
	{
		ips_redirect( false, 'user_only_logged' );
	}
?>