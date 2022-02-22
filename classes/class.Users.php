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

 

class Users
{
	private $password;
	
	public $_error = array();
	
	/**
	 * Set cookie time
	 *
	 * @param
	 * 
	 * @return
	 */
	public function __construct()
	{
		$this->login_cookie_time = Config::get('user_account', 'login_cookie_time') * 60 * 60;
	}
	
	/**
	 * Protect against brute force attack secure for IP change
	 *
	 * @param string $login
	 * 
	 * @return mixed
	 */
	public function bruteForce( $login )
	{
		$row_temporary = Ips_Registry::get('Temporary')->get( array(
			'action' => 'user_login',
			'object_id' => sprintf("%u", crc32( $login ) )
		), true );
		
		$count = isset( $row_temporary['temporary_extra'] ) ? $row_temporary['temporary_extra'] : 1;
		
		if( time() - $row_temporary['time'] > 10 )
		{
			Ips_Registry::get('Temporary')->delete( $row_temporary );
		}
		elseif( time() - $row_temporary['time'] < 10 && $count > 4 )
		{
			throw new Exception( 'user_error_brute_force' );
		}
		else
		{
			Ips_Registry::get('Temporary')->set( array_merge( $row_temporary, array(
				'temporary_extra' => $count + 1
			)) );
		}
		
		return false;
	}
	/**
	 * 
	 *
	 * @param $login - username or e-mail user
	 * @param $password - user password
	 * @param $remember - or remember mplayer for 14 days
	 * 
	 * @return void
	 */
	public function userLogin( $login, $password, $remember = false )
	{
		try
		{
			$this->bruteForce( $login );
			
			$user_id = $this->checkUser( Sanitize::cleanSQL( $login ), hashPassword( $password ) );
			
			if ( $remember )
			{
				$this->setRemember( $user_id );
			}

			return $this->setLogged( $user_id );
			
		}
		catch ( Exception $e )
		{
			Cookie::destroy();
			
			if ( defined( 'IPS_AJAX' ) )
			{
				return $e->getMessage();
			}
			
			ips_redirect( 'login/', $e->getMessage() );
		}
	}
	
