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


class Connect_Controller
{

	/**
	 * Get connect base file
	 *
	 * @param null
	 * 
	 * @return mixed
	 */
	public function route()
	{
		return require_once( ABS_PATH . '/inc/inc.connect.php' );
	}
}
?>
