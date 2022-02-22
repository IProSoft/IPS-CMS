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
use Imagecraft\ImageBuilder;
class Upload_Extended extends Upload
{
	public $config = array();
	
	public static function largeMaxWidth()
	{
		return IPS_VERSION == 'gag' ? 635 : 700;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		self::initDebug( __LINE__ );
		
		require_once( ABS_PATH . '/functions-upload.php' );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function initDebug( $file_line )
	{
		if ( defined( 'IPS_DEBUG' ) )
		{
			ips_log( 'Memory used in line ' . $file_line . ' : ' . memory_get_usage() . PHP_EOL, 'logs/memory.log' );
		}
	}


	/**
	 * The configuration settings for all types of materials
	 * @param $config array - tablica ustawień przekazana 
	 * z klasy po której dziedziczymy (Upload)
	 * table settings transferred from the class on which we inherit (Upload)
	 */
	public function configuration( array $config )
	{
		self::initDebug( __LINE__ );
		
		$this->config                = $config;
		$this->config['margin'] = $this->getMargin( $this->config['upload_type'] );
		$this->config['file_border'] = $this->getBorder();
		
		if ( !isset( $this->config['up_folder'] ) )
		{
			$this->config['up_folder'] = self::createPath();
		}

		$this->config['site_url'] = str_replace( 'www.', '', parse_url( ABS_URL, PHP_URL_HOST ) );
		
		$this->config['final_dimensions'] = array( 
			'medium' => array(
				'width'  => intval( round( $config['width'] ) ),
				'height' => intval( round( $config['height'] ) )
			),
			'large' => array()
		);
		
		$this->config['thumbinail_extension'] = 'png';
		
		$this->config = apply_filters( 'up_create_config', $this->config, $this );
		
		self::initDebug( __LINE__ );
		
	}
	
	/**
	 * Get current file name with extension
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function getName()
	{
		return $this->config['name'] . '.' . $this->config['extension'];
	}
	/**
	 * The function that recognizes the type of material and deciding on:
	 * - error if the image is too small
	 * - The creation of animation for writing
	 * - Copy files if the dimensions remain unchanged
	 * - Text to be superimposed on Demotywator or mem
	 * - Save the finished files
	 */
	public function initCreateImage( $return_images = false )
	{
		self::initDebug( __LINE__ );
		
		$this->backupUploadSource( IMG_PATH . '/' . $this->getName(), $this->getName() );
		
		/* Animation */
		if ( $this->config['upload_type'] == 'animation' )
		{
			return $this->generateAnimation();
		}
		/*  Other types*/
		else
		{
			if ( $this->config['upload_type'] == 'demotywator' )
			{
				Ips_Registry::get( 'Upload_Demotywator' )->uploadFilters();
			}
			elseif ( $this->config['upload_type'] == 'mem' )
			{
				Ips_Registry::get( 'Upload_Mem' )->uploadFilters();
			}
			
			
			$large = Gd::as_resource( IMG_PATH . '/' . $this->getName() );
			$medium = Gd::as_resource( IMG_PATH . '/' . $this->getName() );
			
			if ( !is_resource( $large ) )
			{
				throw new Exception( 'err_uploaded_file' );
			}
			
			$this->config['extension'] = 'png';
			
			$large_width = $this->config['width'] <= self::largeMaxWidth() ? $this->config['width'] : self::largeMaxWidth();
			$medium_width = $this->config['width'] > Config::get( 'file_max_width' ) ? Config::get( 'file_max_width' ) : $this->config['width'];
			
			/* if ( $this->config['upload_type'] == 'demotywator' || $this->config['upload_type'] == 'mem' )
			{
				$large_width = self::largeMaxWidth();
				$medium_width = Config::get( 'file_max_width' );
			} */

			$large	= $this->applyActions( $large,	$large_width,	'large' );
			$medium = $this->applyActions( $medium, $medium_width,	'medium' );
			
			if( $return_images )
			{
				return array(
					'medium' => $large,
					'large' => $medium
				);
			}
			
			/**** End up saving generated images *****/
			$this->saveImages( $large, $medium );
			unset( $large, $medium );
			
		}
	}
	
	/**
	 * Make actions on image
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function applyActions( $image, $width, $size )
	{
		$image = $this->resizeImage( $image, $width, $size );
		
		/** Must be after resize to fit text layter */
		$image = apply_filters( 'up_actions_after_resize', $image, $size, $this->config, $this );
		
		if ( Config::getArray( 'watermark_transparent', 'activ' ) )
		{
			$image = $this->transparentWatermark( $image );
		}
		
		$image = $this->addBorder( $image );

		$image = $this->addMargin( $image );
		
		if ( Config::getArray( 'watermark', 'activ' )  )
		{
			$image = $this->watermark( $image );
		}
		
		$image = apply_filters( 'up_actions_finish', $image, $size, $this->config, $this );
		
		return $image;
	}

