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

class Operations
{
	public function __construct()
	{
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function move( $id, $action )
	{
		$array = '';
		
		switch ( $action )
		{
			/**
			 * Deleting a user account.
			 */
			case 'user_delete':
				
				$u = new Users();
				$u->delete( $id );
			
			break;
			
			/**
			 * Moving to main form waiting room.
			 */
			
			case 'main':
				
				PD::getInstance()->update( IPS__FILES, array(
					'upload_activ' => 1,
					'date_add' => $this->maxDate() 
				), array( 'id' => $id ) );
				
				$this->updateUserStats( $this->getUserId( $id ) );
				
				$fanpage_posting = Config::getArray( 'apps_fanpage_posting' );
				
				if ( $fanpage_posting['on_upload'] )
				{
					if ( Config::get( 'apps_fanpage_post_added' ) == $fanpage_posting['on_upload_count'] || $fanpage_posting['on_upload_count'] == 0 )
					{
						Facebook_Fanpage::postFromId( $id, 'post', array( 
							'fanpage_id' => $fanpage_posting['on_upload_fanpages']
						) );
						Config::update( 'apps_fanpage_post_added', 0 );
					}
					Config::update( 'apps_fanpage_post_added', ( Config::get( 'apps_fanpage_post_added' ) + 1 ) );
				}
			
			break;
			/**
			 * Move from main to waiting.
			 */
			case 'waiting':
				
				PD::getInstance()->update( IPS__FILES, array(
					'upload_activ' => 0,
					'date_add' => $this->maxDate() 
				), array( 'id' => $id ) );
				
				$this->updateUserStats( $this->getUserId( $id ) );
				
			break;
				
			
			/**
			 * Move from private to waiting.
			 */
			case "private-wait":
				
				PD::getInstance()->update( IPS__FILES, array(
					'upload_status' => 'public',
					'date_add' => $this->maxDate() 
				), array( 'id' => $id ) );
				
				$this->updateUserStats( $this->getUserId( $id ) );
				
			break;
			
			/**
			 * Move from archive to waiting.
			 */
			case "archive-wait":
				
				PD::getInstance()->update( IPS__FILES, array(
					'upload_status' => 'public',
					'upload_activ' => 0,
					'date_add' => $this->maxDate() 
				), array( 'id' => $id ) );
				
				$this->updateUserStats( $this->getUserId( $id ) );
				
			break;
			
			/**
			 * Move to archive.
			 */
			case 'archive':
				
				PD::getInstance()->update( IPS__FILES, array(
					'upload_status' => 'archive',
					'upload_activ' => 0,
					'votes_count' => 0,
					'votes_opinion' => 0,
					'date_add' => date( "Y-m-d H:i:s" ) 
				), array( 'id' => $id ) );
				
				Upload_Meta::update( $id, 'votes_up', 0 );
				Upload_Meta::update( $id, 'votes_down', 0 );
				
				$this->updateUserStats( $this->getUserId( $id ) );
			
			break;
			
			/**
			 * Delete file.
			 */
			case 'delete':
				
				$res = PD::getInstance()->select( IPS__FILES, array(
					'id' => $id
				), 1 );
				
				if ( empty( $res ) )
				{
					return false;
				}
				
				$res_article = PD::getInstance()->select( 'upload_text', array(
					'upload_id' => $id 
				), 1 );
				
				$res_ranking = PD::getInstance()->select( 'upload_ranking_files', array(
					'upload_id' => $id 
				), 1, 'src' );
				
				PD::getInstance()->delete( array(
					'upload_text',
					'users_favourites',
					'upload_ranking_files',
					'upload_tags_post',
					'upload_comments',
					'shares'
				), array(
					'upload_id' => $id
				) );
				
				PD::getInstance()->query( 'DELETE FROM ' . db_prefix( 'upload_tags' ) . ' WHERE NOT EXISTS (
					SELECT * FROM ' . db_prefix( 'upload_tags_post tp' ) . ' WHERE tp.id_tag = ' . db_prefix( 'upload_tags' ) . '.id_tag
				)' );
				
				PD::getInstance()->query( 'DELETE FROM ' . db_prefix( 'upload_tags_post' ) . ' WHERE NOT EXISTS (
					SELECT * FROM ' . db_prefix( IPS__FILES . ' up' ) . ' WHERE up.id = ' . db_prefix( 'upload_tags_post' ) . '.upload_id
				)' );
				
				Operations::deleteImages( $res );
				
				if ( file_exists( IPS_VIDEO_PATH . '/' . $res['upload_video'] ) )
				{
					File::deleteFile( IPS_VIDEO_PATH . '/' . $res['upload_video'] );
				}
				
				/* Delete gallery images */
				if ( !empty( $res_article ) && is_serialized( $res_article['long_text'] ) )
				{
					$image = unserialize( $res_article['long_text'] );
					$image = reset( $image );
					
					if ( is_dir( IPS_GELLERY_IMG_PATH . '/' . dirname( $image['src'] ) ) )
					{
						File::deleteDir( IPS_GELLERY_IMG_PATH . '/' . dirname( $image['src'] ) );
					}
				}
				
				/* Delete ranking images */
				if ( !empty( $res_ranking ) )
				{
					preg_match( '@([0-9\-]{10})_([^/]+)/(.*)@', $res_ranking['image'], $matches );
					
					if ( isset( $matches[1] ) && !empty( $matches[1] ) )
					{
						File::deleteDir( IPS_RANKING_IMG_PATH . '/' . $matches[1] . '_' . $matches[2] );
					}
				}
				
				Upload_Meta::delete( $id );
				
				PD::getInstance()->delete( IPS__FILES, array(
					'id' => $id
				) );
				
				$this->updateUserStats( $res['user_id'] );
				
			break;
			
			default:
				return false;
			break;
		}
		
		waitCounterUpdate();
		
		return true;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function maxDate()
	{
		$max_date    = date( "Y-m-d H:i:s" );
		$exists_date = PD::getInstance()->select( IPS__FILES, array(
			'date_add' => array( date( "Y-m-d H:i:s" ), '>' )
		), 1 );
		
		if ( !empty( $exists_date ) )
		{
			$max_date = PD::getInstance()->select( IPS__FILES, false, 1, "DATE_ADD( MAX( date_add ), INTERVAL 2 SECOND) as max_date" );
			
			$max_date = ( empty( $max_date['max_date'] ) ? date( "Y-m-d H:i:s" ) : $max_date['max_date'] );
		}
		
		return ( strpos( $max_date, '0000' ) === false ? $max_date : date( "Y-m-d H:i:s" ) );
	}
	
	/**
	 * Update user ststistics data based on the user's ID
	 *
	 * @param $data - update data
	 * @param $user_login - user login
	 * 
	 * @return void
	 */
	
	public static function updateUserStats( $user_id )
	{
		$db = PD::getInstance();
		
		$db->update( 'users', array(
			'user_boards' => (int) $db->cnt( 'pinit_boards', 'user_id =' . $user_id ),
			'user_uploads' => (int) $db->cnt( IPS__FILES, 'user_id =' . $user_id ),
			'user_comments' => (int) $db->cnt( 'upload_comments', 'user_id =' . $user_id ),
			'user_likes' => (int) $db->cnt( 'pinit_pins_likes', 'user_id =' . $user_id ) 
		), array(
			'id' => $user_id 
		) );
	}
	
	/**
	 * Download ID by ID material, which, he added.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getUserId( $upload_id )
	{
		$res = PD::getInstance()->select( IPS__FILES, array(
			'id' => $upload_id
		), 1 );
		
		if ( !empty( $res ) )
		{
			return isset( $res['user_id'] ) ? $res['user_id'] : false;
		}
		return false;
	}
	
	/**
	 * Links for material visible to admins and moderators.
	 * A normal user sees the links to a waiting room and remove the materials in Private
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function fileModeration( $row )
	{
		$category_options = false;
		if ( $row['upload_status'] == 'public' && Config::get( 'categories_option' )  )
		{
			$category_options = Categories::categorySelectOptions( $row['category_id'] );
		}
		
		return  Templates::getInc()->getTpl( '/__admin/mod_item_actions.html', array(
			'id' => $row['id'],
			'main' => $row['upload_activ'] == 0 && $row['upload_status'] == 'public',
			'wait' => ( $row['upload_activ'] == 1 || ( $row['upload_activ'] == 0 && $row['upload_status'] == 'archive' ) ),
			'upload_status' => $row['upload_status'],
			'wait_action' => ( $row['upload_status'] == 'private' ? 'private-wait' : ( $row['upload_status'] == 'archive' ? 'archive-wait' : 'waiting' ) ),
			'autopost' => ( $row['up_lock'] == 'autopost' ? __( 'actions_mod_autopost_unblock' ) : __( 'actions_mod_autopost_block' ) ),
			'social_lock' => ( $row['up_lock'] == 'social_lock' ? __( 'actions_mod_social_lock_unblock' ) : __( 'actions_mod_social_lock_block' ) ),
			'category_options' => $category_options
		));
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function banUser( $user_id, $user_banned )
	{
		
		if ( USER_MOD )
		{
			global ${IPS_LNG};
			$mod = '
			<div id="user_ban_time" class="user_ban_profile">';
			if ( $user_banned == 1 )
			{
				$user_banned_data = User_Data::get( $user_id, 'user_banned_data' );
				
				$mod .= '<img src="/images/icons/user-icon.png" alt="Ban" /><span>' . __s( 'user_ban_time', $user_banned_data['date_ban'] ) . '</span><span><a href="#" onclick="user_ban_time(' . $user_id . ', \'unban\'); return false;">[' . ${IPS_LNG}['user_ban_unban'] . ']</a></span>';
			}
			else
			{
				$mod .= '
				<img src="/images/icons/user-icon.png"/><span>' . ${IPS_LNG}['user_ban'] . '</span>
				<a href="#" onclick="user_ban_time(' . $user_id . ', \'week\'); return false;">[' . ${IPS_LNG}['user_ban_week'] . ']</a>&ensp;&ensp;
				<a href="#" onclick="user_ban_time(' . $user_id . ', \'month\'); return false;">[' . ${IPS_LNG}['user_ban_month'] . ']</a>&ensp;&ensp;
				<a href="#" onclick="user_ban_time(' . $user_id . ', \'year\'); return false;">[' . ${IPS_LNG}['user_ban_alltime'] . ']</a>&ensp;&ensp;
				';
			}
			return $mod . '</div>';
		}
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function editFileButtons( $id )
	{
		return Templates::getInc()->getTpl( '/__admin/mod_item_actions.html', array(
			'id' => $id
		));
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function reporting( $reported_id, $report_type, $report_message, $file_url, $additional )
	{
		global ${IPS_LNG};
		
		if ( isset( ${IPS_LNG}[$report_message] ) )
		{
			$report_message = ${IPS_LNG}[$report_message];
		}
		
		$row = PD::getInstance()->cnt( db_prefix( 'users u' ) . ' INNER JOIN ' . db_prefix( 'reporting r' ) . ' ON r.user_id = u.id', array(
			'login' => USER_LOGIN,
			'upload_id' => $reported_id,
			'report_type' => $report_type 
		) );
		
		if ( !empty( $row ) )
		{
			return ${IPS_LNG}['report_file_twice'];
		}
		else
		{
			switch ( $report_type )
			{
				case 'file':
					if ( PD::getInstance()->cnt( IPS__FILES, array(
						'id' => $reported_id,
						'user_login' => USER_LOGIN 
					) ) )
					{
						return ${IPS_LNG}['report_own_file'];
					}
				break;
				
				case 'comment':
					if ( PD::getInstance()->cnt( 'upload_comments', array(
						'id' => $reported_id,
						'user_login' => USER_LOGIN 
					) ) )
					{
						return ${IPS_LNG}['report_own_comments'];
					}
				break;
				
				case 'message':
					if ( PD::getInstance()->cnt( 'users_messages', array(
						'id' => $reported_id,
						'from_user_id' => USER_ID 
					) ) )
					{
						return ${IPS_LNG}['report_own_messages'];
					}
				break;
			}
			
			Translate::loadAdminTranslations();
			
			$send = new EmailExtender();
			
			$report_message = str_replace( array(
				'{id}',
				'{file_url}',
				'{report_message}',
				'{report_additional}' 
			), array(
				$reported_id,
				$_POST['file_url'],
				$report_message,
				$_POST['additional'] 
			), ${IPS_LNG}[ 'reporting_message_' . $report_type ] );
			
			$send->EmailTemplate( array(
				'email_to' => Config::get( 'email_admin_user' ),
				'email_content' => $report_message,
				'email_title' => ${IPS_LNG}['reporting_message_' . $report_type . '_title'] 
			) );
			
			if ( Config::get( 'pw_messages_admin' ) == 1 )
			{
				PD::getInstance()->insert( 'reporting', array(
					'user_id' => USER_ID,
					'upload_id' => $reported_id,
					'report_type' => $report_type 
				) );
				
				$users = User_Data::getByValue( 'is_moderator', 1, false );
				
				$messages = new Messages();
				
				foreach ( $users as $user )
				{
					$messages->send( $user['user_id'], ${IPS_LNG}['reporting_message_' . $report_type . '_title'], $report_message, true );
				}
			}
			
			return true;
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function deleteImages( $row )
	{
		$paths = Upload::getPaths();
		
		foreach ( $paths as $path )
		{
			File::deleteFile( $path . '/' . substr( $row['upload_image'], 0, -4 ) . '.gif' );
			File::deleteFile( $path . '/' . $row['upload_image'] );
		}
		
		$backup = ips_img_path( $row['upload_image'], 'backup' );
		
		File::deleteFile( substr( $backup, 0, -4 ) . '.gif' );
		File::deleteFile( $backup );
	}

}
?>