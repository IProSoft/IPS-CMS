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
	
	require_once( dirname( dirname( dirname( dirname(__FILE__) ) ) )  . '/config.php' );
	require_once( ABS_PATH . '/admin/admin-functions.php' );
	require_once( ABS_PATH . '/admin/import-functions.php' );
	require_once( ABS_PATH . '/functions-upload.php');
	
	error_log( 'Uruchomiono CRON IMPORTER: ' . date("Y-m-d H:i:s") );
	
	if( isset( $_GET['id'] ) )
	{
		$row = PD::getInstance()->select( 'plugin_cron_import_files', array( 'id' => $_GET['id'] ), 1 );
		
		if( !empty( $row ) )
		{
			$cron_import = PD::getInstance()->select( 'plugin_cron_import', array( 'id' => $row['cron_id'] ), 1 );
			
			$data = unserialize( $row['cron_serialized'] );
				
			PD::getInstance()->delete( 'plugin_cron_import_files', array( 'id' => $_GET['id'] ));
			
			if( !empty( $row['cron_serialized'] ) )
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
					$mime = getimagesize( ABS_PATH . '/upload/import/' . $file_name );
					
					if( $data['size-checked'] == false )
					{
						if( $mime[0] < 200 || $mime[1] < 200 )
						{
							PD::getInstance()->insert( "upload_imported", array( 
								'title' => $data['title'], 
								'link' => $data['img'], 
								'source_url' => htmlentities( $data['site']
							) ) );
							
							$row = PD::getInstance()->select( 'plugin_cron_import_files', array( 
								'id' => array( $_GET['id'], '>'
							)), 1, null, array( 'id' => 'ASC' ) );
							
							if( !empty( $row['id'] ) )
							{
								return cronIPSCall( IPS_ADMIN_URL . '/plugin/Importer?id=' . $row['id'], 0.01 );
							}
						}
					}
					
					if( $cron_import['cron_watermark_remove'] == 1 )
					{
						cut_watermark( ABS_PATH . '/upload/import/' . $file_name, $mime['mime'], $cron_import['cron_watermark_remove_cut_height'], $cron_import['cron_watermark_remove_cut_direction'] );
					} 
					
					$upload = new Upload_Extended();
				
					try{
						
						$_POST['upload_url'] = ABS_PATH . '/upload/import/' . $file_name;
						$data['upload_type'] = 'image';
						$data['upload_tags'] = extractCommonWords( $cron_import['cron_tags'] );
						$data['top_line'] = $data['title'];
						$data['bottom_line'] = $data['title'];
						$data['site'] = $data['site'];
						if( $data['upload_type'] == 'video')
						{
							$data['upload_video'] = $data['link'];
						}

						$cron_import['cron_users'] = explode( '|', $cron_import['cron_users'] );
						
						$data['user_login'] = $cron_import['cron_users'][ rand(0, count( $cron_import['cron_users'] ) - 1) ];
						$data['upload_source'] = '';
						$data['import_source'] = $data['img'];
						$data['import_category'] = $cron_import['cron_category'];
						$data['import_add_to'] = $cron_import['cron_main_or_wait'];
						
						addImportedFile( $data );

					} catch (Exception $e) {
						/* 
						echo $e->getMessage();exit;
						var_dump( $upload );
						var_dump( $file );
						var_dump( $_POST ); 
						*/
						$row = PD::getInstance()->select( 'plugin_cron_import_files', array( 
								'id' => array( $_GET['id'], '>'
							)), 1, null, array( 'id' => 'ASC' ) );
						if( !empty( $row['id'] ) )
						{
							return cronIPSCall( IPS_ADMIN_URL . '/plugin/Importer?id=' . $row['id'], 0.01 );
						}
					}
					@unlink( ABS_PATH . '/upload/import/' . $file_name );
				}
			}
		}
	}
	else
	{
		require_once( ABS_PATH .  '/admin/libs/plugins/Importer/class.plugin.Importer.php' );
        $object = new Importer ();
		
		$rows = PD::getInstance()->select( 'plugin_cron_import' );

		foreach( $rows as $row )
		{
			$object->preparePost( $row['id'], true );
		}
		
	}
?>