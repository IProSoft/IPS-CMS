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
	ignore_user_abort(true);
	if( !defined( 'IPS_AJAX' ) )
	{
		define( 'IPS_AJAX', true );
	}

/* 	function ajax_pinit()
	{
		define( 'IPS_PINIT_AJAX', true );

			<a href="#" data-href="/ajax/pinit/function_name" class="ips-modal-show">dsfsf</a>
			$_GET['modal_title'] = '';
			$_GET['modal_content'] = '' ;


		//echo json_encode( $_array );
		if( !isset( $_GET['id'] ) && empty( $_GET['id'] ) )
		{
			return 'false';
		}

		if( strpos( $_GET['id'], '--' ) !== false )
		{
			list( $_GET['id'], $_GET['action'] ) = explode( '--', $_GET['id'] );
		}

		$function_name = 'pinit_' . $_GET['id'];

		require_once( CLASS_PATH . '/pinit/functions.php');

		if( function_exists( $function_name ) )
		{
			return $function_name();
		}

		die();
	} */
	/**
	 *
	 */
	function ajax_animation()
	{
		include_once( ABS_PATH . '/functions-upload.php' );

		$upload_animation = new Upload_Animation();
		
		if( get_input('reset') )
		{
			return $upload_animation->reset();
		}

		return $upload_animation->generate();
	}
	/**
	 *
	 */
	function ajax_api_nk()
	{
		if( Nk_UI::isAppValid() )
		{
			require_once( LIBS_PATH . '/nk-php-sdk/src/NK.php' );

			$auth = new NKConnect( array(
				'permissions'		=> array( NKPermissions::BASIC_PROFILE, NKPermissions::EMAIL_PROFILE, NKPermissions::CREATE_SHOUTS),
				'key'				=> Config::get('apps_nk_app', 'app_key'),
				'secret'			=> Config::get('apps_nk_app', 'app_secret'),
				'login_mode'		=> NKConnect::MODE_POPUP,
				'callback_url'		=> ABS_URL . 'connect/nk/true/'
			) );

			$auth->handleCallback();

			if ( !USER_LOGGED && Config::get('apps_auto_login_enabled', 'nk') && $auth->authenticated() )
			{
				$user = $auth->getService()->me();
				//ips_log($user);print_r($_SESSION);

				$user_id = User_Data::getByValue( 'nk_uid', $user->id() );

				if( $user_id )
				{
					$login = new Users();
					$login->user = $result;
					$login->setLogged( $user_id );

					return array(
						'reload' => true
					);
				}

				return array(
					'redirect' => true
				);
			}

			return array(
				'button' => '<a href="javascript:NkConnectPopup();"><img border="0" src="http://nk.pl/img/oauth2/connect"></a>',
				'url' => $auth->nkConnectLoginUri()
			);
		}
	}
	/**
	 *
	 */
	function ajax_api_facebook()
	{
		/** Store user long live acces token Facebook and save userID */
		if( !empty( $_POST ) && isset( $_POST['ui_action'] ) )
		{
			switch( $_POST['ui_action'] )
			{
				case 'user_social_lock':
					if( isset( $_POST['url'] ) )
					{
						preg_match("@" . ABS_URL . "([0-9]{1,})/([A-Za-z0-9_]+)@siU", urldecode( $_POST['url'] ), $matches );

						if( !empty( $matches[1] ) )
						{
							Session::setChild( 'ips_social_lock', null, intval( $matches[1] ) );
							Session::set( 'ips_social_lock_time', time() + 60 * Config::getArray( 'apps_social_lock_options', 'wait_time' ) );
							return [
								'content' => true
							];
						}
					}
					return [
						'content' => false
					];
				break;
				case 'user_posted':
					if( isset( $_POST['response']['id'] ) )
					{
						Session::setChild( 'ips_posted', null, $_POST['file_id'] );
						Session::set( 'ips_posted_time', time() + 60 * Config::getArray( 'apps_facebook_autopost_options', 'wait_time' ) );
					}
				break;
				
				case 'update_stats':
					if( isset( $_POST['url_data'] ) && is_array( $_POST['url_data'] ) )
					{
						new Social_Count( $_POST['url_data'] );
					}
				break;

				case 'update_comments':
					if( isset( $_POST['url'] ) )
					{
						/** Update facebook comments count after add/remove comment by user */
						$file_id = link_get_id( urldecode( $_POST['url'] ) );

						if( isset( $_POST['url'] ) && $file_id )
						{
							$request = Facebook_UI::getApp()->get( '/' . $_POST['url'] . '/comments', Facebook_UI::getDefaultAccessToken() )->getDecodedBody();

							if( isset( $request['share']['comment_count'] ) )
							{
								PD::getInstance()->update( IPS__FILES, array(
									'comments_facebook' => $request['share']['comment_count']
								), array(
									'id' => $file_id
								));
							}
						}
					}
				break;

				case 'delete_connect':
					if( isset( $_POST['connect_type'] ) )
					{
						User_Data::delete( USER_ID, 'access_token_long_live' );
						if( $_POST['connect_type'] == 'account' )
						{
							User_Data::delete( USER_ID, 'facebook_uid' );
						}
					}
				break;

				case 'connect':
					if( isset( $_POST['user_data'] ) && USER_ID )
					{
						$access_token = Facebook_UI::exchangeToken( $_POST['user_data']['accessToken'] );
						
						if( $access_token )
						{
							User_Data::update( USER_ID, 'access_token_long_live', $access_token );

							User_Data::update( USER_ID, 'facebook_uid', $_POST['user_data']['userID'] );

							if( $_POST['post_facebook'] )
							{
								User_Data::update( USER_ID, 'post_facebook', 1 );
							}
						}
					}
				break;
			}
		}
		return true;
	}
	/**
	 *
	 */
	function ajax_auth_login()
	{
		return array(
			'phrase' => __( 'common_login' ),
			'content' => Templates::getInc()->getTpl( 'dialog_login.html' )
		);
	}
	/**
	 *
	 */
	function ajax_auth_register()
	{
		return array(
			'phrase' => __( 'user_register_rules_url' ),
			'user_register_rules_url' => __s( 'user_register_rules', Page::url( array(
				'post_uid' => 'uid_rules'
			) ) ),
			'content' => Templates::getInc()->getTpl( 'dialog_register.html' )
		);
	}
	/**
	 *
	 */
	function ajax_avatar_upload()
	{
		try{
			$upload = new Upload();
			$upload->uploadFile();

			Config::tmp( 'add_max_file_size', 15 );

			$file = $upload->Load( ips_user_avatar( rand() . rand(), 'file' ), array(
				'resize' => 150
			)  );

			if( PD::getInstance()->update( 'users', array( 'avatar' => $file['name'] ), array( 'id' => USER_ID ) ) )
			{
				return array(
					'succes' => ips_user_avatar( $file['name'], 'url' )
				);
			}
			throw new Exception('SQL error');

		} catch ( Exception $e ) {
			return array(
				'error' => __( $e->getMessage() )
			);
		} 
	}
	/**
	 *
	 */
	function ajax_comment_load()
	{
		$CacheFilename = md5( IPS_ACTION_GET_ID . 'ajax_comment_load' );

		if(  Config::get('system_cache', 'comments') == 0 || !$load = Ips_Cache::get( $CacheFilename, false, Config::get('system_cache', 'comments_expiry') ) )
		{
			$comments = new Comments();

			if( !$load = $comments->get( IPS_ACTION_GET_ID )->load() )
			{
				return array(
					'content' => __( 'comments_empty_list' )
				);
			}

			if( Config::get('system_cache', 'comments') )
			{
				Ips_Cache::write( $load, $CacheFilename );
			}
		}
		return array(
			'content' => $load,
			'best_comments' => ( Config::get('widget_top_comments') ? Widgets::bestComments( IPS_ACTION_GET_ID ) : '' )
		);
	}
	/**
	 *
	 */
	function ajax_comment_add()
	{
		if( !IPS_ACTION_GET_ID || empty( $_POST['comment_field'] ) )
		{
			return array(
				'error' => __( 'err_unknown' )
			);
		}

		$user = getUserInfo( USER_ID, true );

		if( USER_LOGGED )
		{
			if ( $user['date_add'] >= date("Y-m-d H:i:s", (time() - ( Config::get( 'user_account', 'comment_wait_time' ) * 3600 ) ) ))
			{
				return array(
					'error' => __s( 'comments_flooding_register', Config::get( 'user_account', 'comment_wait_time' ) )
				);
			}
			elseif( $user['user_banned'] == 1 )
			{
				return array(
					'error' => __( 'comments_add_banned' )
				);
			}
		}
		elseif( !Config::getArray( 'user_guest_option', 'comment' ) )
		{
			return array(
				'error' => __( 'comments_add_logged' )
			);
		}

		if( Config::get('services_premium') &&  !Premium::getInc()->premiumService( 'comments' ) )
		{
			return array(
				'error' => __( 'premium_access' )
			);
		}

		$flooding = PD::getInstance()->cnt( 'upload_comments', array(
			'date_add' => array( date( "Y-m-d H:i:s", ( time() - ( 60 * Config::getArray( 'comments_options', 'flooding_time' ) ) ) ), '>' ),
			'user_login' => USER_LOGIN
		));

		if ( $flooding > 0 )
		{
			return array(
				'error' => sprintf( __( 'comments_flooding_time' ), Config::getArray( 'comments_options', 'flooding_time' ) )
			);
		}

		$reply_to_id = ( isset( $_POST['reply_to_id'] ) && $_POST['reply_to_id'] != 0 ? (int)$_POST['reply_to_id'] : 0 );

		try{

			$comment = new Comments();

			return $comment->add( $_POST['comment_field'], IPS_ACTION_GET_ID, $reply_to_id, $user );

		} catch (Exception $e) {

			return array(
				'error' => __( $e->getMessage() )
			);
		}

	}
	/**
	 *
	 */
	function ajax_moderation()
	{
		return Ips_Registry::get('Moderator')->moderate( get_input('action'), IPS_ACTION_GET_ID );
	}
	/**
	 *
	 */
	function ajax_moderate_comment()
	{
		if( USER_MOD && USER_LOGGED && IPS_ACTION_GET_ID && isset( $_POST['action'] ) )
		{
			$comment = PD::getInstance()->select( 'upload_comments', array(
				'id' => IPS_ACTION_GET_ID,
				'user_login' => USER_LOGIN
			), 1 );

			if( !empty( $comment ) || USER_MOD )
			{
				if( $_POST['action'] == 'delete' )
				{

					if( PD::getInstance()->delete( 'upload_comments', array( 'id' => IPS_ACTION_GET_ID )) )
					{
						PD::getInstance()->increase( IPS__FILES, array(
							'comments' => -1
						), array(
							'id' => $comment['upload_id']
						));

						return array(
							'success' => __( 'comments_deleted' )
						);
					}
				}
				elseif( isset( $_POST['content'] ) )
				{
					$content = Sanitize::cleanXss( $_POST['content'] );

					if( isset( $_POST['comment_modified'] ) && $_POST['comment_modified'] == 'true' )
					{
						$content .= '<br /><br /><span class="comment_modified">' . __( 'comments_modified_by' ) . USER_LOGIN . ' ' . date('Y-m-d H:i:s')  .'</span>';
					}

					if( PD::getInstance()->update( 'upload_comments', array( 'content' => $content ), array( 'id' => IPS_ACTION_GET_ID )) )
					{
						return array(
							'success' => __( 'comments_changed' ),
							'content' => $content
						);
					}
				}
			}
			return array(
				'error' => __( 'err_unknown' )
			);
		}

		return array(
			'error' => __( 'user_permissions' )
		);
	}
	/**
	 *
	 */
	function ajax_search_smilar()
	{
		$action = get_input( 'search_by' );
		$search_by = get_input( 'search_by' );

		if( $action && $search_by )
		{
			$search_by = Sanitize::cleanSQL( $search_by );

			$smilar = new Smilar;

			if( $action == 'upload' )
			{
				$found = $smilar->smilarUpload( $search_by );

				if( $found )
				{
					return array(
						'content' => $found
					);
				}
				else
				{
					return array(
						'content' => '<div class="smilar_text">' . sprintf( __( 'search_results_found' ), 0 ) . '</div>'
					);
				}
			}
			/** Search by title for search widget */
			elseif( $action == 'search_widget' )
			{
				$found = $smilar->smilarSearchWidget( $search_by );

				if( $found )
				{
					return array(
						'content' => $found
					);
				}
			}
		}
		return array();
	}
	/**
	 *
	 */
	function ajax_contact_form()
	{
		if( isset( $_POST['message'] ) && isset( $_POST['email'] ) )
		{
			$send = new  EmailExtender();
			$send->EmailTemplate( array(
				'email_to'		=> Config::get('email_admin_user'),
				'email_content'	=> __admin( 'email_contact_form', $_POST['contact_category'], $_POST['message'], $_POST['email'], $_POST['name'] ),
				'email_title'	=> __admin( 'email_contact_form_title', __admin( $_POST['contact_category'] ) )
			) );
		}
	}
	/**
	 *
	 */
	function ajax_contest()
	{
		if( USER_LOGGED )
		{
			try{

				$contest = new Contest;

				$action = get_input( 'search_by' );

				if( $action == 'add'  )
				{
					$success = $contest->addCaption( IPS_ACTION_GET_ID, Sanitize::cleanXss( $_POST['caption'] ), Sanitize::cleanXss( $_POST['caption_title'] ) );

					if( $success )
					{
						return array(
							'success' => __( 'contest_caption_added' )
						);
					}
				}
				elseif(  $_POST['action'] == 'delete'  )
				{
					if( PD::getInstance()->delete( 'contests_captions', array( 'id' => IPS_ACTION_GET_ID ) ) )
					{
						return array(
							'success' => __( 'contest_caption_deleted' )
						);
					}
				}
				elseif(  $_POST['action'] == 'vote'  )
				{
					$success = $contest->voteCaption( IPS_ACTION_GET_ID, (int)$_POST['contest_id'], $_POST['value'] );

					if( $success )
					{
						return array(
							'success' => __( 'contest_voted' )
						);
					}
				}

			} catch (Exception $e) {

				return array(
					'error' => __( $e->getMessage() )
				);
			}

			return array(
				'error' => __( 'err_unknown' )
			);

		}
		else
		{
			return array(
				'error' => __( 'user_only_logged' )
			);
		}
	}

	/**
	 *
	 */
	function ajax_user_personalize()
	{
		$cookie = array();

		if( Cookie::exists( 'user_personalize' ) )
		{
			$cookie = json_decode( Cookie::get( 'user_personalize' ), true );
		}

		if( isset( $_POST['reset'] ) )
		{
			$cookie = array();
		}
		else
		{
			if( isset( $_POST['personalize'] ) )
			{
				$cookie = $_POST['personalize'];
			}
		}
		Cookie::set( 'user_personalize', json_encode( $cookie ) );
	}

	/**
	 *
	 */
	function ajax_user_ban()
	{
		if( USER_MOD )
		{
			if ( IPS_ACTION_GET_ID && $_POST['action'] != '' )
			{
				$user = new Users;

				if( $_POST['action'] == 'unban' )
				{
					if( $user->unBan( IPS_ACTION_GET_ID ) )
					{
						return array(
							'success' => 'Ban został zdjęty'
						);
					}
				}
				else
				{
					if( $ban_time = $user->ban( IPS_ACTION_GET_ID, $_POST['action'] ) )
					{
						return array(
							'success' => __s( 'user_ban_time', $ban_time )
						);
					}
				}

				return array(
					'error' => __( 'js_alert' ),
					'message' => __( 'user_error_not_exists' )
				);
			}
			else
			{
				return array(
					'error' => __( 'js_alert' ),
					'message' => __( 'err_unknown' )
				);
			}
		}

		return array(
			'error' => __( 'js_alert' ),
			'message' => __( 'user_permissions' )
		);
	}
	/**
	 *
	 */
	function ajax_private_messages()
	{
		if( !USER_LOGGED )
		{
			return false;
		}

		if( $_GET['id'] == 'count' )
		{
			return array(
				'content' => PD::getInstance()->cnt( 'users_messages', array(
					'ajax_read' => 0,
					'user_to_id' => USER_ID
				))
			);
		}
		elseif( $_GET['id'] == 'check' )
		{
			$message = PD::getInstance()->select( 'users_messages', array(
				'ajax_read' => 0,
				'user_to_id' => USER_ID
			), 1 );

			if( $message )
			{
				PD::getInstance()->update( 'users_messages', array(
					'ajax_read' => 1
				), [ 'id' => $message['id'] ] );

				$user = getUserInfo( $message['from_user_id'], false );

				return array(
					'content' =>  array(
						'message_data' => $message['created'],
						'message_info' => $user['login'],
						'message_url' => '/messages/view/' . $message['id'],
					)
				);
			}
			
			return array(
				'content' => 0
			);
		}
		elseif( isset( $_POST['message_to'] ) && isset( $_POST['message_subject'] ) && isset( $_POST['message_content'] ) )
		{
			$_POST = Sanitize::clearGlob( $_POST, 'cleanXss' );

			$row = getUserInfo( false, false, $_POST['message_to'] );

			if( !empty( $row ) )
			{
				try{
					$pm = new Messages();

					if( $pm->send( $row['id'], $_POST['message_subject'], $_POST['message_content'] ) )
					{
						Session::set( 'ips_pw_time', time() );

						return array(
							'success' => true,
							'content' => __( 'pw_message_sent' )
						);
					}

				}catch( Exception $e ){
					return array(
						'error' => __( $e->getMessage() )
					);
				}
			}
			else
			{
				return array(
					'error' => __( 'user_error_not_exists' )
				);
			}
		}
		elseif( isset( $_POST['message_delete'] ) )
		{
			if( $_POST['message_delete'] == 'delete' )
			{
				Messages::delete( IPS_ACTION_GET_ID );

				return array(
					'success' => true
				);
			}
			else
			{
				Messages::toDeleted( IPS_ACTION_GET_ID );

				return array(
					'success' => true
				);
			}
		}

		return array(
			'error' => __( 'err_unknown' )
		);
	}
	/**
	 *
	 */
	function ajax_fast()
	{
		if( IPS_ACTION_GET_ID !== false && isset( $_POST['direct'] ) && isset( $_POST['action'] ) )
		{
			$fast = new Fast;

			return array(
				'content' => $fast->load( $_POST['action'], IPS_ACTION_GET_ID, $_POST['direct'] )
			);
		}
		return array(
			'error' => 'error'
		);
	}








	/**
	* Text upload functions calls
	*/
	function ajax_canvas_store()
	{
		$base64_encoded = get_input('upload_canvas');

		if( $base64_encoded )
		{
			$url = Canvas_Helper::store( $base64_encoded, str_random(10) );
			if( $url )
			{
				return [
					'url' => 'upload/tmp/' . $url
				];
			}
		}

		return [
			'error' => false
		];
	}

	/**
	* Mem upload functions calls
	*/
	function ajax_up_mem()
	{
		return Upload_Ajax::call( 'Upload_Mem' );
	}
	/**
	* Demot upload functions calls
	*/
	function ajax_up_demotywator()
	{
		return Upload_Ajax::call( 'Upload_Demotywator' );
	}

	/**
	* Text upload functions calls
	*/
	function ajax_up_text()
	{
		return Upload_Ajax::call( 'Upload_Text' );
	}

	/**
	 * Handling drop upload
	 */
	function ajax_drop_upload_video()
	{
		$up_video = new Upload_Video;

		$url = get_input('upload_url');

		if( get_input('upload_subtype') != 'video' || empty( $url ) )
		{
			$response = ajax_drop_upload( $up_video->opts['size']['large'] );

			if( isset( $response['error'] ) )
			{
				return $response;
			}

			$url = $response['content'];
		}

		return $up_video->getVideo( $url, array(
			'width' => $up_video->opts['size']['large']
		) );
	}
	/**
	 * Upload text file background
	 */
	function ajax_drop_upload_text()
	{
		$up_text = new Upload_Text;

		$upload = ajax_drop_upload( $up_text->opts['size']['large'] );

		if( isset( $upload['content'] ) )
		{
			$img = $up_text->imgShadow( IPS_TMP_FILES . '/' . basename( $upload['content'] ) );

			$up = new Upload_Extended();
			$upload['shadow'] = $up->put( $img, IPS_TMP_FILES . '/' . str_random( 10 ) );
		}

		return $upload;
	}
	/**
	 * Handling drop upload
	 */
	function ajax_drop_upload_mem()
	{
		$up_mem = new Upload_Mem;

		return $up_mem->userUpload( ajax_drop_upload( $up_mem->opts['size']['large'] ) );
	}
	/**
	 * Handling drop upload
	 */
	function ajax_drop_upload_demotywator()
	{
		$up_demot = new Upload_Demotywator;

		$url = get_input('upload_url');

		if( get_input('upload_subtype') == 'video' && $url )
		{
			return $up_demot->video( $url );
		}

		return $up_demot->userUpload( ajax_drop_upload( $up_demot->opts['size']['large'] ) );
	}

	/**
	 * Handling drop upload
	 */
	function ajax_drop_upload( $max_width = 2500 )
	{
		return call_user_func_array( ( get_input( 'upload_url', false ) ? 'ajax_url_upload' : 'ajax_file_upload' ), array(
			'upload/tmp',
			$max_width
		));
	}


	/**
	 * Upload file from FILES data
	 */
	function ajax_file_upload( $up_folder = 'upload/tmp', $max_width = null )
	{
		$up = new Upload_Ajax;
		
		if( $up->isMultiple( 'Filedata' ) )
		{
			return $up->multiple( 'Filedata', array(
				'max_width' => ( empty( $max_width ) ? Config::get('file_max_width') : $max_width ),
				'up_folder' => $up_folder
			) );
		}
		
		return $up->files( 'Filedata', array(
			'max_width' => ( empty( $max_width ) ? Config::get('file_max_width') : $max_width ),
			'up_folder' => $up_folder
		) );
	}
	/**
	 * Upload file from POST url
	 */
	function ajax_url_upload( $up_folder = 'upload/tmp', $max_width = null )
	{
		$up = new Upload_Ajax;

		return $up->post( 'upload_url', array(
			'max_width' => ( empty( $max_width ) ? Config::get('file_max_width') : $max_width ),
			'up_folder' => $up_folder
		) );
	}

	/**
	 * Handling upload while import
	 */
	function ajax_file_upload_import()
	{
		if( !file_exists( ABS_PATH . '/upload/import/import_folder/' ) )
		{
			mkdir( ABS_PATH . '/upload/import/import_folder/', 0777, true );
		}

		return ajax_file_upload( 'upload/import/import_folder' );
	}



	/**
	 *
	 */
	function ajax_api_google_plus()
	{
		if( isset( $_POST['href'] ) && isset( $_POST['call'] ) )
		{
			preg_match("@" . ABS_URL . "([0-9]{1,})/([A-Za-z0-9_]+)@siU", urldecode( $_POST['href'] ), $matches );

			if( !empty( $matches[1] ) )
			{
				$id = intval( $matches[1] );

				$shares = PD::getInstance()->select( 'shares', array(
					'upload_id' => $id
				), 1);

				PD::getInstance()->insertUpdate( 'shares', array(
					'upload_id' => $id,
					'google' => ( $shares ? $shares['google'] + 1 : 1 )
				));
			}
		}
	}

	/**
	 *
	 */
	function ajax_history()
	{
		if( USER_ID == IPS_ACTION_GET_ID )
		{
			return array(
				'content' => Ips_Registry::get('History')->getUsersHistory( USER_ID )
			);
		}
	}

	/**
	 *
	 */
	function ajax_load_file()
	{
		if( IPS_ACTION_GET_ID )
		{
			/**
			* $_POST['page'] przekazuje czy jesteśmy na stronie materiału.
			*/
			Templates::getInc();
			$display = new Core_Query();
			$display->init( 'load_file', array(
				'display' => false,
				'condition' => array(
					'id' => IPS_ACTION_GET_ID
				)
			) );

			return array(
				'content' => $display->loadFile( $display->files[0], ( isset($_POST['page']) && strpos($_POST['page'], 'file') !== false ? true : false ), false )
			);
		}
	}

	/**
	 *
	 */
	function ajax_pinit_upload()
	{
		/*
		 * jQuery File Upload Plugin PHP Example 5.14
		 * https://github.com/blueimp/jQuery-File-Upload
		 *
		 * Copyright 2010, Sebastian Tschan
		 * https://blueimp.net
		 *
		 * Licensed under the MIT license:
		 * http://www.opensource.org/licenses/MIT
		 */

		error_reporting(E_ALL | E_STRICT);

		require( CLASS_PATH . '/class.UploadHandlerExtended.php');
		$upload_handler = new Upload_Handler_Extended( ( isset( $_GET['id'] ) && $_GET['id'] == 'video' ? 'video_file' : 'tmp_file' ), isset( $_POST['multi_upload'] ) );
	}

	/**
	 *
	 */
	function ajax_popular_posts()
	{
		if( !empty( $_POST ) )
		{
			return Widgets::popularPosts( ( isset( $_POST['count'] ) && is_numeric( $_POST['count'] ) && $_POST['count'] > 1 ? $_POST['count'] : 1  ) );
		}
	}

	/**
	 *
	 */
	function ajax_ranking_delete()
	{
		if( USER_MOD && IPS_ACTION_GET_ID )
		{
			PD::getInstance()->delete( 'upload_ranking_files', array(
				'id' => IPS_ACTION_GET_ID
			), 1 );
		}
	}

	/**
	 *
	 */
	function ajax_redirect()
	{
		if( IPS_ACTION_GET_ID )
		{
			$res = PD::getInstance()->select( IPS__FILES, array(
				'id' => IPS_ACTION_GET_ID
			), 1, 'upload_activ,date_add');

			if( !empty( $res ) )
			{
				$files = Widgets::getNextPrevious( false, $res['upload_activ'], $res['date_add'] );

				if( $_GET['query'] == 'next' && isset( $files['next']['url'] )  )
				{
					ips_redirect( str_replace( ABS_URL, '', $files['next']['url'] ), false, 301 );
				}
				elseif( $_GET['query'] == 'previous' && isset( $files['prev']['url'] )  )
				{
					ips_redirect( str_replace( ABS_URL, '', $files['prev']['url'] ) . '/', false, 301 );
				}
				/**
				* Nie przekierowano, wybieramy najstarszy lub najnowszy materiał
				*/
				$res = PD::getInstance()->select( IPS__FILES, array(
					'upload_activ' => $res['upload_activ']
				), 1, 'id', array( 'date_add' => ( $_GET['query'] == 'next' ? 'DESC' : 'ASC') ) );

				ips_redirect( $res['id'] . '/', false, 301 );
			}
		}
		ips_redirect( false, 'item_not_exists' );
	}

	/**
	 *
	 */
	function ajax_get_template()
	{
		if( isset( $_POST['template_name'] ) )
		{
			$data = array(
				'phrases' => array(),
				'current_user' => getUserInfo( USER_ID, true )
			);

			try{

				if( isset( $_POST['phrases'] ) && is_array( $_POST['phrases'] ) )
				{
					foreach($_POST['phrases'] as $value )
					{
						$data['phrases'][ $value ] = __( $value );
					}
				}

				if( isset( $_POST['compile'] ) )
				{
					$data['content'] = Tools::doTjs( $_POST['template_name'] );
				}
				else
				{
					$data['content'] = Templates::getInc()->getTpl( '/' . trim( urldecode( $_POST['template_name'] ), '/' ) . '.html', array_merge( $data, $_POST ) );

				}

				return $data;

			} catch ( Exception $e ) {
				return array(
					'content' => ''
				);
			}
		}
	}

	/**
	 *
	 */
	function ajax_reporting()
	{
		if( USER_LOGGED )
		{
			if( !isset( $_POST['report_type'] ) || !in_array( $_POST['report_type'], array( 'file', 'comment', 'message') ) )
			{
				return array(
					'content' => ''
				);
			}

			$options = new Operations;

			$status = $options->reporting( (int)$_POST['id'], $_POST['report_type'], has_value( 'subject', $_POST ), has_value( 'file_url', $_POST ), $_POST['additional'] );

			return ( $status === true ? array(
				'success' => true
			) : array(
				'error' => $status
			) );
		}
	}


	/**
	*
	*/
	function ajax_items_load()
	{
		return ajax_items( true, array(
			'display' => false,
			'pagination' => false,
			'sorting' => isset( $_GET['sorting'] ) ? $_GET['sorting'] : 'date_add',
			'on_page' => isset( $_GET['on_page'] ) ? $_GET['on_page'] : Config::get( 'files_on_page')
		) );
	}

	/**
	 *
	 */
	function ajax_items( $json = false, $args = array() )
	{
		if( isset( $_GET['id'] ) && !empty( $_GET['id'] ) )
		{

			define( 'IPS_ONSCROLL', true );

			$scroll_action = isset( $_GET['route'] ) ? $_GET['route'] : $_GET['id'];

			switch( $scroll_action )
			{
				case "top":
					$user = new Top();
					$content = $user->displayFiles();
				break;
				case 'categories':
					if ( !empty( $_GET['id'] ) )
					{
						$categories = new Categories();
						$content = $categories->loadCategory( intval( $_GET['id'] ) );
					}
				break;
				default:
					$display = new Core_Query();
					$content = $display->init( $scroll_action, $args );
				break;
			}

			if( $json )
			{
				return array(
					'items' => $display->files
				);
			}

			echo '<div id="content">' . $content . '</div>';
		}
	}

	/**
	 *
	 */
	function ajax_set_language()
	{
		if( Translate::set( get_input( 'language_code' ) ) )
		{
			return ips_message( 'lang_changed' );
		}

		return ips_message( 'lang_none' );
	}

	/**
	 *
	 */
	function ajax_show()
	{
		$upload_type = trim( $_GET['type'] );
		$allowed_types = Config::get( 'allowed_types' );

		/* Preppend all link */
		array_unshift( $allowed_types, 'all' );

		if( $upload_type == 'all' )
		{
			Session::clear( 'show_files' );
		}
		else
		{
			$show_files = Session::get( 'show_files' );

			if( is_array( $show_files ) )
			{
				if( !in_array( $upload_type, $show_files ) )
				{
					if( in_array( $upload_type, $allowed_types ) )
					{
						$show_files[] = $upload_type;
					}
				}
				else
				{
					$key = array_search( $upload_type, $show_files );

					if( isset( $show_files[$key] ) )
					{
						unset( $show_files[$key] );
					}
				}
			}
			else
			{
				$show_files = array( $upload_type );
			}

			Session::set( 'show_files', array_values( $show_files ) );
		}

		if( !isset( $_POST['ajax_post'] ) )
		{
			header('Location: '.( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : 'http://'.$_SERVER['HTTP_HOST'] ) );
		}
	}

	/**
	 *
	 */
	function ajax_video()
	{
		if( IPS_ACTION_GET_ID )
		{
			return Ips_Registry::get( 'Video' )->ajaxGet( IPS_ACTION_GET_ID );
		}
		return '';
	}

	/**
	 *
	 */
	function ajax_vote_comment()
	{
		try{

			return Comments::vote( $_GET['comment_id'], ( $_GET['vote_action'] == '1' ? 'votes_up' : 'votes_down' ) );

		} catch ( Exception $e ) {

			return array(
				'error' => __( $e->getMessage() )
			);
		}
	}

	/**
	 *
	 */
	function ajax_vote_file()
	{
		if( !USER_LOGGED && !Config::getArray( 'user_guest_option', 'vote' ) )
		{
			return array(
				'error' => 'login'
			);
		}

		$user = getUserInfo( USER_ID, true );

		if( $user['user_banned'] == 1 )
		{
			return array(
				'error' => __( 'user_ban_info' )
			);
		}

		$vote_action = $_POST['vote_type'];

		$row_temporary = Ips_Registry::get('Temporary')->get( array(
			'object_id' => IPS_ACTION_GET_ID,
			'user_id' => $user['id'],
			'action' => $vote_action
		));
		
		if( $row_temporary['time'] && $row_temporary['time'] + 86400 > time() )
		{
			return array(
				'error' => __( 'vote_restriction' )
			);
		}

		$row = PD::getInstance()->select( IPS__FILES , array(
			'id' => IPS_ACTION_GET_ID
		) , 1);

		if( empty( $row ) )
		{
			return false;
		}

		if( $row['user_id'] === USER_ID )
		{
			return array(
				'error' => __( 'vote_own_file' )
			);
		}

		if( $vote_action == 'vote_archive' )
		{
			/********************/
		}

		$vote_action_type = ( $vote_action == 'vote_file_down' ? 'down' : 'up' );

		$update =  array(
			'votes_opinion' => ( $vote_action == 'vote_file_down' ? $row['votes_opinion'] - 1 : $row['votes_opinion'] + 1 ),
			'votes_count' => ( !isset( $row_temporary['id'] ) ? $row['votes_count'] + 1 : $row['votes_count'] )
		);

		PD::getInstance()->update( IPS__FILES, $update, array(
			'id' => IPS_ACTION_GET_ID
		) );

		$votes_count = Upload_Meta::get( IPS_ACTION_GET_ID, 'votes_' . $vote_action_type );

		Upload_Meta::update( IPS_ACTION_GET_ID, 'votes_' . $vote_action_type, (int)$votes_count + 1 );

		Ips_Registry::get('Temporary')->set( $row_temporary );

		if ( Config::get('module_history') )
		{
			Ips_Registry::get('History')->storeAction( 'vote', array(
				'upload_id' => IPS_ACTION_GET_ID,
				'action_name' => substr( $vote_action, 10 ),
			) );
		}

		if(  IPS_FILE_CACHE )
		{
			Ips_Cache::clearCacheFiles();
		}

		return array(
			'success' => ( $vote_action != 'vote_archive' ? __( 'vote_added' ) : __( 'archive_voted' ) ),
			'votes_count' => $update['votes_count'],
			'votes_opinion' => $update['votes_opinion'],
		);

	}

	/**
	 *
	 */
	function ajax_add_favourite()
	{
		if( isset( $_POST['fav_action'] ) && $_POST['fav_action'] == 'delete' )
		{
			PD::getInstance()->delete( 'users_favourites', array(
				'user_id' => USER_ID,
				'upload_id' => IPS_ACTION_GET_ID
			));

			if ( Config::get('module_history') )
			{
				Ips_Registry::get('History')->storeAction( 'favorites', array(
					'upload_id' => IPS_ACTION_GET_ID,
					'action_name' => 'delete'
				) );
			}

			return array(
				'success' => '<span id="fav-ajax">' . __( 'favourites_deleted' ) . '</span>',
			);

		}

		$count = PD::getInstance()->cnt( 'users_favourites', array( 'upload_id' => IPS_ACTION_GET_ID, 'user_id' => USER_ID ) );

		if( $count == 0 )
		{
			if ( Config::get('module_history') )
			{
				Ips_Registry::get('History')->storeAction( 'favorites', array(
					'upload_id' => IPS_ACTION_GET_ID,
					'action_name' => 'add'
				) );
			}

			PD::getInstance()->insert( 'users_favourites', array(
				'user_id' => USER_ID,
				'upload_id' => IPS_ACTION_GET_ID,
				'date_add' => date("Y-m-d H:i:s")
			));
		}


		return array(
			'success' => '<span class="fav-response">' . ( $count == 0 ? __( 'favourites_added' ) : __( 'favourites_already_added' ) ) . ' -- <span class="ips-add-favourites user-action delete" data-id="' . IPS_ACTION_GET_ID . '">' . __( 'favourites_delete' ) . '</span></span>',
		);
	}

	/**
	 *
	 */
	function ajax_watch_user()
	{
		$user = getUserInfo( IPS_ACTION_GET_ID, false );

		if( isset( $_POST['watch_action'] ) && $_POST['watch_action'] == 'delete' )
		{
			PD::getInstance()->delete("users_follow_user", array(
				'user_id' => USER_ID,
				'user_followed_id' => IPS_ACTION_GET_ID
			));

			return array(
				'success' => __( 'follow_deleted' )
			);

		}

		$count = PD::getInstance()->cnt( 'users_follow_user', array(
			'user_id' => USER_ID,
			'user_followed_id' => IPS_ACTION_GET_ID
		) );

		if( $count == 0 )
		{

			PD::getInstance()->insert( 'users_follow_user', array(
				'user_id' => USER_ID,
				'user_followed_id' => $user['id']
			));
		}

		return array(
			'success' => '<span class="watch-response">' . ( $count == 0 ? __( 'follow_added' ) : __( 'follow_already_following' ) ) . ' <span class="ips-watch-user user-action delete" data-id="' . IPS_ACTION_GET_ID . '">'.__( 'common_delete' ).'</span></span>',
		);


	}

	/**
	 *
	 */
	function ajax_vote_ranking()
	{
		if( !USER_LOGGED && !Config::getArray( 'user_guest_option', 'vote' ) )
		{
			return array(
				'error' => 'login'
			);
		}

		$user = getUserInfo( USER_ID, true );

		if( $user['user_banned'] == 1 )
		{
			return array(
				'error' => __( 'user_ban_info' )
			);

		}

		$user_ip = $_SERVER["REMOTE_ADDR"];

		$vote_action = 'ranking_vote_' . $_POST['vote_type'];

		$row_temporary = Ips_Registry::get('Temporary')->get( array(
			'object_id' => IPS_ACTION_GET_ID,
			'user_id' => $user['id'],
			'action' => $vote_action
		));

		if( isset( $row_temporary['id'] ) && $vote_action == $row_temporary['action'] && $row_temporary['time'] && $row_temporary['time'] + 86400 > time() )
		{
			return array(
				'error' => __( 'vote_restriction' )
			);
		}

		$row = PD::getInstance()->from( array(
			IPS__FILES => 'up',
			'upload_ranking_files' => 'rank'
		) )->setWhere( array(
			'rank.id' => IPS_ACTION_GET_ID,
			'rank.upload_id' => 'field:up.id'
		) )->fields('rank.*, up.user_login')->getOne();
		
		if( empty( $row ) )
		{
			return array(
				'error' => __( 'err_unknown' )
			);
		}
		
		if( $row['user_login'] == USER_LOGIN )
		{
			return array(
				'error' => __( 'vote_own_file' )
			);
		}

		$data = array(
			'votes_opinion' => ( $vote_action == 'ranking_vote_up' ? $row['votes_opinion'] + 1 : $row['votes_opinion'] - 1 )
		);

		if( !isset( $row_temporary['id'] ) )
		{
			$data['votes_count'] = $row['votes_count'] + 1;
			$data[( $vote_action == 'ranking_vote_up' ? 'votes_up': 'votes_down' )] = ( $vote_action == 'ranking_vote_up' ? $row['votes_up'] + 1: $row['votes_down'] + 1 );
		}
		else
		{
			$data['votes_up'] = ( $vote_action == 'ranking_vote_up' ? $row['votes_up'] + 1 : $row['votes_up'] - 1 );
			$data['votes_down'] = ( $vote_action == 'ranking_vote_down' ? $row['votes_down'] + 1 : $row['votes_down'] - 1 );
		}

		PD::getInstance()->update( 'upload_ranking_files', $data, array(
			'id' => IPS_ACTION_GET_ID
		) );

		Ips_Registry::get('Temporary')->set( array_merge( $row_temporary, array(
			'action' => $vote_action
		)) );

		if(  IPS_FILE_CACHE )
		{
			Ips_Cache::clearCacheFiles();
		}

		return array(
			'success'	=> __( 'vote_added' ),
			'votes_count'		=> ( !isset( $row_temporary['id'] ) ? $row['votes_count'] + 1 : $row['votes_count'] ),
			'votes_opinion'	=> $data['votes_opinion'],
		);

	}

	/**
	* Clear upload session
	*/
	function ajax_upload_clear()
	{
		include_once( ABS_PATH . '/functions-upload.php' );
		upload_clear();
	}

	/**
	* Tags upload functions calls
	*/
	function ajax_tags()
	{
		$text = new Upload_Tags;

		if( isset( $_POST['action'] ) )
		{
			$action = 'ajax_' . $_POST['action'];

			if( is_callable( array( $text, $action ) ) )
			{
				return $text->$action( $_POST );
			}
		}

		return array();
	}

	/**
	 *
	 */
	function ajax_validate()
	{
		if( empty( $_POST ) )
		{
			return false;
		}

		if( isset( $_POST['action_login'] ) )
		{
			$login = new Users();
			$validate = $login->userLogin( $_POST['login'], $_POST['password'], isset( $_POST['user_remember'] ) );
		}
		elseif( isset( $_POST['password_p'] ) )
		{
			try{

				$register = new User_Register();

				$register->validateForm( $_POST );
				$validate = $register->getUserId();

				ips_message( Config::get( 'user_account', 'email_activation' ) ? 'user_register_success_verify' : 'user_register_success' );

			} catch ( Exception $e ) {

				$validate = $e->getMessage();

			}
		}

		if( isset( $validate ) && is_numeric( $validate ) )
		{
			return array(
				'success' => true,
				'modal_redirect' => Users::redir( $validate )
			);
		}

		return array(
			'error' => __( $validate )
		);
	}

	/**
	* Validate connect data
	*/
	function ajax_validate_connect()
	{
		if( isset( $_POST['suggested_login'] ) )
		{
			$connect_data = Session::get( 'connect' );

			if( $connect_data )
			{
				$connect = new Connect();
				$provider = $connect->getProvider( $connect_data['provider'] );

				if( $provider && $provider->init() && $connect->setParams( $connect_data, $_POST ) )
				{
					try{
						$user_id = $connect->register( $_POST['suggested_login'] );
						$login = new Users();
						$login->setLogged( $user_id );

						return array(
							'success' => true,
							'modal_redirect' => Users::redir( $user_id )
						);
					} catch ( Exception $e ) {
						return array(
							'error' => __( $e->getMessage() )
						);
					}
				}
			}
		}

		return array(
			'error' => __( 'err_unknown' )
		);
	}

	/**
	 *
	 */
	function ajax_download_img()
	{
		if( isset( $_GET['img'] ) )
		{
			$basename = basename($_GET['img']);
			$file = IPS_TMP_FILES . '/' . $basename;
			if( file_exists( $file ) )
			{
				$mime = ($mime = getimagesize($file)) ? $mime['mime'] : $mime;
				$size = filesize($file);
				$fp   = fopen($file, "rb");
				if (!($mime && $size && $fp)) {
					return;
				}

				header("Content-type: " . $mime);
				header("Content-Length: " . $size);

				header("Content-Disposition: attachment; filename=" . $basename);
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				fpassthru($fp);
				die();
			}
		}
		header( "HTTP/1.1 302 Moved Temporarily" );
		header('Location: /images/broken-img.png' );
	}
	/**
	 *
	 */
	function ajax_cache_img()
	{
		if( isset( $_GET['img'] ) )
		{
			if( $img = Ips_Cache::img( $_GET['img'], $_GET ) )
			{
				header('Content-Type: image/jpeg');
				readfile( $img );
				die();
			}
		}
		header( "HTTP/1.1 302 Moved Temporarily" );
		header('Location: /images/broken-img.png' );
	}

	/**
	 *
	 */
	function ajax_img_captcha()
	{
		include_once( LIBS_PATH . "/SimpleCaptcha.php" );
		$captcha = new SimpleCaptcha(array(
			'ips' => array(
				'spacing' => 2,
				'minSize' => Config::getArray('image_captcha_size', 'min'),
				'maxSize' => Config::getArray('image_captcha_size', 'max'),
				'font' 	  => Ips_Registry::get( 'Web_Fonts' )->getFontTtf( Config::getArray('image_captcha_size', 'font') )
			)
		));
		$captcha->CreateImage();
	}

	/**
	 *
	 */
	function ajax_generator_search()
	{
		$rss = new Mem();
		return ips_json( $rss->search( get_input('search'), get_input('category') ) );
	}


	/**
	 *
	 */
	function ajax_rss()
	{
		$rss = new Rss();
		echo $rss->get();
		die();
	}

