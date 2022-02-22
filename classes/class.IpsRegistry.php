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


class Ips_Registry
{
	
	/**
	 *
	 */
	private static $_instances = array();
	
	
	/**
	 * Gets an object from the registry
	 *
	 * @param string $object_id Id of the object
	 * @return object
	 */
	public static function get( $object_id, $params = null )
	{
		if ( !isset( self::$_instances[$object_id] ) || !is_object( self::$_instances[$object_id] ) )
		{
			self::setObject( $object_id, $params );
		}
		
		return self::$_instances[$object_id];
	}
	
	/**
	 * Set an object from the Ips_Registry
	 *
	 * @param string $object_id Id of the object to set
	 * @param mixed $object Object to set
	 *
	 * @return object Instance of the requested object
	 */
	public static function setObject( $object_id, $params )
	{
		self::$_instances[$object_id] = null;
		
		if ( !empty( $object_id ) && class_exists( $object_id ) )
		{
			self::$_instances[$object_id] = new $object_id( $params );
		}
		
		return self::$_instances[$object_id];
	}
}
?>