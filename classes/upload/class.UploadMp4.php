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

class Upload_Mp4
{
	
	public function __construct()
	{
		add_filter( 'up_actions_after_resize', array( 
			$this, 'storePoster'
		) );
	}
	
	public function storePoster( $image, $size, $config, $upload )
	{
		if( $size == 'large' )
		{
			$upload->put( $image, $config['up_folder']['media_poster'] . '/' . $config['name'] );
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
		$video_info = array();
		
		if ( strpos( $upload_video, '/vine.co/v/' ) !== false )
		{
			$video_info = $this->vineURL( $upload_video );
			
			if ( !empty( $video_info ) )
			{
				$upload_video = $video_info['upload_video'];
			}
		}
		
		if( !$upload_video )
		{
			throw new Exception( 'err_video_file_format' );
		}
		
		return $this->upload( $upload_video, $video_info );
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
		
		return $this->upload( $_FILES[$input_id]['tmp_name'] );
	}
	/**
	 * Upload file to server
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function upload( $upload_video, $video_info = array() )
	{
		$upload = new Upload_Single_File();
			
		$upload->setConfig(  array(
			'url' => $upload_video
		) );
		
		$file_data = ips_pathinfo( basename( $upload_video ) );	
		
		if ( $file_data['extension'] !== 'mp4' )
		{
			throw new Exception( 'err_file_extension' );
		}
		
		$file_name = time() . '_' . $file_data['filename'];
		
		if( strlen( $file_name ) > 500 )
		{
			$file_name = time() . '_' . md5( $file_data['filename'] );
		}
		
		$upload->set_ext = 'mp4';
		
		$upload->_file = $upload->setImage( $upload->_file );
		
		$file_name = $upload->storeFile( IPS_TMP_FILES . '/' . $file_name );
		
		return $this->getInfo( $file_name, $video_info );

	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getInfo( $file_name, $video = array() )
	{
		if ( !class_exists( 'ffmpeg_movie' ) )
		{
			include_once( LIBS_PATH . '/MP4Info/getID3/getid3.php' );
			$getID3 = new getID3;
			$info   = $getID3->analyze( IPS_TMP_FILES . '/' . $file_name );
			
			$video_info = array(
				'duration' => $info['playtime_string'],
				'has_video' => isset( $info['video']['resolution_x'] ),
				'width' => $info['video']['resolution_x'],
				'height' => $info['video']['resolution_y'] 
			);
			sscanf( $info['playtime_string'], "%d:%d:%d", $hours, $minutes, $seconds );
			
			$video_info['duration'] = isset( $seconds ) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
			/* 
			include_once( LIBS_PATH . '/MP4Info/MP4Info.php' );
			$info = MP4Info::getInfo( IPS_TMP_FILES . '/' . $file_name );
			
			$video_info = array(
			'duration'	=> $info->duration,
			'has_video' => $info->hasVideo,
			'width'		=> $info->video->width, 
			'height'	=> $info->video->height,
			); 
			*/
		}
		else
		{
			$movie      = new ffmpeg_movie( IPS_TMP_FILES . '/' . $file_name );
			$video_info = array(
				'duration' => $movie->getDuration(),
				'has_video' => $movie->hasVideo(),
				'width' => $movie->getFrameWidth(),
				'height' => $movie->getFrameHeight() 
			);
		}
		
		if ( $video_info['duration'] > Config::get( 'upload_mp4_options', 'max_duration' ) )
		{
			throw new Exception( __s( 'err_mp4_duration', round( $video_info['duration'] - Config::get( 'upload_mp4_options', 'max_duration' ) ) ) );
		}
		
