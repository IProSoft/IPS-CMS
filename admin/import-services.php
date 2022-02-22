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
if( (bool)ini_get('safe_mode') == false )
{
	@set_time_limit(0);
}	
ini_set('max_execution_time', 0);
require_once( ABS_PATH . '/functions-upload.php');
require_once( IPS_ADMIN_PATH .'/import-functions.php');


$sess = Session::get('inc');

if( isset($_GET['auto-add']) )
{
	if( !Session::getNonEmpty( 'inc', 'import_pages' ) || Session::getNonEmpty( 'inc', 'authors' ) )
	{
		ips_admin_redirect( 'import-services', false, __('fill_in_required_fields') );
	}

	if( $_GET['auto-add'] == 'start' )
	{
		/*
		* Pobranie treści stron i wyszukanie obrazków.
		*/
		$link_content = get_link_content( $sess['import_pages'],  $sess['import_pages_regexp'], $sess['import_pages_direct'], $sess['import_pages_start'], (int)$sess['import_pages_limit'] );

		$files_ready = array();
		$skipped_results = 0;
		
		if ( $link_content )
		{
			
			$found_files = extract_html_tags( $link_content, 'img' );
			
			/**
			* Mamy jakieś import_urls, możemy przejść dalej.
			*/
			if ( $found_files )
			{
				$i = 0;
				foreach ( $found_files as $found_file_url )
				{
					//echo $found_file_url->getAttribute('src');exit;
					$site = pasuje( html_entity_decode($found_file_url->getAttribute('src')), $found_file_url->getAttribute('class') );
					$title = $found_file_url->getAttribute('alt');
					if( $site )
					{
						
						$res = $PD->select('upload_imported', array(
							'source_url' => $site['site'],
							'link' => $site['img']
						), 1);

						/**
						* Duplikaty zdjęć nie są dodawane.
						*/					
						if( empty( $res['id'] ) )
						{
							if(empty($title))
							{
								$title = 'Obrazek - '.(time() - 1327237299);
							}
							$files_ready[] = array
								(
									'img' => $site['img'],
									'title' => $title,
									'upload_type' => 'image',
									'site' => $site['site'],
								);
						}
						else
						{
							$skipped_results++;
						}
					} 
					$i++;
				}
			}
		}
		
		/**
		* Mieszanie zdjęć, obrazów.
		*/
		$keys = array_keys( $files_ready ); 
		shuffle( $keys ); 
		$random = array(); 
		$i = 0;
		while ( list(, $val) = each( $keys ) )
		{
			$random[$i] = $files_ready[$val];
			$i++;
		}
			
			
		if( empty( $files_ready ) )
		{
			clear_import_sesion();
			ips_admin_redirect( 'import-services', false, __('nothing_to_add') );
		}
		else
		{
			$random = array_map("unserialize", array_unique( array_map( "serialize", $random ) ) );

			foreach( $random as $key => $import_urls )
			{
				if( isset($sess['import_verify_images']) )
				{
					$raw = ranger( $import_urls['img'] );
					$im = @imagecreatefromstring($raw);

					$width = @imagesx($im);
					$height = @imagesy($im);
					if( empty($width) || empty($height) )
					{
						unset( $random[$key] );
					}
				}
			}

			echo Templates::getInc()->getTpl( '/__admin/import_template_form.html', array(
				'form_action' => admin_url( 'import-services', 'auto-add=ready-images' ),
				'ready_files' => $random,
				'import_settings' => Session::get( 'inc' ),
				'clear_session' => __s( 'import_clear_session', admin_url( 'import-services', 'clear_session=start' ) )
			) );	
			
			
			Session::set( 'ready_files', $random );
		}

	}		

	/**
	* User zaakceptował lub odrzucił obrazki i przesłał formularz POST
	*/
	if( $_GET['auto-add'] == 'ready-images' )
	{
		$ready_files = Session::get( 'ready_files' );
		
		if( !empty( $_POST ) )
		{
			foreach( $ready_files as $key => $import_urls )
			{
				if( !isset( $_POST['import_files'][$key] ) || $_POST['import_files'][$key]['import'] == 0 )
				{
					unset( $ready_files[$key] );
				}
				elseif( isset( $_POST['import_files'][$key]['title'] ) )
				{
					$ready_files[$key] = array_merge( $ready_files[$key], $_POST['import_files'][$key] ) ;
				}
			}
			
			if( empty( $ready_files ) )
			{
				clear_import_sesion();
				ips_admin_redirect( 'import-services', false, __('nothing_to_add_unchecked') );
			}
		}
		
		$ready_files = arrayUnique( $ready_files, 'img' );
		
		if( empty( $ready_files ) )
		{
			clear_import_sesion();
			ips_admin_redirect( 'import-services', false, __('nothing_to_add_added') );
		}
		
		$i = 0;
		$msg = '';
		
		$sess = Session::get( 'inc' );
		
		foreach( $ready_files as $key => $import_urls )
		{

			$file_name = basename(strtolower($import_urls['img']));
			if( file_exists( ABS_PATH . '/upload/import/'.$file_name) )
			{
				$file_name = rand().$file_name;
			}
			
			$options = array(
				'timeout' => 10, 
				'file' => ABS_PATH . '/upload/import/'.$file_name, 
				'refferer' => 'http://' . parse_url($import_urls['img'], PHP_URL_HOST) 
			);
			curlIPS( $import_urls['img'], $options );
			
			if( file_exists( ABS_PATH . '/upload/import/'.$file_name ) && is_image( ABS_PATH . '/upload/import/'.$file_name) )
			{
				$mime = getimagesize( ABS_PATH . '/upload/import/'.$file_name);
				
				$cut = Session::getChild( 'inc', 'import_watermark_cut' );
			
				if( $cut )
				{
					cut_watermark( ABS_PATH . '/upload/import/' . $file_name, $mime['mime'], $cut['cut_height'], $cut['cut_direct'] );
				} 
				
				$upload = new Upload_Extended();
			
				try{
					
					$_POST['upload_url'] = ABS_PATH . '/upload/import/' . $file_name;
					$data = $import_urls;
					$data['title'] = $import_urls['title'];

					$data['upload_type'] = 'image';
					$data['upload_tags'] = extractCommonWords( $sess['upload_tags'] );
					$data['top_line'] = $import_urls['title'];
					$data['bottom_line'] = $import_urls['title'];
					$data['site'] = $import_urls['site'];
					$data['user_login'] = $sess['authors'][rand(0, count($sess['authors']) - 1)];
					$data['upload_source'] = '';
					$data['import_source'] = $import_urls['img'];
					$data['import_category'] = ( isset($import_urls['import_category']) ? $import_urls['import_category'] : ( isset($sess['import_category']) ? $sess['import_category'] : '' ) );
					
					addImportedFile( $data );

					$msg .= admin_msg( array(
						'success' => __s( 'import_file_succes', basename( $import_urls['img'] ) )
					) );
				
					
				} catch (Exception $e) {
					//print_r($e);
					/* echo $e->getMessage();exit;
					var_dump( $upload );
					var_dump( $file );
					var_dump( $_POST ); */
					$msg .= admin_msg( array(
						'alert' => __s( 'import_file_error', basename( $import_urls['img'] ) )
					) );
				}
			
			
				$i++;
			}
			else
			{
				$msg .= admin_msg( array(
					'alert' => __s( 'import_file_copy_error', basename( $import_urls['img'] ) ) 
				) );
			}
			
			unset($ready_files[$key]);
				
			if( $i > 0 )
			{
				if( strpos( ABS_URL, 'iprosoft.pl' ) !== false )
				{
					clear_import_sesion();
					
					ips_admin_redirect('import-services', array(
						'user_defined' =>  $msg . admin_msg( array(
							'alert' => __('demot_functions_limited')
						) )
					));
				}

				Session::set( 'ready_files', array_values( $ready_files ) );
				
				ips_admin_redirect( 'import-services', 'auto-add=reload', array(
					'user_defined' => $msg
				));
			}
		}
		

		clear_import_sesion();
		
		ips_admin_redirect( 'import-services',  array(
			'user_defined' =>  $msg . admin_msg( array(
				'info' => __('import_files_added')
			) )
		) );
	}
	if( $_GET['auto-add'] == 'reload' )
	{
		echo '
			<script type="text/javascript">
				setTimeout("document.location.href = \'' . admin_url( 'import-services', 'auto-add=ready-images' ) . '\';",2500);
			</script>
		'; 
	}
}
	
	echo addImportForm( 'import-services', 'images' );


?>