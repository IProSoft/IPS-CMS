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

class Upload_Text extends Upload_Types
{
	/**
	 * Class constructor
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		$this->opts = $this->init( 'upload_text_options', 'text', IPS_ACTION == 'up'  );
	}
	/**
	 * Add upload image filters
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function uploadFilters()
	{
		add_filter( 'up_actions_after_resize', array( 
			$this, 'adaptLayer'
		) );
		add_filter( 'up_actions_finish', array( 
			$this, 'addLayer'
		) );
	}


	/**
	 * Get system background
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getBg( $bg_type )
	{
		$width = $this->opts['size']['large'];
		$height = $this->opts['set_min_height'] > 0 ? $this->opts['set_min_height'] : 100;

		switch( $bg_type )
		{
			case 'color':
				
				$img   = imagecreatetruecolor( $width, $height );
				
				$color = Gd::hex_convert( $this->opts['bg_color'] );
				imagefilledrectangle( $img, 0, 0, $width, $height, imagecolorallocate( $img, $color['r'], $color['g'], $color['b'] ) );

			break;
			
			case 'gradient':
				
				$img = $this->gradient( imagecreatetruecolor( $width, $height ), array(
					$this->opts['bg_gradient_1'],
					$this->opts['bg_gradient_2'],
					$this->opts['bg_gradient_3'],
					$this->opts['bg_gradient_4'] 
				) );
				
			break;
			
			case 'image':
				
				$file = self::randBg();
			
				if ( empty( $file ) || !is_file( $file ) )
				{
					return $this->getBg( 'color' );
				}
				
				$img = Gd::as_resource( $file );
				
				if( imagesx( $img ) > $this->opts['size']['large'] )
				{
					$img = $this->upload->cropImage( $img, $this->opts['size']['large'] );
				}
				
				$img = $this->imageFitFile( $img, $width, $height );
				
			break;
		}
		
		if( isset( $img ) )
		{
			if( $url = $this->upload->put( $img, IPS_TMP_FILES . '/' . str_random( 20 ), 'jpg' ) )
			{
				return $url;
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
	public function imageFitFile( $image, $width, $height )
	{
		$image_width  = imagesx( $image );
		$image_height = imagesy( $image );
		
		if ( $image_width >= $width && $image_height >= $height )
		{
			/** No fitting */
			return $image;
		}
		
		$img = imagecreatetruecolor( $width, $height );

		if ( $this->opts['user_bg_fit'] == 'full' )
		{
			for ( $x = 0; $x < $width; $x += $image_width )
			{
				for ( $y = 0; $y < $height; $y += $image_height )
				{
					imagecopy( $img, $image, $x, $y, 0, 0, $image_width, $image_height );
				}
			}
		}
		elseif ( $this->opts['user_bg_fit'] == 'fill_color' )
		{
			$color = Gd::hex_convert( $this->opts['user_bg_fit_fill_color'] );
			imagefill( $img, 0, 0, imagecolorallocate( $img, $color[ 'r' ], $color[ 'g' ], $color[ 'b' ] ) );
			imagecopy( $img, $image, ( $width - $image_width ) / 2, ( $height - $image_height ) / 2, 0, 0, $image_width, $image_height );
		}
	
		return $img;
	}
	/**
	 * Put shadow on image
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function imgShadow( $image )
	{
		if ( (int)$this->opts['image_shadow'] > 0 )
		{
			if ( !is_resource( $image ) )
			{
				$image = Gd::as_resource( $image );
			}
			
			$img = imagecreatetruecolor( imagesx( $image ), imagesy( $image ) );
			imagefill( $img, 0, 0, imagecolorallocatealpha( $img, 0, 0, 0, 127 ) );
			imagecopymerge( $img, $image, 0, 0, 0, 0, imagesx( $image ), imagesy( $image ), 100 - (int)$this->opts['image_shadow'] );
			$image = $img;
		} 
		
		return $image;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function gradient( $img, $colors = array( '#FFFFFF', '#FF0000', '#00FF00', '#0000FF' ) )
	{
		$width  = imagesx( $img );
		$height = imagesy( $img );
		
		foreach ( $colors as $key => $value )
		{
			$colors[$key] = $this->hex2rgb( $value );
		}
		
		$rgb = $colors[0];
		
		for ( $x = 0; $x <= $width; $x++ )
		{
			for ( $y = 0; $y <= $height; $y++ )
			{
				$col = imagecolorallocate( $img, $rgb[0], $rgb[1], $rgb[2] );
				
				imagesetpixel( $img, $x - 1, $y - 1, $col );
				
				for ( $i = 0; $i <= 2; $i++ )
				{
					$rgb[$i] = $colors[0][$i] * ( ( $width - $x ) * ( $height - $y ) / ( $width * $height ) ) + $colors[1][$i] * ( $x * ( $height - $y ) / ( $width * $height ) ) + $colors[2][$i] * ( ( $width - $x ) * $y / ( $width * $height ) ) + $colors[3][$i] * ( $x * $y / ( $width * $height ) );
				}
			}
		}
		
		return $img;
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function clean( $text )
	{
		return implode( "\n", array_column( json_decode( $text, true ), 'text' ) );
	}
	/**
	 * Format text
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function format( $text, $with_cut = false )
	{
		
		$text = preg_replace( "/[\r\n]/", " {n}", preg_replace( '~\r\n?~', "\n", $text ) );
		
		if ( $with_cut )
		{
			$cut_length = strlen( $text );
			
			if( $cut_length > 255 )
			{
				$cut_length = 255;
			}
			else
			{
				$cut_length =  $cut_length - $cut_length / 3;
			}
			
			$text = cutWords( $text, $cut_length );
		}
		
		return str_replace( " {n}", "<br />", $text );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function fromJson( $text )
	{
		return json_encode( json_decode( $text, true ) );
	}


	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function storeText( $base64_medium, $base64_large, $img_name )
	{
		if( !is_file( IPS_TMP_FILES . '/' . basename( $img_name ) ) )
		{
			ips_log( func_get_args(), 'logs/upload.log' );
			return array(
				'error' => __( 'err_upload_layer' )
			);
		}
		
		$resource = Gd::as_resource( IPS_TMP_FILES . '/' . basename( $img_name ) );
		$image = $this->imgShadow( $resource );
		
		$file_name = str_random( 20 );
		
		return array(
			'img' 		=> $this->upload->put( $image, IPS_TMP_FILES . '/' . ips_pathinfo( $img_name, PATHINFO_BASENAME ) ),
			'layers'	=> json_encode( array(
				/* 'img' 		=> basename( $img_name ), */
				'medium'	=> Canvas_Helper::store( $base64_medium['img'], 'medium_' . $file_name, $this->upload ),
				'large'		=> Canvas_Helper::store( $base64_large['img'], 'large_' . $file_name, $this->upload )
			))
		);
		
	}

	/**
	 * Put text only image layer
	 *
	 * @param 
	 * @param 
	 * 
	 * @return resource $image
	 */
	public function setLayers( $layer )
	{
		if( !$layer )
		{
			throw new Exception( 'Text layers set error' );
		}
		return json_decode( $layer, true );
	}
	
	/**
	 * Adapt/Resize image to fit text layer
	 *
	 * @param 
	 * @param 
	 * 
	 * @return resource $image
	 */
	public function adaptLayer( $image, $size, $config )
	{
		$this->img_text = Gd::as_resource( IPS_TMP_FILES . '/' . basename( $this->layers[ $size ] ) );

		imagealphablending( $this->img_text, false );
		imagesavealpha( $this->img_text, true );

		return $this->imageFitFile( $image, imagesx( $this->img_text ), imagesy( $this->img_text ) );
	}
	
	/**
	 * Put text only image layer
	 *
	 * @param 
	 * @param 
	 * 
	 * @return resource $image
	 */
	public function addLayer( $image, $size, $config )
	{
		$x = imagesx( $image ) - imagesx( $this->img_text );
		$y = imagesy( $image ) - imagesy( $this->img_text );
	
		imagecopy( $image, $this->img_text, $x / 2, $y / 2, 0, 0, imagesx( $this->img_text ), imagesy( $this->img_text ) );
		
		$this->img_text = null;
		
		return $image;
	}
	
	/**
	 * Select random background from available added by admin
	 *
	 * @param 
	 * 
	 * @return string
	 */
	public static function randBg()
	{
		$files = glob( ABS_PATH . '/upload/system/upload_text_bg/activ__*', GLOB_NOSORT );
		
		$up_text_rand = Session::get( 'up_text_rand', array() );
		
		$files = array_values( array_diff( $files, $up_text_rand ) );
		
		if( empty( $files ) )
		{
			Session::clear( 'up_text_rand' );
			return self::randBg();
		}
		
		if( $files )
		{
			return Session::setChild( 'up_text_rand', null, $files[ mt_rand( 0, count( $files ) - 1 ) ] );
		}
		
		return '';
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}



?>