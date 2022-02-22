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
	if( !defined('IPS_VERSION') ) die('IPS');
	
	if( IPS_VERSION == 'pinestic' )
	{
		ips_redirect();
	}
	
	if( !USER_LOGGED && !Config::getArray( 'user_guest_option', 'upload' ) )
	{
		Cookie::set( 'ips-redir', 'up/', 120 );
		return ips_redirect( 'login/', 'add_for_logged_in' );
	}
	
	$user = getUserInfo( USER_ID, true );

	if( $user['user_banned'] == 1 )
	{
		return ips_redirect('index.html', array(
			'alert' => 'add_ban'
		));
	}
	elseif( $user['activ'] == 0 )
	{
		return ips_redirect('index.html', array(
			'alert' => 'add_account_inactive'
		));
	}
	elseif( $user['date_add'] > date("Y-m-d H:i:s", ( time() - (Config::get( 'user_account', 'upload_wait_time' ) * 3600) ) ) && !USER_MOD )
	{
		return ips_redirect('index.html', array(
			'info' => __s( 'add_account_wait_register', Config::get( 'user_account', 'upload_wait_time' ) )
		));
	}
	
	if( Config::get('services_premium') )
	{
		if( !Premium::getInc()->premiumService( 'add' ) )
		{
			Premium::getInc()->premiumRedirect( 'up/' );
		}
	}

	$routes = App::routes( array(
		'upload_type', 'action', 'upload_id'
	));
	
	if( empty( $routes['upload_type'] ) )
	{
		$routes['upload_type'] = 'image';
	}
	
	add_filter( 'init_base_args', function( $base_args ) use ( $routes )
	{
		$base_args['body_class']['up'] = 'up_type_' . $routes['upload_type'];
		
		return $base_args;
	});
	
	/**
	* 
	* Wyświetlany menu głowne z możliwością wyboru
	* jednego z 6 typów materiałów lub wracamy do 
	* poprzeniego wyboru jest upload nie został zakończony,
	* zwrócił błąd lub uzytkownik edytuje materiał
	* 
	*/	

	require_once( ABS_PATH . '/functions-upload.php' );

	$edit_id = (int)get_input( 'edit_id', false );
	
	if( $edit_id)
	{
		Ips_Registry::get('Edit')->editor( $edit_id );
	}
	
	
	$allow_add = array_keys( array_filter( Config::getArray( 'upload_type' ), function( $val ){
		return intval( $val );
	}) );
	
	sort( $allow_add );
	
	if( empty( $allow_add ) )
	{
		return ips_redirect('index.html', array(
			'alert' => 'add_types_disabled'
		));
	}
	
	if( Config::getArray( 'upload_type', $routes['upload_type'] ) )
	{
		$upload_type = $routes['upload_type'];
	}
	else
	{
		$upload_type = current( $allow_add );
	}

	remove_action( 'before_content', 'call_widget' );
	
	add_filter( 'init_css_files', function( $array ){
		return add_static_file( $array, array(
			'css/up/dropzone.css',
			'css/up/canvas.css',
			'libs/Select2/css/select2.min.css',
			'libs/ColorPicker/ColorPicker/css/colorpicker.css'
		) );
	}, 10 );
	
	add_filter( 'init_js_files', function( $array ) use( $upload_type ){
		return add_static_file( $array, array(
			'libs/ColorPicker/ColorPicker/js/colorpicker_merged_ips.js',
			'libs/progressbar.js/progressbar.min.js',
			'libs/Select2/js/select2.min.js',
			'js/up/canvas_editor.js',
			'js/up/canvas_init.js',
			'js/up/canvas_text.js',
			'js/up/canvas_type_abstract.js',
			'js/up/canvas_demotywator.js',
			'js/up/canvas_mem.js',
			'js/up/upload.js',
			'js/up/drop_multiple.js', 
			'js/up/drop_preview.js', 
			'js/up/drop.js', 
			'js/up/drop_lines.js',
			'js/up/drop_' . $upload_type . '.js'
		)  );
	}, 10 );
		
	add_action( 'after_footer', 'App::async', array( array_column( Ips_Registry::get( 'Web_Fonts' )->getFontsUrls(), 'url' ) ) );
	
	
	
	$list = array();
	
	foreach( $allow_add as $add )
	{
		$list[] = '<li class="regular ' . ( $upload_type == $add ? 'active' : '' ) .'"><a href="/up/' . $add . '/"><span>' . __( 'common_type_' . $add ) . '</span></a></li>';
	}
	
	$html = '';
	
	if( !empty( $list ) && count( $list ) > 1 )
	{
		$html .= '<ul class="fancy-list">' .implode( ' ', $list ).'</ul>';			
	}			
	
	if( Session::getChild( 'upload_tmp', 'file_edit_time', 0 ) < time() - 30 )
	{
		Session::clear( 'upload_tmp' );
	}
	/**
	* Czyszczenie plików tymczasowych podczas edycji podglądu.
	* Nadawanie typu jeśli znajduje się w sesji podglądu.
	*/
	if( $field = up_edit_field( 'upload_type', false ) )
	{
		if( in_array( $field, $allow_add ) )
		{
			$upload_type = $field;
		}
	}
	
	if( $field = up_edit_field( 'file_name', false ) )
	{	
		if( file_exists( ABS_PATH . '/upload/' . md5( $field ) ) )
		{
			unlink( ABS_PATH . '/upload/' . md5( $field ) );
		}
	}
		

	/**
	* Wyświetlany menu wyboru Obrazek|Video|Tekst
	* tylko w wypadku dodawania demotywatora lub kwejka.
	*/
	
	$model = 'image';
	
	if( $upload_type == 'demotywator' || $upload_type == 'video' )
	{
		/** image|text|video|mp4 **/
		$upload_file_type = array_filter( Config::getArray( 'upload_' . $upload_type . '_type' ) );
		
		if( empty( $upload_file_type ) )
		{
			return ips_redirect( 'index.html', array(
				'alert' => 'add_types_disabled'
			));
		}
		if( $upload_type == 'demotywator' )
		{
			ksort( $upload_file_type );
		}
		
		$html .= '<div id="item-select" class="up-select">';
		
		$model = up_edit_field( 'upload_subtype', false );
		
		if( !$model || !in_array( $model, array_keys( $upload_file_type ) ) )
		{
			$model = key( $upload_file_type );
		}
	
		foreach( $upload_file_type as $upload_model => $val )
		{
			$html .= 
			'<span title="' . __( 'add_info_' . $upload_model) . '" class="up_item_' . $upload_model . ( $model == $upload_model ? ' active' : '' ) . '" data-id="' . $upload_model . '">
				' . __( 'common_type_' . $upload_model) . '
			</span>';
		}
		
		$html .= '</div>';	
	}
	elseif( $upload_type == 'text' )
	{
		$model = 'text';
	}

