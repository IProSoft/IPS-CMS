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

class Upload_Helper
{
	/**
	 * Generate dropzone html
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function dropzone( $up_type, $up_preview = false )
	{
		return Templates::getInc()->getTpl( '/upload/up_dropzone.html', array(
			'up_preview' => $up_preview,
			'up_type' 	 => $up_type,
			'url_text'	 => __( ( $up_type == 'video' ? 'add_video_url' : 'add_file_url' ) ),
		) );
	}
}