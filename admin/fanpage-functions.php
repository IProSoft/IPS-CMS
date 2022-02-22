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
function fanpageValidateSettings( $apps_fanpage_auto )
{
	$errors = array();
	
	if( empty( $apps_fanpage_auto['message'] ) && $apps_fanpage_auto['image_info'] == 'off' )
	{
		$errors[] = ips_message( array(
			'alert' =>  __('fill_in_required_fields')
		), true );
	}
	else
	{
		if( $apps_fanpage_auto['title_info'] == 'user' && empty( $apps_fanpage_auto['title'] )  )
		{
			$errors[] = ips_message( array(
				'alert' =>  __('apps_fanpage_auto_title_add')
			), true );
		}
		
		if( $apps_fanpage_auto['caption_info'] == 'user' && empty( $apps_fanpage_auto['caption'] )  )
		{
			$errors[] = ips_message( array(
				'alert' =>  __('apps_fanpage_auto_caption')
			), true );
		}
		
		if( $apps_fanpage_auto['description_info'] == 'user' && empty( $apps_fanpage_auto['description'] )  )
		{
			$errors[] = ips_message( array(
				'alert' =>  __('apps_fanpage_auto_more_details')
			), true );
		}
	}
	
	return $errors;
}
function fanpageDelete( $ids )
{
		
	if( is_array( $ids ) )
	{
		foreach( $ids as $id)
		{
			if( is_numeric( $id ) )
			{
				fanpageDelete( $id );
			}
		}
		return;
	}
	
		$ids = preg_replace( '/[^0-9_]/', '', $ids );
		
		$post = PD::getInstance()->select( 'fanpage_posts', array(
			'post_id' => $ids
		), 1 );
		
		PD::getInstance()->delete( 'fanpage_posts', array(
			'post_id' => $ids
		) );
	
		try {
			
			$token = Facebook_Fanpage::getToken( $post['fanpage_id'] );
			
			$response = Facebook_UI::getApp()->delete( '/' . $ids, [
				'method' => 'delete',
				'access_token' => $token
			], $token )->getDecodedBody();

			if( $response )
			{
				return true;
			}
			
		} catch ( Exception $e ) {
			
		}

	return false;
}