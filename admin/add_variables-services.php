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

	
	
	echo admin_caption( 'caption_add_variables' );
		
	if( isset( $_POST['key'] ) && isset( $_POST['value'] ) && isset( $_POST['autoload'] ) )
	{
		if( preg_match( '/^([0-9a-z\_]*){9,50}$/i', $_POST['key'] ) )
		{
			Config::update( trim( $_POST['key'] ),  trim( $_POST['value'] ), ( $_POST['autoload'] == 'on' ? true : false ) );
			
			echo admin_msg( array(
				'success' => __( 'settings_add_success' )
            ));
		}
		else
		{
			echo admin_msg( array(
				'alert' => __( 'settings_name_error' )
			) );
		}
	}
	elseif( isset( $_POST['language-key'] ) && isset( $_POST['language-value'] ) )
	{
		
		if( !isset( ${IPS_LNG}[$_POST['language-key']] ) || empty( ${IPS_LNG}[$_POST['language-key']] )  )
		{
			if( preg_match( '/^([0-9a-z\_]+){6,50}$/i', $_POST['language-key'] ) )
			{
				$languages = array_keys( Config::getArray( 'language_settings', 'language_locales' ) );
				foreach( $languages as $lang => $code )
				{
					PD::getInstance()->insert("translations", array(
						'translation_name' => trim($_POST['language-key']), 
						'translation_value' => $_POST['language-value'], 
						'orginal' => ( !empty( $_POST['language-orginal'] ) ? $_POST['language-orginal'] : $_POST['language-value'] ), 
						'language' => $code
					));
					
					echo admin_msg( array(
						'success' => __s( 'settings_lang_success', $code )
					) );
				}
				
				Translate::getInstance()->clearLangCache();
			}
			else
			{
				echo admin_msg( array(
					'alert' => __( 'settings_lang_name_error' )
				) );
			}
		}
		else
		{
			echo admin_msg( array(
				'alert' => __( 'settings_lang_exists_error' )
			) );
		}
	}		
	echo Templates::getInc()->getTpl( '/__admin/add_options.html');
?>
