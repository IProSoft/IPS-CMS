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
	require_once( IPS_ADMIN_PATH .'/import-functions.php');
	
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
	
	foreach( $ready_files as $key => $import_urls )
	{
		if( count( $ready_files[$key] ) <= 1 )
		{
			unset( $ready_files[$key] );
		}
	}
	
	$saved = 0;
	foreach( $ready_files as $key => $import_urls )
	{
		$data = $import_urls;
		/**
		* Obrazek
		*/
		if( $import_urls['upload_type'] == 'image' )
		{	
			$file_name = basename( strtolower( $import_urls['img'] ) );
			
			$data['image-link'] = $import_urls['img'];
			
			$cut = Session::getChild( 'inc', 'import_watermark_cut' );
			
			if( $cut )
			{
				if( file_exists( ABS_PATH . '/upload/import/drafts/' . $file_name ) )
				{
					$file_name = rand() . $file_name;
				}
				$options = array(
					'timeout' => 30, 
					'file' => ABS_PATH . '/upload/import/drafts/' . $file_name, 
					'refferer' => 'http://' . parse_url( $import_urls['img'], PHP_URL_HOST ) 
				);
				curlIPS( $import_urls['img'], $options );
				
				$mime = getimagesize( ABS_PATH . '/upload/import/drafts/' . $file_name );

				cut_watermark( ABS_PATH . '/upload/import/drafts/'.$file_name, $mime['mime'], $cut['cut_height'], $cut['cut_direct'] );
				
				$data['image-link'] = ABS_PATH . '/upload/import/drafts/' . $file_name ;
			}
				
			$data['upload_type'] = 'image';
			$data['import_source'] = $import_urls['img'];
			$draft_name = 'import_draft_' . md5( $file_name );
		}
		/**
		* Video
		*/
		else
		{
			$data['upload_type'] = 'video';
			$data['upload_video'] = $import_urls['link'];
			$data['import_source'] = $import_urls['link'];
			$draft_name = 'import_draft_' . md5( $import_urls['link'] );
		}
		
		$sess = Session::get( 'inc' );
		
		$data['title'] = $import_urls['title'];
		
		$data['upload_tags'] = extractCommonWords( Session::getNonEmpty( 'inc', 'upload_tags', $import_urls['upload_tags'] ) );
		$data['top_line'] = $import_urls['title'];
		$data['bottom_line'] = $import_urls['title'];
		$data['user_login'] = $sess['authors'][rand(0, count($sess['authors']) - 1)];
		$data['upload_source'] = '';
		$data['import_category'] =  ( isset( $import_urls['import_category'] ) ? $import_urls['import_category'] : Session::getNonEmpty( 'inc', 'import_category', '' ) ) ;
		$data['import_add_to'] =  Session::getChild( 'inc', 'import_add_to' );
		
		file_put_contents( ABS_PATH . '/upload/import/drafts/' . $draft_name, serialize( $data ) );
		$saved++;
	}
	
	Session::clear( 'inc' );
	Session::clear( 'ready_files' );
	
	ips_admin_redirect('uploading', __s( 'cron_saved_templates', $saved ) );
	
	
}
?>