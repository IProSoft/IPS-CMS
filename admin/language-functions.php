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
	* Nowe tłumaczenia
	*/
	function createLanguage( $new_code, $post_data )
	{
		if( empty( $new_code ) )
		{
			ips_admin_redirect( 'language', 'action=add', __('translation_add_code'));
		}
		
		if( !isset($post_data['from']) || !in_array( $post_data['from'], Translate::codes()) )
		{
			ips_admin_redirect( 'language', 'action=add', __('language_code_not_found') );
		}
		
		$exists = PD::getInstance()->select( 'translations', array( '
			language' => $new_code
		), 1);
		
		if( !empty( $exists ) )
		{
			return ips_admin_redirect( 'language', 'action=add', __('translation_already_exists') );
		}
		
		/**
		* Ok, dodajemy.
		*/
		$phrases = PD::getInstance()->select( 'translations', array(
			'language' => $post_data['from']
		), 0, "translation_name, translation_value, orginal");
				
		foreach( $phrases as $key => $value )
		{
			PD::getInstance()->insert("translations", array(
					'trans_id' => 'NULL', 
					'translation_name' => $value['translation_name'], 
					'translation_value' => $value['translation_value'], 
					'orginal' => $value['orginal'], 
					'language' => $new_code
				)
			);
		}
		
		$languages = Translate::codes();
		
		Config::update( 'language_settings', array(
			'language_locales' => array_merge( Config::getArray( 'language_settings', 'language_locales' ), array(
				$new_code => $languages[ $post_data['from'] ]
			) )
		));
		
		return ips_admin_redirect( 'language', 'action=edit&lang_code=' . $new_code, __('translations_correctly_copied'));
	}
	/*
	* Aktualizacja języka
	*/
	function updateLanguagePhrases( $language, $post_data, $redir )
	{
		
		$data = array();
		global ${IPS_LNG};
		foreach( $post_data as $translation_name => $translation_value )
		{
			if( is_array( $translation_value ) )
			{
				$translation_value = serialize( $translation_value );
			}
			
			if( $translation_value != "" )
			{
				$ex = strcmp( (string)${IPS_LNG}[$translation_name], (string)$translation_value );
				
				if( $ex !== 0 )
				{
					$ex = PD::getInstance()->cnt( 'translations', array(
						'translation_name' => $translation_name, 
						'language' => $language
					));
					
					if( $ex > 0 )
					{
						$data[$translation_name] = stripslashes( convert_line_breaks( $translation_value, '<br />' ) );
					}
				}
			}
		}
		if( !empty( $data ) )
		{
			$query = PD::getInstance()->prepare("UPDATE translations SET translation_value = ? WHERE translation_name = ? AND language = '" . $language . "' LIMIT 1");
			foreach ( $data as $name => $value )
			{
				$query->execute( array( $value, $name ) );
			}
		}

		Translate::getInstance()->clearLangCache();
		
		updateMenu();
		updateMenu( false, 'gag_sub_menu' );
		updateMenu( false, 'pinit_menu' );
		updateMenu( false, 'footer_menu' );
		
		clearCache( 'tpl' );
		
		ips_redirect( admin_url( 'language', 'action=edit&code=' . $language . ( $redir ? '&edit-action=' . $redir : '' ) ), __('translation_was_updated') );
	}
	
	/*
	* Usuwanie języka
	*/
	function deleteLanguage( $code )
	{
		$languages = Translate::codes();
		
		if( count( $languages ) > 1 )
		{
			PD::getInstance()->delete( 'translations', array( 
				'language' => $code
			) );
			
			array_map( 'unlink', glob( CACHE_PATH . '/*.php', GLOB_NOSORT ) );

			$settings = Config::getArray( 'language_settings' );
			
			if( isset( $settings['language_locales'][$code] ) )
			{
				unset( $settings['language_locales'][$code] );
			}
			
			Config::update( 'language_settings', serialize( $settings ) );
			
			return ips_admin_redirect( 'language', false, __('translation_was_removed') );
		}
		else
		{
			return ips_admin_redirect( 'language', false, __('translation_delete_error') );
		}	
	}
	
	/*
	* Eksport języka do pliku
	*/
	function exportLanguage( $code )
	{
		$languages = Translate::codes();
		
		$file = 'lang_' . $code. '.txt';
		
		if( in_array( $code , $languages ) )
		{
			
			$lang = '';
			$phrases = PD::getInstance()->select( 'translations', array(
				'language' => $code
			), 0, "translation_name, translation_value");
			
			foreach( $phrases as $key => $value )
			{
				$lang .= $value['translation_name'] . '[space]' . $value['translation_value'] . "[enter]";
			}
			
			file_put_contents( IPS_ADMIN_PATH .'/backup/' . $file, $lang);
		}
		ips_admin_redirect( 'language', false, __s( 'translation_was_exported', $file ) );

	}
	
	function importLanguage( $code )
	{
		$languages = Translate::codes();
		
		if( in_array( $code , $languages ) )
		{
			$post_data = array();
			$phrases = explode( '[enter]', file_get_contents( IPS_ADMIN_PATH .'/backup/lang_' . $code. '.txt' ) );
			foreach( $phrases as $key => $value )
			{
				if( strpos( $value, '[space]' ) !== false)
				{
					$phrase = explode( '[space]', $value );
					$post_data[$phrase[0]] = $phrase[1];
				}
			}

			updateLanguagePhrases( $code, $post_data );
		}
		
		ips_admin_redirect( 'language' );
	}
	
	function getInputTranslations( $action, $translation, $ajax = false )
	{
		$query = PD::getInstance()->from( 'translations')->fields('translation_name, translation_value, orginal');
		
		if( IPS_VERSION != 'pinestic' )
		{
			$query->where( 'translation_name', 'pinit_', 'NOT REGEXP' );
		}
		
		$query->where( 'language', get_input( 'search_code' ) ? $_POST['search_code'] : $translation );
		
		
		switch( $action )
		{	
			case 'email':
				$query->brackets( '(', 'AND' );
				$query->where( 'translation_name', '^email_', 'REGEXP');
				$query->brackets( ')' );
			break;
			case 'language_email':
				$query->brackets( '(', 'AND' );
				$query->where( 'translation_name', '^top_', 'REGEXP');
				$query->orWhere( 'translation_name', '^menu_', 'REGEXP');
				$query->brackets( ')' );
			break;
			case 'login':
				$query->brackets( '(', 'AND' );
				$query->where( 'translation_name', '^user_register', 'REGEXP');
				$query->orWhere( 'translation_name', '^user_log', 'REGEXP');
				$query->orWhere( 'translation_name', '^user_ac', 'REGEXP');
				$query->brackets( ')' );
			break;
			case 'all':
			break;
			case 'search-phrase':
				
				if( !isset( $_POST['search'] ) || empty( $_POST['search'] )  )
				{
					if( $ajax )
					{
						return ips_message( array(
							'alert' => 'translation_search'
						), true );
					}
					ips_admin_redirect( 'language', false, __('translation_search') );
				}

				$query->brackets( '(', 'AND' )->where( 'translation_value', $_POST['search'], 'REGEXP' )->orWhere( 'translation_name', $_POST['search'], 'REGEXP' )->brackets( ')' );

			break;
			case 'result':
				/* search results */
			break;
			default:
				if( !empty( $action ) )
				{
					$query->where( 'translation_name', '^' . $action . '_', 'REGEXP' );
				}
			break;
			
		}
		
		
		$phrases = $query->get();
		
		
		if( empty( $phrases ) )
		{
			if( $ajax )
			{
				return false;
			}
			ips_admin_redirect( 'language', false, __('nothing_found') );
		}
		
		$html = '';
		foreach( $phrases as $key => $value )
		{
			if( is_serialized( $value['translation_value'] ) )
			{
				$translation = unserialize( $value['translation_value'] );
				$orginal = unserialize( $value['orginal'] );
				
				foreach( $translation as $sub_key => $sub_value )
				{
					$html .= setTranslationHtml( array(
						'translation_name' => $value['translation_name'] . '[' . $sub_key . ']',
						'translation_value' => $sub_value,
						'orginal' => $orginal[$sub_key],
					) );
				}
				continue;
			}
			
			$html .= setTranslationHtml( $value );
		}
		return $html;
	}
	
	function setTranslationHtml( $value )
	{
		return '
		<div class="option-cnt">
			<div class="option-label-top">
				<span>' . $value['translation_name'] . '</span><span class="translation_value">'.strip_tags( otherOrginal( $value['translation_name'], $value['orginal'] ) ).'</span>
			</div>
			<div class="option-inputs">
				<textarea name="' . $value['translation_name'] . '">' . convert_line_breaks( stripslashes( $value['translation_value'] ), "\n").'</textarea>
			</div>
		</div>';
	}
	function otherOrginal( $translation_name, $orginal )
	{
		switch( $translation_name )
		{
			case 'meta_site_title':
				return __('default_page_title');
			break;
			case 'meta_site_description':
				return __('default_page_description');
			break;
			case 'meta_site_keywords':
				return __('default_keywords');
			break;
			case 'meta_site_footer':
				return __('content_footer');
			break;
		}
		
		return $orginal;
	}
?>