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

class Upload_Ranking extends Upload_Types
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
		$this->opts = $this->init( 'ranking_options', 'ranking' );
		
		$this->path = IPS_RANKING_IMG_PATH;
		
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
	 * Assigning and "cleaning" the content of the intro to the gallery.
	 *
	 * @param string $long_text - intro content
	 * 
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
	 * Generating content ranking.
	 *
	 * @param json $images - list of images as an array of JSON
	 * @param int $limit - minimum of added images
	 *
	 * @return string $ready_files - ranking
	 *
	 */
	public function uploadImages( $images, $limit = 2 )
	{
		$this->images = $this->upload->uploadMultiple( $images, $this->path, array_merge( $this->opts, array(
			'limit' => $limit
		) ) );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getRankingImg( $image )
	{
		if ( !$image['url'] )
		{
			return Gd::as_resource( $image['src'] );
		}
		else
		{
			return imagecreatefromstring( curlIPS( $image['src'], array(
				'timeout' => 5,
				'refferer' => 'http://' . parse_url( $image['src'], PHP_URL_HOST ) 
			) ) );
		}
	}
	
	/**
	 * Intro image generating ranking.
	 *
	 * @param 
	 *
	 * @return string 
	 *
	 */
	public function introImage( $images )
	{
		if( $this->opts['create_image'] == 0 )
		{
			return null;
		}

		$img_1 = $this->getRankingImg( $images[ 0 ] );
		$img_2 = $this->getRankingImg( $images[ 1 ] );

		if( !$img_1 || !$img_2 )
		{
			throw Exception( 'err_ranking_intro' );
		}
		
		$width = ( Config::get( 'file_max_width' ) / 2 ) - 10;
		
		$img_1    = $this->upload->simpleResizeImage( $img_1, $width );
		$img_2    = $this->upload->simpleResizeImage( $img_2, $width );
		$height   = imagesy( $img_1 );
		$height_2 = imagesy( $img_2 );
		
		if ( $height < $height_2 )
		{
			$height = $height_2;
		}
		
		$containerIMG = imagecreatetruecolor( Config::get( 'file_max_width' ), $height + 10 );
		
		$ranking_bg_color = Gd::hex_convert( $this->opts['bg_color'] );
		
		$im = imagecolorallocate( $containerIMG, $ranking_bg_color[ 'r' ], $ranking_bg_color[ 'g' ], $ranking_bg_color[ 'b' ] );
		imagefill( $containerIMG, 0, 0, $im );
		
		$imagesx_1 = imagesx( $img_1 );
		$imagesy_1 = imagesy( $img_1 );
		$y_pos_1   = $y_pos_2 = 5;
		
		if ( imagesy( $img_1 ) > imagesy( $img_2 ) )
		{
			$y_pos_2 = ( ( $height + 10 ) - imagesy( $img_2 ) ) / 2;
		}
		elseif ( imagesy( $img_1 ) < imagesy( $img_2 ) )
		{
			$y_pos_1 = ( ( $height + 10 ) - imagesy( $img_1 ) ) / 2;
		}
		
		imagecopymerge( $containerIMG, $img_1, 5, $y_pos_1, 0, 0, imagesx( $img_1 ), imagesy( $img_1 ), 100 );
		imagecopymerge( $containerIMG, $img_2, 15 + $imagesx_1, $y_pos_2, 0, 0, imagesx( $img_2 ), imagesy( $img_2 ), 100 );
		
		$vs_ranking        = imagecreatefrompng( ABS_PATH . '/images/vs_ranking.png' );
		$vs_ranking_width  = imagesx( $vs_ranking );
		$vs_ranking_height = imagesy( $vs_ranking );
		
		imagealphablending( $containerIMG, true );
		imagealphablending( $vs_ranking, true );
		
		imagecopy( $containerIMG, $vs_ranking, ( $width + 10 ) - ( $vs_ranking_width / 2 ), ( $height / 2 ) - ( $vs_ranking_height / 2 ), 0, 0, $vs_ranking_width, $vs_ranking_height );
		imagedestroy( $vs_ranking );
		
		$ribbon        = imagecreatefrompng( ABS_PATH . '/images/ribbon_ranking.png' );
		$ribbon_width  = imagesx( $ribbon );
		$ribbon_height = imagesy( $ribbon );
		
		imagealphablending( $containerIMG, true );
		imagealphablending( $ribbon, true );
		
		imagecopy( $containerIMG, $ribbon, 0, 0, 0, 0, $ribbon_width, $ribbon_height );
		imagedestroy( $ribbon );
		
		return $this->upload->put( $containerIMG, $this->path . '/' . $images[0]['upload_path_name'] . '/' . str_random( 10 ) );
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
		$images = $upload->storeMultiple( $this->images['files'], $this->path, $this->images['path'], $this->opts );
		
		foreach ( $images as $image )
		{
			PD::getInstance()->insert( 'upload_ranking_files', array(
				'upload_id' => $upload->file_add_id,
				'src' => $image ['src']
			) );
		}
		
		$this->upload->addUploadText( array(
			'intro_text' => '',
			'long_text' => $this->intro
		), $upload->file_add_id );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function addImages( $upload_id )
	{
		$images = $this->upload->storeMultiple( $this->images['files'], $this->path, $this->images['path'], $this->opts );
		
		foreach ( $images as $image )
		{
			PD::getInstance()->insert( 'upload_ranking_files', array(
				'upload_id' => $upload_id,
				'image' => $image ['src']
			) );
		}
	}
}