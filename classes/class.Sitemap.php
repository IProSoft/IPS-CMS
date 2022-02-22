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

/* With each call, you can set how often the page is modified
 * Optional: "always","hourly", "daily", "weekly", "monthly", "yearly", "never"; 
 */


class Sitemap
{
    
    public $ERR;
    /**
     * XML character encoding
     */
    public $xml_coding = 'UTF-8';
    
    /**
     * Maximum number of links in the file stiemap.xml
     */
    public $max_urls = 50000;
    
    /**
     * Array() with a list of links to XML
     */
    public $urls = array();
    /**
     * The format for the date of the last modification maps
     */
    public $lastModDateTime = 'Y-m-d\TH:i:s';
    
    
    public $lastModDate = 'Y-m-d';
    
    public $changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');
    public $priority_min = 0.0;
    public $priority_max = 1.0;
    public $priority_step = 0.1;
    public $priority_format = '%01.1f';
    public $compresion = 'gzencode';
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function __construct()
    {
        
    }

  
    /**
     * The tasks performed by adding additional pages to the list and save the file with a map in * .xml
     */
    public function createSitemap()
    {
        
        $this->map_files();
        $this->map_user_profiles();
        $this->map_main();
        $this->map_wait();
        $this->map_archive();
        $this->map_categories();
        $this->map_social();
        
		$this->save();
		
        
    }
    /**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function save( $tmp = false )
    {
        if( $tmp )
		{
			$this->urls = unserialize( File::read( IPS_TMP_FILES . '/sitemap.tmp' ) );
			unlink( IPS_TMP_FILES . '/sitemap.tmp' );
		}
		
		if( File::put( ABS_PATH . '/sitemap.xml', $this->generate() ) )
		{
			$this->submitGoogle();
			
			return true;
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
	public function emptyTmp()
    {
		File::create( IPS_TMP_FILES . '/sitemap.tmp', serialize( array() ) );
	}
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function saveTmp()
    {
        $data = unserialize( File::read( IPS_TMP_FILES . '/sitemap.tmp' ) );
		
		File::put( IPS_TMP_FILES . '/sitemap.tmp', serialize( array_merge( $data, $this->urls ) ) );
    }
	
    /* Rozbicie na funkcje dla
     * wygodniejszego ustawiania
     * wartości w mapie 
     * The breakdown convenient functions for setting the value in the map * /
     */
    
