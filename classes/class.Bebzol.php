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

class Bebzol
{
	
	public static function init()
	{
		App::$app['comments-width'] = 638;
		if ( in_array( IPS_ACTION, array(
			'main',
			'waiting',
			'share',
			'nk',
			'google',
			'random',
			'category',
			'user_files' 
		) ) )
		{
			add_action( 'before_files_display', 'Bebzol::headerContent' );
		}
	}
	public static function headerContent()
	{
		global ${IPS_LNG};
		$fast = Config::getArray( 'page_fast_options', 'widget' ) ? Widgets::fastButton() : '';
		
		switch ( IPS_ACTION )
		{
			case 'main':
				$trans = 'menu_main';
				break;
			case 'waiting':
				$trans = 'menu_waiting_menu';
			break;
			case 'share':
			case 'nk':
			case 'google':
				$trans = 'top_' . IPS_ACTION . '_menu';
			break;
			case 'random':
				$trans = 'menu_random_menu';
			break;
			case 'category':
				
				$category = Categories::getCategories( IPS_ACTION_GET_ID );
				
				return '<div class="header-content-pages"><h1>' . ${IPS_LNG}['meta_categories'] . $category['category_name'] . '</h1>' . $fast . '</div>';
				
			break;
			case 'user_files':
				$trans = 'userlinks_' . $_GET['action'];
			break;
				
		}
		
		$fast = Config::getArray( 'page_fast_options', 'widget' ) ? Widgets::fastButton() : '';
		return ( isset( $trans ) && isset( ${IPS_LNG}[$trans] ) ? '<div class="header-content-pages"><h1>' . ${IPS_LNG}[$trans] . '</h1>' . $fast . '</div>' : $fast );
	}
	
	public static function sideBlock()
	{
		return Templates::getInc()->getTpl( 'side_block.html' );
	}
	
}
?>