	/**
	 * Max image width
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function maxWidth( $size )
	{
		return $size == 'medium' ? Config::get( 'file_max_width' ) : self::largeMaxWidth();
	}
	/**
	 * Dopasowanie obrazu video
	 * do wybranej w PA rozdzielczości ( 16:9 lub 4:3 ).
	 * Adjusting the video image to the desired resolution in PA (16: 9 or 4: 3).
	 * Skalujemy obrazek tylko jeśli jego
	 * ratio jest różne od zadeklarowanego w PA.
	 * Scale the image only if the ratio is different from the declared in PA.
	 * @param string $image - image address stored on the server
	 * return bool
	 */
	public function scaleVideoResolution( $image )
	{
		if ( $this->config['upload_subtype'] == 'mp4' )
		{
			return $image;
		}
		
		$height = imagesy( $image );
		$width  = imagesx( $image );
		
		//$resize_width = Config::get('file_max_width');
		$resize_width = $width;
		
		if ( Config::get( 'upload_video_options', 'add_video_resolution' ) == '16:9' )
		{
			if ( round( $width / $height, 5 ) == 1.77777 )
			{
				return $image;
			}
			$resize_height = round( $resize_width / 1.77777778 );
		}
		elseif ( Config::get( 'upload_video_options', 'add_video_resolution' ) == '4:3' )
		{
			if ( round( $width / $height, 5 ) == 1.33333 )
			{
				return $image;
			}
			$resize_height = round( $resize_width / 1.33333333 );
		}
		
		$img = imagecreatetruecolor( $resize_width, $resize_height );
		imagecopyresampled( $img, $image, 0, 0, 0, 0, $resize_width, $resize_height, $width, $height );
		imagedestroy( $image );
		
		return $img;
		
	}

	/**
	 * The dimensions for saving images reduced by such variables as the picture frame, side and top margins, spacing of the text in  case Demotywator, allowing for subsequent video display type material or GIF perfectly in the frame surrounding the image regardless of the size of the image.
	 * @param $image resource - image for processing
	 * @param $large bolean - large/medium image
	 * true when saving dimensions for materials other than Demotywator
	 */
	public function saveDimensions( $image, $size = 'medium' )
	{
		$height = imagesy( $image );
		$width  = imagesx( $image );
		
		return $this->config['final_dimensions'][$size] = array(
			'width' => ( $width % 2 != 0 ? $width + 1 : $width ),
			'height' => ( $height % 2 != 0 ? $height + 1 : $height ),
			'margin' => $this->config['margin']['side'] + $this->config['file_border']['width'],
			'top' => $this->config['margin']['top'] + $this->config['file_border']['width'] 
		);
		
		/*
		$height = $height - 2 * $this->config['file_border']['width'] - 2 * $this->config['margin']['top'];
		$width  = $width - 2 * $this->config['file_border']['width'] - 2 * $this->config['margin']['side'];
		 */
		/*  
		if ( $large )
		{
			$this->config['final_dimensions']['large'] = array(
				'width' => ( $width % 2 != 0 ? $width + 1 : $width ),
				'height' => ( $height % 2 != 0 ? $height + 1 : $height ),
				'margin' => $this->config['margin']['side'] + $this->config['file_border']['width'],
				'top' => $this->config['margin']['top'] + $this->config['file_border']['width'] 
			);
			return true;
		}
		
		$this->config['final_dimensions'][$size] = array(
			'width' => ( $width % 2 != 0 ? $width + 1 : $width ),
			'height' => ( $height % 2 != 0 ? $height + 1 : $height ),
			'margin' => $this->config['margin']['side'] + $this->config['file_border']['width'],
			'top' => $this->config['margin']['top'] + $this->config['file_border']['width'] 
		); */

		/* $this->config['final_dimensions'] = array_merge( $this->config['final_dimensions'], $final_dimensions );
		
		if ( $this->config['final_dimensions']['width'] % 2 != 0 )
		{
			$this->config['final_dimensions']['width'] += 1;
		} */
		
		//print_r( $this->config['final_dimensions'] );
		//echo serialize($this->config['final_dimensions']);exit;
		//print_r( $this->config['final_dimensions'] );exit;
	}
	

	
	
	
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function resizeImage( $image, $width, $size )
	{
		if( imagesx( $image ) + ( 2 * $this->config['file_border']['width'] + 2 * $this->config['margin']['side'] ) <= $this->maxWidth( $size ) )
		{
			$resize_width = $width;
			$resize_height = imagesy( $image );
		}
		else
		{
			$resize_width  = $this->maxWidth( $size ) - 2 * $this->config['file_border']['width'] - 2 * $this->config['margin']['side'];
			$resize_height = ( $resize_width / imagesx( $image ) ) * imagesy( $image );
			$image = $this->cropImage( $image, $resize_width, $resize_height );
		}
		
		if ( $this->config['upload_type'] == 'video' && Config::get( 'upload_video_options', 'add_video_layer' ) == 1 )
		{
			$image = $this->addVideoLayer( $image, $resize_width, $resize_height );
		}
		
		$this->saveDimensions( $image, $size  );

		return $image;
	}
	
