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

class Fast_Controller
{
	public function route()
	{
		$action = get_input( 'action' );
	
		if( Config::get('page_fast') == 0 || !in_array( $action, array( 'wait', 'main' ) ) || IPS_VERSION == 'pinestic' )
		{
			ips_redirect( false, 'fast_blocked' );
		}
		
		$sql = PD::getInstance()->cnt( IPS__FILES, array( 
			'upload_activ' => ( $action == 'main' ? 1 : 0 )
		));

		if( empty( $sql ) )
		{
			return ips_redirect( false, array(
				'info' => strip_tags( __( 'fast_no_files' ) )
			));
		}
		
		add_filter( 'init_css_files', function( $array ){
			return add_static_file( $array, array(
				'js/fast.js'
			) );
		}, 10 );
		
		add_filter( 'init_js_files', function( $array ){
			return add_static_file( $array, array(
				'css/fast.css'
			) );
		}, 10 );
		
		return Templates::getInc()->getTpl( 'fast.html', array(
			'action' => $action, 
			'logo' => 'logo-' . IPS_VERSION . '.png'
		) );
	}
}