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

class Upload_Demotywator extends Upload_Types
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
		$this->opts = $this->init( 'upload_demotywator_text', 'demotywator', IPS_ACTION == 'up'  );
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
		$top = get_input( 'top_line_layers', false );
		$bottom = get_input( 'bottom_line_layers', false );
		
		if( $top || $bottom )
		{
			$this->layers = array(
				'top_line' => json_decode( $top, true ),
				'bottom_line'	 => json_decode( $bottom, true )
			);
		}
		
		if( get_input( 'up_title_hide' ) == 'true' )
		{
			unset( $this->layers['top_line'] );
		}
		
		add_filter( 'up_actions_after_resize', array( 
			$this, 'saveSize'
		) );
		
		add_filter( 'up_actions_finish', array( 
			$this, 'addLayers'
		) );
		
	}

	
	/**
	 * Add margin and border to file
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function resize( $file )
	{
		$image = Gd::as_resource( $file );
		
		$this->upload->configuration( [
			'upload_type' => 'demotywator',
			'width' => false,
			'height' => false
		] );
		 
		$image = $this->upload->resizeImage( $image, 700, 'large' );
		
		return $this->upload->put( $image, IPS_TMP_FILES . '/' . str_random( 10 ) );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function storeLayers( $base64_medium, $base64_large, $layer_name )
	{
		$file_name = str_random( 20 );
		
		return array(
			'layers'	=> json_encode( array(
				/* 'img' 		=> basename( $img_name ), */
				'medium'	=> Canvas_Helper::store( $base64_medium['img'], 'medium_' . $file_name, $this->upload ),
				'large'		=> Canvas_Helper::store( $base64_large['img'], 'large_' . $file_name, $this->upload )
			))
		);
		
	}
	/**
	 * Add layers
	 *
	 * @param 
	 * @param 
	 * 
	 * @return resource $image
	 */
	public function saveSize( $image, $size, $config )
	{
		if( !isset( $this->size ) )
		{
			$this->size = [];
		}
		
		$this->size[$size] = array(
			'width' => imagesx( $image ),
			'height' => imagesy( $image )
		);
		
		return $image;
	}
	/**
	 * Add layers
	 *
	 * @param 
	 * @param 
	 * 
	 * @return resource $image
	 */
	public function addLayers( $image, $size, $up_config )
	{
		$img_bottom = Gd::as_resource( IPS_TMP_FILES . '/' . basename( $this->layers['bottom_line'][ $size ] ) );
		
		if( isset( $this->layers['top_line'] ) )
		{
			$img_top = Gd::as_resource( IPS_TMP_FILES . '/' . basename( $this->layers['top_line'][ $size ] ) );
		}
		else
		{
			$img_top = imagecreatetruecolor( imagesx( $img_bottom ), 1 );
		}
		
		$width = max( imagesx( $image ), imagesx( $img_bottom ), imagesx( $img_top ) );
		
		$img = Gd::img_colored_fill( array(
			'width' => $width,
			'height' => imagesy( $img_top ) + imagesy( $image ) + imagesy( $img_bottom ),
			'color' => $this->opts['margin']['box_color']
		) );
		
		$lines = [
			'top' => [
				'resource' => $img_top,
				'x' => $this->centerWidth( $width, imagesx( $img_top ) ),
				'y' => 0
			],
			'image' => [
				'resource' => $image,
				'x' => $this->centerWidth( $width, imagesx( $image ) ),
				'y' => 0
			],
			'bottom' => [
				'resource' => $img_bottom,
				'x' => $this->centerWidth( $width, imagesx( $img_bottom ) ),
				'y' => 0
			],
		];
		
		switch( $this->opts['vertical_align'] )
		{
			case 'top':
				$lines = array_replace_recursive( $lines, [
					'image' => [
						'y' => imagesy( $img_top ) + imagesy( $img_bottom )
					],
					'bottom' => [
						'y' => imagesy( $img_top )
					]
				] );
			break;
			
			case 'division':
				$lines = array_replace_recursive( $lines, [
					'image' => [
						'y' => imagesy( $img_top )
					],
					'bottom' => [
						'y' => imagesy( $img_top ) + imagesy( $image )
					]
				] );
			break;
			
			case 'bottom':
				$lines = array_replace_recursive( $lines, [	
					'top' => [
						'y' => imagesy( $image )
					],
					'bottom' => [
						'y' => imagesy( $img_top ) + imagesy( $image )
					]
				] );
			break;
		}
		
		foreach( $lines as $k => $l )
		{
			imagecopy(
				$img,
				$l['resource'], 
				$l['x'],
				$l['y'], 
				0,
				0,
				imagesx( $l['resource'] ),
				imagesy($l['resource'] )
			);
		}
		

		return $this->signature( $img, $width, $lines['image']['y'] + imagesy( $image ), $up_config );
	}
	/**
	 * Get width if grater
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function centerWidth( $max_width, $width )
	{
		return ( $max_width - $width ) / 2;
	}
	/**
	 * Add site adress to demot image as signature
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function signature( $image, $width, $start_point, $up_config )
	{
		$options = Config::getArray( 'upload_demot_signature' );
		
		if( $options['type'] != 'off' )
		{
			$start_point -= $this->opts['margin']['top'];
			
			$upload_signature_font = Ips_Registry::get( 'Web_Fonts' )->getFontTtf( $options['font'] );
			
			if ( $up_config['upload_subtype'] != 'video' && $up_config['upload_subtype'] != 'animation' )
			{
				if ( $options['type'] == 'text' )
				{
					/** From text on picture bottom */
					
					$text = $options['upper'] == 'up' ? strtoupper( $up_config['site_url'] ) : strtolower( $up_config['site_url'] );
					
					$text_dims = Gd::calculate_text_box( $options['font_size'], $upload_signature_font, $text, false );
					$imgc      = imagecreatetruecolor( $width, $text_dims['height'] );
					
					$color = Gd::hex_convert( $options['color'] );
					$color  = imagecolorallocate( $imgc, $color['r'], $color['g'], $color['b'] );
					
					$ctg      = 9;
					$signature_width = $text_dims['width'] + 20;
					$signature_height = $text_dims['height'] + 2;
					
					if ( $signature_height % 2 != 0 )
					{
						$signature_height += 1;
					}
					/* 
					$img_left = imagecreatefrompng( ABS_PATH . '/images/demotywator/tmp_signature_left.png' );
					$img_center = imagecreatefrompng( ABS_PATH . '/images/demotywator/tmp_signature_center.png' );
					$img_right = imagecreatefrompng( ABS_PATH . '/images/demotywator/tmp_signature_right.png' );
					
					if( $signature_height > imagesy( $img_left ) )
					{
						$img_left = $this->cropImage( $img_left, ( ( $signature_height * imagesx( $img_left ) ) / imagesy( $img_left ) ) );
						$img_center = $this->cropImage( $img_center, ( ( $signature_height * imagesx( $img_center ) ) / imagesy( $img_center ) ) );
						$img_right = $this->cropImage( $img_right, ( ( $signature_height * imagesx( $img_right ) ) / imagesy( $img_right ) ) );
					}
					elseif( $signature_height < imagesy( $img_left ) )
					{
						$signature_height = imagesy( $img_left );
					}
					
					$tmp_img = imagecreatetruecolor( $signature_width, $signature_height );
					imagealphablending( $tmp_img, true );
					imagesavealpha( $tmp_img, true );
					$transparency = imagecolorallocatealpha( $tmp_img, 0, 0, 0, 127 );
					imagefill( $tmp_img, 0, 0, $transparency );
					
					$i = 0;
					$pattern_w = imagesx( $img_center );
					$pattern_h = imagesy( $img_center );
					do{
						imagecopy( $tmp_img, $img_center, $i, 0, 0, 0, $pattern_w, $pattern_h );
						$i++;
					}
					while( $i <= $signature_width );
					
					
					
					$img = imagecreatetruecolor( imagesx( $img_left ) + $signature_width + imagesx( $img_right ), $signature_height );
					imagealphablending( $img, true );
					imagesavealpha( $img, true );
					$w = imagecolorallocatealpha( $img, 0, 0, 0, 127 );
					imagefill( $img, 0, 0, $transparency );
					
					imagecopy( $img, $img_left, 0, 0, 0, 0, imagesx( $img_left ), imagesy( $img_left ) );
					imagecopy( $img, $tmp_img, imagesx( $img_left ), 0, 0, 0, imagesx( $tmp_img ), imagesy( $tmp_img ) );
					imagecopy( $img, $img_right, imagesx( $img_left ) + imagesx( $tmp_img ), 0, 0, 0, imagesx( $img_right ), imagesy( $img_right ) );
					
					imagettftext( $img, $options['font_size'], 0, imagesx( $img_left ) + ( $signature_width - $text_dims['width'] ) / 2, ( $signature_height - ( $options['upper'] == 'up' ? $text_dims['height'] : $text_dims['height'] * 0.8 ) ) / 2, $color, $upload_signature_font, $text );
					
					$start_point += 2; */
					
					
					$img = imagecreatetruecolor( $signature_width + $ctg * 1.5, $signature_height );
					imagesavealpha( $img, true );
					imagefill( $img, 0, 0, imagecolorallocatealpha( $img, 0, 0, 0, 127 ) );
					
					$coord = array(
						0, ( $signature_height / 2 ),
						$ctg, 0,
						$signature_width, 0,
						( $signature_width + $ctg ), ( $signature_height / 2 ),
						$signature_width, $signature_height,
						$ctg, $signature_height 
					);
					
					$border = Gd::hex_convert( $up_config['file_border']['color'] );
					
					imagefilledpolygon( $img, $coord, 6, imagecolorallocate( $img, $border['r'], $border['g'], $border['b'] ) );
					imagepolygon( $img, $coord, 6, imagecolorallocate( $img, $border['r'], $border['g'], $border['b'] ) );
					imagettftext( $img, $options['font_size'], 0, ( ( $signature_width + $ctg ) - $text_dims['width'] ) / 2, ( $options['upper'] == 'up' ? $text_dims['height'] : $text_dims['height'] * 0.8 ), $color, $upload_signature_font, $text );
					
					//imagefilter( $img, IMG_FILTER_CONTRAST, -4 );
					$start_point += 1;
					
				}
				elseif ( $options['type'] == 'image' )
				{
					$file = ABS_PATH . '/upload/system/watermark/' . $options['file'];
					if ( is_file( $file ) )
					{
						/** From file on picture bottom */
						$img = Gd::as_resource( $file );
					}
				}
				
				if ( isset( $img ) )
				{
					$signature_x = imagesx( $img );
					$signature_y = imagesy( $img );
					imagealphablending( $image, true );
					imagealphablending( $img, true );
					
					imagecopy( $image, $img, ( imagesx( $image ) - $signature_x ) / 2, $start_point - ( $signature_y / 2 ) - $up_config['file_border']['width'], 0, 0, $signature_x, $signature_y );
				}
			}
			
			if( $options['type'] == 'text_bottom' )
			{
				/** On image text bottom */
				
				$txt_sizes = Gd::calculate_text_box( $options['font_size'], $upload_signature_font, $up_config['site_url'] );
			
				$signature = Gd::img_colored_fill( array(
					'width' => $width,
					'height' => $txt_sizes['height'] + ( $txt_sizes['height'] / 1.5 ),
					'color' => $this->opts['margin']['box_color']
				) );
				
				$color = Gd::img_colored( array(
					'color' => $options['color']
				) , $signature );
					
				$box = Gd::calculate_text_box( $options['font_size'], $upload_signature_font, $up_config['site_url'] );
				
				switch( $options['position'] )
				{
					case 'left':
						$position = 5;
					break;
					case 'right':
						$position = $width - $box['width'] - 5;
					break;
					case 'center':
					default:
						$position = ( $width - $box['width'] ) / 2;
					break;
				}
				
				imagettftext( $signature, $options['font_size'], 0, $position, $txt_sizes['height'] + ( $txt_sizes['height'] / 3 ), $color, $upload_signature_font, $up_config['site_url'] );
				
				$resized = imagecreatetruecolor( $width, imagesy( $image ) + imagesy( $signature ) );
				
				imagecopy( $resized, $image, 0, 0, 0, 0, imagesx( $image ), imagesy( $image ) );
				imagecopy( $resized, $signature, 0, imagesy( $image ), 0, 0, imagesx( $signature ), imagesy( $signature ) );

				$image = $resized;
				unset( $resized );

			}
		}
		
		return $image;
	}
	
	/**
	 * Add link with video
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function video( $url )
	{
		$video = new Upload_Video();
		return $video->getVideo( $url, array(
			'width' => $this->opts['size']['large']
		) );
	}
	
	
	/**
	 * Check if user uploaded photo or video
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function userUpload( $upload )
	{
		if( isset( $upload['content'] ) )
		{
			if( !is_file( $upload['content'] ) )
			{
				ips_log( func_get_args(), 'logs/upload.log' );
				return array(
					'error' => __( 'err_upload_layer' )
				);
			}

			if( $upload['ext'] == 'swf' || $upload['ext'] == 'mp4' )
			{
				return $this->video( $upload['content'] );
			}
			
			
			if( !is_image( $upload['content'] ) )
			{
				return array(
					'error' => __( 'err_uploaded_file' )
				);
			}
			
			if( $upload['ext'] != 'gif' )
			{
				$upload['content'] = $this->resize( $upload['content'] );
			}
		}
		
		return $upload;
	}
	

}