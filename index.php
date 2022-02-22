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

	session_start();
	ob_start();
	include( dirname(__FILE__) . '/php-load.php' );

	/**
	* APP
	*/
	App::init();
	
	App::initHeader( App::$base_args );
	App::initMenu( App::$base_args );
	App::initContent( App::$base_args );
	App::initSideBlock( App::$base_args );
	App::initFooter( App::$base_args );
	
	App::load( App::$base_args );
	
	echo App::minify( Templates::getInc()->getTpl( 'base.html', App::$base_args ) );

