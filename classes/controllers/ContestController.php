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


class Contest_Controller extends Contest
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
		if ( IPS_ACTION_GET_ID )
		{
			return $this->showContest( IPS_ACTION_GET_ID );
		}
		
		return $this->contestsList();
		
	}
}

