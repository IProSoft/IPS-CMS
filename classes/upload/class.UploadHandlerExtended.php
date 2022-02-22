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

require_once( LIBS_PATH . '/UploadHandler.php' );

class Upload_Handler_Extended extends UploadHandler
{
	
	/**
	 * 
	 *
	 * @param $search_inputs - search inputs in $_FILES array
	 * 
	 * @return 
	 */
	public function __construct( $options_type = 'tmp_file', $search_inputs = false )
	{
		$this->error_messages = array(
			'max_file_size' => __('err_file_size'),
			'accept_file_types' => __('err_video_file_format'),
			'max_width' => __('upload_image_max_width'),
			'min_width' => __('upload_image_min_width'),
			'max_height' => __('upload_image_max_height'),
			'min_height' => __('upload_image_min_height')
		);
		
		if ( $options_type == 'tmp_file' )
		{
			parent::__construct( $this->temporaryFileOptions( $search_inputs ) );
		}
		elseif ( $options_type == 'video_file' )
		{
			parent::__construct( array_merge( $this->temporaryFileOptions(), array(
				'accept_file_types_contents' => array(
					'video/mp4'
				)
			)));
		}
		elseif ( $options_type == 'upload' )
		{
			parent::__construct( $this->uploadFileOptions(), false );
		}
		else
		{
			parent::__construct( array(), false );
		}
		
		return $this;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function upload_all_files( $uploaded_file, $name )
	{
		$file       = new stdClass();
		$file->name = $this->get_file_name( null, null, null, null );
		$file->size = $this->fix_integer_overflow( intval( null ) );
		$file->type = null;
		$file->name = $name;
		$file_path  = $this->get_upload_path($file->name);
		
		$this->handle_image_file( $file_path, $file );
		
		return $file;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	private function temporaryFileOptions( $search_inputs )
	{
		$file_inputs = array();
		
		if ($search_inputs)
		{
			foreach ($_FILES as $name => $file)
			{
				if (strpos($name, 'multi_files') !== false)
				{
					$file_inputs = array(
						'param_name' => $name
					);
					break;
				}
			}
		}
		
		return array_merge( array(
			'upload_dir' => IPS_TMP_FILES . '/',
			'upload_url' => '/upload/tmp/',
			
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			// Set to false to disable rotating images based on EXIF meta data:
			'orient_image' => true,
			'image_versions' => array(
				'medium' => array(
					'max_width' => 2600,
					'max_height' => 21200,
					'jpeg_quality' => 100,
					'upload_dir' => IPS_TMP_FILES . '/',
					'upload_url' => '/upload/tmp/'
				)
			)
		), $file_inputs );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	private function uploadFileOptions()
	{
		require_once( ABS_PATH . '/functions.php' );
		$image_folder = getFolderByDate(null, date("Y-m-d H:i:s"));
		
		return array(
			'upload_dir' => ips_img_path($image_folder . '/', 'large'),
			'upload_url' => ips_img($image_folder . '/', 'large'),
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			// Set to false to disable rotating images based on EXIF meta data:
			'orient_image' => true,
			'image_versions' => array(
				'medium' => array(
					'max_width' => 600,
					'max_height' => 1200,
					'jpeg_quality' => 100,
					'upload_dir' => ips_img_path($image_folder . '/', 'medium'),
					'upload_url' => ips_img($image_folder . '/', 'medium')
				),
				'square' => array(
					'crop' => true,
					'max_width' => 70,
					'max_height' => 70,
					'jpeg_quality' => 100,
					'upload_dir' => ips_img_path($image_folder . '/', 'square'),
					'upload_url' => ips_img($image_folder . '/', 'square')
				),
				'thumb' => array(
					'max_width' => 236,
					'max_height' => 400,
					'jpeg_quality' => 100,
					'upload_dir' => ips_img_path($image_folder . '/', 'thumb'),
					'upload_url' => ips_img($image_folder . '/', 'thumb')
				),
				'thumb-small' => array(
					'max_width' => 70,
					'max_height' => 200,
					'jpeg_quality' => 100,
					'upload_dir' => ips_img_path($image_folder . '/', 'thumb-small'),
					'upload_url' => ips_img($image_folder . '/', 'thumb-small')
				),
				'thumb-mini' => array(
					'max_width' => 50,
					'max_height' => 50,
					'jpeg_quality' => 100,
					'upload_dir' => ips_img_path($image_folder . '/', 'thumb-mini'),
					'upload_url' => ips_img($image_folder . '/', 'thumb-mini')
				)
			)
		);
	}
}