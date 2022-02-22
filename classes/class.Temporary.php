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

class Temporary
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct(){}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function get( array $data, $exclude_ip = false )
	{
		if( !$exclude_ip )
		{
			$data['ip'] = user_ip();
		}
		
		$temporary = PD::getInstance()->select( 'temporary', $data, 1 );
		
		if( !$temporary )
		{
			return array_merge( $data, array(
				'time' => false
			) );
		}
		
		return $temporary;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function set( $current_row )
	{
		if( isset( $current_row['id'] ) )
		{
			return PD::getInstance()->update('temporary', array_merge( $current_row, array(
				'time' => time()
			) ), array(
				'id' => $current_row['id']
			) );
		}
		
		return PD::getInstance()->insert( 'temporary', array_merge( $current_row, array(
			'ip' => user_ip(),
			'time' => time()
		) ) );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function delete( $current_row )
	{
		if( isset( $current_row['id'] ) )
		{
			return PD::getInstance()->delete('temporary', array(
				'id' => $current_row['id']
			) );
		}
		
	}
}