	/**
	 * Adding strip video player to the image.
	 * @param resource $image - image resource
	 * @param int $width - width of image to put on
	 * @param int $height - width of image to put on
	 * return resource $image
	 */
	public function addVideoLayer( $image, $width, $height )
	{
		if ( $this->config['upload_subtype'] == 'mp4' )
		{
			return $image;
		}
		
		if ( file_exists( ABS_PATH . '/images/panel.png' ) )
		{
			$img = Gd::as_resource( ABS_PATH . '/images/panel.png' );
			
			$resized_height = imagesy( $img ) * ( $width / imagesx( $img ) );
			
			$resized = imagecreatetruecolor( $width, $resized_height );
			
			imagecopyresampled( $resized, $img, 0, 0, 0, 0, $width, $resized_height, imagesx( $img ), imagesy( $img ) );
			imagedestroy( $img );
			
			imagecopymerge( $image, $resized, 0, $height - $resized_height, 0, 0, $width, $height, 100 );
			imagedestroy( $resized );
		}
		else
		{
			error_log( "Brak pliku panelu video - " . ABS_PATH . "/images/panel.png", 0 );
		}
		
		return $image;
	}
	

	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function addMargin( $image )
	{
		if ( $this->config['margin']['side'] > 0 || $this->config['margin']['top'] > 0 )
		{
			$width = imagesx( $image );
			$height = imagesy( $image );
			
			$img = Gd::img_colored_fill( array(
				'width' => $width + 2 * $this->config['margin']['side'],
				'height' => $height + 2 * $this->config['margin']['top'],
				'color' => $this->config['margin']['box_color']
			) );
			
			imagecopy( $img, $image, $this->config['margin']['side'], $this->config['margin']['top'], 0, 0, $width, $height );
			
			return $img;
		}
		
		return $image;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function addBorder( $image )
	{
		$image = Gd::as_resource( $image );
		
		if ( $this->config['file_border']['width'] == 0 )
		{
			return $image;
		}
		
		$width = imagesx( $image );
		$height = imagesy( $image );
			
		$img = Gd::img_colored_fill( array(
			'width' => $width + 2 * $this->config['file_border']['width'],
			'height' => $height + 2 * $this->config['file_border']['width'],
			'color' => $this->config['file_border']['color']
		) );

		imagecopymerge( $img, $image, $this->config['file_border']['width'], $this->config['file_border']['width'], 0, 0, $width, $height, 100 );

		return $img;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function saveImages( $large, $medium )
	{
		$this->thumbImage( $large, $this->config['up_folder']['thumb'] . '/' . $this->getName(), true );
		
		$large = apply_filters( 'up_save_image', $large );
		$medium = apply_filters( 'up_save_image', $medium );
		
		/* Demot - GIF */

		if ( file_exists( IMG_PATH . '/' . $this->config['name'] . '.gif' ) )
		{
			rename( IMG_PATH . '/' . $this->config['name'] . '.gif', $this->config['up_folder']['gif'] . '/' . $this->config['name']. '.gif' );
		}
		
		$this->put( $large, $this->config['up_folder']['large'] . '/' . $this->config['name'] );
		$this->put( $medium, $this->config['up_folder']['medium'] . '/' . $this->config['name'] );
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function backupUploadSource( $upload_source, $file_name )
	{
		$path = IMG_PATH_BACKUP . '/' . getFolderByDate( null, IPS_CURRENT_DATE );
		
		if ( !is_writable( IMG_PATH_BACKUP . '/' ) )
		{
			chmod( IMG_PATH_BACKUP . '/', 0777 );
		}
		
		$file = $path  . '/' . basename( $file_name );
		
		if ( !file_exists( $file ) || filemtime( $file ) + 60 < time() )
		{
			if( !is_dir( $path ) )
			{
				mkdir( $path, 0777, true );
			}
			
			if ( file_exists( $upload_source ) )
			{
				return copy( $upload_source, $file );
			}
			
			return ips_log( array( $upload_source, $file_name, $this ), 'logs/upload-notics.log');
		}
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function transparentWatermark( $image )
	{
		if( !isset( $this->watermark ) )
		{
			$this->watermark = new Watermark();
		}
		return $this->watermark->addTransparentWatermark( $image );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function watermark( $imageResource )
	{
		if( !isset( $this->watermark ) )
		{
			$this->watermark = new Watermark();
		}

		return $this->watermark->get( 
			$imageResource, 
			$this->config['file_border']['width'] + $this->config['margin']['top'], 
			$this->config['file_border']['width'] + $this->config['margin']['side']
		);
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function get_id()
	{
		return $this->file_add_id;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function generateAnimation()
	{
		if ( $this->config['width'] > Config::get( 'file_max_width' ) )
		{
			$this->config['height'] = $this->config['height'] * ( Config::get( 'file_max_width' ) / $this->config['width'] );
			$this->config['width']  = Config::get( 'file_max_width' );
			
			include_once( LIBS_PATH . '/ImageCraft/autoload.php' );

			$options = ['engine' => 'php_gd', 'locale' => 'en', 'output_format' => 'png'];
			
			$builder = new ImageBuilder( $options );
			$image = $builder
				->addBackgroundLayer()
					->filename( IMG_PATH . '/' . $this->getName() )
					->resize( $this->config['width'], $this->config['height'], 'shrink' )
					->done()
				->save();
			
			if ( $image->isValid() )
			{
				File::put( IMG_PATH . '/' . $this->getName(), $image->getContents() );
			}
		}
		
		$save_path = createFolderByDate( IMG_PATH . '/gif', IPS_CURRENT_DATE, 'path' );
		
		if ( !rename( IMG_PATH . '/' . $this->getName(), $save_path . '/' . $this->getName() ) )
		{
			throw new Exception( 'err_unknown' );
		}
		
		$this->config['final_dimensions']['large'] = $this->config['final_dimensions']['medium'] = array( 
			'width' => intval( round( $this->config['width'] ) ),
			'height' => intval( round( $this->config['height'] ) ),
			'margin' => 0,
			'top' => 0 
		);
		
		$img = Gd::as_resource( $save_path . '/' . $this->getName() );

		if ( !is_resource( $img ) )
		{
			throw new Exception( 'err_uploaded_file' );
		}
		
		$this->config['file_border']['width'] = $this->config['margin']['top'] = $this->config['margin']['side'] = 0;
		$img = $this->watermark( $img );
		
		$this->saveImages( $img, $img );
		$this->config['extension'] = 'png';
	}
	/**
	 * generating thumbnails
	 *
	 * @param string $img - address from which the image is generated thumbnail
	 * @param string $img_to - image address at which to write
	 * @param string $ext - file extension
	 * 
	 * @return 
	 */
	public function thumbImage( $img, $img_to, $with_border = false )
	{
		$pathinfo = ips_pathinfo( $img_to );

		$file_dims = Config::getArray( 'add_thumb_size' );
		
		if ( empty( $file_dims ) || !is_array( $file_dims ) || !isset( $file_dims['width'] ) )
		{
			$file_dims = unserialize( 'a:4:{s:5:"width";i:200;s:6:"height";i:200;s:3:"box";b:1;s:9:"box_color";s:7:"#333333";}' );
			Config::update( 'add_thumb_size', 'a:4:{s:5:"width";i:200;s:6:"height";i:200;s:3:"box";b:1;s:9:"box_color";s:7:"#333333";}' );
		}
		
		$img = Gd::as_resource( $img );

		if ( !is_resource( $img ) )
		{
			ips_log( 'Thumb create problem : ' . (string) $img . ' ' . $img_to );
			throw Exception('Thumb create problem');
		}
		
		$cont_width  = imagesx( $img );
		$cont_height = imagesy( $img );
		$ratio       = $cont_width / $cont_height;
		
		if ( $file_dims['box'] === 'fancy-box' || strpos( $img_to, '/square' ) !== false )
		{
			$new_h   = ( $file_dims['width'] * $cont_height ) / $cont_width;
			$resized = imagecreatetruecolor( $file_dims['width'], $new_h );
			imagecopyresampled( $resized, $img, 0, 0, 0, 0, $file_dims['width'], $new_h, $cont_width, $cont_height );
			
			$to_height = 130;
			if ( imagesy( $resized ) > $to_height )
			{
				$new = imagecreatetruecolor( $file_dims['width'], $to_height );
				imagecopy( $new, $resized, 0, 0, 0, ( $new_h - $to_height ) / 2, $file_dims['width'], $to_height );
				$resized = $new;
				unset( $new );
			}
			
			if ( $with_border )
			{
				$image = imagecreatetruecolor( $file_dims['width'], $this->config['file_border']['width'] / 2 );
				$color = Gd::hex_convert( $this->config['file_border']['color'] );
				$im    = imagecolorallocate( $image, $color['r'], $color['g'], $color['b'] );
				imagefill( $image, 0, 0, $im );
				imagecopy( $resized, $image, 0, $to_height - $this->config['file_border']['width'] / 2, 0, 0, $file_dims['width'], $this->config['file_border']['width'] / 2 );
			}
			
			$this->put( $resized, $this->config['up_folder']['square'] . '/' . $pathinfo['filename'] . '_resized', $pathinfo['extension'] );
			
			if ( $with_border )
			{
				$cont_width -= 2 * $this->config['file_border']['width'];
				$cont_height -= 2 * $this->config['file_border']['width'];
				$resized = imagecreatetruecolor( $cont_width, $cont_height );
				imagecopy( $resized, $img, 0, 0, $this->config['file_border']['width'], $this->config['file_border']['width'], $cont_width, $cont_height );
				$img = $resized;
			}
			$new_w = $file_dims['width'];
			$new_h = ( $file_dims['width'] * $cont_height ) / $cont_width;
			
			if ( $new_h < $file_dims['height'] )
			{
				$new_h = $file_dims['height'];
				$new_w = ( $file_dims['height'] * $cont_width ) / $cont_height;
			}
			
			$new = imagecreatetruecolor( $new_w, $new_h );
			imagecopyresampled( $new, $img, 0, 0, 0, 0, $new_w, $new_h, $cont_width, $cont_height );
			
			$resized = imagecreatetruecolor( $file_dims['width'], $file_dims['height'] );
			
			$x_source = ( $file_dims['width'] > $new_w ? $file_dims['width'] - $new_w : $new_w - $file_dims['width'] );
			$y_source = ( $file_dims['height'] > $new_w ? $file_dims['height'] - $new_h : $new_h - $file_dims['height'] );
			
			imagecopy( $resized, $new, 0, 0, $x_source / 2, $y_source / 2, $new_w, $new_h );
			
			return $this->put( $resized, $pathinfo['dirname'] . '/' . $pathinfo['filename'], $pathinfo['extension'] );
		}
		
		//header('Content-Type: image/jpeg');imagejpeg( $resized );exit;
		
		$target_ratio = $file_dims['width'] / $file_dims['height'];
		
		if ( $ratio > $target_ratio )
		{
			$new_w    = $file_dims['width'];
			$new_h    = round( $file_dims['width'] / $ratio );
			$x_offset = 0;
			$y_offset = round( ( $file_dims['height'] - $new_h ) / 2 );
		}
		else
		{
			$new_h    = $file_dims['height'];
			$new_w    = round( $file_dims['height'] * $ratio );
			$x_offset = round( ( $file_dims['width'] - $new_w ) / 2 );
			$y_offset = 0;
		}
		
		$resized = imagecreatetruecolor( $new_w, $new_h );
		imagecopyresampled( $resized, $img, 0, 0, 0, 0, $new_w, $new_h, $cont_width, $cont_height );
		
		if ( $file_dims['box'] && !empty( $file_dims['box_color'] ) )
		{
			$img     = $resized;
			$color   = Gd::hex_convert( $file_dims['box_color'] );
			$resized = imagecreatetruecolor( $file_dims['width'], $file_dims['height'] );
			$color   = imagecolorallocate( $resized, $color['r'], $color['g'], $color['b'] );
			imagefill( $resized, 0, 0, $color );
			imagecopymerge( $resized, $img, $x_offset, $y_offset, 0, 0, $new_w, $new_h, 100 );
		}
		
		return $this->put( $resized, $pathinfo['dirname'] . '/' . $pathinfo['filename'], $pathinfo['extension'] );
		
		unset( $img_to, $img, $color, $resized );
		
	}
	/**
	 * Adding material MySQL
	 *
	 * @param string $up - upload data
	 * @param string $upload_video - link to video
	 * 
	 * @return bool - whether the material has been added to the database
	 */
	public function addUploadFile( $up, $upload_video = false )
	{
		if ( empty( $up['user_login'] ) )
		{
			$up['user_login'] = USER_LOGIN;
		}
		
		$upload_status = $up['private'] != false ? 'private' : 'public';
		
		$status_activ = 0;
		
		if ( ( defined( 'IPS_CRON' ) || USER_ADMIN ) && Session::getChild( 'inc', 'import_add_to' ) == 'main' )
		{
			$status_activ = 1;
		}
		if ( isset( $this->config['IPS_IMPORT_ACTIV'] ) )
		{
			$status_activ = $this->config['IPS_IMPORT_ACTIV'];
		}
		
		/**
		 * Protection against adding the two materials in the same second (import)
		 */
		$max_date    = date( "Y-m-d H:i:s" );
		$exists_date = PD::getInstance()->select( IPS__FILES, array(
			'date_add' => array(
				$max_date,
				'>=' 
			) 
		), 1 );
		
		if ( !empty( $exists_date ) )
		{
			$max_date = PD::getInstance()->select( IPS__FILES, false, 1, "DATE_ADD( MAX( date_add ), INTERVAL 2 SECOND ) as max_date" );
			$max_date = ( empty( $max_date['max_date'] ) ? date( "Y-m-d H:i:s" ) : $max_date['max_date'] );
		}
		
		$user_id = getUserInfo( false, false, $up['user_login'], 'id' );
		
		$this->config['final_dimensions'] = $this->mergeFinalDimensions( $this->config['final_dimensions'], $this->config['up_folder']['folder'] . '/' . $this->getName() );
		
		if( $up['upload_extra'] )
		{
			$this->config['final_dimensions'] = array_merge( $this->config['final_dimensions'], $up['upload_extra'] );
		}
		
		$upload_data = array(
			'title' 		=> App::censored( $up['title'] ),
			'top_line'	=> App::censored( empty( $up['top_line'] ) ? $up['title'] : $up['top_line'] ),
			'bottom_line'		=> App::censored( empty( $up['bottom_line'] )  ? $up['title'] : $up['bottom_line'] ),
			'user_login' 	=> $up['user_login'],
			'user_id' 		=> $user_id,
			'date_add' 		=> $max_date,
			'upload_image' 	=> $this->config['up_folder']['folder'] . '/' . $this->getName(),
			'upload_activ' 	=> $status_activ,
			'upload_type' 	=> $this->config['upload_type'],
			'upload_status' => $upload_status,
			'upload_subtype'=> $this->config['upload_subtype'],
			'upload_source' => (string)$up['upload_source'],
			'category_id' 	=> 0,
			'upload_data'	=> serialize( $this->config['final_dimensions'] ),
			'upload_video' 	=> ( $upload_video ? $upload_video : 'none' ),
			'upload_adult' 	=> ( isset( $this->config['adult_files'] ) ? (int) $this->config['adult_files'] : 0 ),
			'seo_link' 		=> seoLink( false, $up['title'] ),
			'autopost' 		=> $this->locked()
		) ;
		
		$upload_data = apply_filters( 'upload_db_insert', $upload_data, $this );
		
		$this->file_add_id = PD::getInstance()->insert( IPS__FILES, $upload_data );
		
		if ( !empty( $this->file_add_id ) )
		{
			do_action( 'up_insert_row', $this );
			
			$this->addToCategory( $up['category_id'] );
			
			Ips_Registry::get( 'Upload_Tags' )->saveTags( $this->tags, $this->file_add_id );

			if( !Session::getChild( 'inc', 'import_add_to' ) )
			{
				$this->ogThumb( $this->config['up_folder']['folder'] . '/' . $this->getName() ); 
			}
			
			Operations::updateUserStats( $user_id );

			Upload_Meta::update( $this->file_add_id, 'upload_file_data', array_merge( $up['upload_file_data'], array(
				'image' => $this->getName(),
			) ) );
			
			PD::getInstance()->insert( 'shares', array(
				'upload_id' => $this->file_add_id 
			) );
			
			if ( $upload_status == 'public' && $status_activ == 0 )
			{
				waitCounterUpdate();
			}
			
			if ( $this->config['upload_subtype'] == 'mp4' )
			{
				$file_name = basename( $upload_video );
				
				if ( file_exists( IPS_TMP_FILES . '/' . $file_name ) )
				{
					rename( IPS_TMP_FILES . '/' . $file_name, IPS_VIDEO_PATH . '/' . $file_name );
					
					$webm = IPS_TMP_FILES . '/' . substr( $file_name, 0, -4 ) . '.webm';
					
					if ( file_exists( $webm ) )
					{
						rename( $webm, IPS_VIDEO_PATH . '/webm/' . substr( $file_name, 0, -4 ) . '.webm' );
					}
				}
			}
			
			/* Save user browser and IP */
			$this->userInfo( $this->file_add_id );
			
			if ( !USER_ADMIN && !defined( 'IPS_CRON' ) && User_Data::get( USER_ID, 'post_facebook' ) == 1 )
			{
				Facebook_UI::postUserContent( $this->file_add_id, array(
					'upload_image' => $this->getName(),
					'title' => $up['title'],
					'top_line' => App::censored( $top_line ),
					'message' => User_Data::get( USER_ID, 'post_facebook_message' ) 
				) );
			}
			
			$fanpage_posting = Config::getArray( 'apps_fanpage_posting' );
				
			if ( $fanpage_posting['move_main'] )
			{
				if ( Config::get( 'apps_fanpage_post_move_main' ) == $fanpage_posting['move_main_count'] || $fanpage_posting['move_main_count'] == 0 )
				{
					Facebook_Fanpage::postFromId( $this->file_add_id, 'post', array( 
						'fanpage_id' => $fanpage_posting['move_main_fanpages']
					) );
					Config::update( 'apps_fanpage_post_move_main', 0 );
				}
				Config::update( 'apps_fanpage_post_move_main', ( Config::get( 'apps_fanpage_post_move_main' ) + 1 ) );
			}

			return true;
		}
		
		throw new Exception('err_upload_insert');
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function mergeFinalDimensions( $final_dimensions, $upload_image )
	{
		if ( !isset( $final_dimensions['medium']['file'] ) || empty( $final_dimensions['medium']['file'] ) )
		{
			list( $width, $height, $ext ) = getimagesize( ips_img( $upload_image, 'medium' ) );
			
			$final_dimensions['medium']['file'] = array(
				'width' => $width,
				'height' => $height
			);
		}
		
		if ( !isset( $final_dimensions['large']['file'] ) || empty( $final_dimensions['large']['file'] ) )
		{
			list( $width, $height, $ext ) = getimagesize( ips_img( $upload_image, 'large' ) );
			
			$final_dimensions['large']['file'] = array(
				'width' => $width,
				'height' => $height
			);
		}
		
		return $final_dimensions;
	}
	
	/*
	 * 
	 * param array $text
	 */
	public function addUploadText( $text, $upload_id )
	{
		$text = apply_filters( 'upload_add_text', $text );
		
		if( isset( $text['meta_text'] ) )
		{
			$meta = (array)Upload_Meta::get( $upload_id, 'upload_file_data' );
			
			Upload_Meta::update( $upload_id, 'upload_file_data', array_merge( $meta, [
				'meta_text' => $text['meta_text']
			] ) );
		}
		
		return PD::getInstance()->insert( 'upload_text', array(
			'upload_id' 	=> $upload_id,
			'intro_text'	=> App::censored( $text['intro_text'] ),
			'long_text' 	=> App::censored( $text['long_text'] )
		) );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function addToCategory( $category_id )
	{
		if ( !Categories::exists( $category_id ) )
		{
			$category_id = Categories::defaultCategory();
		}
		
		PD::getInstance()->update( IPS__FILES, array(
			'category_id' => $category_id 
		),  array( 
			'id' => $this->file_add_id
		) );
	}


	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function userInfo( $upload_id )
	{
		include_once( LIBS_PATH . '/GeoInfo.php' );
		
		$info = new GeoInfo();
		$browser = $info->getBrowser();
		
		Upload_Meta::update( $upload_id, 'uploader_data', array(
			'browser_info' => is_array( $browser ) ? implode( '|', $browser ) : $browser,
			'ip' => $info->getIp(),
			'id' => USER_ADMIN ? false : USER_ID
		) );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function ogThumb( $thumb )
	{
		try{
			if( !file_exists( IMG_PATH . '/og-thumb' ) )
			{
				mkdir( IMG_PATH . '/og-thumb', 0777 );
			}
			
			$path = createFolderByDate( IMG_PATH . '/og-thumb', IPS_CURRENT_DATE, 'path' );
			
			$up = new Upload_Single_File;
						
			$up->setConfig( array(
				'files' => 'file_thumb',
				'post' => 'link_thumb' 
			));
			
			$file = $up->Load( $path . '/' . substr( basename( $thumb ), 0, -4 ) );
			
			list( $width, $height ) = getimagesize( $path . '/' . basename( $thumb ) );
			
			PD::getInstance()->update( IPS__FILES, array(
				'upload_data' => serialize( array_merge( $this->config['final_dimensions'], array(
					'og-thumb' => array(
						'width' => $width,
						'height' => $height
					)
				) ) )
			),  array( 
				'id' => $this->file_add_id
			) );
			
		} catch (Exception $e) {}
	}

	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getBorder()
	{
		return array(
			'width' => $this->config['margin']['border_width'],
			'color' => $this->config['margin']['border_color']
		);
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getMargin( $upload_type )
	{
		$margin = Config::getArray( 'upload_margin', $upload_type );
	
		if( $margin == false )
		{
			$margin = Config::getArray( 'upload_margin', 'default' );
		}
		
		return $margin;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function locked()
	{
		if( Config::get( 'apps_facebook_autopost' ) && Config::getArray( 'apps_facebook_autopost_options', 'auto_block' ) )
		{
			return 'autopost';
		}
		if( Config::get( 'apps_social_lock' ) && Config::getArray( 'apps_social_lock_options', 'auto_block' ) )
		{
			return 'social_lock';
		}
		
		return 'off';
	}

	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	
	public static function createPath()
	{
		return array(
			'large' 		=> createFolderByDate( IMG_PATH_LARGE, IPS_CURRENT_DATE, 'path' ),
			'medium' 		=> createFolderByDate( IMG_PATH_MEDIUM, IPS_CURRENT_DATE, 'path' ),
			'thumb' 		=> createFolderByDate( IMG_PATH_THUMB, IPS_CURRENT_DATE, 'path' ),
			'thumb-small' 	=> createFolderByDate( IMG_PATH_THUMB_SMALL, IPS_CURRENT_DATE, 'path' ),
			'thumb-mini' 	=> createFolderByDate( IMG_PATH_THUMB_MINI, IPS_CURRENT_DATE, 'path' ),
			'square' 		=> createFolderByDate( IMG_PATH_SQUARE, IPS_CURRENT_DATE, 'path' ),
			'gif' 			=> createFolderByDate( IMG_PATH_GIF, IPS_CURRENT_DATE, 'path' ),
			'media_poster' 	=> createFolderByDate( IMG_PATH_MEDIA_POSTER, IPS_CURRENT_DATE, 'path' ),
			'folder' 		=> getFolderByDate( null, IPS_CURRENT_DATE ) 
		);
	}

	

	/**
	 * Store image on serwer path
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function put( $img, $path, $ext = 'png' )
	{
		if( $ext == 'jpg' )
		{
			imageinterlace( $img, true );
			if( imagejpeg( $img, $path . '.jpg', Config::get( 'images_compress', 'jpg' ) ) )
			{
				return str_replace( ABS_PATH .'/', ABS_URL, $path ) . '.jpg'; 
			}
		}
		else
		{
			if( imagepng( $img, $path . '.png', Config::get( 'images_compress', 'png' ) ) )
			{
				return str_replace( ABS_PATH .'/', ABS_URL, $path ) . '.png'; 
			}
		}

		return false;
	}
}



