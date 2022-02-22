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

class Widgets
{
	
	
	/*
	 * Przetrzymanie zapytania pobierającego następny i poprzedni materiał 
	 * Toll question withstand the next and previous material
	 */
	public static $nav_resource = false;
	
	public function __construct()
	{
	}
	
	/* 
	 * Dodatek wyświetlający panele
	 * po prawej stronie z Like, NK, Google
	 * Appendix display panels on the right side of Like, NK, Google
	 * plik slide_widget.html w folderze templates/
	 * slide_widget.html file in the folder templates /
	 * @return NULL
	 * @echo szablon html 
	 */
	public static function sliderSocial()
	{
		
		$data = array(
			'facebook_fan_box' => '',
			'button_google' => '',
			'twitter_box' => '',
			'slider_class' => Config::getArray( 'widget_float_slides_options', 'slider_class' ) 
		);
		
		if ( Config::get( 'apps_fanpage_default_id' ) && Config::getArray( 'widget_float_slides_options', 'facebook' ) )
		{
			$data['facebook_fan_box'] = SocialButtons::fanBox( Config::get( 'apps_fanpage_default_id' ), 300, 340 );
		}
		
		if ( Config::getArray( 'widget_float_slides_options', 'google' ) )
		{
			$data['button_google'] = SocialButtons::googleButton( Config::get( 'apps_google_app', 'profile_url' ), 'tall' );
		}
		
		if ( Config::getArray( 'widget_float_slides_options', 'twitter' ) )
		{
			$data['twitter_box'] = SocialButtons::twitterTimeline( Config::get( 'apps_twitter_app', 'username' ), Config::getArray( 'widget_float_slides_options', 'twitter_widget_id' ) );
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_float_slides.html', $data );
	}
	
	
	/**
	 * Pływający box na dole strony. Zawiera buttony przewijania, pokaz slajdów,
	 * rozwijane pole z proponowanymi materiałami i linki zalogowanego usera.
	 * Floating box at the bottom of the page. Buttons contains a scroll, slideshow, drop down box with the proposed materials and links logged mplayer.
	 *
	 * @param null
	 * 
	 * @return mixed
	 */
	public static function floatBOX()
	{
		/* widgetCached with USER_LOGGED */
		$files = PD::getInstance()->select( IPS__FILES, null, 20, 'id,title,upload_image,seo_link', array(
			'date_add' => 'DESC' 
		) );
		
		if ( !empty( $files ) )
		{
			foreach ( $files as $key => $row )
			{
				$files[$key]['url']          = seoLink( $row['id'], false, $row['seo_link'] );
				$files[$key]['upload_image'] = ips_img( $row['upload_image'], 'thumb' );
			}
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_float_box.html', array(
			'files' => $files 
		) );
	}
	
	/**
	 * Odwiedzenie strony po wpisaniu numeru strony
	 * w formularzu pod paginacją
	 * Visiting the site by typing the page number in the form of the pagination
	 *
	 * @param $link - address of the current page
	 * 
	 * @return void
	 */
	public static function goToPage( $current_page )
	{
		return Templates::getInc()->getTpl( '/widgets/widget_goto.html', array(
			'current_page' => $current_page 
		), $current_page );
	}
	
	
	/**
	 * Box with a choice of materials viewed
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function sortBox()
	{
		
		/** Cache ADD serialize( session show_files ) */
		
		$allowed_types = Config::get( 'allowed_types' );
		
		/* Preppend all link */
		array_unshift( $allowed_types, 'all' );
		
		$show_files = Session::get( 'show_files' );
		
		$show_files_selected = ( is_array( $show_files ) ? array_flip( $show_files ) : array(
			'all' => true 
		) );
		
		global ${IPS_LNG};
		
