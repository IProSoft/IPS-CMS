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

class Pin{
	
	public function create( $data )
	{
		$data = apply_filters( 'upload_post_data', $data );
		
		$user_id = isset( $data['user_id'] ) ? (int)$data['user_id'] : USER_ID;
		
		if( !isset( $data['pin_board_id'] ) || empty( $data['pin_board_id'] ) )
		{
			throw new Exception('You must pic Board to add Pin');
		}

		if( !isset( $data['pin_description'] ) || !$data['pin_description'] )
		{
			throw new Exception(' Dodaj opis do dodawanego materiału ');
		}
		
		if( isset( $data['repin_from'] ) )
		{
			$board_id = $this->getField( intval( $data['repin_from'] ), 'board_id' );
			if( $board_id == $data['pin_board_id'] )
			{
				throw new Exception('Ten PIN został juz przypięty do tej tablicy');
			}
		}
		
		$pin_type = 'image';
		
		if( isset( $data['upload_video'] ) && !empty( $data['upload_video'] ) )
		{
			if( strpos( $data['upload_video'], '.mp4') === false )
			{
				$video = new Upload_Video();
				$image = $video->videoParseUrl( urldecode( $data['upload_video'] ) );
				
				if ( !$image || !isset( $image['upload_video'] ) )
				{
					throw new Exception( __('err_video_link') );
				}

				$data['upload_video'] = $image['upload_video'];
			}
			$pin_type = 'video';
		}

		$image_folder = getFolderByDate( null, IPS_CURRENT_DATE );
	
		Upload_Extended::createPath();
		
		$data['upload_image'] = is_array( $data['upload_image'] ) ? $data['upload_image'] : urldecode( $data['upload_image'] );

		$add_watermark = !(bool)has_value( 'repin_from', $data ) && Config::get('watermark') != 'off';
		
		/**
		* Image from Website
		*/
		if( isset( $data['pin_from_url'] ) && !empty( $data['pin_from_url'] ) )
		{
			$upload_image = $data['upload_image'];
		}
		/**
		* Image from URL
		*/
		elseif( isset( $data['upload_image'] ) && !empty( $data['upload_image'] ) && Sanitize::validatePHP( $data['upload_image'], 'url' ) )
		{
			$upload_image = $data['upload_image'];
		}
		/**
		* Image from other PIN
		*/
		elseif( file_exists( ABS_PATH . '/' . $data['upload_image'] ) )
		{
			$upload_image = ABS_URL . $data['upload_image'];
		}
		else
		{
			throw new Exception('Ther was an error while getting Pin image');
		}
		
		$upload_image = $this->importImage( $upload_image, $add_watermark );
		
		/**
		* Gallery files
		*/
		if( isset( $data['upload_image_files'] ) && is_array( $data['upload_image_files'] ) )
		{
			$upload_gallery = $this->importGallery( $data['upload_image_files'] );
			if( $upload_gallery == false )
			{
				throw new Exception('Ther was an error while parsing Pin gallery images');
			}
			$pin_type = 'gallery';
		}

		/* FIX with path */
		$upload_image = $image_folder . '/' . $upload_image;
		
		$db = PD::getInstance();
		
		$board_fields = Board::getField( $data['pin_board_id'], 'board_title,board_privacy,category_id' );
		
		$pin_source = $pin_source_hash = ( has_value( 'pin_source', $data ) ? $data['pin_source'] : ( has_value( 'pin_from_url', $data ) ? $data['pin_from_url'] : 'pin_uploaded' ) );
		
		if( $pin_source != 'pin_uploaded' )
		{
			$pin_source_hash = str_replace( 'www.', '', parse_url( $pin_source, PHP_URL_HOST ) );
			if( strpos( parse_url( ABS_URL, PHP_URL_HOST ), str_replace( 'www.', '', $pin_source_hash ) ) !== false )
			{
				$pin_source = $pin_source_hash = 'pin_uploaded';
			}
		}
		
		if( strpos( $data['upload_video'], '.mp4') !== false )
		{
			$data['upload_video'] = Upload::handleVideoFile( $data['upload_video'], IPS_CURRENT_DATE );
		}
		
		$pin_id = $db->insert( IPS__FILES, array(
			'user_id' => $user_id,
			'category_id' => $board_fields['category_id'],
			'board_id' => $data['pin_board_id'],
			'date_add' => IPS_CURRENT_DATE,
			'date_modified' => IPS_CURRENT_DATE,
			'pin_description' => $data['pin_description'],
			'pin_title' => isset( $data['pin_title'] ) ? $data['pin_title'] : cutWords( $data['pin_description'], 250 ),
			'pin_source' => $pin_source,
			'pin_source_hash' => md5( $pin_source_hash ),
			'upload_image' => $upload_image,
			'repin_from' => has_value( 'repin_from', $data ),
			'pin_type' => ( isset( $this->pin_type ) && $pin_type != 'gallery' ? 'gif' : $pin_type ),
			'upload_video' => isset( $data['upload_video'] ) ? urldecode( $data['upload_video'] ): '',
			'pin_privacy' => $board_fields['board_privacy'],
			'upload_sizes' => $this->pinImagesSizes( $upload_image ),
			'pin_has_place' =>  isset( $data['pin_has_place'] ) ? 1 : 0,
			'pin_color' => $this->getDominantColor( ips_img_path( $upload_image, 'medium' ) )
		));
		 
		if( !$pin_id )
		{
			throw new Exception('Ther was an error while crating PIN');
		}
		
		$db->update('pinit_boards', array(
			'board_pins' => $db->cnt( IPS__FILES, 'board_id =' . $data['pin_board_id'] ),
		), array( 'board_id' => $data['pin_board_id'] ) );
		
		if( isset( $data['repin_from'] ) && is_numeric( $data['repin_from'] ) )
		{
			$db->update(IPS__FILES, array(
				'pin_repins' => $db->cnt( IPS__FILES, 'repin_from =' . $data['repin_from'] ),
			), array( 'id' => $data['repin_from'] ) );
		}
		
		/** Notify users following user */
		Notify::getInstance()->notifyUser( array(
			'type' => 'new_pin',
			'user_id' => $db->select( 'users_follow_user', array( 
					'user_followed_id' => $user_id
				), null, 'user_id' ),
			'pin_id' => $pin_id
		) );
		
		/** Notify users following board */
		Notify::getInstance()->notifyUser( array(
			'type' => 'new_board_pin',
			'user_id' => $db->select( 'pinit_users_follow_board', array( 
				'board_id' => $data['pin_board_id']
			), null, 'user_id'),
			'board_id' => $data['pin_board_id'],
		) );
		
		$u = new PinUser();
		$u->updateUser( $user_id );
		
		File::deleteFile( IPS_TMP_FILES . '/' . basename( $data['upload_image'] ) );

		$this->generateTags( $pin_id, has_value( 'repin_from', $data ), ( isset( $data['tags'] ) ? $data['tags'] : $data['pin_description'] ) );
		
		if( isset( $upload_gallery ) )
		{
			$upload = new Upload_Extended();
			
			$upload->addUploadText( array(
				'intro_text' => $upload_gallery,
				'long_text'	 => $upload_gallery
			), $pin_id );
		}
		
		return array(
			'pin_id'		=> $pin_id,
			'pin_url'		=> $this->pinUrl( $pin_id ),
			'board_id'		=> $data['pin_board_id'],
			'board_title'	=> $board_fields['board_title']
		);
	}
	
	
	/**
	* Edit Pin
	*
	* @param 
	* 
	* @return 
	*/
	
