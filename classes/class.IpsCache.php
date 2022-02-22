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


final class Ips_Cache
{
	/**
	 * Write cache folder
	 * @var string
	 */
	private static $ips_cache_dir = IPS_CACHE_PATH;
	
	static $ips_cache_dir_temporary = false;
	/**
	 * Cache expiration time
	 *
	 * @var int
	 */
	private static $ips_cache_lifetime = IPS_CACHE_LIFETIME;
	
	/**
	 * Debugowanie
	 *
	 * @var int
	 */
	private static $ips_cache_debug = IPS_CACHE_DEBUG;
	
	
	/**
	 * Downloading the file name Cache
	 *
	 * @param string $cache_id - ID cache
	 *
	 * @return string
	 */
	private static function getFilename( $cache_id )
	{
		/**
		 * $subdir != null 
		 * Cache file in a subdirectory.
		 */
		$subdir = 'all/';
		if ( strpos( $cache_id, '/' ) !== false )
		{
			$subdir   = ltrim( substr( $cache_id, 0, ( strrpos( $cache_id, "/" ) + 1 ) ), '/' );
			$cache_id = substr( $cache_id, ( strrpos( $cache_id, "/" ) + 1 ), strlen( $cache_id ) );
		}
		
		return self::getCacheDir() . '/' . $subdir . $cache_id . '-' . IPS_LNG . IPS_CACHE_EXT;
	}
	
	/**
	 * Clearing the cache file
	 *
	 * @param string $cache_id - ID cache
	 */
	public static function clear( $cache_id )
	{
		$cache_file = self::getFilename( $cache_id );
		
		if ( self::isCached( $cache_file ) )
		{
			unlink( $cache_file );
		}
	}
	
