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

class Smilar_Controller
{
	public function route()
	{
		if ( Config::get( 'widget_smilar_url' ) == 0 && is_numeric( $_GET['tag_id'] ) )
		{
			ips_redirect( 'index.html' );
		}
		
		if ( empty( $_GET['tag_id'] ) )
		{
			ips_redirect( 'index.html' );
		}
		
		$display = new Core_Query();
		
		/**
		 * Finding files with smilar tags by file ID
		 */
		if ( is_numeric( $_GET['tag_id'] ) )
		{
			$upload_id = $_GET['tag_id'];
			
			$upload_tags = Tags::getSmilar( $upload_id, 10, true );
			
			if ( !empty( $upload_tags ) )
			{
				$args['counted_data'] = PD::getInstance()->select( 'upload_tags_post', array(
					'id_tag' => array( $upload_tags, 'IN')
				), 1, 'COUNT(DISTINCT upload_id) as files' );
				
				$args['counted_data'] = $args['counted_data']['files'];
				
				$upload_ids = PD::getInstance()->select( 'upload_tags_post', array(
					'id_tag' => array( $upload_tags, 'IN')
				), $display->appGetLimit( 'limit' ), 'DISTINCT upload_id' );
				
				if ( !empty( $upload_ids ) )
				{
					$upload_ids = array_unique( array_column( $upload_ids, 'upload_id' ) );
				}
			}
			
			if ( !isset( $upload_ids ) || empty( $upload_ids ) )
			{
				$args['counted_data'] = 0;
				$upload_ids                = $upload_id;
			}
		}
		/**
		 * Finding files by tag
		 */
		else
		{
			$upload_ids = Tags::tagFiles( $_GET['tag_id'], $display->appGetLimit( 'limit' ) );
			
			$args['counted_data'] = Tags::tagFilesCount( $_GET['tag_id'] );
		}
		
		/** Empty tag id's **/
		if( empty( $upload_ids ) )
		{
			return ips_redirect( false, 'common_not_found' );
		}
		
		$args = array_merge( $args, array(
			'condition' => array(
				'id' => array( $upload_ids, 'IN' )
			),
			'/tag/' . $_GET['tag_id'] . '/',
			'limit' => Config::get( 'files_on_page' ) 
		) );
		
		
		return $display->init( 'smilar', $args );
		

		
		/* 

		var_dump( $limit );
		var_dump( PD::getInstance() );
		exit;
		
		
		$upload_ids = Tags::getSmilar( $_GET['tag_id'] );
		var_dump( PD::getInstance() );
		exit;
		$limit = $this->appGetLimit( 'limit' );
		
		$args['counted_data'] = PD::getInstance()->cnt( 'upload_tags_post', array(
			'id_tag' => array( $upload_ids, 'IN')
		) );
		
		$upload_ids = PD::getInstance()->select( 'upload_tags_post', array(
			'id_tag' => array( $upload_ids, 'IN')
		), $limit, 'DISTINCT upload_id' );
		
		$upload_ids = !empty( $upload_ids ) ? implode( ',', array_column( $upload_ids, 'upload_id' ) ) : '0';
		
		$args = array_merge( $args, array(
			'{upload_ids}' => $upload_ids,
			'{pagination}' => $_GET['tag_id'] 
		) );
		
		
		return;
		$upload_ids = Tags::getSmilar( $_GET['tag_id'], 10 );
		
		if ( empty( $upload_ids ) )
		{
			$upload_ids = '0';
		}
		$args = array_merge( $args, array(
			'{upload_ids}' => $upload_ids,
			'{pagination}' => $_GET['tag_id'] 
		) );
		 */
		//$args = array( '{upload_ids}' => "SELECT DISTINCT upload_id FROM upload_tags_post WHERE id_tag IN( SELECT `id_tag` FROM `upload_tags_post` WHERE `upload_id`='".$_GET['tag_id']."' )", '{pagination}' => $_GET['tag_id'] );
		//return;
	}
}
