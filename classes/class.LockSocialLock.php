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

class Lock_Social_Lock
{
	public $type = 'social_lock';
	/**
	 * Checking whether the selected material may be blocked or published on user timeline
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public function canBlock( &$row )
	{	
		return $this->status( $this->canBlockStatus( $row['id'] ) ) ;
	}
	
	public function status( $status )
	{
		if( $status )
		{
			add_filter( 'init_js_files', function( $array ){
				return add_static_file( $array, array(
					'js/social_lock.js'
				)  );
			}, 10 );
		}

		return $status ? $this : false;
	}

	/**
	 * Checking whether the user has posted some materials, or exided limit
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public static function canBlockStatus( $file_id )
	{
		/**
		 * Appointed time had passed for block next post
		 */
		if ( Session::get( 'ips_posted_time', null ) <= time() )
		{
			$has_liked = Session::get( 'ips_social_lock', array() );
			
			/**
			 * How much can you block
			 */
			$post_files = Config::getArray( 'apps_social_lock_options', 'count' );
			
			/**
			 * Blocked materials
			 */
			if ( count( $has_liked ) < $post_files )
			{
				/**
				 * This material has been Blocked
				 */
				if ( !in_array( $file_id, $has_liked ) )
				{
					return true;
				}
			}
		}
		
		return false;
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
			'url' => seoLink( $row['id'], $row['seo_link'] ),
			'size' => ips_img_size( $row, 'large' )
		];
	
		if ( Config::getArray( 'apps_social_lock_options', 'image' ) )
		{
			$args = array_merge( $args, [
				'image' => ips_img( $row, 'large' ),
				'class' => 'display-img' 
			]);
		}
		
		return Templates::getInc()->getTpl( 'item_lock_social.html', $args );
	}
}
?>