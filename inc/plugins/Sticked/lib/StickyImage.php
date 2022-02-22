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
class StickyImage
{
	/**
	* 
	*/
	public $add = false;
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function __construct()
	{
		$this->opts = Config::getArray( 'plugin_sticked' );
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function initImageConfig( $config, $upload )
	{
		$this->add = ( in_array( $config['upload_type'], $this->opts['add_to'] ) && $config['upload_subtype'] != 'animation' );
		
		if( $this->add )
		{
			Config::tmp( 'watermark', 'off' );
		}
		
		return $config;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function beforeInsert( $data, $upload )
	{
		if( $this->add )
		{
			$data['upload_data'] = serialize( array_merge( unserialize( $data['upload_data'] ), [
				'stick' => $this->opts['height']
			] ) );
		}
		return $data;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function addLayer( $image, $size, $config, $upload )
	{
		$watermark_file = ABS_PATH . '/upload/system/watermark/' . $this->opts['file'];
		
		if ( is_file( $watermark_file ) )
		{
			include_once( LIBS_PATH . '/InterventionImage/autoload.php' );
			
			$watermark = Image::make( $watermark_file );
			
			$img = Image::canvas( imagesx( $image ), imagesy( $image ) + $watermark->getHeight(), $this->opts['color'] )
				->insert( $image, 'top-left' )
				->insert( $watermark, 'bottom-' . $this->opts['align'] )
				->encode( 'png', 100 );
			
			return imagecreatefromstring( $img );
		}
		return $image;
	}
}


?>