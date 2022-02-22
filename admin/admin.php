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
	
	session_start();
	ob_start();
	//error_reporting(E_ALL);
	require_once( dirname(__FILE__) . '/config.php');
	require_once( IPS_ADMIN_PATH .'/admin-functions.php');
	require_once( IPS_ADMIN_PATH .'/update-functions.php' );

	require_once( IPS_ADMIN_PATH .'/libs/class.SystemTools.php' );
	
	System_Tools::checkSystemDirs();

	$admin_route = !empty( $_GET['route'] ) ? $_GET['route'] : 'null';

	global $av_updates;

	
	echo admin_menu( $av_updates, $admin_route );


	if( $admin_route != 'update' && !Updates::validateLicense( 'only_hash' ) )
	{
		ips_admin_redirect('update');
	}
	
	if( Config::get('updates_disabled') != 'true' )
	{
		$not_downloaded = Updates::get( 'count' );
		
		cronCheckUpdates();
		
		if( $admin_route != 'update' && !defined('IPS_SELF') && substr( IPS_ACTION, 0, 7 ) != 'import-')
		{
			$av_updates = Config::noCache('update_alert_count');
			if( $av_updates > 0 )
			{
				ips_admin_redirect('update', false, __( 'updates_available_alert' ) );
			}
		}
	}
	
	echo '<div id="content" class="content-' . $admin_route . '">';
	
	$msg = Session::getFlash();
	
	if ( !empty( $msg ) )
	{
		echo $msg;
	}
		
	Session::set( 'admin_redirect', admin_url( $admin_route ) );

	if( substr( IPS_ACTION, 0, 7 ) == 'import-' )
	{
		$admin_route = 'action-import';
	}
	
