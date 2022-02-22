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

class Login_Controller
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
		$action = get_input( 'sub_action', 'login' );
		
		$login = new Login();
		
		if( $action && method_exists( $login, 'route_' . $action ) )
		{
			return $login->{'route_' . $action}();
		}
		
		return ips_redirect( false, array(
			'info' => 'user_error_logged'
		) );
	}
}