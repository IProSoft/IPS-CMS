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

class Gag
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function init()
	{
		App::$app['comments-width'] = 635;
		
		if ( Config::getArray( 'template_settings', 'gag_sub_menu' ) == 1 )
		{
			if ( in_array( IPS_ACTION, array(
				'main',
				'waiting',
				'share',
				'nk',
				'google',
				'random',
				'top' 
			) ) )
			{
				add_action( 'before_files_display', 'Gag::contentMenu' );
			}
		}
		
		if ( IPS_ACTION == 'profile' )
		{
			add_action( 'before_content', 'Gag::profileHeader', array(
				Sanitize::cleanSQL( $_GET['login'] ) 
			), 0 );
		}
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function profileStats( $row_user )
	{
		return Templates::getInc()->getTpl( 'user_profile_stats.html', $row_user );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function profileHeader( $user_login )
	{
		$row_user = getUserInfo( false, false, $user_login );
		
		return Templates::getInc()->getTpl( 'user_profile_header.html', array(
			'about_me' => User_Data::get( $row_user['id'], 'about_me' ),
			'user_avatar' => ips_user_avatar( $row_user['avatar'], 'url' ),
			'user_login' => $user_login 
		) );
	}

	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function contentMenu()
	{
		return '<div class="content-sub-menu">' . App::getMenu('gag_sub_menu') . ( Config::getArray( 'page_fast_options', 'widget' ) ? Widgets::fastButton() : '' ) . '</div>';
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function sideBlock()
	{
		return Templates::getInc()->getTpl( 'side_block.html');
	}
	
	
}
?>