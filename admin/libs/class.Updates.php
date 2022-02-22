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
	
class Updates 
{
	
	public static $update_status = array();
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function path()
	{
		$path = ABS_PATH . '/upload/update/not_installed';
	
		if( !file_exists( $path ) )
		{
			if ( !mkdir($path) )
			{
				die( __('updates_folder_create_error') );
			}
		}
		
		return ABS_PATH . '/upload/update/not_installed';
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function get( $type )
	{
		switch( $type )
		{
			case 'available':
				self::available();
			break;
			case 'count':
				return self::availableCount();
			break;
		}
		
		return PD::getInstance()->select( 'updates_table', array( 
			'up_status' => $type
		), 0, '*', array( 'up_created' => 'ASC' ) );
	}
		/**
	 * Pobieranie listy nowych aktualizacji.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function availableCount()
	{
		
		$date = PD::getInstance()->select( 'updates_table', array(
			'up_status' => array( 'available', 'NOT IN' )
		), 1, 'up_created', array( 
			'up_created' => 'DESC'
		));
		
		if( !isset( $date['up_created'] ) )
		{
			$date['up_created'] = Config::get('install_date');
		}
		else
		{
			$date['up_created'] = date( "Y-m-d H:i:s", $date['up_created'] );
		}
		
		$available = ips_api_post( 'updates/count' );

		if ( is_array( $available ) && !empty( $date['up_created'] ) ) 
		{ 
			$count = 0;
			
			foreach( $available as $key => $value )
			{
				if( $value >= $date['up_created'] )
				{
					$count++;
				}
			}

			return $count;
		}
		/*
		* Brak dostepnych do instalacji
		*/
		return 0;
		
	}
	/**
	 * Pobieranie listy nowych aktualizacji.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function available()
	{
		
		$date = PD::getInstance()->select( 'updates_table', false, 1, 'up_created', array( 'up_created' => 'DESC' ));
		
		if( !isset( $date['up_created'] ) )
		{
			$date['up_created'] = strtotime( Config::get('install_date') );
		}

		$available = ips_api_post( 'updates/all', array( 
			'user_hash'   => Config::get('license_hash')
		) );

		/*
		* Brak dostepnych do instalacji
		*/
		if( !isset( $available['updates'] ) )
		{
			return array();
		}
		
		foreach( $available['updates'] as $key => $update )
		{
			/**
			* Dodawanie tylko aktualizacji nowszych niż czas instalacji
			*/
			
			if( isset( $update['up_hash'] ) && isset( $update['up_created'] ) && $update['up_created'] >= $date['up_created'] )
			{
				$exists = PD::getInstance()->cnt('updates_table', array( 
					'up_hash' => $update['up_hash']
				));
				if( $exists == 0 )
				{
					PD::getInstance()->insert( 'updates_table', $update );
				}
			}
		}
		
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function clear()
	{
		deleteFilesFolder( self::path() );
		PD::getInstance()->delete( 'updates_table', array(
			'up_status' => array( 'installed', 'NOT IN' )
		)) ;
	}
	
