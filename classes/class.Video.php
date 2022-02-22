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
use MediaEmbed\MediaEmbed;
class Video
{
	/**
	 * Class constructor
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct()
	{
		if ( !isset( $this->MediaEmbed ) )
		{
			include_once( LIBS_PATH . '/MediaEmbed/MediaEmbed.php' );
			include_once( LIBS_PATH . '/MediaEmbed/Object/ObjectInterface.php' );
			include_once( LIBS_PATH . '/MediaEmbed/Object/MediaObject.php' );
			$this->MediaEmbed = new MediaEmbed();
		}
	}
	/**
	 * Class constructor
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function get( $url, $settings )
	{
		if( $this->isWrzuta( $url ) )
		{
			return $this->getWrzuta( $settings );
		}
		
		if( $media = $this->getJsPlayer( $url, $settings ) )
		{
			return $media;
		}
		
		if ( $media = $this->MediaEmbed->parseUrl( $url ) )
		{
			$media->setAttribute( array(
				'type' => null,
				'frameborder' => '0',
				'allowfullscreen' => 'true',
				'class' => 'async_iframe',
				'width' => $settings['width'],
				'height' => $settings['height'] 
			) );

			if( $this->isYoutube( $url ) )
			{
				$media->setParam( array(
					'rel' => 0,
					'fs' => 1,
					'hd' => 1,
					'autoplay' => has_value( 'autoplay', $settings, 0 ),
					'loop' => has_value( 'loop', $settings, 0 ),
					'showinfo' => 1,
					'modestbranding' => 1
				) );
				
				$media->setAttribute( array(
					'class' => 'youtube-player async_iframe'
				) );
			}
			
			return $media->getEmbedCode();
		}
	}
	
	/**
	 * Check if url id YT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function ajaxGet( $id )
	{
		$res = PD::getInstance()->select( IPS__FILES, array(
			'id' => $id
		), 1 );

		if( isset( $_GET['width'] ) && isset( $_GET['height'] )  )
		{
			$_v = array(
				'width' => $_GET['width'],
				'height' => $_GET['height'],
			);
		}
		else
		{
			$_v = unserialize( $res['upload_data'] );
		}

		$settings = array(
			'width' => $_v['medium']['width'],
			'height' => $_v['medium']['height'],
		);

		if( IPS_ACTION_LAYOUT == 'two' )
		{
			array_walk( $settings, create_function( '&$val', '$val *= 0.5;' ) );
		}

		$settings['embed'] = true;
		$settings['autoplay'] = true;

		return $this->get( $res['upload_video'], $settings );
	}
	
	/**
	 * Check if url id YT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function isYoutube( $url )
	{
		return preg_match( '~http(?:s)?://(?:video\.google\.(?:com|com\.au|co\.uk|de|es|fr|it|nl|pl|ca|cn)/(?:[^"]*?))?(?:(?:www|au|br|ca|es|fr|de|hk|ie|in|il|it|jp|kr|mx|nl|nz|pl|ru|tw|uk)\.)?(?:youtube\.com|youtu\.be)(?:[^"]*?)?(?:&|&amp;|/|\?|;|\%3F|\%2F)(?:video_id=|v(?:/|=|\%3D|\%2F)|)([0-9a-z-_]{11})~imu', $url, $this->youtube_id );
	}
	
	/**
	 * Check if url wrzuta.pl
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function isWrzuta( $url )
	{
		if( strpos( $url, 'wrzuta.pl' ) !== false )
		{
			return preg_match( '/http:\/\/(?:www\.)?([a-zA-Z0-9]{1,18}).wrzuta.pl\/film\/([a-zA-Z0-9]{1,18})?/', $upload_video, $this->wrzuta_id );
		}
		
		return false;
	}
	/**
	 * Set player Wrzuta.pl
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getWrzuta( $settings )
	{
		return '<script type="text/javascript" src="http://www.wrzuta.pl/embed_video.js?key=' . $this->wrzuta_id[2] . '&login=' . $this->wrzuta_id[1] . '&width=' . $settings['width'] . '&height=' . $settings['height'] . '&bg=000000"></script>';
	}
	/**
	 * Set player JS
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getJsPlayer( $url, $settings )
	{
		if ( !IPS_IS_MOBILE && !has_value( 'onpage', $settings, false ) && !has_value( 'embed', $settings, false ) )
		{
			$video_player = Config::getArray( 'video_player', 'type' );
			
			if (  defined( 'IPS_ONSCROLL' ) || $video_player == 'ajax' )
			{
				return '<span data-id="' . $settings['id'] . '" class="video_player"></span>';
			}
		}
		
		return false;
	}
}
