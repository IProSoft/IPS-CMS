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
 
class Connect_Facebook
{
	
	/**
	 * @var bool
	 */
	private $session = false;
	
	/**
	 * Get library instance
	 *
	 * @return string
	 */
	public function init()
	{
		$this->session = Facebook_UI::getApp();
		$this->token = Session::get( 'access_token' );
		
		return $this;
	}
	
	/**
	 * Get user info if connected
	 *
	 * @return array|bool
	 */
	public function getUser()
	{
		if( !is_object( $this->session ) )
		{
			return false;
		}
		
		$helper = $this->session->getRedirectLoginHelper();

		try {
			$this->token = $this->getAccessToken( $helper->getAccessToken() );
			
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			return !ips_log( 
				'Graph returned an error: ' . $e->getMessage(), 
			'logs/connect-facebook.log' );
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			return !ips_log( 
				'Facebook SDK returned an error: ' . $e->getMessage(), 
			'logs/connect-facebook.log' );
		}
		
		
		if (! isset($this->token)) {
			if ($helper->getError()) {
				return !ips_log( 
					"Error: " . $helper->getError() . "\n" .
					"Error Code: " . $helper->getErrorCode() . "\n".
					"Error Reason: " . $helper->getErrorReason() . "\n".
					"Error Description: " . $helper->getErrorDescription() . "\n"
				, 'logs/connect-facebook.log' );
			
			} else {
				return !ips_log( 
					"Bad request"
				, 'logs/connect-facebook.log' );
			}
		}
		
		
		
		try {
			$response = $this->session->get('/me?fields=id,name,email,picture.type(large)', $this->token );
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			return !ips_log( 
				'Graph returned an error: ' . $e->getMessage()
			, 'logs/connect-facebook.log' );
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			return !ips_log( 
				'Facebook SDK returned an error: ' . $e->getMessage()
			, 'logs/connect-facebook.log' );
		}
		
		$user = $response->getGraphUser();

		return array( 
			'user' 		 => array(
				'id' => $user['id'],
				'name' => $user['name'],
				'email' => $user['email'],
				'picture' => $user['picture']->getUrl()
			),
			'uid' 	 	 => $user['id'],
			'user_email' => ( !empty( $user['email'] ) ? $user['email'] : $user['name'] . '@facebook.com' )
		);
	}
	
	/**
	 * Return authenticate URL
	 *
	 * @return string
	 */
	public function getRedirect()
	{
		$helper = $this->session->getRedirectLoginHelper();
		
		return $helper->getLoginUrl( ABS_URL . 'connect/facebook/v/', Config::get( 'apps_facebook_app', 'previliges' ) );
	}
	
	/**
	 * Return user data formatted
	 *
	 * @return array
	 */
	public function getUserData( $user )
	{
		$user = Connect::setUserNames( $user['user'], $user['user']['name'] );
		
		$user['uid'] = $user['id'];
		
		$user['user_name'] = isset( $user['username'] ) ? $user['username'] : false;
		
		/**
		* Użytkownik posiada unikalny login
		*/
		$user['login'] = Connect::setLogin( ( isset( $user['username'] ) ? $user['username'] : $user['name'] ), $user['name'] );

		$user['user_birth_date'] = isset( $user['birthday'] ) ? $user['birthday'] : date( 'Y-m-d', strtotime( '-18 years' ) );
		
		$user['thumbnail'] = $this->getThumb( $user );
		
		return $user;
	}
	/**
	 * Return user thumb url
	 *
	 * @return array
	 */
	public function getThumb( $user )
	{
		return $user['picture'];
	}
	
	/**
	 * Store token for save
	 *
	 * @return array
	 */
	public function getAccessToken( $token )
	{
		Session::set( 'access_token', $token );
		return $token;
	}
	
	/**
	 * Make finish functions on registered/logged user
	 *
	 * @return array
	 */
	public function connected( $user_id )
	{
		$access_token = Facebook_UI::exchangeToken( $this->token );
		
		if( $access_token )
		{
			User_Data::update( $user_id, 'access_token_long_live', $access_token );
		}
		
		Cookie::set( 'ips_connected_status', 'connected', 3600 );
	}
	
}