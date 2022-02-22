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
	
class AdSystem{


	private $ads = array();
	
	private static $instance = false;
	
	/**
	* Constructor
	*
	* @param 
	* 
	* @return 
	*/
	public function __construct()
	{
		if( empty( $this->ads ) )
		{
			$this->initAds();
		}
		
		self::$instance = $this;
	}
	/**
	* Initialize ads content
	*
	* @param 
	* 
	* @return 
	*/
	private function initAds()
	{
		if( file_exists( CACHE_PATH . '/cache.ads.php'  ) )
		{
			$this->ads = include( CACHE_PATH . '/cache.ads.php' );
		}
		
		if( !isset( $this->ads ) || empty( $this->ads ) || !is_array( $this->ads )  )
		{
			if( file_exists( CACHE_PATH . '/cache.ads.php' ) )
			{
				unlink( CACHE_PATH . '/cache.ads.php' );
			}
			
			$this->ads = PD::getInstance()->select( 'ads', array(
				'ad_activ' => 1
			) );
			
			foreach( $this->ads as $key => $ad )
			{
				if( file_exists( CACHE_PATH . '/cache.ads_' . $ad['unique_name'] . '.php' ) && strpos( $ad['ad_content'], '[php]') !== false  )
				{
					$this->ads[$ad['unique_name']] = 'include';
				}
				else
				{
					$this->ads[$ad['unique_name']] = $ad['ad_content'];
				}
				unset( $this->ads[$key] );
			}
			
			file_put_contents( CACHE_PATH . '/cache.ads.php' , '<?php return ' . var_export( $this->ads, true ) . ' ?>', LOCK_EX );
		}
	}
	/**
	* Class instance
	*
	* @param 
	* 
	* @return 
	*/
	public static function getInstance()
	{
		if( !self::$instance )
		{
			self::$instance = new AdSystem;
		}
		return self::$instance;
	}
	/**
	* Show multiple ads with one call
	*
	* @param 
	* 
	* @return 
	*/
	public function showAds( $ads_id )
	{
		if( is_array( $ads_id ) )
		{
			foreach( $ads_id as $key => $ad )
			{
				$ads_id[$key] = $this->showAd( $ad );
			}
			
			return implode( '', $ads_id );
		}
		
		return $this->showAd( $ads_id );
	}
	
	/**
	* Show ad
	*
	* @param 
	* 
	* @return 
	*/
	public function showAd( $unique_name, $i = false )
	{
		if( !isset( $this->ads[ $unique_name ] ) || !$this->adsCondition( $unique_name, $i ) )
		{
			return false;
		}

		return $this->generateAd( $unique_name );
	}
	/**
	* Display ads only each X files/comments
	*
	* @param 
	* 
	* @return 
	*/
	public function adsCondition( $unique_name, $i )
	{

		if( strpos( $unique_name, 'between_' ) !== false && ( $i < 1 || ( $i % Config::get( 'ads_frequency', $unique_name ) ) != 0 ) )
		{
			return false;
		}
		
		return true;
	}

	/**
	* Return ad content
	*
	* @param 
	* 
	* @return 
	*/
	public function generateAd( $unique_name )
	{
		$ad_content = htmlspecialchars_decode( stripslashes( $this->ads[ $unique_name ] ), ENT_QUOTES );
			
		if( $unique_name == 'bottom_slide_ad' )
		{
			if( Cookie::exists( 'slide_ad_disable' ) )
			{
				return;
			}
			
			return '
			<div class="bottom_slide_ad">
				<div class="slide_ad_container">
					' . $ad_content . '
					<img width="30" height="200" border="0" title="" src="/images/add-show.png" class="slide_ad_button" />
				</div>
			</div>';
		}
		else
		{
			
			if( $ad_content == 'include' )
			{
				ob_start();
			
					include( CACHE_PATH . '/cache.ads_' . $unique_name . '.php' );
					
				$ad_content = ob_get_contents();
				ob_end_clean();
			}
			
			if( !USER_ADMIN && strpos( $ad_content, '[USER_ADMIN_AD]' ) !== false  )
			{
				$ad_content = '';
			}
			
			return '<div class="ads_' . $unique_name . '">' . $ad_content . '</div>';
		}

	}
}
?>