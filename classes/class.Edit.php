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

class Edit
{
	private $_id;
	
	private $row = array();
	
	private $_error;
	
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function editor( $id )
	{
		$this->upload_id = intval( $id );
		
		$this->row = PD::getInstance()->from( array( 
			'shares' => 's',
			IPS__FILES => 'up',
		) )->setWhere( array(
			'up.id' => $this->upload_id,
			's.upload_id' => $this->upload_id
		) )->join( 'upload_text t' )->on( 't.upload_id', 'up.id' )->getOne();
			
		
		if ( empty( $this->row ) )
		{
			return ips_redirect( false, 'err_unknown' );
		}
		
		
		$upload_file_data = Upload_Meta::get( $this->upload_id, 'upload_file_data' );
		
		$this->row = array_merge( $upload_file_data, $this->row );
		

		if ( $this->row['upload_type'] == 'video' )
		{
			$this->row['upload_video_url'] = $this->row['upload_video'];
			if( file_exists( IPS_VIDEO_PATH . '/' . $this->row['upload_video'] ) )
			{
				$this->row['upload_video_url'] = IPS_VIDEO_URL . '/' . $this->row['upload_video'];
			}
		}

		if ( !empty( $row['long_text'] ) )
		{
			$this->row['long_text'] = $row['long_text'];
		}
		
		
		$this->row['upload_tags'] = array_map( 'trim', explode( ',', Tags::getFileTags( $this->upload_id ) ) );
		
		if ( $this->row['upload_subtype'] == 'animation' )
		{
			$this->row['upload_type'] = 'image';
		}
		
		/* Get gallery images */
		if ( $this->row['upload_type'] == 'gallery' )
		{
			$this->row['long_text'] = $row['intro_text'];
			
			$images = unserialize( $row['long_text'] );
			
			$this->row['upload_images'] = $this->jsJson( $images, 'upload_gallery' );
		}
		/* Get ranking images */
		elseif ( $this->row['upload_type'] == 'ranking' )
		{
			$images = PD::getInstance()->select( 'upload_ranking_files', array(
				'upload_id' => $this->upload_id 
			) );
			
			$this->row['upload_images'] = $this->jsJson( $images, 'upload_ranking' );
		}
		
		/* $this->row['image'] = basename( $this->row['image'] );
		
		if ( $this->row['upload_subtype'] == 'animation' )
		{
			$this->row['image'] = substr( $this->row['image'], 0, -4 ) . '.gif';
		} */
		$message = false;
		
		if ( file_exists( IMG_PATH_BACKUP . '/' . $this->row['upload_image'] ) )
		{
			list( $width, $height )  = getimagesize( IMG_PATH_BACKUP . '/' . $this->row['upload_image'] );
			$this->row['width']     = $width;
			$this->row['height']    = $height;
			$this->row['file_name'] = ips_img( $this->row['upload_image'], 'backup' );
		}
		elseif ( $this->row['upload_type'] == 'demotywator' || $this->row['upload_type'] == 'mem' )
		{
			$message = 'edit_info_part';
		}
		
		if ( $this->row['upload_type'] == 'animation' )
		{
			Session::set( 'animated_gif_tmp',  substr( basename( $this->row['upload_image'] ), 0, -4 ) );
		}
		
		Session::set( 'upload_tmp', [
			'file_edit_id'	 => $this->upload_id,
			'file_edit_time' => time()
		] );
	}
	/**
	 * 
	 *
	 * @param $field
	 * @param $default
	 * 
	 * @return array
	 */
	public function field( $field, $default )
	{
		return isset( $this->row[ $field ] ) ? $this->row[ $field ] : $default;
	}
	