		foreach ( $allowed_types as $key => $show_files )
		{
			$allowed_types[$key] = array(
				'type' => $show_files,
				'active' => isset( $show_files_selected[$show_files] ),
				'text' => ${IPS_LNG}['widget_filter_' . $show_files] 
			);
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_sort_box.html', array(
			'show_files' => $allowed_types 
		) );
	}
	
	/**
	 * Side block big connect button
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function connectButton()
	{
		$text = 'right_panel_register';
		
		if( USER_LOGGED )
		{
			if ( Config::get( 'connect_facebook' ) == 1 || !Config::get( 'apps_login_enabled', 'facebook' ) )
			{
				return null;
			}
			
			$text = 'right_panel_connect';
		}
		
		return '<a class="large-button right-block-action " href="/connect/facebook/">' . __( $text ) . '</a>';
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function facebookFan()
	{
		if ( strlen( Config::get( 'apps_fanpage_default_id' ) ) > 5 )
		{
			return Templates::getInc()->getTpl( '/widgets/widget_facebook_fan_box.html', array(
				'url' => substr( ABS_URL, strpos( ABS_URL, "." ) + 1, -1 ),
				'fanpage' => Config::get( 'apps_fanpage_default' ),
				'template' => ( App::ver( array(
					'bebzol',
					'vines',
					'gag' 
				) ) ? 'side' : 'small' ) 
			) );
		}
	}
	/**
	 * Downloading material previous / next
	 *
	 * @param int $upload_activ - material on the main or in the waiting room
	 * @param data $data - Date Added browsed material
	 * 
	 * @return array self::$nav_resource;
	 */
	public static function getNextPrevious( $id, $upload_activ, $data )
	{
		if ( empty( self::$nav_resource ) )
		{
			if ( !$id )
			{
				$db = PD::getInstance();
				
				$previous = $db->from( IPS__FILES )->setWhere( array(
					'upload_status' => 'public',
					'upload_activ' => $upload_activ,
					'date_add' => array( $data ,  '>' )
				) )->fields("'prev' as sort_key,id,title,upload_image,seo_link")->orderBy( 'date_add', 'ASC' )->limit(1)->getQuery();
				
				$next = $db->from( IPS__FILES )->setWhere( array(
					'upload_status' => 'public',
					'upload_activ' => $upload_activ,
					'date_add' => array( $data ,  '<' )
				) )->fields("'next' as sort_key,id,title,upload_image,seo_link")->orderBy( 'date_add', 'DESC' )->limit(1)->getQuery();
				
				$res = PD::getInstance()->query( "SELECT * FROM (( " . $previous . " ) UNION ALL ( " . $next . " ) ) as alias LIMIT 2" );
				
				if ( isset( $res[0] ) )
				{
					self::$nav_resource[$res[0]['sort_key']] = array(
						'url' => seoLink( $res[0]['id'], false, $res[0]['seo_link'] ),
						'title' => $res[0]['title'],
						'upload_image' => ips_img( $res[0]['upload_image'], 'thumb' ) 
					);
				}
				
				if ( isset( $res[1] ) )
				{
					self::$nav_resource[$res[1]['sort_key']] = array(
						'url' => seoLink( $res[1]['id'], false, $res[1]['seo_link'] ),
						'title' => $res[1]['title'],
						'upload_image' => ips_img( $res[1]['upload_image'], 'thumb' ) 
					);
				}
			}
			else
			{
				return array(
					'next' => array(
						'url' => ABS_URL . 'ajax/redirect/' . $id . '/go/next' 
					),
					'prev' => array(
						'url' => ABS_URL . 'ajax/redirect/' . $id . '/go/previous' 
					) 
				);
			}
		}
		
		return self::$nav_resource;
	}
	
	/**
	 * Download and display the links next - previous page material.
	 *
	 * @param int $upload_activ - material on the main or in the waiting room
	 * @param data $data - Date Added browsed material
	 * 
	 * @return string $buttons
	 */
	public static function navigationButtons( $id )
	{
		$nav_resource = self::getNextPrevious( $id, false, false );
		
		return Templates::getInc()->getTpl( '/widgets/widget_nav_buttons.html', array(
			'next' => ( isset( $nav_resource['next']['url'] ) ? $nav_resource['next']['url'] : false ),
			'previous' => ( isset( $nav_resource['prev']['url'] ) ? $nav_resource['prev']['url'] : false ) 
		) );
	}
	
	/**
	 * Download and display the links next - previous page material.
	 *
	 * @param int $upload_activ - material on the main or in the waiting room
	 * @param data $data - Date Added browsed material
	 * 
	 * @return string $boxes
	 */
	
	public static function navigationBoxes( $upload_activ, $data )
	{
		$nav_resource = self::getNextPrevious( false, $upload_activ, $data );
		
		return Templates::getInc()->getTpl( '/widgets/widget_nav_boxes.html', array(
			'next' => ( isset( $nav_resource['next']['upload_image'] ) ? $nav_resource['next'] : false ),
			'previous' => ( isset( $nav_resource['prev']['upload_image'] ) ? $nav_resource['prev'] : false ) 
		) );
	}
	
	/**
	 * Wyświetlanie buttona FAST nad materiałami tylko
	 * na głównej i w poczekalni
	 * Displaying FAST button on the materials only on the main and waiting
	 *
	 * @param null
	 * 
	 * @return string - button.
	 */
	public static function fastButton()
	{
		if ( in_array( IPS_ACTION, array(
			'waiting',
			'main' 
		) ) )
		{
			return '<div class="fast-button"><a href="' . ABS_URL . 'fast/' . ( IPS_ACTION == 'waiting' ? 'wait' : 'main' ) . '/"></a></div>';
		}
	}
	
	/**
	 * Pagination navigation links above.
	 *
	 * @param $current_page - the actual number of pages
	 * @param $link - current address unnumbered pages
	 * @param $limit - limit for all parties to determine whether the current is not the last
	 * 
	 * @return 
	 */
	public static function navigationPageLinks( $current_page, $page_url, $limit )
	{
		global ${IPS_LNG};
		$page_url = ( $page_url[0] == '/' ? substr( $page_url, 1 ) : $page_url );
		
		$nav_resource = array(
			'next' => ( $current_page < $limit ? ABS_URL . $page_url . ( $current_page + 1 ) : false ),
			'previous' => ( $current_page > 1 ? ABS_URL . $page_url . ( $current_page - 1 ) : false ) 
		);
		
		if ( !empty( $nav_resource['next'] ) || !empty( $nav_resource['previous'] ) )
		{
			return Templates::getInc()->getTpl( '/widgets/widget_nav_page_links.html', $nav_resource );
		}
	}
	
	
	/**
	 * Link nawigacyjny a'la Demotywatory pod listą materiałów.
	 * Link Navigation a'la Demotywatory below the list of materials.
	 *
	 * @param $current_page - the actual number of pages
	 * @param $page_url - current address unnumbered pages
	 * @param $limit - limit for all parties to determine whether the current is not the last
	 * 
	 * @return 
	 */
	public static function navigationPageBox( $current_page, $page_url, $limit )
	{
		
		global ${IPS_LNG};
		
		if ( $current_page < $limit )
		{
			return '<div class="next-box"><a href="' . ABS_URL . ( $page_url[0] == '/' ? substr( $page_url, 1 ) : $page_url ) . ( $current_page + 1 ) . '">' . ${IPS_LNG}['next'] . '</a></div>';
		}
	}
	/**
	 * Search the menu.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function searchBar()
	{
		
		if ( Config::get( 'widget_search_bar' ) == 0 || isset( $_GET['phrase'] ) )
		{
			return;
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_search_bar.html', null, true );
	}
	
	/**
	 * The widget displays the belt with tags at the top of list of materials
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function simplePopularTags()
	{
		
		$res = PD::getInstance()->from( 'upload_tags_post t_r' )->join( 'upload_tags t' )->on( 't.id_tag', 't_r.id_tag' )->fields( "t.tag, COUNT(t_r.id_tag) AS count" )->groupBy( 't.tag' )->orderBy( 'count' )->get();
		
		if ( empty( $res ) )
		{
			return false;
		}
		
		foreach ( $res as $key => $val )
		{
			$res[$key]['url'] = ABS_URL . 'tag/' . Tags::delimiter( $val['tag'] ) . '/';
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_popular_tags_simple.html', array(
			'tags' => $res,
			'count' => count( $res ) - 1 
		) );
	}
	/**
	 * Box containing a list of popular tags
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function popularTags()
	{
		if ( IPS_VERSION == 'pinestic' && IPS_ACTION != 'main' )
		{
			return false;
		}
		
		$res = PD::getInstance()->from( 'upload_tags_post t_r' )->join( 'upload_tags t' )->on( 't.id_tag', 't_r.id_tag' )->fields( "t.tag, COUNT(t_r.id_tag) AS count" )->groupBy( 't.tag' )->orderBy( 'count' )->get( Config::getArray( 'widget_popular_tags_options', 'count' ) );
		
		if ( empty( $res ) )
		{
			return false;
		}
		
		$css_class = array(
			'big',
			'medium',
			'normal',
			'small' 
		);
		
		foreach ( $res as $key => $val )
		{
			$res[$key]['class'] = $css_class[rand( 0, 3 )];
			$res[$key]['url']   = ABS_URL . 'tag/' . Tags::delimiter( $val['tag'] ) . '/';
		}
		
		shuffle( $res );
		
		return Templates::getInc()->getTpl( '/widgets/widget_popular_tags.html', array(
			'tags' => $res,
			'display_header' => Config::getArray( 'widget_popular_tags_options', 'header' )
		) );
	}
	
	/**
	 * Widget wyświetlający pływający blok po prawej w poczekalni
	 * Widget displays a floating block on the right in the waiting room
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public static function popularBox()
	{
		if ( IPS_ACTION == 'main' || IPS_ACTION == 'waiting' )
		{
			if ( IPS_ACTION == 'main' && Config::get( 'widget_top_files_right_main' ) == 0 )
			{
				return;
			}
			elseif ( IPS_ACTION == 'waiting' && Config::get( 'widget_top_files_right_wait' ) == 0 )
			{
				return;
			}
		}
		else
		{
			return;
		}
		
		
		
		$res = PD::getInstance()->select( IPS__FILES, array(
			'upload_activ' => 0,
			'upload_status' => 'public' 
		), 10, false, 'votes_opinion,votes_count,comments DESC' );
		
		
		if ( !empty( $res ) )
		{
			return Templates::getInc()->getTpl( '/widgets/widget_right_waiting.html', array(
				'array' => $res 
			) );
		}
		
	}
	/**
	 * Panel under the category menu
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public static function categoryPanel()
	{
		$categories = Categories::getCategories();
		
		if ( !empty( $categories ) )
		{
			return Templates::getInc()->getTpl( '/widgets/widget_category_panel.html', array(
				'categories' => $categories,
				'image_view' => ( Config::get( 'widget_category_panel_view' ) == 'images' ) 
			) );
		}
		
		return false;
	}
	/**
	 * Displaying a template for the widget menu Facebook Friends
	 *
	 * @param null
	 * 
	 * @return null
	 */
	public static function appFriends()
	{
		return Templates::getInc()->getTpl( 'facebook_friends.html', null, true );
	}
	
	/**
	 * Widget to control the behavior POPUP encouraging to like the page
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public static function popupBox()
	{
		return Templates::getInc()->getTpl( '/widgets/widget_popup.html', array(
			'widget_popup_options' => Config::getArray( 'widget_popup_options' ),
			'apps_fanpage_default' => Config::get( 'apps_fanpage_default' ) 
		), null, true );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function seeMoreWrapper()
	{
		if ( Config::get( 'widget_see_more' ) )
		{
			return Widgets::widgetCached( 'seeMore', ( Config::getArray( 'widget_see_more_options', 'source' ) == 'rand' ? 10 : 2 ) );
		}
	}
	/**
	 * Display box of items under main item on item page
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public static function seeMore()
	{
		$display = new Core_Query();
		
		$settings = Config::getArray( 'widget_see_more_options' );
		
		$args = array(
			'limit' => $settings['limit'],
			'widget_layout' => $settings['layout'],
			'conditions' => array(
				'id' => array(
					IPS_ACTION_GET_ID, '!=' 
				) 
			),
			'pagination' => false,
			'on_widget' => true 
		);
		
		if ( $settings['source'] == 'tags' )
		{
			//OPTIMIZE-IPS ADD RANDOMITY Smilar tags
			$upload_ids = Tags::getSmilar( IPS_ACTION_GET_ID, 10 );
			
			if ( empty( $upload_ids ) )
			{
				return false;
			}
			
			if ( ( $key = array_search( IPS_ACTION_GET_ID, $upload_ids ) ) !== false )
			{
				unset( $upload_ids[$key] );
			}
			
			$args['conditions']['id'] = array(
				$upload_ids,
				'IN' 
			);
		}
		
		$content = $display->init( 'see_more_' . $settings['source'], $args );
		
		if ( !empty( $content ) )
		{
			return Templates::getInc()->getTpl( '/widgets/widget_see_more.html', array(
				'columns' => $settings['layout'],
				'widget_content' => $content
			) );
		}
		
	}
	/**
	 * Funkcja wyświetlająca widget popularnych materiałów pod menu.
	 * The widget displays the most common materials in the menu.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function bestFiles()
	{
		if ( Cookie::exists('widget_best_files_disable') )
		{
			return;
		}
		
		$condition = array(
			'upload_status' => 'public' 
		);
		
		$settings = Config::getArray( 'widget_best_files_options' );
		
		if ( $settings['source'] == 'wait' || $settings['source'] == 'main' )
		{
			$condition['upload_activ'] = (int)( $settings['source'] == 'main' );
		}
		
		if ( $settings['type'] != 'all' )
		{
			$condition['upload_type'] = $settings['type'];
		}
		
		if ( defined( 'IPS_WAIT_BLOCKED' ) && !USER_ADMIN )
		{
			$condition['upload_activ'] = 1;
		}
		
		$rows = PD::getInstance()->optRand( IPS__FILES, $condition, $settings['limit'] );

		if ( !empty( $rows ) )
		{
			foreach ( $rows as $key => $row )
			{
				$rows[$key]['link'] = seoLink( $row['id'], false, $row['seo_link'] );
				
				$rows[$key]['button_share'] = $settings['button'] == 'like' ? SocialButtons::like( $rows[$key]['link'] ) : SocialButtons::shareOld( $rows[$key]['link'], $row['id'] );
				
				$rows[$key]['img_small'] = ips_img( $row, 'thumb' );
				
				if ( isset( $row['pin_title'] ) )
				{
					$rows[$key]['title'] = $row['pin_title'];
				}
			}
			
			return Templates::getInc()->getTpl( '/widgets/widget_best_files.html', array(
				'best_files' => $rows,
				'interval' => ( $settings['interval'] * 1000 ),
				'timer' => $settings['close']
			) );
		}
	}
	/**
	 * Widget displays, a warning about the content.
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public static function onlyAdult()
	{
		if ( isAdult() )
		{
			return;
		}
		return Templates::getInc()->getTpl( '/widgets/widget_adult_only.html', null, true );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function toTop()
	{
		return Templates::getInc()->getTpl( '/widgets/widget_to_top.html', null, true );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function cookiePolicy()
	{
		if ( !Cookie::exists( 'ips_cookie_policy' ) && Config::get( 'widget_cookie_info' ) == 1 )
		{
			return Templates::getInc()->getTpl( '/widgets/widget_cookie_info.html', array(
				'template' => Config::get( 'widget_cookie_info_options', 'template' )
			), true );
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function widgetNotifyPW()
	{
		if ( !USER_LOGGED )
		{
			return false;
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_ajax_notify.html', array(
			'ajax_notify' => Config::get( 'ajax_notify' ) 
		), null, Config::get( 'ajax_notify' ) );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function usersHistoryActivity()
	{
		return Ips_Registry::get( 'History' )->getUsersHistory( 'all' );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	
	public static function widgetMessage( $translate_name )
	{
		switch ( $translate_name )
		{
			case 'pinit_confirm_email_reminder':
				$user = getUserInfo( USER_ID, true );
				if ( $user['upload_activ'] == 1 )
				{
					return false;
				}
				break;
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_top_message.html', array(
			'text' => __( $translate_name ) 
		), null, $translate_name );
	}
	
	/**
	 * Widget personalize site
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function widgetPersonalize()
	{
		$cookie = array();
		
		if( Cookie::exists( 'user_personalize' ) )
		{
			$cookie = json_decode( Cookie::get( 'user_personalize' ), true );
		}

		return Templates::getInc()->getTpl( '/widgets/widget_personalize.html', array(
			'auto_animated' => ( isset( $cookie['auto_animated'] ) && $cookie['auto_animated'] ? 1 : 0 ),
			'auto_video' => ( isset( $cookie['auto_video'] ) && $cookie['auto_video'] ? 1 : 0 ),
			'show_adult' => ( isset( $cookie['show_adult'] ) && $cookie['show_adult'] ? 1 : 0 ) 
		), serialize( $cookie ) );
	}
	/**
	 * Widget small materials in the block on the right
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function smallPosts( $page = 1 )
	{
		return Templates::getInc()->getTpl( '/widgets/widget_small_posts.html' );
	}
	/**
	 * Widget popularnych materiałów w bloku po prawej
	 * Widget common materials in the block on the right
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function popularPosts( $page = 1 )
	{
		if ( defined( 'IPS_AJAX' ) )
		{
			return;
		}

		$condition = array();
		
		$options = Config::getArray( 'widget_popular_posts_options' );
		
		switch ( $options['source'] )
		{
			case 'wait':
				$condition['upload_status'] = 'public';
				$condition['upload_activ']  = 0;
			break;
			case 'main':
				$condition['upload_status'] = 'public';
				$condition['upload_activ']  = 1;
			break;
		}
		
		if ( $options['type'] != 'all' )
		{
			$condition['upload_type'] = $options['type'];
		}
		
		if ( $options['source'] == 'new' )
		{
			$rows = PD::getInstance()->select( IPS__FILES, $condition, xy( $page, $options['limit'] ), '*', array(
				'date_add' => 'DESC' 
			), 'id, title, votes_opinion, comments, seo_link' );
		}
		else
		{
			$rows = PD::getInstance()->optRand( IPS__FILES, $condition, $options['limit'] );
		}
		
		if( empty( $rows ) )
		{
			return false;
		}
		
		foreach ( $rows as $key => $row )
		{
			$rows[$key]['link'] = ABS_URL . $row['id'] . '/' . $row['seo_link'];
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_popular_posts_right.html', array(
			'files_popular' => $rows 
		) );
		
		
	}
	
	/**
	* Display best comments
	*
	* @param 
	* 
	* @return 
	*/
	public static function bestComments()
	{
		$comments = new Comments();
		
		$best_comments = $comments->bestComments( IPS_ACTION_GET_ID );
		
		if( empty( $best_comments ) )
		{
			return false;
		}
		
		$comments = '';

		$tpl = Templates::getInc();	
			
			foreach( $best_comments as $key => $r )
			{
				$comments .= AdSystem::getInstance()->showAd( 'between_comments', $key );

				$comments .= $tpl->getTpl( '/widgets/widget_best_comments.html', array_merge( $r, array(
					'avatar'		=> ips_user_avatar( $r['avatar'] ),
					'content'		=> nl2br( $r['content'] ),
					'date_format'	=> formatDate( $r['date_add'] )
				) ) );
			}
			
		return $comments;

	}
	
	/**
	 * Widget user idle
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function userIdle()
	{

		$condition = array();
		
		$options = Config::getArray( 'widget_user_idle_options' );
		
		$rows = PD::getInstance()->select( IPS__FILES, false, 4, '*', array(
			$options['files_sort'] => 'DESC' 
		) );
		
		if( empty( $rows ) )
		{
			return false;
		}
		
		foreach ( $rows as $key => $row )
		{
			$rows[$key]['seo_link'] = ABS_URL . $row['id'] . '/' . $row['seo_link'];
			$rows[$key]['img_small'] = ips_img( $row, 'thumb' );
		}
		
		return Templates::getInc()->getTpl( '/widgets/widget_user_idle.html', array(
			'idle_files' => $rows,
			'msg' => __s('widget_user_idle_msg', $options['time'] ),
			'time' => $options['time'],
			'options' => $options,
			'class' => ( ( $options['ad'] && !$options['files'] ) || ( !$options['ad'] && $options['files'] ) ? ' has-one-only' : '' )
		) );
		
		
	}
	
	
	/**
	 * Caching of any widget.
	 * Sprawdzanie cache i wyświetlanie użytkownikowi.
	 * Checking cache and display to the user.
	 *
	 * @param $params - widget display controller (function name)
	 * 
	 * @return 
	 */
	public static function widgetCached( $function, $rand = 1 )
	{
		
		$cache_id = '/widgets/' . strtolower( $function ) . '/' . md5( $function . rand( 1, $rand ) );
		
		if ( !$cache_content = Ips_Cache::get( $cache_id ) )
		{
			$cache_content = Widgets::$function();
			
			Ips_Cache::write( $cache_content, $cache_id );
		}
		return $cache_content;
	}
	
	/**
	 * Clear cache for widget.
	 *
	 * @param $function - widget display controller (function name)
	 * 
	 * @return 
	 */
	public static function widgetCachedClear( $function = false )
	{
		Ips_Cache::clearCacheFiles( 'widgets/' . ( $function ? strtolower( $function ) . '/' : '' ) );
	}
}
?>