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


class Upload_Single_File
{
	
	public $_file = false;
	private $demensions = array();
	public $_mime_type = false;
	
	const IMAGETYPE_GIF = 'image/gif';
	const IMAGETYPE_JPEG = 'image/jpeg';
	const IMAGETYPE_PNG = 'image/png';
	const IMAGETYPE_JPG = 'image/jpg';
	const IMAGETYPE_MP4 = 'video/mp4';
	const IMAGETYPE_ICO = 'image/x-icon';
	const IMAGETYPE_ICO_2 = 'image/vnd.microsoft.icon';
	const IMAGETYPE_SWF = 'application/x-shockwave-flash';
	
	private $upload_subtype = false;
	
	private $curl_options = array( 
		'timeout' => 30, 
		'other' => array(
			//52 => true,
			68 => 3, 
			105 => true, 
			78 => 30, 
			92 => 30
		) 
	);
	//CURLOPT_FOLLOWLOCATION => true,
	//52 => true
	//CURLOPT_MAXREDIRS => 3,
	//68 => 3,	
	//CURLOPT_UNRESTRICTED_AUTH => true,
	//105 => true,
	//CURLOPT_CONNECTTIMEOUT => 5,
	//78 => 30,
	//CURLOPT_DNS_CACHE_TIMEOUT => 30,
	//92 => 30,
	//CURLOPT_TIMEOUT => 30,