	public function edit( $data )
	{
		if( !isset( $data['pin_id_edit'] ) || empty( $data['pin_id_edit'] ) )
		{
			throw new Exception('There was problem with Pin data');
		}
		
		if( !isset( $data['pin_board_id'] ) || empty( $data['pin_board_id'] ) )
		{
			throw new Exception('You must pic Board to add Pin');
		}

		if( !isset( $data['pin_description'] ) || !$data['pin_description'] )
		{
			throw new Exception('Dodaj opis do dodawanego materiału ');
		}

		
		$pin_info = $this->getPin( $data['pin_id_edit'], 'board_id' );
		
		if( empty( $pin_info ) )
		{
			throw new Exception('Brak pliku o takim ID');
		}
		
		$pin_edit = array(
			'board_id' => $data['pin_board_id'],
			'date_modified' => IPS_CURRENT_DATE,
			'pin_description' => $data['pin_description'],
			'pin_title' => isset( $data['pin_title'] ) ? $data['pin_title'] : cutWords( $data['pin_description'], 250 )
		);
		
		if( isset( $data['upload_video'] ) && !empty( $data['upload_video'] ) && $data['upload_video'] != $pin_info['upload_video'] )
		{
			
			
			if( strpos( $data['upload_video'], '.mp4') !== false )
			{
				$upload_mp4 = new Upload_Mp4();
				
				$video_data = $upload_mp4->getUpload( array(
					'post' => $data['upload_video']
				));
				
				if( empty( $video_data['upload_video'] ) )
				{
					throw new Exception('err_video');
				}
				
				$upload_image = $video_data['image'];
			}
			else
			{
				$video = new Upload_Video();
				$image = $video->videoParseUrl( urldecode( $data['upload_video'] ) );
				
				if ( !$image || !isset( $image['upload_video'] ) )
				{
					throw new Exception( __('err_video_link') );
				}

				$pin_edit['upload_video'] = $image['upload_video'];
				$upload_image = $image['image'];
			}

			$pin_edit['pin_type'] = 'video';

			$image_folder = getFolderByDate( null, IPS_CURRENT_DATE );
	
			Upload_Extended::createPath();
			

			$pin_edit['upload_sizes'] = $this->pinImagesSizes( $upload_image );
			$pin_edit['upload_image'] = $this->importImage( $upload_image, Config::get('watermark') != 'off' );
			
			$pin_edit['pin_color'] = $this->getDominantColor( ips_img_path( $pin_edit['upload_image'], 'medium' ) );
			
			if( strpos( $data['upload_video'], '.mp4') !== false )
			{
				$pin_edit['upload_video'] = Upload::handleVideoFile( $data['upload_video'], IPS_CURRENT_DATE );
			}
		}
		
		if( PD::getInstance()->update( IPS__FILES, $pin_edit, array( 'id' => $data['pin_id_edit'] ) ) )
		{
			return $pin_edit;
		}
		
		throw new Exception('Wystąpił problem z edycją materiału, prosimy spróbować za chwilę');
	}
	/**
	* Prepare images for gallery
	*
	* @param $images - input array of images
	* 
	* @return 
	*/
	public function importGallery( $images )
	{
		try{
			
			$upload = new Upload();
			
			$images = $upload->uploadImages( $images );
			
			return $images;
		
		}catch( Exception $e ){
			return false;
		}
	}
	
