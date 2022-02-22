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

class Upload_Animation extends Upload_Types
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
		$this->opts = $this->init( 'animation_options', 'animation' );
		
		add_action( 'up_insert_row', array( $this, 'clear' ) );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function generate()
	{
		if( !Session::has('animated_gif_tmp') )
		{
			Session::set( 'animated_gif_tmp', 'animation_' . str_random( 10 ) );
		}

		if( $images = get_input( 'images' ) )
		{
			$files = json_decode( $images );

			if( empty( $files ) )
			{
				die();
			}
			
			$files = $this->adaptFiles( $files );
			
			$fps = intval( get_input( 'fps', 1 ) );

			require_once( LIBS_PATH . '/GifCreator/GifCreator.php' );

			$gif = new GifCreator( 0, 2, array(-1, -1, -1), $this->opts['width'], $this->opts['height'] );
			
			$gif->addFrame( file_get_contents( $this->emptyFrame() ), 0, false, $this->opts['width'], $this->opts['height'] );

			foreach( $files as $key => $file )
			{
				$gif->addFrame( file_get_contents( $file['img'] ), 100/$fps, (bool)$this->opts['const_size']);
			}

			File::put( IPS_TMP_FILES . '/' . Session::get('animated_gif_tmp') . '.gif', $gif->getAnimation() );

			return array(
				'fps' 	=> $fps,
				'image'	=> Session::get('animated_gif_tmp') . '.gif?' . rand(),
			);
		}
	}
	
	public function adaptFiles( $files )
	{
		$resize = new Upload_Extended();
		
		foreach( $files as $key => $file )
		{
			if( filter_var( $file, FILTER_VALIDATE_URL ) === false )
			{
				$file = ABS_PATH . '/' . trim( $file, '/' );
			}
			
			$img = Gd::as_resource( $file );
			
			$width = imagesx( $img );
			$height = imagesy( $img );
			
			if( !$this->opts['const_size'] && imagesx( $img ) > $this->opts['size']['medium'] )
			{
				$img = $resize->cropImage( $img, $this->opts['size']['medium'] );
				$file = $resize->put( $img, IPS_TMP_FILES . '/' . str_random( 10 ) );
			}
			
			$files[$key] = array(
				'w' => imagesx( $img ),
				'h' => imagesy( $img ),
				'img' => $file
			);
		}
		
		if( !$this->opts['const_size'] )
		{
			$this->opts['width'] = max( array_column( $files, 'w' ) );
			$this->opts['height']= max( array_column( $files, 'h' ) );
		}
		
		return $files;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function emptyFrame()
	{
		$file = CACHE_PATH . '/img_cache/transparent.png';
		
		$image = imagecreatetruecolor( $this->opts['width'], $this->opts['height'] );
		imagesavealpha( $image, true );
		imagefill( $image, 0, 0, imagecolorallocatealpha( $image, 0, 0, 0, 127 ) );
		imagepng( $image, $file );
		
		return $file;
	}
	/**
	 * Remove unused files
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function clear()
	{
		$animated_gif_tmp = Session::clear( 'animated_gif_tmp' );
		
		$frames = glob( IPS_TMP_FILES . '/animation_*', GLOB_NOSORT );
				
		foreach( $frames as $image )
		{
			if( strpos( $image, $animated_gif_tmp . '-' ) !== false )
			{
				unlink( $image );
			}
		}
	}
}