	/* 
	 * Add settings for uploading the chosen method.
	 */
	public function setConfig( $config )
	{
		$config = array_merge( array( 
			'files' => '', 
			'post' => '' ,
			'url' => ''
		), $config );
		
		$file = has_value( $config['files'], $_FILES, false );
		if ( $file && !empty( $file['tmp_name'] ) )
		{
			return $this->setFile( $file, $file['name'] );
		}
		
		if ( $post_file = has_value( $config['post'], $_POST, false ) )
		{
			return $this->setFile( $post_file, $post_file );
		}
		
		if ( !empty( $config['url'] ) )
		{
			return $this->setFile( $config['url'], $config['url'] );
		}
		
		if ( !defined( 'IPS_EDIT_FILE' ) || !IPS_EDIT_FILE )
		{
			ips_log( ips_backtrace(), 'logs/upload.log' );
			throw new Exception( 'add_upload_no_file' );
		}
		
		return $this;
	}
	/* 
	 * Set file variable
	 */
	public function setFile( $content, $name )
	{
		$this->_file = $content;
		
		$file_data = ips_pathinfo( basename( $name ) );	
		
		if ( has_value( 'extension', $file_data ) === 'mp4' )
		{
			$this->set_ext = 'mp4';
			$this->_mime_type = self::IMAGETYPE_MP4;
		}
		
		if ( has_value( 'extension', $file_data ) === 'swf' )
		{
			$this->_mime_type = self::IMAGETYPE_SWF;
		}
		
		return $this;
	}
	/**Set uploaded file name
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setFileName( $file_name = null )
	{
		$this->name = $file_name ? $file_name : str_random( 10 );
		
		return $this;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function Load( $file_path, $settings = false )
	{
		if ( empty( $this->_file ) && ( defined( 'IPS_EDIT_FILE' ) && IPS_EDIT_FILE ) )
		{
			return;
		}
		
		$this->_file = $this->setImage( $this->_file );
		
		if ( isset( $settings['allowed-extensions'] ) && $settings['allowed-extensions'] && !in_array( $this->getExtension(), $settings['allowed-extensions'] ) )
		{
			throw new Exception( 'err_ico_extension' );
		}
		
		$this->storeFile( $file_path );
		
		$info = $this->getDimensions();
		
		if ( $settings && isset( $info['width'] ) && !in_array( $this->extension, array( 'swf', 'mp4' ) ) )
		{
			$resize = null;
			
			if( isset( $settings['resize'] ) )
			{
				$resize = $settings['resize'];
			}
			elseif( isset( $settings['max_width'] ) && $settings['max_width'] < $info['width'] )
			{
				$resize = $settings['max_width'];
			}
			
			if ( $resize )
			{
				$this->resizeIMG( $resize );
			}
		}
		
		if ( $ext = $this->checkExtension( $settings ) )
		{
			$this->changeExt( $ext );
		}
		
		$this->checkSize( $this->_filesize );
		
		unset( $this->_file );
		
		$file = array(
			'width' => $info['width'],
			'height' => $info['height'],
			'extension' => $this->extension,
			'filesize' => $this->_filesize,
			'name' => isset( $this->name ) ? $this->name : basename( $this->fileName ) 
		);
		
		if( $this->upload_subtype )
		{
			$file['upload_subtype'] = $this->upload_subtype;
		}
		
		return $file;
	}
	/** Check if file has allowed extension */
	public function checkExtension( $settings )
	{
		if( is_array( $settings ) && isset( $settings['extension'] ) )
		{
			if( !is_array( $settings['extension'] ) )
			{
				$settings['extension'] = array( $settings['extension'] );
			}
			
			return in_array( $this->extension, $settings['extension'] ) ? false : current( $settings['extension'] );
		}
		return false;
	}
	/*
	 * Save the image generated in the correct path.
	 */
	public function storeFile( $file_path )
	{
		$this->extension = $this->getExtension();
		
		$file = $file_path . '.' . $this->extension;
		
		if ( $fp = fopen( $file, 'w' ) )
		{
			//file_put_contents( $file, $this->_file );
			fwrite( $fp, $this->_file );
			fclose( $fp );
			if ( $this->_mime_type == self::IMAGETYPE_GIF )
			{
				if ( $this->isAnimatedGif( $file ) )
				{
					$this->upload_subtype = 'animation';
				}
			}
			$this->_filesize = filesize( $file );
			$this->fileName    = $file;
		}
		else
		{
			throw new Exception( 'Błąd otwarcia pliku.' );
		}
		
		return basename( $file );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getExtension()
	{
		if ( isset( $this->extension ) && !empty( $this->extension ) )
		{
			return $this->extension;
		}
		
		if ( !isset( $this->_mime_type ) )
		{
			throw new Exception( 'MIME ERROR' );
		}
		
		switch ( $this->_mime_type )
		{
			case self::IMAGETYPE_JPEG:
			case self::IMAGETYPE_JPG:
				return 'jpg';
			break;
			case self::IMAGETYPE_GIF:
			case self::IMAGETYPE_PNG:
				return substr( strtolower( $this->_mime_type ), 6 );
			break;
			case self::IMAGETYPE_SWF:
				$this->upload_subtype = 'video';
				return 'swf';
			break;
			case self::IMAGETYPE_ICO:
			case self::IMAGETYPE_ICO_2:
				return 'ico';
			break;
			case self::IMAGETYPE_MP4:
				return 'mp4';
			break;
			default:
				
				if ( in_array( $this->_mime_type, array(
					'application/octet-stream', 
				) ) && isset( $this->set_ext ) )
				{
					return $this->set_ext;
				}
				else
				{
					if ( !USER_ADMIN )
					{
						ips_log( $this, 'logs/upload.log' );
						throw new Exception( 'err_file_extension' );
					}
				}
			break;
		}
	}
	/*
	 * Set the info about the picture
	 */
	public function getDimensions()
	{
		$dimensions = array(
			'width' => false,
			'height' => false 
		);
		
		if ( $file = getimagesize( $this->fileName ) )
		{
			list( $dimensions['width'], $dimensions['height'] ) = $file;
		}
		elseif ( is_resource( $this->_file ) && $file = @imagecreatefromstring( $this->_file ) )
		{
			$dimensions['width']  = imagesx( $file );
			$dimensions['height'] = imagesy( $file );
		}
		
		return $dimensions;
	}
	/*
	 * Check the file size
	 */
	private function checkSize( $size )
	{
		if ( !defined('IPS_ADMIN_PANEL') && $size > Config::get( 'add_max_file_size' ) * 1048576 )
		{
			throw new Exception( 'err_file_size' );
		}
	}
	/**
	 * Grab the image depending on the server, or upload link
	 */
	public function setImage( $image )
	{
		
		unset( $this->_file );
		//table $_FILE
		if ( is_array( $image ) )
		{
			$this->checkSize( $image['size'] );
			if ( $image['error'] == '2' || $image['error'] == '1' )
			{
				throw new Exception( 'err_file_size' );
			}
			$this->_mime_type = $this->getMimeType( $image['tmp_name'] );
			
			return file_get_contents( $image['tmp_name'] );
		}
		/*
		 * File from the link, download and pay
		 */
		elseif ( preg_match( '|^http(s)?://(.*)?$|i', $image ) )
		{
			if ( !in_array( 'curl', get_loaded_extensions() ) )
			{
				throw new Exception( "Biblioteka CURL nie została znaleziona!" );
			}
			
			/*
			if( !check_image_url( $image ) )
			{
			throw new Exception( 'err_uploaded_file');
			}
			*/

			$this->curl_options['refferer'] = 'http://' . parse_url( $image, PHP_URL_HOST );
			$this->curl_options['timeout']  = 30;
			$data                           = curlIPS( $image, $this->curl_options );
			if ( class_exists( 'finfo' ) )
			{
				$file_info = new finfo( FILEINFO_MIME );
				list( $mime_type ) = explode( ";", $file_info->buffer( $data ) );
			}
			else
			{
				$temp_file = array_search( 'uri', @array_flip( stream_get_meta_data( $GLOBALS[mt_rand()] = tmpfile() ) ) );
				file_put_contents( $temp_file, $data );
				$mime_type = $this->getMimeType( $temp_file );
				if ( is_file( $temp_file ) && file_exists( $temp_file ) && is_writable( $temp_file ) )
				{
					@unlink( $temp_file );
				}
			}
			
			if ( empty( $data ) )
			{
				throw new Exception( 'err_uploaded_file' );
			}
			
			$this->_mime_type = $mime_type;
			
			return $data;
			
		} /* // We ask if it is already assigned
		elseif( is_object( $image ) && getCLASS_PATH( $image ) == "Upload_Single_File" ) 
		return $image->getImage();
		//GD image as an object
		elseif( @imagesx($image) > 0 )
		return $image; */ 
		// File from the server
		elseif ( is_file( $image ) )
		{
			
			$this->_mime_type = $this->getMimeType( $image );
			
			return file_get_contents( $image );
		}
		elseif ( is_file( IMG_PATH_BACKUP . '/' . $image ) )
		{
			$this->_mime_type = $this->getMimeType( IMG_PATH_BACKUP . '/' . $image );
			
			return file_get_contents( IMG_PATH_BACKUP . '/' . $image );
		}
		elseif ( is_file( ABS_PATH . $image ) )
		{
			$this->_mime_type = $this->getMimeType( ABS_PATH . $image );
			return file_get_contents( ABS_PATH . $image );
		}
		else
		{
			throw new Exception( 'err_uploaded_file' );
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getMimeType( $file )
	{
		if ( $this->_mime_type )
		{
			return $this->_mime_type;
		}
		
		$imagetype = exif_imagetype( $file );
		
		if( !$imagetype )
		{
			throw new Exception( "Picture does not match any retrieval scheme!" );
		}
		
		return image_type_to_mime_type( $imagetype );
	}
	
	/** 
	 * Save as another EXT
	 *
	 */
	public function changeExt( $extension )
	{
		$image = Gd::as_resource( $this->fileName );
		
		unlink( $this->fileName );
		
		$fileName = str_replace( '.' . $this->extension, '.' . $extension, $this->fileName );

		switch ( $extension )
		{
			case 'jpg':
				imagejpeg( $image, $fileName, Config::get( 'images_compress', 'jpg' ) );
			break;
			case 'png':
				imagepng( $image, $fileName, Config::get( 'images_compress', 'png' ) );
			break;
			case 'gif':
				imagegif( $image, $fileName );
			break;
			default:
				return false;
			break;
		}
		
		$this->extension = $extension;
		
		return $this->fileName = $fileName;
	}
		/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function simpleResizeImage( $containerIMG, $width )
	{
		
		if ( !is_resource( $containerIMG ) )
		{
			return $containerIMG;
		}
		
		$resize_width  = $width;
		$resize_height = ( $resize_width / imagesx( $containerIMG ) ) * imagesy( $containerIMG );
		
		return $this->cropImage( $containerIMG, $resize_width, $resize_height );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function cropImage( $imageResource, $resize_width, $resize_height = false )
	{
		if ( !is_resource( $imageResource ) )
		{
			$imageResource = Gd::as_resource( $imageResource );
		}
		
		if( $resize_width == imagesx( $imageResource ) && !$resize_height )
		{
			return $imageResource;
		}
		
		if ( !$resize_height )
		{
			$resize_height = ( $resize_width / imagesx( $imageResource ) ) * imagesy( $imageResource );
		}
		
		if ( !$resize_width )
		{
			$resize_width = ( $resize_height / imagesy( $imageResource ) ) * imagesx( $imageResource );
		}
		
		$img = imagecreatetruecolor( $resize_width, $resize_height );
		/* 
		imagealphablending( $img, false );
		imagesavealpha( $img, true );
		Problem with demot and border
		 */
		imagecopyresampled( $img, $imageResource, 0, 0, 0, 0, $resize_width, $resize_height, imagesx( $imageResource ), imagesy( $imageResource ) );
		
		return $img;
	}

	
	/** 
	 * Resizing the image resource type
	 *
	 */
	public function resizeIMG( $new_width )
	{
		if ( !is_numeric( $new_width ) || $this->upload_subtype == 'animation' )
		{
			return false;
		}
		
		$image  = Gd::as_resource( $this->fileName );
		$width  = imagesx( $image );
		$height = imagesy( $image );
		
		$new_height = floor( $new_width * ( $height / $width ) );
		
		$tmp = imagecreatetruecolor( $new_width, $new_height );
		
		imagecopyresampled( $tmp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		
		switch ( $this->extension )
		{
			case 'jpeg':
			case 'jpg':
				imagejpeg( $tmp, $this->fileName, Config::get( 'images_compress', 'jpg' ) );
			break;
			case 'png':
				imagepng( $tmp, $this->fileName, Config::get( 'images_compress', 'png' ) );
			break;
			case 'gif':
				imagegif( $tmp, $this->fileName );
			break;
			default:
				return false;
			break;
		}
	}
	/** 
	 * We ask Picture
	 *
	 * @return  Resource Picture
	 */
	private function getImage()
	{
		if ( !$this->isLoaded() )
		{
			throw new Exception( 'Brak pliku do zwrócenia.' );
		}
		
		return $this->file;
	}
	
	/** 
	 * Check if the image is loaded
	 *
	 * @return Boolean
	 */
	private function isLoaded()
	{
		return ( $this->file != null );
	}
	/**
	 * Original snippet http://www.php.net/manual/en/function.imagecreatefromgif.php#104473
	 **/
	private function isAnimatedGif( $file )
	{
		if ( !( $fh = @fopen( $file, 'rb' ) ) )
			return false;
		$count = 0;
		//an animated gif contains multiple "frames", with each frame having a
		//header made up of:
		// * a static 4-byte sequence (\x00\x21\xF9\x04)
		// * 4 variable bytes
		// * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)
		
		// We read through the file til we reach the end of the file, or we've found
		// at least 2 frame headers
		while ( !feof( $fh ) && $count < 2 )
		{
			$chunk = fread( $fh, 1024 * 100 ); //read 100kb at a time
			$count += preg_match_all( '#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches );
		}
		fclose( $fh );
		return $count > 1;
	}
}
?>