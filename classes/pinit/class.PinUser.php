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

class PinUser{
	
	public $user = array();
	
	public static $instance = false;
	
	
	public static function getInstance()
	{
       
	   if( !self::$instance )
        {
            self::$instance = new PinUser();
        }
		return self::$instance;
    }
	
	public function users( $data = false )
	{
		
		if( !isset( $data['user_id'] ) )
		{
			if( !USER_LOGGED )
			{
				throw new Exception('You mus be logged to use this function');
			}
			$data['user_id'] = USER_ID;
		}
		
		$db = PD::getInstance();
		
		
		

		if( isset( $data['only_firends'] ) )
		{
			$users = $db->from( 'users_follow_user f' )->join( 'users u')->on( 'f.user_followed_id', 'u.id' )->where( 'f.user_id', $data['user_id'] )->orderBy('full_name,u.id')->fields("u.*, CONCAT( first_name,' ',last_name ) as full_name")->get( 50 );
		}
		else
		{
			$query = array();
			/**
			* Find by name
			*/
			$query['name_query'] = ( isset( $data['name_like'] ) ? "CONCAT( first_name,' ',last_name ) LIKE %'" . $data['name_like'] . "%'" : '' );
			
			/**
			* Find by email
			*/
			$query['email_query'] = ( isset( $data['email_like'] ) ? "users.email LIKE '" . $data['email_like'] . "%'" : '' );
		
			$users = $db->select( 'users',
				( !empty( $query ) ? '( ' . implode( ' OR ', $query ) . ' )' : null )
				/**
				* Without search current user
				*/
				. ( isset( $data['id_not_like'] ) ? " AND users.id != '" . $data['id_not_like'] . "'" : '' ), 
				50, 
				"*, CONCAT( first_name,' ',last_name ) as full_name", 'full_name,users.id' 
			);
		}
	
		$users_list = array();
		if( $users )
		{
			foreach( $users as $user )
			{
				$users_list[] = array(
					'full_name'	=> $user['full_name'],
					'image'		=> ips_user_avatar( $user['avatar'] ),
					'user_id'	=> $user['id'],
					'href'		=> ABS_URL . 'profile/' . $user['login'],
					'login'		=> $user['login']
				);
			}
		}
		
		return $users_list;
	}
	
	/**
	* Update user stats
	*
	* @param 
	* 
	* @return 
	*/
	public function updateUser( $user_id )
	{
		
		Operations::updateUserStats( $user_id );
		
		$p = new Pin();
		
		User_Data::update( $user_id, 'user_latest_pins', $p->latestPins( array( 
			'user_id' => $user_id
		) ) );

	}
	

	/**
	* Retrive list of board followed by user
	* Also list contains boards added by user so he can't follow It
	*
	* @param 
	* 
	* @return 
	*/
	public function userFollowedBoards( $user_id = USER_ID )
	{

		if( !isset( $this->user[$user_id]['followed_boards'] ) )
		{
			$followed_boards = array();
			
			$db = PD::getInstance();
			
			$followed_boards = $db->select( 'pinit_users_follow_board', array(
				'user_id' => $user_id
			), null, 'user_id, board_id' );
			
			$followed_boards =  !empty( $followed_boards ) ? array_column( $followed_boards, 'board_id' ) : array();
		
			$this->user[$user_id]['followed_boards'] = $followed_boards;
			unset( $followed_boards );
		}
		
		return $this->user[$user_id]['followed_boards'];

	}
	
	/**
	* Check if user follows board
	*
	* @param 
	* 
	* @return 
	*/
	public function userFollowBoard( $user_id = USER_ID, $board_id, $author_id = false )
	{
		if( $user_id == $author_id )
		{
			return 'is_author';
		}

		return in_array( $board_id, $this->userFollowedBoards( $user_id ) );
	}
	
	
	/**
	* Retrive list of users followed by user with ID
	*
	* @param 
	* 
	* @return 
	*/
	public function userFollowedUsers( $user_id = USER_ID )
	{
		
		if( !isset( $this->user[$user_id]['followed'] ) )
		{
			$followed = array();
			
			$db = PD::getInstance();
			
			$followed = $db->select( 'users_follow_user', array(
				'user_id' => $user_id
			), null, 'user_id, user_followed_id' );

			$followed = !empty( $followed ) ? array_column( $followed, 'user_followed_id' ) : array();

			$this->user[$user_id]['followed'] = $followed;
			unset( $followed );
		}
		
		return $this->user[$user_id]['followed'];
	}
	
