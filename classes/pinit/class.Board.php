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

	
class Board{
	
	public function covers()
	{
		if( !isset( $this->covers ) )
		{
			$this->covers = new BoardCover;
		}
		
		return $this->covers;
	}
	/*
	* Set if we are on users own profile
	*/
	public $isUserProfile = false;

	
	public function create( $data )
	{

		$user_id = isset( $data['user_id'] ) ? (int)$data['user_id'] : USER_ID;
		
		if( !isset( $data['board_title'] ) || empty( $data['board_title'] ) || strlen( $data['board_title'] ) < 3 )
		{
			throw new Exception('Musisz wpisać nazwę tablicy ( minimum 3 litery )');
		}
		
		if( $this->findBoardByTitle( $user_id, $data['board_title'] ) )
		{
			throw new Exception("Masz już trablicę o nazwie:  '".$data['board_title']."'");
		}
		
		if( !isset( $data['category_id'] ) || !$data['category_id'] )
		{
			$data['category_id'] = Categories::defaultCategory();
		}
		
		$db = PD::getInstance();
		
		$board_id = $db->insert('pinit_boards', array(
			'user_id' => $user_id,
			'board_title' => $data['board_title'],
			'board_description' => isset( $data['board_description'] ) ? $data['board_description'] : '',
			'date_add' => date("Y-m-d H:i:s"),
			'date_modified' => date("Y-m-d H:i:s"),
			'board_privacy' => isset( $data['board_privacy'] ) ? 'private' : 'public',
			'category_id' => $data['category_id'],
			'board_views' => 1,
			'board_has_map' => isset( $data['board_has_map'] ) ? 1 : 0,
		));
		
		if( !$board_id )
		{
			throw new Exception('Ther was an error while crating Board');
		}
		
		$insert = $db->insert('pinit_users_boards', array(
			'user_id' => $user_id,
			'board_id' => $board_id,
			'is_author' => 1
		));
		
		/*
		if( isset( $data['friends'] ) )
		{
			foreach( $data['friends'] as $friend )
			{
				$db->insert('pinit_users_boards', array(
					'user_id' => $friend,
					'board_id' => $board_id,
					'invited_by' => $user_id,
				));
			}
		}
		*/
		
		if( !$insert )
		{
			$db->delete('pinit_boards', array(
				'board_id' => $board_id
			));
			$db->delete('pinit_users_boards', array(
				'board_id' => $board_id
			));
			throw new Exception('Ther was an error while crating Board');
		}
		
		/** Notify users following user */
		Notify::getInstance()->notifyUser( array(
			'type' => 'new_board',
			'user_id' => $db->select( 'users_follow_user', array( 
							'user_followed_id' => $user_id
						), null, 'user_id' ),
			'board_id' => $board_id
		) );
		
		/**
		* Update user stats
		*/
		$u = new PinUser();
		$u->updateUser( $user_id );
		
		return array(
			'board_id' => $board_id,
			'title' => $data['board_title']
		);
	}
	
	/**
	* Delete specific board
	*
	* @param 
	* 
	* @return 
	*/
	public function delete( $data )
	{
		$board_id = isset( $data['board_id'] ) ? (int)$data['board_id'] : false;
		
		if( !$board_id )
		{
			throw new Exception('Wrong board ID');
		}
		
		$db = PD::getInstance();
		
		$board_info = $this->getBoard( $board_id );
		
    	if( !$board_info )
		{
    		return;
    	}
		
		$del_board = $db->delete('pinit_boards', array( 
			'board_id' => $board_id
		));
		
		if( $del_board )
		{
			$p = new Pin();
			$pins = $p->getPins( array( 
				'board_id' => $board_id
			), 'id' );
			
			if( $pins )
			{
				foreach( $pins as $pin )
				{
					try{
						$p->delete( $pin, false );
					} catch (Exception $e) {}
				
				}
			}
			
			$db->delete( array( 
				'pinit_users_follow_board', 
				'pinit_users_boards'
			), array( 
				'board_id' => $board_id
			));

			/**
			* Update user stats
			*/
			$u = new PinUser();
			$u->updateUser( $board_info['user_id'] );

		}
		
		return $del_board;
	}
	
