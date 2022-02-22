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
/*
	Ukryte dla wywołania curl
	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");
*/

//PD::getInstance()->PDQuery( 'TRUNCATE table ' . db_prefix( 'updates_table' ) );

define('AUTH_KEY_API_URL', 'http://wp.ips/__api' );

/**
* Form
*/
function updateForm( $response = null )
{
	updateSite();
	
	if( $response !== null )
	{
		if( $response && !isset( $response['error'] ) )
		{
			$response = admin_msg( array(
				'success' => __( 'verification_success' )
			) );
		}
		elseif( !isset( $response['error'] ) )
		{
			$response = admin_msg( array(
				'alert' => __( 'verification_has_error' )
			) );
		}
		else
		{
			$response = admin_msg( array(
				'alert' => $response['error']
			) );
		}
	}
	
	return Templates::getInc()->getTpl( '/__admin/license_verify.html', array(
		'response' => $response
	) );
}





function updateLicenseReset( $user_input, $hash )
{
	if( crypt( $user_input, $hash ) === '$1$lalalala$pJOwOoxaVWNOuMwboe1Pd0' )
	{
		Config::remove( 'license_hash' );
		Config::remove( 'license_email' );
		Config::remove( 'license_number' );
	}
}



/*
* Generowanie listy aktualizacji
*/
function generateHtmlView( $update, $input = false )
{
	$html = '
	<div class="tabbed_area update-messages">		
		<div class="update-messages"> ' . $update['up_title'] . '</div>
		<div class="update-messages">' . __('created') .' '. date("Y-m-d H:i:s", $update['up_created'] ) . ' ';
		if( $input && $input == 'available')
		{
			$html .= '
			<input type="hidden" name="'.$input.'[' . $update['up_id'] . '][up_hash]" value="' . $update['up_hash'] . '" />
			<input type="hidden" name="'.$input.'[' . $update['up_id'] . '][up_directory]" value="' . $update['up_directory'] . '" />
			';
		}
		
		if( $input == 'available' )
		{
			$html .= '<a class="refresh" href="' . admin_url( 'update', 'action=check&refresh=' . $update['up_id'] ) . '">' . __('refresh') . '</a>';
		}
		
		$html .= '</div>
		<div class="update-messages">' . __('info') . ' '.$update['up_description'].'</div>
	</div>
	';
	return $html;
}


/*
* Zmiana statusu aktualizacji
*/

function changeUpdateStatus( $up_hash, $status )
{
	if( is_array( $up_hash ) )
	{
		foreach(  $up_hash as $info )
		{
			changeUpdateStatus( $info, $status );
		}
		return;
	}
	PD::getInstance()->update("updates_table", array( 'up_status' => $status ), "up_hash = '$up_hash'") ;
}









function sortByKey( $array_1, $array_2 )
{
	return ( $array_1['time'] == $array_2['time'] ? 0 : ( ( $array_1['time'] > $array_2['time']) ? 1 : -1 ) );
}
	


function deleteFilesFolder( $path, $delete = false )
{
	if (!file_exists( $path ))
	{
		return false;
	}
	if ( is_file( $path ) )
	{
		return unlink( $path );
	}

	$dir = dir( $path );
	while ( false !== $entry = $dir->read() )
	{
		if ( $entry == '.' || $entry == '..' )
		{
			continue;
		}
		deleteFilesFolder( $path  . '/' . $entry, true );
	}

	$dir->close();
	if( $delete )
	{
		return rmdir( $path );
	}
}


/** 
* Zwracanie elementów pierwszej tablicy, które nie występoują w drugiej 
*/
function arrayDifrenceCheck( $array_one, $array_two )
{
	if( !is_array($array_one) || !is_array($array_two) )
	{
		return $array_one;
	}
	foreach( $array_one as $key_one => $data_one )
	{
		foreach( $array_two as $key_two => $data_two )
		{
			if( $data_one['folder'] == $data_two['folder'] )
			{
				unset($array_one[$key_one]);
			}
		}
	}
	return $array_one;
}



