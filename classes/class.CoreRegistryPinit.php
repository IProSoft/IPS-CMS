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
	
	/**
	* Controllers containing definitions for MySQL query
	*/
class Core_Registry_Pinit
{
	
	private static $initalized = false;
	
	public static function init()
	{
		if( !self::$initalized )
        {
            self::$initalized = new Core_Registry_Pinit();
        }
		return self::$initalized;
	}

	/**
	* Call method
	*/
	public function call_action( $controller )
	{
		/**
		* Check if called method exist in registry
		*/
		if( method_exists( $this, 'action_' . $controller ) )
		{
			return $this->{ 'action_' . $controller }();
		}
		
		return false;
	}
	
	public function action_pinit_main()
	{
		return array(
			'sorting' => 'pin_featured DESC,date_add',
			'condition' => array(
				'pin_privacy' => 'public'
			),
			'pagination' => '/page/'
		);
	}
	
	public function action_pinit_category()
	{
		return array(
			'sorting' => 'pin_featured DESC,date_add',
			'condition' => array(
				'pin_privacy' => 'public'
			)
		);
	}

	public function action_pinit_following()
	{
		if( !USER_ID )
		{
			ips_redirect( false, 'pinit_no_user' );
		}
		
		$action = 'pins';
		$page = 1;
		
		$pagination_url = '/following/pins';
		
		if( isset( $_GET['sort'] ) && in_array( $_GET['sort'] , array( 'pins', 'boards', 'users' )) )
		{
			$action = $_GET['sort'];
			$pagination_url = '/following/' . $action;
		}
		
		if( isset( $_GET['sub_sort'] ) && in_array( $_GET['sub_sort'] , array( 'boards', 'users' )) )
		{
			$sub_action = $_GET['sub_sort'];
		}
		
	
		switch( $action )
		{
			case 'pins':
	
				$condition_boards = "p.board_id IN ( SELECT board_id FROM " . db_prefix( 'pinit_users_follow_board' ) . " WHERE user_id  = '" . USER_ID . "' )";
				
				$condition_users = "p.user_id IN ( SELECT user_followed_id FROM " . db_prefix( 'users_follow_user' ) . " WHERE user_id  = '" . USER_ID . "' )";
				
				if( isset( $sub_action ) && $sub_action == 'boards' )
				{
					$condition = $condition_boards;
				}
				elseif( isset( $sub_action ) && $sub_action == 'users' )
				{
					$condition = $condition_users;
				}
				else
				{
					$condition = $condition_boards . ' OR ' . $condition_users;
				}
				
				return array(
					'table'			=> db_prefix( IPS__FILES, 'up' ),
					'condition' 	=> $condition . " AND up.pin_privacy = 'public'",
					'sorting'		=> 'up.date_add',
					'count_records'	=> false,
					'columns'		=> 'up.*',
					'pagination'	=> '/following/pins' . ( isset( $sub_action ) ? '/' . $sub_action : '' ),
					'page'			=> $page
				);
			break;
			case 'boards':
				return array(
					'table'			=> db_prefix( 'pinit_boards', 'b' ) .' LEFT JOIN ' . db_prefix( 'pinit_users_follow_board', 'f' ) . ' ON b.board_id = f.board_id LEFT JOIN users as u ON u.id = b.user_id',
					'condition' 	=> "f.user_id  = '" . USER_ID . "'",
					'sorting'		=> 'b.date_add',
					'count_records'	=> false,
					'columns'		=> "f.*, b.*,u.*, CONCAT( first_name,' ',last_name ) as full_name",
					'pagination'	=> '/following/boards' . ( isset( $sub_action ) ? '/' . $sub_action : '' ),
					'page'			=> $page,
					'pinit_boards'	=> true,
				);
			break;
				
			case 'users':
				return array(
					'table'			=> db_prefix( 'users', 'u' ) . ' LEFT JOIN ' . db_prefix( 'users_follow_user', 'f' ) . ' ON u.id = f.user_followed_id',
					'condition' 	=> "f.user_id  = '" . USER_ID . "'",
					'sorting'		=> 'u.date_add',
					'count_records'	=> false,
					'columns'		=> "f.*, u.*, CONCAT( first_name,' ',last_name ) as full_name",
					'pagination'	=> '/following/users',
					'page'			=> $page,
					'pinit_users'	=> true,
				);
			break;
		}
		
		

		
	}
	

