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

class Messages
{
	
	public $userid = '';
	public $messages = array();
	public $date_format = '';
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		$this->user_id     = USER_ID;
		$this->date_format = "d.m.Y - H:i";
	}

	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function get( $action )
	{
		$this->messages = $this->getMessages( $action );
		
		switch ( $action )
		{
			case 'new':
			case 'read':
			case 'deleted':
			case 'send':
				
				if ( $action == 'send' )
				{
					global ${IPS_LNG};
					
					if ( !empty( $this->messages ) )
					{
						foreach ( $this->messages as $key => $message )
						{
							if ( $message['to_delete'] && !$message['is_readed'] )
							{
								$this->messages[$key]['info'] = ${IPS_LNG}['pw_status_deleted_before_read'];
							}
							elseif ( $message['is_readed'] )
							{
								if ( $message['to_delete'] )
								{
									$this->messages[$key]['info'] = ${IPS_LNG}['pw_status_deleted_after_read'];
								}
								else
								{
									$this->messages[$key]['info'] = ${IPS_LNG}['pw_status_readed'];
								}
							}
							else
							{
								$this->messages[$key]['info'] = ${IPS_LNG}['pw_status_unreaded'];
							}
						}
					}
				}
				
				return Templates::getInc()->getTpl( '/private_messages/' . $action . '_list.html', array(
					'messages_list' => $this->messages 
				) );
				
			break;
			
			case 'view':
				
				
				if ( !empty( $_GET['additional'] ) )
				{
					$message = $this->readMessage( $_GET['additional'] );
					
					if ( empty( $message ) )
					{
						ips_redirect( 'messages/' );
					}
					
					if ( USER_ID == $message['user_to_id'] )
					{
						$this->viewed( $message['id'] );
					}
					
					$user = getUserInfo( false, false, $message['from_user_login'] );
					
					$message['avatar']  = ips_user_avatar( $user['avatar'] );
					$message['message'] = stripslashes( $message['message'] );
					
					if ( $message['is_system_info'] == 0 )
					{
						$message['message'] = nl2br( strip_tags( $message['message'], '' ) );
					}
					
					return Templates::getInc()->getTpl( '/private_messages/view_message.html', $message );
				}
				
				break;
			default:
				
				return $this->get( 'new' );
				
			break;
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getMessages( $type = 'new' )
	{
		switch ( $type )
		{
			
			/**
			 * Reded messages
			 */
			case 'read':
				$result = PD::getInstance()->select( 'users_messages', array(
					'user_to_id' => $this->user_id,
					'is_readed' => 1,
					'to_delete' => 0 
				), null, null, 'viewed_date' );
				
			break;
			
			case "send":
				$result = PD::getInstance()->select( 'users_messages', array(
					'from_user_id' => $this->user_id 
				), null, null, 'created' );
				
			break;
			
			case 'deleted':
				$result = PD::getInstance()->select( 'users_messages', array(
					'user_to_id' => $this->user_id,
					'to_delete' => 1 
				), null, null, 'moved_to_delete_date' );
			break;
			
			/**
			 * New messages/Unread
			 */
			case 'new':
			default:
				
				$result = PD::getInstance()->select( 'users_messages', array(
					'user_to_id' => $this->user_id,
					'is_readed' => 0,
					'to_delete' => 0 
				), null, null, 'created' );
				
			break;
				
		}
		
		if ( !empty( $result ) )
		{
			foreach ( $result as $key => $row )
			{
				$result[$key]['from_user_login']      = $this->getUserLogin( $row['from_user_id'] );
				$result[$key]['user_to_login']        = $this->getUserLogin( $row['user_to_id'] );
				$result[$key]['viewed_date']          = date( $this->date_format, strtotime( $row['viewed_date'] ) );
				$result[$key]['moved_to_delete_date'] = date( $this->date_format, strtotime( $row['moved_to_delete_date'] ) );
				$result[$key]['created']              = date( $this->date_format, strtotime( $row['created'] ) );
			}
			
			return $result;
		}
		
		return false;
	}
	/**
	 * Returning User login
	 *
	 * @param $userid - user id
	 * 
	 * @return bool|int
	 */
	public function getUserLogin( $user_id )
	{
		return getUserInfo( $user_id, false, false, 'login' );
	}
	
	public function readMessage( $message_id )
	{
		
		$row = PD::getInstance()->select( "users_messages", array(
			'id' => $message_id
		), 1 );
		
		if ( !empty( $row ) )
		{
			$row['from_user_login']      = $this->getUserLogin( $row['from_user_id'] );
			$row['user_to_login']        = $this->getUserLogin( $row['user_to_id'] );
			$row['viewed_date']          = date( $this->date_format, strtotime( $row['viewed_date'] ) );
			$row['moved_to_delete_date'] = date( $this->date_format, strtotime( $row['moved_to_delete_date'] ) );
			$row['created']              = date( $this->date_format, strtotime( $row['created'] ) );
			
			return $row;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	 * Mark as readed
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function viewed( $message_id )
	{
		$row = PD::getInstance()->update( 'users_messages', array(
			'is_readed' => 1,
			'ajax_read' => 1,
			'viewed_date' => date( "Y-m-d H:i:s" ) 
		), array(
			'id' => $message_id
		));
		
		return ( $row ) ? true : false;
	}
	
	/**
	 * Mark as deleted
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function toDeleted( $message_id )
	{
		return PD::getInstance()->update( 'users_messages', array(
			'to_delete' => 1,
			'moved_to_delete_date' => date( "Y-m-d H:i:s" ) 
		), array(
			'id' => $message_id
		) );
	}
	
	/**
	 * Delete
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function delete( $message_id )
	{
		return PD::getInstance()->delete( 'users_messages', array(
			'id' => $message_id,
			'user_to_id' => USER_ID
		) );
	}
	
	/**
	 * Send new Message
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function send( $user_to_id, $message_title, $message, $is_system_info = false )
	{
		$user = getUserInfo( USER_ID, true );
				
		if( $user_to_id == USER_ID )
		{
			throw new Exception( 'pw_error_own_account' );
		}
		elseif( $user['user_banned'] != 0 )
		{
			throw new Excception( 'pw_error_ban' );
		}
		elseif( time() - 120 < Session::get( 'ips_pw_time', 0 ) )
		{
			throw new Excception( 'pw_error_flood' );
		}
				
		return PD::getInstance()->insert( "users_messages", array(
			'user_to_id' => $user_to_id,
			'from_user_id' => $user['id'],
			'message_title' => cutWords( $message_title, 255, true ),
			'message' => preg_replace( "/[\r\n]+/", "\n", $message ),
			'created' => date( "Y-m-d H:i:s" ),
			'viewed_date' => '',
			'moved_to_delete_date' => '',
			'is_system_info' => $is_system_info 
		) );
		
	}
	
}
?>