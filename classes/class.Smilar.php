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

class Smilar
{
	/**
	 * Find smilar files for upload demot form
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function smilarUpload( $search_by )
	{
		$rows = PD::getInstance()->select( IPS__FILES, array(
			'title' => array( $search_by, 'LIKE' )
		), 0, "id, title, upload_image");

		if( !empty( $rows ) )
		{
	
			foreach( $rows as $id => $row )
			{
				$rows[$id] = array(
					'url' => seoLink( $row['id'], false, $row['seo_link'] ),
					'thumb_img' => ips_img( $row, 'thumb' ),
					'img' => ips_img( $row, 'medium' ),
					'title' => $row['title']
				);		
			}
			
			return array( 
				'content' => Templates::getInc()->getTpl( '/upload/up_smilar_list.html', array(
					'files' => $rows
				) )
			);
		}
		
		return false;
	}
	
	/**
	 * Find smilar files for upload demot form
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function smilarSearchWidget( $search_by )
	{
		$rows = PD::getInstance()->select( IPS__FILES, array(
			'title' => array( $search_by, 'LIKE' ),
			'top_line' => array( $search_by, 'LIKE', 'OR' ),
			'bottom_line' => array( $search_by, 'LIKE', 'OR' )
		), 10, "id, title, upload_image");

		if( !empty( $rows ) )
		{
	
			foreach( $rows as $id => $row )
			{
				$rows[$id] = array(
					'url' => seoLink( $row['id'], false, $row['seo_link'] ),
					'thumb_img' => ips_img( $row, 'thumb' ),
					'title' => $row['title'],
					'short_title' => ( isset( $row['title'][35] ) ? substr( $row['title'], 0, 35 ) . '...' : $row['title'] )
				);		
			}
		
			return array( 
				'content' => Templates::getInc()->getTpl( 'widget_search_hints.html', array(
					'files' => $rows
				) )
			);
		}
		
		return false;
	}
}
