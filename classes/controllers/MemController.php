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

class Mem_Controller extends Mem
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
		$this->action   = Sanitize::cleanSQL( get_input( 'mem_action', false ) );
		$this->category = Sanitize::cleanSQL( get_input( 'mem_id', 'all' ) );
		
		if ( $this->action == 'add' )
		{
			return $this->addGeneratorForm();
		}
		elseif ( $this->action == 'set' )
		{
			return ips_redirect( 'up/mem/' );
		}
		else
		{
			return $this->memGenerators();
		}
	}
}
