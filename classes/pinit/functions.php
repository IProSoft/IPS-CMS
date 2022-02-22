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
	
	function pinit_pin()
	{
		try{
			
			$pin = new Pin();
			return array( 
				'modal_content' => $pin->showPin( $_GET['query'] )
			);
			
		} catch ( Exception $e ) {
			
			return array( 
				'modal_info' => $e->getMessage()
			);
			
		}
	}
	
	
	
	/**
	* Creates new pin
	*/
	function pinit_create_pin()
	{
		try{
			$pin = new Pin();
			$response = $pin->create( $_POST );
		} catch ( Exception $e ) {
			return  array( 
				'modal_info' => $e->getMessage()
			) ;
		}
		
		return array( 
			'modal_replace' => true, 
			'modal_content' => Templates::getInc()->getTpl( '/modals/pined_success.html', $response )
		);
	}
	
	function pinit_upload()
	{
		return array( 
			'modal_title' => __( 'pinit_upload' ), 
			'modal_content' => Templates::getInc()->getTpl( '/modals/upload.html' )
		);
	}
	
	function pinit_upload_pin()
	{
		return array( 
			'modal_title' => __( 'pinit_upload_pin' ), 
			'modal_content' => Templates::getInc()->getTpl( '/modals/upload_pin.html' )
		);
	}
	
	function pinit_upload_url()
	{
		return array(
			'modal_title' => __( 'pinit_upload_website_info' ),
			'modal_content' => Templates::getInc()->getTpl( '/modals/upload_url.html' )
		);
	}
	function pinit_upload_video()
	{
		return array(
			'modal_title' => __( 'pinit_upload_video_info' ),
			'modal_content' => Templates::getInc()->getTpl( '/modals/upload_video.html' )
		);
	}
	
	function pinit_upload_gallery()
	{
		return array(
			'modal_title' => __( 'pinit_upload_gallery_info' ),
			'modal_content' => Templates::getInc()->getTpl( '/modals/upload_gallery.html' )
		);
	}
	function pinit_upload_gallery_full()
	{
		return array_merge( array( 
			'modal_title' => __( 'pinit_upload_gallery_info' ),
		), pinit_upload_pin_full( array_merge( array(
			'gallery_files' => true
		), $_POST ) ) );
	}
	function pinit_upload_board()
	{

		return array(
			'modal_title' => __( 'pinit_upload_board' ),
			'modal_content' => Templates::getInc()->getTpl( '/modals/upload_board.html', array(
				'board_id' => '',
				'board_title' => '',
				'board_description' => '',
				'board_privacy' => ( isset( $_GET['query'] ) && $_GET['query'] == 'private' ? 'private' : 'public' ),
				'categories_list' => Categories::categorySelectOptions(),
			) )
		);
	}
	
	/**
	* Form to upload Pin or repin existing
	*/
	function pinit_upload_pin_full( $post_data = null )
	{
		if( $post_data == null )
		{
			$post_data = $_POST;
		}
		
		$upload_file = ( isset( $post_data['images'] ) ? $post_data['images'] : ( isset( $post_data['video'] ) ? $post_data['video'] : false ) );
		
		if( $upload_file )
		{
			$info = isset( $_POST['upload_info'][0] ) ? $_POST['upload_info'][0] : null;
					
			if( isset( $info['error'] ) )
			{
				return array( 
					'modal_info' => $info['error']
				);
			}
			
			if( is_array( $upload_file ) )
			{
				if( file_exists( IPS_TMP_FILES . '/' . current( $upload_file ) ) )
				{
					array_walk( $upload_file, function( &$item ){
						$item = '/upload/tmp/' . $item;
					});

					if( !isset( $post_data['gallery_files'] ) )
					{
						$upload_file = reset( $upload_file );
					}
				}
				/** URL file */
				else
				{
					$upload_file = urldecode( current( $upload_file ) );
				}
			}
			elseif( file_exists( ips_img_path( $upload_file, 'large' ) )  )
			{
				$upload_file = ips_img( $upload_file, 'large' );
			}
			elseif( isset( $post_data['repin_from'] ) )
			{
				$pin = new Pin();
				$upload_file = ips_img( $pin->getField( intval( $post_data['repin_from'] ), 'upload_image' ), 'large' );
			}
			else
			{
				return array( 
					'modal_info' => 'error'
				);
			}
		
			$boards_list = pinit_user_boards_list();
			
			$content = Templates::getInc()->getTpl( '/modals/upload_pin_full.html',  array( 
				'thumb'				=> $upload_file,
				'pin_title'			=> isset( $post_data['pin_title'] ) ? $post_data['pin_title'] : '',
				'repin_from'		=> isset( $post_data['repin_from'] )	? $post_data['repin_from']	: '',
				'pin_from_url'		=> isset( $post_data['pin_from_url'] ) ? $post_data['pin_from_url']	: '',
				'image_name'		=> urlencode(  is_array( $upload_file ) ? current( $upload_file ) : $upload_file ),
				'upload_video'		=> isset( $post_data['upload_video'] ) ? urlencode( $post_data['upload_video'] ) : false,
				'create_board_list' => $boards_list['modal_content']
			) );
			
			return array( 
				'modal_content' => $content
			);
		}
		return array( 
			'modal_info' => __( 'pinit_error' )
		);
	}
	
	/**
	* Form to upload Pin or repin existing
	*/
	function pinit_edit_pin_form()
	{
		if( isset( $_POST['pin_id'] ) )
		{
			$pin = new Pin();
			$data = $pin->getPin( $_POST['pin_id'] );
			
			if( !empty( $data ) )
			{
				$image = ips_img( $data['upload_image'], 'thumb' );
				
				
				$boards_list = pinit_user_boards_list();
			
				$content = Templates::getInc()->getTpl( '/modals/upload_pin_full.html', array( 
					'pin_id'			=> $data['id'],
					'pin_id_edit'		=> $data['id'],
					'pin_board_id'		=> $data['board_id'],
					'thumb'				=> urldecode( $image ),
					'pin_title'			=> $data['pin_title'],
					'repin_from'		=> isset( $data['repin_from'] )	? $data['repin_from']	: '',
					'pin_from_url'		=> isset( $data['pin_from_url'] ) ? $data['pin_from_url']	: '',
					'image_name'		=> urlencode( $image ),
					'upload_video'		=> isset( $data['upload_video'] ) ? urlencode( $data['upload_video'] ) : false,
					'create_board_list' => $boards_list['modal_content']
				) );

				return array( 
					'modal_title' => 'Edytuj Pina',
					'modal_content' => $content
				);
			}
		}
	}
	/**
	* Saves edit changes to Pin
	*/
	function pinit_edit_pin()
	{
	
		try{
			$pin = new Pin();
			$response = $pin->edit( $_POST );
		} catch ( Exception $e ) {
			return  array( 
				'modal_info' => $e->getMessage()
			) ;
		}
		
		return array( 
			'modal_set_success' => true,
			'modal_call' => '$(\'.item[data-file-id="' . $_POST['pin_id_edit'] . '"]\').find(".pin-description").html("' . $response['pin_description'] . '")'
		);
	}
	

	function pinit_delete_pin()
	{
		if( isset( $_POST['pin_confirm'] ) && !empty( $_POST['pin_confirm'] ) )
		{
			try{
				
				$pin = new Pin();
				$pin_info = $pin->delete( array(
					'id' => (int)$_POST['pin_id']
				) );
				
			} catch ( Exception $e ) {
				return array( 
					'modal_info' => $e->getMessage()
				);
			}
			
			return array(
				'modal_info_title' => __( 'pinit_pin_delete' ),
				'modal_info' => __( 'pinit_pin_deleted' ),
				'modal_success' => true
			);
		}
		else
		{
			return array(
				'modal_wait' => true,
				'modal_buttons' => true,
				'modal_info_title' => __( 'pinit_pin_delete' ),
				'modal_info' => Templates::getInc()->getTpl( '/modals/delete_pin_confirm.html', array(
					'pin_id' => (int)$_POST['pin_id']
				))
			);
		}
	}
	/**
	* Pin image from import or repin
	*/
	function pinit_pin_it()
	{
		return array_merge( array( 
			'modal_title' => __( 'pinit_upload_website_info' )
		), pinit_upload_pin_full( $_POST ) );
	}
	
	/**
	* Pin image from import or repin
	*/
	function pinit_re_pin()
	{
		return array_merge( array( 
			'modal_title' => __( 'pinit_upload_board_chose' )
		), pinit_upload_pin_full( $_POST ) );
	}
	
	
	/**
	* Find images in WEBSITE
	*/
	function pinit_find_images()
	{
		try{
			$pin = new Pin();
			$images = $pin->findImages( $_POST['find_images_url'] );
		} catch ( Exception $e ) {
			return  array( 'modal_info' => $e->getMessage() ) ;
		}
		
		return array( 
			'modal_function' => 'importImages', 
			'modal_content' => $images,
			'modal_target' => '#all_page'
		);
	}
	
	
	function pinit_upload_video_url()
	{
		try {	
			
			$upload_video = $_POST['upload_video_url'];
			

			if( preg_match( '/\.(mp4)$/i', basename( $upload_video ) ) || preg_match( '/\.(mp4)\?(.*)/i', basename( $upload_video ) ) )
			{
				$upload_mp4 = new Upload_Mp4();
				
				$video_data = $upload_mp4->getUpload( array(
					'url' => $upload_video
				));

				if( empty( $video_data['upload_video'] ) )
				{
					throw new Exception('err_video');
				}
			}
			else
			{
				$video = new Upload_Video();
				$video_data = $video->videoParseUrl( $upload_video );
			}
			
			if ( !$video_data )
			{
				throw new Exception('err_video_link');
			}
			
			return pinit_upload_pin_full( array( 
				'video' => array(
					$video_data['image']
				), 
				'pin_from_url' => $_POST['upload_video_url'],
				'upload_video' => ( file_exists( IPS_TMP_FILES . '/' . basename( $video_data['upload_video'] ) ) ? 'upload/tmp/' . basename( $video_data['upload_video'] ) : $video_data['upload_video'] )
			) );
		
		} catch ( Exception $e ) {
			global ${IPS_LNG};
			return array( 
				'modal_info' => ( isset( ${IPS_LNG}[ $e->getMessage() ] ) ? ${IPS_LNG}[ $e->getMessage() ] : $e->getMessage() )
			);
		}
	}
	function pinit_upload_video_file()
	{
		try {
			if( isset( $_POST['video'] ) )
			{
				$image = false;

				if( is_array( $_POST['video'] ) )
				{
					if( file_exists( IPS_TMP_FILES . '/' . $_POST['video'][0] ) )
					{
						$video_file = $_POST['video'][0];
						if( rename( IPS_TMP_FILES . '/' . $video_file, IPS_TMP_FILES . '/' . $video_file . '.mp4' ) )
						{
							$video_file = ABS_URL . 'upload/tmp/' . $video_file . '.mp4';
						}
					}
				}
				
				if( isset( $_POST['upload_info'] ) )
				{
					$info = $_POST['upload_info'][0];
					
					if( isset( $info['error'] ) )
					{
						throw new Exception( $info['error'] );
					}
					
					if( $info['size'] > Config::get('add_max_file_size') * 1048576)
					{
						throw new Exception( __( 'err_file_size' ) );
					}
					
					if( $video_file )
					{
						$upload_mp4 = new Upload_Mp4();
						
						$video_data = $upload_mp4->getInfo( $video_file );
						
						return pinit_upload_pin_full( array( 'video' => array(
								basename( $video_data['image'] )
							),
							'upload_video' => 'upload/tmp/' . basename( $video_data['upload_video'] )
						) );
			
					}
					ips_log( '$_POST data while upload video was not clear' . "\n" );
				}
				ips_log( '$_POST[upload_info] while upload video was empty' . "\n" );
			}
			
			throw new Exception( __( 'pinit_error' ) );
			
		} catch ( Exception $e ) {
			global ${IPS_LNG};
			return array( 
				'modal_info' => ( isset( ${IPS_LNG}[ $e->getMessage() ] ) ? ${IPS_LNG}[ $e->getMessage() ] : $e->getMessage() )
			);
		}

	}
	function pinit_user_boards_list()
	{
		$content = Templates::getInc()->getTpl( '/modals/create_board_simple.html' );
		
		$board = new Board();
		$boards = $board->userBoards( USER_ID );

		if( $boards )
		{
			$tpl = Templates::getInc();
			$tpl->assign( array( 
				'user_boards' => $boards,
				'create_board_simple' => $content
			) );
			
			$content = $tpl->getTpl( '/modals/create_board_user_boards.html' );
		}
		return array(
			'modal_content' => $content
		);
	}
	
	
	
	/** Delete board with confirm window */
	
	function pinit_delete_board()
	{
		if( isset( $_POST['board_confirm'] ) && !empty( $_POST['board_confirm'] ) )
		{
			try{
				$board = new Board();
				$board_info = $board->delete( array(
					'board_id' => (int)$_POST['board_id']
				) );
			} catch ( Exception $e ) {
				return array( 
					'modal_info' => $e->getMessage()
				);
			}
			
			return array(
				'modal_info_title' => __( 'pinit_board_delete' ),
				'modal_info' => __( 'pinit_board_deleted' ),
				'modal_success' => true
			);
		}
		else
		{
			$db = PD::getInstance();
			
			$tpl = Templates::getInc();
			$tpl->assign( array(
				'board_id' => (int)$_POST['board_id'],
				'count_pins' => __s( 'pinit_board_delete_confirm_full', $db->cnt( IPS__FILES, 'board_id =' . (int)$_POST['board_id'] ) )
			) );

			return array(
				'modal_wait' => true,
				'modal_buttons' => true,
				'modal_info_title' => __( 'pinit_board_delete_confirm' ),
				'modal_info' => $tpl->getTpl( '/modals/delete_board_confirm.html' )
			);
		}
	}
	
	/** Add pinner to board */
	function pinit_pinner()
	{
		try{
			$board = new Board();
			$board_info = $board->addPinner( $_POST );
		} catch ( Exception $e ) {
			return  array( 'modal_info' => $e->getMessage() ) ;
		}
		return array(
			'modal_info' => __( 'pinit_add_pinner' )
		);
	}
	
	
	function pinit_edit_board_form()
	{
		try{
			$board = new Board();
			$board_info = $board->boards( array(
				'board_id' => $_POST['board_id'],
				'limit' => 1
			) );
		} catch ( Exception $e ) {}
		
		if( empty( $board_info ) )
		{
			return false;
		}
		
		$board_info['categories_list'] = Categories::categorySelectOptions( $board_info['category_id'] );
		
		$board_info['users_can_pin'] = $board->boardUsers( $_POST['board_id'], array( 
			'allow_pin' => 1,
			'user_id' => array(
				$board_info['user_id'], '!='
			),
		) );

		$board_info['board_author'] = User::getInstance()->usersModel( array( 
			'user_id' => $board_info['user_id'] 
		));
		
		$board_info['board_author'] = $board_info['board_author'][ $board_info['user_id'] ];
		$board_info['board_author']['invited_by'] = array(
			'full_name' => __( 'pinit_user_self' ),
			'link'		=> ABS_URL . 'profile/' . $board_info['board_author']['login']
		);
		
		array_unshift( $board_info['users_can_pin'], $board_info['board_author'] );
		
		return array( 
			'modal_title' => __( 'pinit_board_edit' ), 
			'modal_content' => Templates::getInc()->getTpl( '/modals/upload_board.html', $board_info )
		);
	}
	
	function pinit_create_board()
	{
		try{
			$board = new Board();
			if( isset( $_POST['board_id'] ) )
			{
				if( $board->edit( $_POST ) )
				{
					return array( 
						'modal_info' => __( 'pinit_board_edited' ),
						'modal_info_title' => __( 'pinit_success' ),
						'modal_success' => true
					);
				}
			}
			else
			{
				$response = $board->create( $_POST );
			}
		} catch ( Exception $e ) {
			return array( 
				'modal_info' => $e->getMessage()
			);
		}
		
		if( isset( $_POST['board_upload'] ) && $_POST['board_upload'] == 'true' )
		{
			$boards_list = pinit_user_boards_list();
			return array( 
				'modal_content' => array( 
					'board_info' => $response, 
					'board_list' => $boards_list['modal_content']
				)
			);
		}
		
		return array( 
			'modal_redirect' => $board->boardUrl( $response['board_id'] )
		);
	}
	

	function pinit_related_boards()
	{
		$pin_id = get_input('pin_id');
		
		if( $pin_id && IPS_ACTION_PAGE )
		{
			$pages = xy( IPS_ACTION_PAGE, 4 );
			
			$b = new Board();
			$response = $b->relatedBoards( $pin_id, $pages );
			
			return array( 
				'load_scroll' => true,
				'modal_content' => $response['blocks'],
				'modal_count'	=> $response['count']
			);
		}
	}
	
	function pinit_related_pins()
	{
		$pin_id = get_input('pin_id');
		
		if( $pin_id && IPS_ACTION_PAGE )
		{
			$pages = xy( IPS_ACTION_PAGE, 20 );
			
			try{
				
				$pin = new Pin();
				
				$items = $pin->relatedPins( $pin_id, $pages, $_GET['related_by'] );
				if( is_array( $items ) && isset( $items['items'] ) )
				{	
					$items = $items['items'];
				}
				
				return array( 
					'compile' => ( get_input('item_layout') == 'normal' ? 'pin_list' : 'doT/small_item' ),
					//'load_scroll' => true,
					'load_scroll' => (count( $items ) >= 20 ),
					'modal_content' => $items,
					'display_title' => Config::getArray('template_settings', 'item_title' )
				);
				
			} catch ( Exception $e ) {}
			
		}
		
		return array( 
			'modal_content' => ''
		);
	}
	
	function pinit_board_related_pins()
	{
		$board_id = get_input('board_id');
		
		if( $board_id && IPS_ACTION_PAGE )
		{
			$pages = xy( IPS_ACTION_PAGE, 20 );
			
			try{
				
				$b = new Board();

				$items = $b->getBoardItems( $board_id, '*', $pages );
				
				/**
				* Small pins at right Panel on PIN page
				*/
				return array( 
					'compile' => 'doT/small_item',
					'load_scroll' => true,
					//'load_scroll' => ( count( $items ) >= 20 ),
					'modal_content' => $items
				);
				
			} catch ( Exception $e ) {}
			
		}
		
		return array( 
			'modal_content' => ''
		);
	}
	
	/**
	* Follow/Unfollow user/board functions
	*/
	
	function pinit_follow_all()
	{
		return follow_helper( 'followBoards', 'user_id', 'follow' );
	}
	
	function pinit_unfollow_all()
	{
		return follow_helper( 'followBoards', 'user_id', 'unfollow' );
	}
	
	function pinit_follow_board()
	{
		return follow_helper( 'followBoard', 'board_id', 'follow' );
	}
	
	function pinit_unfollow_board()
	{
		return follow_helper( 'followBoard', 'board_id', 'unfollow' );
	}
	
	function pinit_follow_user()
	{
		return follow_helper( 'followUser', 'user_id' );
	}
	
	function pinit_unfollow_user()
	{
		return follow_helper( 'followUser', 'user_id' );
	}
	
	function follow_helper( $method_name, $data_id, $force_action = null )
	{
		if( !isset( $_POST[ $data_id ] ) || empty( $_POST[ $data_id ] ) )
		{
			return array(
				'modal_info' => __( 'pinit_error' )
			);
		}
		
		try{
			$u = new PinUser();
			$u->{$method_name}( USER_ID, $_POST[ $data_id ], $force_action );
			unset( $class );
		} catch ( Exception $e ) {
			return array(
				'modal_info' => $e->getMessage()
			);
		}
		
		return array(
			'modal_content' => 'true'
		);
	}
	
	
	/**
	* Retrun Alert HTML template to render in JS
	*/
	function pinit_alert()
	{
		$tpl = Templates::getInc();
		
		$tpl->assign( array(
			'load_button' => ( isset( $_POST['form'] ) && $_POST['form'] == 'alertFormWithoutButton' )
		) );
		
		return array(
			'modal_content' => $tpl->getTpl( '/modals/modal_alert.html' )
		);
	}
	
	/**
	* Like/Unlike
	*/
	function pinit_vote()
	{
	
	}
	
	/**
	* Form to sen PIN to firend/email
	*/
	function pinit_send()
	{
		$info = getUserInfo( USER_ID, true );

		$info['send_type'] = isset( $_GET['type'] ) ? $_GET['type'] : 'send-pin';
	
		return array( 
			'modal_content' => Templates::getInc()->getTpl( '/modals/send.html', $info )
		);
	}
	
	/**
	* Like/Unlike
	*/
	function pinit_like()
	{
		if( isset( $_POST['pin_id'] ) )
		{
			try{
			
				Pin::like( $_POST );
				
			} catch ( Exception $e ) {
				var_dump($e);exit;
				return  array( false ) ;
			}
		}
		return  array( true ) ;
	}
	
	/**
	* Send mesdsage to user
	*/
	function pinit_send_message()
	{
		if( isset( $_POST['user_id'] ) )
		{
			try{
			
				$user = new PinUser();
				$user->send( 'message', $_POST );
				
				return array(
					'modal_info' => __( 'pinit_user_message_sent' ),
					'modal_info_title' => __( 'pinit_success' ),
				);
			
			} catch ( Exception $e ) {
				
				return array(
					'modal_info' => __( 'pinit_user_message_sent_false' )
				);
			}
		}
		return array(
			'modal_info' => __( 'pinit_error' )
		);
	}
	
	/**
	* Send Email
	*/
	function pinit_send_email()
	{
		if( isset( $_POST['user_email'] ) )
		{
			try{
			
				$user = new PinUser();
				$user->send( 'email', $_POST );
				
				return array( 
					'modal_info' => __( 'pinit_user_message_sent' ),
					'modal_info_title' => __( 'pinit_success' ),
				);
			
			} catch ( Exception $e ) {
				
				return array( 
					'modal_info' => __( 'pinit_user_message_sent_false' )
				);
			}
		}
		return array(
			'modal_info' => __( 'pinit_error' )
		);
	}
	
	/**
	* List of users smilar to $_POST variable, ex Tom - Tomas, Tomasz
	*/
	function pinit_find_people()
	{
		$user = new PinUser();
		
		$found = $user->users( array( 
			'name_like' => $_GET['query'], 
			'email_like' => $_GET['query'],
			'id_not_like' => USER_ID,
		) );
		
		return $found ;
	}
	/**
	* Pagaination with Infinity Scrill
	*/
	function pinit_items()
	{
		if( isset( $_GET['query'] ) && !empty( $_GET['query'] ) )
		{
			$scroll_action = $_GET['query'];
			
			define( 'IPS_ONSCROLL', true );
			
			switch( $scroll_action )
			{
				case 'categories':
					$categories = new Categories();
					echo $categories->loadCategory( intval( $_GET['category_id'] ) );
				break;
				default:
					
					if( isset( $_GET['user_profile'] ) && $_GET['user_profile'] == 'true' )
					{
						$_GET['login'] = $scroll_action;
						$scroll_action = 'user_' . $_GET['sort'];
					}
				
					$display = new Core_Query();
					$display->init( $scroll_action, array(
						'display' => false
					));

					$display->files = $display->pinitContent( 'compile' );

					if( !is_array( $display->files ) )
					{
						return array(
							'modal_content' => $display->files
						);
					}

					return array(
						'compile' => $display->files['template'],
						'modal_content' => $display->files['items']
					);
					
				break;
			}			
		}
	}
	/**
	* Load user notifications list 
	*/
	function pinit_notifications()
	{
		$notify = new Notify();
		
		return array( 
			'modal_content' => $notify->notifications( USER_ID )
		);
	}
	
	
	function pinit_login_form()
	{
		return array( 
			'modal_info_title' => __( 'pinit_login_or_register' ),
			'modal_wait' => true,
			'modal_info' => Templates::getInc()->getTpl( '/modals/alert_register.html', array() )
		);
	}
	
	function pinit_confirm_email()
	{
		$user_info = getUserInfo( USER_ID, true );
		
		$user = new Users();
		$send = $user->resendActivation( $user_info['email'] );
		
		return array(
			'message' => __( $send['message'] )
		);
	}
	
	function pinit_places()
	{
		if( isset( $_GET['place_action'] ) )
		{
			try{
				
				$places = new Places();
				
				$response = $places->get( $_GET['place_action'], array_merge( $_GET, $_POST ) );
				
				if( is_array( $response ) )
				{
					return $response;
				}
				
				return array(
					'modal_content' => $response
				);
				
			} catch ( Exception $e ) {
				return  array( 
					'modal_info' => $e->getMessage()
				) ;
			}
		}
	}
	
	function pinit_user_email_change()
	{
		if( isset( $_POST['user_email'] ) )
		{
			if ( !Sanitize::validatePHP( $_POST['user_email'], 'email' ) )
			{
				return  array( 
					'modal_info' => __('user_wrong_email')
				) ;
			}
			
			if( PD::getInstance()->update( 'users', array( 'email' => $_POST['user_email'] ), array( 'id' => USER_ID ) ) )
			{
				return array(
					'modal_set_success' => true
				);
			}
		}
		
		return  false;
	}
	
	function pinit_edit_cover()
	{
		$board_id = ( isset( $_GET['board_id'] ) ? intval( $_GET['board_id'] ) : false );
		
		if( $board_id )
		{
			try{
				$board = new Board();
		
				$board_info = $board->boards( array(
					'board_id' => $board_id,
					'limit' => 1
				) );
			} catch ( Exception $e ) {}
			
			if( empty( $board_info ) )
			{
				return false;
			}
			$pins = new Pin();
			
			$board_items = $pins->getPins( array( 
				'board_id' => $board_id
			), 'id,upload_image', false );
			
			if( empty( $board_items ) )
			{
				return array( 
					'modal_info' => 'Nie ma żadnego obrazu na tablicy'
				);
			}
			
			foreach( $board_items as $key => $item )
			{
				$board_items[ $key ]['upload_image_url'] = ips_img( $item['upload_image'], 'medium' );
			}

			$board_info['json_images'] = json_encode( $board_items );
			$board_info['current_image'] = $board_items[0]['upload_image_url'];

			return array( 
				'modal_title' => 'Zmień okładkę tablicy / ' . $board_info['board_title'], 
				'modal_content' => Templates::getInc()->getTpl( '/modals/board_edit_cover.html', $board_info )
			);
		}
		
	}
	
	function pinit_edit_cover_upload()
	{
		try{
			
			$cover = new BoardCover();
			$board_cover = $cover->cropCover( $_POST );
			
			return array(
				'modal_set_success' => true,
				'modal_call' => '$("#board-block-' . $_POST['board_id'] . ' .middle-block-cover > img, .board-thumb-' . $_POST['board_id'] . '").attr("src", "' . $board_cover . '?reload=' . rand() . '")'
			);
			
		} catch ( Exception $e ) {
			return array( 
				'modal_info' => $e->getMessage()
			);
		}
	}
