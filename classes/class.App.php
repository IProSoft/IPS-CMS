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
	/*
	 * The definition of default paths
	 */
	define( 'ABS_CSS_JS_CACHE_PATH', ABS_PATH . '/cache/minify' );
	
class App
{
	/**
	 * Tablica przechowująca zapisane dane 
	 * A table for storing the recorded data
	 */
	static $app = array();
	

	/**
	 * Contains template args
	 */
	static $base_args = array();
	
	/**
	 * Store base class
	 */
	static $base_class = null;
	
	/**
	 * Start APP
	 *
	 * @param null
	 * 
	 * @return null
	 */
	public static function init()
	{
		$class = self::getClass( IPS_ACTION ) . '_Controller';
		
		if ( class_exists( $class ) )
		{
			self::$base_class = new $class();
		}
		
		do_action( 'init' );
		
		self::$app['comments-width'] = 690;
		
		self::$app['ips_randomity'] = Config::get('ips_randomity');
		
		$class = ucfirst( IPS_VERSION );
		
		if ( class_exists( $class ) )
		{
			$class::init();
		}
	}
		
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function minimalLayout()
	{
		Config::tmp( 'display_side_block', false );
		
		self::$base_args['minimal_layout'] = true;
		
		remove_hook( array(
			'before_content',
			'after_content',
			'after_footer',
			'display_paginator' 
		) );
		
		add_action( 'before_content', 'Session::getFlash' );
	}
	
	/**
	 * Finish page load calling all methods with filters/hooks
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function load( &$base_args )
	{
		$base_args['side_block'] = $base_args['side_block'] ? call_user_func( ucfirst( IPS_VERSION ) .'::sideBlock' ) : App::hiddenBlock( App::$base_args );
		
		$base_args['js_variables'] = self::getJsVariables();

		$config = Config::getMulti( array(
			'app_css_files',
			'app_javascript_files',
			'system_cache'
		) );
		
		$base_args['header_js']  = self::getJs( $config );
		$base_args['header_css'] = self::getCss( $config );
		
		$base_args = apply_filters( 'init_base_args', $base_args );
		
		$base_args['body_class'] = implode( ' ', $base_args['body_class'] );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function initHeader( &$base_args )
	{
		$seo = new Site_Info();
		$seo->setInfo();
		
		/**
		 * Inicjowanie meta tagów(header) dla Facebook.com
		 * Initiating meta tags (header) for Facebook.com
		 */
		
		SocialButtons::initSocial( $seo->info, ( IPS_ACTION == 'file_page' ? getFileInfo() : false ) );
		
		$base_args['title']          = $seo->info['site_title'];
		$base_args['keywords']       = $seo->info['site_keywords'];
		$base_args['description']    = $seo->info['site_description'];
		$base_args['head_code']      = Config::get( 'head_code' );
		$base_args['template']       = IPS_VERSION;
		$base_args['facebook_init']  = SocialButtons::getHeader();
		
		$base_args['body_class']     = array(
			'logged' 	=> USER_LOGGED ? 'ips-logged-in' : 'ips-logged-out',
			'content' 	=> 'content-while-' . str_replace( 'pinestic_', '', IPS_ACTION ),
			'version' 	=> 'ver-' . IPS_VERSION,
			'side_block'=> 'ips-side-block-' . Config::getArray( 'template_settings', 'side_block_position' )
		);
		
