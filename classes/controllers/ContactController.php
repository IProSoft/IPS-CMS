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


class Contact_Controller
{
	public function route()
	{
		return '<div class="simple-page">' . Templates::getInc()->getTpl( 'contact.html' ) . '</div>';
	}
}

?>
