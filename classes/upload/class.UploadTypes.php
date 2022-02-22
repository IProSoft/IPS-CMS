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

class Upload_Types
{
	/**
	 * Class initializer 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function init( $option, $type, $load_fonts = false )
	{
		$this->upload = new Upload_Extended;
		
		$this->opts = array_merge( Config::getArray( $option ), array(
			'margin' => $this->upload->getMargin( $type )
		) );
	
		if( $load_fonts )
		{
			$this->web_fonts = Ips_Registry::get( 'Web_Fonts' );
		
			$this->opts['font'] = array(
				'family' => $this->web_fonts->safeFontName( $this->opts['font'] ),
				'name' => $this->opts['font']
			);
			
			$this->opts['fonts'] = $this->web_fonts->getFontsUrls();
		}
		
		$this->opts['size'] = array(
			'medium' => Config::get( 'file_max_width' ) - 2 * $this->opts['margin']['border_width'] - 2 * $this->opts['margin']['side'],
			'large'	 => Upload_Extended::largeMaxWidth() - 2 * $this->opts['margin']['border_width'] - 2 * $this->opts['margin']['side'],
			'medium_canvas' => (int)Config::get( 'file_max_width' ),
			'large_canvas'	 => Upload_Extended::largeMaxWidth()
		);
		
		return $this->opts;
	}
	
	/**
	 * Get text options
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getOptions()
	{
		return $this->opts;
	}
}