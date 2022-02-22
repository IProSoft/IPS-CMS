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


class File
{
	
	public $file = false;
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function __contruct()
	{
	}
	
	/**
	 * Sprawdzanie czy plik istnieje.
	 * Testing whether a file exists.
	 *
	 * @param $file - address file
	 * 
	 * @return bool|Exception
	 */
	static function checkFile( $file )
	{
		if ( !file_exists( $file ) )
		{
			throw new Exception( 'Plik nie został znaleziony: ' . $file );
		}
		
		return true;
	}
	
	/**
	 * Sprawdzanie czy plik mozna odczytać i/lub zapisać.
	 * Checking whether a file can be read and / or write.
	 * @param $file - address file
	 * @param $action - Write or read a file
	 * 
	 * @return bool|Exception
	 */
	static function isAccessible( $file, $action = 'write' )
	{
		
		self::checkFile( $file );
		
		switch ( $action )
		{
			case 'read':
				
				if ( !is_readable( $file ) )
				{
					throw new Exception( 'Plik nie może zostać odczytany: ' . $file );
				}
				
				break;
			case 'write':
				
				if ( !is_writable( $file ) )
				{
					throw new Exception( 'Plik nie może zostać zapisany: ' . $file );
				}
				
				break;
		}
		
		return true;
	}
	/**
	 * Saving the contents of the file from URL.
	 *
	 * @param $file - address file
	 * @param $content - file url
	 * 
	 * @return bool|Exception
	 */
	static function putUrl( $url, $file, $flag = 'put' )
	{
		$content = curlIPS( $url, array(
			'timeout' => 10, 
			'refferer' => 'http://' . parse_url( $url, PHP_URL_HOST ) 
		) );
		
		if( self::put( $file, $content, $flag ) )
		{
			return $file;
		};
		
		return false;
	}
	/**
	 * Saving the contents of the file.
	 *
	 * @param $file - address file
	 * @param $content - file content
	 * 
	 * @return bool|Exception
	 */
	static function put( $file, $content, $flag = 'put' )
	{
		$dir = dirname( $file );
		if( !is_dir( $dir ) )
		{
			mkdir( $dir, 0777 );
			ips_log( 'Dir not exists, created: ' . $dir );
		}
		if( file_exists( $file ) )
		{
			chmod( $file, 0777 );
		}
		
		
		if ( !$fp = fopen( $file, ( $flag == 'append' ? 'a' : 'w' ) ) )
		{
			throw new Exception( 'File can\'t be saved: ' . $file );
		}
		
		fwrite( $fp, $content );
		fclose( $fp );
		
		chmod( $file, 0755 );
		$content = null;
		
		return true;
	}

	/**
	 * file copy
	 *
	 * @param $file_from - file to copy
	 * @param $file_to - target file
	 * 
	 * @return bool|Exception
	 */
	static function copyFile( $file_from, $file_to )
	{
		$content = self::read( $file_from );
		
		if ( $content )
		{
			return self::put( $file_to, $content );
		}
		throw new Exception( 'Plik nie może zostać zapisany: ' . $file_to );
	}
	/**
	 * file move
	 *
	 * @param $file_from - file to copy
	 * @param $file_to - target file
	 * 
	 * @return bool|Exception
	 */
	static function move( $file_from, $file_to )
	{
		try{
			
			if ( self::isAccessible( $file_from, 'read' ) )
			{
				return rename( $file_from, $file_to);
			}
		
		}catch( Exception $e ){}
		
		return false;
	}
	/**
	 * reading a file
	 *
	 * @param $file - address file
	 * 
	 * @return bool|Exception
	 */
	static function read( $file )
	{
		if ( self::isAccessible( $file, 'read' ) )
		{
			return file_get_contents( $file );
		}
		return false;
	}
	
	
	/**
	 * Utworzenie i próba zapisu pliku
	 * Creating and attempt to save the file
	 *
	 * @param $file - adres pliku
	 * 
	 * @return bool|Exception
	 */
	static function create( $file, $content )
	{
		if ( $handle = @fopen( $file, 'w+' ) )
		{
			if ( fwrite( $handle, $content ) !== false )
			{
				fclose( $handle );
				@chmod( $file, 0775 );
				return true;
			}
		}
		
		throw new Exception( 'Plik nie może zostać zapisany: ' . $file );
	}
	
	/**
	 * Editing content in a file
	 *
	 * @param $file - address file
	 * @param $search - table for substitution
	 * @param $replace - an array of substitutions
	 * 
	 * @return bool|Exception
	 */
	static function replaceInFile( $file, array $search, array $replace )
	{
		self::isAccessible( $file, 'read' );
		$content = self::read( $file );
		
		if ( $content )
		{
			$content = str_replace( $search, $replace, $content );
			self::put( $file, $content );
			$content = null;
			
			return true;
		}
		throw new Exception( 'Empty file content while replace' );
	}
	/**
	 * Delete file with check of permissions
	 *
	 * @param $file_path
	 * 
	 * @return bool
	 * File::deleteDir( ABS_PATH . '/folder/folder2/' . $file )
	 */
	static function deleteFile( $file_path )
	{
		if ( is_file( $file_path ) )
		{
			@chmod( $file_path, 0777 );
			return @unlink( $file_path );
		}
		return false;
	}
	
