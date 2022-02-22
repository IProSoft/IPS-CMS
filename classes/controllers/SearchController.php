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

class Search_Controller extends Search
{
	
	public function route()
	{
		$search_phrase = get_input( 'search_phrase', false );
		
		if ( $search_phrase !== false )
		{
			$url = $this->rewriteSearch( Sanitize::cleanXss( $search_phrase ) );
			
			return ips_redirect( $url );
		}
		
		$search_phrase = get_input( 'phrase', false );
		
		if ( $search_phrase !== false )
		{
			return $this->startSearch( Sanitize::cleanXss( $search_phrase ) );
		}
		
		return $this->displaySearchForm();
	}
}
?>