    public function map_files( $tmp = false )
    {
        
        $res = PD::getInstance()->select( IPS__FILES );
        
        foreach ( $res as $sql )
		{
            $this->addUrl( seoLink( $sql['id'], ( isset($sql['pin_title']) ? $sql['pin_title'] : $sql['title'] ) ), "monthly", "0.5" );
            $this->addUrl( ABS_URL . 'smilar/' . $sql['id'], "monthly", "0.5" );
        }
        
		if( $tmp )
		{
			$this->saveTmp();
		}
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function map_categories( $tmp = false )
    {
        
        $res = Categories::getCategories();
        
        foreach ($res as $sql) {
            
			$this->addUrl( ABS_URL . 'category/' . $sql['id_category'] . ',' . $sql['category_link'], "daily", "1.0", time() );
            
            $res = PD::getInstance()->cnt(IPS__FILES, array( 
				'category_id' => $sql['id_category']
			));
			
            $res = ceil( $res / Config::get('files_on_page') );
            
            for ( $i = 1; $i <= $res; $i++ )
			{
                $this->addUrl( ABS_URL . 'category/' . $sql['id_category'] . ',' . $sql['category_link'] . ',' . $i, "daily", "1.0", time() );
            }
            
        }
       
		if( $tmp )
		{
			$this->saveTmp();
		}
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function map_user_profiles( $tmp = false )
    {
        $res = PD::getInstance()->select( 'users', NULL, NULL, 'login' );
        
		foreach ( $res as $user )
		{
            $this->addUrl( ABS_URL . 'profile/' . $user['login'], 'daily', '0.9' );
            
            $this->addUrl( ABS_URL . 'user/' . $user['login'] . '/all', 'daily', '0.9' );
            $this->addUrl( ABS_URL . 'user/' . $user['login'] . '/main', 'daily', '0.9' );
            $this->addUrl( ABS_URL . 'user/' . $user['login'] . '/waiting', 'daily', '0.9' );
            $this->addUrl( ABS_URL . 'user/' . $user['login'] . '/comments', 'daily', '0.9' );
            $this->addUrl( ABS_URL . 'user/' . $user['login'] . '/opinion', 'daily', '0.9' ); 
        }
		
		if( $tmp )
		{
			$this->saveTmp();
		}
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function map_social( $tmp = false )
    {
        
        $res = PD::getInstance()->cnt(IPS__FILES, array(
			'upload_status' => 'public'
		));
		
        $res = ceil( $res / Config::get('files_on_page') );
        
		$nk = Config::get( 'social_plugins', 'nk_page' ) == 1 || Config::get( 'social_plugins', 'nk' ) == 1;
		$google = Config::get( 'social_plugins', 'google_page' ) == 1 || Config::get( 'social_plugins', 'google' ) == 1;
		
        for ( $i = 1; $i <= $res; $i++ )
		{
            $this->addUrl( ABS_URL . 'share/' . $i, "daily", "1.0", time());
            
            if ( $nk )
			{
                $this->addUrl( ABS_URL . 'nk/' . $i, "daily", "1.0", time());
			}
			
            if ( $google )
			{
                $this->addUrl( ABS_URL . 'google/' . $i, "daily", "1.0", time());
			}
        }
		
		if( $tmp )
		{
			$this->saveTmp();
		}
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function map_main( $tmp = false )
    {
        
        $res = PD::getInstance()->cnt(IPS__FILES, array(
			'upload_activ' => 1,
			'upload_status' => 'public'
		));
		
        $res = ceil($res / Config::get('files_on_page'));
        
        for ( $i = 1; $i <= $res; $i++ )
		{
            $this->addUrl(ABS_URL . 'page/' . $i, "daily", "1.0", time());
        }
		
		if( $tmp )
		{
			$this->saveTmp();
		}
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function map_wait( $tmp = false )
    {
        
        $res = PD::getInstance()->cnt( IPS__FILES, array( 
			'upload_activ' => 1,
			'upload_status' => 'public'
		));
        $res = ceil($res / Config::get('files_on_page'));
        
        for ( $i = 1; $i <= $res; $i++ )
		{
            $this->addUrl( ABS_URL . 'waiting/' . $i, "daily", "1.0", time() );
		}
        
		if( $tmp )
		{
			$this->saveTmp();
		}
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function map_archive( $tmp = false )
    {
        $res = PD::getInstance()->cnt( IPS__FILES, array(
			'upload_status' => 'archive'
		));
		
        $res = ceil($res / Config::get('files_on_page'));
        
        for ( $i = 1; $i <= $res; $i++ )
		{
            $this->addUrl( ABS_URL . 'archive/' . $i, "daily", "0.8", time() );
        }
		
		if( $tmp )
		{
			$this->saveTmp();
		}
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function generateXml()
    {
        
        $xml = array();
        
        $xml[] = sprintf( '<?xml version="1.0" encoding="%s"?>', $this->xml_coding);
        $xml[] = sprintf( '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">');
        $xml[] = sprintf( '<!-- Last update: %s -->', date( $this->lastModDateTime ) . substr( date("O"), 0, 3 ) . ":" . substr( date("O"), 3 ) );
        
        foreach ( $this->urls as $url )
		{
			$xml[] = '<url>';
            $xml[] = "\t" . sprintf('<loc>%s</loc>', $this->xmlconv($url['url']));
			
            if ( isset( $url['lastmod'] ) && is_numeric( $url['lastmod'] ))
			{
                 $xml[] = "\t" . sprintf('<lastmod>%s</lastmod>', date($this->lastModDate, $url['lastmod']));
            }
			
            if ( isset( $url['changefreq'] ) )
			{
                $xml[] = "\t" . sprintf('<changefreq>%s</changefreq>', $this->xmlconv($url['changefreq']));
            }
			
            if ( isset( $url['priority'] ) )
			{
                $priorityStr = sprintf('<priority>%s</priority>', $this->priority_format);
                $xml[]        = "\t" . sprintf( $priorityStr, $url['priority'] );
            }
			
            $xml[] = '</url>';
        }
        
        $xml[] = '</urlset>';
        
        return join( "\n", $xml);
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function addUrl( $url, $changefreq = null, $priority = null, $lastMod = false )
    {
        $this->ERR = '';
        
        if ( count($this->urls) >= $this->max_urls )
		{
            $this->ERR .= "Możesz dodać tylko " . $this->max_urls . " linków do mapy strony.<br />";
            return false;
        }
        
        $dane = array(
            'url' => $url
        );
        
        if (!is_null($lastMod) && is_numeric($lastMod)) {
            $dane['lastmod'] = (int) $lastMod;
        }
        
        if (!is_null($changefreq) && in_array($changefreq, $this->changefreq)) {
            $dane['changefreq'] = $changefreq;
        }
        
        if ( !is_null($priority) && $priority != '' )
		{
            if ($priority <= $this->priority_min)
			{
               $priority = 0.1; 
            }
			if ( $priority >= $this->priority_max )
			{
                $priority = 1; 
            }
        }
		
        $dane['priority'] = (string)round( $priority, 2 );
		
        $this->urls[] = $dane;
    }
    /**
     * Borrowing from GoogleSitemap Convert text to XML
     * @$txt        text
     */
    public function xmlconv($txt)
    {
        static $conv;
        if ( !isset( $conv ) )
		{
            $conv = get_html_translation_table( HTML_ENTITIES, ENT_QUOTES );
            foreach ( $conv as $key => $value )
			{
				$conv[$key] = '&#' . ord($key) . ';';
			}
			
            $conv[chr(38)] = '&';
        }
		
        return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/", "&#38;", strtr( $txt, $conv ) );
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function generate( $compress = false )
    {
        
        $xml = $this->generateXml();
		
        if ( $compress )
		{
            $compress = function_exists( $this->compresion );
        }
		
        if ( $compress )
		{
            $xml = $this->compress( $xml );
        }
		
		return $xml;
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function compress( $string )
    {
        $func = $this->compresion;
        
        if ( strlen($func) == 0 || !function_exists( $func ) )
            return null;
        
        return $func( $string );
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function submitGoogle()
    {
        fopen( "http://www.google.com/webmasters/sitemaps/ping?sitemap=" . urlencode( ABS_URL . 'sitemap.xml' ), "r" );
    }
}
?>