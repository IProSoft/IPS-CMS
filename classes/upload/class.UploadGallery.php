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

class Upload_Gallery extends Upload_Types
{
	public $intro = '';
	/**
	 * Class constructor
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		$this->opts = $this->init( 'gallery_options', 'ranking' );
		
		$this->path = IPS_GELLERY_IMG_PATH;
		
		add_action( 'up_insert_row', array( $this, 'insertImages' ) );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setUpload( Upload_Extended $upload )
	{
		$this->upload = $upload;
	}
	/**
	 * Generating content gallery.
	 *
	 * @param json $obrazki - list of images as an array of JSON
	 *
	 * @return string $ready_files - gallery in the form of a list of images <img>
	 *
	 */
	public function uploadImages( $images )
	{
		$this->images = $this->upload->uploadMultiple( $images, $this->path, array_merge( $this->opts, array(
			'limit' => 2
		) ) );
	}
	/**
	 * Assigning and "cleaning" the content of the intro to the gallery.
	 *
	 * @param string $text - intro content
	 * @return void
	 */
	public function description( $long_text )
	{
		if ( !isset( $this->intro[ 1 ] ) )
		{
			throw new Exception( 'err_description' );
		}
		
		$this->intro = $this->upload->clearHtml( $long_text );
		
		if ( !isset( $this->intro[ 2 ] ) )
		{
			throw new Exception( 'err_description_clean' );
		}
		
		if( !isset( $this->intro[ $this->opts['description_length'] - 1 ] ) )
		{
			throw new Exception( __s( 'err_description_short', $this->opts['description_length'] ) );
		}
		
		$this->intro = htmlspecialchars_decode( $this->intro );
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function insertImages( $upload )
	{
		$images = $this->upload->storeMultiple( $this->images['files'], $this->path, $this->images['path'], $this->opts );
		
		$this->upload->addUploadText( array(
			'intro_text' => $this->intro,
			'long_text' => serialize( $images )
		), $upload->file_add_id );
		
		/*
		foreach ( $images as $image )
		{
			PD::getInstance()->insert( 'gallery_files', array(
				'upload_id' => $upload_id,
				'image'		=> $image
			) );
		}
		*/
	}
}