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

class Minify_Controller
{
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route()
	{
		if( $files = get_input( 'files' ) )
		{
			$content = curlIPS( ABS_URL . '/libs/Minify/index.php?f=' . $files, [
				'timeout' => 10
			]);
			
			File::put( ABS_CSS_JS_CACHE_PATH . '/min-' . get_input( 'hash' ), $content );
			
			return ips_redirect(  'cache/minify/min-' . get_input( 'hash' ) );
		}
	}
}
?>