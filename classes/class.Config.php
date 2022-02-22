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

class Config
{
	
	/**
	 * Cachowanie ustawień konfiguracyjnych
	 * Caching configuration
	 * @protected _cache_configs
	 * @static
	 */
	protected static $_cache_configs = false;
	
	/**
	 * Pobieramy tablice konfiguracji do tablicy w klasie
	 * Configuration panels charge the classroom blackboard
	 * @protected _configs
	 * @static
	 */
	protected static $_configs = null;
	
	/**
	 * Plik cache ustawień konfiguracyjnych.
	 * Cache configuration file.
	 * @protected _configs
	 * @static
	 */
	protected static $_configs_cache = 'cache.settings.json';
	
	/* constructor */
	public function __construct( $allow_cache = true )
	{
		
		if ( $allow_cache && self::cacheTime() && self::$_configs == null )
		{
			self::$_configs = json_decode( file_get_contents( CACHE_PATH . '/' . self::$_configs_cache ), true );
		}
		
		if ( !self::cache() || self::$_configs == null )
		{
			global $config;
			
			self::$_configs = $config;
			
			$config == null;
			
			$this->loadConfigs();
			
			self::$_configs = castTypes( self::$_configs );
			
			$this->setSessionConfig( array(
				'system_cache' => self::getArray( 'system_cache' ),
				'connect_nk' => Session::get( 'connect_nk' ),
				'connect_facebook' => Session::get( 'connect_facebook' ) 
			) );
			
			self::writeCache();
		}
	}
	/**
	 * Zapisywanie pliku cache.
	 * Save a file cache.
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public static function writeCache( $append = false )
	{
		/**
		 * Cachowanie jest włączone
		 * Caching is enabled
		 */
		if ( self::cache() )
		{
			
			$settings = PD::getInstance()->from( 'system_settings' )->get();
			
			foreach ( $settings as $name => $value )
			{
				self::$_configs[$value['settings_name']] = $value['settings_value'];
			}
			
			file_put_contents( CACHE_PATH . '/' . self::$_configs_cache, json_encode( self::$_configs ), LOCK_EX );
		}
		
	}
	
	/**
	 * usuwanie pliku cache.
	 * deleting the cache file.
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public static function deleteCache()
	{
		if ( file_exists( CACHE_PATH . '/' . self::$_configs_cache ) )
		{
			@unlink( CACHE_PATH . '/' . self::$_configs_cache );
		};
	}
	
	public static function cache()
	{
		return (bool)Session::getChild( 'system_cache', 'config' );
	}
	
	/**
	 * Sprawdzanie "żywotności" ustawień konfiguracyjnych
	 * Checking the "lifetime" of configuration settings
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public static function cacheTime()
	{
		/**
		 * Cachowanie jest wyłączone
		 * Caching is disabled
		 */
		if ( !self::cache() )
		{
			return false;
		}
		
		/**
		 * Plik cache nie istnieje, zwraca false.
		 * Cache file does not exist, it returns false.
		 */
		if ( !file_exists( CACHE_PATH . '/' . self::$_configs_cache ) )
		{
			return false;
		}
		
		/**
		 * Sprawdzanie czasu cache.
		 * Checking the cache.
		 */
		
		if ( $config_expiry = Session::getChild( 'system_cache', 'config_expiry' ) )
		{
			if ( filemtime( CACHE_PATH . '/' . self::$_configs_cache ) + intval( $config_expiry ) > time() )
			{
				return true;
			}
		}
		return false;
	}
	/**
	 * Private  function loadConfigs
	 * Pobieranie i zapis ustawień konfiguracyjnych.
	 * Downloading and saving configuration settings.
	 * 
	 * @param  null
	 * 
	 * @return void
	 */
	private function loadConfigs()
	{
		
		$settings = PD::getInstance()->select( 'system_settings', array(
			'autoload' => 'yes'
		), 0, "`settings_name`,`settings_value`" );
		
		foreach ( $settings as $name => $value )
		{
			self::$_configs[$value['settings_name']] = $value['settings_value'];
		}
		
	}
	
	/**
	 * Pobieranie wartości kilku ustawień
	 * Downloading of several settings
	 * 
	 * @param array $config_names
	 *
	 * @return _configs|$config_names
	 */
	public static function getMulti( array $config_names )
	{
		foreach ( $config_names as $key => $settings_name )
		{
			if ( isset( self::$_configs[$settings_name] ) )
			{
				$config_names[$settings_name] = is_string( self::$_configs[$settings_name] ) ? stripslashes( self::$_configs[$settings_name] ) : self::$_configs[$settings_name];
			}
		}
		return $config_names;
	}
	
	/**
	 * Pobieranie wartości pojedyńczych ustawień
	 * Download the single settings
	 * 
	 * @param  mixed $settings_name
	 *
	 * @return _configs|$settings_name
	 */
	public static function get( $settings_name, $sub_key = false )
	{
		if( $sub_key )
		{
			return self::getArray( $settings_name, $sub_key );
		}
		//echo "Pobieram '" . $settings_name . "'\n";
		if ( isset( self::$_configs[$settings_name] ) )
		{
			return is_string( self::$_configs[$settings_name] ) ? stripslashes( self::$_configs[$settings_name] ) : self::$_configs[$settings_name];
		}
		
		return self::missingConfig( $settings_name, false );
	}
	
	/**
	 * Array config for keys 
	 * 
	 * @param  mixed $settings_name, mixed $sub_key
	 * 
	 * @return _configs|$settings_name[$sub_key]
	 */
	public static function getArray( $settings_name, $sub_key = false )
	{
		
		if ( isset( self::$_configs[$settings_name] ) )
		{
			if ( is_serialized( self::$_configs[$settings_name] ) )
			{
				self::$_configs[$settings_name] = castTypes( unserialize( self::$_configs[$settings_name] ) ); 
			}
			
			if ( $sub_key )
			{
				if ( isset( self::$_configs[$settings_name][$sub_key] ) )
				{
					return self::$_configs[$settings_name][$sub_key];
				}
				
				ips_log( 'Missing Config Sub Key: ' . $sub_key . ', settings_name: ' . $settings_name, 'logs/config.log' );
				
				return false;
			}
			elseif ( !$sub_key )
			{
				return self::$_configs[$settings_name];
			}
		}
		
		return self::missingConfig( $settings_name, false );
		
	}
	
	/**
	 * Dla kluczy tablicowanych array()
	 * Tablicowanych array for keys ()
	 * 
	 * @param  mixed $settings_name, mixed $sub_key
	 * 
	 * @return _configs|$settings_name[$sub_key]
	 */
	public static function range( $settings_name, $sub_key = false, $element = false )
	{
		$range = self::get( $settings_name, $sub_key );
		
		if( $range )
		{
			list( $min, $max ) = explode( ',', $range );
			
			if( $element )
			{
				return $element == 'max' ? $max : $min;
			}
			
			return [ $min, $max ];
		}
		
		return null;
	}
	
	/**
	 * Pobieranie ustawienia bezpośrednio z bazy, aby uniknąć omyłkowych nadpisań.
	 * Download directly from the database settings to override avoid by mistake.
	 *
	 * @param  mixed $settings_name, mixed $sub_key
	 * 
	 * @return mixed
	 */
	public static function noCache( $settings_name )
	{
		$setting = PD::getInstance()->select(  'system_settings', array(
			'settings_name' => $settings_name
		), 1, "`settings_name`,`settings_value`,`autoload`" );
		
		if ( isset( $setting['settings_value'] ) )
		{
			
			if ( is_serialized( $setting['settings_value'] ) )
			{
				return castTypes( unserialize( $setting['settings_value'] ) );
			}
			
			return $setting['settings_value'];
		}
		
		return false;
	}
	
	/**
	 * Jeśli z jakiegoś powodu ustawienie nie 
	 * znalazło się w tablicy próbujemy pobrać je z bazy.
	 * If for some reason the setting is not found in the array are trying to retrieve it from the database.
	 *
	 * @param  mixed $settings_name, mixed $sub_key
	 * 
	 * @return mixed
	 */
	private static function missingConfig( $settings_name, $sub_key = false )
	{
		$setting = PD::getInstance()->select( 'system_settings', array(
			'settings_name' => $settings_name
		), 1, "`settings_name`,`settings_value`,`autoload`" );
		
		if ( isset( $setting['settings_value'] ) )
		{
			if ( $setting['autoload'] == 'yes' )
			{
				self::restoreConfig();
			}
			
			if( IPS_DEBUG )
			{
				ips_log( 'Missing Config : ' . $settings_name . $sub_key . ', file: ' . $_SERVER['SCRIPT_FILENAME'] . ', settings_value:' . $setting['settings_value'] . ', settings_autoload:' . $setting['autoload'], 'logs/config.log' );
			}
			
			if ( is_serialized( $setting['settings_value'] ) )
			{
				$setting['settings_value'] = castTypes( unserialize( $setting['settings_value'] ) );
			}
			
			self::$_configs[$setting['settings_name']] = $setting['settings_value'];
			
			return $setting['settings_value'];
		}
		
		return false;
	}
	
	/**
	 * For the keys to be used in the $ _SESSION
	 * 
	 * @param array $config_names
	 * 
	 * @return void
	 */
	public static function setSessionConfig( $config_names )
	{
		if ( is_array( $config_names ) )
		{
			foreach ( $config_names as $name => $value )
			{
				if ( !empty( $name ) )
				{
					self::$_configs[$name] = Session::set( $name, $value );
				}
			}
		}
	}
	
	/**
	 * Public static function restoreConfig
	 * Ponowne wybranie ustawień, restet już zapisanych.
	 * Redial settings restet already stored.
	 * 
	 * @param  null
	 * 
	 * @return void
	 */
	public static function restoreConfig()
	{
		self::$_configs = array();
		new Config( false );
	}
	
	
	
	/**
	 * Tworzenie opcji konfiguracyjnej 
	 * Creating a configuration option
	 * @param string $name - field name
	 * @param string $value - field value
	 * @param bool $autoload - if the field is to be loaded at boot
	 * @return bool
	 */
	public static function createConfig( $name, $value, $autoload = true )
	{
		if ( empty( $name ) )
		{
			return false;
		}
		
		if ( Config::get( $name ) === false )
		{
			if ( is_array( $value ) )
			{
				$value = serialize( $value );
			}
			
			$data = array(
				'settings_name' => $name,
				'settings_value' => (string) $value,
				'autoload' => ( (bool) $autoload == true ? 'yes' : 'no' ) 
			);
			
			PD::getInstance()->insert( 'system_settings', $data );
			
			self::$_configs[$name] = $value;
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Update settings configuration value
	 * @param string|array $setting_value value of the configuration variable
	 *
	 * @param string|bool $settings_name - name of the field to update
	 *
	 * @return bool
	 */
	public static function update( $settings_name, $setting_value, $autoload = true )
	{
		if ( !$settings_name )
		{
			return false;
		}
		
		if ( !empty( $settings_name ) )
		{
			if ( strpos( $settings_name, ',' ) !== false )
			{
				$options = explode( ',', $settings_name );
				
				foreach ( $options as $option )
				{
					self::update( $option, $setting_value, $autoload );
				}
				
				return;
			}
			
			if ( Config::get( $settings_name ) === false )
			{
				return Config::createConfig( $settings_name, $setting_value, $autoload );
			}
			
			if ( is_array( $setting_value ) )
			{
				if ( is_serialized( self::$_configs[$settings_name] ) )
				{
					self::$_configs[$settings_name] = castTypes( unserialize( self::$_configs[$settings_name] ) );
				}
				
				self::$_configs[$settings_name] = array_merge( ( is_array( self::$_configs[$settings_name] ) ? self::$_configs[$settings_name] : array() ), $setting_value );
				
				$setting_value = serialize( self::$_configs[$settings_name] );
			}
			else
			{
				self::$_configs[$settings_name] = $setting_value;
			}
			
			return PD::getInstance()->update( 'system_settings', array(
				'settings_value' => $setting_value 
			), array(
				'settings_name' => $settings_name
			) );
			
		}
		return false;
		
	}
	
	/**
	 * 
	 *
	 * @param string $name - field name
	 * 
	 * @return null
	 */
	public static function tmp( $name, $value = null )
	{
		if ( $value === null )
		{
			return isset( self::$_configs[$name] ) ? self::$_configs[$name] : false;
		}
		
		$loc = &self::$_configs;
		foreach( explode( '.', $name ) as $step )
		{
			$loc = &$loc[$step];
		}
		
		return $loc = $value;
	}
	
	/**
	 * Usuwanie opcji konfiguracyjnej
	 * Deleting a configuration option
	 *
	 * @param string $name - remove the field name
	 * 
	 * @return null
	 */
	public static function remove( $name )
	{
		return PD::getInstance()->delete( 'system_settings', array(
			'settings_name' => $name
		));
	}
	
	/**
	 * Zmiana nazwy opcji konfiguracyjnej
	 * Change the name of the configuration option
	 *
	 * @param string $name - nazwa pola
	 * @param string $from_name - nazwa pola
	 * 
	 * @return null
	 */
	public static function renameConfig( $name, $from_name )
	{
		PD::getInstance()->update( 'system_settings', array(
			'settings_name' => $name 
		), array(
			'settings_name' => $from_name 
		) );
		
	}
	/**
	 * Public static function createAnonymousUser
	 * Tworzenie użytkownika Anonymous umozliwiającego 
	 * ocenianie i dodawanie materiałów, komentarzy bez logowania.
	 * Anonymous User Creation allows evaluating and adding materials, comments without logging in.
	 * 
	 * @param  null
	 * 
	 * @return int $id 
	 */
	public static function createAnonymousUser()
	{
		global ${IPS_LNG};
		$register = new User_Register();
		
		$register->registerUser( array(
			'login' => ${IPS_LNG}['anonymous_login'],
			'email' => Config::get( 'email_admin_user' ),
			'user_birth_date' => date('Y-m-d', strtotime('-18 years') ),
			'date_add' => date( "Y-m-d H:i:s", strtotime( "-1 day" ) ),
			'password' => str_random( 20 )
		), true );
		
		return $register->getUserId();
	}
	/**
	 * Pobieranie ID aninimowego usera
	 * Download ID aninimowego mplayer
	 *
	 * @param bool $return_row - return the entire array of data
	 * 
	 * @return 
	 */
	public static function anonymousInfo()
	{
		
		global ${IPS_LNG};
		$row = PD::getInstance()->select( 'users', array(
			'login' => ${IPS_LNG}['anonymous_login']
		), 1 );
		
		if ( empty( $row ) )
		{
			$id  = Config::createAnonymousUser();
			$row = PD::getInstance()->select( 'users', array(
				'id' => $id 
			), 1 );
		}
		
		return $row;
	}

	public static function timedConfig( $action = 'get', $settings_name, $value = '' )
	{
		if ( $action == 'get' && isset( self::$_configs[$settings_name] ) )
		{
			return self::$_configs[$settings_name];
		}
		elseif ( $action == 'set' )
		{
			self::$_configs[$settings_name] = $value;
		}
		
		return false;
	}
}
?>