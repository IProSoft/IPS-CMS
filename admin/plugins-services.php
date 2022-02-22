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
	
	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");
	
	echo admin_caption( 'caption_plugins' );
	
	$plugins = new Plugin_Manage();
	$plugins->loadPlugins();
	
	if( isset( $_GET['update'] ) )
	{
		$response = $plugins->updatePlugin( $_GET['update'] );
		
		if( $response == true )
		{
			echo admin_msg( array(
				'info' =>  __('plugin_updated')
			) );
		}
		
		else
		{
			echo $response;
		}
	}
	
	if( isset( $_GET['activate'] ) )
	{
		$plugins->activatePlugin( $_GET['activate'] );
	}
	
	if( isset( $_GET['deactivate'] ) )
	{
		$plugins->deactivatePlugin( $_GET['deactivate'] );
	}
	
	if( isset( $_GET['available'] ) )
	{
		echo $plugins->availablePlugins();
	}
	elseif( !isset( $_GET['plugin'] ) )
	{		
		if( !empty( $plugins->plugins ) )
		{
			$plugins->initAllPlugins();
		}
		else
		{
			echo '<h4>' . __( 'plugins_list_empty' ) . '</h4>';
		}
	}
	else
	{
		echo $plugins->initPlugin( $_GET['plugin'] );
	}
	
	if( !isset( $_GET['available'] ) )
	{
		echo '<hr><a href="' . IPS_ADMIN_URL . '/route-plugins?available" class="button">' . __( 'plugins_list_available' ) . '</a>';
	}
?>