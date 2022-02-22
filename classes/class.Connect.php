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


class Connect
{
	/*
	 * Provider name
	 */
	public $provider = false;
	
	/*
	 * Provider class container
	 */
	public $providerClass = false;
	/*
	 * The field names in the table corresponding to the ID mplayer for a particular API
	 */
	protected $providers = array( 
		'nk' => 'nk_uid', 
		'facebook' => 'facebook_uid',
		'twitter' => 'twitter_uid'
	);

	/*
	 * Table of data required for connected via API
	 */
	public $user = array(
		'uid' => true, 
		'login' => true, 
		'email' => false,
		'user_name' => false, 
		'first_name' => false, 
		'last_name' => false, 
		'thumbnail' => false, 
		'user_birth_date' => false
	);
	
	/**
	 * Set template without menu etc
	 *
	 * @param string $provider - name provider
	 * 
	 * @return void
	 */
	public function __construct()
	{
		App::minimalLayout();
	}
	
	/**
	 * Get Class provider
	 *
	 * @param string $provider - name provider
	 * 
	 * @return void
	 */
	public function getProvider( $provider )
	{
		if ( array_key_exists( $provider, $this->providers ) )
		{
			$this->field = $this->providers[$provider];
			
			$this->provider = $provider;
			
			$class = 'Connect_' . ucfirst( $provider );
		
			if ( !class_exists( $class, true ) )
			{
				return $this->errors( "Can't find provider class" );
			}
			
			$this->providerClass = new $class();
			
			return $this->providerClass->init();
		}
		
		return $this->errors( "Can't find provider" );
	}

	/**
	 * Check if user connected before
	 *
	 * @param string $user_email
	 * @param int $uid
	 * @param string $provider
	 * 
	 * @return int|bool
	 */
	public function isConnected( $uid )
	{
		$user_id = User_Data::getByValue( $this->field, $uid );
		
		if( !empty( $user_id ) )
		{
			$this->providerClass->connected( $user_id );
		}
		
		return !empty( $user_id ) ? $user_id : false;
	}
	
	/**
	 * Set user data as param $user
	 *
	 * @param array $user_data - parameters / user data
	 * @param string $post_data - $_POST
	 * 
	 * @return void
	 */
	public function setParams( array $user_data, $post_data = array() )
	{
		if( !empty( $user_data ) )
		{
			$user_data = array_merge( $user_data, $post_data );
			
			foreach ( $this->user as $name => $value )
			{
				if ( isset( $user_data[$name] ) )
				{
					$this->user[$name] = $user_data[$name];
				}
			}
			
			return true;
		}
		return false;
	}
	
