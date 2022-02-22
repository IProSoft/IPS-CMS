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

class User_Files
{
	
	private $myFiles = false;
	private $login = false;
	/**
	 * 
	 *
	 * @param $login - user login
	 * @param bool $on_profile - whether we are on the user's profile
	 * 
	 * @return 
	 */
	public function __construct( $login = false )
	{
		if( $login == false )
		{
			$login = get_input( 'login' );
		}
		/**
		 * User not logged in, and without a proper login
		 */
		if ( !USER_LOGGED && empty( $login ) )
		{
			ips_redirect( 'index.html' );
		}
		
		$this->login = empty( $login ) ? USER_LOGIN : Sanitize::cleanXss( $login );
		
		$this->action = get_input( 'action' ) ? $_GET['action'] : 'all';
		
		$this->myFiles = ( USER_LOGGED && USER_LOGIN == $this->login );
		
		$this->user_id = getUserInfo( false, false, $this->login, 'id' );
		
		/**
		 * Try to look at users provate files
		 */
		if ( $this->action == 'private' && !$this->myFiles )
		{
			ips_redirect( 'index.html' );
		}
		
		add_action( ( IPS_VERSION == 'gag' ? 'before_content' : 'before_files_display' ), 'ips_html', array(
			$this->getMenu() 
		), 10 );
		
		$actions = $this->actions();
		
		add_filter( 'core_init_before', function( $display ) use( $actions ){
			$display->controller = array_merge( $display->controller, $actions );
		});

	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function actions()
	{
		$actions = array(
			'condition' => array(
				'user_id' => $this->user_id,
				'upload_status' => 'public'
			),
			'sorting' => 'date_add',
			'pagination' => '/user/' . $this->login . '/' . $this->action . '/' 
		);
		
		switch ( $this->action )
		{
			case 'main':
				$actions['condition']['upload_activ'] = 1;
			break;
			
			case 'wait':
				$actions['condition']['upload_activ'] = 0;
			break;
			
			case 'votes_opinion':
				$actions['sorting'] = 'votes_opinion';
			break;
			
			case 'archive':
			case 'private':
				$actions['condition']['upload_status'] = $this->action;
			break;
			
			case 'fav':
				$actions = array_merge( $actions, array(
					'table' => 'users_favourites fav',
					'join' => array(
						'table' => IPS__FILES . ' up',
						'on' => array( 'up.id' => 'fav.upload_id' )
					),
					'columns' => 'up.*, fav.upload_id',
					'condition' => array(
						'up.upload_status' => 'public',
						'fav.user_id' => $this->user_id 
					),
					'sorting' => 'fav.date_add' 
				) );
			break;
			
			case 'comments':
				$actions = array_merge( $actions, array(
					'table' => 'upload_comments c',
					'join' => array(
						'table' => 'users',
						'on' => array( 'users.id' => 'c.user_id' )
					),
					'condition' => array(
						'c.user_id' => $this->user_id 
					),
					'sorting' => 'c.date_add',
					'comments' => true,
					'columns' => 'c.*, users.avatar' 
				) );
			break;
			
			case 'followed':
				$actions = array_merge( $actions, array(
					'condition' => array(
						'up.upload_status' => 'public',
						'up.user_id' => array(
							'SELECT user_followed_id FROM ' . db_prefix( 'users_follow_user' ) . ' WHERE user_id = ' . $this->user_id, 'IN' 
						) 
					) 
				) );
			break;
		}
		
		return $actions;
	}
	
	
	/**
	 * Displaying the user menu.
	 * Linki Prywatne i Obserwowane wyświetlane są tylko w profilu użytkownika
	 * Links Private and observed are only displayed in the user profile
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function getMenu()
	{
		return Templates::getInc()->getTpl( 'user_files_menu.html', array(
			'user_login' => $this->login,
			'user_files' => $this->myFiles,
			'new_follower_files' => ( $this->myFiles ? $this->checkWatched() : false ) 
		) );
	}
	
	/**
	 * Sprawdzanie czy obserwowani użytkownicy dodali nowe
	 * materiały i wyświetlanie stosownego komunikatu.
	 * Checking whether the monitored users have added new content and display relevant statement.
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function checkWatched()
	{
		if ( Session::get( 'checked_watched' ) < time() )
		{
			$db = PD::getInstance();
			
			$in_authors = $db->from(
				'users_follow_user f' 
			)->where( 'f.user_id', USER_ID )->fields( array(
				'f.user_followed_id' 
			) )->getQuery();
		
			$db->reset();
			
			$last_visit = User_Data::get( $this->user_id, 'user_last_visit' );
			
			$row = $db->from( IPS__FILES . ' up' )
			->where( 'date_add', $last_visit, '>' )
			->where( 'user_id', $in_authors, 'in' )
			->fields( 'COUNT(up.id) as count' )->getOne();
			
			Session::set( 'checked_watched', time() + 3600 );
		}
		
		return ( isset( $row ) && !empty( $row['count'] ) );
	}
}
?>