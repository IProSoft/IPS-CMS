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
use Intervention\Image\ImageManagerStatic as Image;
class Watermark
{
	
	/**
	 * Add mask to image with site name or other
	 * 
	 */
	public $opaque_mask = false;
	
	public $image;
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		$this->opts = Config::getArray( 'watermark_options' );
		
		if ( Config::getArray( 'watermark_transparent', 'activ' ) != false )
		{
			$file = ABS_PATH . '/upload/system/watermark/' . Config::getArray( 'watermark_transparent', 'file' );
			
			if ( is_file( $file ) && $filemtime = filemtime( $file ) )
			{
				if( $cache = Ips_Cache::getDBCache( 'watermark_transparent', 'upload', true ) )
				{
					if( strtotime( $cache['cache_stored'] ) > $filemtime && !empty( $cache['cache_data'] ) )
					{	
						$img = Gd::as_resource( CACHE_PATH . '/' . $cache['cache_data'] );
						
						if( is_resource( $img ) )
						{
							return $this->opaque_mask = $img;
						}
					}
				}
				
				$this->opaque_mask = Gd::opacity( $file, ( 100 - Config::getArray( 'watermark_transparent', 'opacity' ) ) );
				
				if( $this->opaque_mask  )
				{
					$cache_file = 'img_cache/watermark_transparent_' . str_random( 5 ) . '.png';
				
					imagepng( $this->opaque_mask, CACHE_PATH . '/' . $cache_file );
					
					Ips_Cache::storeDBCache( 'watermark_transparent', $cache_file, 'upload' );
				}
			}
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function get( $image, $top, $left )
	{
		$this->opts = Config::getArray( 'watermark_options' );
		
		$this->setPosition( $left, $top );

		if ( Config::get( 'watermark' ) == 'as_image' )
		{
			$image = $this->asImage( $image );
		}
		elseif ( Config::get( 'watermark' ) == 'as_text' )
		{
			$image = $this->asText( $image );
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
	public function addTransparentWatermark( $image )
	{
		if ( !is_resource( $image ) )
		{
			return $image;
		}
		
		if ( $this->opaque_mask )
		{
			$image = imagepalettetotruecolor_wrapper( $image );
			
			$angle = Config::getArray( 'watermark_transparent', 'angle' );
			
			$src_w = imagesx( $this->opaque_mask );
			$src_h = imagesy( $this->opaque_mask );
			
			$img_watermark = imagecreatetruecolor( imagesx( $image ) + imagesy( $image ), $src_h );
			
			imagealphablending( $img_watermark, false );
			imagesavealpha( $img_watermark, true );
			
			$transparency = imagecolorallocatealpha( $img_watermark, 0, 0, 0, 127 );
			imagefill( $img_watermark, 0, 0, $transparency );
			
			imagesettile( $img_watermark, $this->opaque_mask );
			
			imagefilledrectangle( $img_watermark, 0, 0, imagesx( $image ) * 2, $src_h, IMG_COLOR_TILED );
			
			$img_watermark = imagerotate( $img_watermark, $angle, $transparency );
			
			$rand_from_top = rand( 20, imagesy( $image ) - imagesy( $img_watermark ) - 10 );
			
			imagecopy( $image, $img_watermark, -50, $rand_from_top, 0, 0, imagesx( $img_watermark ), imagesy( $img_watermark ) );
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
	public function stickWatermark( $image )
	{
		$watermark_file = ABS_PATH . '/upload/system/watermark/' . Config::getArray( 'plugin_sticked', 'file' );
		
		if ( is_file( $watermark_file ) )
		{
			$_stick_watermark_height = Config::getArray( 'plugin_sticked', 'height' );
			$_stick_watermark_color  = Config::getArray( 'plugin_sticked', 'color' );
			
			/* 
			$watermark = array_random( scandir_folder('watermark/') );
			$image = imagecreatefrompng($watermark);
			*/
			$oryginalny_width  = imagesx( $image );
			$oryginalny_height = imagesy( $image );
			
			$watermark        = imagecreatefrompng( $watermark_file );
			$watermark_width  = imagesx( $watermark );
			$watermark_height = imagesy( $watermark );
			
			$create_width = 0;
			
			if ( Config::getArray( 'plugin_sticked', 'direction' ) == 'right' )
			{
				$create_width = $oryginalny_width - $watermark_width;
				if ( $create_width < 0 )
				{
					$create_width = 0;
				}
			}
			
			$new_image = imagecreatetruecolor( $oryginalny_width, $_stick_watermark_height );
			$color     = Gd::hex_convert( $_stick_watermark_color );
			imagefilledrectangle( $new_image, 0, 0, $oryginalny_width, $_stick_watermark_height, imagecolorallocate( $new_image, $color['r'], $color['g'], $color['b'] ) );
			
			imagealphablending( $watermark, false );
			imagesavealpha( $watermark, true );
			imagecopy( $new_image, $watermark, $create_width, 0, 0, 0, $oryginalny_width, $oryginalny_height );
			
			$final_img = imagecreatetruecolor( $oryginalny_width, $oryginalny_height + $_stick_watermark_height );
			imagecopy( $final_img, $image, 0, 0, 0, 0, $oryginalny_width, $oryginalny_height );
			imagecopymerge( $final_img, $new_image, 0, $oryginalny_height, 0, 0, $oryginalny_width, $_stick_watermark_height, 100 );
			$image = $final_img;
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
	public function asImage( $image )
	{
		if ( is_file( ABS_PATH . '/upload/system/watermark/' . $this->opts['file'] ) )
		{
			include_once( LIBS_PATH . '/InterventionImage/autoload.php' );
			
			$watermark = Image::make( ABS_PATH . '/upload/system/watermark/' . $this->opts['file'] )->opacity( $this->opts['opacity'] );
			$img = Image::make( $image )
					->insert( $watermark, $this->opts['position'], $this->opts['position_x'], $this->opts['position_y'] )
					->encode( 'png', 100 );

			return imagecreatefromstring( $img );
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
	public function asText( $image )
	{
		include_once( LIBS_PATH . '/ImageCraft/autoload.php' );

		$this->opts['text_font'] = Ips_Registry::get( 'Web_Fonts' )->getFontTtf( $this->opts['text_font'] );
		
		if( $this->opts['text_font'] )
		{
			include_once( LIBS_PATH . '/InterventionImage/autoload.php' );
			
			$img = Image::make( $image )
				->text( $this->opts['text'], $this->opts['position_x'], $this->opts['position_y'], function($font) {
					$font->file( $this->opts ['text_font']);
					$font->size( $this->opts['text_size'] );
					
					$color = Gd::hex_convert( $this->opts['text_font_color'] );
					
					$font->color( array( $color['r'], $color['g'], $color['b'], $this->opts['opacity']/100 ) );
					
					list( $valign, $align ) = explode( '-', $this->opts['position'] );
					
					$font->align( $align );
					$font->valign( $valign );
					
				})->encode( 'png', 100 );
			
			return imagecreatefromstring( $img );
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
	public function setPosition( $left, $top )
	{
		if ( $this->opts['position_absolute'] == 0 )
		{
			return false;
		}
		
		$points = array(
			'x' => $this->opts['position_x'],
			'y' => $this->opts['position_y']
		);
		
		switch( $this->opts['position'] )
		{
			case 'top_left':
			case 'top_right':
			case 'center_left':
			case 'center_right':
			case 'bottom_left':
			case 'bottom_right':
				$this->opts['position_x'] += $left;
				$this->opts['position_y'] += $top;
			break;
			default:
				$this->opts['position_y'] += $top;
			break;
		}
	}
	
}