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

class Moderator_Controller
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	
	public function route()
	{
		if ( !USER_MOD )
		{
			return ips_redirect( false, 'user_only_logged' );
		}
		
		remove_hook( array(
			'before_content',
			'after_content',
			'after_footer'
		) );
		
		return require_once( ABS_PATH . '/inc/inc.moderator.php' );
	}
}