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

class Rss
{
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function get()
    {
		if( Config::get('rss_off') == 0 )
		{
			ips_redirect( false, 'rss_off' );
		}
		
		if( isset( $_GET['rss_type'] ) && strpos( $_GET['rss_type'], '.' ) !== false )
		{
			header('Content-type: application/xml');
			return $this->showRss( str_replace( '.xml', '', $_GET['rss_type'] ) );
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function switchRss($type)
    {
        switch ($type) {
            case 'waiting':
            case 'wait':
                return array(
                    'upload_activ' => 0,
                    'upload_status' => 'public'
                );
                break;
            case 'glowna':
            case 'main':
                return array(
                    'upload_activ' => 1,
                    'upload_status' => 'public'
                );
                break;
            default:
                return false;
            break;
        }
    }
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function showRss($type)
    {
        
        global ${IPS_LNG};
        
        $rss = '<?xml version="1.0" encoding="UTF-8" ?>
		<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
		<channel>
		<title>Kanał RSS</title>
		<description>' . ${IPS_LNG}['meta_site_title'] . '</description>
		<link>' . ABS_URL . '</link>
		<atom:link href="' . ABS_URL . $type . '.xml" rel="self" type="application/rss+xml" />
		';
        
        $res = PD::getInstance()->select(IPS__FILES, $this->switchRss($type), 20, false, '`date_add` DESC');
        
        foreach ($res as $row) {
            $data = date('r', strtotime($row['date_add']));
            
            $rss .= '
			<item>
				<title>' . $row['title'] . '</title>
				<description>&lt;img src="' . ips_img($row, 'thumb') . '" &gt;&lt;br&gt;' . $row['top_line'] . '</description>
				<link>' . seoLink($row['id'], $row['title']) . '</link>
				<guid>' . seoLink($row['id'], $row['title']) . '</guid>
				<pubDate>' . $data . '</pubDate>
				<enclosure url="' . ips_img($row, 'thumb') . '" type="image/jpeg" length="' . ips_img_path($row, 'thumb') . '" />
			</item>
			';
        }
        
        $rss .= '
		</channel>
		</rss>';
        
        return $rss;
    }
}
?>