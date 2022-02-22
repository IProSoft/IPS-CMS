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
	
	/** Filters **/
	/**
	* Available filters: 
	*  og_meta: $og_meta, $_info
	*  init_args: $base_args
	*  up_create_config: $Upload_Extended->config, $Upload_Extended
	*  upload_post_data: $_POST array while upload
	*  upload_comment_content: comment content while add
	*  upload_ranking_images: ranking images while upload
	*  upload_add_text: upload text
	*  
	*/
	function add_filter( $tag, $function_to_add, $priority = 10 )
	{
		return Ips_Registry::get( 'Hooks' )->add_filter( $tag, $function_to_add, $priority );
	}
	
	function remove_filter( $tag, $function_to_remove, $priority )
	{
		return Ips_Registry::get( 'Hooks' )->remove_filter( $tag, $function_to_remove, $priority);
	}
	
	function apply_filters( $tag )
	{
		return Ips_Registry::get( 'Hooks' )->apply_filters( $tag, array_slice( func_get_args(), 1 ) );
	}
	
	/** Actions **/
	function add_action( $hook, $function, $params = null, $priority = 1 )
    {
        return Ips_Registry::get( 'Hooks' )->add_action( $hook, $function, $params, $priority );
    }
	
	function remove_action( $hook, $function, $function_callable = false )
    {
        return Ips_Registry::get( 'Hooks' )->remove_action( $hook, $function, $function_callable );
    }

    function do_action( $hook, $args = false )
    {
		return Ips_Registry::get( 'Hooks' )->do_hook( $hook, $args );
    } 
	
	function remove_hook( $hook )
    {
        return Ips_Registry::get( 'Hooks' )->remove_hook( $hook );
    }
	/**
	* Call widget with check if its on in admin panel
	*/
	function call_widget( $function, $conditions = false, $params = null )
	{
		$add_param = false;

		if( $conditions !== false )
		{
			if( is_string( $conditions ) && Config::get( $conditions ) === 0 )
			{
				return false;
			}
			elseif( is_array( $conditions ) )
			{
				if( isset( $conditions['screen'] ) && Config::get( $conditions['setting_name'] ) == 0 )
				{
					return false;
				}
				
				if( isset( $conditions['screen'] ) && !App::onScreen( $conditions['screen'] ) )
				{
					return false;
				}

				/** IPS_ACTION condition */
				if( isset( $conditions['ips_action'] ) && IPS_ACTION != $conditions['ips_action'] )
				{
					return false;
				}
				elseif( isset( $conditions['ips_action_exclude'] ) && in_array( IPS_ACTION, $conditions['ips_action_exclude'] ) )
				{
					return false;
				}

				/** $_COOKIE condition */
				if( isset( $conditions['cookie'] ) )
				{
					$exists = Cookie::exists( $conditions['cookie']['name'] );
					if( $conditions['cookie']['value'] == 'isset' && !$exists )
					{
						return false;
					}
					elseif( $conditions['cookie']['value'] == 'not-isset' && $exists  )
					{
						return false;
					}
				}
				/** widgetCached cached with random hash number */
				if( isset( $conditions['rand_cache'] ) )
				{
					$add_param = $conditions['rand_cache'];
				}
			}
		}
		
		echo Widgets::$function( $params, $add_param );
	}
	
	/**
	* Calls add display
	*/
	function callable_action_ads( $params )
	{
		echo AdSystem::getInstance()->showAds( $params );
	}
	
	/**
	* Calls plugin init
	*/
	function callable_plugin( $plugin, $ips_actions = false )
	{
		if( $plugin && Config::getArray( 'ips_plugins', strtolower( $plugin ) ) !== false )
		{
			if( $ips_actions && !in_array( IPS_ACTION, $ips_actions ) )
			{
				return false;
			}
			
			$file = ABS_PLUGINS_PATH . '/' . $plugin . '/class.plugin.' . $plugin .'.php';

			if( file_exists( $file ) )
			{
				require_once( $file );
				$class = new $plugin;
				
				return $class->hooks();
			}
		}
		
		return false;
	}
	/**
	* Available Hooks: 
	*  load
	*  init
	*  before_content, 
	*  after_content,
	*  before_footer
	*  after_footer
	*  before_files_display
	*  after_files_display
	*/
	

	add_action('after_footer', 'callable_action_ads', array( array( 'left_side_list', 'right_side_list', 'bottom_slide_ad' ) ) );

	/** Float position **/
	add_action( 'after_content', 'call_widget', array( 'widgetCached', 'widget_user_idle', 'userIdle' ), 10 );
	add_action( 'after_content', 'call_widget', array( 'toTop', 'widget_go_to_top' ), 10 );
	add_action( 'after_content', 'call_widget', array( 'widgetCached', array( 
		'cookie' => array(
			'value' => 'not-isset',
			'name' => 'ips-popup'
		), 
		'setting_name' => 'widget_popup' 
	), 'popupBox' ), 10 );
	
	
	
	if( IPS_VERSION != 'pinestic' )
	{
		/** Float position **/
		add_action('after_content', 'call_widget', array( 'widgetCached', 'widget_float_slides', 'sliderSocial' ), 10 );
		add_action('after_content', 'call_widget', array( 'widgetCached', array(
			'setting_name' => 'widget_float_box',
			'screen' => 800
		), 'floatBOX' ), 10 );
	}
	else
	{
		add_action('after_top', 'call_widget', array( 'widgetMessage', array( 
			'setting_name' => 'widget_confirm_email_reminder' 
		), 'pinit_confirm_email_reminder' ), 1,array( 
			'only_logged' => true,
			'ips_version' => 'pinestic',
		) );
	}
	
	add_action('after_content', 'call_widget', array( 'onlyAdult', 'widget_only_adult' ) );
	
	add_action('after_footer', 'call_widget', array( 'widgetNotifyPW', 'ajax_notify' ) );
	add_action('after_footer', 'call_widget', array( 'cookiePolicy' ) );
	
	Ips_Registry::get( 'Hooks' )->register_actions();