		if ( $video_info['has_video'] !== true )
		{
			throw new Exception( 'err_video_file_format' );
		}
		
		
		if( !isset( $video['image'] ) || empty( $video['image'] ) ) 
		{
			if ( Config::get( 'upload_mp4_options', 'extract_image' ) )
			{
				$cmd = 'ffmpeg -ss 0.0 -i ' . IPS_TMP_FILES . '/' . $file_name . ' -t 1 -s 700x' . ( ( 700 * $video_info['height'] ) / $video_info['width'] ) . ' -f image2 ' . IPS_TMP_FILES . '/' . $file_name . '.png';
			
				shell_exec( $cmd );
				
				if( !file_exists( IPS_TMP_FILES . '/' . $file_name . '.png' ) )
				{
					throw new Exception('upload_video_extract_error');
				}
				
				$video['image'] = ABS_URL . 'upload/tmp/' . $file_name . '.png';
				
			}
		}

		if ( Config::get( 'upload_mp4_options', 'download_always' ) == 1 )
		{
			if ( file_exists( IPS_TMP_FILES . '/' . $file_name ) )
			{
				$file_name = Upload::handleVideoFile( $file_name, IPS_CURRENT_DATE );
			}
		}
		
		return array(
			'width' => $video_info['width'],
			'height' => $video_info['height'],
			'upload_video' => $file_name,
			'image' => isset( $video['image'] ) ? $video['image'] : false,
			'cover' => $this->generateImageMp4( $video_info )
		);

		return false;
	}
	

	/**
	 * Generates collored image for MP4
	 *
	 * @param $video_data
	 * 
	 * @return 
	 */
	public function generateImageMp4( $video_data )
	{
		$options = Config::getArray( 'upload_mp4_options' );
		
		$filename = str_random( 10 ) . '.jpg';
		
		$img = imagecreatetruecolor( $video_data['width'], $video_data['height'] );
		
		$color = Gd::hex_convert( $options['default_cover'] );
		
		imagefill( $img, 0, 0, imagecolorallocate( $img, $color['r'], $color['g'], $color['b'] ) );
		
		if ( $options['cover_add'] && file_exists( ABS_PATH . '/images/mp4_cover.png' ) )
		{
			list( $width, $height ) = getimagesize( ABS_PATH . '/images/mp4_cover.png' );
			
			if ( $width < $video_data['width'] && $height < $video_data['height'] )
			{
				imagecopymerge( $img, imagecreatefrompng( ABS_PATH . '/images/mp4_cover.png' ), ( $video_data['width'] - $width ) / 2, ( $video_data['height'] - $height ) / 2, 0, 0, $video_data['width'], $video_data['height'], 100 );
			}
		}
		
		imagejpeg( $img, IPS_TMP_FILES . '/' . $filename, Config::get( 'images_compress', 'jpg' ) );

		return ABS_URL . 'upload/tmp/' . $filename;
	}
	
	/**
	 * The data for downloading movies vine.com
	 *
	 * @param $url - url to the video
	 *	
	 * @return boolean or array -  If you find an image or false
	 */
	public function vineURL( $url )
	{
		preg_match( '@http(?:s)://vine.co/v/([0-9a-zA-Z]{5,12})@', $url, $matches );
		
		if ( isset( $matches[1] ) && !empty( $matches[1] ) )
		{
			$url = 'https://vine.co/v/' . $matches[1];
		}
		
		$video = curlIPS( trim( $url ), array(
			'timeout' => 2 
		) );
		

		preg_match( '@<meta([\s]*)property="og:image"([\s]*)content="([^"]+)">@ius', $video, $image_src );
		preg_match( '@<meta([\s]*)property="twitter:player:stream"([\s]*)content="([^"]+)">@ius', $video, $video_src );
		
		if ( !isset( $image_src[1] ) || !isset( $video_src[1] ) )
		{
			return false;
		}
		
		$image_src = $image_src[1];
		if ( strrpos( $image_src, "?" ) !== false )
		{
			$image_src = substr( $image_src, 0, strrpos( $image_src, "?" ) );
		}
		
		$video_src = $video_src[1];
		if ( strrpos( $video_src, "?" ) !== false )
		{
			$video_src = substr( $video_src, 0, strrpos( $video_src, "?" ) );
		}
		
		if ( empty( $image_src ) || empty( $video_src ) )
		{
			return false;
		}
		
		return array(
			'upload_video' => $video_src,
			'image'		   => $image_src
		);
		
	}
		
	
}
