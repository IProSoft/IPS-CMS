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

class Web_Fonts
{
	/**
	 * The class constructor
	 *
	 * @param null
	 * 
	 * @return null
	 */
	public function __construct()
	{
		$this->all_fonts = $this->getFonts();
	}
	/**
	 * Retrieve a list of available fonts GoogleFonts
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getFonts()
	{
		$fonts = Ips_Cache::getDBCache( 'web_fonts_list', 'upload' );
		
		if ( empty( $fonts ) )
		{
			$fonts = $this->setAvailableFonts();
		}
		
		$fonts_object = json_decode( $fonts );
		
		if ( $fonts_object && is_object( $fonts_object ) )
		{
			if ( $fonts_object->items && is_array( $fonts_object->items ) )
			{
				return $fonts_object->items;
			}
		}

		return array();
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getFontsUrls()
	{
		if( $cache = Ips_Cache::getDBCache( 'cache_fonts_options', 'upload' ) )
		{
			return $cache;
		}
	
		$installed = array_keys( Config::getArray( 'web_fonts_config', 'installed' ) );
		
		$fonts = [];
		
		if( !is_array( $installed ) )
		{
			$installed = array();
		}
		
		$options = [];
		
		foreach ( $this->all_fonts as $font )
		{
			if( in_array( $this->safeFontName( $font->family ), $installed ) )
			{
				$font_name =  $this->safeFontName( $font->family );
				
				$options[$font_name] = array(
					'family' => $font->family,
					'url' => $this->getFontUrl( $font )
				);
			}
		}
		
		Ips_Cache::storeDBCache( 'cache_fonts_options', serialize( $options ), 'upload' );
		
		return $options;
	}
	
	/**
	 * Retrieve a font ttf file
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getFontTtf( $font_name )
	{
		$font_path = CACHE_PATH . '/cache_fonts/' . $font_name . '.ttf';
		
		if( file_exists( $font_path ) )
		{
			return $font_path;
		}
		
		$font_name = $this->realFontName( $font_name );
		
		foreach ( $this->all_fonts as $font )
		{
			if( $font->family == $font_name  )
			{
				$url = isset( $font->files->regular ) ? $font->files->regular : current( $font->files );
				
				curlIPS( $url, array(
					'timeout' => 10, 
					'file' => $font_path, 
					'refferer' => 'http://google.pl'
				) );
				
				return $font_path;
			}
		}

		return false;
	}
	/**
	 * Find font URL
	 * @param 
	 * 
	 * @return 
	 */
	public function urlWebFont( $font_name )
	{
		$font_name = $this->realFontName( $font_name );
		
		foreach ( $this->all_fonts as $font )
		{ 
			if( $font->family == $font_name  )
			{
				return array( 
					'url' => $this->getFontUrl( $font )
				);
			}
		}

		return '';
	}
	
	/**
	 * Get font URL
	 * @param 
	 * 
	 * @return 
	 */
	public function getFontUrl( $font )
	{
		$url       = 'http://fonts.googleapis.com/css?family=';
		$subsets = array();

		if ( isset( $font->subsets ) && !empty( $font->subsets ) )
		{
			foreach ( $font->subsets as $subset )
			{
				$subsets[] = $subset;
			}
		}

		$url .= str_replace( '-', '+', $font->family ) . ( isset( $font->variants ) ? ":" . implode( ",", $font->variants ) : '' );
		
		if ( !empty( $subsets ) )
		{
			$url .= "&subset=" . implode( ",", array_unique( $subsets ) );
		}
		
		return $url;
	}
	
	/**
	 * Retrieve a list of available fonts GoogleFonts from server
	 *
	 * @param 
	 * 
	 * @return 
	 */
	private function setAvailableFonts()
	{
		$fonts = json_encode( json_decode( curlIPS( 'http://api.iprosoft.pro/webfonts/' ) ) );
		
		Ips_Cache::storeDBCache( 'web_fonts_list', $fonts , 'upload' );
		
		return $fonts;
	}
	
	/**
	 * Swap space on the dash in the names of fonts to use in the form elements
	 *
	 * @param string $name - font name
	 * 
	 * @return string
	 */
	public function safeFontName( $name )
	{
		return ( str_replace( ' ', '-', trim( $name ) ) );
	}
	/**
	 * Swap dash on the space in the names of fonts
	 *
	 * @param string $name - font name
	 * 
	 * @return string
	 */
	public function realFontName( $name )
	{
		return ( str_replace( '-', ' ', trim( $name ) ) );
	}
}