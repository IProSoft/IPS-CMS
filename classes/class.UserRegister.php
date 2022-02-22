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

class User_Register
{
	
	public $_error = array();
	
	
	/* 
	 *Verification login, to determine the number of characters at the top.
	 */
	public function checkLogin( $user_login )
	{
		
		if ( !preg_match( '/^[A-Za-z0-9_]{' . Config::get('user_account', 'login_length') . '}$/i', $user_login ) )
		{
			list( $min, $max ) = explode( ',',  Config::get('user_account', 'login_length') );
			throw new Exception( __s( 'user_register_login_error', $min, $max ) );
		}
		
		$user = PD::getInstance()->cnt( 'users', array(
			'login' => Sanitize::cleanSQL( $user_login )
		) );
		
		if ( !empty( $user ) )
		{
			throw new Exception( 'user_register_login_used' );
		}
		
		return true;
	}
	/* 
	 * Verification login, to determine the number of characters at the top.
	 */
	public function checkPassword( $user_password, $user_password_confirm )
	{
		
		if ( empty( $user_password ) || empty( $user_password_confirm ) )
		{
			throw new Exception( 'user_register_password_empty' );
		}
		
		if ( $user_password != $user_password_confirm )
		{
			throw new Exception( 'user_password_diffrent' );
		}
		
		if ( !preg_match( '/^(.*){' . Config::get('user_account', 'password_length') . '}$/i', $user_password ) )
		{
			list( $min, $max ) = explode( ',',  Config::get('user_account', 'password_length') );
			throw new Exception( __s( 'user_register_password_strength', $min, $max ) );
		}
		
		return true;
	}
	
	
	/* 
	* Verification email address at any time you can add additional conditions.
	*/
	public function checkEmail( $email )
	{
		if ( empty( $email ) )
		{
			throw new Exception( 'user_set_email' );
		}
		
		if ( !Sanitize::validatePHP( $email, 'email' ) )
		{
			throw new Exception( 'user_wrong_email' );
		}
		
		$row = PD::getInstance()->cnt( 'users', array(
			'email' => $email
		) );
		
		if ( !empty( $row ) )
		{
			throw new Exception( 'user_register_email_error' );
		}
		
		return true;
		
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getUserId()
	{
		return $this->register_id;
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function validateForm( $post_data )
	{
		if ( !isset( $post_data['accept_rules'] ) || empty( $post_data['accept_rules'] ) )
		{
			throw new Exception( 'user_register_error_rules' );
		}
		
		if ( !isset( $post_data['form_box'] ) && Config::get( 'user_account', 'register_captcha' ) == 1 )
		{
			if ( !$this->validateCaptcha( $post_data['g-recaptcha-response'] ) )
			{
				throw new Exception( 'common_captcha_error' );
			}
		}
		
		if ( !isset( $post_data['first_name'] ) )
		{
			$post_data['first_name'] = '';
		}
		
		if ( !isset( $post_data['last_name'] ) )
		{
			$post_data['last_name'] = '';
		}
		
		return $this->validateRegistration( array(
			'first_name' => $post_data['first_name'],
			'last_name' => $post_data['last_name'],
			'user_login' => ( !isset( $post_data['login'] ) ? $post_data['first_name'] : $post_data['login'] ),
			'user_password' => $post_data['password'],
			'user_password_confirm' => $post_data['password_p'],
			'user_email' => $post_data['email'],
			'user_birth_date' => ( isset( $post_data['birth_date'] ) ? $post_data['birth_date'] : date('Y-m-d', strtotime('-18 years') ) ) 
		) );
			
			
		
		
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function validateRegistration( array $user_data, $user_social = false )
	{
		$this->checkLogin( trim( $user_data['user_login'] ) );
		$this->checkPassword( $user_data['user_password'], $user_data['user_password_confirm'] );
		$this->checkEmail( $user_data['user_email'] );

		$registered = $this->registerUser( array(
			'login' => trim( $user_data['user_login'] ),
			'password' => $user_data['user_password'],
			'email' => trim( $user_data['user_email'] ),
			'first_name' => ( isset( $user_data['first_name'] ) ? $user_data['first_name'] : '' ),
			'last_name' => ( isset( $user_data['last_name'] ) ? $user_data['last_name'] : '' ),
			'user_birth_date' => $user_data['user_birth_date'] 
		), $user_social );
		
		if ( !$registered )
		{
			throw new Exception( 'err_unknown' );
		}
		
		if ( Config::get( 'module_history' ) )
		{
			Ips_Registry::get( 'History' )->storeAction( 'register', array(
				'user_id' => $this->getUserId() 
			) );
		}
		
		return $registered;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function registerUser( $user_data, $facebook = false )
	{
		$this->register_id = PD::getInstance()->insert( 'users', array_merge( $user_data, array(
			'password' => hashPassword( $user_data['password'] ),
			'date_add' => IPS_CURRENT_DATE,
			'avatar' => 'anonymus.png',
			'activ' => ( $facebook || Config::get( 'user_account', 'email_activation' ) == 0 ? 1 : 0 ) 
		) ) );
		
		if ( empty( $this->register_id ) )
		{
			return false;
		}
		
		$secure_token = getSecureToken( $this->register_id . $user_data['login'] );
		
		User_Data::update( $this->register_id, 'secure_token', $secure_token );
		User_Data::update( $this->register_id, 'default_language', IPS_LNG );
		
		if ( Config::get( 'user_account', 'email_activation' ) )
		{
			$this->activationEmail( $secure_token, $user_data );
		}
		
		return $this->register_id;
	}
	
	/**
	 * Send email to user with activation link
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function activationEmail( $secure_token, $user_data )
	{
		
		global ${IPS_LNG};
		
		$send = new EmailExtender();
		return $send->EmailTemplate( array(
			'email_to' => $user_data['email'],
			'email_content' => nl2br( sprintf( ${IPS_LNG}['user_register_email_activation'], $user_data['login'], $user_data['password'], ABS_URL . 'login/activate_account/?hash=' . $secure_token ) ),
			'email_title' => ${IPS_LNG}['user_register_email_title'],
			'email_footer' => ${IPS_LNG}['user_register_email_active'] 
		), 'email_activation' );
		
	}
	
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function validateCaptcha( $response )
	{
		$secret = Config::getArray( 'recaptcha_token', 'privatekey' );
		
		$recaptcha = curlIPS( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $response . '&remoteip=' . $_SERVER['REMOTE_ADDR'], array(
			'timeout' => 2 
		) );
		
		if( is_null( $recaptcha ) || empty( $recaptcha ) )
		{
			return false;
		}
		
		$decoded = json_decode( $recaptcha, true );
		
		if( isset( $decoded['error-codes'] ) )
		{
			ips_log( 'Captcha error :' );
			ips_log( $decoded['error-codes'] );
		}
		
		return $decoded['success'];
		
	}
}

