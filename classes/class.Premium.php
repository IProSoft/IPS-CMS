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

class Premium
{
	
	private static $_instance = false;
	
	private static $_acces = false;
	
	private static $_expired_time = false;
	
	private static $_categories = null;
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		$this->setPremium();
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getInc()
	{
		if ( !self::$_instance )
		{
			self::$_instance = new Premium();
		}
		
		return self::$_instance;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setPremium()
	{
		if ( USER_LOGGED )
		{
			$premium_user = getUserInfo( USER_ID, true );
			
			if ( !empty( $premium_user ) && $premium_user['premium_from'] != null )
			{
				$time = date( "Y-m-d", ( strtotime( $premium_user['premium_from'] ) + ( 60 * 60 * 24 * $premium_user['days'] ) ) );
				
				if ( $time > date( "Y-m-d" ) )
				{
					self::$_acces = true;
				}
				else
				{
					self::$_expired_time = $time;
				}
			}
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function createPremum( $extend_premium, $user_id )
	{
		$row = PD::getInstance()->select( 'premium_users', array(
			'user_id' => $user_id 
		), 1 );
		
		if ( !empty( $row ) )
		{
			$user = PD::getInstance()->update( 'premium_users', array(
				'days' => $extend_premium + $row['days'] 
			), array( 
				'user_id' => $user_id
			) );
		}
		else
		{
			$user = PD::getInstance()->insert( 'premium_users', array(
				'user_id' => $user_id,
				'premium_from' => date( "Y-m-d" ),
				'days' => $extend_premium 
			) );
		}
		
		return $user ? true : false;
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function premiumCategories()
	{
		$_categories = array();
		
		$categories = Categories::getCategories();
		
		if ( !empty( $categories ) )
		{
			foreach ( $categories as $cat )
			{
				if ( $cat['only_premium'] == 1 )
				{
					$_categories[] = $cat['id_category'];
				}
			}
		}
		
		return $_categories;
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function premiumService( $config = false )
	{
		if ( $config && !Config::getArray( 'services_premium_options', $config ) )
		{
			return true;
		}
		
		return self::$_acces;
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function premiumRedirect( $redir )
	{
		if ( self::$_expired_time )
		{
			ips_message( array(
				'alert' =>  __s( 'premium_access_time', self::$_expired_time )
			) );
		}
		else
		{
			ips_message( array(
				'alert' =>  'premium_access'
			) );
		}
		
		Cookie::set( 'ips-redir', $redir, 3600 );
		
		ips_redirect( ( USER_LOGGED ? 'premium/' : 'login/' )  );
	}
}
?>