switch( $admin_route )
{

	
	case 'action-import':

		echo admin_caption( 'import_title_' . str_replace( '-', '_', substr( IPS_ACTION, 7 ) ) );
		
		include ( IPS_ADMIN_PATH .'/' . IPS_ACTION . '.php' );

	break;
	
	case 'change_view':

		if( isset( $_GET['thumbs'] ) && $_GET['thumbs'] != 'none' )
		{
			Session::set( 'admin_thumbs', $_GET['thumbs'] );
		}
		else
		{
			Session::clear( 'admin_thumbs' );
		}
		ips_redirect();
	break;
	

	case 'un_ban_user';
		un_ban_user( $_GET );
	break;
	
	case 'geo':
	
		echo geo( $_GET );
	
	break;
	
		
	
	
	case 'admin';
		if( IPS_ACTION_GET_ID )
		{
			if( $_GET['admin-action'] == 'add' )
			{ 
				;
				if( User_Data::update( IPS_ACTION_GET_ID, 'is_moderator', 1 ) && User_Data::update( IPS_ACTION_GET_ID, 'is_admin', 1 ) )
				{
					ips_message( array(
						'info' =>  __( 'moderate_admin_add ' ) .  IPS_ACTION_GET_ID
					) );
				}
			} 
			else if( $_GET['admin-action'] == 'remove' )
			{
				if( User_Data::delete( IPS_ACTION_GET_ID, 'is_admin' ) )
				{
					ips_message( array(
						'info' =>  __( 'moderate_admin_remove' ) .  IPS_ACTION_GET_ID
					) );
				}
			}
		}
		else
		{
			ips_message( array(
				'alert' =>  __( 'moderate_wrong_id' )
			) );
		}
		ips_redirect();
	break;
	
	case 'moderator';
		if( IPS_ACTION_GET_ID )
		{
			if( $_GET['admin-action'] == 'add' )
			{
				if( User_Data::update( IPS_ACTION_GET_ID, 'is_moderator', 1 ) )
				{
					ips_message( array(
						'normal' =>  __( 'moderate_mod_add ' ) .  IPS_ACTION_GET_ID
					) );
				}
			} 
			else if( $_GET['admin-action'] == 'remove' )
				{
				if( User_Data::delete( IPS_ACTION_GET_ID, 'is_moderator' ) )
				{
					ips_message( array(
						'normal' =>  __( 'moderate_mod_remove' ) .  IPS_ACTION_GET_ID
					) );
				}
			}
		}
		else
		{
			ips_message( array(
				'alert' =>  __( 'moderate_wrong_id' )
			) );
		}

		ips_admin_redirect( 'users' );
		
	break;
	
	case 'activate':
		if( activateUser( IPS_ACTION_GET_ID ) )
		{
			ips_message( array(
				'info' => __( 'moderate_account_activated' )
			) );
		}
		else 
		{
			ips_message( array(
				'alert' =>  __( 'moderate_wrong_id' )
			) );
		}
		ips_redirect();
	break;
	case 'delete_files':
		
		deleteFiles();
		ips_redirect( false, array(
			'info' => __( 'moderate_files_deleted' )
		));
	break;
	case 'delete_users':
		deleteUsers();
		ips_redirect( false, array(
			'info' => __( 'moderate_accounts_deleted' )
		));
	break;
	case 'delete_user':
		if( deleteUser( IPS_ACTION_GET_ID ) )
		{
			ips_message( array(
				'info' => __( 'moderate_user_deleted' )
			) );
		}
		else 
		{
			ips_message( array(
				'alert' => __( 'moderate_wrong_id' )
			) );
		}
		ips_redirect();
	break;
	case 'del_com':
		if( deleteComment( IPS_ACTION_GET_ID ) )
		{
			ips_message( array(
				'normal' =>  __( 'moderate_comment_deleted' )
			) );			
		}
		else
		{
			ips_message( array(
				'alert' =>  __( 'moderate_comment_deleted_error' )
			) );
		}		
		ips_redirect(); 
	break;
	case 'akcept':
		if( moveToMain( IPS_ACTION_GET_ID ) )
		{
			ips_message( array(
				'info' =>  __('file_moved')
			) );
		}
		else
		{
			ips_message( array(
				'alert' =>  __( 'moderate_move_error' )
			) );
		}
		ips_redirect();
	break;
	case 'delete_file':
		
		if( deleteFile( IPS_ACTION_GET_ID ) )
		{
			ips_message('file_deleted');					
		}
		ips_redirect();
	break;
	case 'delete_pin':
		
		if( deletePin( IPS_ACTION_GET_ID ) )
		{
			ips_message('file_deleted');					
		}
		ips_redirect();
	break;
	case 'feature_pin':
		
		if( featurePin( IPS_ACTION_GET_ID ) )
		{
			ips_message('file_changed');					
		}
		ips_redirect();
	break;
	case 'delete_board':
		
		if( deleteBoard( IPS_ACTION_GET_ID ) )
		{
			ips_message('pinit_board_deleted');					
		}
		ips_redirect();
	break;
	case 'export_tokens';
		export_tokens();
	break;
	case 'del_facebook':
		
	break;
	case 'pins':
		
		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'sort_by', array(
			'date_add' => 'data',
			'pin_likes' => 'likes',
			'pin_repins' => 'repins',
			'comments' => 'comments',
			'comments_facebook' => 'comments_facebook',
			'upload_views' => 'view_count',
			'pin_featured' => 'pin_featured'
		) )->addSelect( 'privacy', array(
			'all' => 'common_all',
			'public' => 'public',
			'private' => 'private'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->addInputOption( 'login', __( 'pagin_username' ) )->addSortOpt( 'pins' )->wrapSelects()->wrap()->addJS( 'pins', $PD->cnt( IPS__FILES ) )->addMessage('')->get();

	break;
	case 'tags':
		
		$pagin = new Pagin_Tool;	
		
		echo $pagin->addSelect( 'sort_by', array(
			'count_files' => 'pagin_assigned_files',
			't.tag' => 'pagin_alphabetically',
			'post_visibility' => 'visibility'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->addInputOption( 'tag','Tag' )->wrapSelects()->wrap()->addJS( 'tags', $PD->cnt( 'upload_tags' ) )->addCaption( 'caption_tags' )->get();
	
	break;
	
	case 'boards':
		
		
		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'sort_by', array(
			'date_add' => 'data',
			'board_followers' => strtolower( __('pinit_followers') ),
			'board_pins' => 'pinit_pin_repins',
			'board_views' => 'view_count'
		) )->addSelect( 'privacy', array(
			'all' => 'common_all',
			'public' => 'public',
			'private' => 'private'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->addInputOption( 'login', __( 'pagin_username' ) )->wrapSelects()->wrap()->addJS( 'boards', $PD->cnt( 'pinit_boards' ) )->addMessage('')->get();
		

	break;
	
	case 'delete_connection':
		if( isset( $_GET['uid'] ) && substr( $_GET['uid'], 0, -3 ) === 'uid' )
		{
			$deleted = User_Data::delete( false, $_GET['uid'] );
		}

		ips_admin_redirect( 'options', 'action=apps', __s( 'delete_connection', $deleted ) );
	break;
	

	case 'archive':
	case 'wait':
	case 'main':
		
	
		if( $admin_route != 'archive' )
		{
			$numrows =  $PD->cnt( IPS__FILES, array(
				'upload_activ' => ( $admin_route == 'wait' ? 0 : 1 ),
				'upload_status' => 'public'
			));
		}
		else
		{
			$numrows =  $PD->cnt( IPS__FILES, array(
				'upload_status' => 'archive'
			));
		}
	
		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'sort_by', array(
			'date_add' => 'data',
			'votes_opinion' => strtolower( __('common_opinion') ),
			'comments' => 'comments',
			'comments_facebook' => 'comments_facebook',
			'upload_views' => 'view_count'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->addInputOption( 'login', __( 'pagin_username' ) )->addSortOpt( $admin_route )->wrapSelects()->wrap()->addJS( $admin_route, $numrows )->addMessage( __s( 'files_delete_info', $admin_route ) )->addCaption( 'caption_' . $admin_route )->get();
		
	break;
	case 'users':
		 
		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'sort_by', array(
			'user_uploads' => 'added_files',
			'user_comments' => 'comments',
			'u.date_add' => 'register_date',
			'u.activ' => 'user_activ'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->addInputOption( 'login', __( 'pagin_username' ) )->wrapSelects()->wrap()->addJS( 'users', $PD->cnt( 'users' ) )->addMessage('users_delete_info')->addCaption('caption_users')->get();
		
		
	break;
	
	case 'banned_users':
		
		$pagin = new Pagin_Tool;	
				
		echo $pagin->addInputOption( 'login', __( 'pagin_username' ) )->wrapSelects()->wrap()->addJS( 'ban', $PD->cnt( 'users_data', array(
			'setting_key' => 'user_banned_data'
		)) )->addMessage('')->addCaption( 'caption_banned_users' )->get();
		
	break;
	
	case 'comment':

		$pagin = new Pagin_Tool;	
				
		echo $pagin->addSelect( 'sort_by', array(
			'date_add' => 'data',
			'comment_opinion' => strtolower( __('common_opinion') ),
			'comment_votes' => strtolower( __('field_votes') ),
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->wrapSelects()->wrap()->addJS( 'comments', $PD->cnt( 'upload_comments' ) )->addMessage('')->addCaption( 'common_comments_num' )->get();
		
	break;
	
	
	
	

	case 'logout':
		Cookie::destroy( true );
		ips_redirect( 'index.html', 'user_logged_out' );  
	break;


	default:
		
		if( file_exists( IPS_ADMIN_PATH .'/' . $admin_route . '-services.php' ) )
		{
			include ( IPS_ADMIN_PATH .'/' . $admin_route . '-services.php' );
		}
		else
		{
			include ( IPS_ADMIN_PATH .'/statistic-services.php' );
		}
	break;
		
}
	
	
echo '</div>
</div>

		<link rel="stylesheet" media="screen" type="text/css" href="' . ABS_URL . 'libs/ColorPicker/ColorPicker/css/colorpicker.css" />
		<script type="text/javascript" src="' . ABS_URL . 'libs/ColorPicker/ColorPicker/js/colorpicker_merged_ips.js"></script>	
		<script type="text/javascript">
			dialogCss = \'' . Config::getArray('js_dialog', 'style') . '\';
		</script>
		<link rel="stylesheet" href="' . IPS_ADMIN_URL . '/css/colorPicker.css" type="text/css" />
		
		
		<link rel="stylesheet" href="' . ABS_URL . '/libs/Chosen/chosen.min.css" type="text/css" />
		<script type="text/javascript" src="' . ABS_URL . '/libs/Chosen/chosen.jquery.min.js"></script>	
		
		<script type="text/javascript">
			function chosenWrap( select )
			{
				select.chosen({
					placeholder_text_multiple: "' . __('admin_chosen_placeholder') . '",
					disable_search_threshold: 10,
					disable_search: true,
					width : "50%"
				})
			}
			$("select").each(function(){
				if( !$(this).hasClass("ddslick") && !$(this).parents(".tinyeditor").length )
				{
					chosenWrap( $(this) )
				}
			})
		</script>
		
</body></html>';


?>