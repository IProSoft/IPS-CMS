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


class Upload_Ajax extends Upload_Single_File
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function url( $url, $options = array() )
	{
		return $this->upload( array(
			'url' => $url
		), $options );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function post( $input_name, $options = array() )
	{
		return $this->upload( array(
			'post' => $input_name
		), $options );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function files( $input_name, $options = array() )
	{
		return $this->upload( array(
			'files' => $input_name
		), $options );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function isMultiple( $input_name )
	{
		return isset( $_FILES[$input_name]['tmp_name'] ) && is_array( $_FILES[$input_name]['tmp_name'] );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function multiple( $input_name, $options = array() )
	{
		$success = [];
		foreach( $_FILES[$input_name]['tmp_name'] as $key => $f )
		{
			$name = str_random(5);
			$_FILES[ $name ] = array(
				'name' => $_FILES[$input_name]['name'][$key],
				'type' => $_FILES[$input_name]['type'][$key],
				'tmp_name' => $_FILES[$input_name]['tmp_name'][$key],
				'error' => $_FILES[$input_name]['error'][$key],
				'size' => $_FILES[$input_name]['size'][$key],
			);

			$success[] = $this->upload( array(
				'files' => $name
			), $options );
		}
		
		return [
			'content' => $success
		];
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function upload( $config, $options )
	{
		try {

			$file_name = isset( $options['file_name'] ) ? $options['file_name'] : str_random( 10 ) ;
			
			$upload = new Upload_Single_File();
			
			$file = $this->setFileName( $file_name )
					->setConfig( $config )
					->Load( ABS_PATH . '/' . $options['up_folder'] . '/' . $file_name, $options );
			
			return array_merge( $file, array(
				'content'	=> $options['up_folder'] . '/' . $file_name . '.' . $file['extension'],
				'ext'		=> $file['extension']
			) );
		
		} catch (Exception $e) {
			return array(
				'error' => __( $e->getMessage() )
			);
		}
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function call( $class )
	{
		if( isset( $_POST['func'] ) && in_array( $_POST['func'], array( 'getBg', 'storeText', 'storeLayers' ) ) )
		{
			if( $content = Ips_Autoloader::call( $class, $_POST['func'], get_input( 'args' ) ) )
			{
				return array(
					'content' => $content
				);
			}
		}
		return array();
	}
}
