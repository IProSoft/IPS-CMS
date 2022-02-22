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

class Translate
{
	
	public $langCode = false;
	
	private static $lang_instance = null;
	/**
	 * Konstruktor sprawdza jaki został ustawiony domyślny język i przypisuje do stałej
	 * Builder checks which has been set default language and attributed to the constant
	 *
	 * @param null
	 * 
	 * @return  null
	 */
	public function __construct()
	{
		self::$lang_instance = $this;
		
		if ( Cookie::exists( 'ips_language' ) )
		{
			$iso_lang  = trim( Cookie::get( 'ips_language' ) );
			$languages = Translate::codes();
			
			foreach ( $languages as $lang )
			{
				if ( $lang === $iso_lang )
				{
					$this->langCode = $lang;
				}
			}
		}
		
		unset( $languages, $iso_lang );
		
		if ( empty( $this->langCode ) )
		{
			$this->langCode = Config::getArray( 'language_settings', 'default_language' );
		}
		if ( !defined( 'IPS_LNG' ) )
		{
			define( 'IPS_LNG', $this->langCode );
			define( 'IPS_CACHE_PHP', 'cache_lang_' . strtolower( $this->langCode ) . '.php' );
			define( 'IPS_CACHE_JS', 'cache_lang_' . strtolower( $this->langCode ) . '.js' );
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getInstance()
	{
		if ( !isset( self::$lang_instance ) or self::$lang_instance === null )
		{
			self::$lang_instance = new Translate();
		}
		return self::$lang_instance;
	}
	/**
	 * Pobieranie wszystkich tłumaczeń z pliku cache lub bezpośrednio z bazy + cachowanie
	 * Download all translations from the cache file or directly from the database + caching
	 *
	 * @param null
	 * 
	 * @return array
	 */
	public function getTranslations()
	{
		
		$lang = $this->getCachedTranslations();
		
		if ( empty( $lang ) )
		{
			$phrases = PD::getInstance()->select( "translations", array(
				'language' => $this->langCode 
			), 0, "translation_name, translation_value" );
			
			foreach ( $phrases as $key => $value )
			{
				$lang[$value['translation_name']] = stripslashes( $value['translation_value'] );
			}
			
			$this->preSprintf( $lang );
			
			file_put_contents( CACHE_PATH . '/' . IPS_CACHE_PHP, "<?php\nreturn " . var_export( $lang, true ) . ";" );
		}
		
		if ( ( USER_MOD === true || USER_ADMIN === true ) || defined( 'IPS_CRON' ) )
		{
			return array_merge( $lang, $this->getAdminTranslations() );
		}
		
		return $lang;
	}
	
	/**
	 * Download all translations from the file cache
	 *
	 * @param null
	 * 
	 * @return array
	 */
	public function preSprintf( &$lang )
	{
		$lang['user_login_remember'] = sprintf( $lang['user_login_remember'], Config::get('user_account', 'login_cookie_remember_time') );
	}
	
	/**
	 * Download all translations from the file cache
	 *
	 * @param null
	 * 
	 * @return array
	 */
	public function getCachedTranslations()
	{
		
		if ( file_exists( CACHE_PATH . '/' . IPS_CACHE_PHP ) )
		{
			$translations = include( CACHE_PATH . '/' . IPS_CACHE_PHP );
			if ( is_array( $translations ) )
			{
				return $translations;
			}
		}
		
		return false;
	}
	/**
	 * Load admin only translations
	 *
	 * @param null
	 * 
	 * @return array
	 */
	public function getAdminTranslations()
	{
		
		$lang_file = IPS_ADMIN_PATH .'/language/lang-' . substr( Config::get('admin_lang_locale'), 0, 2 ) . '.php';
		
		if ( !file_exists( $lang_file ) )
		{
			$lang_file = IPS_ADMIN_PATH .'/language/lang-pl.php';
		}
		
		$translations = include_once( $lang_file );
		
		if ( is_array( $translations ) )
		{
			return $translations;
		}
		
		return array();
	}
	
	/**
	 * Merge admin translations with normal
	 *
	 * @param null
	 * 
	 * @return array
	 */
	public static function loadAdminTranslations()
	{
		global ${IPS_LNG};
		$lang = new Translate();
		
		${IPS_LNG} = array_merge( ${IPS_LNG}, $lang->getAdminTranslations() );
	}
	
	/**
	 * Pobieranie i cachowanie tłumaczeń dla plików JS
	 * Wszystkie z przedrostkiem js_, dodatkowe w kluzuli IN()
	 * Downloading and caching translations for all JS files with the prefix js_, additional kluzuli IN ()
	 * @param null
	 * 
	 * @return string nazwa pliku
	 */
	public function getJsLang()
	{
		if ( !file_exists( CACHE_PATH . '/' . IPS_CACHE_JS ) )
		{
			$phrases = PD::getInstance()->from( 'translations' )->setWhere( array(
				'language' => $this->langCode,
				'translation_name' => array('^js_', 'REGEXP')
			))->fields('translation_name, translation_value')->get();
			

			foreach ( $phrases as $key => $value )
			{
				$phrases[ $key] = "\t'" . $value['translation_name'] . "' : '" . addcslashes( stripslashes( $value['translation_value'] ), "'" ) . "'";
			}
			
			File::put( CACHE_PATH . '/' . IPS_CACHE_JS, '/** Modified : ' . date( "Y-m-d H:i:s" ) . ' **/' . "\n" . "function ips_i18n (translations) {\n\tthis.translations = translations;\n}\n\nips_i18n.prototype.__ = function ( name ) {\n\treturn this.translations[name];\n};\nvar ips_i18n = new ips_i18n({\n" . implode( ",\n", $phrases ) . "\n})" );
		}
		
		return 'cache/' . IPS_CACHE_JS;
	}
	/**
	 * Clearing the cache
	 *
	 * @param null
	 * 
	 * @return void
	 */
	public function clearLangCache()
	{
		array_map( "unlink", glob( CACHE_PATH . '/cache_lang_*', GLOB_NOSORT ) );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setLanguageForm()
	{
		$languages = Translate::codes();
		
		$links = '
		<form id="change_language" action="" method="post">
			<select name="change_language">
			';
			foreach ( $languages as $lang )
			{
				$links .= '<option value="' . $lang . '"' . ( $lang === $this->langCode ? ' selected="selected"' : '' ) . '>' . $lang . '</option>';
			}
			return $links . '
			</select>
		</form>';
		
	}

	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function codes()
	{
		return array_keys( Config::getArray( 'language_settings', 'language_locales' ) );
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function available( $lang )
	{
		return in_array( $lang, self::codes() );
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function set( $language_code )
	{
		Cookie::clear( 'ips_language' );

		if( $language_code )
		{
			$languages = Translate::codes();	
			
			foreach( $languages as $lang )
			{
				if( $lang === $language_code )
				{
					return Cookie::set( 'ips_language', $lang );
				}
			}
		}

		Cookie::set( 'ips_language', IPS_LNG );	
		
		return false;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function translate( $input )
	{
		$input = preg_replace_callback( "|{lang=(.+)}|U", function( $matches ){
			
			global ${IPS_LNG}; 
			
			if( !isset( ${IPS_LNG}[ $matches[1] ] ) )
			{
				if( IPS_DEBUG )
				{
					__log( $matches[1] );
				}
				
				return $matches[1];
			}
			
			return ${IPS_LNG}[$matches[1]];
			
		}, $input );
		
		return $input;
	}
}
?>