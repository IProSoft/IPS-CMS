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
	
	/**
	* Controllers containing definitions for MySQL query
	*/
class Core_Registry
{
	
	private static $initalized = false;
	
	public static function init()
	{
		if( !self::$initalized )
        {
            self::$initalized = new Core_Registry();
        }
		return self::$initalized;
	}

	/**
	* Call method
	*/
	public function call_action( $controller )
	{
		/**
		* Check if called method exist in registry
		*/
		if( method_exists( $this, 'action_' . $controller ) )
		{
			return $this->{ 'action_' . $controller }();
		}
		
		return false;
	}
	
	
	public function action_main()
	{
		add_action('after_files_display', 'call_widget', array( 'popularBox', 'widget_top_files_right' ) );
		
		return array(
			'condition' => array(
				'upload_status' => 'public',
				'upload_activ' => 1
			),
			'pagination' => '/page/'
		);
	}
	
	public function action_waiting()
	{
		add_action('after_files_display', 'call_widget', array( 'popularBox', 'widget_top_files_right' ) );
		
		return array(
			'condition' => array(
				'upload_status' => 'public',
				'upload_activ' => 0
			),
			'pagination' => '/waiting/'
		);
	}
	
	public function action_archive()
	{
		return array(
			'condition' => array(
				'upload_status' => 'archive',
			),
			'pagination' => '/archive/'
		);
	}
	
	public function action_random()
	{
		return array(
			'sorting' => false,
			'limit' => 1,
			'callable_before' => 'getRandomId',
		);
	}
	
	public function action_smilar()
	{
		return array();
	}
	
	/**
	* TOP NK/SHARE/GOOGLE
	*/ 
	public function action_social_shares()
	{
		return array(
			'table' => IPS__FILES . ' up',
			'join' => array(
				'table' => 'shares s',
				'on' => array(
					'up.id' => 's.upload_id'
				),
				'type' => 'INNER'
			),
			'condition' => array(
				'up.upload_status' => 'public'
			),
			'callable_before' => 'socialApp',
		);
	}
	
	
	/**
	* The proposed materials Widget
	*/
	public function action_see_more_waiting()
	{
		return array(
			'condition' => array(
				'upload_status' => 'public',
				'upload_activ' => 0,
			),
			'sorting' => 'date_add'
		);
	}
	public function action_see_more_rand()
	{
		return array(
			'condition' => array(
				'upload_status' => 'public'
			),
			'sorting' => 'RAND()'
		);
	}
	public function action_see_more_main()
	{
		return array(
			'condition' => array(
				'upload_status' => 'public',
				'upload_activ' => 1,
			),
			'sorting' => 'date_add'
		);
	}
	public function action_see_more_top()
	{
		return array(
			'condition' => array(
				'upload_status' => 'public',
			),
			'sorting' => 'votes_opinion'
		);
	}
	public function action_see_more_tags()
	{
		return array(
			'condition' => array(
				'upload_status' => 'public'
			),
			'sorting' => 'votes_opinion',
		);
	}
	
	
	
	
	
	public function action_filter()
	{
		return array(
			'condition' => array(
				'upload_status' => 'public'
			),
			'callable_before' => 'filterFiles',
		);
	}
	
	
	/**
	* Conditions for user content
	*/
	
		
	public function action_user_files()
	{
		return array(
			'table' => IPS__FILES . ' up',
			'sorting' => 'date_add',
			'condition' => array(
				'upload_status' => 'public'
			)
		);
	}
	
		
	/**
	* Conditions for the page TOP
	*/
	public function action_top()
	{
		return array(
			'condition' => array(
				'upload_status' => 'public'
			),
			'premium' => false,
			'callable_before' => 'topFiles',
		);
	}
		/**
		* Conditions for the panel moderator
		*/

	public function action_mod_files()
	{
		
		return array(
			'table' => IPS__FILES . ' up',
			'join' => array(
				'table' => 'shares s',
				'on' => array(
					'up.id' => 's.upload_id'
				)
			),
			'columns' => 'up.*,s.share'
		);	
	}
	
	public function action_mod_comments()
	{
		
		return array(
			'table' => 'upload_comments c',
			'join' => array(
				'table' => 'users u',
				'on' => array(
					'u.id' => 'c.user_id'
				)
			),
			'comments' => true,
			'columns'	=> 'c.*, u.avatar',
			'premium' => false
		);
	}
		/**
		* Wyświetlanie pojedyńczego pliku poprzez
		* ajax podczas cenzurowania
		* Displaying a single file via ajax while censoring
		*/
	public function action_load_file()
	{
		return array(
			'limit' => 1,
		);
	}
		/**
		* Display category
		*/
	public function action_category()
	{
		return array(
			'sorting' => "date_add",
			'condition' => array(
				'upload_status' => 'public'
			),
		);
	}
	
	public function action_ips_embed()
	{
		return array(
			'pagination' => false
		);
	}
	/**
	* 
	*/
	public function action_items()
	{
		return array(
			'table' => IPS__FILES,
			'condition' => array(
				'upload_status' => 'public'
			),
			'count_records' => false
		);
	}
}
?>