	/**
	* Edit board
	*
	* @param 
	* 
	* @return 
	*/
	public function edit( $data )
	{
		
		$board_id = isset( $data['board_id'] ) ? (int)$data['board_id'] : false;
		
		if( !$board_id )
		{
			throw new Exception('Wrong board ID');
		}
		
		$db = PD::getInstance();
		
		$board_info = $this->boards( array( 
			'board_id' => $board_id
		) );
		

    	if( !$board_info )
		{
    		throw new Exception('Wrong board_info');
    	}
		
		$update = array(
			'date_modified' => date("Y-m-d H:i:s")
		);
		if( isset( $data['board_title'] ) )
		{
			$update['board_title'] = (string)$data['board_title'];
		}
		if( isset( $data['category_id'] ) )
		{
			$update['category_id'] = (string)$data['category_id'];
		}
		if( isset( $data['board_description'] ) )
		{
			$update['board_description'] = (string)$data['board_description'];
		}
		
		$update['board_privacy'] = isset( $data['board_privacy'] ) ? 'private' : 'public';
		
		$result = $db->update('pinit_boards', $update, array( 
			'board_id' => $board_id
		) );
		
		if( $result )
		{
			$board_users = array_column( $db->select( 'pinit_users_boards', array( 
				'board_id' => $board_id
			)), 'user_id' );
				
			if( isset( $data['users_can_pin'] ) )
			{
				$data['users_can_pin'] = array_unique( $data['users_can_pin'] );
				
				$notify_users = array();
				
				foreach( $data['users_can_pin'] as $user_id )
				{
					if( !in_array( $user_id, $board_users ) )
					{
						$db->insert('pinit_users_boards', array(
							'user_id'	=> $user_id,
							'board_id'	=> $board_id,
							'allow_pin' => 1,
							'invited_by' => USER_ID
						));
						
						$notify_users[] = $user_id;
					}
				}
				
				/* Notify users that they were invited to pin in board */
				if( !empty( $notify_users ) )
				{
					Notify::getInstance()->notifyUser( array(
						'type' => 'board_invite',
						'user_id' => $notify_users,
						'board_id' => $board_id
					) );
				}
				
				/* Notify users that were deleted from pin in board */
				if( isset( $data['users_can_pin_current'] ) )
				{
					$notify_users = array_diff( $data['users_can_pin_current'], $data['users_can_pin'] );
					
					if( !empty( $notify_users ) )
					{
						foreach( $notify_users as $user_id )
						{
							$db->delete('pinit_users_boards', array(
								'user_id'	=> $user_id,
								'board_id'	=> $board_id
							));
						}
						
						Notify::getInstance()->notifyUser( array(
							'type' => 'board_invite_delete',
							'user_id' => $notify_users,
							'board_id' => $board_id
						) );
					}
				}
			}
			
			$db->update( IPS__FILES, array(
				'category_id' => $data['category_id'],
				'date_modified' => date("Y-m-d H:i:s")
			), array( 'board_id' => $board_id ) );
		}

		return true;
	}
	