function updateLanguage( $data )
{
	if( !is_array( $data ) || empty( $data ) )
	{
		return false;
	}
	global ${IPS_LNG};
	$languages = Translate::codes();
	foreach( $data as $key => $val )
	{
		foreach( $languages as $lang => $code )
		{
			if( !isset( ${IPS_LNG}[$key] ) )
			{	
				PD::getInstance()->insert("translations", 
					array(
						'translation_name' => $key, 
						'translation_value' => $val, 
						'orginal' => $val, 
						'language' => $code
					)
				);
			}
			else
			{
				PD::getInstance()->update("translations", 
					array(
						'translation_value' => $val, 
						'orginal' => $val
					),
					"translation_name = '" . $key . "' "
				);
			}
		}
	}
	Translate::getInstance()->clearLangCache();
}
function updateOptions( $data )
{
	if( !is_array( $data ) || empty( $data ) )
	{
		return false;
	}
	foreach( $data as $key => $val )
	{
		Config::update( $key, $val );
	}
}
function ips_api_post( $api, $post_data = array(), $debug = false )
{
	if( !isset( $post_data['user_domain'] ) )
	{
		$post_data['user_domain'] = ABS_URL;
	}
	
	$curl_options = array(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query( array ( 
			'user_data' => $post_data, 
			'env' => array( $_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME'], Config::get('email_admin_user'), ABS_URL, rand() ),
			'user_lang' => Config::get('admin_lang_locale')
		)),
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 1,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_UNRESTRICTED_AUTH => true,
		CURLOPT_FRESH_CONNECT => true,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_DNS_CACHE_TIMEOUT => 30,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:13.0) Gecko/20100101 Firefox/13.0',
		CURLOPT_REFERER => 'http://' . ( !empty( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ),
		CURLOPT_HTTPHEADER => array( 
			"Cache-Control: no-cache" 
		)
	);

	$curl = curl_init();
	curl_setopt_array($curl, $curl_options);
	curl_setopt($curl, CURLOPT_URL, AUTH_KEY_API_URL . '/' . $api );
	$data = curl_exec( $curl );
	
	curl_close( $curl );
	
	if( !empty( $data ) && $data != 'false'  )
	{
		return is_json( $data ) ? json_decode( $data, true ) : $data ;
	}
	
	if( $debug )
	{
		return  $data ;
	}
	
	return false;
}

function updateStatusMessages( $update_status )
{
	usort( $update_status, create_function('$a,$b', 'return strtolower($a[\'msg\']) > strtolower($b[\'msg\']);') );

	return Templates::getInc()->getTpl( '/__admin/update_status.html', array(
		'table_name' => 'Operacje',
		'row_first' => 'Status',
		'row_second' => 'Plik',
		'data' => $update_status
	));
}
/**
* Sprawdzanie czy zostały dodane nowe aktualizacje.
*/
function cronCheckUpdates()
{
	if( abs( Config::get('update_alert_last_time') ) < time() && Config::get('updates_disabled') != 'true' )
	{
		echo '
		<script>
		$(document).ready(function(){
			checkUpdatesCount();
		});
		</script>';
		Config::update( 'update_alert_last_time', time() + 7200 );
	}
}

 

function systemCheckDirectory( $create = false )
{
	if( file_exists( ABS_PATH . '/upload/update/system_check/' ) )
	{
		if( is_writeable( ABS_PATH . '/upload/update/system_check/' ) )
		{
			return true;
		}
	}
	elseif( $create )
	{
		if( !file_exists( ABS_PATH . '/upload/update' ) )
		{
			mkdir( ABS_PATH . '/upload/update/' );
			chmod( ABS_PATH . '/upload/update/', 0777 );
		}
		mkdir( ABS_PATH . '/upload/update/system_check/' );
		chmod( ABS_PATH . '/upload/update/system_check/', 0777 );
		return systemCheckDirectory();
	}
	
	return false;
}
function downloadPack()
{
	if ( !in_array( 'curl', get_loaded_extensions() ) )
	{
		return '<div class="message">' . __s( 'error_library', '<b>CURL</b>' ) . '</div>';
	}
	
	if ( !in_array( 'curl', get_loaded_extensions() ) )
	{
		return '<div class="message">' . __s( 'error_library', '<b>CURL</b>' ) . '</div>';
	}
	
	if( !class_exists('ZipArchive') )
	{
		return '<div class="message">' . __s( 'error_library', '<b>ZipArchive</b>' ) . '</div>';
	}
	
	File::deleteDir( ABS_PATH . '/upload/update/system_check/' );
	
	systemCheckDirectory();
	
	$response = ips_api_post( 'download/install', array( 
		'user_license' => Config::get('license_number'),
		'user_email'   => Config::get('license_email')
	) );

	if( !$response || isset( $response['error'] ) )
	{
		if( isset( $response['error'] ) )
		{
			return '<div class="message"> ' . $response['error'] . '</div>';
		}
		
		return '<div class="message"> ' . __('license_auth_wrong_data_url') . '</div>';
	}

	File::deleteDir( ABS_PATH . '/upload/update/system_check/' );
	
	if ( !file_exists( ABS_PATH . '/upload/update/system_check' ) )
	{
		mkdir( ABS_PATH . '/upload/update/system_check', 0777, true );
	}
	

	try{
		
		File::create( ABS_PATH . '/upload/update/system_check/install.zip', $response );
		
		$zip = new ZipArchive;
		$res = $zip->open( ABS_PATH . '/upload/update/system_check/install.zip' );
		
		if( $res === true )
		{
			$zip->extractTo( ABS_PATH . '/upload/update/system_check/' );
			$zip->close();
			@unlink( ABS_PATH . '/upload/update/system_check/install.zip' );
			return true;
		}
		else
		{
			return ('<div class="message">' . __('error_unpacking_zip_file') . '</div>');
		}
		
	} catch ( Exception $e ) {
							
		return ('<div class="message">' . __('error_saving_zip_file') . '</div>');
		
	}
}

function updateSite()
{
	return cronIPSCall( constant( str_replace('|', '_', 'AUTH|KEY|API|URL') ) . '/user/call?u=' . urlencode( constant( str_replace('|', '_', 'ABS|URL') ) ), 0.5 );
}
function updateTranslations()
{
	$options = include( ABS_PATH . '/upload/update/system_check/install/import-language.php' );
	global $install_language;
	$added = 0;
	$languages = Translate::codes();
	
	foreach ( $install_language as $name => $translation )
	{
		foreach( $languages as $lang => $code )
		{
			$row = PD::getInstance()->cnt( 'translations', array(
				'translation_name' => $name,
				'language' => $code
			));
			
			if( $row < 1 )
			{
				if( PD::getInstance()->insert( 'translations', array(
					'translation_name' => $name,
					'translation_value' => $translation,
					'orginal' => $translation,
					'language' => $code,
				) ) !== false )
				{
					$added++;
				}
			}
			
		}
	}
	
	return $added;
}

/**
* Update ustawień do bazy 
*/
function updateSettings()
{

	$options = include( ABS_PATH . '/upload/update/system_check/install/import-options.php' );
	$added = 0;
	foreach( $options as $table_name => $settings )
	{
		foreach( $settings as $key => $set )
		{
			$setting = Config::noCache( $set['settings_name'] );
			
			if( $setting === false )
			{
				Config::update( $set['settings_name'], $set['settings_value'], ( $set['autoload'] == 'yes' ) );

				$added++;
			}
		}
	}
	return $added;
}

function updateTables()
{
	$tables = include( ABS_PATH . '/upload/update/system_check/install/import-tables.php' );
	$added = 0;
	foreach( $tables as $table_name => $sql )
	{
		if( PD::getInstance()->PDQuery( $sql ) !== false )
		{
			$added++;
		}
	}
	return $added;
}
function files_are_equal($a, $b)
{
	if( !is_file( $a ) && !is_file( $b ) )
	{
		return true;
	}
	// Check if filesize is different
	if( filesize($a) !== filesize($b) )
		return false;

	// Check if content is different
	$ah = fopen( $a, 'rb' );
	$bh = fopen( $b, 'rb' );

	$result = true;
	while(!feof($ah))
	{
		if(fread($ah, 8192) != fread($bh, 8192))
		{
			$result = false;
			break;
		}
	}

	fclose($ah);
	fclose($bh);

	return $result;
}
function checkSystemDB()
{
	$PD = PD::getInstance();
	echo __s('updates_added_additional_translations', updateTranslations() ) 
	. ' <br /> '
	. __s('updates_added_configuration_options', updateSettings() ) ;
	updateTables();
	jQueryConfig();
}
function checkSystemFiles()
{
	$not_allowed_dirs = array( 
		'/install/',
		'/install/images/',
		'/images/',
		'/images/bebzol/',
		'/images/color/',
		'/images/fast/',
		'/images/icons/',
		'/images/icons_dodaj/',
		'/templates/' . IPS_VERSION . '/css/'
	);
	$not_allowed_files = array( 
		'favicon.ico'
	);
	if( strpos( ABS_URL, 'iprosoft.pl') === true ) 
	{
		$not_allowed_dirs = $not_allowed_files = array( );
	}
	
	$objects = new RecursiveIteratorIterator(
								   new RecursiveDirectoryIterator( ABS_PATH . '/upload/update/system_check/' ), 
								   RecursiveIteratorIterator::SELF_FIRST);
	
	$update_status = $update_status_files = array();
	
	foreach( $objects as $name => $object )
	{
		if( basename( $name ) != '.' && basename( $name ) != '..' )
		{

			$real_path = realpathDir( 'upload/update/system_check/', $object->getPathname() );
			
			$write_path = realpathDir( ABS_PATH, $real_path );
			
			$top_folder = '/' . substr( substr( $write_path, 1 ), 0, strrpos( substr( $write_path, 0, -1 ), "/" ) );
			
			if( $object->isDir() )
			{
				if( !file_exists( $real_path ) && strpos( $real_path, '/install') === false )
				{
					if( mkdir( $real_path ) )
					{
						$update_status[] = array(
							'status' => true,
							'msg' => __s( 'updates_created_directory', $write_path )
						);
					}
					else
					{
						
						$update_status[] = array(
							'status' => false,
							'msg' => __s( 'updates_error_create_directory', $write_path )
						);
					}
				}
			}
			else
			{
				if( strpos( $real_path, '/install') === false )
				{
					if( !file_exists( $real_path ) )
					{
						$contents = @file_get_contents( $object->getPathname() );
						try{
							
							File::copyFile( $object->getPathname(), $real_path );

							if( !files_are_equal( $object->getPathname(), $real_path ) )
							{
								throw new Exception('file');
							}
							
							$update_status_files[] = array(
								'status' => true,
								'msg' => __s( 'updates_updated_file', $write_path ) 
							);
							
						} catch ( Exception $e ) {
							
							$update_status_files[] = array(
								'status' => false,
								'msg' => __s( 'updates_error_write_permissions', $write_path )
							);
							
						}
					}
					elseif( !in_array( $top_folder, $not_allowed_dirs ) && !in_array( basename( $object->getPathname() ), $not_allowed_files ) )
					{
						if( !files_are_equal( $object->getPathname(), $real_path ) )
						{
							if( isset( $_GET['force_update'] ) )
							{
								$contents = @file_get_contents( $object->getPathname() );
								
								try{
									
									File::copyFile( $object->getPathname(), $real_path );

									if( !files_are_equal( $object->getPathname(), $real_path ) )
									{
										throw new Exception('file');
									}
									
									$update_status_files[] = array(
										'status' => true,
										'msg' => __s( 'updates_updated_file', $write_path ) 
									);
									
								} catch ( Exception $e ) {
									
									$update_status_files[] = array(
										'status' => false,
										'msg' => __s( 'updates_error_write_permissions', $write_path )
									);
									
								}
							}
							else
							{
								$update_status_files[] = array(
									'status' => false,
									'msg' => __s( 'updates_file_expired', $write_path )
								);
							}
							
						}
					}
				}
			}
		}
	}
	$messages = '';
	
	if( empty( $update_status ) )
	{
		$messages .= admin_msg( array(
			'info' => __('updates_folders_up_to_date')
		) );
	}
	else
	{
		$messages .= updateStatusMessages( $update_status ) . '<br />';
	}
	
	if( empty( $update_status_files ) )
	{
		$messages .= admin_msg( array(
					'info' =>  __('updates_files_up_to_date')
				) )
	;
	File::deleteDir( ABS_PATH . '/upload/update/system_check/' );
	}
	else
	{
		$messages .= 
		updateStatusMessages( $update_status_files ) 
		. '<br /><a href="' . admin_url( 'update', 'action=system&step=check_files&force_update=true' ) . '" class="button">' 
		. __('updates_force_update_files') . '</a><br /><br />
		<div class="div-info-message">
			' . __('update_info_2') . '
		</div><br />';
	}
	return $messages;
}
?>