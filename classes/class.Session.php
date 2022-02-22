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

class Session
{
    private static $onetime_values;
	
	const ONETIME_KEY = '__onetime_values';

    /**
     * Session::Session's initializer
     * This function call session_start() if not initialized
     *
     * @param bool $regenerate true: call self::regenerateId();
     */
    public static function start( $regenerate = false )
    {
        if ( session_status() === PHP_SESSION_NONE )
		{
            session_start();
        }

        if ( $regenerate )
		{
            self::regenerateId();
        }

        self::$onetime_values = isset( $_SESSION[ self::ONETIME_KEY ] ) ? $_SESSION[ self::ONETIME_KEY ] : array();
    }

    /**
     * Set onetime value
     *
     * @param mixed $key key name
     * @param mixed $value value
     */
    public static function setFlash($value)
    {
        $_SESSION[self::ONETIME_KEY][] = $value;
    }
    /**
     * Get onetime value
     *
     * @param mixed $key key name
     * @return mixed value or null if not set
     */
    public static function getFlash()
    {
		$_SESSION[ self::ONETIME_KEY ] = array();
		return !empty(self::$onetime_values) ? implode( "\n", self::$onetime_values ) : '';
    }

	
	/**
	 * Set a session variable
	 *
	 * @param string $key The name of the variable.
	 * @param string $value The value of the session variable
	 * @return boolean
	 */
	public static function set( $key, $value = 'empty' )
	{
		
		/* Unset session */
		if ( $value === 'empty' )
		{
			return self::clear( $key );
		}
		
		$_SESSION[ $key ] = $value;
		
		return $value;
	}
	
	/**
	 * Set a session variablein array session
	 *
	 * @param string $key The name of the variable.
	 * @param string $value The value of the session variable
	 * @return boolean
	 */
	public static function setChild( $key, $child, $value )
	{
		if ( !isset( $_SESSION[ $key ] ) )
		{
			self::set( $key, array() );
		}
		
		if( $child === null )
		{
			return $_SESSION[ $key ][] = $value;
		}
		
		$_SESSION[ $key ][ $child ] = $value;
		
		return $value;
	}
	/**
	 * Clear (unset) a session variable
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function clear( $key, $value = null )
	{
		if( isset( $_SESSION[$key] ) )
		{
			$value = $_SESSION[$key];
			unset( $_SESSION[$key] );
		}
		return $value;
	}

	/**
	 * Get a session variable
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public static function get( $key, $default = false )
	{
		return isset( $_SESSION[$key] ) ? $_SESSION[$key] : $default;
	}
	
	/**
	 * Get a session variable and forget it
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public static function pull( $key, $default = false )
	{
		if( isset( $_SESSION[$key] ) )
		{
			$default = $_SESSION[$key];
			unset( $_SESSION[$key] );
		}
		return $default;
	}
	/**
	 * Get a session variable with subkey
	 *
	 * @param string $key
	 * @param string $child
	 * @param string $default
	 * @return mixed
	 */
	public static function getChild( $key, $child, $default = false )
    {
        return isset( $_SESSION[$key][$child] ) ? $_SESSION[$key][$child] : $default;
    }
	
	/**
	 * Get a session variable from array and forget it
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public static function pullChild( $key, $child, $default = false )
	{
		if( isset( $_SESSION[$key][$child] ) )
		{
			$default = $_SESSION[$key][$child];
			unset( $_SESSION[$key][$child] );
		}
		return $default;
	}
	/**
	 * Returns true if there is a session variable with this name.
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function has( $key )
	{
		return isset( $_SESSION[ $key ] );
	}
	
	/**
	 * Merge current session with array of variables
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function push( array $values )
	{
		$_SESSION = array_merge( $_SESSION, $values );
		
		return $_SESSION;
	}
	/**
	 * Returns true if there no session with this name or it's empty, or 0,
	 * or a few other things. Check http://php.net/empty for a full list.
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function getNonEmpty( $key, $child = false, $default = false )
	{
		if( isset( $_SESSION[ $key ] ) )
		{
			if( $child )
			{
				if( isset( $_SESSION[ $key ][ $child ] ) && !empty( $_SESSION[ $key ][ $child ] ) )
				{
					return $_SESSION[ $key ][ $child ];
				}
			}
			elseif( !empty( $_SESSION[ $key ] ) )
			{
				return $_SESSION[ $key ];
			}
		}
		
		return $default;
	}
    /**
     * Alias session_regenerate_id($delete_old)
     *
     * @param bool $delete_old [=true]
     */
    public static function regenerateId($delete_old = true)
    {
        session_regenerate_id($delete_old);
    }
	
    /**
     * Get all $_SESSION vars
     *
     * @param none
     */
    public static function all()
    {
        return $_SESSION;
    }
	/**
	 * Clear all session
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function destroy( $all =false )
	{
		/**
		* Save cookies value
		*/
		$remember = array( 
			'ips_posted'=> ''
		);
		
		foreach( $_SESSION as $sess => $val )
		{
			if( in_array( $sess, array_keys( $remember ) ) )
			{
				$remember[$sess] = $val;
			}
		}
		
		session_unset();
		session_destroy();
		session_write_close();
		session_start();
		$_SESSION = array();
		
		if( !$all )
		{
			foreach( $remember as $sess => $value )
			{
				if( !empty( $value ) )
				{
					Session::set( $sess, $value );
				}
			}
		}
	}
}
