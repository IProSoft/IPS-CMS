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
	

interface InterfacePluggable
{
	/**
	* Call plugin install function
	*
	* @param 
	* 
	* @return 
	*/
	public function install();

	/**
	* Plugin uninstall
	*
	* @param 
	* 
	* @return 
	*/			
	public function uninstall();
	/**
	* Get plugin info
	*
	* @param 
	* 
	* @return 
	*/
	public function info();
	/**
	* Initialize plugin object
	*
	* @param 
	* 
	* @return 
	*/
	public function init();
	
	/**
	* Register hooks/filters while page load
	*
	* @param 
	* 
	* @return 
	*/
	public function hooks();
	
	/**
	* Register plugin language phrases
	*
	* @param 
	* 
	* @return 
	*/
	public function translate();
	
	/**
	* Define if the plugin have to be called while script load
	*
	* @param 
	* 
	* @return bool
	*/
	public function hasHooks();
}

?>