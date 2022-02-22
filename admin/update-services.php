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
	require_once( IPS_ADMIN_PATH .'/update-functions.php' );

	echo admin_caption( 'caption_update' );
	
	if ( !extension_loaded('zip') )
	{
		echo __('updates_requires_php_zip_library');
		die();
	}
	if( !in_array('curl', get_loaded_extensions() ) )
	{
		echo __('updates_requires_curl_library');
		die();
	}
	
	$response = true;
	
	if( !empty( $_POST ) && isset( $_POST['license_email'] ) && isset( $_POST['license_number'] ) )
	{
		$response = Updates::getUserHash( $_POST['license_email'], $_POST['license_number'] );
	}
	
	if( $response === true )
	{
		$response = Updates::validateLicense();
	}
	
	if( $response !== true )
	{
		echo updateForm( $response );
		die();
	}

	echo '
	<div style="margin-top: 10px; margin-bottom: 10px;">
		' . responsive_menu( array(
			'check' => array( 
				'alert' => 'updates_check_available'
			),
			'downloaded' => 'updates_downloaded',
			'installed' => 'updates_installed',
			'settings' => 'settings',
			'system' => 'updates_system_check',
		), admin_url( 'update', 'action=' ), false  ) . '
	</div>
	';
	
	$action = isset( $_GET['action'] ) ? $_GET['action'] : 'check';

	if( $action == 'update_disable' )
	{
		Config::update( 'updates_disabled', 'true' );
		ips_admin_redirect('/', false, __('updates_disable_success') );
	}
	
	if( $action == 'update_enable' )
	{
		Config::update( 'updates_disabled', 'false' );
		ips_admin_redirect( 'update', false, __('updates_enable_success') );
	}
	
	if( Config::get('updates_disabled') == 'true' )
	{
		ips_admin_redirect('/', false, __('updates_disable_alert') );
	}
	
	if( $action == 'update_license' )
	{
		Config::update( 'license_hash', '' );
		ips_admin_redirect('update');
	}
	elseif( $action == 'settings' )
	{
		echo Templates::getInc()->getTpl( '/__admin/update_settings.html' );
	}
	elseif( $action == 'check' )
	{
		/**
		* Wyszukujemy i wyświetlamy dostępne ale nie pobrane aktualizacje.
		*/
		if( isset( $_GET['refresh'] ) )
		{
			PD::getInstance()->delete( 'updates_table', array(
				'up_id' => (int)$_GET['refresh']
			), 1 );
		}
		
		Updates::clear();
		
		$available = Updates::get( 'available' );
		
		Config::update( 'update_alert_count', count( $available ) );
		
		if( !empty( $available ) )
		{
			$available = Updates::addCreatedDate( $available );
			
			echo admin_msg( array(
				'info' =>__('updates_available') 
				) );
		
			echo Templates::getInc()->getTpl( '/__admin/updates_available.html', array(
				'available' => $available
			));
		}
		else
		{
			echo admin_msg( array(
			'alert' => __('updates_available_empty')
			) );
		}
	}
	
	elseif( $action == 'downloaded' )
	{
		/**
		* Wyszukujemy i wyświetlamy już pobrane aktualizacje, które jeszcze nie zostały zainstalowane
		*/
		
		$downloaded = Updates::get( 'downloaded' );

		if( !empty( $downloaded ) )
		{
			$downloaded = Updates::addCreatedDate( $downloaded );
			
			echo admin_msg( array(
			'info' => __('updates_install_ready')
			) );

			
			echo Templates::getInc()->getTpl( '/__admin/updates_install.html', array(
				'downloaded' => $downloaded
			));
		}
		else
		{
			echo admin_msg( array(
			'alert' => __('updates_install_empty')
			) );
		}
	}
	
	elseif( $action == 'installed' )
	{
		/**
		* Wyszukujemy i wyświetlamy już pobrane i zainstalowane aktualizacje.
		*/
		$installed = Updates::get( 'installed' );

		if( !empty( $installed ) )
		{
			$installed = Updates::addCreatedDate( $installed );
			
			echo admin_msg( array(
				'info' => __('updates_installed')
				) );
	
			echo Templates::getInc()->getTpl( '/__admin/updates_installed.html', array(
				'installed' => $installed
			));
		}
		else
		{
			echo admin_msg( array(
			'alert' => __('updates_empty_list')
			) );
		}
	} 
	
	elseif( $action == 'clear' )
	{
		/*
		* Czyszczenie ustawień
		*/
		Updates::clear();
		ips_admin_redirect( 'update', false, __('updates_clear_data_success') );
	}
	elseif( $action == 'install' )
	{
		/*
		* Instalacja
		*/	
		if( !empty( $_POST['updates_install'] ) )
		{
			echo Updates::installUpdates( $_POST['updates_install'] );
			
			$downloaded = Updates::get( 'downloaded' );

			echo '<br /><a href="' . admin_url( 'update', 'action=' . ( empty( $downloaded ) ? 'installed' : 'downloaded' ) . '' ) . '" class="button">' . __('common_back') . '</a>';
		}
	}
	elseif( $action == 'download' )
	{
		$downloaded = Updates::download( $_POST['updates_available'] );
				
		ips_admin_redirect( 'update', 'action=downloaded', __s( 'updates_download_count', $downloaded['count'] ) . '<br />' . implode( '<br />', $downloaded['errors'] ) );
	}
	elseif(  $action == 'system' )
	{
		if( systemCheckDirectory( true ) )
		{
			$step = isset( $_GET['step'] ) && !empty( $_GET['step'] ) ? $_GET['step'] : 'info';
			$license_email = Config::get('license_email');
			$license_number = Config::get('license_number');
			
			echo '<div class="tabbed_area" style="padding:10px;">';

			switch( $step )
			{
				case 'info':
					if( empty( $license_email ) || empty( $license_number ) || isset( $_GET['wrong_data'] ) )
					{
						echo updateForm();
					}
					else
					{
						echo '<a href="' . admin_url( 'update', 'action=system&step=download' ) . '" class="button">'.__('updates_system_check_start').'</a>';
					}
					
				break;
				
				case 'download':
					
					$result = downloadPack();
					
					if( $result === true )
					{
						echo admin_msg( array(
							'info' => __('updates_package_stored_on_server')
						) );
					}	
					else
					{
						echo $result;
					}
				break;
					
				case 'check_database':
					
					echo checkSystemDB();
					
					echo admin_msg( array(
						'info' => __('updates_db_updated')
					) );
				break;
				
				case 'check_files':
					
					echo admin_msg( array(
						'info' => __('updates_system_check_success')
					) );
					
					echo checkSystemFiles();
					
				break;
			}
			echo '</div>';
		}
		else
		{
			echo admin_msg( array(
			'info' =>__('update_info_1') 
		) );

		}
	}
?>