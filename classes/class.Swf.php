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
class Swf
{
	/**
	 * Class constructor
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		
	}
	/**
	 * Class constructor
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function get( $url, $settings )
	{
		return Templates::getInc()->getTpl( 'js_flash_player.html', array(
			'id' => str_random( 8 ),
			'file' => IPS_VIDEO_URL . '/' . $url,
			'file_name' => basename( $url ),
			'height' => $settings['height'],
			'width' => $settings['width'] 
		) );
	}
}
