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

class History
{
	/**
	 * 
	 * Saving the actions done with proper parameters before the function call.
	 * 
	 * @param string $action - name that identifies the selected action
	 * @param mixed $additional - Additional information on the requested action
	 */

	public function storeAction( $action, $additional = null )
	{
		if ( Config::getArray( 'module_history_actions', $action ) )
		{
			if ( !isset( $additional['user_id'] ) && !USER_ID )
			{
				return false;
			}
			
			$args = array(
				'action' => $action,
				'user_id' => ( isset( $additional['user_id'] ) ? $additional['user_id'] : USER_ID ),
				'date_add' => date( "Y-m-d H:i:s" ) 
			);
			
			if ( isset( $additional['upload_id'] ) && is_numeric( $additional['upload_id'] ) )
			{
				$args['object_id'] = $additional['upload_id'];
			}
			
			$args['object_name'] = isset( $additional['action_name'] ) ? $additional['action_name'] : 'empty';
			
			$this->saveAction( $args );
		}
	}
	
	/**
	 * Saving shares already prepared an array history.
	 * @param array $data - he has done user action
	 */
	private function saveAction( $data )
	{
		if ( is_array( $data ) && !empty( $data ) )
		{
			$exists = PD::getInstance()->cnt( 'history', array_diff( $data, array(
				$data['date_add'] 
			) ) );
			
			if ( $exists )
			{
				if ( $data['action'] == 'login'  )
				{
					$cond = array( 
						'action' => $data['action'], 
						'user_id' => $data['user_id']
					);
				}
				else
				{
					$cond = array( 
						'object_id' => $data['object_id'], 
						'object_name' => $data['object_name'], 
						'user_id' => $data['user_id']
					);
				}
				
				PD::getInstance()->update( 'history', $data, $cond );
			}
			else
			{
				PD::getInstance()->insert( 'history', $data );
			}
		}
		
	}
	

	
	/**
	 * Get a history of visited pages and actions
	 * 
	 * @return mixed $history - return a list of actions taken by user/all users
	 */
	
	
	public function getUsersHistory( $user_id = USER_ID )
	{
		
		$query = PD::getInstance()->from( 'history h' )
			->join( IPS__FILES . ' up', 'LEFT OUTER' )
			->on( 'h.object_id', 'up.id' )
			->fields('h.*, up.title, up.upload_image')
			->orderBy( 'h.date_add' )
			->limit(50);
		
		if ( $user_id == 'all' )
		{
			$query = $query->join( 'users u', 'LEFT OUTER' )
				->on( 'h.user_id', 'u.id' )
				->fields('u.login,u.avatar');
		}
		
		$res = $query->get();

		if ( !empty( $res ) )
		{
			foreach ( $res as $key => $row )
			{
				$file_img = $link = '';
				
				if ( isset( $row['login'] ) )
				{
					if ( empty( $row['login'] ) )
					{
						/** User not exists */
						PD::getInstance()->delete( 'history', array(
							'id' => $row['id']
						) );
						continue;
					}
					
					$res[$key]['user'] = array(
						'avatar' => ips_user_avatar( $row['avatar'], 'url' ),
						'login' => $row['login'] 
					);
				}
				
				if ( !empty( $row['object_id'] ) )
				{
					if( empty( $row['title'] ) )
					{
						PD::getInstance()->delete( 'history', array(
							'id' => $row['id']
						) );
						continue;
					}
					
					$res[$key]['object'] = array(
						'url' => seoLink( $row['object_id'], $row['title'] ),
						'img' => ips_img_cache( $row, '20x20' ) 
					);
					
					if ( isset( $row['title'] ) )
					{
						$link = '<a href="' . $res[$key]['object']['url'] . '">' . cutWords( $row['title'], 50 ) . '</a>';
					}
				}
				
				if ( $row['action'] == 'favorites' )
				{
					$row['action'] .= '-' . $row['object_name'];
				}
				
				$res[$key]['content'] = __s( 'widget_history_' . $row['action'], $link, formatDate( $row['date_add'] ), $row['object_name'] );
			}
		}
		
		if ( empty( $res ) )
		{
			return $user_id == 'all' ? '' : __( 'widget_history_none' );
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_history.html', array(
			'history' => $res,
			'users' => $user_id 
		) );
		
	}
	
	/**
	 * Cleaning the whole history of visited pages and actions.
	 *
	 * @param int $days - quantity of days back above that clear history
	 *
	 */

	public function clearHistory( $days = 60 )
	{
		PD::getInstance()->delete( 'history', "DATE_ADD(date_add, INTERVAL " . intval( $days ) . " DAY) < now()" );
	}
}