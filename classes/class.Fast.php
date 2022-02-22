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

class Fast
{
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		App::minimalLayout();
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function load( $get_from, $current_file, $load_direction = 'next' )
	{
		$condition = array(
			'upload_activ' => ( $get_from == 'main' ? 1 : 0 )
		);
		
		if( $current_file )
		{
			$condition['id'] = array( 
				$current_file, 
				( $load_direction == 'prev' ? '>' : '<' )
			);
		}
		
		$display = new Core_Query();
		$display->getData( IPS__FILES, $condition, 'date_add', 10 );
		
		if( !empty( $display->files ) )
		{
			foreach( $display->files as $key => $row )
			{
				$link = seoLink( $row['id'], false, $row['seo_link'] );
				
				$row['buttons'] = array();
				
				if ( Config::getArray( 'page_fast_options', 'share' ) )
				{
					$row['buttons'][] = SocialButtons::share( $link );
				}
				
				if( Config::getArray( 'page_fast_options', 'like' ) )
				{
					$row['buttons'][] = SocialButtons::like( $link );
				}
				
				if( Config::getArray( 'page_fast_options', 'google' ) )
				{
					$row['buttons'][] = SocialButtons::googleButton( $link );
				}
				
				$row['buttons'] = implode( "\n", $row['buttons'] );

				if( isset( $row['title'][50] ) )
				{
					$row['title'] = cutWords( $row['title'], 50 ) . '...';
				}
			
				$info = $display->getFileInfo( $row['id'], $row['category_id'], $row['upload_adult'] );
				
				if( $info['upload_adult'] )
				{	
					$row['dimensions'] = $display->getFileDimension( $row );
					
					if( $row['dimensions']['height'] > 500 )
					{
						$row['dimensions']['height'] = 500;
					}	
				}
				else
				{
					$row['file'] = $display->loadFile( $row, false, false );
				}
				
				return array(
					'content' => Templates::getInc()->getTpl( 'fast_files.html', $row )
				);
			}
		}
		return false;
	}
}