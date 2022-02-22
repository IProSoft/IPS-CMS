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

class Upload_Meta
{
	static $_cached = array();
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function create( $upload_id, $meta_key, $meta_value )
	{
		$insert_id = PD::getInstance()->insertUpdate( 'upload_meta', array(
			'upload_id' => $upload_id,
			'meta_key' => $meta_key,
			'meta_value' => self::compactSerialize( $meta_value ) 
		) );
		
		if ( !$insert_id )
		{
			$debug_backtrace = debug_backtrace();
			ips_log( ( isset( $debug_backtrace[0]['file'] ) ? $debug_backtrace[0]['file'] : '' ) . $upload_id . ' - ' . $meta_key . ' - ' . $meta_value );
		}
		
		return $insert_id;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function update( $upload_id, $meta_key, $meta_value )
	{
		if ( !is_numeric( $upload_id ) || empty( $meta_key ) )
		{
			return false;
		}
		
		if ( !self::exist( $upload_id, $meta_key ) )
		{
			return self::create( $upload_id, $meta_key, $meta_value );
		}
		
		return PD::getInstance()->update( 'upload_meta', array(
			'meta_value' => self::compactSerialize( $meta_value ) 
		), array(
			'upload_id' => $upload_id,
			'meta_key' => $meta_key 
		) );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function exist( $upload_id, $meta_key )
	{
		return PD::getInstance()->cnt( 'upload_meta', array(
			'upload_id' => $upload_id,
			'meta_key' => $meta_key 
		) );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function delete( $upload_id, $meta_key = false, $meta_value = false )
	{
		if ( $upload_id != false )
		{
			$delete_by['upload_id'] = $upload_id;
		}
		
		if ( $meta_key != false )
		{
			$delete_by['meta_key'] = $meta_key;
		}
		
		if ( $meta_value != false )
		{
			$delete_by['meta_value'] = $meta_value;
		}
		if ( empty( $delete_by ) )
		{
			return false;
		}
		return PD::getInstance()->delete( 'upload_meta', $delete_by );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function get( $upload_id, $meta_key = false )
	{
		$get_by = array(
			'upload_id' => $upload_id 
		);
		
		if ( $meta_key != false )
		{
			$get_by['meta_key'] = $meta_key;
		}
		
		$data = self::getCache( $upload_id, $meta_key );
		
		if ( empty( $data ) || !is_array( $data ) )
		{
			return !$meta_key ? array() : false;
		}
		
		if ( $meta_key != false )
		{
			if ( !isset( $data['meta_value'] ) )
			{
				return false;
			}
			/**
			 * Return only one value | check if is serialized
			 */
			if ( is_serialized( $data['meta_value'] ) )
			{
				$data['meta_value'] = unserialize( $data['meta_value'] );
			}
			
			return $data['meta_value'];
		}
		
		/**
		 * Return array of values | check if is serialized
		 */
		foreach ( $data as $key => $value )
		{
			if ( is_serialized( $value['meta_value'] ) )
			{
				$data[$key]['meta_value'] = unserialize( $value['meta_value'] );
			}
		}
		
		/**
		 *
		 */
		return $data;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getByValue( $meta_key, $meta_value, $limit_rows = 1 )
	{
		$data = PD::getInstance()->select( 'upload_meta', array(
			'meta_key' => $meta_key,
			'meta_value' => $meta_value 
		), $limit_rows, 'upload_id' );
		
		if ( empty( $data ) )
		{
			return false;
		}
		
		return $limit_rows == 1 ? $data['upload_id'] : $data;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function compactSerialize( $meta_value )
	{
		if ( is_array( $meta_value ) || is_object( $meta_value ) )
		{
			$meta_value = serialize( $meta_value );
		}
		
		return (string) $meta_value;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function cache( $upload_id, $meta_key, $data )
	{
		if ( $meta_key == false )
		{
			self::$_cached[$upload_id] = $data;
		}
		elseif ( !isset( self::$_cached[$upload_id] ) )
		{
			self::$_cached[$upload_id] = array(
				$meta_key => $data 
			);
		}
		else
		{
			self::$_cached[$upload_id][$meta_key] = $data;
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getCache( $upload_id, $meta_key )
	{
		
		if ( isset( self::$_cached[$upload_id] ) )
		{
			if ( $meta_key == false )
			{
				return self::$_cached[$upload_id];
			}
			elseif ( isset( self::$_cached[$upload_id][$meta_key] ) )
			{
				return self::$_cached[$upload_id][$meta_key];
			}
			
			return false;
		}
		
		$data = PD::getInstance()->select( 'upload_meta', array(
			'upload_id' => $upload_id
		) );
			
		if ( !empty( $data ) )
		{
			foreach( $data as $k => $val )
			{
				self::cache( $upload_id, $val['meta_key'], $val );
			}
			
			return self::getCache( $upload_id, $meta_key );
		}
		
		return false;
		
	}
}