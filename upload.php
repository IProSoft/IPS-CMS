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

	include( dirname(__FILE__) . '/php-load.php' );
	
	header('Content-Type: text/html; charset=utf-8');
	
	include( ABS_PATH . '/functions-upload.php' );
	
	define( 'IPS_EDIT_FILE', get_input( 'edit_id' ) );
	
	Session::set( 'up-redirect', 'up/' .  $_GET['upload_type'] . '/' . ( IPS_EDIT_FILE ? '?edit_id=' . IPS_EDIT_FILE : '' ) );
	
	if( !USER_LOGGED && Config::getArray( 'user_guest_option', 'upload' ) == 0 )
	{
		ips_redirect( 'login/', 'add_for_logged_in' );
	}
	
	if( empty( $_POST ) )
	{
		return ips_redirect( 'index.html' );
	}
	
	$upload = new Upload_Extended();
	
	$_POST = apply_filters( 'upload_post_data', $_POST );
	
	$upload_video = has_value( 'upload_url', $_POST, false );
	
	try{
		
		if( !csrf_token_verify( get_input('_token') ) )
		{
			throw new Exception('err_csrf_token');
		}

		if( !isset( $_GET['upload_type'] ) )
		{
			throw new Exception('err_unknown');
		}
		
		if( Config::get('add_require_rules') )
		{
			if( !isset( $_POST['add_rules'] ) )
			{
				throw new Exception('add_require_rules_error');
			}
		}
		
		if ( !isset( $_POST['ajax_post'] ) && !defined( 'IPS_ADMIN_PANEL' ) && ( Config::get( 'add_captcha' ) == 2 || ( !USER_LOGGED && Config::get( 'add_captcha' ) == 1 ) ) )
		{	
			if( strtolower( trim( $_POST['captcha'] ) ) != Session::get( 'captcha' ) )
			{
				throw new Exception('common_captcha_error');
			}
		}
		
		$upload_type = has_value( 'upload_type', $_GET, 'image' );
		$upload_subtype = has_value( 'upload_subtype', $_POST, false );
		
		if( $upload_type == 'video' && empty( $_POST['title'] ) )
		{
			preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $upload_video, $matches );
			
			if( isset( $matches[0] ) && !empty( $matches[0] ) )
			{
				$sxml = @simplexml_load_file( 'http://gdata.youtube.com/feeds/api/videos/' . $matches[0] );
				$_POST['title']  = (string)$sxml->title;
				$upload_video = 'http://www.youtube.com/watch?v=' . $matches[0];
			}
		}
		
		$upload->makeFileConfig( $_GET['upload_type'], $upload_subtype );
		
		if( Config::get('upload_tags') )
		{
			$upload->setUserTags( get_input( 'upload_tags' ), get_input( 'top_line' ) . ' ' . get_input( 'bottom_line' ) );
		}
		
		$switch_type = $upload_subtype;
		
		if( !in_array( $switch_type , array( 'image', 'text', 'video', 'mp4', 'swf' ) ) )
		{
			$switch_type = $upload_type;
		}

		switch( $switch_type )
		{
			case 'ranking':
			
				$up_ranking = new Upload_Ranking();
				$up_ranking->setUpload( $upload );
				
				$up_ranking->uploadImages( $_POST['upload_images'], ( isset( $_POST['update_ranking'] ) ? 1 : 2 ) );
				
				if( !isset( $_POST['update_ranking'] ) )
				{
					if( Config::get( 'ranking_options', 'add_description' ) )
					{
						$up_ranking->description( Sanitize::cleanXss( $_POST['long_text'], true ) );
					}
				}
				else
				{
					$up_ranking->addImages( $_POST['upload_id'] );
					
					upload_clear();
					
					die( ips_json( array(
						'content' => ips_message( array(
							'info' => 'add_success_ranking_updated'
						), true ),
						'url'     => ABS_URL . $_POST['upload_id'] .'/'
					) ) );
				}
				
				$upload->uploadFile( $up_ranking->introImage( array_values( $up_ranking->images['files'] ) ) );
				
			break;
			
			case 'article':
			
				$upload->uploadFile();
				$article = $upload->prepareArticle( Sanitize::cleanXss( $_POST['long_text'], true ) );
				
			break;
			
			case 'gallery':
				
				$up_gallery = new Upload_Gallery();
				$up_gallery->setUpload( $upload );
				$up_gallery->uploadImages( $_POST['upload_images'] );
				
				if( Config::get( 'gallery_options', 'add_description' ) )
				{
					$up_gallery->description( Sanitize::cleanXss( $_POST['long_text'], true ) );
				}
				
				$upload_extra = array(
					'images_count' => count( $up_gallery->images['files'] )
				);

				$upload->uploadFile();
			break;
			
			case 'animation':
				
				if( !$animated_gif_tmp = Session::get('animated_gif_tmp') )
				{
					throw new Exception( 'err_uploaded_file' );
				}
				/** Attach action */
				$up_animation = new Upload_Animation();

				$upload->setConfig( array(
					'url' => 'upload/tmp/' . $animated_gif_tmp . '.gif'
				) );

			break;
			
			case 'image':
				$upload->uploadFile();
			break;
			
			case 'text':
				
				$upload->setConfig( array(
					'post' => 'text_bg_link'
				) );
				
				if(	!$text = get_input( 'long_text' ) )
				{
					throw new Exception( 'err_text' );
				}
				
				$up_text = new Upload_Text;
				$up_text->layers = $up_text->setLayers( get_input( 'upload_text_layers' ) );
				
				$up_text->uploadFilters();

				$clean = $up_text->clean( $text );
				
				$article = [
					'long_text'  => $up_text->format( $clean ),
					'intro_text' => $up_text->format( $clean, true ),
					'meta_text'  => $up_text->fromJson( $text )
				];
				
			break;
			
			case 'video':
			case 'swf':
			case 'mp4':
			
				if( $upload_subtype == 'swf' )
				{
					$video = new Upload_Swf();

					$video_data = $video->getUpload( array(
						'post' => 'upload_url'
					));
				}
				elseif( $upload_subtype == 'mp4' )
				{
					$video = new Upload_Mp4();

					$video_data = $video->getUpload( array(
						'post' => 'upload_url'
					));
				}
				else
				{
					$video = new Upload_Video();
					$video_data = $video->videoParseUrl( $upload_video );
				}
				
				if ( !$video_data || empty( $video_data['upload_video'] ) )
				{
					throw new Exception('err_video_link');
				}
				
				$upload_video_size = get_input( 'upload_video_size', false );

				if( $upload_video_size )
				{
					$video_data = array_merge( $video_data, json_decode( $upload_video_size, true ) );
				};
		
				$upload->uploadVideoImage( $upload->adaptCover( $video_data['image'], $video_data ) );

				$upload_video = $video_data['upload_video'];
				
			break;
			
			default:

				return ips_redirect( 'up/', array(
					'alert' => 'Hakier?'
				) );
				
			break;
		}

		$file = $upload->Load( IMG_PATH . '/' . $upload->name );
		
		//var_dump($upload);print_r($file);exit;
		
		$file['upload_type']	= $upload_type;
		$file['upload_subtype']	= has_value( 'upload_subtype', $file, $upload_subtype );
		$file['title']			= $upload->getTitle();
		$file['top_line']		= Sanitize::nl2br2( Sanitize::cleanXss( get_input( 'top_line', $file['title'] ) ) );
		$file['bottom_line']		= Sanitize::nl2br2( Sanitize::cleanXss( get_input( 'bottom_line', '' ) ) );
		$file['up_title_hide']	= (bool)get_input( 'up_title_hide', false );
		$file['upload_adult']	= (bool)get_input( 'upload_adult', false );
		$file['upload_source']  = get_input( 'upload_source', '' );
		$file['upload_source']  = Sanitize::validatePHP( $file['upload_source'], 'url' ) ? Sanitize::cleanSQL( $file['upload_source'] ) : htmlspecialchars( $file['upload_source'], ENT_QUOTES );
		$file['user_login'] 	= Sanitize::cleanXss( get_input( 'user_login', USER_LOGIN ) );
		$file['private'] 		= get_input( 'private', Session::getChild( 'upload_tmp', 'upload_status' ) == 'private' );
		$file['category_id'] 	= get_input( 'file_category', false ) ;
		$file['upload_extra'] 	= isset( $upload_extra ) ? $upload_extra : false;
		
		$file['upload_file_data'] = array(
			'top_line' => get_input( 'top_line', '' ),
			'bottom_line' => get_input( 'bottom_line', '' ),
			'top_line_json' => get_input( 'top_line_json', '' ),
			'bottom_line_json' => get_input( 'bottom_line_json', '' ),
			'long_text' => get_input( 'long_text', '' ),
			'up_title_hide' => $file['up_title_hide']
		);
		
		
		if( !isset( $article ) && isset( $_POST['extra_description'] ) )
		{
			$article['long_text'] = $article['intro_text'] = Sanitize::cleanXss( $_POST['extra_description'] );
		}
		
		$upload->configuration( $file );
		
		if( !empty( $file['name'] ) || !IPS_EDIT_FILE )
		{	
			$uploaded_files = $upload->initCreateImage( isset( $_POST['ajax_img_return'] ) );
			
			if( isset( $_POST['ajax_img_return'] ) )
			{
				die( ips_json( array( 
					'content' => ips_message( array(
						'info' => 'common_wait_redirect'
					), true ),
					'url'     => '/ajax/download_img/?img=' . basename( $upload->put( $uploaded_files[$_POST['ajax_img_return']], IPS_TMP_FILES . '/' . str_random( 10 ) ) )
				) ) );
			}
		}
		else
		{
			$edit_file_name = false;
		}	

		
		
		if( $file_edit_id = Session::getChild( 'upload_tmp', 'file_edit_id' ) && get_input( 'edit_id' ) )
		{
			$edit = new Edit();
			$edit->setId( $file_edit_id  );
			$edit->category_id = $file['category_id'];
			$edit->tags 	   = ( isset($_POST['upload_tags']) ? $_POST['upload_tags'] : false );
			
			if( isset( $article ) )
			{
				$edit->editArticleText = $article;
			}
			
			if( isset( $ranking_images ) )
			{
				$edit->editRankingImages = $ranking_images;
			}
			
			if( !isset( $edit_file_name ) )
			{
				$edit_file_name = $upload->config['up_folder']['folder'] . '/' . basename( $upload->name );
			}
			
			$edit->editFile( $edit_file_name, $file, $file['upload_source'], $file['private'], $upload_video, $upload );
			
			upload_clear();
			
			return ips_redirect('index.html');
		}
		else
		{
			if( $upload->addUploadFile( $file, $upload_video ) )
			{
				if( isset( $article ) )
				{
					$upload->addUploadText( $article, $upload->file_add_id );
				}

				if ( Config::get('module_history') )
				{
					Ips_Registry::get('History')->storeAction( 'add', array(
						'upload_id' => $upload->file_add_id,
						'action_name' => $upload_type
					) );
				}
			}
			
			if( $file['upload_type'] == 'mem' )
			{
				if( isset( $_POST['up_generator'] ) )
				{
					Mem::used( $_POST['up_generator'] );
				}
			}
			
			upload_clear();

			if( isset( $_POST['ajax_post'] ) )
			{
				die( ips_json( array(
					'content' => ips_message( array(
						'info' => 'add_success_' . $upload_type
					), true ),
					'url'     => seoLink( $upload->file_add_id, $file['title'] ),
					'text'    => __( 'user_mod_item_view' )
				) ) ); 
			}
			
			die();
			return ips_redirect( 'index.html', 'add_success_' . $upload_type , 'info' );
		}
		
	} catch ( Exception $e ) {
		
		if( is_string( $e->getMessage() ) )
		{
			return upload_error( $e->getMessage() );
		}
		
		ips_log( $e->getMessage() );
		
		return upload_error( 'err_unknown' );
		
	}
?>