		if ( IPS_ACTION != 'file_page' )
		{
			$base_args['body_class']['columns'] = IPS_ACTION_LAYOUT . '_columns';
		}
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function initMenu( &$base_args )
	{
		if ( isset( $base_args['minimal_layout'] ) )
		{
			return;
		}
		
		$user = array(
			'facebook_btn' => false,
			'nk_btn' => false 
		);
		
		if ( !Config::get( 'connect_facebook' ) && Config::get( 'apps_login_enabled', 'facebook' ) )
		{
			$user['facebook_btn'] = true;
		}
		
		if ( !Config::get( 'connect_nk' ) && Config::get( 'apps_login_enabled', 'nk' ) )
		{ 
			$user['nk_btn'] = true;
		}
		
		if ( USER_LOGGED )
		{
			$user = array_merge( getUserInfo( USER_ID, true ), $user );
		}
		
		$base_args['contest_url']    = IPS_VERSION == 'kwejk' && App::$app['count_konkursy'];
		$base_args['categories']     = Config::get( 'categories_option' ) ? Categories::getCategoriesMenu( IPS_VERSION == 'pinestic' ) : '';
		$base_args['fanpage']        = SocialButtons::like( Config::get( 'apps_fanpage_default' ) );
		$base_args['menu_list']      = App::getMenu( IPS_VERSION == 'pinestic' ? 'pinit_menu' : 'main_menu' );
		$base_args['additional_css'] = Config::get( 'web_fonts_config', 'css' );
		$base_args['widgets']        = Widgets::searchBar();
		$base_args['user']           = $user;

	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function initFooter( &$base_args )
	{
		if ( isset( $base_args['minimal_layout'] ) )
		{
			return;
		}

		$base_args['footer_code']  = Config::get( 'footer_code' );
		$base_args['stat_text'] = '';
		$base_args['change_language'] = ( Config::getArray( 'language_settings', 'allow_change_languages' ) && Config::getArray( 'language_settings', 'ips_multilanguage' ) ? Translate::getInstance()->setLanguageForm() : '' );
		
		$base_args['footer_menu'] = App::getMenu( 'footer_menu' );
		
		if ( Config::get( 'online_stats' ) )
		{
			$stats = new Users_Online();
			$online = $stats->generateStats( 'bottom_stats' );
			$base_args['stat_text'] = $stats->generateStats( __( 'bottom_stats' ) );
		}
		
	}
	
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function initSideBlock( &$base_args )
	{
		$base_args['side_block'] = App::ver( array( 
			'gag', 'bebzol', 'vines'
		) ) && Config::tmp( 'display_side_block' );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function hiddenBlock( &$base_args )
	{
		$base_args['body_class']['side_block'] = 'ips-side-block-hidden';
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function initContent( &$base_args )
	{
		if ( strpos( IPS_ACTION, 'pinestic_' ) !== false )
		{
			//$base_args['body_content'] = Pinestic::getInstance()->index_action( substr( IPS_ACTION, 10 ) );
			
			return $base_args['body_content'] = Pinestic::getInstance()->index_action( substr( IPS_ACTION, 10 ) );
		}
		
		if( is_object( self::$base_class ) && method_exists( self::$base_class, 'route' ) )
		{
			//$base_args['body_content'] = self::$base_class->route();
			
			return $base_args['body_content'] = self::$base_class->route();
		}

		$display = new Core_Query();
		
		$base_args['body_content'] = $display->init( IPS_ACTION );
		/* Couses TP save cache error : unset( $display ); */
		
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function countMySQL()
	{
		return ( IPS_VERSION == 'pinestic' ? 0 : (int)Config::get( 'wait_counter' ) );
	}

	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getJsVariables()
	{
		$js = Config::getArray( 'jquery_array' );
		
		if ( USER_LOGGED )
		{
			$js['ips_user'] = array_merge( $js['ips_user'], array(
				'is_logged' => 'true',
				'login' =>  USER_LOGIN,
				'user_id' => USER_ID
			) );
		}
		
		$js['ips_config'] = array_merge( $js['ips_config'], array(
			'file_id'		=>	IPS_POST_PAGE ? IPS_ACTION_GET_ID : false,
			'ips_action'	=>  IPS_ACTION,
			'upload_action' =>	IPS_ACTION == 'up' ? trim( $_GET['routes'], '/' ) : false,
			'wait_counter'	=>  Config::getArray('template_settings', 'wait_counter' ) ? self::countMySQL() : 0,
			'is_mobile'		=>  IPS_IS_MOBILE,
			'debug'			=>  IPS_DEBUG,
		) );
			
		$js = apply_filters( 'init_js_variables', $js );
		
		$js['ips_config'] 	 = json_encode( $js['ips_config'] );
		$js['ips_user'] 	 = json_encode( $js['ips_user'] );
	
		ksort( $js );
		
		foreach( $js as $key => $variable )
		{
			$js[$key] = $key . ' = ' . $variable;
		}
		
		return 'var ' . implode( ",\n\t\t", $js ) . ';';
	}
	/**
	 * Preparation of the cache file for CSS
	 *
	 * @param null
	 * 
	 * @return null
	 */
	public static function getCss( $config )
	{
		$css_files = apply_filters( 'init_css_files', unserialize( $config['app_css_files'] ) );
		/**
		 * The default CSS files
		 */
		if( USER_MOD )
		{
			array_unshift( $css_files['path'], 'css/css_moderator.css' );
			
			return self::adminFiles( $css_files['path'] );
		}
		
		/**
		 * Caching enabled, download files from the cache
		 */
		if ( $config['system_cache']['css_js'] != 0 )
		{
			return self::checkFile( $css_files['minify'], $config['system_cache']['css_js_expiry'], 'css' );
		}
		
		return self::setFile( $css_files, 'css' );
	}
	
	/**
	 * Preparation of the cache file for CSS
	 *
	 * @param null
	 * 
	 * @return null
	 */
	public static function getJs( $config )
	{
		$js_files = apply_filters( 'init_js_files', unserialize( $config['app_javascript_files'] ) );

		/**
		 * The default JS files
		 */
		array_unshift( $js_files['minify'], Translate::getInstance()->getJsLang() );
		
		if( USER_MOD )
		{
			array_unshift( $js_files['path'], Translate::getInstance()->getJsLang() );
			
			return self::adminFiles( $js_files['path'] );
		}

		return self::setFile( $js_files, 'js' );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function setFile( $files, $type )
	{
		$files = implode( ',', $files['minify'] );
		
		return array( array(
			'src' => ABS_URL . 'cache/minify/min-' . base_convert( intval( $files, 36 ), 20, 36 ) . '.' . $type . '-' . $files,
			'ips_randomity' => self::$app['ips_randomity']
		) );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function adminFiles( $files )
	{
		$files = self::findToMinify( $files );
		
		return array_map( function( $file ){
			return array(
				'src'			=> ABS_URL . $file,
				'ips_randomity' => Config::get( 'ips_randomity' )
			);
		}, $files );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function minifyPaths( $ips_version = IPS_VERSION)
	{
		return array(
			ABS_PATH,
			ABS_PATH . '/cache',
			ABS_PATH . '/css',
			ABS_PATH . '/css/dialogs',
			ABS_PATH . '/js',
			ABS_PATH . '/templates/' . $ips_version . '/css',
			ABS_PATH . '/templates',
			ABS_PATH . '/libs'
		);
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function findToMinify( $to_minify, $ips_version = IPS_VERSION )
	{
		$include_files = array();
		
		$include_path_minify = self::minifyPaths( $ips_version );
		
		foreach( $to_minify as $file )
		{
			foreach( $include_path_minify as $val => $path )
			{
				if( file_exists( $path . '/' . $file ) )
				{
					$include_files[] = strlen( ABS_PATH ) > 1 ? trim( str_replace( ABS_PATH, '', $path ) . '/' . $file, '/' ) : ( substr( $path, 1 ) . '/' . $file );
					break;
				}
			}
		}
		
		return $include_files;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function getMenu( $menu_id = 'main_menu' )
	{
		$menu_items = Config::getArray('cache_data_menus', $menu_id . '_' . strtolower( IPS_LNG ) );
		
		if( !$menu_items )
		{
			require_once( IPS_ADMIN_PATH .'/admin-functions.php');
			$menu_items = updateMenu( false, $menu_id );
		}
	
		return Translate::getInstance()->translate( $menu_items );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function ver( array $check_versions )
	{
		return in_array( IPS_VERSION, $check_versions );
	}

	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function block( $title, $content, $has_include = false, $visibility = true )
	{
		if( $has_include )
		{
			preg_match_all( '/\[include="([^"]*)"\]/', $content, $code );
			
			foreach( $code[1] as $file )
			{
				ob_start();
					include( ABS_PATH . '/' . trim( $file, '/' ) );
					$get_contents = ob_get_contents();
				ob_end_clean();
				
				$content = preg_replace( '@\[include="' . $file . '"\]@iu', $get_contents, $content );
			}
		}

		return !empty( $content ) && $visibility ? $content : '';
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function routes( array $routes )
	{

		$user_get = explode( '/', $_GET['routes'] );
		
		foreach( $routes as $key => $val )
		{
			$routes[$val] = isset( $user_get[$key] ) ? $user_get[$key] : '';
			unset( $routes[$key] );
		}
		
		$_GET = array_merge( $_GET, $routes );
		
		return $routes;
	}
	
	/**
	 * Get the CSRF token value.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getToken()
	{
		if( !$_token = Session::get('_token') )
		{
			$_token = Session::set( '_token', str_random( 40 ) );
		}
		
		return (string)$_token;
	}
	
	/**
	 * Get the CSRF token value.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getClass( $class_name )
	{
		if( strpos( $class_name, '_' ) !== false )
		{
			return implode( '_', array_map( 'ucfirst', explode( '_', $class_name ) ) );
		}
		
		return ucwords( $class_name );
	}
	
	/**
	 * Replace censored words in string.
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function censored( $text )
	{
		$censored_words = Config::getArray( 'censored_words' );

		if( is_array( $censored_words ) )
		{
			foreach( $censored_words as $word )
			{
				$text = preg_replace('/' . str_replace( '.', '\.', $word['word'] ) . '/i', $word['change_to'], $text );
			}
		}
		return $text;
	}
	
	/**
	 * Load CSS async
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function async( $urls )
	{
		return self::script( 'head.load(' . str_replace( '\\', '', json_encode( $urls ) ) . ')' );
	}
	
	/**
	 * Insert script
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function script( $content )
	{
		return '<script type="text/javascript">' . $content . '</script>';
	}

	/**
	 * Comparet screen width
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function onScreen( $width )
	{
		$size = Cookie::get( 'ips_screen_size' );
		
		return $size == 0 || $size < $width;
	}
	
	/**
	 * Minify HTML
	 *
	 * @param 
	 * 
	 * @return 
	 */
	static function minify( $c )
	{
		return preg_replace('/[\s]+/mu', ' ', preg_replace('#\>\R*\t*\s*\t*\<#', '><', $c ) );
	}
}
?>