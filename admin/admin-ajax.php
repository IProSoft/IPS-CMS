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
require_once( dirname(__FILE__) . '/config.php');
require_once( dirname(__FILE__) . '/admin-functions.php');

	$ajax_action = $ajax_id = $call_content = $field_name = null;
	/**
	* Clear encodeURIComponent
	*/
	if ( is_array( $_GET ) )
	{
        foreach ( $_GET as $key => $val )
		{
            $_GET[$key] = preg_replace('/%([0-9a-f]{2})/i', 'chr(hexdec($1))', (string) $val );
        }
    }
	
	/**
	* Akcja wykonywane za pomocą jquery,
	* usuwanie wielu userów, materiałów itp jednocześnie
	*/
	if( isset( $_POST['ajax_actions'] ) )
	{
		if( !isset($_POST['ajax_data']) || empty($_POST['ajax_data']))
		{
			die('false');
		}
		
		$ids = $_POST['ajax_data'];
		
		switch( $_POST['ajax_actions'] ){
			
			case 'ads_edit_save':
				die( json_encode( adUpdate( $ids ) ) ) ;
			break;
			case 'get_font':
				$fonts = new Admin_Web_Fonts();
	
				die( json_encode( $fonts->urlWebFont( $ids ) ) ) ;
				
			break;
			case 'add_font':
				$fonts = new Admin_Web_Fonts();
	
				$fonts->addWebFont( $ids );
				
				die( json_encode(array(
					'info' => __( 'system_action_success')
				)) );
			break;
			
			case 'font_delete':
				
				$fonts = new Admin_Web_Fonts();
	
				if( $fonts->deleteFont( $ids ) )
				{
					die( json_encode(array(
						'info' => __( 'admin_font_deleted')
					)) );
				}
				
				die( json_encode(array(
					'info' => __( 'admin_font_delete_error')
				)) );
				
			break;
			
			case 'language-search':
				
				$_POST = $_POST['ajax_data'];
				
				include_once( IPS_ADMIN_PATH .'/language-functions.php' );

				die( getInputTranslations( 'search-phrase', '', 'ajax' ) );
				
			break;
			case 'save-settings':
				
				$_POST = $_POST['ajax_data'];
				
				$validate_options = Ips_Registry::get( 'Validate_Admin' )->validate( $_POST );
				
				if( !empty( $validate_options ) )
				{
					die( implode( '<br />', $validate_options ) );
				}
				
				updateSystemOptions( $_POST );
			
				die( 'true' );
				
			break;
			case 'test_mail':
				Ips_Registry::get( 'Mailing_Admin' )->test( $ids );
			break;
			case 'delete':
				deleteFile($ids);
			break;
			case 'delete_hook':
				if( isset( $_POST['ajax_additional'] ) )
				{
					deleteHook( $ids, $_POST['ajax_additional'] );
				}
			break;
			case 'send_suggest':
				if( !empty( $ids ) )
				{
					sendSuggest( $ids );
				}
			break;
			case 'save_hook_position':
				saveHooksPosition( $ids );
			break;
			case 'deleteTags':
				deleteTags( $ids );
			break;
			case 'deletearchive':
				deleteFile( $ids, true );
			break;
			case 'archive':
				moveToArchive($ids);
			break;
			case 'main':
				moveToMain($ids);
			break;
			case 'autopost':
				autopostBlock($ids);
			break;
			case 'social_lock':
				likeBlock($ids);
			break;
			case 'wait':
				moveToWait($ids);
			break;
			case 'waitarchive':
				moveToWait( $ids, true );
			break;
			case 'deletecomment':
				deleteComment($ids);
			break;
			case 'deleteuser':
				deleteUser($ids);
			break;
			case 'userban':
				if( isset($_POST['ajax_additional']) )
				{
					userBan( $ids, (int)$_POST['ajax_additional'] );
				}
			break;
			case 'fanpage_post_remove':
				deleteFanpagePost($ids);
			break;
			case 'deletegenerator':
				deleteGenerator($ids);
			break;
			case 'deletecategory':
				deletecategory($ids);
			break;
			case 'deleteboards':
				deleteBoard( $ids );
			break;
			case 'deletepin':
				deletePin( $ids );
			break;
			case 'deletecategoryall':
				deletecategoryall($ids);
			break;
			case 'category_change':
				if( isset($_POST['ajax_additional']) )
				{
					category_change($ids, (int)$_POST['ajax_additional']);
				}
			break;
			default:
			if( function_exists( $_POST['ajax_actions'] ) )
			{
				die( call_user_func_array( $_POST['ajax_actions'], $_POST ) );
			}
			break;
		}
		
		die('true');
	}
	
	
	$ajax_action = ( isset( $_GET['ajax_action'] ) ? $_GET['ajax_action'] : ( isset( $_POST['ajax_action'] ) ? $_POST['ajax_action'] : 'none' ) );
	
	if( isset( $_GET['call_content'] ) && !empty( $_GET['call_content'] ) )
	{
		$call_content = stripslashes( $_GET['call_content'] );
	}
	
	if( isset( $_GET['field_name'] ) && !empty( $_GET['field_name'] ) )
	{
		$field_name = $_GET['field_name'];
	}
	
	if( isset( $field_name ) && strpos( $field_name, '_' ) !== false )
	{
		$ajax_id = preg_replace( "/[^0-9]/", "", $field_name );
	}
	

	switch ( $ajax_action )
	{
		case 'activ_text_file':
			
			activ_text_file( $_GET['file'] );
			die('true');
		break;
		case 'delete_file':
			
			deleteFileBySetting( $_GET['file'] );
			die('true');
		break;
		case 'delete_file_path':
			
			deleteFileByPath( $_GET['file'] );
			die('true');
		break;
		case 'updates_count':
			
			$not_downloaded = Updates::get( 'count' );
			
			Config::update( 'update_alert_count', $not_downloaded );
			
			die( json_encode(array(
				'count' => (int)$not_downloaded,
				'info' => __s( 'updates_count', $not_downloaded )
			)) );
			
		break;
		case 'categories':
			if( !empty( $field_name ) && !empty( $call_content ) && !empty( $ajax_id ) )
			{
				PD::getInstance()->update( 'upload_categories', array( 'category_name' => $call_content ) , "id_category = '" . (int) $ajax_id . "'");
			}
			die( $call_content );
			
		break;
		case 'pages-list':
			
			if( !empty( $field_name ) && !empty( $call_content ) && !empty( $ajax_id ) )
			{
				$post_permalink = seoLink( false, $call_content );
				if( strlen( $post_permalink ) > 254 )
				{
					$post_permalink = substr( $post_permalink, 0, 150 ) . '.html';
				}
				PD::getInstance()->update("posts", array( 'post_title' => $call_content, 'post_permalink' => $post_permalink ) , "id = '" . (int) $ajax_id . "'");
			}
			die( $call_content );
		break;
		case 'contests':
			
			if( !empty( $field_name ) && !empty( $call_content ) && !empty( $ajax_id ) )
			{
				PD::getInstance()->update("contests", array( 'tytul' => $call_content ) , "id = '" . (int) $ajax_id . "'");
			}
			die( $call_content );
			
		break;
		case 'albums':
			
			if( isset( $_POST['fanpage_id'] ) )
			{
				$fanpage_ids = $_POST['fanpage_id'];
				
				if( !is_array( $fanpage_ids ) )
				{
					$fanpage_ids = array();
				}

				$call_content = array();
				
				foreach( $fanpage_ids as $key => $fanpage_id )
				{
					$call_content[$fanpage_id] = Facebook_Fanpage::getAlbums( $fanpage_id );
				}
				
			}
			die( json_encode( $call_content ) );
			
		break;
		case 'generator':
			
			if( !empty( $field_name ) && !empty( $call_content ) && !empty( $ajax_id ) )
			{
				PD::getInstance()->update("mem_generator", array( 'mem_title' => $call_content ) , "id = '" . (int) $ajax_id . "'");
			}
			die( $call_content );
			
		break;
		
		case 'generator-cats':
			
			if( !empty( $field_name ) && !empty( $call_content ) && !empty( $ajax_id ) )
			{
				PD::getInstance()->update("mem_generator_categories", array( 'category_text' => $call_content ) , "id = '" . (int) $ajax_id . "'");
			}
			die( $call_content );
			
		break;
		case 'tags':
		
			if( !empty( $field_name ) && !empty( $call_content ) && !empty( $ajax_id ) )
			{
				PD::getInstance()->update( 'upload_tags', array( 
					'tag' => $call_content
				) , "id_tag = '" . (int) $ajax_id . "'");
			}
			die( $call_content );
			
		break;
		case 'main':
		case 'wait':
		case 'archive':
		
			if( !empty( $field_name ) && !empty( $call_content ) && !empty( $ajax_id ) )
			{
				PD::getInstance()->update( IPS__FILES, array( 
					'title' => $call_content
				) , "id = '" . (int) $ajax_id . "'");
				
			}
			die( $call_content );
			
		break;
		
		case 'load-server-stats':
			require_once ( IPS_ADMIN_PATH .'/libs/class.SystemTools.php' );
			$system = new System_Tools();
			echo $system->call('countAddedFiles');
		break;
		
		case 'load-stats':
		
			$CacheFilename = md5( 'Analytics' ) ;
			Ips_Cache::setCacheLifetime( 600 );
			$CacheFile = Ips_Cache::get( $CacheFilename );
			if( !$CacheFile || isset($_GET['ga_change']) || isset($_POST['ga_data']) )
			{
				require ( IPS_ADMIN_PATH .'/libs/GAPI/gapi.class.php' );
				require ( IPS_ADMIN_PATH .'/libs/GAPI/GaAnalytics.php' );
				
				$ga = new Ga_Analytics();
				if( $ga->checkSettings() && $ga->checkProfile() )
				{
					ob_start();
						echo $ga->report();
					$CacheFile = ob_get_contents();
					ob_end_clean();
					
					Ips_Cache::write( $CacheFile, $CacheFilename );
				}
			}
			die( $CacheFile );
			
		break;
		
		case 'delete-menu-item':

			$item = PD::getInstance()->select('menus', array(
				'item_id' => $_POST['menu_item'],
				'menu_id' => $_POST['menu_id'] ) 
			);
			
			if( __( $item['item_anchor'] ) !== $item['item_anchor'] )
			{
				PD::getInstance()->delete('translations', array( 'translation_name' => $item['item_anchor'] ));
			}
			
			if( __( $item['item_title'] ) !== $item['item_title'] )
			{
				PD::getInstance()->delete('translations', array( 'translation_name' => $item['item_title'] ));
			}

			PD::getInstance()->delete('menus', array( 'item_id' => $_POST['menu_item'], 'menu_id' => $_POST['menu_id'] ) );
			die( 'true' );
		break;
		
		case 'save-menu-item':
			
			parse_str( $_POST['saved_item'], $output );
			
			menuCreateItem( $output );
			
			die( 'true' );
		break;
		case 'reset-menu':
			
			if( isset( $_POST['menu_id'] ) )
			{
				resetMenu( $_POST['menu_id'] );
			}
			
			die( 'true' );
		break;
		case 'save-menu':
			
			//print_r( $_POST );
			
			if( isset( $_POST['saved_menu'] ) && is_array( $_POST['saved_menu'] ) )
			{
				updateMenu( $_POST['saved_menu'], $_POST['menu_id'] );
			}
			 
			die( 'true' );
		break;
		
		default:
		
		break;
	}