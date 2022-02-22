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
	session_start();
	require_once( dirname(__FILE__) . '/config.php');
	require_once( IPS_ADMIN_PATH .'/admin-functions.php');
	require_once( IPS_ADMIN_PATH .'/update-functions.php' );
	
	$messages = array();
	
	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");
	
	if( isset( $_GET['delete_file'] ) )
	{
		deleteFileBySetting( $_GET['delete_file'] );
	}
	
	if( isset( $_GET['delete_file_path'] ) )
	{
		deleteFileByPath( $_GET['delete_file_path'] );
	}

	if( isset( $_GET['template_layout'] ) )
	{
		saveLayout( $_GET['template_layout'], true );
		
		return ips_admin_redirect( 'template', 'action=settings');
	}
	
	if( isset( $_GET['template_layout_thumbs'] ) )
	{
		saveThumbsCount( $_GET['template_layout_thumbs'] );
		
		return ips_admin_redirect( 'template', 'action=settings');
	}
	
	if( isset( $_GET['ips_version'] ) )
	{
		changeIpsVersion( $_GET['ips_version'] );
		
		return ips_admin_redirect( 'template', 'action=version');
	}
	
	
	
	/** Settings save from forms **/
	
	if( !empty( $_POST ) )
	{
		setEmptyMultiple();
		
		if( isset( $_POST['apps_facebook_app'] ) )
		{
			if( !in_array( 'email', $_POST['apps_facebook_app']['previliges'] ) )
			{
				$_POST['apps_facebook_app']['previliges'][] = 'email';
			}
			Config::remove( 'cache_data_valid_facebook_config' );
		}
		
		if( isset( $_POST['apps_fanpage_auto'] ) )
		{
			include_once ( IPS_ADMIN_PATH .'/fanpage-functions.php' );
			$errors = fanpageValidateSettings();
			
			if( !empty( $errors ) )
			{
				unset( $_POST['apps_fanpage_auto'] );
				$messages = array_merge( $messages, $errors );
			}
		}
		
		if( isset( $_POST['apps_google_maps_customize']['customized_styles'] ) )
		{
			$code = trim( $_POST['apps_google_maps_customize']['customized_styles'] );
			
			if ( Sanitize::validatePHP( $code, 'url' ) && strpos( $code, 'snazzymaps.com' ) !== false )
			{
				$code = trim( get_snazzy_maps( $code ) );
				if( empty( $code ) )
				{
					$messages[] = __('apps_google_maps_snazzy_error');
				}
			}
			
			$code = str_replace("'", '"', $code );
			
			if( !is_string( $code ) || !is_array( json_decode( $code, true ) ) )
			{
				unset( $_POST['apps_google_maps_customize']['customized_styles'] );
				$messages[] = __('apps_google_maps_code_error');
			}
			else
			{
				$_POST['apps_google_maps_customize']['customized_styles'] = $code;
			}
		}
	
		if( isset( $_POST['add_hook'] ) )
		{
			try{
				
				$hooks->create_action( $_POST['add_hook'] );
				
				return ips_admin_redirect( 'hooks', 'action=view', __s( 'hooks_block_saved', strtolower( __( 'hook_' . $_POST['add_hook']['hook'] ) ) ) );
			
			}catch( Exception $e ){
				
				return ips_admin_redirect( 'hooks', 'action=add', $e->getMessage() );
			}
		}
		
		if( isset( $_POST['pagin_css'] ) && $_POST['pagin_css'] == 'infinity' )
		{
			Config::update( 'widget_navigation_bottom,widget_navigation_bottom_box', 0 );
		}

		
		if( isset( $_POST['mem_generator'] ) )
		{
			activMenuItem( 'main_menu', 'mem', $_POST['mem_generator'] );
		}
		
		if( isset( $_POST['template_settings'] ) && isset( $_POST['template_settings']['gag_sub_menu'] ) )
		{
			if( $_POST['template_settings']['gag_sub_menu'] == 1 )
			{
				PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'top' ), 1, 0) WHERE menu_id = 'main_menu'");
				PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'main', 'share', 'up', 'waiting' ), 1, 0) WHERE menu_id = 'gag_sub_menu'");
			}
			else
			{
				PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'main', 'share', 'up', 'top', 'waiting' ), 1, 0) WHERE menu_id = 'main_menu'");
			}
		}
		
		if( isset( $_POST['categories_option'] ) )
		{
			activMenuItem( 'main_menu', 'categories', $_POST['categories_option'] );
			$_POST['widget_category_panel'] = 0;
		}
		
		if( isset( $_POST['social_plugins']['google_page'] ) || isset( $_POST['social_plugins']['google'] ) )
		{
			activMenuItem( 'main_menu', 'google', ( $_POST['social_plugins']['google_page'] == 1 || $_POST['social_plugins']['google'] == 1 ? 1 : 0 ) );
		}
		
		if( isset( $_POST['social_plugins']['nk'] ) || isset( $_POST['social_plugins']['nk_page'] ) )
		{
			activMenuItem( 'main_menu', 'nk', ( $_POST['social_plugins']['nk'] == 1 || $_POST['social_plugins']['nk_page'] == 1 ? 1 : 0 ) );
		}
		
		$validate_options = Ips_Registry::get( 'Validate_Admin' )->validate( $_POST );
		
		if( !empty( $validate_options ) )
		{
			$messages = array_merge( $messages, $validate_options );
		}
		
		
		if( !empty( $_POST ) )
		{
			$messages = updateSystemOptions( $_POST, $messages );
			$messages[] = __('settings_saved');
		}
	}
	
	/*
	* Zapisywanie usług Premium
	*/
	if( isset( $_POST['add_premium'] ) && !empty( $_POST['add_premium'] ) )
	{
		if( !empty( $_POST['add_premium_time'] ) )
		{
			smsPremiumUsers( $_POST['add_premium'], $_POST['add_premium_time'] );
			$messages[] = __('settings_premium_added_to') . ' ' . $_POST['add_premium'];
		}
		else
		{
			$messages[] = __('settings_enter_number_of_days');
		}	
	}
	

	$upload_file = array(
		'upload_signature' => array(
			'config_name' => array(
				'upload_demot_signature' => 'file'
			),
			'path' => ABS_PATH . '/upload/system/watermark',
		),
		'plugin_sticked_file' => array(
			'config_name' => array(
				'plugin_sticked' => 'file'
			),
			'basename' => 'plugin_sticked_file',
			'path' => ABS_PATH . '/upload/system/watermark',
		),
		'watermark_transparent' => array(
			'config_name' => array(
				'watermark_transparent' => 'file'
			),
			'path' => ABS_PATH . '/upload/system/watermark',
		),
		'upload_text_bg_image' => array(
			'basename' => 'basename',
			'path' => ABS_PATH . '/upload/system/upload_text_bg',
		),
		'watermark_file' => array(
			'config_name' => array(
				'watermark_options' => 'file'
			),
			'path' => ABS_PATH . '/upload/system/watermark',
		),
		'logo' => array(
			'path' => ABS_PATH . '/images',
			'basename' => 'logo-' . IPS_VERSION,
			'message' => __s('settings_file_changed', 'logo')
		),
		'logo_small' => array(
			'path' => ABS_PATH . '/images',
			'basename' => 'logo-' . IPS_VERSION . '-small',
			'message' => __s('settings_file_changed', 'logo')
		),
		'favicon' => array(
			'path' => ABS_PATH ,
			'basename' => 'favicon',
			'allowed-extensions' => array( 'ico' ),
			'extension' => 'ico',
			'message' => __s('settings_file_changed', 'favicon')
		),
		/**
		* Zapisywanie obrazu tła MP$.
		*/
		'mp4_cover_file_upload' => array(
			'config_name' => array(
				'upload_mp4_options' => 'cover_file'
			),
			'path' => ABS_PATH . '/images',
			'basename' => 'mp4_cover'
		)
	);
	
	foreach( $upload_file as $input_name => $upload )
	{
		if( !empty( $_FILES[ $input_name ]["tmp_name"] ) )
		{
			
			$file_name = $input_name;
			
			if( isset( $upload['basename'] ) )
			{
				$file_name = $upload['basename'];
				if( $upload['basename'] == 'basename' )
				{
					$file_name = basename( $_FILES[ $input_name ]["name"] );
				}
			}
			
			$file_name = uploadAdminImage( str_random( 4 ) . '_' . $file_name, array(
				'max_width' => 1200,
				'extension' => ( isset( $upload['extension'] ) ? $upload['extension'] : 'png' ),
				'allowed-extensions' => ( isset( $upload['allowed-extensions'] ) ? $upload['allowed-extensions'] : false )
			), $upload['path'], $input_name );
			
		
			if( !empty( $file_name ) )
			{
				if( isset( $upload['config_name'] ) )
				{
					if( is_array( $upload['config_name'] ) )
					{
						$config_key = current( array_keys( $upload['config_name'] ) );
						
						$config_value =  Config::noCache( $config_key );
						
						if( !is_array( $config_value ) )
						{
							$config_value = array();
						}

						Config::update( $config_key, array(
							current( $upload['config_name'] ) => $file_name
						), true, 'system_settings' );
						
					}
					else
					{
						Config::update( $upload['config_name'], $file_name, true );
					}
				}
				if( isset( $upload['message'] ) )
				{
					$messages[] = $upload['message'];
				}
			}
			else
			{
				$messages[] = __('settings_incorrect_extension');
			}
		}
	}
	
		
	$redir = false;

	if( !empty( $messages ) )
	{
		ips_message( array(
			'normal' =>  implode( '<br />', $messages )
		) );
	}
	if( Session::has( 'admin_redirect' ) )
	{
		$redir = Session::get( 'admin_redirect' );
	}
	elseif( isset($_POST['sms_services_form']) )
	{
		$redir = admin_url( 'premium' );
	}
	
	ips_redirect( $redir );
?>