	public function action_pinit_pins()
	{
		$sorting = 'date_add';
		$pagination_url = '/pins';
		if( !empty( $_GET['sort'] ) && in_array( $_GET['sort'] , array( 'likes', 'repins' )) )
		{
			$sorting = 'pin_' . $_GET['sort'];
			$pagination_url = '/pins/' . $_GET['sort'];
		}
		
		return array(
			'condition' => "pin_privacy = 'public'",
			'sorting'		=> $sorting,
			'pagination' => '/page/',
			'pagination' 	=> $pagination_url,
		);
	}
	public function action_pinit_users()
	{
		$sorting = 'users.date_add';
		$pagination_url = '/users';
		
		if( !empty( $_GET['sort'] ) && in_array( $_GET['sort'] , array( 'pins', 'boards', 'followers', )) )
		{
			$sorting = 'users.user_' . $_GET['sort'];
			$pagination_url = '/users/' . $_GET['sort'];
		}
		
		return array(
			'table'			=> db_prefix( 'users', 'u' ),
			'condition'		=> "u.activ = 1",
			'sorting'		=> $sorting,
			'count_records'	=> false,
			'columns'		=> "u.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination' 	=> $pagination_url,
			'pinit_users'	=> true,
		);
	}
	
	public function action_pinit_boards()
	{
		
		$sorting = 'b.date_add';
		
		$pagination_url = '/boards';
		
		if( !empty( $_GET['sort'] ) && in_array( $_GET['sort'] , array( 'pins', 'views', 'followers' ) ) )
		{
			$sorting = 'b.board_' . $_GET['sort'];
			$pagination_url = '/boards/' . $_GET['sort'];
		}
		
		return array(
			'table'			=> db_prefix( 'pinit_boards', 'b' ) . ' LEFT JOIN ' . db_prefix( 'users', 'u' ) . ' ON u.id = b.user_id',
			'sorting'		=> $sorting,
			'pinit_boards'	=> true,
			'count_records'	=> false,
			'columns'		=> "u.*, b.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination'	=> $pagination_url
		);
	}
	
	
	
	public function action_pinit_popular_liked()
	{
		return array(
			'table'			=> db_prefix( 'pinit_pins', 'up' ) . ' LEFT JOIN ' . db_prefix( 'users', 'u' ) . ' ON u.id = up.user_id',
			'condition' 	=> "up.pin_privacy = 'public'",
			'sorting'		=> 'up.pin_likes',
			'count_records'	=> false,
			'columns'		=> "u.*, up.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination' 	=> 'popular/liked/'
		);
	}
	public function action_pinit_popular_followed()
	{
		return array(
			'table'			=> db_prefix( 'pinit_boards', 'b' ) . ' LEFT JOIN ' . db_prefix( 'users', 'u' ) . ' ON u.id = b.user_id',
			'condition' 	=> "b.board_privacy = 'public'",
			'sorting'		=> 'b.board_followers',
			'pinit_boards'	=> true,
			'count_records'	=> false,
			'columns'		=> "u.*, b.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination' 	=> 'popular/boards/'
		);
	}
	public function action_pinit_popular_repinned()
	{
		$array = $this->action_pinit_popular_liked();
		$array['sorting'] = 'up.pin_repins';
		
		return $array;
	}
	
	/** Who liked Pin */
	public function action_pinit_pin_likes()
	{
		return array(
			'table'			=> db_prefix( 'users', 'u' ) . ' LEFT JOIN ' . db_prefix( 'pinit_pins_likes', 'l' ) . ' ON u.id = l.user_id',
			'condition' 	=> "l.pin_id  = '" . IPS_ACTION_GET_ID . "'",
			'sorting'		=> 'u.date_add',
			'count_records'	=> false,
			'columns'		=> "l.user_id, l.pin_id, u.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination' 	=> 'pin/' . IPS_ACTION_GET_ID . '/likes/',
			'pinit_users'	=> true,
		);
	}
	
	/** Repinned to boards */
	public function action_pinit_pin_repins()
	{
		return array(
			'table'			=> db_prefix( 'pinit_boards', 'b' ) . ' LEFT JOIN ' . db_prefix( 'users', 'u' ) . ' ON u.id = b.user_id',
			'condition' 	=> "b.board_privacy = 'public' AND b.board_id IN ( SELECT DISTINCT board_id FROM pinit_pins WHERE repin_from = '" . IPS_ACTION_GET_ID . "' )",
			'sorting'		=> 'b.date_add',
			'pinit_boards'	=> true,
			'count_records'	=> false,
			'columns'		=> "u.*, b.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination' 	=> 'pin/' . IPS_ACTION_GET_ID . '/repins/'
		);
	}
	