	/**
	* Check id user fallow user
	*
	* @param 
	* 
	* @return 
	*/
	public function userFollowUser( $user_id = USER_ID, $user_followed_id )
	{
		if( USER_ID == $user_followed_id )
		{
			return 'is_user';
		}

		return in_array( $user_followed_id, $this->userFollowedUsers( $user_id ) );
	}
	

	
	/**
	* Model user data
	*
	* @param 
	* 
	* @return 
	*/
	public function usersModel( $users )
	{
		global ${IPS_LNG};
		
		$db = PD::getInstance();
		
		$first_element = (array)reset( $users );
		
		/** Cache users model */
		if( !isset( $first_element['full_name'] ) )
		{
			
			if( isset( $users['user_id'] ) )
			{
				$users = array_flip( $users );
			}

			/* 
				jako parametr zawsze musi być tablica np array( 'tutaj id usera' => 'dowolne' )
			*/
			$rows = $db->select(  'users', array(
				'id' => array( array_unique( array_keys( $users ) ) ), 'IN')
			), false, "id,login,avatar,CONCAT( first_name,' ',last_name ) as full_name" );
			
			if( empty( $rows ) )
			{
				return false;
			}
			
			$users = array();
			
			foreach( $rows as $key => $user )
			{
				$users[ $user['id'] ] = $user;
			}
			
			// Róznica między zmiennymi np gdy użytkownik usunięty .... */
		}
		
		$users_data = array();
		