	/**
	 * Pobieranie aktualizacji i rozpakowywanie na serwerze do wybranego folderu
	 *
	 * @param 
	 * 
	 * @return 
	 */
	function download( $available )
	{
		$count = 0;
		if( is_array( $available ) )
		{
			$errors = array();
			foreach( $available as $item => $package )
			{
				$download = ips_api_post( 'updates/get', array( 
					'user_hash'   =>  Config::get('license_hash'),
					'up_hash'     => $package['up_hash']
				));
				
				$file_dirname = Updates::path() . '/' . $package['up_directory'] ;
				
				if( !empty( $download ) && !is_array( $download ))
				{
					file_put_contents( $file_dirname . '.zip',  $download );
					
					$zip = new ZipArchive;
					if ( $zip->open( $file_dirname . '.zip' ) === true && @mkdir( $file_dirname ) )
					{
						$zip->extractTo( $file_dirname);
						$zip->close();
						changeUpdateStatus( $package['up_hash'], 'downloaded' );
						$count++;
					}
					else
					{
						$errors[] = __('updates_zip_create_error');
					}
				}
				else
				{
					$errors[] = __('updates_zip_download_error') . ' ' . $package['up_directory'];
					ips_log( $download );
				}
			}
		}
		return array( 
			'count' => $count,
			'errors' => $errors
		);
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function addCreatedDate( $updates )
	{
		foreach( $updates as $key => $update )
		{	
			$updates[ $key ]['up_created_time'] = date("Y-m-d H:i:s", $update['up_created'] );
		}
		
		return $updates;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getUserHash( $license_email, $license_number ){

		$response = ips_api_post( 'user/hash', array( 
			'user_domain'  => ABS_URL,
			'user_email' => trim( $license_email ), 
			'user_license' => trim( $license_number )
		) );
		
		if( isset( $response['user_hash'] ) )
		{
				Config::update( 'license_email', $license_email );
				Config::update( 'license_number', $license_number );
				Config::update( 'license_hash', $response['user_hash'] );
				Config::restoreConfig();
				
			return true;
		}
		
		return $response;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function validateLicense( $action = 'all' ){
	
		if( strlen( Config::get('license_hash') ) < 20 )
		{
			return false;
		}
		
		if( $action == 'only_hash')
		{
			return true;
		}
		
		$valid_user_hash = ips_api_post( 'user/validate', array( 
			'user_hash' => Config::get('license_hash'),
		) );
		
		if( isset( $valid_user_hash['user_hash'] ) )
		{
			return true;
		}
		
		if( isset( $valid_user_hash['error'] ) )
		{
			return $valid_user_hash;
		}
		
		return false;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function status( $phrase, $status, $real_path = '' )
	{
		self::$update_status[] = array(
			'status' => $status,
			'msg' => __s( $phrase, $real_path )
		);
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function installUpdates( $updates_install )
	{
		if( is_array( $updates_install ) )
		{

			self::$update_status = array();
			foreach( $updates_install as $item => $package )
			{
				if( isset( $package['up_hash'] ) )
				{		
					$path = Updates::path() . '/' . $package['up_directory'];
					
					if( file_exists( $path ) )
					{
						$objects = new RecursiveIteratorIterator(
									   new RecursiveDirectoryIterator($path), 
									   RecursiveIteratorIterator::SELF_FIRST );
				
						$sorted = array();
						$pop = array();
						
						foreach( $objects as $name => $object )
						{
							if( basename( $name ) != '.' && basename( $name ) != '..')
							{
								if( $object->getFilename() != 'ips-include.php' )
								{
									$sorted[] = $object;
								}
								else
								{
									$pop[] = $object;
								}	
							}
						}
						
						ksort( $sorted );
						
						$sorted = $pop + $sorted;
						
						foreach( $sorted as $name => $object )
						{
							
							$real_path = realpathDir( $path, $object->getPathname() );
							
							if( $object->isDir() && !file_exists( ABS_PATH . $real_path ) )
							{
								/* Nowy folder */ 
								if ( @mkdir( ABS_PATH . '/' . $real_path ) )
								{
									self::status( 'updates_valid_folder_create', true, $real_path );
								}
								else
								{
									self::status( 'updates_error_folder_create', false, $real_path );
								}
							}
							elseif( $object->isFile() )
							{
								if( $object->getFilename() == 'ips-include.php' )
								{
									
									try{
										$include_status = include( $object->getPathname() );

										if( !empty( $include_status ) && is_array( $include_status ) )
										{
											foreach( $include_status as $key => $status )
											{
												self::status( $status, false );
											}
										}
										
										self::status( 'updates_valid_include' );
										
									} catch ( Exception $e ) {
										self::status( 'updates_error_include', false );
									}
									
								}
								elseif( $object->getFilename() == 'ips-language.php' )
								{
									$data = include( $object->getPathname() );
									
									updateLanguage( $data );
									
									self::status( 'updates_added_additional_translations', true, count( $data ) );
								}
								elseif( $object->getFilename() == 'ips-options.php' )
								{
									$data = include( $object->getPathname() );
									
									updateOptions( $data );
									
									self::status( 'updates_added_configuration_options', true, count( $data ) );
								}
								elseif( $object->getFilename() != 'info.txt' )
								{
									try{
										
										File::copyFile( $object->getPathname(), ABS_PATH . $real_path );
										
										self::status( 'updates_updated_file', true, str_replace( $path, '', $object->getPathname() ) );
										
									} catch ( Exception $e ) {
										
										self::status( 'updates_error_write_permissions', false, str_replace( $path, '', $object->getPathname() ) );
										
									}

								}
							}
						}
						
						changeUpdateStatus( $package['up_hash'], 'installed' );
						
						deleteFilesFolder( $path, true );
						
						@unlink( $path . '.zip' );
						
						if( file_exists( $path ) )
						{
							self::status( 'updates_error_delete_files', false, $package['up_directory'] );
						}
						
					}
					else
					{
						self::status( 'updates_error_directory_not_found', false, $package['up_directory'] );
					}
				}
			}
		}
		else
		{
			self::status( 'error_during_installation', false, $real_path );
		}
		
		return updateStatusMessages( self::$update_status );
	}
}
?>