	/**
	 * Checking the data sent by the user.
	 * The method calls the function UserLogin ()
	 *
	 * Zwraca wartość true jeśli user_id istnieje w bazie
	 * Returns true if there is a database user_id
	 *
	 * @return bool
	 */
	private function checkUser( $login, $password )
	{
		$this->user = PD::getInstance()->select( "users", array(
			'login' => $login,
			'email' => array(
				$login, '=', 'OR' 
			) 
		), 1 );
		
		if ( !empty( $this->user ) )
		{
			if ( $this->user['activ'] != '1' )
			{
				throw new Exception( 'user_error_inactive' );
			}
			elseif ( $this->user['password'] != $password )
			{
				throw new Exception( 'user_error_password' );
			}
			else
			{
				return (int) $this->user['id'];
			}
		}
		else
		{
			throw new Exception( 'user_error_not_exists' );
		}
	}
	
	
	/**
	 * Saving a unique token for possible comparisons in the database.
	 *
	 * @param $id intiger - a unique user ID
	 * 
	 * @return void
	 */
	private function setRemember( $id, $cookie = true )
	{
		$this->login_cookie_time = Config::get('user_account', 'login_cookie_remember_time') * 24 * 60 * 60;
		
		if( $cookie )
		{
			Cookie::set( 'ssid_autologin', $this->userToken( $id, true ), $this->login_cookie_time );
		}
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setLogged( $id )
	{
		Session::destroy();
		
		if ( empty( $this->user ) )
		{
			$this->user = PD::getInstance()->select( 'users', array(
				'users.id' => $id 
			), 1 );
		}
		
		Session::push( array(
			'user_name' => $this->user['login'],
			'user_id' => $id
		) );

		User_Data::get( $id );
		
		if ( User_Data::get( $id, 'is_admin' ) == 1 )
		{
			Session::set( 'user_admin', md5( sha1( AUTH_KEY . md5( AUTH_KEY . $id ) ) . '_admin' ) );
			ips_log( array( 
				'user_id'=> $id,
				'ip' 		=> $_SERVER['REMOTE_ADDR'],
				'time'		=> date("Y-m-d H:i:s"),
			), 'logs/admin-login.log' );
		}
		
		if ( User_Data::get( $id, 'is_moderator' ) == 1 )
		{
			Session::set( 'user_moderator', md5( sha1( AUTH_KEY . md5( AUTH_KEY . $id ) ) . '_moderator' ) );
		}
		
		$nk_uid = User_Data::get( $id, 'nk_uid' );
		
		if ( $nk_uid )
		{
			Config::setSessionConfig( array(
				'connect_nk' => 1 
			) );
		}
		
		$facebook_uid = User_Data::get( $id, 'facebook_uid' );
		
		if ( $facebook_uid )
		{
			Config::setSessionConfig( array(
				'connect_facebook' => 1 
			) );
		}
		
		$lang = User_Data::get( USER_ID, 'default_language' );
		
		if( Translate::available( $lang ) )
		{
			Cookie::set( 'ips_language', $lang, $this->login_cookie_time );
		}
		
		Cookie::set( 'ssid_global', ips_crypt( $id ), $this->login_cookie_time );
		
		User_Data::update( $id, 'user_last_visit', date( "Y-m-d H:i:s" ) );
	
		ips_message( array(
			'normal' => __s( 'welcome_message', $this->user['login'] )
		) );
		
		if ( Config::get( 'module_history' ) )
		{
			Ips_Registry::get( 'History' )->storeAction( 'login', array(
				'user_id' => $id 
			) );
		}
		
		if( Config::GET( 'apps_facebook_app', 'save_token' ) )
		{
			Cookie::set( 'ips_get_token', 'true' );
		}
		
		if ( defined( 'IPS_AJAX' ) )
		{
			return $id;
		}
		
		ips_redirect( self::redir( $id ) );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function isRemember()
	{
		$ssid_global = Cookie::get( 'ssid_global', false );
		
		if( $ssid_global )
		{
			$ssid_autologin = Cookie::get( 'ssid_autologin', false );
		
			if( $ssid_autologin && $ssid_autologin == getSecureToken( ips_decrypt( $ssid_global ) ) )
			{
				$user_id = User_Data::getByValue( 'secure_token', $ssid_autologin  );
				
				if( $user_id )
				{
					return $this->setRemember( $user_id, false ) && $this->setLogged( $user_id );
				}
			}

			Cookie::destroy( true );
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function redir( $user_id )
	{
		if ( User_Data::get( $user_id, 'is_admin' ) == 1 )
		{
			return admin_url( '/' );
		}
		elseif ( Cookie::exists('ips-redir' ) )
		{
			return Cookie::get('ips-redir' );
		}
		
		return 'index.html';
	}
	/**
	 * 
	 *
	 */
	public function activateAccount( $secure_token )
	{
		if ( !USER_LOGGED )
		{
			if ( !empty( $secure_token ) )
			{
				$this->user = PD::getInstance()->from( array( 
					'users' => 'u', 
					'users_data' => 'u_d'
				) )->setWhere(array(
					'setting_value' => Sanitize::onlyAlphanumeric( $secure_token ),
					'setting_key' => 'secure_token',
					'u.id' => 'field:u_d.user_id' 
				))->getOne();
		
				if ( !empty( $this->user ) && !empty( $this->user['user_id'] ) )
				{
					if ( $this->user['activ'] > 0 )
					{
						return ips_redirect( 'index.html', 'user_account_active' );
					}
					else
					{
						PD::getInstance()->update( 'users', array(
							'activ' => 1 
						), array(
							'id' => $this->user['user_id']
						) );
						
						User_Data::delete( $this->user['user_id'], 'secure_token' );
						
						return $this->setLogged( $this->user['user_id'] );
					}
				}
				else
				{
					return ips_redirect( 'index.html', 'user_error_account' );
				}
			}
			else
			{
				return ips_redirect( 'index.html', 'user_error_acces' );
			}
		}
		else
		{
			return ips_redirect( 'index.html', 'user_error_logged' );
		}
	}
	/**
	 * Forgot your password - send link
	 *
	 * @param $email - User email address
	 * 
	 * @return 
	 */
	public function resetPassword( $reset_password )
	{
		$user = PD::getInstance()->select( 'users', array(
			'email' => $reset_password 
		), 1 );
		
		if ( !empty( $user ) )
		{
			$url = base_convert( uniqid( 'user_reset_password', true ), 15, 36 );
			
			User_Data::update( $user['id'], 'user_reset_password', $url );
			
			$send = new EmailExtender();
			$send->EmailTemplate( array(
				'email_to' => $reset_password,
				'email_content' => makeClicableURL( __s( 'user_reset_password_email', $user['login'], ABS_URL . 'login/save_password/?hash=' . $url ) ),
				'email_title' => __( 'user_reset_password_title' )
			), 'email_lost_password' );
			
			return ips_redirect( '/', 'user_reset_password_sent' );
		}
		else
		{
			return ips_redirect( 'login/reset_password', 'user_error_email' );
		}
	}
		/**
	 * Forgot your password - send link
	 *
	 * @param $email - User email address
	 * 
	 * @return 
	 */
	public function savePassword( $data, $hash )
	{
		$user_id = User_Data::getByValue( 'user_reset_password', $hash, 1 );
		
		if ( !$hash || !$user_id )
		{
			return ips_redirect( false, array(
				'alert' => 'err_unknown'
			) );
		}

		if ( !has_value( 'pasword', $data ) || !has_value( 'pasword_repeat', $data ) )
		{
			return ips_redirect( 'login/save_password/?hash=' . $hash, array(
				'alert' => 'fill_in_required_fields'
			) );
		}

		if ( $data['pasword'] != $data['pasword_repeat'] )
		{
			return ips_redirect( 'login/save_password/?hash=' . $hash, array(
				'alert' => 'user_password_diffrent'
			) );
		}
		
		if ( strlen( $data['pasword'] ) < Config::get('user_account', 'password_length') )
		{
			return ips_redirect( 'login/save_password/?hash=' . $hash, array(
				'alert' => __s( 'user_register_password_strength', Config::get('user_account', 'password_length') )
			) );
		}
		
		if ( PD::getInstance()->update( 'users', array(
			'password' => hashPassword( $data['pasword'] ) 
		), array(
			'id' => $user_id 
		) ) )
		{
			User_Data::delete( $user_id , 'user_reset_password' );
			
			return ips_redirect( 'login/', array(
				'info' => 'edit_profile_changed'
			) );
		}
		
		return ips_redirect( '/', array(
			'alert' => 'err_unknown'
		) );

	}
	
	/**
	 * Resend the activation link.
	 *
	 * @param $email - User email address
	 * 
	 * @return 
	 */
	public function resendActivation( $email )
	{
		
		$user = PD::getInstance()->select( 'users', array(
			'email' => $email 
		), 1 );
		
		if ( !empty( $user ) )
		{
			if ( $user['activ'] == 1 )
			{
				return array(
					'url' => 'index.html',
					'message' => 'user_account_active' 
				);
			}
			else
			{
				$send = new EmailExtender();
				$send->EmailTemplate( array(
					'email_to' => $email,
					'email_content' => __s( 'user_resend_activation_email', $user['login'], ABS_URL . 'login/activate_account/?hash=' . $this->userToken( $user['id'] ) ),
					'email_title' => __( 'user_resend_activation_title' )
				) );
				
				return array(
					'url' => 'login/',
					'message' => 'user_resend_activation_sent' 
				);
			}
		}
		else
		{
			return array(
				'url' => 'login/resend_activation',
				'message' => 'user_error_email' 
			);
		}
		
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function userToken( $user_id, $regenerate = false )
	{
		$secure_token = User_Data::get( $user_id, 'secure_token' );
		
		if ( empty( $secure_token ) || $regenerate )
		{
			$secure_token = getSecureToken( $user_id );
			User_Data::update( $user_id, 'secure_token', $secure_token );
		}
		
		return $secure_token;
	}
	
	/**
	 * Delete users account
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function delete( $id )
	{
		$db = PD::getInstance();
		
		$user = $db->select( 'users', array(
			'id' => $id 
		), 1 );
		
		if ( !empty( $user ) && $db->delete( 'users', array( 'id' =>  $id ) ) )
		{
			$db->delete( array(
				'users_follow_user',
				'users_favourites',
				'users_data' 
			), array( 'user_id' =>  $id ) );
			
			/**
			 * Delete private files
			 */
			$private = $db->select( IPS__FILES, array(
				'upload_status' => 'private',
				'user_login' => $user['login'] 
			) );
			
			if ( !empty( $private ) )
			{
				$operations = new Operacje();
				foreach ( $private as $i => $row )
				{
					$operations->move( $row['id'], 'delete' );
				}
			}
			
			$db->update( IPS__FILES, array(
				'user_login' => __( 'anonymous_login' )
			), array(
				'user_login' => $user['login']
			) );
			
			/*** ADD PINIT */
			
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Edit users account
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function edit( $id, $data )
	{
		$user = getUserInfo( $id );
		
		if ( !empty( $user ) )
		{
			
			if ( isset( $data['user_data']['about_me'][1500] ) )
			{
				ips_message( array(
					'alert' => 'edit_profile_tolong'
				) );
			}
			
			if ( isset( $data['user_data'] ) )
			{
				$allowed = array(
					'about_me', 'gender', 'post_facebook', 'post_facebook_message', 'newsletter'
				);
				foreach( $data['user_data'] as $key => $value )
				{
					if( in_array( $key, $allowed ) )
					{
						User_Data::update(  USER_ID, $key, $value );
					}
				}
			}

			if ( isset( $data['email'] ) && !empty( $data['email'] ) )
			{
				$user = array(
					'email' => Sanitize::sanitizePHP( $data['email'], 'email' ),
					'user_birth_date' => Sanitize::sanitizePHP( $data['birth_date'] )
				);
				
				if ( isset( $data['first_name'] ) && !empty( $data['first_name'] ) )
				{
					$user['first_name'] = Sanitize::cleanXss( $data['first_name'] );
				}
				
				if ( isset( $data['last_name'] ) && !empty( $data['last_name'] ) )
				{
					$user['last_name'] = Sanitize::cleanXss( $data['last_name'] );
				}
				
				if ( PD::getInstance()->update( 'users', $user, array(
					'id' => USER_ID 
				) ) )
				{
					ips_message( array(
						'info' => 'edit_profile_changed'
					) );
				}
			}
			
			if ( isset( $data['old_password'] ) )
			{
				return $this->changePassword( $id, $data );
			}
			
			return true;
		}
		
		return false;
	}
	/**
	 * Edit users password
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function changePassword( $id, $data )
	{
		
		if ( !has_value( 'old_password', $data ) || !has_value( 'password_change', $data ) || !has_value( 'password_change_repeat', $data ) )
		{
			ips_redirect( 'edit_profile/password', 'fill_in_required_fields' );
		}
		
		$user = getUserInfo( $id );
		
		if ( hashPassword( $data['old_password'] ) != $user['password'] )
		{
			ips_redirect( 'edit_profile/password', 'edit_profile_wrong_password' );
		}
		
		if ( $data['password_change'] != $data['password_change_repeat'] )
		{
			ips_redirect( 'edit_profile/password', 'user_password_diffrent' );
		}
		
		if ( PD::getInstance()->update( 'users', array(
			'password' => hashPassword( $data['password_change'] ) 
		), array(
			'id' => USER_ID 
		) ) )
		{
			return ips_message( array(
				'info' => 'edit_profile_changed'
			) );
		}
		
		ips_message( array( 
			'alert' => 'err_unknown'
		) );
		
		return false;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	
	public function getStats( $user_id )
	{	
		$upload_post = PD::getInstance()->from( 'upload_post' )->where( 'user_id', $user_id )->fields('COALESCE( SUM(votes_opinion), 0)')->getQuery();

		$comments = PD::getInstance()->from( 'upload_comments' )->where( 'user_id', $user_id )->fields(array(
			'COALESCE( SUM(comment_opinion), 0) as comments_opinion',
			'COUNT(*) as user_comments',
			'(' . $upload_post . ') as posts_opinion'
		))->getOne();
		
		$files = PD::getInstance()->from( IPS__FILES )->where( 'user_id', $user_id )->fields(array(
			'COALESCE( SUM(upload_post.upload_activ=1), 0) as added_main',
			'COALESCE( SUM(upload_post.upload_activ=0), 0) as added_wait'
		))->getOne();
		
		return array_merge( $comments, $files );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	
	public function getBy( array $user, $one = true )
	{
		$query = PD::getInstance()->from( 'users' );
		
		if( isset( $user['user_id'] ) )
		{
			$query = $query->where( 'id', $user['user_id'] );
		}
		
		if( isset( $user['user_login'] ) )
		{
			$query = $query->where( 'login', $user['user_login'] );
		}
		
		if( isset( $user['user_email'] ) )
		{
			$query = $query->where( 'email', $user['user_email'] );
		}
		
		if( $one )
		{
			return $query->getOne();
		}
		
		return $query->get();
	}
	
	/**
	 * Ban user for x time
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function ban( $user_id, $time )
	{
		if( in_array( $time, array( 'week', 'month', 'year' ) ) )
		{
			$ban_time = date("Y-m-d H:i:s", strtotime("now + 1 " . $time ) );
			
			$banned = PD::getInstance()->update( 'users', array(
				'user_banned' => 1
			), array(
				'id' => $user_id
			) );
			
			if( $banned )
			{
				User_Data::update( $user_id, 'user_banned_data', array(
					'who_ban' => USER_LOGIN,
					'date_ban' => $ban_time
				) );

				return $ban_time;
			}
		}
		
		return false;
	}
	/**
	 * Ban user for x time
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function unBan( $user_id )
	{
		$unban = PD::getInstance()->update( 'users', array( 
			'user_banned' => 0
		), array(
			'id' => $user_id
		) );
		
		if( $unban )
		{
			User_Data::delete( $user_id, 'user_banned_data' );
		} 
		
		return $unban;
	}
}
?>