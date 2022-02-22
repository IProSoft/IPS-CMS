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

class Facebook_Fanpage
{
	/**
	 *
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public static function postFromId( $id, $force_type = 'post', $data = array() )
	{
		$row = PD::getInstance()->select( IPS__FILES, array(
			'id' => $id 
		), 1 );
		
		if ( empty( $row ) )
		{
			return !ips_log( "Facebook_UI, postFromId, $id" );
		};
		
		$config = Config::getArray( 'apps_fanpage_auto' );
		
	
		$row = array_merge( $row, [
			'type' => 'post',
			'link' => seoLink( $row['id'], $row['title'] ),
			'title' => !empty( $config['title'] ) && $config['title_info'] == 'user' ? $config['title'] : $row['title'],
			'caption' => ( !empty( $config['caption'] ) && $config['caption_info'] == 'user' ? $config['caption'] : ( $config['caption_info'] == 'off' ? false : __('meta_site_title') ) ),
			'description' => ( !empty( $config['description'] ) && $config['description_info'] == 'user' ? $config['description'] : ( $config['description_info'] == 'off' ? false : __('meta_site_description') ) ),
			'image' => ( !empty( $config['image'] ) && $config['image_info'] == 'user' ? $config['image'] : ( $config['image_info'] == 'off' ? false : ips_img( $row, 'large' ) ) ),
			'message' => ( !empty( $config['message'] ) ? $config['message'] : null ),
		] );
		

		if ( $config['image_info'] == 'off' )
		{
			$row['title'] = $row['caption'] = $row['description'] = $row['link'] = false;
		}
		
		return self::post( $id, array_merge( $row, $data ), $force_type );
		
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public static function post( $id, $row = false, $force_type = 'post' )
	{
		if ( empty( $id ) && empty( $row ) )
		{
			return !ips_log( "Facebook_UI, post, $id" );
		}
		
		/* First extract image from data */
		if( !isset( $row['upload_image_fanpage'] ) )
		{
			/** Upload image to gallery **/
			if ( $row['type'] == 'upload' || $force_type == 'upload' )
			{
				if ( isset( $row['upload_image'] ) )
				{
					/** Auto upload **/
					$row['upload_image_fanpage'] = ips_img_path( $row, 'large' );
				}
				else
				{
					/** Uploads from PA **/
					if ( !empty( $_FILES["file"]["tmp_name"] ) )
					{
						$row['upload_image_fanpage'] = realpath( $_FILES["file"]["tmp_name"] );
					}
					else
					{
						$img = str_replace( ABS_URL, '', $row['image'] );
						
						if ( file_exists( ABS_PATH . '/' . $img ) )
						{
							$row['upload_image_fanpage'] = ABS_PATH . '/' . $img;
						}
						elseif( Sanitize::validatePHP( $row['image'], 'url') )
						{
							$row['upload_image_fanpage'] = $row['image'];
						}
					}
				}
				
				if ( !isset( $row['upload_image_fanpage'] ) )
				{
					return __admin( 'fanpage_error_image' );
				}
			}
			else
			{
				if ( empty( $row['image'] ) && empty( $row['message'] ) )
				{
					return __admin( 'fanpage_error_image_message' );
				}
				
				$row['upload_image_fanpage'] = $row['image'];
			}
		}
		
		$fanpage_id = Config::get( 'apps_fanpage_default_id' );
		
		if( isset( $row['fanpage_id'] ) )
		{
			$fanpage_id = $row['fanpage_id'];
			
			if( is_array( $row['fanpage_id'] )  )
			{
				$messages = array();
				
				foreach( $row['fanpage_id'] as $fanpage_id )
				{
					$status = self::post( $id, array_merge( $row, array( 
						'fanpage_id' => $fanpage_id,
						'album_id' => ( isset( $row['album_id'][$fanpage_id] ) ? $row['album_id'][$fanpage_id] : false )
					) ), $force_type );
					
					if( $status )
					{
						if( is_string( $status ) && !is_numeric( $status ) )
						{
							$messages[ $fanpage_id  ] = $status;
						}
					}
				}
				
				return !empty( $messages ) ? 'Dodano wpis na ' . ( count( $row['fanpage_id'] ) - count( $messages ) ) . ' z ' . count( $row['fanpage_id'] ) . ' Fanpage' . implode( '<br />', $messages ) : true ;
			}
		}
		
		$fanpage_adress = ' : ' . self::filter( 'url', 'fanpage_id', $fanpage_id );
		
		try
		{
			$token = self::getToken( $fanpage_id );
			
			if ( !$token )
			{
				return __admin( 'fanpage_error_api_facebook' ) . $fanpage_adress;
			}
		}
		catch ( Exception $ex )
		{
			return !ips_log( $ex, 'logs/fanpage.log' ); 
		}
		
