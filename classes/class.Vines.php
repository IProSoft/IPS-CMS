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

class Vines
{
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function init()
	{
		
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function redirectMain()
	{
		if ( ( empty( $_GET ) && IPS_ACTION == 'main' ) && empty( $_POST ) && Config::get( 'vines_main_as_file' ) )
		{
			
			$data = PD::getInstance()->select( 'upload_post', array(
				'upload_status' => 'public',
				'upload_activ' => 1 
			), 1, 'id,seo_link', array(
				'date_add' => 'DESC' 
			) );
			
			if ( !empty( $data ) )
			{
				ips_redirect( $data['id'] . '/' . $data['seo_link'] );
				
				$_GET = array(
					'route' => 'file_page',
					'id' => $data['id'],
					'name' => $data['seo_link'] 
				);
				
				$_SERVER['REQUEST_URI'] = '/' . $data['id'] . '/' . $data['seo_link'];
			}
		}
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function sideBlock()
	{
		return Templates::getInc()->getTpl( 'side_block.html' );
	}
	
}
?>