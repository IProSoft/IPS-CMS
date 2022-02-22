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
	include( dirname( __FILE__ ) . '/php-load.php');


	if( isset( $_GET['load'] ) )
	{
		$CacheFilename = 'embed-' . md5( serialize( $_GET ) );
		
		if( $CacheFile = Ips_Cache::get( $CacheFilename ) )
		{
			die( $CacheFile );
		}
		
		$allowed_embed_settings = array(
			'source' => array( 
				'wait',
				'main',
				'category',
				'all'
			),
			'source_order' => array( 
				'date_add',
				'votes_opinion',
				'shares',
				'rand'
			),
		);
		$embed_settings = array(
			'img_size' => 100, /* pixels */
			'source' => 'all', /* wait, main, category, all */
			'category_id' => false,
			'source_order' => 'date_add', /* date_add, votes_opinion, shares, rand */
			'limit' => 10,
			'captions' => false,
		);
		
		foreach( $embed_settings as $setting_name => $value )
		{
			if( isset( $_GET[ $setting_name ] ) )
			{
				if( isset( $allowed_embed_settings[ $setting_name ] ) && !in_array( $_GET[ $setting_name ], $allowed_embed_settings[ $setting_name ])  )
				{
					continue;
				}
				
				$embed_settings[ $setting_name ] = $_GET[ $setting_name ];
			}
		}
		
		if( !is_numeric( $embed_settings['img_size'] ) )
		{
			/** Wrong img_size setting */
			$embed_settings['img_size'] = 100;
		}
		elseif( $embed_settings['img_size'] > Config::getArray( 'add_thumb_size', 'width' ) )
		{
			$embed_settings['img_size'] = Config::getArray( 'add_thumb_size', 'width' );
		}
		
		if( $embed_settings['source'] == 'category'  )
		{
			if( empty( $embed_settings['category_id'] ) || !is_numeric( $embed_settings['category_id'] ) )
			{
				/** Wrong user image size settings */
				$embed_settings['source'] = 'all';
			}
		}
		
		if( !is_numeric( $embed_settings['limit'] )  )
		{
			/** Wrong limit setting */
			$embed_settings['limit'] = 10;
		}
		
		$ips_embed = new Ips_Embed();
		$CacheFile = $ips_embed->embed( $embed_settings );
		
		Ips_Cache::write( $CacheFile, $CacheFilename );
		header('Content-Type: application/json');
		echo 'requestembed(' .json_encode( array( 
			'content' => $CacheFile
		) ) . ')';
	}
	else
	{
		$ips_embed = new Ips_Embed();
		echo $ips_embed->embedCode();
	}
?>