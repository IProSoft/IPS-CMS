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

	
class Tools
{
	static function asyncLoad( $ajax_url, $get, $callback, $timeout = false )
	{
		$object = array(
			'class' => 'ips-async-load',
			'data-callback' => $callback,
			'data-href' => '/ajax/pinit/' . $ajax_url . '/',
			'data-get' => str_replace( '"', "'", json_encode( $get ) ),
		);
		
		if( $timeout )
		{
			$object['data-timeout'] = $timeout * 1000;
		}
		
		foreach( $object as $attribute => $value )
		{
			$object[$attribute] = $attribute . '="' . $value . '"';
		}
		
		return '<div ' . implode( ' ', $object ) . '><img width="48" height="48" src="/images/svg/spinner.svg"></div>';
	}
	
	static function doTjs( $file )
	{
		$content = file_get_contents( ABS_TPL_PATH . '/' . trim( urldecode( $file ), '/' ) . '.html' );
		
		if( strpos( $file, 'pin_list' ) !== false )
		{
			$content = preg_replace( '/\{loop="([^"]*)"\}/siu', '', $content );
			$content = preg_replace( '/\{\/loop\}/siu', '', $content );
			$content = preg_replace( '/\$value./siu', '$', $content );
		}
		
		$content = preg_replace( '/USER_ID/', 'user_id', $content );
		
		$content = preg_replace( '/\{loop="([^"]*)"\}/siu', '{{~it.\\1 :value:index}}', $content );
		
		$content = preg_replace( '/\{\/loop\}/siu', '{{~}}', $content );
		
		$content = preg_replace( '/\{if(?: condition){0,1}="([^$]*)\$([^"]*)"\}/siu', '{{? it.\\2}}', $content );
		
		$content = preg_replace( '/\{elseif(?: condition){0,1}="([^$]*)\$([^"]*)"\}/siu', '{{?? it.\\2}}', $content );
		
		$content = preg_replace( '/\{else\}/siu', '{{??}}', $content );
		
		$content = preg_replace( '/\{\/if\}/siu', '{{?}}', $content );
		
		$content = preg_replace( '/\{\$value\.([^}.]*)\}/siu', '{{=value.\\2}}', $content );
		
		$content = preg_replace( '/\{\$value\}/siu', '{{=value}}', $content );
		
		$content = preg_replace( '/\{\$([^}]*)\}/siu', '{{=it.\\1}}', $content );
		
		return Translate::getInstance()->translate( $content );
	}
}
