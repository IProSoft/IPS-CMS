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
class Mp4
{
	/* Autoplay only first file */
	public $autoplay = false;
	/**
	 * Class constructor
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct( $on_widget )
	{
		$this->autoplay = (int)( !$on_widget && Config::get( 'mp4_autoplay' ) );
		$this->player	= Config::get( 'mp4_player' );
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
		$settings['video_autoplay'] = (int)$this->autoplay;
		
		if( $this->autoplay )
		{
			$this->autoplay = false;
		}

		$is_url = strpos( $url, 'http' ) !== false;
		
		$settings['video_config'] = array(
			'video_subtitles' => false,
			'video_watermark' => false,
			'width' => $settings['width'],
			'height' => $settings['height'],
			'video_webm' => false,
			'video_src' => $is_url ? $url : IPS_VIDEO_URL . '/' . $url,
			'media_poster' => ips_img( $settings['upload_image'], 'media_poster' ) 
		);
		
		$settings['vid_config']   = $settings['video_config'];
		$settings['video_config'] = json_encode( $settings['video_config'] );
		$settings['player_type']  = $this->player;
		
		return Templates::getInc()->getTpl( 'item_video_mp4.html', $settings );
	}
	
	
}
