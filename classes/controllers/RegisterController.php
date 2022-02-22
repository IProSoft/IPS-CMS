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

class Register_Controller
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct(){}
	
	public function route()
	{
		if( !empty( $_POST ) )
		{
			try{
			
				$register = new User_Register();	
				
				$user_id = $register->validateForm( $_POST );
				
				if( Config::get( 'user_account', 'email_activation' ) )
				{
					return ips_redirect( 'index.html', 'user_register_success_verify' );
				}
				
				ips_message( [
					'info' => 'user_register_success'
				] );
				
				$user = new Users();
				
				return $user->setLogged( $user_id );

			} catch ( Exception $e ) {
				
				ips_redirect( '/register/', [
					'alert' => $e->getMessage()
				] );
			}
		}
		
		add_filter( 'init_js_files', function( $array ){
			return add_static_file( $array, array(
				'js/validate/register_validate.js', 
				'js/validate/date_picker_handler.js'
			) );
		}, 10 );
		
		return Templates::getInc()->getTpl( 'user_register.html', array(
			'login' => get_input( 'login', '' ),
			'email' => get_input( 'email', '' ),
			'user_register_rules_url' => __s('user_register_rules', Page::url( array( 
				'post_uid' => 'uid_rules'
			) ) ),
			'recaptcha_public_key' => Config::getArray( 'recaptcha_token', 'publickey' ),
			'birth_date' => get_input( 'birth_date', '' ),
		) );
	}
}