		/** Upload image to gallery **/
		if ( $row['type'] == 'upload' || $force_type == 'upload' )
		{
			try
			{
				/**
				 * Album photo upload
				 */
				$photo = Sanitize::validatePHP( $row['upload_image_fanpage'], 'url') ? array(
					'url' => $row['upload_image_fanpage']
				) : array(
					'source' => Facebook_UI::getApp()->fileToUpload( $row['upload_image_fanpage'] )
				);
				
				$response = Facebook_UI::getApp()->post( '/' . $row['album_id'] . '/photos', array_merge( [
					'message' => $row['message'] 
				], $photo ), $token )->getDecodedBody();
			

				if ( $response )
				{
					return PD::getInstance()->insert( 'fanpage_posts', array(
						'album_id' => $row['album_id'],
						'fanpage_id' => $fanpage_id,
						'post_id' => $response['id'],
						'post_title' => $row['title'],
						'post_data' => date( "Y-m-d H:i:s" ),
						'post_url' => 'https://www.facebook.com/photo.php?fbid=' . $response['id'],
						'post_type' => 'upload' 
					) );
				}
				
				return !ips_log( $response );
				
			}
			catch ( Exception $ex )
			{
				return !ips_log( $ex, 'logs/fanpage.log' ); 
			}
		}
		else
		{
			/**
			 * Uploading as post to fanpage wall
			 */
			
			try
			{
				$response = Facebook_UI::getApp()->post( '/' . $fanpage_id . '/feed', array(
					'uid' => $fanpage_id,
					'message' => $row['message'],
					'link' => $row['link'],
					'name' => $row['title'],
					'caption' => $row['caption'],
					'description' => $row['description'],
					'picture' => $row['upload_image_fanpage'] 
				), $token )->getDecodedBody();
				
				if ( $response )
				{
					return PD::getInstance()->insert( 'fanpage_posts', array(
						'upload_id' => ( isset( $row['id'] ) ? $row['id'] : $id ),
						'fanpage_id' => $fanpage_id,
						'post_id' => $response['id'],
						'post_title' => $row['title'],
						'post_data' => date( "Y-m-d H:i:s" ),
						'post_url' => 'https://www.facebook.com/' . str_replace( '_', '/posts/', $response['id'] ),
						'post_type' => 'post' 
					) );
				}
				
				return !ips_log( $response );
			}
			catch ( FacebookClientException $ex )
			{
				return !ips_log( $ex, 'logs/fanpage.log' ); 
			}
			catch ( FacebookClientException $ex )
			{
				return !ips_log( $ex, 'logs/fanpage.log' ); 
			}
			catch ( Exception $ex )
			{
				return !ips_log( $ex, 'logs/fanpage.log' ); 
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
	public static function assignAccesTokens( $token )
	{
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		if( !is_array( $fanpages ) )
		{
			return false;
		}
		
		$response = Facebook_UI::getApp()->get('/me?fields=accounts', $token )->getDecodedBody();
		
		$count = 0;
		
		if( isset( $response['accounts']['data'] ) )
		{
			foreach ( $response['accounts']['data'] as $account )
			{
				foreach ( $fanpages as $key => $fanpage )
				{
					if ( $account['id'] == $fanpage['fanpage_id'] || ( isset( $account['name'] ) && $account['name'] == $fanpage['fanpage_id'] ) )
					{
						$fanpages[$key]['api_token'] = $account['access_token'];
						
						/** Save Acces token for current activ Fanpage */
						if( Config::get( 'apps_fanpage_default_id' ) == $fanpage['fanpage_id'] )
						{
							Config::update( 'apps_fanpage_default_token', $account['access_token'] );
						}
						$count++;
					}
				}
			}
		}
		
		Config::update( 'apps_fanpage_array', $fanpages );
		
		return $count;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getToken( $fanpage_id )
	{
		
		if ( empty( $fanpage_id ) )
		{
			throw new Exception( 'Error: getToken, empty $fanpage_id' );
		}
		
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		if( is_array( $fanpages ) )
		{
			$fanpage = ips_search_array( 'fanpage_id', $fanpage_id, $fanpages );
			
			if( isset( $fanpage['api_token'] ) )
			{
				return $fanpage['api_token'];
			}
		}
		
		$response = Facebook_UI::getApp()->get('/me?fields=accounts', $token )->getDecodedBody();

		if( isset( $response['accounts']['data'] ) )
		{
			foreach ( $response['accounts']['data'] as $account )
			{
				if ( $account['id'] == $fanpage_id || ( isset( $account['name'] ) && $account['name'] == $fanpage_id ) )
				{
					$fanpage_token = $account['access_token'];
				}
			}
		}
		
		return isset( $fanpage_token ) ? $fanpage_token : false;
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function getAlbums( $fanpage_id )
	{
		try
		{
			$token = Facebook_Fanpage::getToken( $fanpage_id );
			
			$response = Facebook_UI::getApp()->get('/' . $fanpage_id . '?fields=albums', $token )->getDecodedBody();

			if( isset( $response['albums']['data'] ) )
			{
				return $response['albums']['data'];
			}
		}
		catch ( Exception $e )
		{}
		
		return array( 
			'error' =>  __( 'configure_facebook_api' ) . '  - <a target="_blank" href="' . admin_url( 'fanpage', 'action=settings' ) . '" class="button" role="button">' . __( 'configuration' ) . '</a>'
		);
	}
	
	/**
	 * Pulling the cable ID saved Fanpage
	 *
	 * @param null
	 * 
	 * @return bool|numeric
	 */
	public static function getFanpageID( $fanpage_url )
	{
		preg_match( '@([0-9]{10,})@iu', $fanpage_url, $matched );
		
		list( $fanpage_id ) = $matched;
		
		if ( empty( $fanpage_id ) || !is_numeric( $fanpage_id ) )
		{
			$explode    = explode( '/', trim( parse_url( $fanpage_url, PHP_URL_PATH ), '/' ) );
			$fanpage_id = end( $explode );
		
			$json = curlIPS( 'https://graph.facebook.com/' . $fanpage_id . '?access_token=' .( Config::get('apps_facebook_app', 'app_id') . '|' . Config::get('apps_facebook_app', 'app_secret') ), array(
				'timeout' => 10 
			) );
			$json       = json_decode( $json, true );
			$fanpage_id = isset( $json['id'] ) ? $json['id'] : false;
		}
		
		if ( is_numeric( $fanpage_id ) )
		{
			return $fanpage_id;
		}
		
		return false;
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function addFanpage( $url )
	{
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		if( !is_array( $fanpages ) )
		{
			$fanpages = array();
		}
		
		$fanpage_url = strtok( $url, '?' );
		$md5_key = md5( $fanpage_url );
		
		if( !isset( $fanpages[ $md5_key ] ) && preg_match( '~^https?://(?:www\.)?facebook.com/(.+)?$~i', $fanpage_url ) )
		{
			$fanpages[ $md5_key ] = array( 
				'url' => $fanpage_url,
				'fanpage_id' => self::getFanpageID( $fanpage_url )
			);
			
			Config::update( 'apps_fanpage_array', $fanpages );
			
			if( count( $fanpages ) == 1 )
			{
				self::setDefaultFanpage( $md5_key );
			}
			
			return array( 
				'info' => 'apps_fanpage_saved'
			);
		}
		else
		{
			return array( 
				'alert' => 'apps_fanpage_url_error'
			);
		}
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function deleteFanpage( $md5_key )
	{
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		if( isset( $fanpages[$md5_key] ) )
		{
			unset( $fanpages[ $md5_key ] );
			
			PD::getInstance()->update('system_settings', array( 
				'settings_value' => serialize( $fanpages )
			), "`settings_name` = 'apps_fanpage_array'");
			
			if( count( $fanpages ) == 1 )
			{
				self::setDefaultFanpage( key( $fanpages ) );
			}
			elseif( count( $fanpages ) == 0 )
			{
				Config::update( 'apps_fanpage_default', '' );
				Config::update( 'apps_fanpage_default_id', '' );
			}
			
			return array( 
				'info' => 'apps_fanpage_saved'
			);
		}
		else
		{
			return array( 
				'alert' => 'apps_fanpage_url_error'
			);
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function setDefaultFanpage( $md5_key )
	{
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		Config::update( 'apps_fanpage_default', $fanpages[ $md5_key ]['url'] );
		Config::update( 'apps_fanpage_default_id', $fanpages[ $md5_key ]['fanpage_id'] );	
		Config::update( 'apps_fanpage_default_token', ( isset( $fanpages[ $md5_key ]['api_token'] ) ? $fanpages[ $md5_key ]['api_token'] : '' ) );

		return array( 
			'info' => 'apps_fanpage_saved'
		);
	}
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
/* 	public static function getToken( $fanpage_id )
	{
		$token = self::filter( 'api_token', 'fanpage_id', $fanpage_id );

		if( $token )
		{
			return $token;
		}
		
		return Config::get( 'apps_fanpage_default_token' );
	} */
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function filter( $column_key, $index_key, $key = false )
	{
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		if( is_array( $fanpages ) )
		{
			$fanpages = array_column( $fanpages,  $column_key, $index_key );
			
			if( $key )
			{
				return isset( $fanpages[ $key ] ) ? $fanpages[ $key ] : false;
			}
			
			return $fanpages;
		}
		
		return false;
	}
}
?>