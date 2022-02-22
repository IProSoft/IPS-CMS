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

/**
 *
 * Autloader jest odpowiedzialny za ładowanie klas PHP. Pozwala na zdefiniowanie
 * ścieżki pobierania plików klas bez potrzeby includowania ich w skrypcie.
 * Autloader is responsible for loading the PHP classes. Allows you to define the path to download class files without the need includowania them in the script.
 */
class Ips_Autoloader
{
	/**
	 * Class files
	 *
	 * @var array
	 */
	private static $_class_files = array();
	/**
	 * Loaded classes
	 *
	 * @var array
	 */
	private static $_loaded_files = array();
	
	/**
	 * Funkcja wywołująca załadowanie pliku klasy
	 * The calling function to load the class file
	 *
	 * @param $class - caused by class name
	 * 
	 * @return void
	 */
	static public function loadClass( $class )
	{
		if( empty( self::$_class_files ) )
		{
			self::$_class_files = self::getClasses();
		}
		
		$class_file = 'class.' . str_replace( '_', '', $class ) . '.php';
		
		if( isset( self::$_class_files[ str_replace( '_', '', $class ) . '.php' ] ) )
		{
			require self::$_class_files[ str_replace( '_', '', $class ) . '.php' ];
		}
		elseif( isset( self::$_class_files[$class_file] ) )
		{
			require self::$_class_files[$class_file];
		}
		elseif( strpos( $class, '\\' ) !== false )
		{
			return;
		}
		elseif( defined('USER_ADMIN') && USER_ADMIN && file_exists( IPS_ADMIN_PATH . '/libs/' . $class_file ) )
		{
			require IPS_ADMIN_PATH .'/libs/' . $class_file;
		}
		else
		{
			if( !file_exists( CACHE_PATH . '/cache.autoload.php' ) )
			{
				return false;
			}
			return unlink( CACHE_PATH . '/cache.autoload.php' );
		}
		
		self::$_loaded_files[] = $class_file;
	}
	
	/**
	 * Funkcja rejestrująca klasę Ips_Loader jako SPL autoloader.
	 * Ips_Loader recording feature class as the SPL autoloader.
	 *
	 * @param $class - caused by class name
	 * 
	 * @return void
	 */
	static public function register()
	{
		spl_autoload_register( array(
			__CLASS__,
			'loadClass' 
		) );
	}
	/**
	 * Returns the loaded class files
	 *
	 * @return array
	 */
	static public function getLoadedFiles()
	{
		return self::$_loaded_files;
	}
	
	/**
	 * Returns the class files
	 *
	 * @return array
	 */
	static public function getClasses( $path = CLASS_PATH )
	{
		if( file_exists( CACHE_PATH . '/cache.autoload.php' ) )
		{
			return include_once( CACHE_PATH . '/cache.autoload.php' );
		}
		/** Most optimized way **/
		$files = array_merge( 
			glob( $path . '/class.*' ), 
			glob( $path . '/pinit/class.*' ), 
			glob( $path . '/upload/class.*' ), 
			glob( $path . '/controllers/*' ), 
			glob( $path . '/interface/*' )
		);
		
		$classes = array();
		
		foreach( $files as $file )
		{
			$classes[basename($file)] = $file;
		}
		
		file_put_contents( CACHE_PATH . '/cache.autoload.php' , '<?php return ' . var_export( $classes, true ) . ' ?>', LOCK_EX );

		return $classes;
		
		/* $files = new RecursiveIteratorIterator( 
			new RecursiveDirectoryIterator( realpath( $path ), 
			RecursiveDirectoryIterator::SKIP_DOTS )
		); */
	}
	
	static public function call( $class, $function, $args = array() )
	{
		$object = new $class();
		
		if( is_callable( array( $object, $function ) ) )
		{
			return call_user_func_array( array( 
				$object, $function
			), $args );
		}
		
		return false;
	}
}
?>