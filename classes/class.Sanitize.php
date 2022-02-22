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

class Sanitize
{
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function nl2br2( $ciag, $replace = "<br />" )
	{
		return str_replace( array(
			"\r\n",
			"\r",
			"\n" 
		), $replace, $ciag );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function onlyAlphanumeric( $data, $replace = '' )
	{
		return strip_tags( preg_replace( "/[^0-9a-zA-ZĘÓĄŚŁŻŹĆŃęóąśłżźćń\s]/si", $replace, $data ) );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function cleanSQL( $data )
	{
		return strip_tags( preg_replace( "/[^0-9a-zA-ZĘÓĄŚŁŻŹĆŃęóąśłżźćń_@\?()\[\]*&;:,\.%$#\s\n\-\/]/si", "", $data ) );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function cleanXss( $str, $return_clear = false )
	{
		if ( is_array( $str ) )
		{
			while ( list( $key ) = each( $str ) )
			{
				$str[$key] = self::cleanXss( $str[$key] );
			} //list( $key ) = each( $str )
			
			return $str;
		} //is_array( $str )
		
		//$str = rawurldecode($str);
		//return filter_var( $str, FILTER_SANITIZE_SPECIAL_CHARS );
		
		$str    = self::setEncoding( $str, 'UTF-8' );
		$filtry = array(
			'#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
			'/%0[0-8bcef]/',
			'#</*\w+:\w[^>]*>#i',
			'/%1[0-9a-f]/',
			'/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S',
			'#(alert|javascript|charset|window|document\.|\.cookie|script|xss|applet|meta|xml|blink|link|script|iframe|frame|frameset|base64\s*,)$#si' 
		);
		do
		{
			$str = preg_replace( $filtry, '', $str, -1, $count );
		} while ( $count );
		if ( $return_clear )
		{
			return $str;
		} //$return_clear
		return stripslashes( $str );
		//return htmlentities($str, ENT_QUOTES | ENT_IGNORE, "UTF-8");
	}
	
	/**
	 * Removes everything except alphanumeric characters
	 * @param mixed $data
	 * @return string
	 */
	public static function alphaString( $data )
	{
		return preg_replace( '/[^\w]/', '', $data );
	}
	/**
	 * Removes unwanted characters with tags
	 * @param mixed $data
	 * @return string
	 */
	public static function tag( $tag )
	{
		return preg_replace( "/[^\p{L}0-9\s\,\-\=]/u", "", preg_replace( '!\s+!', ' ', $tag ) );
	}
	
	/**
	 * Filters the data according to the selected method
	 * Associated with PHP filter_var http://php.net/manual/en/filter.filters.sanitize.php
	 * @param mixed $data
	 * @param string $filter
	 * @return mixed
	 */
	public static function sanitizePHP( $data, $filter = 'email' )
	{
		$filters = array(
			'email' => FILTER_SANITIZE_EMAIL,
			'encoded' => FILTER_SANITIZE_ENCODED,
			'magic_quotes' => FILTER_SANITIZE_MAGIC_QUOTES,
			'float' => FILTER_SANITIZE_NUMBER_FLOAT,
			'int' => FILTER_SANITIZE_NUMBER_INT,
			'special_chars' => FILTER_SANITIZE_SPECIAL_CHARS,
			'string' => FILTER_SANITIZE_STRING,
			'url' => FILTER_SANITIZE_URL 
		);
		
		if ( !array_key_exists( $filter, $filters ) )
		{
			return false;
		} //!array_key_exists( $filter, $filters )
		
		return filter_var( $data, $filters[$filter] );
	}
	
	/**
	 * Validation of the data according to the selected method
	 * Associated with PHP filter_var http://www.php.net/manual/en/filter.filters.validate.php
	 * @param mixed $data
	 * @param string $filtr
	 * @return mixed
	 */
	public static function validatePHP( $data, $filter = 'email' )
	{
		$filters = array(
			'email' => FILTER_VALIDATE_EMAIL,
			'float' => FILTER_VALIDATE_FLOAT,
			'int' => FILTER_VALIDATE_INT,
			'ip' => FILTER_VALIDATE_IP,
			'url' => FILTER_VALIDATE_URL 
		);
		
		if ( !array_key_exists( $filter, $filters ) )
		{
			return false;
		} //!array_key_exists( $filter, $filters )
		
		return filter_var( $data, $filters[$filter] );
	}
	
	/**
	 * Conversion for the selected character encoding. Source coding is detected automatically
	 */
	public static function setEncoding( $string, $set_encoding )
	{
		if ( version_compare( PHP_VERSION, '4.0.6', '>' ) )
		{
			$mb_list = mb_list_encodings();
			$current = mb_detect_encoding( $string, $mb_list );
			
			if ( $current != $set_encoding )
			{
				$string = mb_convert_encoding( $string, $set_encoding, $current );
			} //$current != $set_encoding
		} 
		//version_compare( PHP_VERSION, '4.0.6', '>' )
		return $string;
	}
	/**
	 * Clean the entire array specified as an argument
	 * Depracated - $_POST i $_GET
	 * @param array $array : board
	 * @param array $type : Selected functions given in the form of an array
	 * @return Array
	 */
	public static function clearGlob( $array, $functions )
	{
		if ( is_array( $array ) )
		{
			while ( list( $key ) = each( $array ) )
			{
				$array[$key] = self::clearGlob( $array[$key], $functions );
			} //list( $key ) = each( $array )
			
			return $array;
		} //is_array( $array )
		if ( !is_array( $functions ) )
		{
			$functions = array(
				$functions 
			);
		} //!is_array( $functions )
		foreach ( $functions as $key )
		{
			$array = call_user_func( array(
				'self',
				$key 
			), $array );
			
		} //$functions as $key
		return $array;
	}
}