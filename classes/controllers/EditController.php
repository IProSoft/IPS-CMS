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

class Edit_Controller
{
	public function route()
	{
		$edit = new Edit();

		if ( $row = $edit->isEditable( IPS_ACTION_GET_ID ) )
		{
			return $edit->redirect( $row );
		}
		
		return ips_redirect( 'index.html', 'user_permissions' );
	}
}

