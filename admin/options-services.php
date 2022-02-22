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
	
	
	
	$main_action = isset($_GET['action']) && !empty( $_GET['action'] ) ? $_GET['action'] : 'other_options';
	$sub_action = isset( $_GET['sub_action'] ) && !empty( $_GET['sub_action'] ) ? $_GET['sub_action'] : ($main_action == 'upload' ? ( IPS_VERSION == 'pinestic' ? 'pinit' : 'add') : 'none' ) ;
	
	$sub_text = isset( ${IPS_LNG}['actions_text_' . $sub_action] ) ? ' > ' . ${IPS_LNG}['actions_text_' . $sub_action] : '';

	echo admin_caption( __( 'caption_options' ) . ' > '.__( 'caption_small_' . $main_action ) . $sub_text );
	
	echo '<form action="admin-save.php" enctype="multipart/form-data" method="post">		';
	
	if( $main_action == "other_options" )
	{ 
		if( defined('IPS_SELF') )
		{
			echo displayOptionField( 'like_image_block' );
		}
			
		echo '
			<!-- Main Options -->
			' . displayArrayOptions( getOptionsFile()['main_options'] ) . '
			<!-- End Main Options -->
			<span>
				<a id="dialog_wybor" href="#" rel="'. Config::getArray('js_dialog', 'in').'" onclick="podglad_dialog(); return false;">Podgląd Widgetu Dialog</a>
			</span>
		';
	}
	elseif( $main_action == "meta" )
	{
		ips_admin_redirect( 'language', 'action=meta&code=' . Config::getArray( 'language_settings', 'default_language') );
	}
	elseif( $main_action == "sitemap" )
	{
		ips_admin_redirect( 'sitemap' );
	}
	elseif( $main_action == "privileges" )
	{ 
		echo '
			<!-- Priviliges -->
			' . displayArrayOptions( getOptionsFile()['options_privileges'] ) . '
			<!-- End Priviliges -->
		';
	}
	elseif( $main_action == "apps" )
	{ 
		Nk_UI::isAppValid( true );
		Facebook_UI::isAppValid( true );
		
		echo '
				<!-- Apps -->
				' . displayArrayOptions( getOptionsFile()['options_apps'] ) . '
				<!-- End Apps -->

		
			<a href="' . admin_url( 'fanpage', 'action=settings' ) . '" class="button" target="_blank">Uruchom lub odśwież konfigurację API Facebook</a>
		
			<div class="div-info-message">
				<p>
					Wpisz <strong>"false"</strong> jeśli nie posiadasz wybranych danych.<br /><br />
					AUTOPOST publikuje materiał tylko gdy użytkownik przegląda witrynę.<br /><br />
					Facebook Aplication ID, Secret służą do logowania poprzez Facebook, NK KLUCZ i SEKRET służą do logowania poprzez nk.pl (jeśli nie chcesz używać tej opcji pozostaw bez zmian).<br /><br />
					<a href="http://www.iprosoft.pl/aplikacja-na-nk-pl-developers/" target="_blank">Zakładanie aplikacji NK</a>.<br /><br />
					<a href="http://www.iprosoft.pl/jak-zalozyc-aplikacje-na-facebook-com/" target="_blank">Zakładanie aplikacji Facebook</a>.<br /><br />
					***Zapętla rejestrację poprzez Facebook wymagając zaakceptowania zezwolenia na publikowanie treści na ścienie użytkownika( autopost )
				</p>
				
				<p>
					' . __( 'apps_facebook_app_save_token_info' ) . '
				</p>
			</div>';
	}
	elseif( $main_action == "fast")
	{ 
		echo '
				<!-- Fast -->
				' . displayArrayOptions( getOptionsFile()['options_fast'] ) . '
				<!-- End Fast -->
		';
	}

	elseif( $main_action == "email")
	{

		echo '
				<!-- Add -->
				' . displayArrayOptions( getOptionsFile()['options_email'] ) . '
				<!-- End Add -->
			';

	}
	
	elseif( $main_action == "upload")
	{ 
		$links = array(
			'add' => 'actions_text_add',
			'animation' => 'actions_text_animation',
			'article' => 'actions_text_article',
			'demotywator' => 'actions_text_demotywator',
			'gallery' => 'actions_text_gallery',
			'mem' => 'actions_text_mem',
			'ranking' => 'actions_text_ranking',
			'text' => 'actions_text_text',
			'video' => 'actions_text_video',
			'watermark' => 'actions_text_watermark'
		);

		if( IPS_VERSION == 'pinestic' )
		{
			unset( $links['ranking'], $links['article'], $links['demotywator'], $links['mem'], $links['text'], $links['add'] );
			$links['pinit'] = 'actions_text_pinit';
		}
		
		echo responsive_menu( $links, admin_url( 'options', 'action=upload&sub_action=' ) );
		
		if( $sub_action == 'fonts' )
		{
			$fonts = new Admin_Web_Fonts();
	
			echo '
				<!-- Add -->
				' . displayArrayOptions( $fonts->listArrayFonts() ) . '
				<!-- End Add -->
			';
			Ips_Cache::clearDBCache( array(
				'cache_hash' => 'cache_fonts_options'
			) );
		}
		elseif( $sub_action == 'add' )
		{
			echo '
				<!-- Add -->
				' . displayArrayOptions( getOptionsFile()['options_add_types'] ) . '

				<!-- End Add -->
			';
		}
		elseif( $sub_action == 'pinit' )
		{
			echo '
				<!-- pinit -->
				' . displayArrayOptions( getOptionsFile()['options_pinit'] ) . '
				<!-- End pinit -->
			';
		}
		elseif( $sub_action == 'article' )
		{
			echo '
				<!-- Articles -->
				' . displayArrayOptions( getOptionsFile()['options_articles'] ) . '
				<!-- End Articles -->
			';
		}
		elseif( $sub_action == 'gallery' )
		{
			echo '
					<!-- Galleries -->
					' . displayArrayOptions( getOptionsFile()['options_galleries'] ) . '
					<!-- End Galleries -->

				<div class="div-info-message">
					<p>' . ${IPS_LNG}['admin_info_1'] . '</p>
				</div>
			';
		}
		elseif( $sub_action == 'animation' )
		{
			echo '
				<!-- Animation -->
				' . displayArrayOptions( getOptionsFile()['options_animation'] ) . '
				<!-- End Animation -->
			';
		}
		elseif( $sub_action == 'ranking' )
		{
			echo '

					<!-- Rankings -->
					' . displayArrayOptions( getOptionsFile()['options_ranking'] ) . '
					<!-- End Rankings -->

				<div class="div-info-message">
					<p>' . ${IPS_LNG}['admin_info_1'] . '</p>
				</div>
			';
		}
		elseif( $sub_action == 'video' )
		{
			echo '
				<!-- Video -->
				' . displayArrayOptions( getOptionsFile()['options_video'] ) . '
				<!-- End Video -->
			';
			
		}
		elseif( $sub_action == 'demotywator' )
		{
			echo '
				<!-- Demotywator -->
				' . displayArrayOptions( getOptionsFile()['options_demotywator'] ) . '
				<!-- End Demotywator -->
			';
			
		}
		elseif( $sub_action == 'mem' )
		{
			
			echo '
				<!-- Mem -->	
				' . displayArrayOptions( getOptionsFile()['options_mem'] ) . '
				<!-- Mem -->	
			';

		}
		elseif( $sub_action == 'text' )
		{
			
			echo '
				<!-- Text file -->
				' . displayArrayOptions( getOptionsFile()['options_add_text'] ) . '
				<!-- End Text file -->
				<div class="div-info-message">
					<br />
					<p><strong><sup>1</sup> </strong> ' . __( 'upload_text_options_info' ) . '</p>
				</div>
			';
			
		}
		elseif( $sub_action == 'watermark' )
		{
			
			echo '
				<!-- Watermark -->
				' . displayArrayOptions( getOptionsFile()['options_watermark'] ) . '
				<!-- End Watermark -->
				<div class="div-info-message">
					<p><strong><sup>1</sup> </strong> ' . __( 'watermark_options_info' ) . '</p>
				</div>
			';
		}
	}
	elseif( $main_action == "advanced")
	{ 
		echo '';
	}
	elseif( $main_action == "mysql") 
	{ 
		
	}
	elseif( $main_action == "optimization")
	{ 
		if( isset( $_GET['cache'] ) )
		{
			$info = clearCache( $_GET['cache'] );
			ips_admin_redirect( 'options', 'action=optimization', $info);
		}
		if( isset( $_GET['animated'] ) )
		{
			$info = checkImagesAnimations();
			ips_admin_redirect( 'options', 'action=optimization', $info);
		}

	echo '

		<!-- Cache -->
			' . displayArrayOptions( getOptionsFile()['cache_options'] ) . '
		<!-- End Cache -->

		<br />
		<div class="content_tabs tabbed_area">
			<div class="caption_small"><span class="caption">' . __( 'system_cache_clear_cache' ) . '</span></div>
			<div class="padding_10">
				<a class="button" href="' . admin_url( 'options', 'action=optimization&cache=tpl' ) . '">' . __( 'system_cache_clear_tpl' ) . '</a>
				<a class="button" href="' . admin_url( 'options', 'action=optimization&cache=jscss' ) . '">' . __( 'system_cache_clear_js_css' ) . '</a>
				<a class="button" href="' . admin_url( 'options', 'action=optimization&cache=ipscache' ) . '">' . __( 'system_cache_clear_files' ) . '</a>
				<a class="button" href="' . admin_url( 'options', 'action=optimization&cache=img' ) . '">' . __( 'system_cache_clear_tmp' ) . '</a>
				<a class="button" href="' . admin_url( 'options', 'action=optimization&animated=true' ) . '">' . __( 'system_cache_clear_animations' ) . '</a>
				<a class="button" href="' . admin_url( 'options', 'action=optimization&cache=mysql' ) . '">' . __( 'system_cache_clear_mysql' ) . '</a>
			</div>
		</div>
		<div class="div-info-message">
			<p><strong><sup>' . __( 'online_stats_title' ) . ' </sup> </strong> ' . __( 'online_stats_info' ) . '</p>
		</div>
	';
	}
	elseif( $main_action == "widget" )
	{ 

		echo displayWidgetsSettings();
		
	}
	elseif( $main_action == "social" )
	{
		echo displayArrayOptions( getOptionsFile()['options_social'], 'content_tabs', 'options_social_info' );
	}
	
	echo '
	<input type="submit" class="button" value="' . __('save_all') . '" />
	</form>
	';
	 			
		
	Session::set( 'admin_redirect', admin_url( 'options', 'action=' . $main_action . ( isset( $sub_action ) ? '&sub_action=' . $sub_action : '' ) ) );
		
?>