	/**
	 * Delete all cache files or by directory 
	 */
	public static function clearCacheFiles( $subdir = null )
	{
		$files = glob( self::getCacheDir() . '/' . $subdir . "*" . IPS_CACHE_EXT );
		
		if ( !empty( $files ) && is_array( $files ) )
		{
			array_map( 'unlink', $files );
		}
		
		unset( $files );
		
		if ( $subdir == null )
		{
			$files = glob( self::getCacheDir() . '/*', GLOB_ONLYDIR | GLOB_NOSORT );
			
			if ( !empty( $files ) && is_array( $files ) )
			{
				array_map( function( $dir )
				{
					IPS_Cache::clearCacheFiles( basename( $dir ) . '/' );
				}, $files );
			}
			
			unset( $files );
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function cacheExpiry( $cache_lifetime )
	{
		return ( $cache_lifetime ? $cache_lifetime : self::$ips_cache_lifetime );
	}
	/**
	 * Downloading content cache file
	 *
	 * @param mixed $cache_id
	 * @param bool $unserialize - data serialization
	 *
	 * @return string
	 */
	public static function get( $cache_id, $unserialize = false, $cache_lifetime = false )
	{
		/**
		 * Sprawdzenie czy ustawiony został poprawny czas cache.
		 * Check whether you have set the correct time cache.
		 */
		if ( self::cacheExpiry( $cache_lifetime ) > 0 && !USER_ADMIN )
		{
			
			/* Cache with xCache */
			if ( X_Cache::enabled() )
			{
				if ( $file = X_Cache::get( $cache_id ) )
				{
					if ( time() - $file['time'] < self::cacheExpiry( $cache_lifetime ) )
					{
						return $file['content'];
					}
					X_Cache::delete( $cache_id );
				}
				return null;
			}
			$cache_file = self::getFilename( $cache_id );
			/**
			 * Checking if the cache file exists.
			 */
			if ( self::isCached( $cache_file ) )
			{
				/**
				 * Checking if the cache file has not expired.
				 */
				if ( ( time() - filemtime( $cache_file ) ) < self::cacheExpiry( $cache_lifetime ) )
				{
					/* ips_log('Used cached file ' . $cache_file ); */
					/**
					 * Returning cache file.
					 */
					return $unserialize ? unserialize( file_get_contents( $cache_file ) ) : file_get_contents( $cache_file );
					
				}
				
				/**
				 * Deleting a file expired cache file.
				 */
				self::clear( $cache_id );
				
			}
		}
		
		/**
		 * No file cache.
		 */
		return null;
	}
	
	
	/**
	 * Przygotowanie danych do zpisu pliku cache.
	 * Preparation of data to write cache file.
	 *
	 * @param mixed $cache_content
	 * @param string $cache_id - ID cache
	 * @param bool $serialize - data serialization
	 *
	 * @return bool
	 */
	public static function write( $cache_content = null, $cache_id, $serialize = false )
	{
		/**
		 * Check whether you have set the correct time cache.
		 */
		if ( (int) self::$ips_cache_lifetime > 0 )
		{
			/* Cache with xCache */
			if ( X_Cache::enabled() )
			{
				return X_Cache::set( $cache_id, array(
					'time' => time(),
					'content' => $cache_content 
				), self::$ips_cache_lifetime );
			}
			
			/**
			 * We do not store empty cache.
			 */
			if ( empty( $cache_content ) || ( is_array( $cache_content ) && !count( $cache_content ) ) )
			{
				if ( self::$ips_cache_debug )
				{
					trigger_error( "Próba zapisu pustego pliku Cache \"" . self::getCacheDir() . '/' . "(" . __METHOD__ . ")", E_USER_WARNING );
				}
				
				return false;
			}
			
			
			$subdir = null;
			
			if ( strpos( $cache_id, "/" ) === false )
			{
				$cache_id = 'all/' . $cache_id;
			}
			
			$dirs = explode( '/', $cache_id );
			if ( count( $dirs ) > 1 )
			{
				$cache_id_rem = array_pop( $dirs );
			}
			/**
			 * Tworzenie podkatalogów jeśli nie istneją.
			 * Create directories if they do not exist.
			 */
			foreach ( $dirs as $dir )
			{
				if ( $dir !== null )
				{
					if ( !is_dir( self::getCacheDir() . '/' . $subdir . $dir ) )
					{
						mkdir( self::getCacheDir() . '/' . $subdir . $dir );
					}
					$subdir .= $dir . '/';
				}
			}
			
			
			
			/**
			 * Sprawdzamy czy plik posiada prawa do zapisu
			 * Check if a file is writable
			 */
			if ( is_writable( self::getCacheDir() . '/' . $subdir ) )
			{
				// write cache file (with cache header data)
				/**
				 * Save the file cache.
				 */
				return self::writeFile( $cache_content, $cache_id, $serialize );
				
			}
			elseif ( self::$ips_cache_debug )
			{
				trigger_error( "Brak praw do zapisu w katalogu. \"" . self::getCacheDir() . '/' . "\" (" . __METHOD__ . ")", E_USER_WARNING );
			}
		}
	}
	
	
	/**
	 * Sprawdzanie czy plik cache istnieje
	 * Testing whether a file exists cache
	 *
	 * @param mixed $cache_id
	 *
	 * @return bool
	 */
	public static function isCached( $cache_id )
	{
		return file_exists( $cache_id );
	}
	
	/**
	 * Setting the Time Cache
	 *
	 * @param int $ips_cache_lifetime
	 */
	public static function setCacheLifetime( $ips_cache_lifetime = 0 )
	{
		self::$ips_cache_lifetime = (int) $ips_cache_lifetime;
	}
	
	
	/**
	 * Save a file cache
	 *
	 * @param mixed $cache_content
	 * @param string $cache_id - ID cache
	 * @param bool $serialize - data serialization
	 *
	 * @return bool
	 */
	public static function writeFile( $cache_content, $cache_id, $serialize )
	{
		return file_put_contents( self::getFilename( $cache_id ), ( $serialize || is_array( $cache_content ) ? serialize( $cache_content ) : self::cleanHTML( $cache_content ) ) ) ? true : false;
		
	}
	
	/**
	 * Cleaning the cache with useless junk.
	 *
	 * @param mixed $cache_content
	 */
	public static function cleanHTML( $cache_content )
	{
		return $cache_content;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getCacheDir()
	{
		return !empty( self::$ips_cache_dir_temporary ) ? self::$ips_cache_dir_temporary : self::$ips_cache_dir;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function storeDBCache( $cache_hash, $cache_data, $cache_type )
	{
		$cache_hash = substr( abs( crc32( md5( $cache_hash ) ) ), 0, 9 );
		
		if( !is_string( $cache_data ) )
		{
			$cache_data = serialize( $cache_data );
		}
		
		$cache_exists = PD::getInstance()->cnt( 'cached', array(
			'cache_hash' => $cache_hash,
			'cache_type' => $cache_type 
		) );

		if ( !$cache_exists )
		{
			return PD::getInstance()->insert( 'cached', array(
				'cache_stored' => date( "Y-m-d H:i:s" ),
				'cache_hash' => $cache_hash,
				'cache_data' => $cache_data,
				'cache_type' => $cache_type 
			) );
		}
		else
		{
			return PD::getInstance()->update( 'cached', array(
				'cache_data' => $cache_data,
				'cache_stored' => date( "Y-m-d H:i:s" ) 
			), array(
				'cache_hash' => $cache_hash
			) );
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getDBCache( $cache_hash, $cache_type, $return_full = false )
	{
		$cache = PD::getInstance()->select( 'cached', array(
			'cache_hash' => abs( crc32( md5( $cache_hash ) ) ),
			'cache_type' => $cache_type,
		), 1 );
		
		if( $cache )
		{
			if ( ( time() - strtotime( $cache['cache_stored'] ) ) < self::cacheExpiry( false ) )
			{
				return $return_full ? $cache : ( is_serialized( $cache['cache_data'] ) ? unserialize( $cache['cache_data'] ): $cache['cache_data'] ) ;
			}
		}
		return false;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function clearDBCache( $cache_clear )
	{
		if( isset( $cache_clear['cache_hash'] ) )
		{
			$cache_clear['cache_hash'] = abs( crc32( md5( $cache_clear['cache_hash'] ) ) );
		}
		return PD::getInstance()->delete( 'cached', $cache_clear, false );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function img( $img, $info )
	{
		if ( isset( $info['width'] ) )
		{
			$img_path = self::findImg( $img );
		
			if ( $img_path )
			{
				$path = createFolderByDate( IPS_CACHE_IMG_PATH . '/' . $info['width'] . 'x' . has_value( 'height', $info, '' ), dirname( $img ) . '/1', 'path' );
				
				$crop = new Upload_Handler_Extended( null );
				
				list( $width, $height ) = getimagesize( $img_path );
				
				$crop->create_scaled_image( basename( $img ), false, array(
					'max_width' => $info['width'],
					'max_height' => ( isset( $info['height'] ) && $info['height'] > 0 ? $info['height'] : $height ),
					'file_path_ips' => $img_path,
					'file_path_save' => $path . '/' . basename( $img ),
					'crop' => true,
					'jpeg_quality' => 100,
					'upload_dir' => $path . '/' 
				) );
				
				return $path . '/' . basename( $img );
			}
		}
		
		return false;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function findImg( $img )
	{
		$img_path = ips_img_path( $img, 'large' );
		
		if ( is_file( $img_path ) )
		{
			return $img_path;
		}
		
		$img_path_bck = ips_img_path( $img, 'backup' );
			
		if ( is_file( $img_path_bck ) )
		{
			return $img_path_bck;
		}
			
		$img_path = ABS_PATH . '/upload/' . trim( $img );
		
		if ( is_file( $img_path ) )
		{
			return $img_path;
		}
		
		return false;
	}
}
?>