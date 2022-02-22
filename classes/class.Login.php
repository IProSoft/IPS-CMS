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

class Login extends Users
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 *
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route_login()
	{
		if( USER_LOGGED )
		{
			return ips_redirect( false, array(
				'info' => 'user_error_logged'
			) );
		}

		if( isset( $_POST['action_login'] ) )
		{
			if( !empty( $_POST['login'] ) && !empty( $_POST['password'] ) )
			{
				return $this->userLogin( $_POST['login'], $_POST['password'], isset( $_POST['user_remember'] ) );
			}
			else
			{
				return ips_redirect( 'login/', array( 
					'alert' => 'user_error_empty_fields'
				));
			}
		}
		
		add_filter( 'init_js_files', function( $array ){
			return add_static_file( $array, array(
				'js/validate/register_validate.js', 
				'js/validate/date_picker_handler.js'
			) );
		}, 10 );
		
		return Templates::getInc()->getTpl( 'user_login.html', array( 
			'login' => get_input( 'login', $_POST, '' )
		) );
	}
	
	/**
	 *
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route_user_logout()
	{
		Cookie::destroy( true );
		return ips_redirect( 'index.html', 'user_logged_out' );
	}
	/**
	 *
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route_delete_account()
	{
		if ( $this->delete( USER_ID ) )
		{
			Cookie::destroy( true );
		}
		
		ips_redirect( 'index.html' );
	}
	/**
	 *
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route_reset_password()
	{
		if ( isset( $_POST['reset_password'] ) )
		{
			$this->resetPassword( $_POST['reset_password'] );
		}
		
		return Templates::getInc()->getTpl( 'user_reset_password.html' );
	}
	
	/**
	 *
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route_save_password()
	{
		$hash = get_input( 'hash' );
		
		if ( isset( $_POST['save_password'] ) )
		{
			$this->savePassword( $_POST['save_password'], $hash );
		}
		
		return Templates::getInc()->getTpl( 'user_reset_password_save.html', array(
			'hash' => $hash,
		) );
	}
	/**
	 *
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route_resend_activation()
	{
		if ( isset( $_POST['resend_activation'] ) )
		{
			$send = $this->resendActivation( $_POST['resend_activation'] );
			
			ips_redirect( $send['url'], $send['message'] );
		}
		
		return Templates::getInc()->getTpl( 'user_resend_activation.html' );
	}
	
	/**
	 *
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route_activate_account()
	{
		return $this->activateAccount( $_GET['hash'] );
	}
}