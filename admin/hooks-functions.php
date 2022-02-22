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

/** Hook functions */
/* function reservedPriority( $hook )
{
	$priority_list = array(
		'before_content' => array( 1, 2, 3, 4, 5, 6 ),
		'after_content' =>  array( 1, 2, 10 ),
	);
	return isset( $priority_list[$hook] ) ? $priority_list[$hook] : array();
} */

function defaultWidgets()
{
	return array(
		'widget_sort_box' => array(
			'hook' => 'before_content',
			'function' => 'call_widget',
			'priority' => 0.9,
			'params' => array( 'sortBox', 'widget_sort_box' ),
		),
		'widget_category_panel' => array(
			'hook' => 'before_content',
			'priority' => 3.9,
			'function' => 'call_widget',
			'params' => array( 'widgetCached', 'widget_category_panel', 'categoryPanel' )
		),
		'widget_best_files' => array(
			'hook' => 'before_content',
			'priority' => 4.9,
			'function' => 'call_widget',
			'params' => array( 
				'widgetCached', 
				array( 
					'rand_cache' => ( Config::get( 'widget_best_files_source' ) == 'rand' ? 100 : 10 ), 
					'setting_name' => 'widget_best_files' 
				), 
				'bestFiles'
			)
		),
		'widget_history_users_activity' => array(
			'hook' => 'before_content',
			'priority' => 5.9,
			'function' => 'call_widget',
			'params' => array( 'widgetCached', 'widget_history_users_activity', 'usersHistoryActivity' )
		), 
		'widget_popular_tags_wait' => array(
			'templates' => array(
				'no' => array( 'pinestic' )
			),
			'hook' => 'before_content',
			'priority' => 6.9,
			'function' => 'call_widget',
			'params' => array( 
				'widgetCached', array( 
					'ips_action' => 'waiting', 
					'rand_cache' => 5, 
					'setting_name' => 'widget_popular_tags_wait' 
				), 'simplePopularTags'
			)
		), 
		
		'widget_fan_box' => array(
			'hook' => ( App::ver( array( 'bebzol', 'vines', 'gag' ) ) ? 'at_side_block' : 'after_content' ),
			'priority' => 3.9,
			'function' => 'call_widget',
			'params' => array( 'facebookFan', 'widget_fan_box' ),
			'positions' => 'allow_all'
		), 
		'widget_popular_tags' => array(
			'hook' => ( App::ver( array( 'bebzol', 'vines', 'gag' ) ) ? 'at_side_block' : 'after_content' ),
			'priority' => 4.9,
			'function' => 'call_widget',
			'params' => array( 'widgetCached', 'widget_popular_tags', 'popularTags' ),
			'positions' => 'allow_all'
		),
		
		/* SIEDE BLOCK */
		'widget_personalize' => array(
			'templates' => array(
				'no' => array( 'pinestic' )
			),
			'hook' => 'at_side_block',
			'priority' => 2.9,
			'function' => 'call_widget',
			'params' => array( 
				'widgetCached', array( 
					'ips_action_exclude' => array('file_page'), 
					'setting_name' => 'widget_personalize' 
				), 'widgetPersonalize'
			),
			'positions' => array( 'at_side_block' )
		),
		'widget_side_action_button' => array(
			'templates' => array(
				'no' => array( 'pinestic' )
			),
			'hook' => 'at_side_block',
			'priority' => 5.9,
			'function' => 'call_widget',
			'params' => array( 'connectButton', 'widget_side_action_button' ),
			'positions' => array( 'at_side_block' )
		),
		'widget_popular_posts' => array(
			'templates' => array(
				'no' => array( 'pinestic' )
			),
			'hook' => 'at_side_block',
			'priority' => 6.9,
			'function' => 'call_widget',
			'params' => array( 
				'widgetCached', array( 
					'rand_cache' => 5, 
					'setting_name' => 'widget_popular_posts' 
				), 
				'popularPosts'
			),
			'positions' => array( 'at_side_block' )
		), 
		'widget_small_posts' => array(
			'templates' => array(
				'no' => array( 'pinestic' )
			),
			'hook' => 'at_side_block',
			'priority' => 7.9,
			'function' => 'call_widget',
			'params' => array( 
				'widgetCached', array( 
					'rand_cache' => 5, 
					'setting_name' => 'widget_small_posts' 
				), 'smallPosts'
			),
			'positions' => array( 'at_side_block' )
		), 
	);
}

function defaultAdsHooks()
{
	return array(
		'under_menu' => array( 
			'hook' => 'before_content',
			'priority' => 1.9,
			'positions' => array( 'before_content' )
		),
		'side_block_top' => array( 
			'hook' => 'at_side_block',
			'priority' => 2.1,
			'positions' => array( 'at_side_block' )
		),
		'side_block_bottom' => array( 
			'hook' => 'at_side_block',
			'priority' => 10,
			'positions' => array( 'at_side_block' )
		)
	);
}

