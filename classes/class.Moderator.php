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

class Moderator extends Operations
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function moderate( $action, $id )
	{
		if( is_numeric( $id ) && $this->can( $action, $id ) )
		{
			switch( $action )
			{
				case 'main':	
				case 'waiting':
				case 'private-wait':
				case 'archive-wait':
				case 'archive':
				
					if( $this->move( $id, $action ) )
					{
						return [
							'content' => __( 'file_moved' ),
							'remove' => true
						];
					}
				break;
				
				case 'delete':	
					
					if( $this->move( $id, 'delete'  ) )
					{
						return [
							'content' => __( 'file_deleted' ),
							'url' => ABS_URL,
							'remove' => true
						];
					}
				break;
				
				case 'contest_delete_caption':
					if( PD::getInstance()->delete( 'contests_captions', array( 'id' => $id ) ) )
					{	
						return [
							'content' => __( 'file_deleted' )
						];
					}
				break;
				
				case 'delete_comment':
					if( PD::getInstance()->delete( 'upload_comments', array( 'id' => $id )) )
					{	
						return [
							'content' => __( 'comments_deleted' )
						];
					}
				break;
				
				
				case 'adult':
					
					$row = PD::getInstance()->select( IPS__FILES, array( 
						'id' => $id
					), 1 );
					
					if( PD::getInstance()->update( IPS__FILES, array( 'upload_adult' => !$row['upload_adult'] ), array( 'id' => $id ) ) )
					{
						return [
							'content' => __( 'system_action_success' )
						];
					}
				break;
				case 'social_lock':
					$row = PD::getInstance()->select( IPS__FILES, array( 
						'id' => $id
					), 1 );
					
					if( PD::getInstance()->update( IPS__FILES, array( 'up_lock' => ( $row['up_lock'] == 'social_lock' ? 'off' : 'social_lock' ) ), array( 'id' => $id ) ) )
					{
						return [
							'content' => __s( 'autopost_file_blocked', __( $row['up_lock'] != 'social_lock' ? 'autopost_social_lock_status' : 'autopost_unsocial_lock_status' )  )
						];
					}
					else
					{
						return [
							'alert' => __( 'error_mysql_query' )
						];
					}
				break;
				case 'autopost':
					$row = PD::getInstance()->select( IPS__FILES, array( 
						'id' => $id
					), 1 );
					
					if( PD::getInstance()->update( IPS__FILES, array( 'up_lock' => ( $row['up_lock'] == 'autopost' ? 'off' : 'autopost' ) ), array( 'id' => $id ) ) )
					{
						return [
							'content' => __s( 'autopost_file_blocked', __( $row['up_lock'] != 'autopost' ? 'autopost_blocked_status' : 'autopost_unblocked_status' )  )
						];
					}
					else
					{
						return [
							'alert' => __( 'error_mysql_query' )
						];
					}
				break;
				
				case 'facebook':
					if( Facebook_UI::isAppValid() )
					{					
						Session::set( 'ips-redirect', $_SERVER['HTTP_REFERER'] );
						
						return [
							'url' => admin_url( 'fanpage', 'id=' . $id )
						];
					}
					else
					{
						return [
							'alert' => __( 'apps_credintials_error' )
						];
					}
				break;
				
				case 'category_change':
				
					$category_id = (int)$_POST['category_id'];
					
					if ( Categories::exists( $category_id ) )
					{
						PD::getInstance()->update( IPS__FILES, array( 
							'category_id' => $category_id
						), array( 
							'id' => $id
						));
		
						Ips_Cache::clearCacheFiles();
						
						return [
							'content' => __( 'category_saved' )
						];
					}
					return [
						'error' => __( 'category_not_exists' )
					];
				break;
				
				default:
					return [
						'alert' => 'Pusty parametr akcji'
					];
				break;
			}
		
		}
		
		return [
			'error' => __( 'user_permissions' )
		];
	}
	
	public function can( $action, $id )
	{
		if( !USER_ADMIN )
		{
			if( !USER_MOD )
			{
				if( $action == 'private-wait' || $action == 'delete' )
				{
					$count = PD::getInstance()->cnt( IPS__FILES, array(
						'user_id' => USER_ID,
						'upload_status' => 'private',
						'id' => $id
					));
					
					if( $count > 0 )
					{
						return true;
					}
				}
				
				return false;
			}
			else
			{
				return Config::getArray( 'mod_privileges', $action ) == 1;
			}
		}
		
		return true;
	}
}