	/**
	* Import image from another website
	*
	* @param $image_url string url to image
	* 
	* @return $uploaded_finish->name string image name on server
	*/
	public function importImage( $image_url, $add_watermark = false )
	{
		
		require_once( ABS_PATH . '/functions-upload.php' );

		$upload_handler = new Upload_Handler_Extended( 'upload' );

		$image_folder = createFolderByDate( IMG_PATH_LARGE, date("Y-m-d H:i:s"), 'path' );
		$file_path = $image_folder . '/' . $upload_handler->newFileName( '' );
		
		$upload_file = new Upload_Single_File();
		
		$file = $upload_file->setConfig( array(
			'url' => urldecode( $image_url )
		) )->Load( $file_path );
		
		/* Change ext to jpg if GIF */
		if( $file['extension'] == 'gif' )
		{
			$this->pin_type = 'gif';
			copy( $file_path . '.' . $file['extension'], createFolderByDate( IMG_PATH_GIF, date("Y-m-d H:i:s"), 'path' ) . '/' . basename( $file_path ) .'.' . $file['extension'] );
			$file['name'] = basename( $upload_file->changeExt( 'jpg' ) );
		}
		
		if( $add_watermark )
		{
			$watermark = new Upload();
			$watermark->_mime_type = $watermark->getMimeType( $image_folder . '/' . $file['name'] );
			$watermark->makeWatermark( $image_folder . '/' . $file['name'] );
		}
		
		$uploaded_finish = $upload_handler->upload_all_files( $image_folder . '/' . $file['name'], $file['name'] );
		
		return $uploaded_finish->name;
		
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function pinUrl( $pin_id )
	{
		return ABS_URL . 'pin/' . $pin_id;
	}
	


	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function findPinByTitle( $user_id, $pin_title )
	{
		$db = PD::getInstance();
		
		$pin_id = $db->select( 'pinit_boards', "( pin_title LIKE '" . $pin_title. "' OR pin_description LIKE '" . $pin_title. "' ) AND user_id = '" . $user_id . "'");
					
		if( !empty( $pin_id ) )
		{
			return true;
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
	public function findImages( $url )
	{
		if( !Sanitize::validatePHP( $url, 'url' ) )
		{
			throw new Exception( __('pinit_upload_website_error') );
		}
		
		require_once( ABS_PATH . '/functions-upload.php');
		require_once( IPS_ADMIN_PATH .'/libs/class.FastImage.php');
		require_once( IPS_ADMIN_PATH .'/import-functions.php');
					
		$html = get_link_content( array( $url ) );
		
		if( empty( $html ) )
		{
			throw new Exception( __('pinit_upload_website_no_images') );
		}
		
		$images = extract_html_tags( $html, 'img' );
		
		if( empty( $images ) )
		{
			throw new Exception( __('pinit_upload_website_no_images') );
		}
		
		$files_ready = array();
		
		$domain_url = 'http://' . parse_url( $url,  PHP_URL_HOST );
		
		foreach ( $images as $img )
		{
			$image = new FastImage( $img->getAttribute('src') );
							
			list( $width, $height ) = $image->getSize();

			if( empty( $width ) || ( $width > 200 && $height > 200 ) )
			{
				$files_ready[] = array
				(
					'upload_image' => !Sanitize::validatePHP( $img->getAttribute('src'), 'url' ) ? $domain_url . '/' . ltrim( $img->getAttribute('src'), '/' ) : $img->getAttribute('src'),
					'pin_title' => $img->getAttribute('alt'),
					'pin_source' => $url,
				);
			}
		}

		$videos = extract_youtube_links( $html );

		if( !empty( $videos ) )
		{
			$video = Ips_Registry::get( 'Video' );
			
			foreach ( $videos as $video_id )
			{
				$files_ready[] = array
				(
					'upload_image' => 'http://img.youtube.com/vi/' . $video_id . '/0.jpg',
					'pin_title' => '',
					'pin_source' => $url,
					'pin_video_url' => 'http://www.youtube.com/watch?v=' . $video_id,
					'pin_video' => $video->get( 'http://www.youtube.com/watch?v=' . $video_id, array(
						'width' 	=> 236,
						'height' 	=> 132,
						'embed'		=> true
					)))
				);
			}
		}	
		
		if( empty( $files_ready ) )
		{
			throw new Exception( __('pinit_upload_website_no_images') );
		}
		
		$files_ready = array_map( "unserialize", array_unique( array_map( "serialize", $files_ready ) ) );
		
		return $this->pinLayout( $files_ready, 'import_pins.html' );
	}
	/**
	* Display Pins
	*
	* @param 
	* 
	* @return 
	*/
	public function showPin( $pin_id, $pin = null )
	{
		$db = PD::getinstance();
		if( $pin == null )
		{
			$pin = $db->select( IPS__FILES, array( 
				'id' => $pin_id
			), 1 );
		}
		
		if( empty( $pin ) )
		{
			throw new Exception( "This Pin don't exists" );
		}
		
		$users_data = $boards_data = $comments_data = array();
		
		$this->pinUsers( $pin, $users_data );
		$this->pinBoards( $pin, $boards_data );
		//$this->pinComments( $pin, $comments_data );

		$pin['upload_image']	= ips_img( $pin['upload_image'], 'large' );
		$pin['pin_user']	= $users_data[ $pin['user_id'] ];
		$pin['board']		= $boards_data[ $pin['board_id'] ];
		$pin['date_add']	= formatDate( $pin['date_add'] );
		//$pin['pin_comments']= $comments_data[ $pin['id'] ];
		$pin['pin_comments'] = '';
		$pin['pin_source_url'] = false;
		
		$facebook_comments = '';

		if( Config::getArray( 'comments_options', 'type' ) == 'facebook' || Config::getArray( 'comments_options', 'type' ) == 'ajax_facebook' )
		{
			$facebook_comments = SocialButtons::comments( seoLink( $pin['id'], $pin['seo_link'] ), 736, 10 );
		}
				
		add_filter( 'init_js_files', function( $array ){
			return add_static_file( $array, array(
				'js/comments.js'
			)  );
		}, 10 );
		
		$pin['pin_comments_wrapper'] = Templates::getInc()->getTpl( 'comments.html', array(
			'current_user' => getUserInfo( USER_ID, true ),
			'facebook_comments' => $facebook_comments,
			'id' => $pin['id'],
		) );
		
		$b = new Board();
		
		/** Pins smilar by Source Website **/
		if( $pin['pin_source'] != 'pin_uploaded')
		{
			if( !empty( $pin['pin_source'] ) && Sanitize::validatePHP( $pin['pin_source'], 'url' ) )
			{
				$pin['pin_source_url'] = $pin['pin_source'];
			}
		}
		
		$pin['gallery_images'] = '';
		
		if( $pin['pin_type'] == 'gallery' )
		{
			$display = new Core_Display;
			$pin['gallery_images'] = $display->getGallery( $pin['id'], false, '' );
		}

		/** Related Pins list **/
		/** Get users ID that repined from this pin */

		/**/

		$pin['mod_links'] = $pin['is_author'] = false;
		
		if( USER_MOD )
		{
			$pin['mod_links'] = Operations::fileModeration( array(
				'id' => $pin['id'],
				'upload_activ' => true,
				'upload_status' => $pin['pin_privacy'],
				'up_lock' => $pin['up_lock'],
				'category_id' => $pin['category_id']
			) );
		}
		elseif( $pin['user_id'] == USER_ID )
		{
			$pin['is_author'] = true;
		}
		
		$pin['size'] = $this->getPinImagesSizes( $pin['upload_sizes'], 'originals' );
		
		if( !empty( $pin['upload_video'] ) )
		{
			$display = new Core_Display;
		
			$res = array(
				'id' => $pin['id'],
				'upload_video' => IPS_VIDEO_URL . '/' . $pin['upload_video'],
				'upload_type' => ( preg_match( '/\.(mp4)$/i', basename( $pin['upload_video'] ) ) ? 'mp4' : false ),
				'title' => $pin['pin_title'],
				'upload_image' => $pin['upload_image'],
			);
			
			$file_dims = array(
				'width' => 736,
				'height' => ( $pin['size']['h'] * 736 ) / $pin['size']['w'],
				'padding' => 0
			);
			
			
			
			
			
			
			if ( $res['upload_subtype'] == 'mp4' )
			{

				$pin['pin_video'] = Ips_Registry::get( 'Mp4', isset( $this->args['on_widget'] ) )->get( $res['upload_video'], array_merge( $file_dims, array(
					'id' 		=> $id,
					'onpage' 	=> $onpage,
					'video_url' => $this->getSeoLink( $res['id'] ),
					'video_title' => $res['title'],
					'upload_image' => $res['upload_image'],
				) ) );
			}
			else
			{
				/* $onpage = ( IPS_ACTION == 'file_page' && $id == IPS_ACTION_GET_ID ) || IPS_ACTION == 'pin';
				$autoplay = $loop = false;
				
				if ( ( IPS_ACTION == 'file_page' && $id == IPS_ACTION_GET_ID ) || IPS_ACTION == 'pin' )
				{
					$autoplay = Config::getArray( 'video_player', 'autoplay' );
					$loop     = Config::getArray( 'video_player', 'loop' );
				}
				 */
				$pin['pin_video'] = Ips_Registry::get( 'Video' )->get( $res['upload_video'], array_merge( $file_dims, array(
					'id' 		=> $id,
					'onpage' 	=> $onpage,
					'loop' 		=> $onpage ? Config::getArray( 'video_player', 'loop' ) : false,
					'autoplay' 	=> $onpage ? Config::getArray( 'video_player', 'autoplay' ) : false,
				) ) );
			}
			
			
			
			
			
			
			
			
			$video = $display->videoEmbed( $pin['id'], $res );
			
			/*
			* MP4 Video
			*/
			if( is_array( $video ) )
			{
				$video['video_height'] = $pin['size']['h'];
				$video['video_width'] = $pin['size']['w'];
				$pin['pin_video'] = Templates::getInc()->getTpl( 'item_video_mp4.html', $video );
			}
			else
			{
				$pin['pin_video'] = Templates::getInc()->getTpl( 'item_video.html', array_merge( $display->videoSizes,  array(
					'id'		=> $pin['id'],
					'title'		=> $pin['pin_title'],
					'video_img' => $pin['upload_image'],
					'embed'		=> $video,
					'max_w' 	=> 100,
					'max_h' 	=> 100,
				) ) );
			}
		}
		
		$pin['pin_tags'] = Tags::getFileTags( $pin['id'], true );

			
		if( IPS_ACTION == 'pin' )
		{
			$pin['ad_above_pin'] = AdSystem::getInstance()->showAd('above_file');
			$pin['ad_under_comments'] = AdSystem::getInstance()->showAd('under_comments');
			$pin['ad_under_pin'] = AdSystem::getInstance()->showAd('under_file');
			$pin['ad_right_pin_top'] = AdSystem::getInstance()->showAd('pin_side_block_top');
			$pin['ad_right_pin_bottom'] = AdSystem::getInstance()->showAd('pin_side_block_bottom');
			$pin['ad_right_pin_middle'] = AdSystem::getInstance()->showAd('pin_side_block_middle');
			$pin['ads_under_image_file'] = AdSystem::getInstance()->showAd('under_image_file');
		}
		else
		{
			$pin['ads_under_image_file'] = $pin['ad_above_pin'] = $pin['ad_under_comments'] = $pin['ad_under_pin'] = $pin['ad_right_pin_top'] = $pin['ad_right_pin_bottom'] = $pin['ad_right_pin_middle'] = '';
		}

		/* IMG size */
		if( $pin['size']['w'] > 736 )
		{
			$pin['size']['h'] = ( $pin['size']['h'] * 736 ) / $pin['size']['w'];
			$pin['size']['w'] = 736;
		}
		
		
		$pin['small_items_board'] = Tools::asyncLoad( 'board_related_pins', array(
			'board_id' => $pin['board_id']
		), 'loadSmallPanel', 1 );
		
		$pin['small_items_source'] = Tools::asyncLoad( 'related_pins', array(
			'pin_id'		=> $pin['id'],
			'related_by'	=> 'source'
		), 'loadSmallPanel', 2 );
	

		$pin['related_boards'] = Tools::asyncLoad( 'related_boards', array(
			'pin_id'		=> $pin['id']
		), 'loadRelatedBoards', 3 );
		
		
		$pin['related_pins'] = Tools::asyncLoad( 'related_pins', array(
			'pin_id'		=> $pin['id'],
			'related_by'	=> 'user_repinned',
			'item_layout'	=> 'normal'
		), 'loadRelatedPins', 4 );
		
		
		$pin['board_user_info'] = $b->getField( $pin['board_id'], "u.avatar, CONCAT( first_name,' ',last_name ) as full_name,u.login,b.*");

		$pin['board_user_info']['avatar'] = ips_user_avatar( $pin['board_user_info']['avatar'] );
		
		$u = User::getInstance();
		
		$pin['user_follow_board'] = $u->userFollowBoard( USER_ID, $pin['board_id'], $pin['board']['user_id'] );
		
		$pin['source_pins'] = ( $pin['pin_source'] != 'pin_uploaded' ? $db->cnt( IPS__FILES, array(
			'pin_source_hash' => $pin['pin_source_hash']
		)) : 0 );
		
		$pin['navigation_buttons'] = false;
		if( Config::get('widget_navigation_on_page') == 1 )
		{
			$pin['navigation_buttons'] = Widgets::navigationButtons( $pin['id'] );
		}
			
			
		return Templates::getInc()->getTpl( 'pin.html', $pin );
	
	}
	

	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function relatedPins( $pin_id, $pages = 50, $related_by = 'user_repinned' )
	{
		/** Related Pins list **/
		
		$db = PD::getinstance();
		

		switch( $related_by )
		{
			/** Get PINs by board ID */
			case 'board':
				
				$board_id = array_column( $db->select( IPS__FILES, array(
					'id' => $pin_id
				), 1, 'board_id'), 'board_id' );
				
				$related_pins = $db->select( IPS__FILES, array(
					'board_id' => $board_id,
					'id' => array( $pin_id, '!=' ),
				), $pages );
				
			break;
			/** Get users ID PINs that repined from this pin */
			case 'user_repinned':
				
				$user_ids = array_column( $db->select( IPS__FILES, array(
					'repin_from' => $pin_id
				), null, 'DISTINCT user_id'), 'user_id' );
				
				$related_pins = $db->select( IPS__FILES, array(
					'user_id' => $user_ids
				), $pages );
				
				return $this->displayFiles( $related_pins, 'compile' );
				
			break;
			/** Get PINs with smilar tags */
			case 'tags':
				
				$upload_ids = Tags::getSmilar( $pin_id, 10 );
			
				if( !empty( $upload_ids ) )
				{
					$related_pins = $db->select( IPS__FILES, array(
						'id' => $upload_ids,
						'id' => array( $pin_id, '!=' ),
						'repin_from' => array( $upload_ids, 'NOT IN' ) 
					), $pages );
				}
				
			break;
			case 'source':
				
				$pin = $db->select( IPS__FILES, array( 
					'id' => $pin_id
				), 1 );
			
			
				if( empty( $pin ) )
				{
					throw new Exception( "This Pin don't exists" );
				}
				
				$smilar_pins = $this->getPins( array(
					'pin_source_hash' => $pin['pin_source_hash'],
					'pin_id' => array( $pin['id'], '!=' ),
				), '*', $pages );
			
				if( !empty( $smilar_pins ) )
				{
					foreach( $smilar_pins as $key => $smilar_pin )
					{
						$smilar_pins[$key]['upload_image'] = ips_img( $smilar_pin['upload_image'], 'square' );
					}

					return $smilar_pins;
				}
			break;
		}
		

		if( !empty( $related_pins ) )
		{
			return $this->displayFiles( $related_pins );
		}

		return null;
		/**/
	}
	/**
	* Display Pins list
	*
	* @param 
	* 
	* @return 
	*/
	public function pinLayout( &$pins, $tpl_name = 'pin_list.html' )
	{
		return Templates::getInc()->getTpl( $tpl_name, array(
			'pins'			=> $pins,
			'display_title' => Config::getArray('template_settings', 'item_title' )
		));
	
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function displayFiles( &$pins, $option = 'render'  )
	{
		$users_data = $boards_data = $comments_data = array();
		
		$this->pinUsers( $pins, $users_data );
		$this->pinBoards( $pins, $boards_data );
		
		/* $this->pinComments( $pins, $comments_data ); */
		global ${IPS_LNG};
		foreach( $pins as $key => $pin )
		{
			$pins[$key]['sort_key'] = time() . $key;
			$pins[$key]['is_author'] = $pin['user_id'] == USER_ID;
			$pins[$key]['upload_image']		= ips_img( $pin['upload_image'], 'thumb' );
			$pins[$key]['upload_image_size']= $this->getPinImagesSizes( $pin['upload_sizes'], 'medium_thumb' );
			$pins[$key]['pin_user']			= $users_data[ $pin['user_id'] ];
			$pins[$key]['pin_user']['text'] = isset( $pin['repin_from'] ) && !empty( $pin['repin_from'] ) ? ${IPS_LNG}['pinit_pin_repinned_by'] : ${IPS_LNG}['pinit_pin_pinned_by'];
			$pins[$key]['board']			= $boards_data[ $pin['board_id'] ];
			$pins[$key]['pin_comments'] 	= $pin['comments'];
		}
		
		if( $option == 'compile' )
		{
			return array( 
				'items'		=> $pins,
				'template'	=> 'pin_list'
			);
		}
		
		return $this->pinLayout( $pins );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function pinUsers( &$pins, &$users_data )
	{
		$user_ids = isset( $pins['user_id'] ) ? array( $pins['user_id'] => $pins['user_id'] ) : array_column( $pins, 'repin_from', 'user_id' );
		
		$users_data = User::getInstance()->usersModel( $user_ids );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function pinBoards( &$pins, &$boards_data )
	{
		$board_ids = isset( $pins['board_id'] ) ? array( 'board_id' => $pins['board_id'] ) : array_unique( array_column( $pins, 'board_id' ) ) ;
		
		$b = new Board();
				
		$b->boardsModel( array_flip( $board_ids ), $boards_data, true );
		
		unset( $b, $boards );
	}
	
	/**
	* Select comments for pins list
	*
	* @param 
	* 
	* @return 
	*/
	public function pinComments( &$pins, &$comments_data, $limit = false )
	{
		$pin_ids = isset( $pins['id'] ) ? array( $pins['id'] ) : array_column( $pins, 'id' );
		$db = PD::getinstance();

		foreach( $pin_ids as $key => $pin_id )
		{
			$comments = new Comments( $pin_id, false, $limit );
			$comments_data[ $pin_id ] = $comments->get( $pin_id, array(
				'limit' => $limit
			) )->load();
		}
		
		unset( $comments );
	}
	
	/**
	* Delete pin
	*
	* @param $reload_board set to tru if only delete pin, not whole board
	* 
	* @return bool
	*/
	public function delete( $data, $reload_board = true )
	{
		$pin_id = isset( $data['id'] ) ? (int)$data['id'] : false;
		
		if( !$pin_id )
		{
			throw new Exception('Wrong Pin ID');
		}
		
		$db = PD::getinstance();
		
		$pin_info = $this->getPin( $pin_id );
		
		if( !$pin_info )
		{
			throw new Exception( $pin_info );
		}
		
		$delete = $db->delete( IPS__FILES, array(
			'id' => $pin_id
		));
		
		if( !$delete )
		{
			throw new Exception( 'Mysql Error' );
		}
		else
		{
			$db->delete( array( 'pinit_pins_likes', 'pinit_places_pins' ), array(
				'pin_id' => $pin_id
			));

			if( $reload_board )
			{
				$b = new Board();
				$b->updateBoard( $pin_info['board_id'], $pin_info );
			}
			
			$this->deletePinFiles( $pin_info );
			
			return true;
		}
		
	}
	
	public function deletePinFiles( $pin_info )
	{

		Operations::deleteImages( $pin_info );
		
		if( !empty( $pin_info['upload_video'] ) && preg_match( '@^([0-9]*)/([0-9]*)/([^.]{1,})\.([0-9a-zA-Z]{1,})@iu', $pin_info['upload_video'] )  )
		{
			File::deleteFile( IPS_VIDEO_PATH . '/' . $pin_info['upload_video'] );
		}
		
		return true;
	}
	/**
	* Get user or board latest added pins
	*
	* @param 
	* 
	* @return 
	*/
	public function latestPins( $data = array( 'user_id' => USER_ID ) )
	{

		if( !empty( $data ) )
		{
			$db = PD::getInstance();
			
			$pins = $this->getPins( array_merge( $data, array(
				'order_by' => 'pinit_pins.date_add',
				'limit' => 15
			) ), 'id');
			
			$latest_add = array();
			
			if( $pins )
			{
				foreach( $pins as $pin )
				{
					$latest_add[] = $pin['id'];
				}
			}
		}
		return $latest_add;
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public static function like( $data )
	{
		if( !isset( $data['pin_id'] ) || empty( $data['pin_id'] ) )
		{
			throw new Exception('Empty Pin ID');
		}
		if( !isset( $data['pin_like'] ) )
		{
			throw new Exception('Empty User action Like/Unlike');
		}
		
		$db = PD::getInstance();
		
		$db->delete( 'pinit_pins_likes', array(
			'pin_id'	=> $data['pin_id'],
			'user_id'	=> USER_ID
		));
		
		if( $data['pin_like'] == 'true' )
		{
			$db->insert( 'pinit_pins_likes', array(
				'pin_id'	=> $data['pin_id'],
				'user_id'	=> USER_ID
			));
		}
		
		$db->update( IPS__FILES, array(
			'pin_likes'	=> $db->cnt( 'pinit_pins_likes', 'pin_id =' . $data['pin_id'] )
		), array(
			'id' => $data['pin_id'],
		));
		
		$db->update( 'users', array(
			'user_likes' => $db->cnt( 'pinit_pins_likes', 'user_id =' . USER_ID )
		), array(
			'id' => USER_ID,
		));
		
		/* 
		$db->update( 'users', array(
			'user_likes' => $db->cnt( 'pinit_pins_likes', 'pin_id =' . $data['pin_id'] )
		), array(
			'id' => "(SELECT user_id FROM ".IPS__FILES." WHERE id = " . $data['pin_id'] . ")",
		)); 
		*/
		
		return true;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public static function getField( $pin_id, $fields )
	{
		$db = PD::getInstance();
		
		$pins = $db->select(IPS__FILES, array( 
			'id' => $pin_id
		)), 1, $fields );

		return ( count( $pins ) > 0 ? ( strpos( $fields, ',' ) === false ? $pins[$fields] : $pins ) : false );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getPin( $pin_id, $fields = '*' )
	{
		return $this->getPins( array( 
			'pin_id' => $pin_id
		), $fields, 1);
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getPins( $data, $fields = '*', $limit = null )
	{
		$conditions = array();
		
		$order = null;
		
		if( isset( $data['order_by'] ) )
		{
			$order = $data['order_by'];
			unset( $data['order_by'] );
		}

		if( isset( $data['pin_id'] ) )
		{
			$conditions[ 'up.id'] = $data['pin_id'];
		}
		
		if( isset( $data['board_id'] ) )
		{
			$conditions[ 'up.board_id'] = $data['board_id'];
		}
		
		if( isset( $data['pin_source'] ) )
		{
			$conditions[ 'up.pin_source_hash'] = md5( $data['pin_source'] );
		}
		
		if( isset( $data['pin_source_hash'] ) )
		{
			$conditions[ 'up.pin_source_hash'] = $data['pin_source_hash'];
		}
		
		if( isset( $data['repin_from'] ) )
		{
			$conditions[ 'up.repin_from'] = "'". $data['repin_from'] . "'";
		}

		$db = PD::getInstance();
		
		$pins = $db->select( IPS__FILES . ' up', $conditions, $limit, $fields, $order );

		return ( count( $pins ) > 0 ? $pins : false );
	}
	
	/**
	* Return size of all Pin images decoded from JSON
	*
	* @param $sizes
	* 
	* @return json object
	*/
	public function getPinImagesSizes( $json_sizes, $size )
	{
		return (array)json_decode( $json_sizes )->$size;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	
	public function generateTags( $pin_id, $repin_from, $pin_description )
	{
		if( !empty( $repin_from ) )
		{
			$tags = Tags::getFileTags( $repin_from );
		}
		
		if( !isset( $tags ) || empty( $tags ) )
		{
			$tags = implode( ',', array_keys( array_flip( preg_split("/[\s,]+/" , Sanitize::onlyAlphanumeric( mb_strtolower( $pin_description, 'UTF-8'), " " ) ) ) ) );
		}
		
		return Ips_Registry::get( 'Upload_Tags' )->saveTags( $tags, $pin_id );

	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	
	public function feature( $pin_id )
	{
		$db = PD::getInstance();
		
		$pin = $db->select(IPS__FILES, array(
			'id' => $pin_id
		), 1 );
		
		return $db->update(IPS__FILES, array(
			'pin_featured' => ( $pin['pin_featured'] == 1 ? 0 : 1 )
		), array(
			'id' => $pin_id
		) );
		
	}
	
	/**
	* Return size of all Pin images to optimize later display
	*
	* @param $file_name
	* 
	* @return json string
	*/
	public function pinImagesSizes( $file_name )
	{
		$image = array( 
			'upload_image' => $file_name
		);
		
		$originals		= getimagesize( ips_img_path( $image, 'large' ) );
		$medium			= getimagesize( ips_img_path( $image, 'medium' ) );
		$medium_thumb	= getimagesize( ips_img_path( $image, 'thumb' ) );
		$small_thumb	= getimagesize( ips_img_path( $image, 'thumb-small' ) );
		$mini_thumb		= getimagesize( ips_img_path( $image, 'thumb-mini' ) );

		
		return json_encode(array(
			'mini_thumb'	=> array(
				'w' => $mini_thumb[0],
				'h' => $mini_thumb[1],
			),
			'small_thumb'	=> array(
				'w' => $small_thumb[0],
				'h' => $small_thumb[1],
			),
			'medium'		=> array(
				'w' => $medium[0],
				'h' => $medium[1],
			),
			'medium_thumb'	=> array(
				'w' => $medium_thumb[0],
				'h' => $medium_thumb[1],
			),
			'originals'		=> array(
				'w' => $originals[0],
				'h' => $originals[1],
			),
		));
	}
	
	/**
	* Gets pin image dominant color and saves to DB
	*
	* @param 
	* 
	* @return 
	*/
	public function getDominantColor( $image )
	{

		$image = Gd::as_resource( $image );
		
		$pixel = imagecreatetruecolor( 1, 1);
		imagecopyresampled( $pixel, $image, 0, 0, 0, 0, 1, 1, imagesx( $image ), imagesy( $image ) );
		$rgb = imagecolorat( $pixel, 0, 0 );
		$color = imagecolorsforindex( $pixel, $rgb );
		
		return sprintf( '%02s%02s%02s', dechex( $color['red'] ), dechex( $color['green'] ), dechex( $color['blue'] ) );
	}
}
?>