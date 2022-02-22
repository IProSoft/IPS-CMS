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

class Site_Info
{
	
	private $id = false;
	
	private $_pageType = 'index';
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getInfo( $type )
	{
		if ( isset( $this->info[$type] ) )
		{
			return $this->info[$type];
		}
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function setInfo( )
	{
		
		if ( IPS_ACTION_GET_ID && IPS_POST_PAGE )
		{
			$this->row = getFileInfo();
		}
		
		$this->info['site_title']       = IPS_VERSION === 'vines' && IPS_ACTION == 'main' && Config::get( 'vines_main_as_file' ) ? $this->vinesTitle() : $this->title();
		$this->info['site_keywords']    = $this->keywords();
		$this->info['site_description'] = $this->description();
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function vinesTitle( )
	{
		$data = PD::getInstance()->select( IPS__FILES, array(
			'upload_status' => 'public',
			'upload_activ' => 1 
		), 1, 'title', array(
			'date_add' => 'DESC' 
		) );
		
		if ( !empty( $data['title'] ) )
		{
			return $data['title'];
		}
		
		return $this->title();
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function title( )
	{
		global ${IPS_LNG};
		
		switch ( IPS_ACTION )
		{
			case 'tag':
			case 'smilar':
				if ( isset( $_GET['tag_id'] ) )
				{
					$tag = $_GET['tag_id'];
					
					if ( is_numeric( $tag ) )
					{
						$data = PD::getInstance()->select( 'upload_tags', array(
							'id_tag' => $_GET['tag_id'] 
						), 1 );
						
						if ( !empty( $data['tag'] ) )
						{
							$tag = mb_convert_case( $data['tag'], MB_CASE_TITLE, "UTF-8" );
						}
					}
					
					return ( !is_numeric( $tag ) ? $tag . ' - ' : '' ) . ${IPS_LNG}['meta_site_title'] . ' - ' . __s( 'meta_page_num', IPS_ACTION_PAGE );
				}
				
			break;
			case 'categories':
				$category = Categories::getCategories( IPS_ACTION_GET_ID );
				
				return ${IPS_LNG}['meta_' . IPS_ACTION] . $category['category_name'];
			break;
			case 'file_page':
				
				if ( isset( $this->row['title'] ) )
				{
					return str_replace( '"', '', $this->row['title'] );
				}
				
			break;
			case 'pin':
				
				if ( isset( $this->row['pin_title'] ) )
				{
					return str_replace( '"', '', $this->row['pin_title'] );
				}
				
			break;
			
			default:
				
				$action = IPS_ACTION;
				
				if ( isset( $_GET['on_profile'] ) )
				{
					$action = 'profile';
				}
				
				if ( isset( ${IPS_LNG}['meta_' . $action] ) && !empty( ${IPS_LNG}['meta_' . $action] ) && ${IPS_LNG}['meta_' . $action] != 'false' )
				{
					return ${IPS_LNG}['meta_' . $action] . ( isset( $_GET['login'] ) ? ( !empty( $_GET['login'] ) ? $_GET['login'] : USER_LOGIN ) : '' );
				}
				
			break;
		}
		return ${IPS_LNG}['meta_site_title'];
	}
	/**
	 * Pobieranie słów kluczowych dla konkretnego materiału lub dla całej strony
	 * Downloading keywords for a particular material or for the entire page
	 *
	 * @param null
	 * 
	 * @return string
	 */
	public function keywords( )
	{
		if ( isset( $this->row ) && IPS_POST_PAGE )
		{
			if ( !empty( $this->row['upload_tags'] ) )
			{
				return $this->row['upload_tags'];
			}
		}
		global ${IPS_LNG};
		
		return ${IPS_LNG}['meta_site_keywords'];
	}
	/**
	 * Site Description
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function description( )
	{
		global ${IPS_LNG};
		
		if ( IPS_ACTION_GET_ID && IPS_POST_PAGE )
		{
			if ( IPS_ACTION == 'file_page' )
			{
				if ( isset( $this->row['title'] ) && isset( $this->row['bottom_line'] ) )
				{
					return filter_var( $this->row['title'] . ( strcmp( $this->row['title'], $this->row['bottom_line'] ) != 0 ? '  ' . $this->row['bottom_line'] : '' ) . ' - ' . ${IPS_LNG}['meta_site_description'], FILTER_SANITIZE_STRING );
				}
			}
			elseif ( IPS_ACTION == 'pin' )
			{
				if ( isset( $this->row['pin_title'] ) && isset( $this->row['pin_description'] ) )
				{
					return filter_var( $this->row['pin_title'] . ( strcmp( $this->row['pin_title'], $this->row['pin_description'] ) != 0 ? '  ' . $this->row['pin_description'] : '' ) . ' - ' . ${IPS_LNG}['meta_site_description'], FILTER_SANITIZE_STRING );
				}
			}
			
		}
		
		return ${IPS_LNG}['meta_site_description'];
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __destruct( )
	{
		unset( $this );
	}
}
?>