	/**
	 * 
	 *
	 * @param $row
	 * 
	 * @return array
	 */
	public function redirect( $row )
	{
		$message = false;
		
		if ( !file_exists( IMG_PATH_BACKUP . '/' . $row['upload_image'] ) )
		{
			if ( $row['upload_type'] == 'demotywator' || $row['upload_type'] == 'mem' )
			{
				$message = 'edit_info_part';
			}
		}
		
		if ( $row['upload_subtype'] == 'animation' )
		{
			$row['upload_type'] = 'image';
		}
		
		return ips_redirect( 'up/' . $row['upload_type'] . '/?edit_id=' . $row['id'], $message );
	}
	/**
	 * 
	 *
	 * @param $images
	 * 
	 * @return array
	 */
	public function jsJson( $images, $path )
	{
		foreach ( $images as $k => $image )
		{
			$images[ $k ] = 'upload/' . $path . '/' . $image['src'];
		}
		
		return "['" . implode( "','", $images ) . "']";
	}
	/**
	 * Check if user can edit file
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public function isEditable( $id )
	{
		if( !USER_LOGGED )
		{
			return false;
		}
		
		$upload_id = intval( $id );
		
		$row = PD::getInstance()->from( array( 
			IPS__FILES => 'up',
		) )->setWhere( array(
			'up.id' => $upload_id
		) )->getOne();
		
		if( empty( $row ) )
		{
			return false;
		}
		
		$user = getUserInfo( USER_ID, true );
		
		return ( empty( $user ) || ( $row['user_login'] != $user['login'] && !USER_MOD ) ) ? false : $row ;
	}
	
	public function editFile( $upload_image, $file, $upload_source, $private, $upload_video, $upload )
	{
		if ( is_int( $this->upload_id ) && !empty( $this->row ) )
		{
			$update = array(
				'title' => $file['title'],
				'upload_source' => $upload_source,
				'seo_link' => seoLink( false, $file['title'] ) 
			);
			
			if ( empty( $upload_image ) )
			{
				$update['upload_image'] = $this->row['upload_image'];
			}
			else
			{
				$update['top_line']   = $file['top_line'];
				$update['bottom_line']    = $file['bottom_line'];
				$update['upload_type']  = $file['type'];
				$update['upload_image'] = $upload_image;
				
				Operations::deleteImages( $this->row );
			}
			
			if ( !empty( $upload_video ) )
			{
				$update['upload_video'] = $upload_video;
			}
			
			if ( $this->category_id )
			{
				$update['category_id'] = intval( $this->category_id );
			}
			
			$update['upload_data'] = serialize( $upload->config['final_dimensions'] );
			
			PD::getInstance()->update( IPS__FILES, $update, array(
				'id' => $this->upload_id
			) );
			
			if ( $this->tags )
			{
				PD::getInstance()->delete( array(
					'upload_tags_post'
				), array(
					'upload_id' => $this->upload_id
				) );
				
				Ips_Registry::get( 'Upload_Tags' )->saveTags( $this->tags, $this->upload_id );
			}
			
			if ( isset( $this->editArticleText ) )
			{
				PD::getInstance()->update( "upload_text", array(
					'intro_text' => $this->editArticleText['intro_text'],
					'long_text' => $this->editArticleText['long_text'] 
				), array( 
					'upload_id' => $this->upload_id
				));
			}
			
			if ( isset( $this->editRankingImages ) )
			{
				PD::getInstance()->delete( 'upload_ranking_files', array(
					'upload_id' => $this->upload_id 
				) );
				
				foreach ( $this->editRankingImages as $image )
				{
					PD::getInstance()->insert( 'upload_ranking_files', array(
						'upload_id' => $this->upload_id,
						'image' => basename( $image ) 
					) );
				}
				
			}
			
			Upload_Meta::update( $this->upload_id, 'upload_file_data', array(
				'image' => $upload->getName(),
				'font_color' => $upload->config['font_color'],
				'font_name' => $upload->config['font'] 
			) );
			
			//echo PD::getInstance()->debug();
			//print_r($update);
			//print_r($dane);
			//var_dump($this);
			//print_r($this->tags);
			//echo $upload_video;
			//exit;
		}
		Session::clear( 'upload_tmp' );
	}
}
?>
