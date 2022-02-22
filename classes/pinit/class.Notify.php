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
	
class Notify{
	
	private static $instance = false;
	
	public static function getInstance()
	{
        if( !self::$instance )
        {
            return new Notify();
        }
        else
        {
            return self::$instance;
        }
    }
	/**
	* Display template in user panel, right side of menu
	*
	* @param $user_id
	* 
	* @return string
	*/
	public function notifications( $user_id )
	{
		if( is_array( $user_id ) )
		{
			if( !isset( $user_id['user_id'] ) )
			{
				throw new Exception('Not valid user_id for notiFy');
			}
			$user_id = $user_id['user_id'];
		}
		
		if( !$cached = Ips_Cache::get( $this->getCacheFilename( $user_id ) ) )
		{

				$notifications = $this->getByUser( $user_id );
		
				if( !empty( $notifications ) )
				{
					foreach( $notifications as $key => $item )
					{
						$content = unserialize( $item[ 'notify_content' ] );

						$content['content'] = __replace( 'pinit_notify_' . $item['notify_type'] . ( isset( $content['content']['{count}'] ) ? '_more' : '' ), $content['content'] );
						
						$notifications[ $key ]['notify_content'] = $content;
					}
				}
				
				$cached = Templates::getInc()->getTpl( '/modals/notifications_panel.html',array(
					'items'	=> $notifications,
				) );
			
			Ips_Cache::write( $cached, $this->getCacheFilename( $user_id ) );
			
		}
		
		return $cached;
	}
	
	/**
	* Return notifications cache filename
	*
	* @param 
	* 
	* @return 
	*/
	public function getCacheFilename( $user_id )
	{
		return IPS_PINIT_C_NOTIFY . md5( $user_id );
	}
	
	/**
	* Get all users notifications
	*
	* @param $user_id
	* @param $notification_type
	* 
	* @return array
	*/
	public function getByUser( $user_id, $notification_type = null )
	{
		$notifications = PD::getInstance()->select( 'users_notifications', array(
			'notify_user_id' => USER_ID
		));
		
		PD::getInstance()->update( 'users_notifications', array(
			'notify_viewed' => 1
		), array( 
			'notify_user_id' => USER_ID
		), null, false );
		
		return $notifications;
	}
	
	/**
	* Send user notification with formatted message
	*
	* @param $user_id
	* @param $data
	* 
	* @return bool
	*/
	public function notifyUser( $data )
	{
		if( !isset( $data['type'] ) )
		{
			throw new Exception('Empty parameters Notify::notifyUser');
		}

		if( isset( $data['user_id'] ) )
		{
			/** !empty in other IF - form upload PIN users */
			if( !empty( $data['user_id'] ) )
			{
				return $this->createNotify( $data );
			}
			return false;
		}
		
		if( isset( $data['user_email'] ) && !empty( $data['user_email'] ) )
		{
			if( !Sanitize::validatePHP( $data['user_email'], 'email') )
			{
				throw new Exception('Not valid email');
			}
			
			$user = PD::getInstance()->select( 'users', array(
				'email' => $data['user_email']
			), 1, 'id' );
			
			if( empty( $user ) )
			{
				$send = new EmailExtender();
				return $send->EmailTemplate( array(
					'email_to'		=> $data['user_email'],
					'email_content'	=> 'User recomends You a PIN: ' . $data['send_message'],
					'email_title'	=> 'Reccomended PIN'
				) );
			}
			else
			{
				$data['user_id'] = $user['id'];
				return $this->createNotify( $data );
			}
		}
		ips_log( $data );
		ips_log( 'Not valid data for notifyUser' );
	}
	
