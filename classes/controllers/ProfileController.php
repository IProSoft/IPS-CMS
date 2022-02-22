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

class Profile_Controller
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct(){}
	
	public function route()
	{
		return require_once( ABS_PATH . '/inc/inc.profile.php' );
	}
}