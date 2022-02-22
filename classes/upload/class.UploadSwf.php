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

class Upload_Swf
{
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getUpload( $files )
	{
		$video_url = isset( $files['url'] ) ? $files['url'] : get_input( $files['post'], false );
				
		if( isset( $files['file'] ) && empty( $video_url ) )
		{
			return $this->uploadFile( $files['file'] );
		}

		return $this->uploadUrl( $video_url );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function uploadUrl( $upload_video )
	{
		if( !$upload_video )
		{
			throw new Exception( 'err_video_file_format' );
		}
		
		return $this->upload( $upload_video, [
			'url' => $upload_video
		]);
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function uploadFile( $input_id )
	{
		if( !isset( $_FILES[$input_id]['name'] ) || empty( $_FILES[$input_id] ) )
		{
			throw new Exception( 'err_video_file_format' );
		}
		
		return $this->upload( $_FILES[$input_id]['name'], [
			'files' => $input_id
		]);
	}
	/**
	 * Upload file to server
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function upload( $upload_video, $config )
	{
		$upload = new Upload_Single_File();
			
		$upload->setConfig( $config );
		
		$file_data = ips_pathinfo( basename( $upload_video ) );	
		
		if ( $file_data['extension'] !== 'swf' )
		{
			throw new Exception( 'err_file_extension' );
		}
		
		$file_name = time() . '_' . $file_data['filename'];
		
		if( strlen( $file_name ) > 500 )
		{
			$file_name = time() . '_' . md5( $file_data['filename'] );
		}
		
		$upload->set_ext = 'swf';
		
		$upload->_file = $upload->setImage( $upload->_file );
		
		$file_name = $upload->storeFile( IPS_TMP_FILES . '/' . $file_name );
		
		list( $width, $height ) = getimagesize( IPS_TMP_FILES . '/' . $file_name );
		
		if( strpos( $upload_video, 'http' ) !== false )
		{
			unlink( IPS_TMP_FILES . '/' . $file_name );
			$file_name = $upload_video;
		}
		else
		{
			$file_name = Upload::handleVideoFile( $file_name, IPS_CURRENT_DATE );
		}
		
		return array(
			'width' => $width,
			'height' => $height,
			'upload_video' => $file_name,
			'image' => false
		);

	}
}