function getAllowedPositions( $hook )
{
	$hooks = array_merge( defaultAdsHooks(), defaultWidgets() );
	
	$positions = array(
		'before_content' => __( 'hook_before_content' ),
		'after_content' => __( 'hook_after_content' ),
		'before_footer' => __( 'hook_before_footer' ),
		'after_footer' => __( 'hook_after_footer' ),
	);

	if( isset( $hooks[ $hook ] ) && isset( $hooks[ $hook ]['positions'] ) )
	{	
		if( is_array( $hooks[ $hook ]['positions'] ) )
		{
			$positions = array();
			foreach( $hooks[ $hook ]['positions'] as $key => $position )
			{
				$positions[$position] = __( 'hook_' . $position );
			}
		}
		else
		{
			$positions['at_side_block'] = __( 'hook_at_side_block' );
		}
	}
	
	return $positions;
}
function updateHooksAds( $ad_unique_name = false, $ad_activ = false )
{
	$ads_hooks = defaultAdsHooks();
	
	if( !$ad_unique_name )
	{
		$ads = PD::getInstance()->select( 'ads', array(
			'unique_name' => array( array_keys( $ads_hooks ), 'IN' )
		));
		
		foreach( $ads as $ad )
		{
			updateHooksAds( $ad['unique_name'], $ad['ad_activ'] );
		}
		return ;
	}

	if( array_key_exists( $ad_unique_name, $ads_hooks ) )
	{
		$hooks = Ips_Registry::get( 'Hooks' );
	
		$key = $ads_hooks[$ad_unique_name]['hook'] . '_' . $ad_unique_name;
		
		$action = $hooks->find_action( $key );
		
		if ( !$action && $ad_activ )
		{
			$hooks->update_action( $key, $ads_hooks[$ad_unique_name]['hook'], 'callable_action_ads', array( $ad_unique_name ), $ads_hooks[$ad_unique_name]['priority'] );
		}
		elseif( $action && !$ad_activ )
		{
			$hooks->delete_action_by_key( $key, $ads_hooks[$ad_unique_name]['hook'] );
		}
	}
}

function updateWidgetHooks( $force_update = false, &$hooks_post )
{
	$to_check = defaultWidgets();
	
	if( !defined('IPS_INSTALLING') )
	{
		Widgets::widgetCachedClear();
	}
	
	$hooks = Ips_Registry::get( 'Hooks' );

	foreach( $to_check as $widget => $options )
	{
		if( isset( $options['templates'] ) )
		{
			if( isset( $options['templates']['no'] ) && in_array( IPS_VERSION, $options['templates']['no'] ) )
			{
				$hooks_post[ $widget ] = 0;
			}
		}
		
		if( isset( $hooks_post[ $widget ] ) )
		{
			$key = $options['hook'] . '_' . $widget;
			
			if( $hooks_post[ $widget ] == 1 && ( Config::noCache( $widget ) != 1 || $force_update ) )
			{
				$action = $hooks->find_action( $key, $options['hook'] );
				
				if( $action )
				{
					$options = $action;
				}
				
				$hooks->update_action( $key, $options['hook'], $options['function'], $options['params'], $options['priority'] );
			}
			elseif( $hooks_post[ $widget ] == 0 && ( Config::noCache( $widget ) != 0 || $force_update ) )
			{
				$hooks->delete_action_by_key( $key, $options['hook'] );
			}
		}
	}
	
	
}
function updatePluginHooks()
{
	/* $hooks = Ips_Registry::get( 'Hooks' );
	
	$files = File::search( ABS_PLUGINS_PATH, '.hooks.php' );
	foreach( $files as $file )
	{
		$actions = require_once( $file );
		if( is_array( $actions ) )
		{
			foreach( $actions as $action )
			{
				$hooks->register_action( $action['hook'], 'callable_plugin', $action['args'], $action['priority'], $action['key'] );
			}
		}
	} */
}

function updateSystemHooks()
{
	$hooks = Ips_Registry::get( 'Hooks' );
	
	if( !$hooks->find_action( 'Session::getFlash', 'before_content' ) )
	{
		$hooks->update_action( 'Session::getFlash', 'before_content', 'Session::getFlash', null, 3 );
	}

}
function realoadDefaultHooks()
{
	Config::update('hooks_actions_registry', serialize( array() ) );
	
	$widgets = defaultWidgets();
	
	$fields = PD::getInstance()->from( 'system_settings')->where( 'settings_name', array_keys( $widgets ), 'IN' )->get();
	
	$settings = array_column( $fields, 'settings_value', 'settings_name' );
	
	updateHooksAds();
	updateWidgetHooks( true, $settings );
	updateSystemHooks();
	updatePluginHooks();
}
function hookTitle( $function, $params )
{
	global ${IPS_LNG};
	
	if( count( $params ) > 1 )
	{
		if( is_string( $params[1] ) && isset( ${IPS_LNG}[ $params[1] . '_title' ] ) )
		{
			return ${IPS_LNG}[ $params[1] . '_title' ];
		}
		elseif( is_array( $params[1] ) && isset( $params[1]['setting_name'] ) )
		{
			if( isset( ${IPS_LNG}[ $params[1]['setting_name'] . '_title' ] ) )
			{
				return ${IPS_LNG}[ $params[1]['setting_name'] . '_title' ];
			}
		}
	}
	
	if( $function == 'Session::getFlash' )
	{
		return ${IPS_LNG}[ 'hook_session_flash_title' ];
	}
	
	if( $function == 'callable_action_ads' )
	{
		$text = '';
		foreach( $params as $ad_id )
		{
			$text .= ${IPS_LNG}[ 'ads_' . $ad_id ];
		}
		
		return $text;
	}
	
	return 'system';
}