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

/*
 * Class that performs the update counter access to be provided for social networking
 */
class Social_Count
{
	
	private $urls = array();
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct( $urls, $stream = false )
	{
		
		if ( empty( $urls ) || !is_array( $urls ) )
		{
			return false;
		}
		
		$cache_hash = md5( serialize( $urls ) );
		
		if ( !$this->isCached( $cache_hash ) )
		{
			$this->countShares( $urls, $cache_hash );
		}
	}
	
	/**
	 * Each check is made max every minute
	 *
	 * @param null
	 * 
	 * @return bull
	 */
	public function isCached( $cache_hash )
	{
		if ( Config::get( 'social_cache' ) == 1 )
		{
			$row = Ips_Cache::getDBCache( $cache_hash, 'social', true );
			
			if ( !empty( $row ) && $row['cache_stored'] > date( 'Y-m-d H:i:s', time() - 60 ) )
			{
				return true;
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
	public function countShares( $urls, $cache_hash )
	{
		foreach ( $urls as $key => $url )
		{
			$file_url = ( isset( $url['url'] ) ? $url['url'] : ( isset( $url['id'] ) ? $url['id'] : false ) );
			
			$file_id = link_get_id( urldecode( $file_url ) );
			
			if ( $file_url && $file_id )
			{
				$data = array();
				
				/* Call from JS until 7 Octorer 2015 */
				if ( isset( $url['total_count'] ) )
				{
					$data['share'] = $url['total_count'];
					
					if ( isset( $url['commentsbox_count'] ) )
					{
						PD::getInstance()->update( IPS__FILES, array(
							'comments_facebook' => $url['commentsbox_count'] 
						), array(
							'id' => $file_id
						) );
					}
				}
				/* Call from PHP  */
				elseif ( isset( $url['share'] ) )
				{
					$data['share'] = $url['share']['share_count'];
					
					PD::getInstance()->update( IPS__FILES, array(
						'comments_facebook' => $url['share']['comment_count'] 
					), array(
						'id' => $file_id
					) );
				}
				
				if ( Config::get( 'social_plugins', 'nk_page' ) == 1 || Config::get( 'social_plugins', 'nk' ) == 1 )
				{
					$data['nk'] = $this->countNK( $file_url );
				}
				
				if ( !empty( $data ) )
				{
					PD::getInstance()->update( 'shares', $data, array(
						'upload_id' => $file_id 
					) );
				}
			}
		}
		
		Ips_Cache::storeDBCache( $cache_hash, $cache_hash, 'social' );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function countNK( $url )
	{
		$html = curlIPS( "http://nk.pl/fajne/widget?url=" . urlencode( $url ) . "&type=0&color=0&title=&image=&description=&index=0", array(
			'cookie' => true,
			'timeout' => 3 
		) );
		
		$count = 0;
		
		$count_pos = strpos( $html, '"count":' ) + 8;
		
		if ( !empty( $count_pos ) )
		{
			$count = substr( $html, $count_pos, strpos( $html, ',"', $count_pos ) - $count_pos );
		}
		
		return $count;
	}	
}