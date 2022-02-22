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

class User_Data
{
	static $_cached = array();
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function create( $user_id, $setting_key, $setting_value )
	{
		$insert_id = PD::getInstance()->insertUpdate( 'users_data', array(
			'user_id' => $user_id,
			'setting_key' => $setting_key,
			'setting_value' => self::compactSerialize( $setting_value ) 
		) );
		
		if ( !$insert_id )
		{
			$debug_backtrace = debug_backtrace();
			ips_log( ( isset( $debug_backtrace[0]['file'] ) ? $debug_backtrace[0]['file'] : '' ) . $user_id . ' - ' . $setting_key . ' - ' . $setting_value );
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
	public static function update( $user_id, $setting_key, $setting_value )
	{
		if ( !is_numeric( $user_id ) )
		{
			return false;
		}
		
		if ( empty( $setting_key ) )
		{
			return false;
		}
		
		if ( !self::exist( $user_id, $setting_key ) )
		{
			return self::create( $user_id, $setting_key, $setting_value );
		}
		
		return PD::getInstance()->update( 'users_data', array(
			'setting_value' => self::compactSerialize( $setting_value ) 
		), array(
			'user_id' => $user_id,
			'setting_key' => $setting_key 
		) );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function exist( $user_id, $setting_key )
	{
		return PD::getInstance()->cnt( 'users_data', array(
			'user_id' => $user_id,
			'setting_key' => $setting_key 
		) );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function delete( $user_id, $setting_key = false, $setting_value = false )
	{
		if ( $user_id != false )
		{
			$delete_by['user_id'] = $user_id;
		}
		
		if ( $setting_key != false )
		{
			$delete_by['setting_key'] = $setting_key;
		}
		
		if ( $setting_value != false )
		{
			$delete_by['setting_value'] = $setting_value;
		}
		if ( empty( $delete_by ) )
		{
			return false;
		}
		return PD::getInstance()->delete( 'users_data', $delete_by );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function get( $user_id, $setting_key = false )
	{
		$get_by = array(
			'user_id' => $user_id 
		);
		if ( $setting_key != false )
		{
			$get_by['setting_key'] = $setting_key;
		}
		
		$data = self::getCache( $user_id, $setting_key );
		
		if ( empty( $data ) )
		{
			$data = PD::getInstance()->select( 'users_data', $get_by, ( $setting_key == false ? null : 1 ) );
			if ( !empty( $data ) )
			{
				self::cache( $user_id, $setting_key, $data );
			}
		}
		
		if ( empty( $data ) || !is_array( $data ) )
		{
			return false;
		}
		
		if ( $setting_key != false )
		{
			if ( !isset( $data['setting_value'] ) )
			{
				return false;
			}
			/**
			 * Return only one value | check if is serialized
			 */
			if ( is_serialized( $data['setting_value'] ) )
			{
				$data['setting_value'] = unserialize( $data['setting_value'] );
			}
			
			return $data['setting_value'];
		}
		
		/**
		 * Return array of values | check if is serialized
		 */
		foreach ( $data as $key => $value )
		{
			if ( is_serialized( $value['setting_value'] ) )
			{
				$data[$key]['setting_value'] = unserialize( $value['setting_value'] );
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
	public static function getByValue( $setting_key, $setting_value, $limit_rows = 1 )
	{
		$data = PD::getInstance()->select( 'users_data', array(
			'setting_key' => $setting_key,
			'setting_value' => $setting_value 
		), $limit_rows, 'user_id' );
		
		if ( empty( $data ) )
		{
			return array();
		}
		
		return $limit_rows == 1 ? $data['user_id'] : $data;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function compactSerialize( $setting_value )
	{
		if ( is_array( $setting_value ) || is_object( $setting_value ) )
		{
			$setting_value = serialize( $setting_value );
		}
		
		return (string) $setting_value;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function cache( $user_id, $setting_key, $data )
	{
		if ( $setting_key == false )
		{
			self::$_cached[$user_id] = $data;
		}
		elseif ( !isset( self::$_cached[$user_id] ) )
		{
			self::$_cached[$user_id] = array(
				$setting_key => $data 
			);
		}
		else
		{
			self::$_cached[$user_id][$setting_key] = $data;
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getCache( $user_id, $setting_key )
	{
		
		if ( isset( self::$_cached[$user_id] ) )
		{
			if ( $setting_key == false )
			{
				return self::$_cached[$user_id];
			}
			elseif ( isset( self::$_cached[$user_id][$setting_key] ) )
			{
				return self::$_cached[$user_id][$setting_key];
			}
		}
		
		return false;
		
	}
}