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
$msg = '';
$link_content = array();
$sess = Session::get( 'inc' );
	
	/**
	* Weryfikowanie czy zostały podane poprawne linki
	*/
	if( !empty($_POST) && !isset($_POST['post_files_to_add']) )
	{
		$adresy = $sess['import_pages'];
		$sess['import_pages'] = array();
		foreach( $adresy as $url )
		{	
			if( $link = playlistLink($url) )
			{
				$sess['import_pages'][] = $link;
			}
		}
		
		if( empty($sess['import_pages']) )
		{
			$msg = __('import_playlist_error');
		}
	}
	if( isset($_POST['post_files_to_add']))
	{
		$ready_files = Session::get( 'ready_files' );
		ob_start();
		if( !empty( $_POST ) )
		{
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
		Session::set( 'ready_files', arrayUnique($ready_files, 'link') );
		
		ips_admin_redirect('import-youtube-playlist');
	}
	if( empty($sess['import_pages']) || empty($sess['authors']) )
	{
		echo admin_msg( array(
	'info' => __('fill_in_required_fields') . ' <br />'.$msg) );
		$validALL = false;
	}
	
	else
	{
		$validALL = true;
	}
    // set feed URL
	//http://www.youtube.com/playlist?list=PLBBA60F0ECA7D2878&feature=g-all-a
   
if( $validALL && !Session::has( 'ready_files' ) ){
	$adresy = $sess['import_pages'];

	foreach( $adresy as $nazwa => $link )
	{
		$sxml = @simplexml_load_file($link);

		if (!$sxml)
		{
		  break;
		}
		foreach ($sxml->entry as $entry)
		{
			
			$media = $entry->children('http://search.yahoo.com/mrss/');
			$attrs = $media->group->player->attributes();

			if( isset($attrs['url']) && !empty( $attrs['url'] ) )
			{	
				$url = (string)$attrs['url'];
				$res = $PD->select("upload_imported", array(
					'source_url' => 'Youtube',
					'link' => htmlentities($url)
				));
				if( empty($res['id']) )
				{

					$title = (string)$media->group->title;
					
					if( empty($title) )
					{
						$title = 'Video - ' . (time() - 1327237299);
					}
					
					$attrsthumbnail = $media->group->thumbnail[0]->attributes();
					$keywords = $media->group->keywords;
					$keywords = extractCommonWords( $keywords );
					if( is_array($keywords) )
					{
						$keywords = implode(",", array_keys( $keywords ) );
					}
						$files_ready[] = array
							(
								'img' => (string)$attrsthumbnail['url'],
								'link' => str_replace('&feature=youtube_gdata_player', '', $url),
								'title' => str_replace('Cars on icy road Compilation', 'Wypadki zimą - ', $title),
								'site' => 'Youtube',
								'upload_tags' => $keywords,
								'upload_type' => 'video'
							);
				}
			}
		}
	}
	
	
	if( empty( $files_ready ) )
	{
		clear_import_sesion();
		ips_admin_redirect( 'import-youtube-playlist', false, __('nothing_to_add'));
	}
	else
	{
		echo Templates::getInc()->getTpl( '/__admin/import_template_form.html', array(
			'form_action' => admin_url( 'import-youtube-playlist', 'auto-add=ready-images' ),
			'ready_files' => $files_ready,
			'import_settings' => $sess,
			'clear_session' => __s( 'import_clear_session', admin_url( 'import-youtube-playlist', 'clear_session=start' ) )
		) );
		
		Session::set( 'ready_files', $files_ready );
	}
	
}
elseif( $ready_files = Session::has( 'ready_files' ) )
{
	if(!empty($ready_files ) )
	{
	
		$i = 0;

		foreach($ready_files as $val => $import_urls)
		{

			error_reporting(E_ALL);
			

				

			$upload = new Upload_Extended();
			try{
				
				$data = $import_urls;
				$data['title'] = $import_urls['title'];
				$data['upload_type'] = 'image';
				$data['upload_type'] = 'video';
				$data['upload_tags'] = extractCommonWords( ( !empty( $sess['upload_tags'] ) ? $sess['upload_tags'] : $import_urls['upload_tags'] ) );
				$data['upload_video'] = $import_urls['link'];
				$data['top_line'] = $import_urls['title'];
				$data['bottom_line'] = $import_urls['title'];
				$data['site'] = $import_urls['site'];
				$data['user_login'] = $sess['authors'][rand(0, count($sess['authors']) - 1)];
				$data['upload_source'] = $import_urls['site'];
				$data['import_source'] = $import_urls['link'];
				$data['import_category'] = ( isset($import_urls['import_category']) ? $import_urls['import_category'] : ( isset($sess['import_category']) ? $sess['import_category'] : '' ) );
				
				addImportedFile( $data );

				echo admin_msg( array(
				'success' => __s( 'import_file_succes', basename($import_urls['title']) ) ) );
			
						} catch (Exception $e) {
				@unlink( ABS_PATH . '/upload/import/'.$file_name );
				//echo $e->getMessage();exit;	
				echo admin_msg( array(
					'alert' => __s( 'import_file_error', basename($import_urls['title']) ) );
			}
			
			
			$i++;

			unset($ready_files[$val]);
				
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
	'info' => __( 'import_files_added' ).' ' . __s( 'import_clear_session', admin_url( 'import-youtube-playlist', 'clear_session=start' ) ) 
) );
	}


}
	echo addImportForm( false, 'playlist' );

	
	
?>