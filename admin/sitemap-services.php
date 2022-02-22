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
	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");

	echo admin_caption( 'sitemap_caption' );
	
	if( !isset( $_GET['sitemap_generate'] ) )
	{
		Session::set( 'sitemap_generate', array() );
		
		echo '
		
		<form action="" enctype="multipart/form-data" method="get">
			' . displayArrayOptions( array(
				'sitemap' => array(
					'current_value' => 1,
					'option_set_text' => '',
					'option_is_array' => array(
						'files' => array(
							'current_value' => 1,
							'option_set_text' => 'sitemap_set_files',
						),
						'user_profiles' => array(
							'current_value' => 1,
							'option_set_text' => 'sitemap_set_user_profiles'
						),
						'wait' => array(
							'current_value' => 1,
							'option_set_text' => 'sitemap_set_wait'
						),
						'main' => array(
							'current_value' => 1,
							'option_set_text' => 'sitemap_set_main'
						),
						'archive' => array(
							'current_value' => 1,
							'option_set_text' => 'sitemap_set_archive'
						),
						'categories' => array(
							'current_value' => 1,
							'option_set_text' => 'sitemap_set_categories'
						),
						'social' => array(
							'current_value' => 1,
							'option_set_text' => 'sitemap_set_social'
						)
					)
				)
			)) . '
			<input type="hidden" name="sitemap_generate" value="true" />
		<input type="submit" class="button" value="' . __('sitemap_generate') . '" />
		</form>';
	}
	else
	{
		
		if( $_GET['sitemap_generate'] == 'true' )
		{
			Session::set( 'sitemap_generate', $_GET['sitemap'] );
			
			ips_admin_redirect( 'sitemap', 'sitemap_generate=start&page=1' );
		}
		
		$sitemap_generate = Session::get( 'sitemap_generate', array() );
		
		if( array_sum( $sitemap_generate ) == 0 )
		{
			return ips_admin_redirect( 'sitemap', false, 'sitemap_error_empty' );
		}
		
		$sitemap = new Sitemap;
		
		if( !isset( $_GET['current'] ) )
		{
			$sitemap->emptyTmp();
			ips_admin_redirect( 'sitemap', 'sitemap_generate=generate&page=1&current=' . array_search( 1, $sitemap_generate ) );
		}
		
		echo admin_loader( '48' );
		
		$action = $_GET['current'];
		
		
		if( is_callable( array( $sitemap, 'map_' . $action ) ) )
		{
			$value = $sitemap->{'map_' . $action}( 'temp' );
		}
		
		unset( $sitemap_generate[$action] );
		
		Session::set( 'sitemap_generate', $sitemap_generate );
		
		$next = array_search( 1, $sitemap_generate );
		
		if( empty( $next ) )
		{
			if( !$sitemap->save('temp') )
			{
				ips_admin_redirect( 'sitemap', false, 'sitemap_error_save' );
			}
			ips_admin_redirect( 'sitemap', false, 'sitemap_success' );
		}
		
		echo ips_redirect_js( admin_url( 'sitemap', 'sitemap_generate=generate&current=' . $next ), 2 );
		die();
	}

?>