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
	include_once ( IPS_ADMIN_PATH .'/language-functions.php' );
	
	$languages = Translate::codes();	
	
	$default_language = Config::getArray( 'language_settings', 'default_language');
	
	if( isset( $_POST['code'] ) && !empty( $_POST['code'] ) )
	{
		$code = $_POST['code'];
		if( in_array( $code, $languages ) )
		{
			$redir = $_POST['translation-action'];
			
			unset( $_POST['code'] );
			unset( $_POST['translation-action'] );
			
			updateLanguagePhrases( $code, $_POST, $redir );
		}
	}
	
	echo admin_caption( 'language_translation' );
	
	$menu = array(
		'settings' => 'settings',
		'edit' => 'language_edit_translations'
	);
	
	if( Config::getArray( 'language_settings', 'ips_multilanguage' ) )
	{
		$menu = array_merge( $menu, array(
			'add' => 'language_add_translations'
		) );	
	}
	
	$form = array();
	$langs = '';
	foreach( $languages as $lang => $code )
	{
		$langs .= '<a class="button" href="' . admin_url( 'language', 'code=' . $code ) .'"> '.$code.' </a>';
		$form[$code] = $code;
	}
	
	echo responsive_menu( $menu, admin_url( 'language', 'action=' ) ) . '<br />';
	
	$action = isset( $_GET['action'] ) && !empty( $_GET['action'] ) ? $_GET['action'] : 'edit';
	
	if( $action == 'add' )
	{
		if( isset( $_POST['create'] ) )
		{
			createLanguage( $_POST['create'], $_POST );
		}
		if( Config::getArray( 'language_settings', 'ips_multilanguage' ) )
		{
			echo '	
			<div class="tabbed_box">
				<form method="post" class="translation newlangcreate newlang_form" action="" >
					' . displayArrayOptions(array(
						'create' => array(
							'current_value' => '',
							'option_set_text' => 'language_code_created',
							'option_type' => 'input',
							'option_lenght' => 10
						),
						'from' => array(
							'current_value' => $default_language,
							'option_set_text' => 'language_select_to_copy',
							'option_select_values' => $form
						),
					)) . '
					<button class="button">'.__('language_create').'</button>
				</form>
			</div>';	
		}
	
	}
	elseif( $action == 'edit' )
	{
		
		$translation = Config::getArray( 'language_settings', 'default_language');
		
		if( isset( $_GET['code'] ) && !empty( $_GET['code'] ) )
		{
			if( in_array($_GET['code'], $languages ) )
			{
				$translation = $_GET['code'];
			}
		}
		
		$current_value = Config::getArray( 'language_settings', 'language_locales' );
		
		echo '	
		<div class="tabbed_box">
			<form method="post" action="admin-save.php">' . 
				displayArrayOptions( array(
						'language_settings' => array(
							'option_set_text' => __( 'language_settings_title' ) . ' (' . $translation . ')',
							'option_is_array' => array(
								'language_locales][' . $translation  . '' => array(
									'option_set_text' => __( 'social_plugins_language_title' ),
									'current_value' => $current_value[$translation],
									'option_select_values' => array_combine( languagesList(), languagesList() )
								)
							)
						)
				) ) 
				. '
				<button class="button">'.__('save').'</button>
			</form>
		</div>
		
		<div class="content_tabs tabbed_area">
			<div class="caption_small"><span class="caption">' . __('language_select_for_editing') . '</span></div>
			<div class="option-cnt translation-edit">' . $langs . '</div>
		</div>
		';	
		
		
		$languages_social = Config::getArray( 'language_settings', 'language_locales');
		

		$action = isset( $_GET['edit-action'] ) && !empty( $_GET['edit-action'] ) ? $_GET['edit-action'] : 'add';

		echo '
		<div class="tabbed_box">	
			<div class="title_caption"><span class="caption" style="display: inline-block; width: 210px;">' . __s('language_current_edit', $translation ) . '</span>
			
				<form class="translation search" action="' . admin_url( 'language', 'action=edit&edit-action=search-phrase&code=' . $translation ) . '" method="post" style="width: 700px; display: inline-block; text-align: right;">
					<input type="hidden" value="true" name="search">
					<input type="hidden" value="' . $translation . '" name="search_code">
					<input type="text" value="" name="search" class="search-phrase" />
					<button class="button">'.__('language_search_field').'</button>
				</form>
			
			</div>
			<div class="tabbed_box translate_options">	
				' . 
				responsive_menu( array(
					'add' => 'language_add',
					'all' => 'language_all',
					'bbcode' => 'language_bbcode',
					'comments' => 'language_comments',
					'common' => 'language_common',
					'connect' => 'language_connect',
					'contact' => 'language_contact',
					'contest' => 'language_contest',
					'edit' => 'language_edit',
					'email' => 'language_email',
					'err' => 'language_err',
					'favourites' => 'language_favourites',
					'generator' => 'language_generator',
					'js' => 'language_js',
					'login' => 'language_log_in',
					'menu' => 'language_menu',
					'meta' => 'language_meta',
					'pw' => 'language_pw',
					'report' => 'language_report',
					'search' => 'language_search',
					'user' => 'language_user',
					'date_time' => 'language_date_time',
					'widget' => 'language_widgets'
				), admin_url( 'language', 'action=edit&code=' . $translation . '&edit-action=' ) ) .'
			</div>	
		</div>
		
		<form method="post" class="translation main-form" action="">
			<input type="hidden" name="code" value="' . $translation . '" />
			<input type="hidden" name="translation-action" value="' . $action . '" />
			<div class="tabbed_area">	
				'. getInputTranslations( $action, $translation ) . '
			</div>	
			<button class="button">' . __('language_save') . '</button>
		</form>
		
		<div class="tabbed_area" style="padding: 10px">
			
			<a href="' . admin_url( 'language', 'code=' . $translation.'&action=delete' ) . '" class="button">'.__('remove_language').'</a>
			
			<a href="' . admin_url( 'language', 'code=' . $translation.'&action=export' ) . '" class="button">'.__('export_language').'</a>
			
		</div>
		';
	}
	elseif( $action == 'settings' )
	{
		echo '
		<div class="tabbed_box">
			<form method="post" action="admin-save.php">
				' . displayArrayOptions( array(
					'language_settings' => array(
						'option_is_array' => array(
							'ips_multilanguage',
							'default_language' => array(
								'current_value' => $default_language,
								'option_select_values' => $form,
								'option_depends' => Config::getArray( 'language_settings', 'ips_multilanguage' )
							),
							'allow_change_languages' => array(
								'option_depends' => Config::getArray( 'language_settings', 'ips_multilanguage' )
							)
						)
					)
				) ) . '
				<button class="button">'.__('save').'</button><br />
			</form>
		</div>
		';
	}
	elseif( isset( $_GET['action'] ) && isset( $_GET['code'] )  )
	{
		switch( $_GET['action'] )
		{
			case 'delete':
				deleteLanguage( $_GET['code'] );
			break;
			case 'export':
				exportLanguage( $_GET['code'] );
			break;
			case 'import':
				importLanguage( $_GET['code'] );
			break;
		}	
	}
	
	
	
	
	


