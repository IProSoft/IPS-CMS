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

class Upload_Mem extends Upload_Types
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
		$this->opts = $this->init( 'upload_mem_text', 'mem', IPS_ACTION == 'up'  || IPS_ACTION == 'mem'  );
		
		if( $this->opts['add_background'] && $this->opts['background_opacity'] != 0 )
		{
			$color = Gd::hex_convert( $this->opts['background_color'] );
			$this->opts['background_color'] = 'rgba( ' . $color['r'] . ', ' . $color['g'] . ', ' . $color['b'] . ', ' . ( ( 100 - $this->opts['background_opacity'] )/100 ) . ' )';
		}
		
		if( $this->opts['add_border'] && $this->opts['border_opacity'] != 0 )
		{
			$color = Gd::hex_convert( $this->opts['border_color'] );
			$this->opts['border_color'] = 'rgba( ' . $color['r'] . ', ' . $color['g'] . ', ' . $color['b'] . ', ' . ( ( 100 - $this->opts['border_opacity'] )/100 ) . ' )';
		}
		
		$this->opts['size']['medium_canvas'] -= 2 * ( $this->opts['margin']['side'] + $this->opts['margin']['border_width'] );
		$this->opts['size']['large_canvas'] -= 2 * ( $this->opts['margin']['side'] + $this->opts['margin']['border_width'] );
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
			'upload_type' => 'mem',
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
		
		$img_long = Gd::as_resource( IPS_TMP_FILES . '/' . basename( $this->layers['bottom_line'][ $size ] ) );
		
		if( isset( $this->layers['top_line'] ) )
		{
			$img_short = Gd::as_resource( IPS_TMP_FILES . '/' . basename( $this->layers['top_line'][ $size ] ) );
		}
		else
		{
			$img_short = imagecreatetruecolor( imagesx( $img_long ), 1 );
		}
		
		$width = max( imagesx( $image ), imagesx( $img_long ), imagesx( $img_short ) );
		
		$img = Gd::img_colored_fill( array(
			'width' => $width,
			'height' => imagesy( $image ),
			'transparent' => true
		) );
		
		$lines = [
			'image' => [
				'resource' => $image,
				'x' => $this->centerWidth( $width, imagesx( $image ) ),
				'y' => 0
			],
			'short' => [
				'resource' => $img_short,
				'x' => $this->centerWidth( $width, imagesx( $img_short ) ),
				'y' => $this->opts['margin']['top'] + $this->opts['margin']['border_width']
			],
			'long' => [
				'resource' => $img_long,
				'x' => $this->centerWidth( $width, imagesx( $img_long ) ),
				'y' => 0
			],
		];
		
		$lines = array_replace_recursive( $lines, [
			'long' => [
				'y' => imagesy( $image ) - imagesy( $img_long ) - $this->opts['margin']['top'] - $this->opts['margin']['border_width']
			]
		] );
		
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
		
		return $img;
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

			if( !is_image( $upload['content'] ) )
			{
				return array(
					'error' => __( 'err_uploaded_file' )
				);
			}
			
			$upload['content'] = $this->resize( $upload['content'] );
		}
		
		return $upload;
	}
}