	/**
	 * Delete files with check of permissions
	 *
	 * @param $files_path
	 * @param $match
	 * @param $file_size
	 * 
	 * @return bool
	 * File::deleteDir( ABS_PATH . '/folder/folder2/' )
	 */
	static function deleteFiles( $files_path, $file_size = true, $recursive = false, $match = false )
	{
		if ( substr( $files_path, -1 ) == '/' )
		{
			$files_path = substr( $files_path, 0, -1 );
		}
		
		$file_size = $file_size ? function_exists( 'checkSize' ) : false;
		if ( is_dir( $files_path . '/' ) )
		{
			if ( $handle = opendir( $files_path . '/' ) )
			{
				while ( false !== ( $file = readdir( $handle ) ) )
				{
					if ( $file != '.' && $file != '..' )
					{
						if ( is_file( $files_path . '/' . $file ) )
						{
							if ( $match && !preg_match( '~' . $match . '~imu', $file ) )
							{
								continue;
							}
							
							if ( $file_size )
							{
								checkSize( $files_path . '/' . $file );
							}
							else
							{
								self::deleteFile( $files_path . '/' . $file );
							}
						}
						elseif ( is_dir( $files_path . '/' . $file ) )
						{
							self::deleteFiles( $files_path . '/' . $file, $file_size, $recursive, $match );
						}
					}
				}
				closedir( $handle );
			}
		}
		
	}
	
	/**
	 * Delete folder with files
	 *
	 * @param $path
	 * 
	 * @return bool
	 * File::deleteDir( ABS_PATH . '/folder/folder2')
	 */
	static function deleteDir( $path )
	{
		if ( substr( $path, -1 ) == '/' )
		{
			$path = substr( $path, 0, -1 );
		}
		
		$class_func = array(
			__CLASS__,
			__FUNCTION__ 
		);
		
		$files = glob( $path . '/*', GLOB_NOSORT );
		
		if ( !empty( $files ) )
		{
			array_map( $class_func, $files );
		}
		
		return is_file( $path ) ? @unlink( $path ) : ( is_dir( $path ) ? @rmdir( $path ) : true );
	}
	/**
	 * Create empty directory
	 *
	 * @param $path
	 * 
	 * @return bool
	 * File::deleteDir( ABS_PATH . '/folder/folder2')
	 */
	static function createDir( $path, $chmod = 0775 )
	{
		if ( !mkdir( rtrim( rtrim( $path, '\\' ), '/' ), $chmod, true ) )
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Copy entire directory
	 *
	 * @param $path
	 * 
	 * @return bool
	 * File::copyDir( ABS_PATH . '/folder/folder', ABS_PATH . '/folder/folder_new' )
	 */
	static function copyDir( $source_path, $dest_path, $chmod = 0775 )
	{
		if ( !file_exists( $source_path ) )
		{
			throw new Exception( 'Source path not exists' );
		}
		
		if ( substr( $source_path, -1 ) == '/' )
		{
			$source_path = substr( $source_path, 0, -1 );
		}
		
		if ( substr( $dest_path, -1 ) == '/' )
		{
			$dest_path = substr( $dest_path, 0, -1 );
		}
		
		if ( is_link( $source_path ) )
		{
			return symlink( readlink( $source_path ), $dest_path );
		}
		
		if ( is_file( $source_path ) )
		{
			return copy( $source_path, $dest_path );
		}
		
		if ( !is_dir( $dest_path ) )
		{
			if ( !mkdir( $dest_path, $chmod, true ) )
			{
				throw new Exception( 'Can\'t create folder' );
			}
		}
		
		$dir = dir( $source_path );
		
		while ( false !== $entry = $dir->read() )
		{
			
			if ( $entry == '.' || $entry == '..' )
			{
				continue;
			}
			
			File::copyDir( $source_path . '/' . $entry, $dest_path . '/' . $entry );
		}
		
		$dir->close();
		
		return true;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function search( $path, $strpos = false, $files = array() )
	{
		$objects = new RecursiveIteratorIterator(
								   new RecursiveDirectoryIterator( trim( $path, '/' ) . '/' ), 
								   RecursiveIteratorIterator::SELF_FIRST);
	
		foreach( $objects as $name => $object )
		{
			if( !$strpos || strpos( $object->getPathname(), $strpos ) !== false )
			{
				$files[] = $object->getPathname();
			}
		}
		
		return $files;
	}
}
?>