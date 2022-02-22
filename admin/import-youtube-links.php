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
	@set_time_limit(6000);
}	
ini_set('max_execution_time', 6000);

include( IPS_ADMIN_PATH .'/import-functions.php');

	
	
	if( !Session::getNonEmpty( 'inc', 'import_pages' ) || Session::getNonEmpty( 'inc', 'authors' ) )
	{
		echo admin_msg( array(
			'info' => __('fill_in_required_fields')
		) );

		$validALL = false;
	}
	else
	{
		$validALL = true;
	}
    /**
	* Dodajemy gotowe linki z tytułami
	*/
	if( isset($_POST['post_files_to_add']))
	{
		ob_start();

		if( !empty( $_POST ) )
		{
			$ready_files = Session::get( 'ready_files' );
			
			foreach( $ready_files as $key => $import_urls )
			{
				if( !isset( $_POST['import_files'][$key] ) || $_POST['import_files'][$key]['import'] == 0 )
				{
					unset($ready_files[$key]);
				}
				elseif( isset( $_POST['import_files'][$key]['title'] ) )
				{
					$ready_files[$key] = array_merge( $ready_files[$key], $_POST['import_files'][$key] ) ;
				}
			}
		}
		
		Session::set( 'ready_files', arrayUnique( $ready_files, 'link' ) )
		
		ips_admin_redirect('import-youtube-links');
	}


	if( !isset($_POST['post_files_to_add']) && $validALL && !Session::getNonEmpty( 'ready_files' ) )
	{
		$sess = Session::get( 'inc' );
		/**
		* Pobieranie treści
		*/
		$link_content = get_link_content( $sess['import_pages'],  $sess['import_pages_regexp'], $sess['import_pages_direct'], $sess['import_pages_start'], (int)$sess['import_pages_limit'] );
		
		$videos = extract_youtube_links( $link_content );
		if( defined('IPS_SELF' ) )
		{
			$videos = extract_youtube_links_ips_self( $link_content, $videos );
		}
		
		foreach( $videos as $id => $video_id )
		{
			if( is_array( $video_id ) && isset( $video_id['link'] ) )
			{
				$title = $video_id['title'];
				$video_id = $video_id['link'];
			}
			$sxml = @simplexml_load_file( 'http://gdata.youtube.com/feeds/api/videos/' . $video_id );
			
			if( is_object( $sxml ) && $sxml )
			{

				if( !isset( $title ) || empty( $title ) )
				{
					$title = (string)$sxml->title;
				}
				
				$res = $PD->select("upload_imported", array(
					'source_url' => 'Youtube',
					'link' => 'http://www.youtube.com/watch?v=' . $video_id
				));
				if( empty($res['id']) )
				{

					if( empty( $title ) )
					{
						$title = 'Video - ' . rand();
					}

					$keywords = extractCommonWords( $title );
					
					if( is_array( $keywords ) )
					{
						$keywords = implode(",", array_keys( $keywords ) );
					}
						$files_ready[] = array
							(
								'img' => 'http://img.youtube.com/vi/' . $video_id . '/0.jpg',
								'link' => 'http://www.youtube.com/watch?v=' . $video_id,
								'title' => trim($title),
								'site' => 'Youtube',
								'upload_tags' => $keywords,
								'upload_type' => 'video',
								'import_category' => ( isset($sess['import_category']) ? $sess['import_category'] : '' )
							);
				}
				$title = null;
			}

	
		}
		if( empty( $files_ready ) )
		{
			clear_import_sesion();
			ips_admin_redirect( 'import-youtube-links', __('nothing_to_add') );
		}
		else
		{
			echo Templates::getInc()->getTpl( '/__admin/import_template_form.html', array(
				'form_action' => admin_url( 'import-youtube-links', 'auto-add=ready-images' ),
				'ready_files' => $files_ready,
				'import_settings' => $sess,
				'clear_session' => __s( 'import_clear_session', admin_url( 'import-youtube-links', 'clear_session=start' ) )
			) );	

			Session::set( 'ready_files', $files_ready );
		}
		
	}
	elseif( Session::has( 'ready_files' ) )
	{
		if( $ready_files = Session::getNonEmpty( 'ready_files' ) )
		{
			$i = 0;
			$sess = Session::get( 'inc' );
			
			foreach( $ready_files as $key => $import_urls )
			{
				try{
					
					$data = $import_urls;
					$data['title'] = $import_urls['title'];
					$data['upload_subtype'] = 'video';
					$data['upload_type'] = 'video';
					$data['upload_tags'] = extractCommonWords( $import_urls['upload_tags'] );
					$data['upload_video'] = $import_urls['link'];
					$data['top_line'] = $import_urls['title'];
					$data['bottom_line'] = $import_urls['title'];
					$data['site'] = $import_urls['site'];
					$data['user_login'] = $sess['authors'][rand(0, count($sess['authors']) - 1)];
					$data['upload_source'] = $import_urls['site'];
					$data['import_source'] = $import_urls['img'];
					$data['import_category'] = ( isset($import_urls['import_category']) ? $import_urls['import_category'] : ( isset($sess['import_category']) ? $sess['import_category'] : '' ) );
					
					addImportedFile( $data );
					
					echo admin_msg( array(
						'success' => __s( 'import_file_succes', $import_urls['title'] )
					) );
					
					
				} catch (Exception $e) {
					
					@unlink( ABS_PATH . '/upload/import/'.$file_name );	
					
					echo admin_msg( array(
						'alert' => __s( 'import_file_error', $import_urls['title'] ) 
					) );
				}

				$i++;
				unset($ready_files[$key]);
					
				if( $i > 5 )
				{
					Session::set( 'ready_files', array_values( $ready_files ) );
					echo '
					<script type="text/JavaScript">
						setTimeout("location.reload(true);",2500);
					</script>
					'; 
					exit;
				}
			}
			
			Session::set( 'ready_files', array_values( $ready_files ) );
			
			echo '
			<script type="text/JavaScript">
				setTimeout("location.reload(true);",2500);
			</script>
			';
			exit;
			
		}
		else
		{
			echo admin_msg( array(
				'success' => __( 'import_add_success' )
			) );
		}
	}
	
	echo addImportForm( false, 'youtube-links' );

	
?>