	/**
	* Save user notification in database to display in user panel
	*
	* @param $user_id
	* @param $data
	* 
	* @return bool
	*/
	public function formatNotify( $user_id, &$data, &$user_from )
	{
		if( $notify_content = $this->check( $user_id, $data, $user_from ) )
		{
			return $notify_content;
		}
		
		$notify_content = array(
			'user' => '','content' => '','object' => '','message' => '','redirect' => ''
		);
		
		if( isset( $data['send_message'] ) )
		{
			$notify_content['message'] = htmlspecialchars( $data['send_message'] );
		}
		
		switch( $data['type'] )
		{
			/* Send Pin */
			/* Followed user added new Pin */
			
			case 'new_pin':
			case 'send_pin':
				
				$notify_content = array(
					'user' => array(
						'url'	=> $user_from['login'] . '/pins',
						'image' => $user_from['avatar_link'],
					),
					'content' => array(
						'{full_name}'	=> $user_from['full_name']
					),
					'object' => array(
						'url'	=> 'pin/' . $data['pin_id'],
						'image'	=> ips_img( Pin::getField( $data['pin_id'], 'upload_image' ), 'thumb' ),
					),
					'redirect' => 'pin/' . $data['pin_id'],
				);
			break;
			
			/* Send Board */
			/* Followed user added new Board */
			/* Invite to Pin in Board */
			/* New Pin in followed Board */
			case 'new_board':
			case 'send_board':
			case 'board_invite':
			case 'new_board_pin':	
				
				$b = new Board();
				$cover = new BoardCover;
				
				$notify_content = array(
					'user' => array(
						'url'	=> $user_from['login'] . '/pins',
						'image' => $user_from['avatar_link'],
					),
					'content' => array(
						'{full_name}'	=> $user_from['full_name']
					),
					'object' => array(
						'url'	=> 'board/' . $data['board_id'],
						'image'	=> $cover->getThumb( $b->getBoard( $data['board_id'] ) ),
					),
					'redirect' => 'board/' . $data['board_id'],
				);

			break;

			
			case 'new_followers':
			
			break;
			
			case 'board_invite_delete':
				
			break;
		}
		
		return $this->cache( $data['type'], $notify_content );
	}
	

	/**
	* Save user notification in database
	*
	* @param $user_id
	* @param $data
	* 
	* @return bool
	*/
	public function createNotify( &$data )
	{
		$notification = array();
		
		if( !is_array( $data['user_id'] ) )
		{
			$data['user_id'] = array ( $data['user_id'] );
		}
		
		if( empty( $data['user_id'] ) )
		{
			return false;
		}
		
		if( in_array( USER_ID, $data['user_id'] ) )
		{
			/** Not self notify */
			unset( $data['user_id'][ array_search( USER_ID, $data['user_id'] ) ] );
		}
		
		$notification['notify_sender_id'] = USER_ID;
		$notification['notify_type'] = $data['type'];
		$notification['notify_message'] = ( isset( $data['send_message'] ) && !empty( $data['send_message'] ) ? $data['send_message'] : '' );
		
		$user_from = getUserInfo( USER_ID, true );
		/**
		* Foreach - for add to more than one user, like followers
		*/
		foreach( $data['user_id'] as $user_id )
		{
			if( is_array( $user_id ) )
			{
				$user_id = $user_id['user_id'];
			}
			
			Ips_Cache::clear( $this->getCacheFilename( $user_id ) );
			
			$notification['notify_content'] = serialize( $this->formatNotify( $user_id, $data, $user_from ) );
			$notification['notify_user_id'] = $user_id;
			
			PD::getInstance()->insert('users_notifications', $notification );
		}
		
		return true;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function check( $user_id, $data, $user_from )
	{
		$exists_not_read = PD::getInstance()->select( 'users_notifications', array(
			'notify_type' => $data['type'],
			'notify_user_id' => $user_id,
			'notify_sender_id' => $user_from['id'],
			'notify_viewed' => 0
		), 1, 'notify_id, notify_content' );
		
		if( !empty( $exists_not_read ) )
		{
			$notify_content = unserialize( $exists_not_read['notify_content'] );
			
			$notify_content['content']['{count}'] = isset( $notify_content['content']['{count}'] ) ? $notify_content['content']['{count}'] + 1 : 2;
			
			PD::getInstance()->delete('users_notifications', array(
				'notify_id' => $exists_not_read['notify_id']
			) );
			
			/** Chenge link to one Pin to users profile */
			if( $data['type'] == 'new_pin' )
			{
				$notify_content['redirect'] = 'profile/' . $user_from['login'] ;
			}
			
			return $notify_content;
		}
		elseif( isset( $this->cached[ $data['type'] ] ) )
		{
			return $this->cached[ $data['type'] ];
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
	public function cache( $type, $notify_content )
	{
		$this->cached[$type] = $notify_content;
		
		return $notify_content;
	}
}