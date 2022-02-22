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
require LIBS_PATH . '/TwitterOAuth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
class Connect_Twitter
{
	/**
	 * @var bool
	 */
	private $oauth = false;
	
	/**
	 * @var bool
	 */
	private $oauth_token = null;
	
	/**
	 * @var bool
	 */
	private $oauth_token_secret = null;
	
	/**
	 * Get library instance
	 *
	 * @return string
	 */
	public function init()
	{
		$app = Config::getArray('apps_twitter_app');
		
		if( !$app['consumer_key'] || !$app['consumer_secret'] )
		{
			return false;
		}
		try{
			
			$this->oauth = new TwitterOAuth( $app['consumer_key'], $app['consumer_secret'] );
			
			if ( has_value( 'oauth_verifier', $_GET ) )
			{
				$this->authenticate();
			}
		
		} catch(Exception $ex) {
			return !ips_log( $ex, 'logs/connect-twitter.log' );
		}
		
		return $this;
	}
	
	/**
	 * User authenticated / get access_token
	 *
	 * @return string
	 */
	public function authenticate()
	{
		$this->oauth->setOauthToken( ips_unset( 'oauth_token', $_SESSION, null ), ips_unset( 'oauth_token_secret', $_SESSION, null ) );

		$request_token = $this->oauth->oauth( 'oauth/access_token', array(
			'oauth_verifier' => $_GET['oauth_verifier']
		));

		if ( $this->oauth->getLastHttpCode() != 200 )
		{
			throw new Exception( 'Twitter oauth/access_token miss' );
		}
		
		$this->oauth_token = $request_token['oauth_token'];
		$this->oauth_token_secret = $request_token['oauth_token_secret'];
		
		$this->oauth->setOauthToken( $this->oauth_token, $this->oauth_token_secret );
	}
	/**
	 * Get user info if connected
	 *
	 * @return array|bool
	 */
	public function getUser()
	{
		if ( $this->oauth_token && $this->oauth_token_secret )
		{
			$user_info = $this->oauth->get('account/verify_credentials');
			
			if ( !isset( $user_info->error ) )
			{
				return array( 
					'user' 		 => $user_info,
					'uid' 	 	 => $user_info->id,
					'user_email' => false
				);
			}
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
		try{
			
			$request_token = $this->oauth->oauth( 'oauth/request_token', array(
				'oauth_callback' => ABS_URL . 'connect/twitter/v/'
			));
	
			if ( $this->oauth->getLastHttpCode() == 200 && $request_token['oauth_callback_confirmed'] )
			{
				Session::set( 'oauth_token', $request_token['oauth_token'] );
				Session::set( 'oauth_token_secret', $request_token['oauth_token_secret'] );
				
				return $this->oauth->url( 'oauth/authenticate', array(
					'oauth_token' => $request_token['oauth_token']
				));
			}
		
		} catch(Exception $ex) {
			return !ips_log( $ex, 'logs/connect-twitter.log' );
		}
		
		return false;

	}
	
	/**
	 * Return user data formatted
	 *
	 * @return array
	 */
	public function getUserData( $user )
	{
		$user_data = array();
			
			$user_data = Connect::setUserNames( $user_data, $user['user']->name );
			
			$user_data['uid'] = $user['user']->id;
			
			$user_data['user_name'] = $user['user']->name;

			$username = isset( $user['user']->screen_name ) ? $user['user']->screen_name : $user['user']->name ;
			
			$user_data['login'] = Connect::setLogin( $username, $user['user']->name );
			
			$user_data['user_birth_date'] = date('Y-m-d', strtotime('-18 years') );
			
			$user_data['email'] = $user_data['login'] . '_t@twitter.com';
			
			$user_data['about_me'] = $user['user']->description;
			
			$this->thumb = $user['user']->profile_image_url;
			
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
		return User_Data::update( $uder_id, 'twitter_oauth', array(
			'oauth_token' 		 => $this->oauth_token,
			'oauth_token_secret' => $this->oauth_token_secret
		) );			
	}
}