	/**
	 * Check if we can create account wthout ask for login/email
	 *
	 * @return array
	 */
	public function canAutoCreate()
	{
		if( strlen( $this->user['login'] ) > 20 )
		{
			return false;
		}
		
		if ( !isset( $this->user['login'][5] ) )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Create an account without asking for login
	 *
	 * @param null
	 * 
	 * @return 
	 */
	public function makeAutoCreate()
	{
		$user = Ips_Registry::get('Users')->getBy( array(
			'user_email' => $this->user['email']
		) );
		
		if ( !empty( $user ) )
		{
			return $this->connectUser( $user );
		}
		elseif ( USER_LOGGED )
		{
			return $this->connectUser( getUserInfo( USER_ID, true ) );
		}
		
		try{
			return $this->register( $this->user['login'] );
		}catch( Exception $e ){}
		
		return false;
	}
	
	
	
	
	
	
	/*
	 * Connect user with existing account and update user data
	 *
	 * @param int $user_id
	 * @param int $uid
	 *
	 * @return bool true|false
	 */
	public function connectUser( $user )
	{
		User_Data::update( $user['id'], $this->field, $this->user['uid'] );

		$data = array();
		
		if ( empty( $user['avatar'] ) || $user['avatar'] == 'anonymus.png' )
		{
			if ( $avatar = $this->getAvatar() )
			{
				$data['avatar'] = $avatar;
			}
		}
		
		if ( empty( $user['first_name'] ) )
		{
			$data['first_name'] = $this->user['first_name'];
		}
		
		if ( empty( $user['last_name'] ) )
		{
			$data['last_name'] = $this->user['last_name'];
		}
		
		if ( !empty( $data ) )
		{
			PD::getInstance()->update( 'users', $data, array( 
				'id' => $user['id']
			));
		}
		
		$this->providerClass->connected( $user['id'] );
		
		return $user['id'];
	}
	
	/**
	 * Get user avatar from social connect
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function getAvatar()
	{
		if ( isset( $this->user['thumbnail'] ) && !empty( $this->user['thumbnail'] ) )
		{
			$file_name = md5( rand() . $this->user['thumbnail'] ) . '.jpg';
			
			$file = curlIPS( $this->user['thumbnail'], array(
				'file' => ips_user_avatar( $file_name, 'file' ) 
			) );
			
			return $file ? $file_name : false;	
		}
		
		return false;
	}
	


	/*
	 * Displaying form
	 *
	 * @param null
	 *
	 * @return string
	 */
	public function form()
	{
		return Templates::getInc()->getTpl( 'user_connect.html', array(
			'login' => $this->user['login'],
			'email' => $this->user['email'],
			'last_name' => $this->user['last_name'],
			'first_name' => $this->user['first_name']
		) );
	}
	
	/**
	 * Verification/Register
	 *
	 * param string $login - user login
	 * 
	 * @return bool
	 */
	public function register( $login )
	{
		/**
		 * Checking whether login is taken.
		 */
		$user = Ips_Registry::get('Users')->getBy( array(
			'user_login' => $login
		) );
		
		if ( !empty( $user ) )
		{
			throw new Exception( 'user_register_login_used' );
		}
		
		$password = uniqid();
		
		$register = new User_Register();
			
		$register->validateRegistration( array(
			'user_login' => $login,
			'user_password' => $password,
			'user_password_confirm' => $password,
			'user_email' => $this->user['email'],
			'user_birth_date' => $this->user['user_birth_date'] 
		), true );
		
		$user_id = $register->getUserId();
		
		$user = Ips_Registry::get('Users')->getBy( array(
			'user_id' => $user_id
		) );
		
		ips_message( 'user_register_success' ); 
			
		return $this->connectUser( $user );

	}

	/**
	 * Logowanie użytkownika. Jeśli użytkownik nie zaakceptował
	 * jeszcze danych zostaje wyświetlony formularz.
	 * User login. If you have not yet accepted the form data is displayed.
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function login( $user_id )
	{
		$user = Ips_Registry::get('Users')->getBy( array(
			'user_id' => $user_id
		) );
		
		if ( empty( $user ) && !USER_LOGGED )
		{
			if ( defined( 'IPS_AJAX' ) )
			{
				return false;
			}
			
			throw new Exception( 'connect_error_accept' );
		}
		else
		{
			
			/**
			 * Update facebook/nk/twitter username if available
			 */
			if ( isset( $this->user['user_name'] ) )
			{
				User_Data::update( $user_id, 'username_' . substr( $this->field, 0, -4 ), $this->user['user_name'] );
			}
			
			if ( Config::get( 'module_history' ) )
			{
				Ips_Registry::get( 'History' )->storeAction( 'connect', array(
					'action_name' => substr( $this->field, 0, -4 ),
					'user_id' => $user_id 
				) );
			}
			
			Session::clear( 'connect' );
			
			$login = new Users();
			$login->setLogged( $user_id );
		}
	}
	

	
	/**
	 * Delete user connection
	 *
	 * @param int $user_id
	 * @param string $field_name
	 * 
	 * @return array 
	 */
	public function deleteConnect( $user_id, $field_name )
	{
		return User_Data::delete( $user_id, $field_name );
	}
	
	
	/*
	 * Prepare login with specified parameters (First Name, Last Name) as an additional parameter send username available on social networking sites
	 * 
	 * @param $username string fb:login lub nk:name
	 * @param $additional string
	 *
	 * @return $login
	 */
	public static function setLogin( $username, $additional = '' )
	{
		include_once( LIBS_PATH . '/urlify-master/URLify.php');
		
		$username = URLify::downcode( Sanitize::onlyAlphanumeric( $username ) );
		/**
		 * Login shorter than 5 characters
		 */
		if ( !isset( $username[5] ) )
		{
			$username .= '_' . URLify::downcode( Sanitize::onlyAlphanumeric( $additional ) );
		}
		/**
		 * Login zawiera niedozwolone znaki.
		 * Username contains invalid characters.
		 */
		if ( strpos( $username, " " ) !== false || !preg_match( '/^[A-Za-z0-9_]$/i', $username ) )
		{
			$username = preg_replace( "/[^0-9a-zA-Z_]/si", '', $username );
		}
		
		return $username;
	}
	/**
	 * Set first/last name from data
	 *
	 * @param array $user_data
	 * @param string $name
	 * 
	 * @return array 
	 */
	public static function setUserNames( array $user_data, $name )
	{
		if ( strpos( $name, ' ' ) !== false )
		{
			$data = explode( ' ', $name );
			
			if ( !isset( $user_data['first_name'] ) || empty( $user_data['first_name'] ) )
			{
				$user_data['first_name'] = ( isset( $data[0] ) && !empty( $data[0] ) ? $data[0] : '' );
			}
			
			if ( !isset( $user_data['last_name'] ) || empty( $user_data['last_name'] ) )
			{
				$user_data['last_name'] = ( is_array( $data ) ? end( $data ) : '' );
			}
		}
		
		return $user_data;
	}
	
	/**
	 * Sets log to track errrors
	 *
	 * @param string $msg 
	 * 
	 * @return bool
	 */
	public function errors( $msg )
	{
		return !ips_log( $msg . "\n" . ips_backtrace( true ). "\n", 'logs/connect.log' );
	}
}
?>
