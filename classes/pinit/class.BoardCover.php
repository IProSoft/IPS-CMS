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

	
class BoardCover
{
	public $upload_handler = false;
	
	public $json_images = array();

	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getHandler()
	{
		if( !$this->upload_handler )
		{
			$this->upload_handler = new Upload_Handler_Extended( null );
		}
		
		return $this->upload_handler;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getImages( &$board )
	{
		if( !isset( $board['board_cover'] ) )
		{
			return (object)array();
		}
		
		if( isset( $this->json_images[ $board['board_id'] ] ) )
		{
			return (object)$this->json_images[ $board['board_id'] ];
		}
		
		$this->json_images[ $board['board_id'] ] = (object)json_decode( $board['board_cover'] );
		
		if( !is_object( $this->json_images[ $board['board_id'] ] ) )
		{
			/** No cover/thumbs created */
			return (object)array();
		}
		
		return (object)$this->json_images[ $board['board_id'] ];
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getCover( &$board )
	{
		$board_images = $this->getImages( $board );
		
		if( isset( $board_images->cropped_cover ) )
		{
			return ips_img( $board_images->cropped_cover, 'board/cropp' );
		}
		
		if( isset( $board_images->cover ) )
		{
			/*
			* Return cover
			*/
			return ips_img( $board_images->cover, 'board/cover' );
		}
		elseif(  $board['board_pins'] > 0 )
		{
			$pin_image = PD::getInstance()->optRand( IPS__FILES, array(
				'board_id' => $board['board_id']
			), 1 );
			
			return ips_img( $this->createCover( $board, ips_img_path( $pin_image['upload_image'], 'large' ), true ), 'board/cover' );
		}

		return 'images/pinit/board_cover.png';
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getThumb( &$board )
	{
		$board_images = $this->getImages( $board );
		
		if( isset( $board_images->thumbnail ) )
		{
			/*
			* Return thumbnail
			*/
			return ips_img( $board_images->thumbnail, 'board/thumbnail' );
		}
		elseif( $board['board_pins'] > 0 )
		{
			if( isset( $board_images->cropped_cover ) && !empty( $board_images->cropped_cover ) )
			{
				$image = ips_img_path( $board_images->cropped_cover, 'board/cropp' );
				if( !file_exists( $image ) )
				{
					unset( $image );
				}
			}
			if( !isset( $image ) )
			{
				$pin_image = PD::getInstance()->optRand( IPS__FILES, array(
					'board_id' => $board['board_id']
				), 1 );
				
				$image = ips_img_path( $pin_image['upload_image'], 'large' );
			}
			return ips_img( $this->createThumb( $board, $image, true ), 'board/thumbnail' );
		}
		
		return 'images/pinit/board_thumbnail.png';
	}
	
	/**
	* Create image from pin img
	*
	* @param array $board
	* @param string $source
	* @param array $options
	* 
	* @return 
	*/
	public function create( $board, $source, $options )
	{
		$file = 'board_id-' . $board['board_id'] . '-' . $options['name'] . ( strpos( $options['name'], '.' ) === false ? '.' . pathinfo( $source, PATHINFO_EXTENSION ) : '' );
		
		$image_folder = createFolderByDate( ips_img_path( '', 'board/' . ( isset( $options['dir'] ) ? $options['dir'] : $options['name'] ) ), $board['date_add'], 'path' );

		$this->getHandler()->create_scaled_image( $file, false, array_merge( array(
			'file_path_ips' => $source,
			'file_path_save' => $image_folder . '/' . $file,
			'crop' => true,
			'jpeg_quality' => 100,
			'upload_dir' => $image_folder . '/'
		), $options ) );
		
		if( $options['update_board'] )
		{
			$board_images = $this->getImages( $board );
			
			$board_images->$options['name'] = getFolderByDate( null, $board['date_add'] ) . '/' . $file;
			
			/**
			* Update board image
			*/
			PD::getInstance()->update( 'pinit_boards', array(
				'board_cover' => json_encode( $board_images )
			), array(
				'board_id' => $board['board_id']
			) );
		}
		
		return getFolderByDate( null, $board['date_add'] ) . '/' . $file;
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function createCover( $board, $image, $db_update = false )
	{
		return $this->create( $board, $image, array(
			'name'			=> 'cover',
			'max_width' 	=> 216,
			'max_height' 	=> 150,
			'update_board'	=> $db_update
		) );
	}
	
	/**
	* Crop pin image to fit cover size from user coords
	*
	* @param 
	* 
	* @return 
	*/
	public function cropCover( $upload_info )
	{
		$img = ips_img_path( $upload_info['upload_image'], 'large' );
		
		if( !file_exists( $img ) )
		{
			throw new Exception('Nie można odnaleźć ścieżki obrazu.');
		}
		
		$crop = json_decode( $upload_info['image_cropp'] );
		
		$img = Gd::as_resource( $img );
		
		if( $crop->angle > 0 )
		{
			$img = imagerotate( $img, - $crop->angle, 0 );
		}
		
		/* JS error */
		$crop->scale += 0.08474576271186;
		
		$crop->h = imagesx( $img ) / $crop->w * $crop->h;
		$crop->x = imagesx( $img ) / $crop->w * $crop->x;
		$crop->y = imagesx( $img ) / $crop->w * $crop->y;
		$crop->w = imagesx( $img );
		
		if( $crop->scale > 1 )
		{
			$ratio = imagesx( $img ) / ($crop->scale * imagesx( $img ));
			array_walk( $crop, function( &$value, $key, $ratio ) {
				$value *= $ratio;
			}, $ratio );
		}
		
		$image = imagecreatetruecolor( $crop->w, $crop->h );

		imagecopy( $image, $img, 0, 0, $crop->x, $crop->y, imagesx( $img ), imagesy( $img ) );
		
		$crop = json_decode( $upload_info['image_cropp'] );
		
		$final_image = imagecreatetruecolor( $crop->w, $crop->h );
		imagecopyresampled( $final_image, $image, 0, 0, 0, 0, $crop->w, $crop->h, imagesx( $image ), imagesy( $image ) );
		
		unset( $img, $image );
		
		
		/** Problem jeśli aktualizacji lub nowy PIN */
		$b = new Board;
		$board = $b->getBoard( $upload_info['board_id'] );
		
		$board_images = $this->getImages( $board );
		
		$board_images->source_image = $upload_info['upload_image'];
		
		$cover = ips_img_path( $board_images->cover, 'board/cropp' );
		
		if( !is_dir( dirname( $cover ) ) )
		{
			mkdir( dirname( $cover ), 0777, true );
		}
		
		imagejpeg( $final_image, $cover, 100 );
		
		$board_images->cropped_cover = $board_images->cover;
		
		PD::getInstance()->update( 'pinit_boards', array(
			'board_cover' => json_encode( array() )
		), array(
			'board_id' => $upload_info['board_id']
		) );
		
		return ips_img( $board_images->cover, 'board/cropp' );
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function createThumb( $board, $image, $db_update = false )
	{
		return $this->create( $board, $image, array(
			'name'			=> 'thumbnail',
			'max_width' 	=> 60,
			'max_height' 	=> 60,
			'update_board'	=> $db_update
		) );
	}
	

	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function deleteCovers( $board_images )
	{
	
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function createThumbs( $board, $file_thumbs = array() )
	{
		$db = PD::getInstance();
		
		$board_images = $this->getImages( $board );
		
		$not_id = array();
		
		if( !empty( $file_thumbs ) )
		{
			foreach( $file_thumbs as $key => $thumb )
			{
				$not_id[] = preg_replace( '/board_id-' . $board['board_id'] . '-thumbs-([0-9]+).([A-Za-z]+)/i', '\\1.\\2', basename( $thumb ) );
			}
		}
		
		$board_pins = $db->optRand( IPS__FILES, array(
			'board_id' => $board['board_id'],
			'id' => array(
				$not_id,
				'NOT IN'
			),
			'repin_from' => array( array_column( $db->select( IPS__FILES, array(
					'board_id' => $board['board_id'],
					'id' => $not_id
				), 10, 'id'), 'id' ),
				'NOT IN'
			)
		), 4 );
		
		if( !empty( $board_pins ) )
		{
			$board_pins = array_slice( $board_pins, 0, 4 - count( $file_thumbs ) );
			
			foreach( $board_pins as $upload_image )
			{
				$thumb_file = $this->create( $board, ips_img_path( $upload_image['upload_image'], 'large' ), array(
					'name'			=> 'thumbs-' . intval( $upload_image['id'] ),
					'dir'			=> 'thumbs',
					'max_width' 	=> 60,
					'max_height' 	=> 60,
					'update_board'	=> false
				) );

				array_unshift( $file_thumbs, $thumb_file );
			}
			
			$file_thumbs = array_slice( $file_thumbs, 0, 4 );
			
			if( !empty( $file_thumbs ) )
			{
				$board_images->file_thumbs = $file_thumbs;
				
				/**
				* Update board images data even if empty
				*/
				$db->update( 'pinit_boards', array(
					'board_cover' => json_encode( $board_images )
				), array(
					'board_id' => $board['board_id']
				) );
			}
		}
		
		return $file_thumbs;
	}
	
	/**
	* Update board cover/thumbnail/thumbs
	*
	* @param $pin_image - passed while PIN delete and board update
	* 
	* @return 
	*/
	public function boardCoversUpdate( $board, $pin_image )
	{
		$db = PD::getInstance();
		
		$board_images = json_decode( $board['board_cover'] );
		
		$skip_update = array();
		
		if( is_null( $board_images ) )
		{
			/** No cover/thumbs created */
			$board_images = (object)array();
		}
		else
		{
			if( isset( $board_images->source_image ) && strpos( $board_images->source_image, $pin_image['upload_image'] ) === false )
			{
				$skip_update['cover'] = true;
			}
			
			if( isset( $board_images->file_thumbs ) && strpos( implode( '', $board_images->file_thumbs ), $board['board_id'] . '-thumbs-' . $pin_image['id'] ) === false )
			{
				$skip_update['thumbs'] = true;
			}
		}
	
		$pin_image = $db->optRand( IPS__FILES, array(
			'board_id' => $board['board_id']
		), 1 );

		if( !empty( $pin_image ) )
		{
			$image = ips_img_path( $pin_image['upload_image'], 'large' );
			
			/** Update cover and main thumb */
			if( !isset( $skip_update['cover'] ) )
			{
				if( isset( $board_images->cropped_cover ) )
				{
					File::deleteFile( ips_img_path( $board_images->cropped_cover, 'board/cropp' ) );
					unset( $board_images->cropped_cover );
				}
				
				if( isset( $board_images->cover ) )
				{
					File::deleteFile( ips_img_path( $board_images->cover, 'board/cover' ) );
				}
				
				if( isset( $board_images->thumbnail ) )
				{
					File::deleteFile( ips_img_path( $board_images->thumbnail, 'board/thumbnail' ) );
				}
				
				$board_images->source_image = $pin_image['upload_image'];
				$board_images->cover = $this->createCover( $board, $image, true );
				$board_images->thumbnail = $this->createThumb( $board, $image, true );
			}
			
			/** Update small thumbs */
			if( !isset( $skip_update['thumbs'] ) )
			{
				if( !empty( $board_images->file_thumbs ) )
				{
					foreach( $board_images->file_thumbs as $thumb )
					{
						File::deleteFile( ips_img_path( $thumb, 'board/thumbs' ) );
					}
				}
				$board_images->file_thumbs = $this->createThumbs( $board, array() );
			}
		}
		else
		{
			$board_images = (object)array();
		}
		
		/**
		* Update board images data even if empty
		*/
		$db->update( 'pinit_boards', array(
			'board_cover' => json_encode( $board_images )
		), array(
			'board_id' => $board['board_id']
		) );
	}
	
}