	public function boardUrl( $board_id, $url_slug = false )
	{
		return ABS_URL . 'board/' . $board_id . '/' . ( $url_slug ? seoLink( false, $url_slug ) : '' );
	}
	
	
	public function boardUsers( $board_id, $data )
	{
		if( !$board_id )
		{
			return array();
		}
		
		$data['board_id'] = $board_id;

		array_walk( $data, function( &$item, &$key ){ 
			$key = 'pinit_users_boards' . $key; 
		});
		
		$db = PD::getInstance();
		
		$users = $db->->from('pinit_users_boards u_b')->join( 'users u' )->on( 'u.id', 'u_b.user_id' )->setWhere( $data )->fields( "u_b.*, u.id, u.login, u.avatar, CONCAT( first_name,' ',last_name ) as full_name" )->get();

		
		if( $users )
		{
			return User::getInstance()->usersModel( $users );
		}
		
		return array();
	}
	
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function userBoards( $user_id )
	{
		$db = PD::getInstance();
		
		$boards = $db->select('pinit_boards', array(
			'user_id' => $user_id
		), null, 'board_title,board_id,board_privacy' );

		return count( $boards ) > 0 ? $boards : false;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public static function getField( $board_id, $fields = '*' )
	{
		$db = PD::getInstance();
		
		$boards = $db->from('pinit_boards b');
		
		if( strpos($fields, 'u.')  !== false )
		{
			$boards = $users->join( 'users u' )->on( 'u.id', 'b.user_id' );
		}
		
		$boards = $boards->where( array(
			'b.board_id' => $board_id
		) )->fields( $fields )->getOne();

		return ( count( $boards ) > 0 ? ( strpos( $fields, ',' ) === false ? $boards[$fields] : $boards ) : false );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function findBoardByTitle( $user_id, $board_title )
	{
		$db = PD::getInstance();
		
		$board_id = $db->select( 'pinit_boards', array(
			'board_title' => array( $board_title, 'LIKE'),
			'user_id' =>$user_id
		));
					
		if( !empty( $board_id ) )
		{
			return $board_id;
		}
		return false;
	}
	
	public function getBoard( $board_id )
	{
		return $this->boards( array( 
			'board_id' => $board_id,
			'limit' => 1
		) );
	}
	
	public function boards( $data )
	{
		$conditions = array();
		
		if( isset( $data['board_id'] ) )
		{
			$conditions['board_id'] = $data['board_id'];
		}
		elseif( is_array( $data['board_id'] ) )
		{
			$conditions['board_id'] = array( $data['board_id'], 'IN' );
		}
		
		if( !isset( $data['limit'] ) )
		{
			$data['limit'] = null;
		}
		
		$boards = PD::getInstance()->select( 'pinit_boards', $conditions, $data['limit'] );

		if( !empty( $boards ) )
		{
			return $boards;
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
	public function relatedBoards( $pin_id, $limit )
	{
		if( empty( $pin_id ) || !is_numeric( $pin_id ) )
		{
			return false;
		}
		
		$db = PD::getInstance();
		
		$related_boards = $db->from( 'pinit_boards b' )->join( 'users u', array(
			'b.user_id' => 'field:u.id'
		), 'LEFT' )->where( 'board_id', 'SELECT board_id FROM ' . db_prefix( 'pinit_pins' ) . ' WHERE repin_from = ' . $pin_id, 'in' )->fields( "u.*, b.*, CONCAT( first_name,' ',last_name ) as full_name" )->get( $limit );
		
		$board_blocks = null;
		if( !empty( $related_boards ) )
		{
			foreach( $related_boards as $board )
			{
				$board_blocks .= $this->boardBlock( $board );
			}
		}
		
		return array( 
			'blocks' => $board_blocks,
			'count' => count( $related_boards )
		);
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getBoardItems( $board_id, $layout = 'small', $limit = null )
	{
		$pins = new Pin();
		
		$board_items = $pins->getPins( array( 
			'board_id' => $board_id
		), 'id,upload_image,pin_title', $limit );
		
		if( empty( $board_items ) )
		{
			return null;
		}
		
		foreach( $board_items as $key => $item )
		{
			$board_items[ $key ]['upload_image'] = ips_img( $item['upload_image'], 'thumb' );
		}
		
		return $board_items;
	}

	
	
	

	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function boardThumbs( &$board )
	{

		$db = PD::getInstance();
		
		$board_images = $this->covers()->getImages( $board );

		$file_thumbs = isset( $board_images->file_thumbs ) && !empty( $board_images->file_thumbs ) ? (array)$board_images->file_thumbs : array();

		if( ( $board['board_pins'] >= 1 && ( count( $file_thumbs ) < $board['board_pins'] ) && count( $file_thumbs ) < 4 ) )
		{
			$file_thumbs = $this->covers()->createThumbs( $board, $file_thumbs );
		}
		
		foreach( $file_thumbs as $key => $thumb )
		{
			$file_thumbs[$key] = ips_img( $thumb, 'board/thumbs' );
		}
		
		if( count( $file_thumbs ) < 4 )
		{
			while( count( $file_thumbs ) < 4 )
			{
				$file_thumbs[] = 'images/pinit/board_thumbnail.png';
			}
		}
		
		return $file_thumbs;
	}
	
	/**
	* Generate unique filename for board
	*
	* @param 
	* 
	* @return 
	*/
	public function cacheFilename( $board_id, $followed_board )
	{
		return IPS_PINIT_C_BOARD . '/' . md5( 'board_' . $board_id . $followed_board );
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function boardBlock( $board_data, $option = 'render' )
	{
		$followed_board = User::getInstance()->userFollowBoard( USER_ID, $board_data['board_id'], $board_data['user_id'] );
	
		$board_data['user_follow_board'] = $followed_board;
		$board_data['author_image'] = ips_user_avatar( $board_data['avatar'] );
		$board_data['board_thumbs'] = $this->boardThumbs( $board_data );
		$board_data['board_cover'] = $this->covers()->getCover( $board_data );
		$board_data['board_url'] = $this->boardUrl( $board_data['board_id'], $board_data['board_title'] );
		
		if( $option == 'compile' )
		{
			return $board_data;
		}	
		
		return Templates::getInc()->getTpl( 'board_block.html', $board_data );

	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function displayBoardPins( $board_id )
	{
		
		$db = PD::getInstance();

		$p = new Pin();

		$board_pins = $p->getPins( array(
			'board_id' => $board_id
		), '*', xy( IPS_ACTION_PAGE, Config::get( 'files_on_page') ) );
		
		return $p->displayFiles( $board_pins );
		
	}
	
	/**
	* Update list of board stats
	*
	* @param $pin_info = passed only on PIN delete
	* 
	* @return 
	*/
	public function updateBoard( $board_id, $pin_info = false )
	{
		
		$db = PD::getInstance();
		
		$p = new Pin();
		
		$db->update( 'pinit_boards', array(
			'board_pins'		=> (int)$db->cnt( IPS__FILES, 'board_id =' . $board_id ),
			'board_followers'	=> (int)$db->cnt( 'pinit_users_follow_board', 'board_id =' . $board_id ),
			'latest_pins'		=> implode( ',', $p->latestPins( array( 
				'board_id' => $board_id
			)))
		), array( 'board_id' => $board_id ) );
		
		/**
		* Update board cover and thumbnails
		*/
		$this->covers()->boardCoversUpdate( $this->getBoard( $board_id ), $pin_info );

	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function displayBoardBlocks( $boards, $boards_privacy = 'public', $action = 'render' )
	{

		$board_blocks = array();
		
		if( $action == 'render' && $this->isUserProfile() )
		{
			$board_blocks[] = Templates::getInc()->getTpl( 'user_board_profile.html', array(
				'boards_privacy' => $boards_privacy
			) );
			
		}

		if( !empty( $boards ) )
		{
			foreach( $boards as $board )
			{
				$board_blocks[] = $this->boardBlock ( $board, $action);
			}
		}
		
		return ( $action == 'render'  ? implode( "\n", $board_blocks ) : array( 
			'items'		=> $board_blocks,
			'template'	=> 'board_block'
		) );
		
	}

	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public static function secretBoards( $user_id )
	{
		
		$b = new Board();
		
		if( $b->isUserProfile() )
		{
			$board_blocks = '<div class="secret-boards"><div class="sub-content">';
			
			$display = new Core_Query();
			$display->init( 'boards', array( 
				'condition' => array(
					'b.board_privacy' => 'private'
				),
				'display' => false
			) );

			$board_blocks = $b->displayBoardBlocks( $display->files, 'private' );
			
			unset( $display );
			
			return '<div class="secret-boards"><div class="sub-content">' . $board_blocks . '</div></div>';
		}
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function isUserProfile()
	{
		if( IPS_ACTION !== 'boards' && IPS_ACTION !== 'user_boards' )
		{
			return false;
		}
		
		$this->isUserProfile = ( get_input( 'login' ) == USER_LOGIN );
		
		return $this->isUserProfile;
	}
	
	
	/**
	* Model board data
	*
	* @param 
	* 
	* @return 
	*/
	public function boardsModel( $boards, &$boards_data, $reset_array = false  )
	{
		
		$first_element = (array)reset( $boards );
		
		if( !isset( $first_element['board_id'] ) || !isset( $first_element['board_title'] ) )
		{
			if( isset( $boards['board_id'] ) )
			{
				$boards = array_flip( $boards );
			}
			
			$rows = $this->boards( array( 
				'board_id' => array_flip( $boards )
			) );
			
			if( empty( $rows ) )
			{
				return false;
			}

			foreach( $rows as $key => $board )
			{
				$boards[ $board['board_id'] ] = is_array( $boards[ $board['board_id'] ] ) ? array_merge( $boards[ $board['board_id'] ], $board ) : $board;
			}
		}

		if( $boards !== false )
		{
			$u = User::getInstance();
			
			foreach( $boards as $key => $board )
			{
				if( !isset( $boards_data[ $board['board_id'] ] ) )
				{
					$boards_data[ $board['board_id'] ] = $board;
				}
				$boards_data[ $board['board_id'] ]['user_id'] = $board['user_id'];
				$boards_data[ $board['board_id'] ]['board_id'] = $board['board_id'];
				$boards_data[ $board['board_id'] ]['board_link'] = $this->boardUrl( $board['board_id'], $board['board_title'] );
				$boards_data[ $board['board_id'] ]['category_id'] = $board['category_id'];
				$boards_data[ $board['board_id'] ]['board_title'] = $board['board_title'];
				$boards_data[ $board['board_id'] ]['board_thumb'] = $this->covers()->getThumb( $board );
				$boards_data[ $board['board_id'] ]['is_user_board'] = $board['user_id'] == USER_ID;
				$boards_data[ $board['board_id'] ]['user_follow_board'] = $u->userFollowBoard( USER_ID, $board['board_id'], $board['user_id'] );
				$boards_data[ $board['board_id'] ]['user_board_author'] = USER_ID == $board['user_id'];
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
	public function boardPanel()
	{
		$db = PD::getInstance();
		
		
		$board_info = array();
		
		$this->boardsModel( array( 
			'board_id' => (int)IPS_ACTION_GET_ID
		), $board_info );
		
		if( empty( $board_info ) )
		{
			ips_redirect( false, 'Nie ma takiej tablicy w systemie' );
		}
		
		$board_info = reset($board_info);
		
		$board_info['user_board_author'] = User::getInstance()->usersModel( array(
			'user_id' => $board_info['user_id']
		));
		$board_info['user_board_author'] = $board_info['user_board_author'][$board_info['user_id']];
		
		if( $board_info['board_has_map'] )
		{
			App::$base_args['body_class'] = App::$base_args['body_class'] . ' board_map';
			$board_info['api_key'] = Config::getArray( 'apps_google_app', 'maps_api_key' );
			$places = new Places;
			
			$board_info['map_locations'] = json_encode( $places->getPlaces( array(
				'board_id' => (int)IPS_ACTION_GET_ID
			) ) );
			
			
			$board_info['map_customize'] = Config::getArray('apps_google_maps_customize');
			
		}
		
		
		
		
		return Templates::getInc()->getTpl( 'board_info_block' . ( $board_info['board_has_map'] ? '_map' : '' ). '.html', $board_info );
	}
}
?>