		if( !empty( $users ) )
		{
			
			foreach( $users as $key => $user )
			{
				$users_data[ $user['id'] ] = $user;
				$users_data[ $user['id'] ]['user_id'] = ( isset( $user['user_id'] ) ? $user['user_id'] : $user['id'] );
				$users_data[ $user['id'] ]['link'] = ABS_URL . 'profile/' . $user['login'];
				$users_data[ $user['id'] ]['avatar'] = ips_user_avatar( $user['avatar'] );
				$users_data[ $user['id'] ]['full_name'] = strlen( $user['full_name'] ) > 1 ? $user['full_name'] : $user['login'] ;
				//$users_data[ $user['id'] ]['text'] = isset( $user['repin_from'] ) && !empty( $user['repin_from'] ) ? ${IPS_LNG}['pinit_pin_repinned_by'] : ${IPS_LNG}['pinit_pin_pinned_by'];
				$users_data[ $user['id'] ]['user_follow_user'] = $this->userFollowUser( USER_ID, $user['id'] );
			}
			
			if( isset( $users_data[ $user['id'] ]['invited_by'] ) )
			{
				$invited_by = array_column( $users_data, 'invited_by', 'user_id' );
				
				$rows = $db->select(  'users', array(
					'id' => array( array_unique( array_keys( $invited_by ) ) ), 'IN')
				), false, "id,login,CONCAT( first_name,' ',last_name ) as full_name" );
				
				foreach( $rows as $key => $user )
				{
					$rows[ $user['id'] ] = $user ;
				}
				
				foreach( $users_data as $key => $user )
				{
					if( isset( $rows[ $users_data[ $user['id'] ]['invited_by'] ] ) )
					{
						$users_data[ $user['id'] ]['invited_by'] = array(
							'full_name' => 'Invited by ' . ( USER_ID == $rows[ $users_data[ $user['id'] ]['invited_by'] ]['id'] ? 'you' : $rows[ $users_data[ $user['id'] ]['invited_by'] ]['full_name'] ),
							'link'		=> ABS_URL . 'profile/' . $rows[ $users_data[ $user['id'] ]['invited_by'] ]['login'],
						);
					}
				}
			}
		}
		
		return $users_data;
	}

	/**
	* Send Pin to user
	*
	* @param 
	* 
	* @return 
	*/
	public function send( $type, array $data )
	{
		
		if( $type == 'email' || $type == 'message' )
		{
			$data['type'] = ( isset( $data['board_id'] ) && !empty( $data['board_id'] ) ? 'send_board' : 'send_pin' );
			
			return Notify::getInstance()->notifyUser( $data );
		}
		
		
		
		
		/////////////
		/**
		* Send email to user
		*/
		if( $type == 'email' || isset( $data['user_email'] ) )
		{
			return $this->sendEmail( $data );
		}
		
		/**
		* Send private message to user
		*/
		if( $type == 'message' || isset( $data['user_id'] ) )
		{
			return $this->sendMessage( $data );
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
	public function userPanel()
	{
		$db = PD::getInstance();
		
		$user_login = get_input( 'login' );
		
		$user_login = $user_login ? $user_login : USER_LOGIN;
		
		$user = $db->select( 'users', array(
			'login' => $user_login
		), 1 , "*, CONCAT( users.first_name,' ',users.last_name ) as full_name");

		$user['avatar'] = ips_user_avatar( $user['avatar'] );
		$user['about_me'] = User_Data::get( $user['id'], 'about_me' );
		$user['user_profile'] = $user_login == USER_LOGIN;
		$user['current_user_follow'] = $this->userFollowUser( USER_ID, $user['id'] );
		
		User_Data::get( $user['id'] );
		
		$user['facebook_uid'] = User_Data::get( $user['id'], 'facebook_uid' );
		$user['nk_uid'] = User_Data::get( $user['id'], 'nk_uid' );
		$user['twitter_uid'] = User_Data::get( $user['id'], 'twitter_uid' );
		
		return Templates::getInc()->getTpl( 'user_info_block.html', $user );
	}
	
	/**
	* Follow/Unfollow board by ID, UserId
	*/
	public function followBoards( $user_id, $follow_user_id, $force_action = 'follow' )
	{
		
		$board = new Board;
		$boards = $board->userBoards( $follow_user_id );

		$this->followUser( $user_id, $follow_user_id );
		
		if( $boards )
		{
			foreach( $boards as $board )
			{
				$this->followBoard( $user_id, $board['board_id'], $force_action );
			}

			return true;
		}
		
		return false;
	}
	
	public function followBoard( $user_id, $board_id, $force_action = 'follow' )
	{
		if( empty( $board_id ) )
		{
			throw new Exception('Empty parameter for User->followBoard');
		}
		
		$db = PD::getInstance();
		
		$follow = $this->userFollowBoard( $user_id, $board_id );
		
		if( !$follow && $force_action === 'follow' )
		{
			//if( USER_ID != $user_id && $this->userFollowUser( USER_ID, $user_id) )
			//{
				$last = $db->insert('pinit_users_follow_board', array(
					'user_id' => $user_id,
					'board_id' => $board_id
				));
			//}
		}
		elseif( $follow !== 'is_author' || $force_action === 'unfollow' )
		{
			$last = $db->delete('pinit_users_follow_board', array(
				'user_id' => $user_id,
				'board_id' => $board_id
			));
		}
		
		if( isset( $last ) )
		{
			$db->update('pinit_boards', array(
				'board_followers' => (int)$db->cnt( 'pinit_users_follow_board', 'board_id =' . $board_id )
			), array( 'board_id' => $board_id ) );
			
		}
	
		return isset( $last );
		
		//Model_History::addHistory( $user_id, Model_History::UNFOLLOW, 0, $board_id );
	}
	
	/**
	* Follow/Unfollow user by ID
	*
	* @param 
	* 
	* @return 
	*/
	public function followUser( $user_id, $user_followed_id )
	{
		if( empty( $user_followed_id ) )
		{
			throw new Exception('Empty parametr');
		}
		
		if( $user_id == $user_followed_id )
		{
			throw new Exception('You cant follow Yourself');
		}
		
		$db = PD::getInstance();
		
		if( !$this->userFollowUser( $user_id, $user_followed_id ) )
		{
			$last = $db->insert('users_follow_user', array(
				'user_id' => $user_id,
				'user_followed_id' => $user_followed_id
			));
		}
		else
		{
			$last = $db->delete('users_follow_user', array(
				'user_id' => $user_id,
				'user_followed_id' => $user_followed_id
			));
		}
		
		if( $last )
		{
			$db->update('users', array(
				'user_followers' => (int)$db->cnt( 'users_follow_user', array( 
					'user_followed_id' => $user_followed_id 
				) )
			), array( 'id' => $user_followed_id ) );
	
			$db->update('users', array(
				'user_is_following' => (int)$db->cnt( 'users_follow_user', array( 
					'user_id' => $user_id 
				))
			), array( 'id' => $user_id ) );
		}
		
		return $last;
	}
	
	/**
	* Display formatted block with user pins thumbs
	*
	* @param 
	* 
	* @return 
	*/
	public function displayUserBlocks( $users, $action = 'render' )
	{
		if( !empty( $users ) )
		{
			$users_data = $this->usersModel( $users );
			
			foreach( $users_data as $key => $user )
			{
				$users_data[ $key ] = $this->userBlock( $user, $action );
			}
			
			return ( $action == 'render'  ? implode( "\n", $users_data ) : array( 
				'items'		=> array_values( $users_data ),
				'template'	=> 'user_block'
			) );
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
	public function userBlock( $user_data, $action = 'render' )
	{
		
		$user_data['user_thumb'] = $this->userThumb( $user_data['avatar'] );
		$user_data['user_thumbs'] = $this->userThumbs( $user_data );
		
		if( $action == 'compile' )
		{
			return $user_data;
		}
				
		return Templates::getInc()->getTpl( 'user_block.html', $user_data );
	}
	/**
	* Creates 100x100 user avatar to display in block
	*
	* @param 
	* 
	* @return 
	*/
	public function userThumb( $user_avatar )
	{
		$user_avatar = basename( $user_avatar );
		
		if( !file_exists( ABS_PATH . '/' . IPS_PINIT_C_USER_IMG . '/' . $user_avatar ) )
		{
			$upload_handler = new Upload_Handler_Extended( null );
			$upload = $upload_handler->create_scaled_image( $user_avatar, false, array(
				'file_path_ips' => ips_user_avatar( $user_avatar, 'file' ),
				'file_path_save' => ABS_PATH .'/' . IPS_PINIT_C_USER_IMG . '/' . $user_avatar,
				'crop' => true,
				'max_width' => 100,
				'max_height' => 100,
				'jpeg_quality' => 100,
				'upload_dir' => ABS_PATH .'/' . IPS_PINIT_C_USER_IMG . '/'
			) );
			
		}

		return ABS_URL . IPS_PINIT_C_USER_IMG . '/' . $user_avatar;
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function userThumbs( $user_data, $force_update = false )
	{
		
		$db = PD::getInstance();
		
		$file_thumbs = User_Data::get( $user_data['user_id'], 'pin_thumbs_cache', array() );
		
		$count = count( $file_thumbs );
	
		if( $count < 4 && ( $user_data['user_uploads'] > $count ) || $force_update )
		{
			$not_images = array();
			if( is_array( $file_thumbs ) )
			{			
				foreach( $file_thumbs as $thumb )
				{
					$not_images[] = preg_replace( '/user_id-' . $user_data['id'] . '-thumb-([A-Za-z0-9]+).([A-Za-z]+)/i', '\\1.\\2', basename( $thumb ) );
				}
			}
			/** If MySQL hangs use array_diff( $user_pins, $not_images ) */

			$user_pins = $db->optRand( IPS__FILES, array(
				'user_id' => $user_data['id'],
				'upload_image' => array(
					$not_images,
					'NOT REGEXP'
				),
				'repin_from' => array(
					0,
					'!='
				)
			), 4 );
			
			
			
			
			
			
			if( !empty( $user_pins ) )
			{
				$upload_handler = new Upload_Handler_Extended( null );
				
				foreach( $user_pins as $upload_image )
				{
					$thumb_file = 'user_id-' . $user_data['id'] . '-thumb-' . basename( $upload_image['upload_image'] );

					$upload = $upload_handler->create_scaled_image( $thumb_file, false, array(
						'file_path_ips' => ips_img_path( $upload_image['upload_image'], 'large' ),
						'file_path_save' => ABS_PATH .'/' . IPS_PINIT_C_USER_IMG . '/' . $thumb_file,
						'crop' => true,
						'max_width' => 60,
						'max_height' => 60,
						'jpeg_quality' => 100,
						'upload_dir' => ABS_PATH .'/' . IPS_PINIT_C_USER_IMG . '/'
					) );
					
					array_unshift( $file_thumbs, IPS_PINIT_C_USER_IMG . '/' . $thumb_file );
				}
				
				$file_thumbs = array_slice( $file_thumbs, 0, 4 );
			}
			
			User_Data::update( $user_data['id'], 'pin_thumbs_cache', $file_thumbs );
		}
		
		if( count( $file_thumbs ) < 4 )
		{
			while( count( $file_thumbs ) < 4 )
			{
				$file_thumbs[] = 'images/pinit/user_pin_thumbnail.png';
			}
		}
		
		return $file_thumbs;
	}
	
	/**
	* Generate unique filename for user
	*
	* @param 
	* 
	* @return 
	*/
	public function cacheFilename( $user_id, $follow_user )
	{
		return IPS_PINIT_C_USER . '/' . md5( 'user_' . $user_id . $follow_user );
	}
}
?>