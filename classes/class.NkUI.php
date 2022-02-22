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

class Nk_UI
{
	/**
	 * Validate nk settings
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function isAppValid( $cached = true )
	{
		$valid_config = Config::get( 'cache_data_valid_nk_config' );
		
		if( $cached && is_string( $valid_config ) )
		{
			return Config::get('cache_data_valid_nk_config') == 'true' ? true : false ;
		}
		
		$valid = false;
		
		if ( Config::get('apps_nk_app', 'app_key') != '' && strlen( Config::get('apps_nk_app', 'app_key') ) > 10 )
		{
			if ( Config::get('apps_nk_app', 'app_secret') != '' && strlen( Config::get('apps_nk_app', 'app_secret') ) > 10 )
			{
				$valid = true;
			}
		}
		
		return Config::update( 'cache_data_valid_nk_config', ( $valid ? 'true' : 'false' ) ) && $valid;
	}
}