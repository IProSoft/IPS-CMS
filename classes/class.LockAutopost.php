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
class Lock_Autopost
{
	public $type = 'autopost';
	/**
	 * Checking whether the selected material may be blocked or published on user timeline
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public function canBlock( &$row )
	{	
		/**
		 * Is the user is logged into Facebook and/or accepted the application
		 */
		$connected = $this->checkUserConnected();
		
		if ( $connected )
		{
			Cookie::set( 'ips-redir', $row['id'] . '/' . $row['seo_link'], 3600 );
			/**
			 * Is the user should still publish material or material which is watching.
			 */
			return $this->status( $this->canPostStatus( $connected, $row['id'] )) ;
		}
		
		return false;
	}
	
	public function status( array $status )
	{
		$status = array_merge( [
			'post' => false,
			'lock' => false
		], $status );
		
		add_filter( 'init_js_variables', function( $array ) use ( $status ) {
			return array_push_assoc( $array, 'ips_config', [
				'app_publish' => $status['post']
			] );
		});	

		return $status['lock'] ? $this : false;
	}
	/**
	 * To check whether the user has accepted the application.
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public function checkUserConnected()
	{
		/**
		 * Logged in user already connected by Facebook
		 */
		if ( Session::get( 'connect_facebook' ) == 1 )
		{
			$status = 'connected';
		}
		else
		{
			$status = Cookie::get( 'ips_connected_status', 'not_set' );
		
			if ( Config::getArray( 'apps_facebook_autopost_options', 'only_logged' ) && $status == 'not_logged' )
			{
				return false;
			}
		}

		return $status;
	}
	
	/**
	 * Checking whether the user has posted some materials, or exided limit
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public static function canPostStatus( $status, $file_id )
	{
		if ( $status != 'connected' )
		{
			return [
				'post' => false,
				'lock' => true
			];
		}
		
		/**
		 * Appointed time had passed for publication next post
		 */
		if ( Session::get( 'ips_posted_time', null ) <= time() )
		{

			$has_posted = Session::get( 'ips_posted', array() );
			
			/**
			 * How much can you publish
			 */
			$post_files = Config::getArray( 'apps_facebook_autopost_options', 'count' );
			
			/**
			 * Published materials
			 */
			if ( count( $has_posted ) < $post_files )
			{
				/**
				 * This material has been published
				 */
				if ( !in_array( $file_id, $has_posted ) )
				{
					return [
						'post' => true,
						'lock' => false
					];
				}
			}
		}
		return [
			'post' => false,
			'lock' => false
		];
	}
		
	/**
	 * Display template for the locked material
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public static function template( &$row )
	{
		$args = [
			'size' => ips_img_size( $row, 'large' ),
			'image' => false,
			'class' => '',
			'autopost_blocked' => __s( 'autopost_blocked', ABS_URL . 'connect/facebook/' )
		];
	
		if ( Config::getArray( 'apps_facebook_autopost_options', 'image' ) )
		{
			$args = array_merge( $args, [
				'image' => ips_img( $row, 'large' ),
				'class' => 'display-img' 
			]);
		}
		
		return Templates::getInc()->getTpl( 'item_lock_autopost.html', $args );
	}
}
?>