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
 
class Connect_Nk
{
	
	/**
	 * @var bool
	 */
	private $service = false;
	
	/**
	 * Get library instance
	 *
	 * @return string
	 */
	public function init()
	{
		if( has_value( 'error', $_GET) )
		{
			return false;
		}
		
		require_once( LIBS_PATH . '/nk-php-sdk/src/NK.php' );

		$this->service = new NKConnect( array(
			'permissions'		=> array( NKPermissions::BASIC_PROFILE, NKPermissions::EMAIL_PROFILE, NKPermissions::CREATE_SHOUTS ),
			'key'				=> Config::get('apps_nk_app', 'app_key'),
			'secret'			=> Config::get('apps_nk_app', 'app_secret'),
			'login_mode'		=> NKConnect::MODE_POPUP,
			'callback_url'		=> ABS_URL . 'connect/nk/true/'
		) );
		
		$this->service->handleCallback();
		
		return $this;
	}
	
	/**
	 * Get user info if connected
	 *
	 * @return array|bool
	 */
	public function getUser()
	{
		if ( $this->service->authenticated() )
		{
			$user = $this->service->getService()->me();
			
			return array( 
				'user' 		 => $user,
				'uid' 	 	 => $user->id(),
				'user_email' => $user->email()
			);
		}
		return false;
	}
	
	/**
	 * Return authenticate URL
	 *
	 * @return string
	 */
	public function getRedirect()
	{
		return $this->service->nkConnectLoginUri();
	}
	
	/**
	 * Return user data formatted
	 *
	 * @return array
	 */
	public function getUserData( $user )
	{
		$user_data = array();
			
			$user_data['user_name'] = $user['user']->name();
			
			$user_data = Connect::setUserNames( $user_data, $user_data['user_name'] );
			
			$user_data['login'] = Connect::setLogin( $user['user']->name(), $user['user']->id() );
			$user_data['uid'] = $user['user']->id();
			$user_data['email'] = $user['user']->email();
			$user_data['user_birth_date'] = date('Y-m-d', strtotime('-' . $user['user']->age() . ' years') ) ;
			
			$this->thumb = $user['user']->thumbnailUrl();
			
			if( Config::get('apps_get_thumbnail') == 1 )
			{		
				$user_data['thumbnail'] = $this->getThumb();
			}
			
		return $user_data;
	}
	 
	/**
	 * Return user thumb url
	 *
	 * @return array
	 */
	public function getThumb()
	{
		return $this->thumb;
	}
	
	/**
	 * Make finish functions on registered/logged user
	 *
	 * @return array
	 */
	public function connected( $uder_id )
	{
		
	}
}