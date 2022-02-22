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

class Categories_Controller extends Categories
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
        return $this->loadCategory( IPS_ACTION_GET_ID );
    }
}


?>