	public function action_pinit_pin()
	{
		return 'Pinestic::displayPin';
	}
	
	public function action_pinit_board()
	{
		return array(
			'condition'		=> 'board_id = {board_id}',
			'pagination'	=> '{pagination}',
			'callable_before' => 'boardBefore',
		);
	}
	
	public function action_pinit_user_boards()
	{
		$user_info = getUserInfo( false, false, $_GET['login'] );
		
		if( !isset( $user_info['id'] ) )
		{
			ips_redirect( false, 'pinit_no_user' );
		}
		
		add_action('after_content', 'Board::secretBoards', array( $user_info['id'] ) );

		return array(
			'table'			=> db_prefix( 'pinit_boards', 'b' ) . ' LEFT JOIN ' . db_prefix( 'users', 'u' ) . ' ON u.id = b.user_id',
			'condition' 	=> ( $user_info['id'] == USER_ID ? '' : "b.board_privacy = '{board_privacy}' AND " ) . "b.user_id = " . $user_info['id'],
			'sorting'		=> 'b.date_add',
			'count_records'	=> false,
			'pinit_boards'	=> true,
			'columns'		=> "u.*, b.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination'	=> '/' . $user_info['login'] . '/boards'
		);
	}
	
	public function action_pinit_user_pins()
	{
		$user_info = getUserInfo( false, false, $_GET['login'] );
		
		if( !isset( $user_info['id'] ) )
		{
			ips_redirect( false, 'pinit_no_user' );
		}
		
		return array(
			'condition' => 'user_id = ' . $user_info['id'],
			'pagination' => '/' . $user_info['login'] . '/pins'
		);
	}
	
	public function action_pinit_user_likes()
	{
		$user_info = getUserInfo( false, false, $_GET['login'] );
		
		if( !isset( $user_info['id'] ) )
		{
			ips_redirect( false, 'pinit_no_user' );
		}
		
		return array(
			'table'		=> db_prefix( 'pinit_pins_likes', 'l' ) . ' LEFT JOIN ' . db_prefix( 'pinit_pins', 'up' ) . ' ON l.pin_id = up.id',
			'sorting'	=> 'up.date_add',
			'condition' => 'l.user_id = ' . $user_info['id'],
			'pagination' => '/' . $user_info['login'] . '/likes'
		);
	}
	

	public function action_pinit_user_follow()
	{
		$user_info = getUserInfo( false, false, $_GET['login'] );
		
		if( !isset( $user_info['id'] ) )
		{
			ips_redirect( false, 'pinit_no_user' );
		}
		
		return array(
			'table'			=> db_prefix( 'users', 'u' ) . ' LEFT JOIN ' . db_prefix( 'users_follow_user', 'f' ) . ' ON u.id = f.user_followed_id',
			'condition' 	=> "f.user_id  = '" . $user_info['id'] . "'",
			'sorting'		=> 'u.date_add',
			'count_records'	=> false,
			'columns'		=> "f.*, u.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination' => '/' . $user_info['login'] . '/follow',
			'pinit_users'	=> true,
		);
	}
	
	public function action_pinit_user_followers()
	{
		$user_info = getUserInfo( false, false, $_GET['login'] );
		
		if( !isset( $user_info['id'] ) )
		{
			ips_redirect( false, 'pinit_no_user' );
		}
		
		return array(
			'table'			=> db_prefix( 'users', 'u' ) . ' LEFT JOIN ' . db_prefix( 'users_follow_user', 'f' ) . ' ON u.id = f.user_id',
			'condition' 	=> "f.user_followed_id  = '" . $user_info['id'] . "'",
			'sorting'		=> 'u.date_add',
			'count_records'	=> false,
			'columns'		=> "f.*, u.*, CONCAT( first_name,' ',last_name ) as full_name",
			'pagination' => '/' . $user_info['login'] . '/follow',
			'pinit_users'	=> true,
		);
	}
	
	public function action_pinit_source()
	{
		$source = 'pin_uploaded';

		if( !empty( $_GET['source'] ) )
		{
			$source = $_GET['source'];
		}
		
		return array(
			'sorting'		=> 'pin_featured DESC, date_add',
			'condition' => "pin_privacy = 'public' AND pin_source_hash = '" . md5( $source ) . "'",
			'pagination' 	=> '/' . $source . '/',
		);
	}
	
	
	