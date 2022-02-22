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
	define( 'IPS_CRON', true );
	
	require_once( dirname( dirname( dirname( dirname( dirname(__FILE__) ) ) ) )  . '/php-load.php' );

	if( isset( $_GET['webm_id'] ) && !empty( $_GET['webm_id'] ) )
	{
		$row = PD::getInstance()->select( IPS__FILES, array( 'id' => $_GET['webm_id'] ), 1 );
		
		$video_file = str_replace( '.mp4', '', $row['upload_video'] );
		
		if( !file_exists( IPS_VIDEO_PATH . '/' . $video_file . '.webm' ) )
		{
			include_once( ABS_PATH . '/ips-functions-self.php' );
			ips_webm( IPS_VIDEO_PATH  . '/' . $row['upload_video'] );
		}
		
		die();
	}
	
	require_once( ABS_PATH . '/admin/admin-functions.php' );
	require_once( ABS_PATH . '/admin/import-functions.php' );
	require_once( ABS_PATH . '/functions-upload.php');
	
	ips_log( 'Uruchomiono CRON Vines: ' . date("Y-m-d H:i:s"), 'logs/vine_up.log' );
	
	if( isset( $_GET['id'] ) && !empty( $_GET['id'] ) )
	{
		ips_log( 'Uruchomiono CRON Vines: ' . date("Y-m-d H:i:s") . ' o ID ' . $_GET['id'], 'logs/vine_up.log' );
		
		$row = PD::getInstance()->select( 'plugin_import_vines_files', array( 'id' => $_GET['id'] ), 1 );
		
		if( empty( $row ) )
		{
			ips_log( 'Brak takiego pliku Vines: ' . date("Y-m-d H:i:s") . ' o ID ' . $_GET['id'], 'logs/vine_up_log.log' );
			die('Brak takiego pliku');
		}
		
		$import_vines = PD::getInstance()->select( 'plugin_import_vines', array( 'id' => $row['cron_id'] ), 1 );
		
		$data = unserialize( $row['cron_serialized'] );
			
		//PD::getInstance()->delete( 'plugin_import_vines_files', array( 'id' => $_GET['id'] ));
		
		if( !empty( $row ) && !empty( $row['cron_serialized'] ) )
		{
			
			$file_name = basename( strtolower( $data['img'] ) );
			
			if( file_exists( ABS_PATH . '/upload/import/'. $file_name ) )
			{
				$file_name = rand() . $file_name;
			}
			
			$options = array(
				'timeout' => 10, 
				'file' => ABS_PATH . '/upload/import/' . $file_name, 
				'refferer' => 'http://' . parse_url( $data['img'], PHP_URL_HOST ) 
			);
			
			curlIPS( $data['img'], $options );
			
			if( file_exists( ABS_PATH . '/upload/import/' . $file_name ) && is_image( ABS_PATH . '/upload/import/' . $file_name ) )
			{

				$upload = new Upload_Extended();
			
				try{
					
					$data['upload_video'] = $data['link'];
					
					Session::setChild( 'inc', 'import_add_to', true );
					
					$_POST['upload_url'] = ABS_PATH . '/upload/import/' . $file_name;
					$_POST['upload_video'] = $data['upload_video'];
					$_POST['upload_video_url'] = $data['upload_video'];
					$data['upload_type'] = 'video';
					$data['upload_subtype'] = 'mp4';
					$data['upload_tags'] = extractCommonWords( $import_vines['cron_tags'] );
					$data['top_line'] = $data['title'];
					$data['bottom_line'] = $data['title'];
					$data['site'] = $data['site'];
					
					$import_vines['cron_users'] = explode( '|', $import_vines['cron_users'] );
					
					$data['user_login'] = $import_vines['cron_users'][ rand(0, count( $import_vines['cron_users'] ) - 1) ];
					
					$data['upload_source'] = '';
					$data['import_source'] = $data['link'];
					$data['import_category'] = $import_vines['cron_category'];
					$data['import_add_to'] = $import_vines['cron_main_or_wait'];
					
					$file_id = addImportedFile( $data );
					
					if( $file_id )
					{
						PD::getInstance()->delete( 'plugin_import_vines_files', array( 'id' => $_GET['id'] ));
						
						//rename( ABS_PATH . '/_vines_extract/uploads/' . $data['unique_filename'] . '.jpg', ABS_PATH . '/_vines_extract/uploads/imported/' . $data['unique_filename'] . '.jpg' );
						rename( ABS_PATH . '/_vines_extract/uploads/' . $data['unique_filename'] . '.txt', ABS_PATH . '/_vines_extract/uploads/imported/' . $data['unique_filename'] . '.txt' );
						rename( ABS_PATH . '/_vines_extract/uploads/' . $data['unique_filename'] . '.mp4', ABS_PATH . '/_vines_extract/uploads/imported/' . $data['unique_filename'] . '.mp4' );
				
						return cronIPSCall( IPS_ADMIN_URL . '/plugin/Vines?webm_id=' . $file_id, 10 );
					}
					
					
				} catch (Exception $e) {
					ips_log( 'Błąd pliku Vines: ' . date("Y-m-d H:i:s") . ' o ID ' . $_GET['id'], 'logs/vine_up_log.log' ); 
					ips_log( $e, 'logs/vine_up_log.log' ); 
					var_dump($e);
					echo $e->getMessage();
					var_dump( $upload );
					var_dump( $_POST ); 
					exit;
					$row = PD::getInstance()->select( 'plugin_import_vines_files', array( 
							'id' => array( $_GET['id'], '>'
					)), 1, null, array( 'id' => 'ASC' ) );
					if( !empty( $row['id'] ) )
					{
						return cronIPSCall( IPS_ADMIN_URL . '/plugin/Vines?id=' . $row['id'], 0.01 );
					}
				}
				
				Session::clear( 'inc' );
				@unlink( ABS_PATH . '/upload/import/' . $file_name );
			}
			else
			{
				PD::getInstance()->delete( 'plugin_import_vines_files', array( 'id' => $_GET['id'] ));
				
				rename( ABS_PATH . '/_vines_extract/uploads/' . $data['unique_filename'] . '.txt', ABS_PATH . '/_vines_extract/uploads/failed/' . $data['unique_filename'] . '.txt' );
				rename( ABS_PATH . '/_vines_extract/uploads/' . $data['unique_filename'] . '.mp4', ABS_PATH . '/_vines_extract/uploads/failed/' . $data['unique_filename'] . '.mp4' );
			}
		}
		else
		{
			ips_log( 'Błąd pliku Vines: ' . date("Y-m-d H:i:s") . ' o ID ' . $_GET['id'] . ': cron_serialized', 'logs/vine_up_log.log' ); 
		}
	}
	else
	{
		require_once( ABS_PATH .  '/admin/libs/plugins/Vines/class.plugin.Vines.php' );
        $object = new Vines ();
		
		$rows = PD::getInstance()->select( 'plugin_import_vines' );

		foreach( $rows as $row )
		{
			$object->preparePost( $row['id'], true );
		}
		
	}
?>