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

class Facebook_UI
{
	public static $app = false;
	public static $status = false;
	/**
	 * Sprawdzanie czy wybrany materiał może 
	 * zostać zablokowany lub opublikowany na ścienie użytkownika
	 * Checking whether the selected material may be blocked or published on thinning user
	 *
	 * @param null
	 * 
	 * @return bool
	 */
	public static function isBlocked( &$row )
	{
		if ( $row['upload_status'] == 'public'  )
		{
			if ( $row['upload_adult'] && Config::get( 'apps_facebook_app', 'exclude_adult' ) )
			{
				return false;
			}
			
			if ( $row['up_lock'] != 'off' )
			{
				if ( $row['up_lock'] == 'autopost' && Config::get( 'apps_facebook_autopost' ) )
				{
					$object = new Lock_Autopost();
				}
				elseif( $row['up_lock'] == 'social_lock' && Config::get( 'apps_social_lock' ) )
				{
					$object = new Lock_Social_Lock();
				}
				if( isset( $object ) )
				{
					return $object->canBlock( $row  );
				}
			}
		}
		
		return false;
	}
	

	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getApp()
	{
		if( !self::$app )
		{
			require_once( LIBS_PATH . '/facebook-php-sdk-v5/autoload.php' );
			self::$app = new Facebook\Facebook([
				'app_id' => Config::get( 'apps_facebook_app', 'app_id' ),
				'app_secret' => Config::get( 'apps_facebook_app', 'app_secret' ),
				'default_graph_version' => Config::get( 'apps_facebook_app', 'app_version' ),
			]);
		}
		
		return self::$app;
	}
	

	/**
	 * Retrive url shares/likes count
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getUrlStats( $urls )
	{
		$params = implode( ',', $urls );

		$response = self::getApp()->get( '/?ids=' . $params, Config::get('apps_facebook_app', 'app_id') . '|' . Config::get('apps_facebook_app', 'app_secret') )->getDecodedBody();
		
		if( $response && is_array( $response ) )
		{
			return $response;
		}
		
		return array(); 
	}
	
	/**
	 * Retrive url shares/likes count
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function getUrlStatsBatch( $urls )
	{
		$params = array_map( function( $value ){
			return array(
				'method' => 'GET',
				'relative_url' => '/?id=' . $value
			);
		}, $urls );

		$response = self::getApp()->sendBatchRequest( $params, Config::get('apps_facebook_app', 'app_id') . '|' . Config::get('apps_facebook_app', 'app_secret') )->getDecodedBody();
		
		if( $response && is_array( $response ) )
		{
			return $response;
		}
		
		return array();
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function postUserContent( $file_id, $data = array() )
	{
		if ( !$file_id )
		{
			return false;
		}
		
		if ( empty( $data ) )
		{
			$data = getFileInfo( $file_id );
		}
		
		try
		{
			$request = self::getApp()->post( '/' . User_Data::get( USER_ID, 'facebook_uid' ) . '/feed', array(
				'picture' => ips_img( $data, 'large' ),
				'link' => seoLink( $file_id, $data['title'] ),
				'name' => $data['title'],
				'description' => $data['top_line'],
				'privacy' => "{'value': 'EVERYONE'}",
				'message' => ( isset( $data['message'] ) ? $data['message'] : '' ) 
			), User_Data::get( USER_ID, 'access_token_long_live' ) )->getDecodedBody();
			
			return true;
		}
		catch ( Exception $e )
		{
			return false;
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public static function exchangeToken( $token )
	{
		$fb = self::getApp();

		$oAuth2Client = $fb->getOAuth2Client();
		
		try {
			$accessToken = $oAuth2Client->getLongLivedAccessToken($token);
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			return !ips_log( 
				"Error getting long-lived access token: " . $e->getMessage()
			, 'logs/access_token.log' ); 
		}

		return $accessToken->getValue();
	}
	/**
	 * Validate facebook settings
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function isAppValid( $cached = true )
	{
		if( $cached && is_string( Config::get( 'cache_data_valid_facebook_config' ) ) )
		{
			return Config::get('cache_data_valid_facebook_config') == 'true' ? true : false ;
		}
		
		$valid = false;
		
		if ( Config::get('apps_facebook_app', 'app_id' ) != '' && preg_match('/[0-9]{10,}/', Config::get('apps_facebook_app', 'app_id') ) )
		{
			if ( Config::get('apps_facebook_app', 'app_secret') != '' &&  preg_match('/[0-9a-z]{10,37}/i', Config::get('apps_facebook_app', 'app_secret') ) )
			{
				$valid = true;
			}
		}
		
		return Config::update( 'cache_data_valid_facebook_config', ( $valid ? 'true' : 'false' ) ) && $valid;
	}
	/**
	 * Validate facebook user ID
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public static function validUserId( $id )
	{
		return ( strlen( $id ) > 5 && is_numeric( $id )  );
	}
    
	/**
     * Returns the default AccessToken entity.
     *
     * @return AccessToken|null
     */
	public static function getDefaultAccessToken()
	{
		return Config::get('apps_facebook_app', 'app_id') . '|' . Config::get('apps_facebook_app', 'app_secret');
	}
}
?>