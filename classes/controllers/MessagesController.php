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

class Messages_Controller extends Messages
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
		if ( USER_LOGGED )
		{
			return Templates::getInc()->getTpl( '/private_messages/private_messages.html', array(
				'messages' => $this->get( get_input( 'action' ) ),
				'send_to' => ( get_input( 'action' ) == 'write' && !empty( $_GET['additional'] ) ? $_GET['additional'] : false ) 
			) );
		}
		
		return ips_redirect( false, array(
			'alert' => 'user_only_logged'
		));
		
	}
}
?>