/**
* 
* Przygotowanie zmiennych dla formularza dodawania.
* 
*/	
	$variables = array(
		'post_title' => '',

		'post_top_line' => '',
		'post_top_line_json' => '',
		'post_bottom_line' => '',
		'post_bottom_line_json' => '',
		
		'post_long_text' => '',

		'post_upload_mp4_url' => '',
		'post_upload_swf_url' => '',
		
		'post_upload_url' => '',
		'post_link_thumb' => '',
		'post_upload_video_url' => '',
		'post_new_video' => '',
		'post_new_input' => '',
		'post_upload_source' => '',
		'post_upload_tags' => false,
	);
	
	array_walk( $variables, function( &$value, $key ){
		$value = up_edit_field( str_replace( 'post_', '', $key ) );
	});
	

	up_push( $variables, 'extra_description', ( Config::get('add_extra_description') == 'all_users' || ( Config::get('add_extra_description') == 'only_admin' && USER_MOD ) ) );
	up_push( $variables, 'max_file_size', Config::get('add_max_file_size') * 1048576);

	if( !empty( $variables['post_upload_video_url'] ) )
	{
		up_push( $variables, 'post_new_video', '<button class="change_input change_input_auto">' . __( 'add_file_new_input' ) . '</button>'  );
	}

	if( !empty( $variables['post_upload_url'] ) )
	{
		up_push( $variables, 'post_new_input', '<button class="change_input change_input_auto">' . __( 'add_file_new_input' ) . '</button>'  );
	}
	
	up_push( $variables, 'upload_subtype', $model );
	up_push( $variables, 'user_name', USER_LOGIN );
	up_push( $variables, 'text_on',	 ( $model == 'text'    ? '' : 'opt_hidden' ) );
	up_push( $variables, 'video_on', ( $model == 'video'   ? '' : 'opt_hidden' ) );
	up_push( $variables, 'mp4_on', 	 ( $model == 'mp4'     ? '' : 'opt_hidden' ) );
	up_push( $variables, 'swf_on', 	 ( $model == 'swf'     ? '' : 'opt_hidden' ) );
	up_push( $variables, 'image_on', ( $model == 'image'   ? '' : 'opt_hidden' ) );
	
	up_push( $variables, 'add_require_rules', __s( 'add_require_rules', '/post/' . seoLink( false, 'regulamin' ) ));
	
	/**
	* Categories list
	*/
	up_push( $variables, 'categories_list', Config::get('categories_option') == 1 ? Categories::categorySelectOptions( up_edit_field( 'file_category', false ) ) : false );
	
	if( $upload_type == 'demotywator' || $upload_type == 'mem' )
	{
		$upload_font_select = '';
		
		$font_color = str_replace('#', '', up_edit_field( 'font_color', Config::getArray( 'upload_' . $upload_type . '_text', 'font_color' ) ) );
		
		if ( Config::get('upload_demotywator_text', 'user_font') == 1)
		{
			$font = up_edit_field( 'font', Config::getArray( 'upload_' . $upload_type . '_text', 'font' ) );
			
			$upload_font_select =  Templates::getInc()->getTpl( '/upload/up_font_pick.html', array(
				'font_list' => fontList( $font )
			) );

			$url = Ips_Registry::get( 'Web_Fonts' )->urlWebFont( str_replace( '-', ' ', $font ) );
		}		

		up_push( $variables, 'upload_font_select', $upload_font_select );
		up_push( $variables, 'font_color', '#' . $font_color );
	}
	
	
	add_filter( 'init_js_variables', function( $array ) use ( $variables, $upload_type ) {
		return array_merge_one_deep( $array, [
			'ips_config' => array(
				'up_image_options' => Config::getArray( 'upload_margin', 'default' ),
				'up_video_options' => Config::getArray( 'upload_margin', 'video' ),
				'upload_action' => $upload_type
			)
		]);
	});

	$up_type = Ips_Registry::get('Upload_' .ucfirst( $upload_type ));
	
	if( $up_type != null )
	{
		add_filter( 'init_js_variables', function( $array ) use ( $variables, $upload_type, $up_type ) {
			
			$array['ips_config']['up_' . $upload_type . '_options'] = castTypes( $up_type->getOptions() );
			
			return $array;
		});
	}

	up_push( $variables, 'text_options', castTypes( Config::getArray( 'upload_text_options' ) ) );
	
	if( Config::getArray( 'upload_demotywator_type', 'text' ) || Config::getArray( 'upload_type', 'text' ))
	{
		$text = Ips_Registry::get('Upload_Text');
		up_push( $variables, 'text_options', castTypes( $text->getOptions() ) );
		
		if( !$variables['text_options']['user_font'] )
		{
			$variables['text_options']['fonts'] = array(
				$variables['text_options']['fonts'][ $variables['text_options']['font']['family'] ]
			);
		}
		
		add_filter( 'init_js_variables', function( $array ) use ( $variables ) {
			
			$array['ips_config']['up_text_options'] = $variables['text_options'];
			
			return $array;
		});
	}

	
	if( $upload_type == 'gallery' || $upload_type == 'ranking' )
	{
		add_filter( 'init_css_files', function( $array ){
			return add_static_file( $array, array(
				'libs/tinyeditor/tinyeditor.css'
			) );
		}, 10 );
		
		add_filter( 'init_js_files', function( $array ){
			return add_static_file( $array, array(
				'libs/tinyeditor/tiny.editor.packed.js'
			)  );
		}, 10 );
	
		up_push( $variables, 'add_description', __s( 'add_description', Config::getArray( $upload_type . '_options', 'description_length' ) )  );
		up_push( $variables, 'upload_images', str_replace( '"', "'", up_edit_field( 'upload_images' ) ) );
	}
	
	up_push( $variables, 'edit_id', get_input( 'edit_id' ) );
	
	if( $variables['edit_id'] )
	{
		add_filter( 'init_js_files', function( $array ){
			return add_static_file( $array, array(
				'js/up/upload_edit.js'
			) );
		}, 10 );
	}
	
	if( $upload_type == 'animation' )
	{
		$animation = '';
		
		if( $animated_gif_tmp = Session::get('animated_gif_tmp') )
		{
			if( file_exists( IPS_TMP_FILES . '/' . $animated_gif_tmp . '.gif' ) )
			{
				if( $routes['action'] == 'delete')
				{
					Session::clear( 'animated_gif_tmp' );
				}
				else
				{
					$animation = '<img src="/upload/tmp/' . $animated_gif_tmp . '.gif" />';
				}
			}
		}
		
		up_push( $variables, 'animated_gif', $animation );
		up_push( $variables, 'fps_array', range(1, 15) );
	}
	
	if( isset( $routes['upload_id'] ) )
	{
		up_push( $variables, 'upload_id', $routes['upload_id'] );
	}
	
	if( $upload_type == 'ranking' && $routes['action'] == 'add')
	{
		$count = PD::getInstance()->cnt("upload_ranking_files", array( 
			'upload_id' => (int)$routes['upload_id']
		));
		
		up_push( $variables, 'ranking_max_add_count_text', __s( 'add_ranking_limit', Config::range( 'ranking_options', 'items_range', 'max' ) - $count )  );
	}

	return $html . Templates::getInc()->getTpl( '/upload/upload_' . $upload_type . ( $routes['action'] == 'add' ? '_add' : '' ). '.html', $variables );

