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

class Cookie
{
	/**
	 * Permanent cookie period
	 */
	const PERMANENT = '+1 year';

	/**
	 * The domain that the cookie is available to
	 *
	 * @var string
	 */
	private static $domain = null;

	/**
	 * The path on the server in which the cookie will be available on.
	 *
	 * @var string
	 */
	private static $path = '/';

	/**
	 * @var bool
	 */
	private static $secure = null;

	/**
	 * Prefix for all cookies
	 *
	 * @var string
	 */
	private static $prefix = '';

	private function __construct(){}

	/**
	 * Get info about domain setting
	 *
	 * @return string
	 */
	public static function getDomain()
	{
		if ( self::$domain === null )
		{
			self::$domain = parse_url( ABS_URL, PHP_URL_HOST );
		}
		
		return self::$domain;
	}


	/**
	 * Get info about prefix setting
	 *
	 * @return string
	 */
	public static function getPrefix()
	{
		return self::$prefix;
	}

	/**
	 * Get info about path setting
	 *
	 * @return string
	 */
	public static function getPath()
	{
		return self::$path;
	}

	/**
	 * Get info about secure setting
	 *
	 * @return boolean
	 */
	public static function isSecure()
	{
		if( self::$secure === null )
		{
			self::$secure = ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' );
		}
		return self::$secure;
	}

	/**
	 * Get realname for cookie
	 *
	 * @param string $name
	 * @return string
	 */
	public static function realName( $name )
	{
		return self::$prefix . trim( $name );
	}
	
	/**
	 * Get expire time for cookie
	 *
	 * @param string $name
	 * @return string
	 */
	public static function getExpire( $expire )
	{
		if ( $expire === true )
		{
			return self::PERMANENT;
		}
		
		if ( is_numeric( $expire ) )
		{
			return time() + $expire;
		}
		elseif ( is_string( $expire ) && $expire ) 
		{
			return strtotime( $expire );
		}
		
		return 0;
	}
	/**
	 * Set a cookie
	 *
	 * @param string $name The name of the cookie.
	 * @param string $value The value of the cookie. This value is stored on the clients computer; do not store sensitive information.
	 *                      Assuming the name is 'cookiename', this value is retrieved through $_COOKIE['cookiename'] or Cookie::get('cookiename')
	 * @param bool|string|int $expire The time the cookie expires. Possible values:<br>
	 *                                <strong>true</strong> - set cookie permanently<br>
	 *                                <strong>false</strong> - set cookie for current session only<br>
	 *                                <strong>int</strong> - The number of seconds to add to the current time<br>
	 *                                <strong>string</strong> - Relative DateTime Formats (ex: '+3 days', '+1 week' etc.)<br>
	 *                                         See http://es.php.net/manual/en/datetime.formats.relative.php
	 * @return boolean If output exists prior to calling this function, setcookie() will fail and return FALSE.
	 *                 If setcookie() successfully runs, it will return TRUE. This does not indicate whether the user accepted the cookie.
	 */
	public static function set( $name, $value, $expire = self::PERMANENT )
	{
		
		/* Unset cookie */
		if ( empty( $value ) )
		{
			$expire = '-1 day';
		}
		
		$name = self::realName( $name );
		
		$value = (string)$value;

		$expire = self::getExpire( $expire );

		if ( time() > $expire )
		{
			unset( $_COOKIE[$name] );
		}
		else
		{
			$_COOKIE[ $name ] = $value;
		}
		
		return setcookie( $name, $value, $expire, self::getPath(), self::getDomain(), self::isSecure() );
	}

	/**
	 * Clear (unset) a cookie
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function clear( $name )
	{
		return self::set( $name, '', '-1 day' );
	}

	/**
	 * Get a cookie
	 *
	 * @param string $name
	 * @param string $default
	 * @return mixed
	 */
	public static function get( $name, $default = false )
	{
		$name = self::realName( $name );
		return isset( $_COOKIE[$name] ) ? $_COOKIE[$name] : $default;
	}
	/**
	 * Returns true if there is a cookie with this name.
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function exists( $name )
	{
		return isset( $_COOKIE[ self::realName( $name ) ] );
	}

	/**
	 * Returns true if there no cookie with this name or it's empty, or 0,
	 * or a few other things. Check http://php.net/empty for a full list.
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function isEmpty( $name )
	{
		return empty( $_COOKIE[ self::realName( $name ) ] );
	}

	/**
	 * Clear all session and cookie vars
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function destroy( $all = false )
	{
		Session::destroy();
		/**
		* Save cookies value
		*/
		$remember = array( 
			'ips_connected_status'=> '',
			'ips_popup'			  => '', 
			'ips_cookie_policy'   => '',
			'ssid_global'		  => '',
			'ssid_autologin'	  => ''
		);
		
		foreach( $_COOKIE as $cookie => $val )
		{
			if( in_array( $cookie, array_keys( $remember ) ) )
			{
				$remember[$cookie] = $val;
			}
		}
		
		if ( isset( $_SERVER['HTTP_COOKIE'] ) )
		{
            $cookies = explode( ';', $_SERVER['HTTP_COOKIE'] );
           
			foreach( $cookies as $cookie ) 
			{
                list( $name )  = explode( '=', $cookie );
                Cookie::clear( $name );
            }
			
            Cookie::clear( 'PHPSESSID' );
        }
		
		Cookie::clear ( session_id() );
		
		$_COOKIE = array();
		
		if( !$all )
		{
			foreach( $remember as $cookie => $value )
			{
				if( !empty( $value ) )
				{
					Cookie::set( $cookie, $value );
				}
			}
		}
	}
}
