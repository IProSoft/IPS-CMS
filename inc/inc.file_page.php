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
	/** ALL versions without pinestic */
	if( !defined('IPS_VERSION') ) die();
	
	global $PD, ${IPS_LNG};
	
	$row = getFileInfo();
	
	if( !empty( $row ) )
	{
		if( $row['upload_status'] == 'private' && $row['user_login'] != USER_LOGIN ) 
		{
			return ips_redirect();
		}		
		if( Config::get('services_premium') )
		{
			if( !Premium::getInc()->premiumService( 'category' ) && $only_premium = Premium::getInc()->premiumCategories() )
			{
				if( in_array( $row['category_id'] , $only_premium ) )
				{
					return Premium::getInc()->premiumRedirect( $row['id'] . '/' . $row['seo_link'] );
				}
			}
		}
	} 
	else 
	{
		return ips_redirect( false, 'item_not_exists' );
	}
		
	if( $row['upload_activ'] == 0 && defined('IPS_WAIT_BLOCKED') && !USER_ADMIN )
	{
		return ips_redirect();
	}
	
	$PD->update( IPS__FILES, array( 
		'upload_views' => $row['upload_views'] + 1
	), array( 'id' => IPS_ACTION_GET_ID ) );
	
	/**
	* False - file not blocked
	*/
	$blocked = Facebook_UI::isBlocked( $row );

	$CacheFilename = md5( IPS_ACTION_GET_ID . 'file_page' . ( $blocked ? $blocked->type : 'normal' ) . $row['upload_adult'] . $row['up_lock'] );
	
	if( !IPS_FILE_CACHE || !$fileContainer = Ips_Cache::get( $CacheFilename ) )
	{	
		$display = new Core_Display();
		$file_url = $display->getSeoLink( $row['id'], $row['seo_link'] );

		$info = $display->getFileInfo( $row['id'], $row['category_id'], $row['upload_adult'] );
		
		/**
		* Sprawdzanie statusu autopostu
		*/
		if(  is_object( $blocked )  )
		{
			$fileContainer = $blocked->template( $row );
		}
		elseif( $info['upload_adult'] )
		{	
			$fileContainer = $display->adultFile( $row, true );			
		}
		else 
		{
			$fileContainer = $display->loadFile( $row, true );
		}

		if( $row['upload_type'] == 'gallery' && $display->options['gallery_images_count'] )
		{
			$row['title'] = $display->addImageCount( $row );
		}
		
		$row['comments'] = $display->commentsCount( $row );

		if( Config::get('widget_navigation_on_page_box') == 1 )
		{
			$nav_box = Widgets::navigationBoxes( $row['upload_activ'], $row['date_add'] );
		}
		
		if( Config::get('widget_navigation_on_page') == 1 )
		{
			$nav_link = Widgets::navigationButtons( $row['id'] );
		}
		
		if( Config::get('widget_file_tags') == 1)
		{
			$upload_tags = Tags::getFileTags( $row['id'], true );
		}
				
		$variables = $row;
			
		$variables['upload_tags'] = ( isset( $upload_tags ) && !empty( $upload_tags ) ? $upload_tags : '' );
		$variables['title'] = stripslashes( $row['title'] );
		$variables['file_url'] = $file_url;
		$variables['date_add'] = formatDate( $row['date_add'] );
		$variables['vote_type'] = Config::get('vote_file_menu');
		$variables['report_file'] = true;
		$variables['file_container'] = $fileContainer;

		$variables['file_category'] = $info['file_category'];
		$variables['nav'] = array(
			'box'	=> ( isset( $nav_box )	? $nav_box	: '' ),
			'link'	=> ( isset( $nav_link ) ? $nav_link : '' )
		);
		$variables['button_nk'] = '';
		$variables['button_like'] = '';
		$variables['button_like_big'] = '';
		$variables['button_twitter'] = '';
		$variables['button_twitter_big'] = '';
		$variables['button_share'] = '';
		$variables['button_google'] = '';
		
		$variables['widget_url_copy'] = Config::get('widget_url_copy');
		$variables['upload_source'] = '';
		$variables['file_class_css'] = ( $display->hidden_watermark ? 'image-hidden-watermark' : '' );
		

		if( Config::get( 'social_plugins', 'nk_page' ) )
		{
			$variables['button_nk'] = SocialButtons::nkButton( $file_url, $row['title'], ips_img( $row, 'thumb' ) );
		}
		
		if ( Config::get( 'social_plugins', 'share_page' ) )
		{
			$variables['button_share'] = SocialButtons::shareOld( $file_url, $row['id']  );
		}
		
		if( Config::get( 'social_plugins', 'like_page' ) )
		{
			$variables['button_like'] = SocialButtons::like( $file_url, Config::get( 'social_plugins', 'like_template' ), Config::get( 'social_plugins', 'like_add_share' ) );
		}
		
		if( Config::get( 'social_plugins', 'google_page' ) )
		{
			$variables['button_google'] = SocialButtons::googleButton( $file_url );
		}
		
		if( Config::get( 'social_plugins', 'twitter_page' ) )
		{
			$variables['button_twitter'] = SocialButtons::twitterButton( $file_url, $row['title'] );
		}
		
		if( IPS_VERSION == 'vines' )
		{
			if( Config::get( 'social_plugins', 'like_page_big' ) == '1')
			{
				$variables['button_like_big'] = SocialButtons::like( $file_url, 'box_count' );
			}
			
			if( Config::get( 'social_plugins', 'twitter_page_big' ) == '1')
			{
				$variables['button_twitter_big'] = SocialButtons::twitterButton( $file_url, $row['title'], 'vertical' );
			}
		}
		
		if( Config::getArray('template_settings', 'upload_source' ) == 1 )
		{
			$variables['upload_source'] = ( !empty( $row['upload_source'] ) ? '<p class="file-source">' . ${IPS_LNG}['upload_source'] . ' ' . makeClicableURL( $row['upload_source'] ) . '</p>' : '' );
		}
		
		$variables['ads'] = array(
			'above_file' => AdSystem::getInstance()->showAd('above_file'),
			'under_file' => AdSystem::getInstance()->showAd('under_file'),
			'under_image' => AdSystem::getInstance()->showAd('under_image_file')
		);

		$template = $row['upload_status'] == 'public' ? 'item_on_page.html' : 'item_' . $row['upload_status'] . '.html';
		
		$variables['mod_links'] = '';
		
		if( USER_MOD )
		{
			$variables['mod_links'] = Operations::fileModeration( $row );
		}
		elseif( $row['user_login'] == USER_LOGIN && $row['upload_status'] == 'private' )
		{
			$variables['mod_links'] = Operations::editFileButtons( $row['id'] );
		}
		
		$variables['description_box'] = '';
		
		if( Config::getArray('add_extra_description') && strpos( 'gallery|article|ranking', $row['upload_type'] ) === false )
		{
			$variables['description_box'] = $display->additionalDescription( $row );
		}
		
		if ( Config::get('widget_social_share') == 1 )
		{
			$variables['img_social_share'] = ips_img( $row['upload_image'], 'medium' );
		}
		
		$comments_type = Config::getArray( 'comments_options', 'type' );
		
		$variables['comments_options'] = array(
			'facebook'	=> false,
			'ajax'		=> false,
			'count' => array(
				'ajax' => $variables['comments'],
				'facebook' => $variables['comments_facebook']
			)
		);
		
		if( Config::getArray( 'comments_options', 'type' ) != 'off' && $row['upload_status'] == 'public' )
		{
			add_filter( 'init_js_files', function( $array ){
				return add_static_file( $array, array(
					'js/comments.js'
				)  );
			}, 10 );
			
			$variables['comments_options'] = array_merge( $variables['comments_options'], array(
				'facebook' => $comments_type != 'ajax',
				'ajax' => $comments_type == 'ajax' || $comments_type == 'ajax_facebook',
				'header' => App::ver( array( 'bebzol', 'vines' ) ),
				'allways_visible' => Config::getArray( 'comments_options', 'allways_visible' ) || $comments_type == 'facebook',
				'facebook_comments' => $comments_type != 'ajax' ? SocialButtons::comments( $file_url, App::$app['comments-width'], 10 ) : false,
				'comments_show_text' => ( Cookie::exists('comments-visibility') ? ${IPS_LNG}['js_comments_show_hidden'] : ${IPS_LNG}['js_comments_show_visible'] ),
				'as_comment' => ( Config::getArray( 'comments_options', 'as_image' ) && $row['upload_type'] == 'demotywator' ? true : false ),
			));
		}
		
		$fileContainer = Templates::getInc()->getTpl( $template, $variables );
		
		$fileContainer .= AdSystem::getInstance()->showAd('under_comments');
		
		if( IPS_FILE_CACHE )
		{
			Ips_Cache::write( $fileContainer, $CacheFilename );
		}
	}
	
	$fileContainer .= Widgets::seeMoreWrapper();
	
	if( IPS_VERSION == 'vines' )
	{
		$fileContainer .= '<div class="next-box" style="width: 95%;"><a href="' . ABS_URL . '">' . ${IPS_LNG}['menu_main'] . '</a></div>';
	}

	if ( Config::get('module_history') )
	{
		Ips_Registry::get('History')->storeAction( 'view', array( 
			'upload_id' => $row['id']
		) );
	}
	
	do_action( 'item_view', array(
		'id' => $row['id']
	));
	
	return $fileContainer;
