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

	require_once( IPS_ADMIN_PATH .'/hooks-functions.php');
	/**
	* Wyświetlanie ustawień widgetów
	*/
	function displayWidgetsSettings()
	{
		$options_widgets = getOptionsFile()['options_widgets'];
		
		$data = '<div class="widget-settings">';
		
		foreach( $options_widgets as $option_name => $option_data )
		{
			$options_widgets[ $option_name ]['option_name'] = __( $option_name . '_title' );
		}
		
		array_sort_by_column( $options_widgets, 'option_name', SORT_ASC );

		foreach( $options_widgets as $option_name => $option_data )
		{
			/**
			* Widget niedostepny w wybranym szablonie
			*/
			if( isset( $option_data['opt_allowed_templates'] ) && !in_array( IPS_VERSION, $option_data['opt_allowed_templates'] ) )
			{
				continue;
			}
			
			if( isset( $option_data['opt_not_allowed_templates'] ) && $option_data['opt_not_allowed_templates'] == IPS_VERSION )
			{
				continue;
			}
			
			if( isset( $option_data['widget_depends'] ) )
			{
				
				$depends = optionDepends( $option_data['widget_depends'], '', '' );
				if( $depends !== false )
				{
					continue;
				}
			}
			
			$option_data['option_set_text'] = ' ';
			$option_data['option_type'] = 'text';
			$option_data['option_value'] = ' ';
			
			
			$data .= '
			<div id="' . $option_name . '" class="widget-tab content_tabs tabbed_area ' . ( Config::get( $option_name ) ? 'active' : '' ) . '">
				<div class="widget-caption">
					<h5>' . ( isset( $option_data['option_sprintf'] ) ? __s( $option_name . '_title', $option_data['option_sprintf'] ) : __( $option_name . '_title' ) ) . '</h5>
					<div class="widget-descript">'. __( $option_name . '_descript' ) . '</div>
					
				</div>
				<div class="widget-options">
				
				' . displayOptionField( $option_name, $option_data ) ;
				
				if( isset( $option_data['option_depends'] ))
				{
					$data .= '<span classs="has_option_depends"></span>';
				}
			
			$data .= '
				</div>
			</div>
			
			';
		}
		return $data . '</div>';
	}
	
	/** Set multiple empty if not checked any element */
	
	function setEmptyMultiple()
	{
		if( isset( $_POST['options_multiple'] ) )
		{
			foreach( $_POST['options_multiple'] as $option => $key )
			{
				if( !isset( $_POST[ $option ] ) )
				{
					if( $key == 'true' )
					{
						$_POST[ $option ] = array();
					}
					elseif( !isset( $_POST[ $option ][$key] ) )
					{
						$_POST[ $option ][$key] = array();
					}
				}
			}
		}
	}
	
	function adminOption( $option_name, $option_data = array() )
	{
		$default_data = array(
			'option_type' => 'radio',
			'option_lenght' => 1,
			'option_rows' => 10,
			'option_cols' => 15,
		);
		
		$option_data = array_merge( $default_data, $option_data );
			
		$orginal_name = $sub_option_name = $option_name;
		
		if( is_array( $option_name ) )
		{
			$sub_option_name = key( $option_name ) . '_' . current( $option_name ) . '';
			
			$option_value = ( !isset( $option_data['current_value'] ) ? Config::getArray( key( $option_name ), current( $option_name ) ) : $option_data['current_value'] );
	
			$option_name = key( $option_name ) . '[' . current( $option_name ) . ']';
			
		}
		elseif( strpos( $option_name, '[' ) !== false  )
		{
			$sub_option_name = str_replace( array( '][', '[',']'), '_', $option_name );
			
			$option_value = $option_data['current_value'];
		}
		else
		{
			$option_value =  !isset( $option_data['current_value'] ) ? Config::get( $option_name ) : $option_data['current_value'];
		}

		if( $option_value == false && isset( $option_data['default_value'] ) )
		{
			$option_value = $option_data['default_value'];
		}
		if( $option_data['option_type'] === 'value' )
		{
			return $option_value;
		}
		elseif( isset( $option_data['option_select_values'] ) )
		{
			$data = $select_ddslick = '';
			
			if( isset( $option_data['option_ddslick'] ) )
			{
				$select_ddslick = 'id="' . $option_name . '_ddslick" class="ddslick"';
				$data .= '<input type="hidden" name="' . $option_name . '" class="ddslick-value" />';
			}
			if( isset( $option_data['option_multiple'] ) )
			{
				if( is_array( $orginal_name ) )
				{
					$data .= '<input type="hidden" name="options_multiple[' . key( $orginal_name ) . ']" value="' . current( $orginal_name ) . '"/>';
				}
				else
				{
					$data .= '<input type="hidden" name="options_multiple_flat[' . $orginal_name . ']" value="' . $orginal_name . '"/>';
				}
			}
			
			$data .= '<select ' . $select_ddslick . ' name="' . $option_name  
			. ( isset( $option_data['option_multiple'] ) ? '[]' : '' ) . '" ' . ( isset( $option_data['option_multiple'] ) ? 'multiple' : '' ) . '>';

			foreach( $option_data['option_select_values'] as $key => $val )
			{
				if( isset( $option_data['option_value_as_key'] ) )
				{
					$key = $val;
				}
				
				$ddslick = '';
			
				if( isset( $option_data['option_ddslick'] ) )
				{
					$ddslick = 'data-imagesrc="' . $val['img'] . '"';
					
					if( isset( $val['description'] ) )
					{
						$ddslick .= 'data-description="' . $val['description'] . '"';
					}
					
					$val = '';
				}
			
				$data .= '<option ' . $ddslick . ' value="' . $key . '" ' . ( ( ( is_array( $option_value ) && in_array( $key, $option_value ) ) || $option_value == $key ) ? 'selected="selected"': '')  . '>' . $val . '</option>';
			}
			return $data .='</select>';
		}
		elseif( $option_data['option_type'] === 'range' )
		{
			return '<input class="number_ranger" name="' . $option_name . '" value="' . $option_value . '" data-min="' . $option_data['option_min'] . '" data-max="' . $option_data['option_max'] . '" />';
		}
		elseif( $option_data['option_type'] === 'range_slider' )
		{
			return '
			<div class="range_cnt">
				<div class="range_text min"></div>
				<div class="range_slider"></div>
				<div class="range_text max"></div>
				<input type="hidden" name="' . $option_name . '" value="' . $option_value . '" data-min="' . $option_data['option_min'] . '" data-max="' . $option_data['option_max'] . '" />
			</div>
			';
		}
		elseif( $option_data['option_type'] === 'input' )
		{
			return '<input type="text" name="' . $option_name . '" ' . ( $option_data['option_lenght'] ? 'size="' . $option_data['option_lenght'] . '"' : '' ) . ' value="' . $option_value . '" ' . ( isset( $option_data['max_length'] ) ? 'maxlength="' . $option_data['max_length'] . '"' : '' ) . ' ' . ( isset( $option_data['option_placeholder'] ) ? 'placeholder="' . $option_data['option_placeholder'] . '"' : '' ) . ' />';
		}
		elseif( $option_data['option_type'] === 'password' )
		{
			return '<input type="password" name="' . $option_name . '" ' . ( $option_data['option_lenght'] ? 'size="' . $option_data['option_lenght'] . '"' : '' ) . ' value="' . $option_value . '" ' . ( isset( $option_data['max_length'] ) ? 'maxlength="' . $option_data['max_length'] . '"' : '' ) . ' ' . ( isset( $option_data['option_placeholder'] ) ? 'placeholder="' . $option_data['option_placeholder'] . '"' : '' ) . ' />';
		}
		elseif( $option_data['option_type'] === 'textarea' )
		{
			return '<textarea name="' . $option_name . '" rows="' . $option_data['option_rows'] . '" cols="' . $option_data['option_cols'] . '" ' . ( isset( $option_data['max_length'] ) ? 'maxlength="' . $option_data['max_length'] . '"' : '' ) . '>' . $option_value . '</textarea>';
		}
		elseif( $option_data['option_type'] === 'text' )
		{
			return $option_data['option_value'];
		}
		else
		{
			if( !isset( $option_data['option_names'] ) || $option_data['option_names'] != 'yes_no' )
			{
				$option = array(
					'option_on'		=> __( 'option_turn_on' ),
					'option_off'	=> __( 'option_off' )
				);
				
				if( $option_value == 1 )
				{
					
					$option = array(
						'option_on'		=> __( 'option_on' ),
						'option_off'	=> __( 'option_turn_off' )
					);

				}
			}
			else
			{
				$option = array(
					'option_on'		=> __( 'yes' ),
					'option_off'	=> __( 'no' )
				);

			}
			
			return '
			<label class="label_radio" for="' . $sub_option_name . '_on">
				<input type="radio" name="' . $option_name . '" id="' . $sub_option_name . '_on" value="1" ' . ( $option_value == 1 ? 'checked="checked"' : '' ) . '/>' . $option['option_on'] . '
			</label>
			<label class="label_radio" for="' . $sub_option_name . '_off">
				<input type="radio" name="' . $option_name . '" id="' . $sub_option_name . '_off" value="0" ' . ( $option_value == 0 ? 'checked="checked"' : '' ) . ' />' . $option['option_off'] . '
			</label>';
		}
		
	}
	
	
	function displayOptionsFields( $options  )
	{
		foreach( $options as $key => $option )
		{
			$options[$key] = is_array( $option ) ? displayOptionField( $key, $option  ) : displayOptionField( $option  );
		}
		
		return implode( "\n", $options );
	}
	
	function getOptionTextHelper( $option_name, $string = '' )
	{
		$current = current( $option_name );
		return is_array( $current ) ? getOptionTextHelper( $current, $string ) : $string . '_' . $current;
	}
	
	function getOptionText( $option_name, $option_data )
	{
		if( is_array( $option_name ) )
		{
			$option_name = getOptionTextHelper( $option_name, key( $option_name ) );
		}
		
		if( strpos( $option_name, '[' ) !== false  )
		{
			$option_name = trim( str_replace( array( '][', '[', ']'), '_', $option_name ), '_' );
		}
		
		$text = '';
		
		if( isset( $option_data['option_sprintf'] ) )
		{
			$text = __s( $option_name . '_title', $option_data['option_sprintf'] );
		}
		elseif( isset( $option_data['option_set_text'] ) )
		{
			if( $option_data['option_set_text'] )
			{
				$text = __( $option_data['option_set_text'] );
			}
		}
		else
		{
			$text = __( $option_name . '_title' );
		}
		
		return $text;
	}
	function optionDepends( $depends, $display_name, $option_name_text )
	{
		if( isset( $depends ) )
		{
			
			if( is_array( $depends ) )
			{
				foreach( $depends as $option => $value )
				{
					if( is_array( $value ) )
					{
						foreach( $value as $o => $v )
						{
							if( Config::get( $option, $o ) != $v )
							{
								return;
							}
						}
					}
					elseif( Config::get( $option ) != $value )
					{
						return;
					}
				}
			}
			elseif( $depends === false )
			{
				return;
			}
			elseif( $depends === 'demo_disabled' )
			{
				if( strpos( ABS_URL, 'iprosoft.' ) !== false )
				{
					return '
					<div class="option-cnt">
						' . ( $display_name ? '
						<span>
							' . $option_name_text . '
						</span>
						' : '' ) . '
							'. __('in_demo_disabled') . '
					</div>';
				}
			}
			elseif( is_string( $depends ) && !Config::get( $depends ) )
			{
				return;
			}

		}
		return false;
	}
	/** Display option array */
	function displayOptionField( $option_name, $option_data = array(), $display_name = true, $level = 1  )
	{
		if( isset( $option_data['opt_display'] ) && !$option_data['opt_display'] )
		{
			return;
		}
		if( isset( $option_data['opt_not_allowed_templates'] ) && in_array( IPS_VERSION, $option_data['opt_not_allowed_templates'] ) )
		{
			return;
		}
		
		if( isset( $option_data['opt_allowed_templates'] ) && !in_array( IPS_VERSION, $option_data['opt_allowed_templates'] ) )
		{
			return ;
		}
	
		/*
		if( $level > 2 )
		{
			if( is_array( $option_name ) )
			{
				$option_name = key( $option_name ) . '_' . current( $option_name );
			}
		} */
			
		if( is_string( $option_name ) && strpos( $option_name, ',' ) !== false )
		{
			$options = explode( ',', $option_name );
			$data = '';

			foreach( $options as $option )
			{
				$data .= displayOptionField( $option, $option_data, $display_name );
			}
			return $data;
		}

		/*** SET option text */
		$option_name_text = getOptionText( $option_name, $option_data );
		
		/** Check if option depends on another */ 
		if( isset( $option_data['option_depends'] ) )
		{
			$depends = optionDepends( $option_data['option_depends'], $display_name, $option_name_text );
			if( $depends !== false )
			{
				return $depends;
			}
		}
		
		/** Retrun only HTML */
		if(
			( is_string( $option_name ) && strpos( $option_name, 'opt_html' ) !== false ) 
				||
			( is_array( $option_name ) && strpos( get_current( $option_name ), 'opt_html' ) !== false )
		)
		{
			return newBlock( $option_data ) . $option_data['content'];
		}
		
		$data = '';
		$option_is_array = '';
		if( isset( $option_data['option_is_array'] )  )
		{
			$option_value = array();
			$display_name = false;
			
			if( !isset( $option_data['option_display_name'] ) || $option_data['option_display_name'] )
			{
				$option_data['option_new_block'] = $option_name_text;
				$option_data['option_css'] = 'width_full';
			}
			
			foreach( $option_data['option_is_array'] as $key_option => $option )
			{
				if( isset( $option['option_is_array'] ) )
				{
					$option_data['option_new_block'] = __( str_replace( '_title', '_' . $key_option . '_title', $option_name_text ) );
					
					$option_is_array_value = Config::getArray( $option_name, $key_option );
				
					foreach( $option['option_is_array'] as $key_suboption => $suboption )
					{
						$suboption = ( is_array( $suboption ) ? $suboption : array() );
						
						if( !isset( $suboption['current_value'] ) )
						{
							$suboption['current_value'] = $option_is_array_value[$key_suboption];
						}
						
						$option_is_array .= displayOptionField( $option_name . '[' . $key_option . '][' . $key_suboption . ']' , $suboption, true, $level + 1 );
					}
				}
				else
				{
					$option_is_array .= displayOptionField( array( 
						$option_name => ( is_array( $option ) ? $key_option : $option )
					), ( is_array( $option ) ? $option : array() ), true, $level + 1 );
				}
			}
			
		}
		elseif( is_array( $option_name ) )
		{
			$option_value = false;
			$data = adminOption( $option_name, $option_data );
			
			$option_name = key( $option_name ) . '_' . current( $option_name );
		}
		else
		{
			$option_value = (int)Config::get( $option_name );
			$data = adminOption( $option_name, $option_data );
		}
		
		if( isset( $option_data['option_append_to_input'] ) )
		{
			$data = '<div class="input_with_button">' . $data . $option_data['option_append_to_input'] . '</div>';
		}

		
		$opt_suboptions = $option_is_array;
		if( $option_value === 1 && isset( $option_data['opt_suboptions'] ) ) 
		{
			$option_data['option_css'] = 'width_full';
			$opt_suboptions = '<div class="widget-tab-sub-options">';
				
				foreach( $option_data['opt_suboptions'] as $option_name => $widget_option_data )
				{
					if( isset( $widget_option_data['opt_allowed_templates'] ) && !in_array( IPS_VERSION, $widget_option_data['opt_allowed_templates'] ) )
					{
						continue;
					}
					$opt_suboptions .= displayOptionField( $option_name, $widget_option_data );
				}
				
			$opt_suboptions .= '</div>';
		}
		
		$option_new_block = newBlock( $option_data );
		
		$option_return = '<div class="option-cnt ' . ( isset( $option_data['option_css'] ) ? $option_data['option_css'] : '' ) . '">';

		if( $display_name )
		{
			$option_return .= '
			<span>
				' . ( isset( $option_data['option_sprintf'] ) ? __s( $option_name . '_title', $option_data['option_sprintf'] ) : $option_name_text ) . '
			</span>';
		}
		
		$option_return = $option_new_block . ( !empty( $data ) ? $option_return . '<div class="option-inputs">' .  $data . '</div></div>' : '' ) . $opt_suboptions;
		
		return $option_return;
	}
	
	function newBlock( $option_data )
	{
		$option_new_block = '';
		
		if( isset( $option_data['option_new_block'] ) && $option_data['option_new_block'] )
		{
			if( !isset( $option_data['first_option'] ) )
			{
				$option_new_block .= '</div><div class="content_tabs tabbed_area">';
			}
			
			if( is_string( $option_data['option_new_block'] ) )
			{
				$option_new_block .= '<div class="caption_small"><span class="caption">' . $option_data['option_new_block'] . '</span></div>';
			}
		}
		
		return $option_new_block;
	}
	
	
	
	
	function displayArrayOptions( $options, $css = 'content_tabs', $info_block = false )
	{
		if( !is_string( $css ) )
		{
			$css = 'content_tabs';
		}
		
		$first = key($options);
		
		if( !is_array( $options[ $first ] ) )
		{
			$options = array_merge( array(
				$options[ $first ] => array(
					'first_option' => true
				)
			), $options );
			unset( $options[ $first ] );
		}
		else
		{
			$options[ $first ]['first_option'] = true;
		}

		foreach( $options as $key => $option )
		{
			$options[ $key ] = is_array( $option ) ? displayOptionField( $key, $option ) : displayOptionField( $option );
		}
		return '<div class="' .$css . ' tabbed_area">' . implode( "\n", $options ) . '</div>' . ( $info_block ? 
		'<div class="div-info-message"><p>' . __( $info_block ) . '</p></div>'
		: '' );
	}
	
	function activMenuItem( $menu_id, $item_id, $value )
	{
		PD::getInstance()->update('menus', array( 'item_activ' => $value ), array( 'item_id' => $item_id, 'menu_id' => $menu_id ));
	}
	/* 
	Zmiana szablonu całej strony 
	*/
	
function languagesList()
{
	return array( 'af_ZA', 'ar_AR', 'az_AZ', 'be_BY', 'bg_BG', 'bn_IN', 'bs_BA', 'ca_ES', 'cs_CZ', 'cx_PH', 'cy_GB', 'da_DK', 'de_DE', 'el_GR', 'en_GB', 'en_PI', 'en_UD', 'en_US', 'eo_EO', 'es_ES', 'es_LA', 'et_EE', 'eu_ES', 'fa_IR', 'fb_LT', 'fi_FI', 'fo_FO', 'fr_CA', 'fr_FR', 'fy_NL', 'ga_IE', 'gl_ES', 'gn_PY', 'he_IL', 'hi_IN', 'hr_HR', 'hu_HU', 'hy_AM', 'id_ID', 'is_IS', 'it_IT', 'ja_JP', 'jv_ID', 'ka_GE', 'km_KH', 'kn_IN', 'ko_KR', 'ku_TR', 'la_VA', 'lt_LT', 'lv_LV', 'mk_MK', 'ml_IN', 'ms_MY', 'nb_NO', 'ne_NP', 'nl_NL', 'nn_NO', 'pa_IN', 'pl_PL', 'ps_AF', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'si_LK', 'sk_SK', 'sl_SI', 'sq_AL', 'sr_RS', 'sv_SE', 'sw_KE', 'ta_IN', 'te_IN', 'th_TH', 'tl_PH', 'tr_TR', 'uk_UA', 'ur_PK', 'vi_VN', 'zh_CN', 'zh_HK', 'zh_TW' );
}
function changeIpsVersion( $ips_version, $install_precess = false )
{

	if( Config::getArray('gallery_options', 'load' ) == 'pinit' )
	{
		Config::update( 'gallery_options', array( 
			'load' => 'simple'
		) );
	}
	
	$image_width =  600;
	
	switch( $ips_version )
	{
		case 'pinestic':
			$image_width =  900;
			Config::update( 'pagin_css', 'infinity' );
			Config::update( 'video_player', array(
				'type' => 'standard'
			));
			Config::update( 'upload_video_options', array(
				'add_video_resolution' => '4:3'
			));
			Config::update( 'files_on_page', 20 );
			
			Config::update( 'widget_best_files,online_stats,widget_category_panel,widget_fan_box,rss_off,widget_float_box,widget_go_to,widget_search_bar,widget_navigation_bottom_box,widget_navigation_on_page_box,widget_navigation_on_page,widget_top_comments,widget_top_files_right,widget_see_more,social,widget_sort_box,widget_history_users_activity,module_history,widget_top_files_right', 0 );
			
			Config::update( 'gallery_options', array(
				'local' => 1,
				'gallery_images_count' => 0,
				'load' => 'pinit'
			) );

		break;
		case 'bebzol':
		case 'vines':

			
			Config::update( 'widget_top_files_right', 0 );
			Config::update( 'vote_file_menu', 7 );
			Config::update( 'gallery_options', array(
				'load' => 'pinit'
			) );
			
			PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'random', 'up', 'top', 'waiting' ), 1, 0) WHERE menu_id = 'main_menu'");
			
			PD::getInstance()->query("
			UPDATE " . db_prefix( 'menus' ) . " SET
				item_position = CASE
					WHEN item_id = 'mem' THEN '1'
					WHEN item_id = 'up' THEN '2'
					WHEN item_id = 'share' THEN '3'
					WHEN item_id = 'google' THEN '4'
					WHEN item_id = 'nk' THEN '5'
					WHEN item_id = 'random' THEN '6'
					WHEN item_id = 'waiting' THEN '7'
					WHEN item_id = 'top' THEN '8'
					WHEN item_id = 'categories' THEN '9'
					WHEN item_id = 'main' THEN '10'
			END
			");
			
		break;
		case 'kwejk':
		case 'demotywator':

			PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'main', 'share', 'random', 'up', 'top', 'waiting' ), 1, 0) WHERE menu_id = 'main_menu'");
			PD::getInstance()->query("
			UPDATE " . db_prefix( 'menus' ) . " SET
				item_position = CASE
					WHEN item_id = 'mem' THEN '1'
					WHEN item_id = 'main' THEN '2'
					WHEN item_id = 'share' THEN '3'
					WHEN item_id = 'google' THEN '4'
					WHEN item_id = 'nk' THEN '5'
					WHEN item_id = 'random' THEN '6'
					WHEN item_id = 'up' THEN '7'
					WHEN item_id = 'top' THEN '8'
					WHEN item_id = 'waiting' THEN '9'
					WHEN item_id = 'categories' THEN '10'
			END
			");
		break;
		case 'gag':
			
			Config::update( 'widget_top_files_right', 0 );
			Config::update( 'widget_see_more_options', array(
				'layout' => 'one'
			));
			Config::update( 'vote_file_menu', 6 );
			saveLayout( 'one' );
			if( Config::getArray('template_settings', 'gag_sub_menu' ) )
			{
				PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'main', 'top', 'up', 'waiting' ), 1, 0) WHERE menu_id = 'gag_sub_menu'");
				PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'share' ), 1, 0) WHERE menu_id = 'main_menu'");
			}
			else
			{
				PD::getInstance()->query("UPDATE " . db_prefix( 'menus' ) . " SET item_activ=IF(item_id IN( 'main', 'share', 'up', 'top', 'waiting' ), 1, 0) WHERE menu_id = 'main_menu'");
			}
			
			PD::getInstance()->query("
			UPDATE " . db_prefix( 'menus' ) . " SET
				item_position = CASE
					WHEN item_id = 'mem' THEN '1'
					WHEN item_id = 'top' THEN '2'
					WHEN item_id = 'main' THEN '3'
					WHEN item_id = 'share' THEN '4'
					WHEN item_id = 'google' THEN '5'
					WHEN item_id = 'nk' THEN '6'
					WHEN item_id = 'waiting' THEN '7'
					WHEN item_id = 'up' THEN '8'
					WHEN item_id = 'categories' THEN '9'
					WHEN item_id = 'random' THEN '10'
			END
			WHERE menu_id = 'main_menu'
			");
		break;
		default:
			ips_message( array(
				'alert' =>  __('templates_select_error')
			) );
			return;
		break;
	}
	

	realoadDefaultHooks();
		
	updateHtaccessRules( $ips_version );
	
	activMenuItems();
	
	updateMenu();
	updateMenu( false, 'gag_sub_menu' );
	updateMenu( false, 'pinit_menu' );
	updateMenu( false, 'footer_menu' );
	
	jQueryConfig( $ips_version );
	
	Config::update( 'file_max_width', $image_width );
	
	@unlink( CACHE_PATH . '/cache.ads.php' );
	
	if( !$install_precess )
	{
		try{							
			File::replaceInFile( ABS_PATH . '/config.php', array("define ('IPS_VERSION', '" . IPS_VERSION . "')"), array( "define ('IPS_VERSION', '" . $ips_version . "')" ) );
			ips_message( array(
				'info' =>  __('template_has_been_changed')
			) );
			
		} catch ( Exception $e ) {
			ips_message( array(
				'info' =>  __('config_file_edit_error')
			) );
		}
	}
	
}
function updateHtaccessRules( $ips_version )
{
	try{
		
		$contents = explode( "\n", File::read( ABS_PATH . '/.htaccess' ) );
		foreach( $contents as $line_num => $line )
		{
			if( strpos( $line, '#Redirect for PINIT') !== false )
			{
				if( $ips_version == 'pinestic' )
				{
					$contents[ $line_num + 1 ] = str_replace( '#', '', $contents[ $line_num + 1 ] );
					$contents[ $line_num + 2 ] = str_replace( '#', '', $contents[ $line_num + 2 ] );
				}
				elseif( strpos( $contents[ $line_num + 1 ], '#') == false )
				{
					$contents[ $line_num + 1 ] = '#' . $contents[ $line_num + 1 ];
					$contents[ $line_num + 2 ] = '#' . $contents[ $line_num + 2 ];
				}
				break;
			}
		}
		
		File::put( ABS_PATH . '/.htaccess', implode( "\n", $contents ) );
		
		return true;
	
	} catch ( Exception $e ) {
		return false;
	}
		
	
}

function resetMenu( $menu_id )
{
	PD::getInstance()->delete('menus', array(
		'menu_id' => $menu_id
	));
	
	Config::update( 'cache_data_menus', array(
		$menu_id => null
	));
	
	switch( $menu_id )
	{
		case 'main_menu':
			
			menuCreateItem( array_merge( menuCreateHelper( 'main_menu', 'mem' ), array(
				'item_anchor' => 'menu_generator_menu',
				'item_title' => 'menu_generator',
				'item_activ' => 0,
				'item_position' => 1,
			) ) );
			
			menuCreateItem( array_merge( menuCreateHelper( 'main_menu', 'main' ), array(
				'item_url' => '/',
			) ) );//menu_main
			
			menuCreateItem( menuCreateHelper( 'main_menu', 'top' ) ); //menu_top
			
			menuCreateItem( menuCreateHelper( 'main_menu', 'share' ) );//menu_shared
			
			menuCreateItem( menuCreateHelper( 'main_menu', 'google' ) );//menu_google
			
			menuCreateItem( menuCreateHelper( 'main_menu', 'nk' ) );//menu_nk
			
			menuCreateItem( menuCreateHelper( 'main_menu', 'random' ) );//menu_random
			
			menuCreateItem( menuCreateHelper( 'main_menu', 'waiting' ) );//menu_waiting
			
			menuCreateItem( menuCreateHelper( 'main_menu', 'up' ) );//menu_up
			
			menuCreateItem( array_merge( menuCreateHelper( 'main_menu', 'categories' ), array(
				'item_class' => 'categories-menu',
				'item_url' => '#',
			) ) );//menu_generator

		break;
		
		case 'gag_sub_menu':
		
			menuCreateItem( array_merge( menuCreateHelper( 'gag_sub_menu', 'main' ), array(
				'item_url' => '/',
				'item_position' => 1,
			) ) );//menu_main
			menuCreateItem( menuCreateHelper( 'gag_sub_menu', 'top' ) );//menu_top
			menuCreateItem( menuCreateHelper( 'gag_sub_menu', 'waiting' ) );//menu_waiting
			menuCreateItem( array_merge( menuCreateHelper( 'gag_sub_menu', 'up' ), array(
				'item_class' => 'add-file',
			) ) );//menu_up


		break;
		
		case 'pinit_menu':
			
			menuCreateItem( array_merge( menuCreateHelper( 'pinit_menu', 'pins' ), array(
				'item_title' => 'pinit_top_pins',
				'item_position' => 1,
			) ) );
			
			menuCreateItem( array_merge( menuCreateHelper( 'pinit_menu', 'boards' ), array(
				'item_title' => 'pinit_top_boards',
			) ) );
			
			menuCreateItem( array_merge( menuCreateHelper( 'pinit_menu', 'users' ), array(
				'item_title' => 'pinit_top_users',
			) ) );
			
			menuCreateItem( array_merge( menuCreateHelper( 'pinit_menu', 'following' ), array(
				'item_title' => 'pinit_top_following',
			) ) );

				
		break;
		
		case 'footer_menu':	
			
			menuCreateItem( array_merge( menuCreateHelper( 'footer_menu', 'rules' ), array(
				'item_anchor' => 'common_rules',
				'item_title' => 'common_rules',
				'item_position' => 1,
				'item_url' => 'post-url-1',
			) ) );
			
			menuCreateItem( array_merge( menuCreateHelper( 'footer_menu', 'ads' ), array(
				'item_anchor' => 'bottom_ads',
				'item_title' => 'bottom_ads',
				'item_url' => 'post-url-2',
			) ) );

			menuCreateItem( array_merge( menuCreateHelper( 'footer_menu', 'bottom_contact' ), array(
				'item_anchor' => 'bottom_contact',
				'item_title' => 'bottom_contact',
				'item_url' => 'contact.html',
			) ) );
			
			menuCreateItem( array_merge( menuCreateHelper( 'footer_menu', 'bottom_news' ), array(
				'item_anchor' => 'bottom_news',
				'item_title' => 'bottom_news',
				'item_url' => '/news/',
			) ) );

		break;
	}
	
	activMenuItems( $menu_id );
	
	
}
function menuCreateItem( $item )
{
	if( !has_value( 'menu_id', $item ) || !has_value( 'item_anchor', $item ) )
	{
		return !ips_log('Menu create error');
	}
	
	$max = PD::getInstance()->select('menus', array( 
		'menu_id' => $item['menu_id']
	), 1, 'MAX(item_position) as max' );
	
	$item['item_position'] = (int)$max['max'] + 1;
	
	if( !has_value( 'item_id', $item ) )
	{
		$item['item_id'] = 'user_item_' . rand();
	}
	
	$item['item_title'] = ( !has_value( 'item_title', $item ) ? '' : $item['item_title'] );
	$item['item_class'] = ( !has_value( 'item_class', $item ) ? '' : $item['item_class'] );
	$item['item_url'] = ( !has_value( 'item_url', $item ) ? '' : $item['item_url'] );
	$item['item_target'] = ( !has_value( 'item_target', $item ) ? '' : $item['item_target'] );
	$item['item_activ'] = ( !has_value( 'item_activ', $item ) ? 1 : $item['item_activ'] );

	return PD::getInstance()->insert('menus', $item );
}
function menuCreateHelper( $menu_id, $item_id )
{
	return array(
		'menu_id' => $menu_id,
		'item_id' => $item_id,
		'item_anchor' => 'menu_' . $item_id . '_menu',
		'item_title' => 'menu_' . $item_id ,
		'item_url' => '/' . $item_id . '/',
	);
}
function activMenuItems( $menu_id = false )
{
	activMenuItem( 'main_menu', 'mem', Config::get('mem_generator') );
	activMenuItem( 'main_menu', 'categories', Config::get('categories_option') );
	activMenuItem( 'main_menu', 'google', ( Config::get( 'social_plugins', 'google_page' ) == 1 || Config::get( 'social_plugins', 'google' ) == 1 ) );
	activMenuItem( 'main_menu', 'nk', ( Config::get( 'social_plugins', 'nk' ) == 1 || Config::get( 'social_plugins', 'nk_page' ) == 1 ) );
	
	if( IPS_VERSION == 'gag' )
	{
		if( $menu_id == 'gag_sub_menu' )
		{
		
		}
	}
}
/**
* Aktualizacja menu
*/
function updateMenu( $menu_items = false, $menu_id = 'main_menu' )
{
	
	if( !$menu_items )
	{
		$menu_items = PD::getInstance()->select('menus', array(
			'menu_id' => $menu_id,
			'item_activ' => 1
		), null, '*', array( 'item_position' => 'ASC' ) );
	}
	if( empty( $menu_items ) )
	{
		return false;
	}
	require_once( dirname(__FILE__) . '/libs/class.MenuBuilder.php');
	require_once( dirname(__FILE__) . '/libs/class.MenuBuilderTidy.php');
			
	$menu = MenuBuilder::factory();
	
	$menu->attrs = array(  
		'id'    => 'menu-navigation',  
		'class' => ( $menu_id == 'gag_sub_menu' || $menu_id == 'pinit_menu' ? 'responsive-menu content-submenu-items' : 'right-float' ),  
	); 
	
	$menu_to_save = array();
	
	global ${IPS_LNG};
	
	/**Pierwszy element to opis menu **/
	
	if( !isset( $menu_items[0]['item_title'] ) || empty( $menu_items[0]['item_title'] ) )
	{
		unset( $menu_items[0] );
	}

	foreach( $menu_items as $key => $menu_item )
	{
		if( $menu_item['item_activ'] == 1 )
		{
			$item_url = str_replace( '{ABS_URL}', ABS_URL, $menu_item['item_url'] );

			if( strpos( $item_url, 'post-url' ) !== false )
			{
				$item_url = '/pages/' . Page::cache( $item_url, 'post_permalink' );
			}
		
			$item_anchor	= isset( ${IPS_LNG}[ $menu_item['item_anchor'] ] )	? '{lang=' . $menu_item['item_anchor'] . '}' : htmlspecialchars_decode( $menu_item['item_anchor'] ) ;
			$item_title		= isset( ${IPS_LNG}[ $menu_item['item_title'] ] )	? '{lang=' . $menu_item['item_title'] . '}'	 : htmlspecialchars_decode( $menu_item['item_title'] );
			
			if( $menu_item['item_id'] == 'categories' && Config::get('categories_option') )
			{
				if( IPS_VERSION !== 'pinestic' )
				{				
					$categories = Categories::getCategories();
					
					$submenu = MenuBuilder::factory();
					
					foreach( $categories as $r )
					{
						$submenu->add( strip_tags( $r['category_name'] ), null, ABS_URL . 'category/' . $r['id_category'] . ',' . $r['category_link'], null );
					}
					
					$submenu->attrs = array  
					( 
						'class' => 'categories-sub fancy-menu responsive-slide',  
					);
					$menu->add( $item_anchor, $item_title, $item_url, $menu_item['item_class'], $menu_item['item_target'], $submenu );
				}
			}
			else
			{
				$menu->add( '<i class="i-m"></i>' . $item_anchor, $item_title, $item_url, $menu_item['item_class'], $menu_item['item_target'] );
			}
		}
		
		$menu_item['item_position'] = $key + 1;
		
		PD::getInstance()->update( 'menus', $menu_item, array( 
			'item_id' => $menu_item['item_id'], 
			'menu_id' => $menu_id
		));

	}
	
	
	$html = new MenuBuilderTidy( $menu, ( $menu_id == 'gag_sub_menu' ? false : true ) );
	$content = $html->render();

	
	/* if( IPS_VERSION == 'pinestic' && Config::get('categories_option') )
	{
		$submenu = MenuBuilder::factory();
		$categories = Categories::getCategories();
					
		$submenu = MenuBuilder::factory();
		
		foreach( $categories as $r )
		{
			$submenu->add( strip_tags( $r['category_name'] ), null, ABS_URL . 'category/' . $r['id_category'] . ','.$r['category_link'], null );
		}
		
		$submenu->attrs = array  
		( 
			'class' => 'categories-sub',  
		); 
		$html = new MenuBuilderTidy( $submenu, false );
		$content .= $html->render();
	} */
	
	
	Config::update( 'cache_data_menus', array(
		$menu_id . '_' . strtolower( IPS_LNG ) => $content
	) );
	
	return $content;
}
function menuItemsForm( $menu_array, $menu_id, $name )
{
	echo '
	<script>
		$(function() {
			$( "#ips-menu-edit-'.$menu_id.'" ).sortable();
		});
	</script>
	<form id="form-menu-edit-'.$menu_id.'" class="form-menu-edit" action="admin-save.php" enctype="multipart/form-data" method="post">	
		
	<ul id="ips-menu-edit-'.$menu_id.'" class="ips-menu-edit">
	<li class="ui-state-default ips-menu-name"> '. $name . '</li>
	';
	foreach( $menu_array as $key => $item )
	{
		
		echo '
		
		<li class="ui-state-default" id="menu-' . $item['item_id'] . '">
			
			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
			<span class="item-title">' . __( htmlspecialchars( $item['item_anchor'] ) ) . '</span>
			<a href="#" title="'.__('menu_item_edit').'" id="edit-' . $item['item_id'] . '" class="item-edit">'.__('menu_item_edit').'</a>
			
			<div id="settings-menu-' . $item['item_id'] . '" class="menu-item-settings">
				<p class="field-url description description-wide">
					
					<label for="edit-menu-item-url-' . $item['item_id'] . '">
						'.__('menu_item_url').'<br>
						<input type="text" id="menu-item-url" value="' . $item['item_url'] . '" name="menu-' . $item['item_id'] . '-url" />
					</label>
					';
					if( strpos( $item['item_id'], 'user_' ) !== false || $menu_id == 'footer_menu' )
					{	
						echo '
						<label for="item_anchor">
							' . __('menu_item_link') . ' <br>
							<input type="text" name="menu-' . $item['item_id'] . '-anchor" value="' . htmlspecialchars( $item['item_anchor'] ) . '" id="menu-' . $item['item_id'] . '-anchor">
						</label>
						<label for="item_title">
							' . __('menu_item_title') . '<br>
							<input type="text" name="menu-' . $item['item_id'] . '-title" value="' . htmlspecialchars( $item['item_anchor'] ) . '" id="menu-' . $item['item_id'] . '-title">
						</label>
						';
					
					}
					else
					{
						echo '
						<input type="hidden" id="item-title-menu-' . $item['item_id'] . '" value="' . $item['item_title'] . '" name="menu-' . $item['item_id'] . '-title" />
						<input type="hidden" id="item-anchor-menu-' . $item['item_id'] . '" value="' . $item['item_anchor'] . '" name="menu-' . $item['item_id'] . '-anchor" />
						';
					}
					
					echo '
					<label for="edit-menu-item-class-' . $item['item_id'] . '">
						'.__('menu_item_css').'<br>
						<input type="text" id="menu-item-class" value="' . $item['item_class'] . '" name="menu-' . $item['item_id'] . '-class" />
					</label>
					
					<label for="edit-menu-item-target-' . $item['item_id'] . '">
						'.__('menu_item_target').'<br>
						
						<select id="menu-item-target" name="menu-' . $item['item_id'] . '-target" style="width: 175px; padding: 5px;">
							<option value="" '.( $item['item_target'] == 'self' ? ' selected="selected" ' : '').'>'.__('menu_item_target_self').'</option>
							<option value="_blank" '.( $item['item_target'] == '_blank' ? ' selected="selected" ' : '').'>'.__('menu_item_target_blank').'</option>
						</select>
					</label>
					
				</p>

				<input type="hidden" id="item-activ-menu-' . $item['item_id'] . '" value="' . ( $item['item_activ'] ? '1' : '0' ) . '" name="menu-' . $item['item_id'] . '-activ" />
				
				<a href="#" title="'.__('menu_item_disable').'" id="edit-' . $item['item_id'] . '" class="item-edit-activ">' . 
				( $item['item_activ'] ? '<span style="color:red">'.__('turn_off').'</span>' : '<span style="color:green">'.__('menu_item_on').'</span>' )
				. '</a>';
				
				if( strpos( $item['item_id'], 'user_' ) !== false)
				{
					echo '
					<a href="#" title="' . __('common_delete') . '" id="edit-' . $item['item_id'] . '-delete" class="item-edit-delete">
						<span style="color:red">' . __('common_delete') . '</span>
					</a>
					';
				}
				
				echo '
			</div>
		</li>';
	}
	echo '
				</ul>
			<input type="submit" class="button" value="' . __('save') . '" />
			<input data-id="' . $menu_id . '" type="button" class="reset-menu button" value="' . __('reset_menu') . '" />
			</form>';
}
/**
* Alias
*/
function deletecategory( $ids )
{
	Ips_Registry::get( 'Categories_Admin' )->delete( $ids, false);
}
/**
* Alias
*/
function deletecategoryall( $ids )
{
	Ips_Registry::get( 'Categories_Admin' )->delete( $ids, true );
}
function saveLayout( $layout )
{
	if( $layout == 'one' || $layout == 'two' || $layout == 'three' )
	{
		Config::deleteCache();
		Config::update( 'template_settings', array(
			'layout' => $layout
		) );
	}
	
	return ips_message( array(
		'normal' => 'templates_layout_changed'
	) );
}
function saveThumbsCount( $count )
{
	if( in_array( $count, array( 3, 4, 5 ) ) )
	{
		Config::update( 'template_settings', array(
			'thumb_count' => 'i-' . $count 
		) );
	}
	
	return ips_message( array(
		'normal' => 'templates_layout_changed'
	) );
}
/**
* Usuwanie obrazków, która jakimś cudem zostały w folderze upload/
*/
function clearNotUsedFiles()
{
	return true;
	
	
	$in_database = PD::getInstance()->select( IPS__FILES, null, 0, "SUBSTRING(duze_img, 1, length(duze_img) - 4) as duze_img, SUBSTRING(img_url, 1, length(img_url) - 4) as img_url,SUBSTRING(mini_url, 1, length(mini_url) - 4) as mini_url");
	
	if( empty( $in_database ) )
	{
		return true;
	}

	$merged_database = array();
	foreach( $in_database as $images )
	{
		foreach( $images as $image )
		{
			$merged_database[$image] = $image;
		}
	}
	
	unset( $in_database );
	
	global $filesSize;
	
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( IMG_PATH ), RecursiveIteratorIterator::SELF_FIRST );
	
	$files = array();
	
	foreach ( $iterator as $file )
	{
		if ( $file->isFile() && $file->getFilename() != '.htaccess' )
		{
			$files[ substr( basename( $file->getFilename() ), 0, -4 ) ] = $file->getFilename();
		}
	}
	
	unset( $iterator );

	foreach( $files as $file_key => $file )
	{
		if( isset( $merged_database[ $file_key ] ) )
		{
			unset( $files[ $file_key ]);
		}
	}
	unset( $merged_database );
	
	foreach( $files as $file_key => $file )
	{
		$filesSize += filesize( IMG_PATH . '/' . $file );
		unlink( IMG_PATH . '/' . $file );
	}
}

function checkSize( $file )
{
	global $filesSize;
	
	if( is_dir( $file ) )
	{
		array_map( "checkSize", glob( $file . '/*', GLOB_NOSORT ) );
	}
	else
	{
		$filesSize += filesize( $file );
		
		if ( !is_writable( $file ) )
		{
			chmod( $file, 0755 );
		}
		unlink( $file );
	}
}

function clearCache( $cache, $match_filename = false )
{
	
	$filesSize = 0;

	switch( $cache )
	{
		case 'all':
			
			$info = clearCache( 'tpl' ) . '<br />';
			$filesSize += $filesSize;
			$info .= clearCache( 'jscss' ) . '<br />';
			$filesSize += $filesSize;
			$info .= clearCache( 'ipscache' ) . '<br />';
			$filesSize += $filesSize;
			$info .= clearCache( 'img' ) . '<br />';
			$filesSize += $filesSize;
			$info .= clearCache( 'mysql' ) . '<br />';

		break;
		case 'tpl':
			$info = __('system_cache_info_templates');
			
			File::deleteFiles( CACHE_PATH . '/tpl_cache' );

		break;
		
		case 'mysql':
			
			$rows = PD::getInstance()->delete( 'cached', 'id > 0' );
			
			$users_data = PD::getInstance()->cnt( 'users_data', "setting_key LIKE '%_cache'" );
			
			PD::getInstance()->update( 'users_data', array(
				'setting_value' => serialize( array() ),
			), "setting_key LIKE '%_cache'", false );
			
			$system_settings = PD::getInstance()->cnt( 'system_settings', "settings_name LIKE 'cache_data_%'" );
			
			PD::getInstance()->update( 'system_settings', array(
				'settings_value' => serialize( array() ),
			), "settings_name LIKE 'cache_data_%'", false);
			
			return __s( 'system_cache_deleted_mysql', ( $rows + $users_data + $system_settings ) );
		break;
		
		case 'jscss':
			$info = __('system_cache_info_jscss');
			
			File::deleteFiles( CACHE_PATH . '/minify' );
			
		break;
		
		case 'ipscache':
			$info = __('system_cache_info_files');

			File::deleteFiles( IPS_CACHE_PATH, true, true );
			File::deleteFiles( IPS_CACHE_IMG_PATH, true, true );
			
			if( file_exists( ABS_PATH . '/tmp/' ) )
			{
				/** For servers with tmp/directory */
				File::deleteFiles( ABS_PATH . '/tmp/' );
			}
			
			clearNotUsedFiles();
		break;
		
		case 'img':
			$info = __('system_cache_info_tmp');
			
			File::deleteFiles( ABS_PATH . '/upload/import/', true, true );
			
			if( file_exists( ABS_PATH . '/upload/tmp/' ) )
			{
				File::deleteFiles( ABS_PATH . '/upload/tmp/' );
			}
			
			removeEmptySubfolders( ABS_PATH . '/upload/upload_gallery/' );
		break;
		
		default:
			return 'Cache clear ERROR';
		break;
	}
	
	PD::getInstance()->delete( 'cached', array(
		'cache_type' => 1,
		'cache_stored' => array( time() - 60, '<' )
	));

	global $filesSize;
	
	return $info . __s( 'system_cache_deleted', round( convertBytes( $filesSize ) / 1048576, 4 ) );
}


function checkImagesAnimations()
{
	
	$rows = PD::getInstance()->select( IPS__FILES, array( 
		'upload_type' => 'animation'
	));
	
	$count = 0;
	
	foreach( $rows as $id => $row )
	{
		$img_path = ips_img_path( $row, 'gif' );
		
		if( !file_exists( $img_path ) )
		{
			$img = imagecreatefromgif( IMG_PATH_BACKUP . '/' . $row['upload_image'] );
			
			if( $img == false )
			{
				$img = Gd::as_resource( IMG_PATH_BACKUP . '/' . $row['upload_image'] ); 
			}
			
			if( $img )
			{
				imagegif( $img, $img_path, Config::get('images_compress', 'jpg') );
			}
			
			unset( $img );
			
			$count++;
		}
	}
	return __s( 'system_cache_fixed', $count );
}



function getSystemFonts()
{
	$installed = Config::get( 'web_fonts_config', 'installed' );
	
	$options = array();
	
	if( empty( $installed ) )
	{
		return $options;
	}
	
	$fonts = new Admin_Web_Fonts();
	
	foreach ( $installed as $font_name => $font )
	{
		$url = $fonts->urlWebFont( str_replace( '-', ' ', $font_name ) );

		$options[$font_name] = array(
			'current_value' => '<link rel="stylesheet" type="text/css" href="' . $url['url'] . '"><span style="font-family:' . str_replace( '-', ' ', $font_name ) . '" class="font-preview">ABC def GHI !</span><button class="button font_delete" href="#" data-font="' . $font_name . '">' . __( 'common_delete' ) . '</button>',
			'option_set_text' => str_replace( '-', ' ', $font_name ),
			'option_type' => 'value'
		);
	}
	
	return $options;
}



function adminUploadedFile( $config_name, $path )
{
	$config_value = is_array( $config_name ) ? Config::getArray( key( $config_name ), current( $config_name ) ) : Config::get( $config_name );
	
	
	if( is_string( $config_value ) && is_file( ABS_PATH. '/' . trim( $path, '/' ) . '/' . $config_value ) )
	{
		return __s( 'admin_delete_upload', ABS_URL . trim( $path, '/' ) . '/' . $config_value, ( is_array( $config_name ) ? current( array_keys( $config_name ) ) . '--' . current( array_values( $config_name ) ) : $config_name ));
	}
	
	return false;
}

function uploadAdminImage( $file_name, $settings, $path, $input_name = 'file' )
{
	
	try {
		
		$upload = new Upload_Single_File();
		
		$upload->setConfig( array(
			'files' => $input_name
		) );
		
		$upload->name = $file_name;
		$file = $upload->Load( $path . '/' . $file_name, $settings );

		$file_name = $file_name . '.' . $file['extension'];
		
	} catch (Exception $e) {
		
		ips_redirect( Session::get( 'admin_redirect', false ), __( $e->getMessage() ) );
		
		$file_name = null;
	}
	return $file_name;
}



function updateCategoryOption()
{
	
	
	$categories = Categories::getCategories( false, true );

	PD::getInstance()->update( IPS__FILES, array(
		'category_id' => Categories::defaultCategory()
	), array(
		'category_id' => array( array_column( $categories, 'id_category' ), 'NOT IN' )
	), false );
		
/* 	
	include_once ( IPS_ADMIN_PATH .'/language-functions.php' );
	$languages = Translate::codes();
	foreach( $languages as $language )
	{	
		foreach( $categories as $key => $category )
		{
			$category_name = PD::getInstance()->select( 'translations', array(
				'translation_name' => 'category_text_' . $category['id_category'],
				'language' => $language
			), 1, 'translation_value' );
			
			if( !isset( $category_name['translation_value'] ) && !empty( $category_name['translation_value'] ) )
			{
				$categories[$key]['category_name'] = $category_name['translation_value'];
			}
		}
		
		Config::update( 'cache_data_categories', array( 
			$language => $categories
		) );
	} */
	
}
function deleteGenerator( $ids ){
	
	if( is_array( $ids ) )
	{
		foreach( $ids as $id )
		{
			deleteGenerator( $id );
		}
		return;
	}
	
	Ips_Registry::get( 'Mem_Admin' )->delete( $ids );
}



function activateUser($ids){
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			activateUser($id);
		}
	}
	if( is_numeric($ids) )
	{ 
		if( PD::getInstance()->update("users", array('activ' => 1 ), array(
			'id' => (int)$ids
		) ) );
		{
			return true;
		}
	}
	return false;
}
function upStatus( $type )
{
	switch( $type )
	{
		case 'main':
		case 'wait':
		break;
	}
	
	return $type;
}
function deleteFiles()
{
	$status = get_input( 'status' );
	if( $status )
	{
		$where = [
			'upload_status' => ( $status == 'archive' ? 'archive' : 'public' )
		];
		
		if( $status != 'archive' )
		{
			$where['upload_activ'] = $status == 'main';
		}
		
		$files = PD::getInstance()->from( 'upload_post' )->setWhere( $where )->get();
		
		if( !empty( $files ) )
		{
			$operations = new Operations;
			foreach( $files as $file )
			{
				$operations->move( (int)$file['id'], 'delete' );
			}
		}
	}
}
function deleteUsers()
{
	$users = PD::getInstance()->from( 'users u' )
		->fields("u.*,( SELECT setting_value FROM " . db_prefix( 'users_data' ) . " WHERE user_id = u.id AND setting_key = 'is_admin') AS is_admin")
		->get();
	
	if( !empty( $users ) )
	{
		$operations = new Operations;
		foreach( $users as $users )
		{
			if( intval( $users['is_admin'] ) !== 1 )
			{
				$operations->move( (int)$users['id'], 'user_delete' );
			}
		}
	}
}

function deleteUser($ids){
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			deleteUser($id);
		}
	}
	if( is_numeric($ids) )
	{
		$operations = new Operations;
		if( $operations->move( (int)$ids, 'user_delete' ) )
		{
			return true;
		}
	}
	return false;
}

function adUpdate( $data )
{
	if( !empty( $data['ad_unique_name'] ) && !empty( $data['ad_content'] ) && isset( $data['ad_activ'] ) )
	{
		
		preg_match( '@(.*)\[php\](.*)\[\/php\](.*)@is', $data['ad_content'], $content );
		
		if( isset( $content[2] ) )
		{
			if( strpos( ABS_URL, 'iprosoft') === false )
			{
				file_put_contents( CACHE_PATH . '/cache.ads_' . $data['ad_unique_name'] . '.php', "<?php\n" . stripslashes( $content[2] ) . "\n?>" );
			}
			else
			{
				return [
					'info' => __('ads_placing_php_code_blocked'),
					'status' => 'error'
				];
			}
		}
	
		
		PD::getInstance()->update( 'ads', array(
			'ad_content' => htmlspecialchars( $data['ad_content'], ENT_QUOTES ),
			'ad_activ'	 => (int)$data['ad_activ']
		), array(
			'unique_name' => $data['ad_unique_name']
		));
		
		File::deleteFile( CACHE_PATH . '/cache.ads.php' );
		
		updateHooksAds( $data['ad_unique_name'], (int)$data['ad_activ'] );
		
		return [
			'info' => __('ads_settings_saved'),
			'status' => 'success',
			'ad' => $data['ad_content'],
			'activ' => (int)$data['ad_activ']
		];

	}
	
	return [
		'info' => __('error_mysql_query'),
		'status' => 'error'
	];
}
function likeBlock( $ids ){
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			likeBlock($id);
		}
	}
	if( is_numeric($ids) )
	{
		$row = PD::getInstance()->select( IPS__FILES, array( 
			'id' => $ids
		), 1);

		PD::getInstance()->update( IPS__FILES, array( 'up_lock' => ( $row['up_lock'] == 'social_lock' ? 'off' : 'social_lock' ) )," `id`='".$ids."' ");
		return true;
	}
	return false;
}
function autopostBlock( $ids ){
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			autopostBlock($id);
		}
	}
	if( is_numeric($ids) )
	{
		$row = PD::getInstance()->select( IPS__FILES, array( 
			'id' => $ids
		), 1);

		PD::getInstance()->update( IPS__FILES, array( 'up_lock' => ( $row['up_lock'] == 'autopost' ? 'off' : 'autopost' ) )," `id`='".$ids."' ");
		return true;
	}
	return false;
}


function moveToArchive( $ids ){
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			moveToArchive($id);
		}
	}
	if( is_numeric($ids) )
	{
		$operations = new Operations;
		if( $operations->move( (int)$ids, 'archive' ) )
		{
			return true;
		}
	}
	return false;
}
function deleteBoard( $ids )
{
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			deleteBoard( $id );
		}
		return;
	}
	if( is_numeric( $ids ) )
	{
		try {
			$b = new Board();
			$b->delete( array(
				'board_id' => $ids
			) );
		} catch ( Exception $e ) {}
	}
}
function deletePin( $ids )
{
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			deletePin( $id );
		}
		return;
	}
	if( is_numeric( $ids ) )
	{
		try {
			$pin = new Pin();
			$pin->delete( array(
				'id' => $ids
			) );				
		} catch ( Exception $e ) {}
	}
}

function featurePin( $ids )
{
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			featurePin( $id );
		}
		return;
	}
	if( is_numeric( $ids ) )
	{
		try {
			$pin = new Pin();
			$pin->feature( $ids );				
		} catch ( Exception $e ) {}
	}
}

function deleteTags( $ids )
{
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			deleteTags( $id );
		}
		return;
	}
	if( is_numeric( $ids ) )
	{
		Upload_Tags::deleteTag( $ids );
	}
	return false;
}
function deleteHook( $ids, $hook )
{
	if( is_array($ids) )
	{
		foreach( $ids as $key => $id)
		{
			deleteHook( $id, $hook[$key] );
		}
		return;
	}

	if( strlen( $ids ) > 0  )
	{
		Ips_Registry::get( 'Hooks' )->delete_action_by_key( $ids, $hook );
	}
	return false;
}
function saveHooksPosition( $ids )
{
	if( is_array( $ids ) )
	{
		$hooks = Ips_Registry::get( 'Hooks' );
		foreach( $ids as $hook )
		{
			try{	
				$hooks->create_action( array(
					'key'		=> $hook['id'],
					'priority'	=> $hook['priority']
				) );
			}catch( Exception $e ){}
		}
	}
}

function deleteFile( $ids, $archive = false )
{
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			deleteFile( $id, $archive );
		}
		return;
	}
	if( is_numeric($ids) )
	{
		$operations = new Operations;
		if( $operations->move( (int)$ids, 'delete' ) )
		{
			return true;
		}
	}
	return false;
}
function moveToMain($ids){
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			moveToMain($id);
		}
	}
	if( is_numeric($ids) )
	{
		$operations = new Operations;
		if( $operations->move( (int)$ids, 'main' ) )
		{
			return true;
		}
	}
	return false;
}
function category_change( $ids, $category_id )
{
	
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			category_change($id, $category_id);
		}
	}
	if( is_numeric($ids) && is_numeric( $category_id ) )
	{
		
		PD::getInstance()->update( IPS__FILES, array( 'category_id' => $category_id ), array(
			'id' => (int)$ids
		) );
		
		return true;
		
	}
	return false;
}

function moveToWait($ids, $archive = false )
{
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			moveToWait( $id, $archive );
		}
	}
	if( is_numeric( $ids ) )
	{
		$operations = new Operations;
		if( $operations->move( (int)$ids, ( $archive ? 'archive-wait' : 'waiting' ) ) )
		{
			return true;
		}
	}
	return false;
}

function deleteComment($ids)
{
	if( is_array($ids) )
	{
		foreach( $ids as $id)
		{
			deleteComment($id);
		}
	}
	if( is_numeric($ids) )
	{
		if( PD::getInstance()->delete( 'upload_comments', array(
			'id' => (int)$ids
		) ) )
		{
			return true;
		}
	}
	return false;
}
function deleteFanpagePost( $ids )
{
	include_once ( IPS_ADMIN_PATH .'/fanpage-functions.php' );
	return fanpageDelete( $ids );
}
function geo()
{
	if( IPS_ACTION_GET_ID )
	{

		$data = Upload_Meta::get( IPS_ACTION_GET_ID, 'uploader_data' );
		
		
		if( $data['id'] )
		{
			$data['user'] = getUserInfo( $data['id'], false );
		}

		if( !empty( $data ) )
		{
			return Templates::getInc()->getTpl( '/__admin/geo_info.html', array_merge( $data, array(
				'upload_id' => IPS_ACTION_GET_ID
			)) );
		}
	}
	return ips_admin_redirect( '/', false, 'item_not_exists' );
}
function un_ban_user()
{
	if( IPS_ACTION_GET_ID )
	{
		if( PD::getInstance()->update( 'users', array( 'user_banned' => 0 ), array( 'id' => IPS_ACTION_GET_ID ) ) )
		{
			User_Data::delete( IPS_ACTION_GET_ID, 'user_banned_data' );
			ips_message( array(
				'info' =>  __( 'user_unban_success' )
			) );
		}
	}
	else
	{
		ips_message( array(
			'alert' =>  __( 'moderate_wrong_id' )
		) );
	}
	ips_redirect();
}
function userBan( $ids, $time ){
	if( is_array($ids) )
	{
		foreach( $ids as $id )
		{
			userBan( $id, $time );
		}
		return;
	}
	switch ( $time )
	{
		case 0:  //ban na tydzień
			$date_ban = date("Y-m-d H:i:s", strtotime("now + 1 week"));
		break;
		case  1:   //ban na miesiąc
			$date_ban = date("Y-m-d H:i:s", strtotime("now + 1 month"));
		break;
		case 2:   //ban na zawsze
			$date_ban = date("Y-m-d H:i:s", strtotime("now + 1 year"));
		break;
		default:
			return true;
		break;
	}
	if( PD::getInstance()->update( 'users', array( 'user_banned' => 1 ), array( 'id' => (int)$ids ) ) )
	{
		User_Data::update( (int)$ids, 'user_banned_data', array(
			'who_ban' => USER_LOGIN,
			'date_ban' => $date_ban
		) );
		
		return true;
	}
	else
	{
		return false;	
	}
}
	/* 
	'' = array(
				'msg' => '',
				'condition' = ''
			),
	*/



function infinity_scroll_pages( $value )
{
	if( $value > 1 )
	{
		return true;
	}
	
	$option = isset( $_POST['infinity_scroll_onclick'] ) ? $_POST['infinity_scroll_onclick'] : Config::get('infinity_scroll_onclick');
	
	if( $option == 1 )
	{
		return false;
	}
	
	return true;
}
function infinity_scroll_onclick( $value )
{
	if( !$value )
	{
		return true;
	}
	
	$option = isset( $_POST['infinity_scroll_pages'] ) ? $_POST['infinity_scroll_pages'] : Config::get('infinity_scroll_pages');
	
	if( $option < 2 )
	{
		return false;
	}
	
	return true;
}

function shellExecAllowed( $on = false )
{
	if( $on == 0 )
	{
		return true;
	}
	$ffmpeg = trim( shell_exec('type -P ffmpeg') );
	if ( empty( $ffmpeg ) )
	{
		return false;
	}
	return true;
}
function array_merge_settings( $orginal, $set_value )
{
	foreach( $orginal as $key => $value )
	{
		if( is_array( $value ) && isset( $set_value[$key] ) && is_array( $set_value[$key] ) )
		{
			if( isset( $_POST['options_multiple'] )  )
			{
				if( array_search( $key, $_POST['options_multiple'] ) !== false )
				{
					continue;
				}
			}
			$set_value[$key] = array_merge( $orginal[$key], $set_value[$key] );
		}
	}
	return array_merge( $orginal, $set_value );
}

function updateSystemOptions( $settings, $messages = array() )
{
	$seo_links_format = Config::get('seo_links_format');
	$seo_links_format_pct = Config::get('seo_links_format_pct');
	
	updateSystemHooks();
	
	updateWidgetHooks( false, $settings );
	
	Config::deleteCache();

	foreach( $settings as $settings_name => $set_value )
	{
		if( $settings_name != 'form' )
		{
			$set_value = is_array( $set_value ) ? $set_value : stripslashes( trim( $set_value ) ) ;
			
			if( is_array( $set_value ) )
			{
				$config_value =  Config::getArray( $settings_name );
					
				if( !is_array( $config_value ) )
				{
					$config_value = array();
				}
				else
				{
					if( !isset( $_POST['options_multiple_flat'] ) || array_search( $config_value, $_POST['options_multiple_flat'] ) === false )
					{
						$set_value = array_merge_settings( $config_value, $set_value );
					}
				}

				$set_value = serialize( $set_value );
			}

			if( PD::getInstance()->cnt( 'system_settings', array( 'settings_name' => $settings_name ) ) )
			{
				PD::getInstance()->update( 'system_settings', array( 
					'settings_value' =>  $set_value
				), array( 
					'settings_name' => $settings_name
				) );
				
				if( PD::getInstance()->cnt( 'system_settings', array( 'settings_name' => $settings_name . '_options' ) ) )
				{
					PD::getInstance()->update( 'system_settings', array( 
						'autoload' =>  ( $set_value === 0 ? 'no' : 'yes' )
					), array(
						'settings_name' => $settings_name . '_options'
					) );
				}
			}
		}
	}

	jQueryConfig();
	
	waitCounterUpdate();
	
	if( isset( $_POST['seo_links_format'] ) || isset( $_POST['seo_links_format_pct'] ) )
	{
		
		if( $seo_links_format_pct != $_POST['seo_links_format_pct'] )
		{
			PD::getInstance()->query( "UPDATE " . db_prefix( IPS__FILES ) . " SET seo_link = replace(seo_link, '" . $seo_links_format_pct . "', '" . $_POST['seo_links_format_pct'] . "');" );
		}
		
		if( $seo_links_format != $_POST['seo_links_format'] )
		{
			PD::getInstance()->query( "UPDATE " . db_prefix( IPS__FILES ) . " SET seo_link = replace(seo_link, '.html', '');" );
			
			if( $_POST['seo_links_format'] == 1 )
			{
				PD::getInstance()->query( "UPDATE " . db_prefix( IPS__FILES ) . " SET seo_link = CONCAT(seo_link, '.html');" );
			}
		}
		
		Page::updateLinks();
	}
	
	Translate::getInstance()->clearLangCache();
	
	$messages = textFileTemplate( $messages );
	
	return $messages;
}
 
function textFileTemplate( $messages )
{
	if( Config::getArray( 'upload_text_display_type' ) == 'display_as_text' )
	{
		Config::update( 'upload_text_options', array(
			'bg' => 'color',
			'user_bg_fit' => 'full',
			'user_bg' => 0
		) );
	}

	$html = array();
	
	$html[] = 'height: 100%';
	$html[] = 'color: ' . Config::getArray( 'upload_text_options', 'font_color' );
	$html[] = 'text-align: ' . Config::getArray( 'upload_text_options', 'position' );
	//$html[] = 'opacity: ' . Config::getArray( 'upload_text_options', 'image_shadow' );
	$html[] = 'font-size: ' . Config::getArray( 'upload_text_options', 'font_size' ) . 'px';
	
	if( Config::getArray( 'upload_text_options', 'shadow' ) )
	{
		$html[] = 'text-shadow: 0 0 ' . Config::getArray( 'upload_text_options', 'font_size' ) . 'px ' . Config::getArray( 'upload_text_options', 'shadow_color' );
	}
	
	$html[] = 'background-color: ' . Config::getArray( 'upload_text_options', 'bg_color' );
	
	$padding = Config::getArray( 'upload_text_options', 'padding' );
	
	$margin = Config::getArray( 'upload_margin', 'text' );
	
	$content = '
	.file-container .text-file-wrapper{
		' . implode( ";\n", array_merge( $html, array( 
			'padding: 0px '. $padding . 'px 0px ' . $padding . 'px',
		) ) ) . '
	}
	.file-container .text-file-wrapper-normal{
		' . implode( ";\n", array_merge( $html, array( 
			'padding: ' . ( $padding + $margin['side'] ) . 'px ' . ( $padding + $margin['top'] ) . 'px ' . ( $padding + $margin['side'] ) . 'px ' . ( $padding + $margin['top'] ) . 'px',
			'border: ' . $margin['border_width'] . 'px solid ' . $margin['border_color']
		) ) ) . '
	}
	';
	

	try{
		File::put( ABS_PATH . '/css/text_files.css', $content );
	}catch(Exception $e){
		$messages[] = __( 'create_text_template' );
	}
	
	return $messages;
}

function checkFfmpeg()
{
	if( extension_loaded('ffmpeg') && class_exists('ffmpeg_movie') )
	{
		return true;
	}
	
	return false;
}
/**
* Funkcja zapisuje tablicę do ładowania w JS, inc.header.php
*/
function jQueryConfig( $ips_version = IPS_VERSION )
{
	Config::restoreConfig();
	
	$rand = rand();
	
	$icheck = array(
		'skin' => Config::getArray('ips_icheck_skin', 'skin'),
		'css_class' => ( Config::getArray( 'ips_icheck_skin', 'skin' ) != 'futurico' && Config::getArray( 'ips_icheck_skin', 'skin' ) != 'polaris' ? '-' . Config::getArray( 'ips_icheck_skin', 'color' ) : ''),
		'color_file' => ( Config::getArray( 'ips_icheck_skin', 'skin' ) != 'futurico' && Config::getArray( 'ips_icheck_skin', 'skin' ) != 'polaris' ? Config::getArray( 'ips_icheck_skin', 'color' ) : Config::getArray( 'ips_icheck_skin', 'skin' ) ),
	);
	
	$locales = Config::getArray( 'language_settings', 'language_locales' );

	$app_javascript = array
	(
		'ips_user' => array(
			'is_logged' => false,
			'user_id' => false,
			'login' => false,
			'guest_comment' =>  Config::getArray( 'user_guest_option', 'comment' ),
		),
		'ips_items_on_page' => Config::get('files_on_page'),
		
		'ips_image_lock' => ( Config::get('like_image_block') == '1' ? 'true': 'false'),
		
		'ips_randomity' => $rand,
		
		'ips_config' => array(
			'auto_login' => Config::getArray('apps_auto_login_enabled'),
			'app_id' => Config::get('apps_facebook_app', 'app_id'),
			'app_version' => Config::get('apps_facebook_app', 'app_version'),
			'app_publish' => Config::get('apps_facebook_autopost') ? true : false,
			'app_status' => 'not_logged',
			'fanpage_id' => Config::get('apps_fanpage_default_id'),
			'version' => $ips_version,
			'url' => ABS_URL,
			'animation' => array(
				'show' => Config::getArray('js_dialog', 'in'),
				'hide' => Config::getArray('js_dialog', 'out')
			),
			'icheck' => $icheck,
			'js_tiptip' => Config::getArray('template_settings', 'tips' ),
			'infinity' => array(
				'pages' => Config::get('infinity_scroll_pages'),
				'onclick' => Config::get('infinity_scroll_onclick'),
			),
			'locale' => array(
				'normal'  => $locales[IPS_LNG],
				'shorten' => substr( $locales[IPS_LNG], 0, 2 )
			),
			'medium_width' => Config::get( 'file_max_width' )
		)
	);
	
	array_walk_recursive( $app_javascript, function( &$value, $key ){
		if( is_numeric( $value ) && intval( $value ) < 2147483647 )
		{
			settype ( $value, 'float' );
		}
	});

	$app_javascript_files = array(
		'libraries.js',
		'app.js',
		'scripts.js',
		'scroll/jquery.stickyscroll.js'
	);
	
	if( Config::get('pagin_css') != 'none' )
	{
		$app_javascript_files[] = 'paginator.js';
	}
	
	$app_css_files = array(
		'style.css',
		'__common/css/common.css',
		'swobject.css',
		'pagination/pagin_' . Config::get('pagin_css') . '.css',
		'dialogs/' . Config::getArray('js_dialog', 'style') . '/dialog.css'
	);
	
	if( Config::get( 'upload_text_display_type' ) == 'display_as_text' )
	{
		$app_css_files[] = 'text_files.css';
	}
	
	/**
	* Włączony preloader obrazków
	*/
	/* if( Config::get('img_preloader') )
	{
		$app_javascript_files[] = 'jquery.lazyload.js';
	} */
	if( Config::get('widget_cookie_info') )
	{
		$app_javascript_files[] = 'widgets/widget_cookie_info.js';
		$app_css_files[] = 'widgets/widget_cookie_info.css';
	}
	
	if( Config::get('widget_user_idle') )
	{
		$app_javascript['ips_user']['idle_time'] = Config::get('widget_user_idle_options', 'time' );
		$app_javascript_files[] = 'widgets/widget_user_idle.js';
		$app_css_files[] = 'widgets/widget_user_idle.css';
	}
	
	if( Config::get('widget_search_bar') )
	{
		$app_css_files[] = 'autosuggest.css';
	}
	
	/**
	* Włączony panel najlepsze pod menu
	*/
	if( Config::get('widget_best_files') )
	{
		$app_javascript_files[] = 'widgets/widget_best_files.js';
		$app_css_files[] = 'widgets/widget_best_files.css';
	}

	if( $ips_version == 'pinestic' )
	{
		$app_javascript_files[] = 'isotope/isotope.pkgd.min.js';
	}
	
	$app_css_files = array_reverse( $app_css_files );
	
	if( file_exists( ABS_PATH . '/css/style.' . $ips_version . '.css' ) )
	{
		$app_css_files[]		= 'style.' . $ips_version . '.css';
	}
	
	if( file_exists( ABS_PATH . '/js/functions.' . $ips_version . '.js' ) )
	{
		$app_javascript_files[] = 'functions.' . $ips_version . '.js';
	}

	/*serialize to save only new array */
	Config::update( 'app_javascript_files', serialize( array( 
		'minify'	=> $app_javascript_files,
		'path'		=> App::findToMinify( $app_javascript_files, $ips_version )
	) ));
	
	Config::update( 'app_css_files', serialize( array( 
		'minify'	=> $app_css_files,
		'path'		=> App::findToMinify( $app_css_files, $ips_version )
	) ));
	
	Config::update( 'ips_randomity', $rand );
	
	Config::update( 'jquery_array', serialize( $app_javascript ) );

	clearCache( 'jscss' );
}


function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {

    $max_size = ini_get('post_max_size');
    $upload_max = ini_get('upload_max_filesize');
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}
/**
 * Convert a shorthand byte value from a PHP configuration directive to an integer value
 * @param    string   $value
 * @return   int
 */
function convertBytes( $value ) {
    if ( is_numeric( $value ) ) {
        return $value;
    } else {
        $value_length = strlen( $value );
        $qty = substr( $value, 0, $value_length - 1 );
        $unit = strtolower( substr( $value, $value_length - 1 ) );
        switch ( $unit ) {
            case 'k':
                $qty *= 1024;
                break;
            case 'm':
                $qty *= 1048576;
                break;
            case 'g':
                $qty *= 1073741824;
                break;
        }
        return $qty;
    }
}


function voteMenus()
{
	
	$menus_type = glob(ABS_PATH . '/images/icons/vote/vote_up_[0-9].png', GLOB_NOSORT);

	$options = '';
	
	$activ_type = Config::get('vote_file_menu');
	
	foreach( $menus_type as $key => $file )
	{
		$type = preg_replace( "/[^0-9]/", "", basename( $file ) );

		$options .= '<input type="radio" name="vote_file_menu" value="' . $type . '" '.( $activ_type == $type  ? ' checked="checked"': '' ).' /> 
		<span class="green"><img class="img_vote" src="../images/icons/vote/vote_up_' . $type . '.png" /></span>
		<span class="red"><img class="img_vote" src="../images/icons/vote/vote_down_' . $type . '.png" /></span><br />';
	}
	$options .= '<input type="radio" name="vote_file_menu" value="10" '.(( Config::get('vote_file_menu') == 10) ? " checked": "").' />
	<span>Wyłaczone</span><br />';
	
	
	return $options;
	
}

function jqueryDialogs()
{
	$s = array();
	if ( $handle = opendir(ABS_PATH . '/css/dialogs/') )
	{
		while ( false !== ( $dir = readdir($handle) ) )
		{
			if( $dir != '.' && $dir != '..')
			{
				$s[$dir] = $dir;
			}
		}
		closedir($handle);
	}
	return $s;
}

function jqueryDialogAnimations()
{
	
	$animations = array('blind', 'clip', 'drop', 'explode', 'fade', 'fold', 'puff', 'slide', 'scale', 'bounce', 'highlight', 'pulsate', 'shake', 'size', 
	//'transfer'
	);
	asort( $animations );
	$options = array();
	foreach( $animations as $name )
	{
	  $options[$name] = $name;
	}

	return $options;
}
function export_tokens()
{
	$tokens = PD::getInstance()->PDQuery("SELECT *, ( SELECT setting_value FROM users_data WHERE setting_key = 'facebook_uid' AND users_data.user_id = u.user_id ) as facebook_uid FROM users_data as u WHERE setting_key = 'access_token_long_live' ");
	
	$tokenstxt = '';
	
	foreach( $tokens as $token )
	{
		$tokenstxt .= $token['facebook_uid'] . ':' . $token['setting_value'] . "\n";
	}
	
	ob_end_clean();
	
	file_put_contents( 'tokens.txt', $tokenstxt );
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"tokens.txt\""); 
	readfile('tokens.txt');
	unlink('tokens.txt');
	die();
}
/* Import SMS codes both file/textarea */
function addCodes($sms_codes_post, $import_codes_file, $service_id )
{
	$import_codes_list = array();
	
	if( !empty( $sms_codes_post ) )
	{
		$import_codes_list = explode( "\n" , $sms_codes_post );
	}

	if( !empty( $import_codes_file ) )
	{
		$import_codes_list = array_merge( $import_codes_list, explode("\n" , file_get_contents( $import_codes_file ) ) );
	}	
	
	if( $import_codes_list )
	{
		$premium = new PremiumPay();

		foreach( $import_codes_list as $code )
		{
			if( !empty( $code ) )
			{
				$premium->addSMSCode( $code, $service_id );
			}
		}
	}
}

function smsPremiumUsers( $add_premium, $add_premium_time)
{
	$loginy = array();
	
	$add_premium = array_map('trim', explode("," , $add_premium));
	
	$row = PD::getInstance()->select( 'users', array(
		'login' => array( $add_premium, 'IN' )
	));
	
	$premium = new Premium();
	
	foreach( $row as $user )
	{
		$premium->createPremum( (int)$add_premium_time, $user['id'] );
	}
}

function generateTable( $type, $vars, $additional = '' ){

	$table = '';
	switch($type){
		case 'head':
		case 'foot':
			foreach( $vars as $var => $val )
			{
				$table .= '<th><span>'.$val.'</span> <span class="sorting-indicator"></span></th>';
			}
		break;
		case 'body':
			foreach( $vars as $var => $val )
			{
				$table .= '<td>' . $val . $additional .'</td>';
			}
		break;
		case '':
		break;
	}
	return $table;
	
}
/**
 * Converts newlines and break tags to an
 * arbitrary string selected by the user,
 * defaults to PHP_EOL.
 *
 * In the case where a break tag is followed by
 * any amount of whitespace, including \r and \n,
 * the tag and the whitespace will be converted
 * to a single instance of the line break argument.
 *
 * @author Matthew Kastor
 * @param string $string
 *   string in which newlines and break tags will be replaced
 * @param string $line_break
 *   replacement string for newlines and break tags
 * @return string
 *   Returns the original string with newlines and break tags
 *   converted
 */
function convert_line_breaks($string, $line_break = PHP_EOL) {
    $patterns = array(   
                        "/(<br>|<br \/>|<br\/>)\s*/i",
                        "/(\r\n|\r|\n)/"
    );
    $replacements = array(   
                            PHP_EOL,
                            $line_break
    );
    $string = preg_replace($patterns, $replacements, $string);
    return $string;
}
function removeEmptySubfolders( $path, $main = false ){

	if( substr( $path, -1 ) != '/' )
	{
		$path .= '/';
	}
	
	$d2 = array( '.', '..' );
	
	$dirs = array_diff( glob( $path.'*', GLOB_ONLYDIR ), $d2 );
	
	foreach( $dirs as $dir )
	{
		removeEmptySubfolders( $dir, true );
	}

	if( $main && count( glob( $path . '*' ) ) === 0 )
	{
		@rmdir( $path );
	}
}







/**
*
*/
function colorPicker( $config_name, $force_value = false, $force_name = false )
{
	$color = $force_value ? $force_value : Config::get( $config_name );
	if( !is_string( $color ) )
	{
		$color = '#fff';
	}
	return '
	<div id="color_' . $config_name . '" class="color_select_container" style="height: 30px display:inline;" data-color="'.$color.'">
		<div class="colorSelectorContainer" style="background-color: '.$color.';">
			<input id="' . $config_name . '" type="hidden" name="' . ( $force_name ? $force_name : $config_name ) . '" value="'.$color.'" />
		</div>
		<span>'.__('select_color').'</span>
	</div>';
}

function colorPickerArr( $name, $key )
{
	return colorPicker( $name . '_' . $key, Config::getArray( $name, $key ), $name . '[' . $key . ']' );
}


	
function realpathDir( $path_one, $path_two )
{
	$path = str_replace( str_replace("\\", "/", $path_one ), '', str_replace("\\", "/", $path_two ) );
	
	if( strpos( $path, '/') === false )
	{
		$path = str_replace( "\\", "/", $path_two );
	}
	
	return str_replace( "//", "/", $path );
}

function sendSuggest( $data )
{
	$send = new EmailExtender();
	$send->EmailTemplate( array(
		'email_to'		=> base64_decode( 'a29udGFrdEBpcHJvc29mdC5wbA==' ),
		'email_content'	=> $data['suggestion'] . '<br /><br />' . ABS_URL ,
		'email_title'	=> 'Sugestia: ' . $data['suggestion_page']
	) );
}

function replaceInTemplate( array $replace, array $to )
{
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( ABS_PATH. '/templates/' ), RecursiveIteratorIterator::SELF_FIRST );

	foreach ( $iterator as $info )
	{
		if ( $info->isFile() )
		{
			try{							
				File::replaceInFile( $info->getPathname(), $replace, $to );
			} catch ( Exception $e ) {}
		}
	}
		
}

function deleteFileByPath( $file )
{
	$file = base64_decode( $file );
	
	if( is_file( $file ) )
	{
		unlink( $file );
	}
}
function deleteFileBySetting( $setting_name )
{
	$update_value = '';
	if( strpos( $setting_name, '--' ) !== false )
	{
		$config_name = explode( '--', $setting_name );
		
		$config_value = Config::getArray( $config_name[0], $config_name[1] );
		
		$update_value = array( 
			$config_name[1] => ''
		);
		
		$setting_name = $config_name[0];
	}
	else
	{
		$config_value = Config::get( $config_name );
	}
	
	if( is_file( ABS_PATH. '/upload/system/watermark/' . $config_value ) )
	{
		unlink( ABS_PATH. '/upload/system/watermark/' . $config_value );
		Config::update( $setting_name, $update_value, true );
	}
}

function actionsButtons( $buttons )
{
	$actions = '<div class="nice-blocks features-table-actions-div actions-block  blocks-header"><div class="actions-checked">' .  __('checked') . '</div><div class="blocks-header-options">';

	foreach( $buttons as $lang => $onclick )
	{
		$actions .= ( is_array( $onclick ) ? reset( $onclick ) : '' ) . '<button class="button ' . $lang . '" onclick="actionAdmin(\'' . ( is_array( $onclick ) ? key( $onclick ) : $onclick ). '\');">' . __( $lang ) . '</button>';
	}
	return $actions . '</div></div>';
}

function admin_menu( $av_updates, $admin_route )
{
	$sub_menu = false;
	
	if( in_array( $admin_route, array('main','wait','archive','pins','boards','categories','comment','tags') ) )
	{
		$sub_menu = 'catalog_menu';
	}
	elseif( in_array( $admin_route, array('users','banned_users') ) )
	{
		$sub_menu = 'user_menu';
	}
	elseif( $admin_route == 'options' )
	{
		$sub_menu = 'options_menu';
	}
	elseif( $admin_route == 'template' )
	{
		$sub_menu = 'template';
	}
	$menu = array(
		'update' => array(
			'lang' => __( 'admin_menu_updates' )
		),
		'catalog_menu' => array(
			'lang' => __( 'admin_menu_catalog' ),
			'route' => 'catalog_menu',
			'css' => 'sub_menu_items',
			'sub_menu' => array(
				'main' => array(
					'lang' => __( 'admin_menu_main' ),
					'display' => IPS_VERSION != 'pinestic',
				),
				'wait' => array(
					'lang' => __( 'admin_menu_wait' ),
					'display' => IPS_VERSION != 'pinestic',
				),
				'archive' => array(
					'lang' => __( 'admin_menu_archive' ),
					'display' => IPS_VERSION != 'pinestic',
				),
				'pins' => array(
					'lang' => __( 'admin_menu_pins' ),
					'display' => IPS_VERSION == 'pinestic',
				),
				'boards' => array(
					'lang' => __( 'admin_menu_boards' ),
					'display' => IPS_VERSION == 'pinestic',
				),
				'categories' => array(
					'lang' => __( 'admin_menu_categories' )
				),
				'comment' => array(
					'lang' => __( 'admin_menu_comments' )
				),
				'tags' => array(
					'lang' => __( 'admin_menu_tags' )
				)
			)
		),
		'user_menu' => array(
			'lang' => __( 'admin_menu_users' ),
			'route' => 'user_menu',
			'css' => 'sub_menu_items',
			'sub_menu' => array(
				'users' => array(
					'lang' => __( 'admin_menu_users_list' )
			),
				'banned_users' => array(
					'lang' => __( 'admin_menu_users_bannes' )
				)
			)
		),
		'options_menu' => array(
			'lang' => __( 'admin_menu_options' ),
			'route' => 'options_menu',
			'css' => 'sub_menu_items',
			'sub_menu' => array(
				'other_options' => array(
					'lang' => __( 'caption_small_other_options' ),
					'route' =>'options?action=other_options',
			),
				'apps' => array(
					'lang' => __( 'caption_small_apps' ),
					'route' =>'options?action=apps',
			),
				'social' => array(
					'lang' => __( 'caption_small_social' ),
					'route' =>'options?action=social',
			),
				'email' => array(
					'lang' => __( 'caption_small_email' ),
					'route' =>'options?action=email',
			),
				'fast' => array(
					'lang' => __( 'caption_small_fast' ),
					'route' =>'options?action=fast',
			),
				'sitemap' => array(
					'lang' => __( 'caption_small_sitemap' ),
					'route' =>'options?action=sitemap',
			),
				'meta' => array(
					'lang' => __( 'caption_small_meta' ),
					'route' =>'options?action=meta',
			),
				'widget' => array(
					'lang' => __( 'caption_small_widget' ),
					'route' =>'options?action=widget',
			),
				'optimization' => array(
					'lang' => __( 'caption_small_optimization' ),
					'route' =>'options?action=optimization',
			),
				'upload' => array(
					'lang' => __( 'caption_small_upload' ),
					'route' =>'options?action=upload',
			),
				'privileges' => array(
					'lang' => __( 'caption_small_privileges' ),
					'route' =>'options?action=privileges',
				)
			)
		),
		'fanpage' => array(
			'lang' => __( 'admin_menu_fanpage' ),
			'display' => IPS_VERSION != 'pinestic',
		),
		'generator' => array(
			'lang' => __( 'admin_menu_generator' )
		),
		'hooks' => array(
			'lang' => __( 'admin_menu_hooks' )
		),
		'contests' => array(
			'lang' => __( 'admin_menu_contests' )
		),
		'mailing' => array(
			'lang' => __( 'admin_menu_mailing' )
		),
		'plugins' => array(
			'lang' => __( 'admin_menu_plugins' )
		),
		'pages' => array(
			'lang' => __( 'admin_menu_pages' )
		),
		'premium' => array(
			'lang' => __( 'admin_menu_premium' )
		),
		'ads' => array(
			'lang' => __( 'admin_menu_ads' )
		),
		
		'template' => array(
			'lang' => __( 'admin_menu_template' ),
			'route' => 'template',
			'css' => 'sub_menu_items',
			'sub_menu' => array(
				'fonts' => array(
					'lang' => __( 'template_fonts' ),
					'route' =>'template?action=fonts',
				),
				'favicon' => array(
					'lang' => __( 'template_favicon' ),
					'route' =>'template?action=favicon',
				),
				'logo' => array(
					'lang' => __( 'template_logo_menu' ),
					'route' =>'template?action=logo',
				),
				'menu' => array(
					'lang' => __( 'template_menu' ),
					'route' =>'template?action=menu',
				),
				'files' => array(
					'lang' => __( 'template_files' ),
					'route' =>'template?action=files',
				),
				'settings' => array(
					'lang' => __( 'template_settings_title' ),
					'route' =>'template?action=settings',
				),
				'version' => array(
					'lang' => __( 'template_version' ),
					'route' =>'template?action=version',
				)	
			)
		),
		'language' => array(
			'lang' => __( 'admin_menu_language' )
		),
		'uploading' => array(
			'lang' => __( 'admin_menu_uploading' )
		),
		'cron' => array(
			'lang' => __( 'admin_menu_cron' )
		),
		'censored' => array(
			'lang' => __( 'admin_menu_censored' )
		)
	);
	
	if( $admin_route == 'upload' || strpos( $admin_route, 'import-' ) !== false )
	{
		$admin_route = 'uploading';
	}
	
	
	
	$admin_action = $admin_route == 'options' || $admin_route == 'template' && isset($_GET['action']) && !empty( $_GET['action'] ) ? $_GET['action'] : 'other_options';
	
	$admin_alerts = Config::getArray( 'admin_alerts' );
	
	Config::update( 'admin_alerts', serialize( array() ) );
	
	return Templates::getInc()->getTpl( '/__admin/admin_menu.html', array(
		'admin_alerts' => $admin_alerts,
		'av_updates' => $av_updates,
		'admin_route' => $admin_route,
		'sub_menu' => $sub_menu,
		'admin_action' => $admin_action,
		'admin_menu' => admin_menu_elements( $menu, $sub_menu, $admin_route, $admin_action )
	) );
}
function admin_menu_elements( $elements, $sub_menu, $admin_route, $current_admin_action )
{
	$icons = array(
		'update' => 'fa fa-refresh',
		'censored' => 'fa fa-transgender',
		'fanpage' => 'fa fa-facebook-square',
		'generator' => 'fa fa-files-o',
		'archive' => 'fa fa-archive',
		'main' => 'fa fa-check-square',
		'categories' => 'fa fa-bars',
		'comment' => 'fa fa-comments-o',
		'wait' => 'fa fa-exchange',
		'tags' => 'fa fa-tag',
		'hooks' => 'fa fa-arrows',
		'contests' => 'fa fa-calendar',
		'mailing' => 'fa fa-envelope-o',
		'apps' => 'fa fa-shield',
		'social' => 'fa fa-share-alt',
		'email' => 'fa fa-paper-plane',
		'fast' => 'fa fa-forward',
		'sitemap' => 'fa fa-map-o',
		'meta' => 'fa fa-globe',
		'widget' => 'fa fa-folder-o',
		'optimization' => 'fa fa-bar-chart',
		'other_options' => 'fa fa-cog',
		'upload' => 'fa fa-cloud-upload',
		'privileges' => 'fa fa-shield',
		'plugins' => 'fa fa-plug',
		'pages' => 'fa fa-columns',
		'premium' => 'fa fa-usd',
		'ads' => 'fa fa-line-chart',
		'fonts' => 'fa fa-font',
		'favicon' => 'fa fa-circle-o',
		'logo' => 'fa fa-picture-o',
		'menu' => 'fa fa-bars',
		'files' => 'fa fa-pencil-square-o',
		'settings' => 'fa fa-television',
		'version' => 'fa fa-server',
		'language' => 'fa fa-language',
		'uploading' => 'fa fa-cloud-download',
		'users' => 'fa fa-cloud-download',
		'banned_users' => 'fa fa-user-times',
		'cron' => 'fa fa-tasks',
	);
	
	array_sort_by_column( $elements, 'lang', SORT_ASC );
	
	$menu = ''; 
	foreach( $elements as $key => $element)
	{
		if( isset( $element['sub_menu'] ) )
		{
			$menu .= 
			'<div class="menu_items_cnt '. ( $sub_menu == $key ? 'activ' : '' ) . '">
				<a href="#" class="button sub_menu_items  ' . ( $sub_menu == $key ? 'button-selected' : '' ) . '">' . $element['lang']  . '<span></span></a>
				
				<div class="menu_items_submenu menu_items_' . $key . '">
					' . admin_menu_elements( $element['sub_menu'], $sub_menu, $admin_route, $current_admin_action ) . '
				</div>
			</div>';
		}
		else
		{
			$menu .= admin_menu_element( $admin_route, $key, $element, $current_admin_action, $icons );
		}
	}
	return $menu;
}
function admin_menu_element( $current_route, $key, $element, $current_admin_action, $icons )
{
	if( isset( $element['display'] ) && $element['display'] == false )
	{
		return;
	}
	
	$route = isset( $element['route'] ) ? $element['route'] : $key;

	return '<a href="route-' . $route . '" class="button ' 
			. ( $route == $current_route || $key == $current_admin_action ? 'button-selected ' : ' ' ) 
			. ( isset( $element['css'] ) ? $element['css'] : '' ) . ' "><i class="' . admin_menu_icon( $key, $icons) . '"></i>' 
			. $element['lang'] 
			. '</a>';
	
	
	
	
	
	
	
	return array( 
		'element' => 
			'<a href="route-' . $route . '" class="button ' 
			. ( $route == $current_route ? 'button-selected ' : ' ' ) 
			. ( isset( $element['css'] ) ? $element['css'] : '' ) . ' ">' 
			. $element['lang'] 
			. '</a>',
		'sort' => $element['lang']
	);
}

function admin_menu_icon( $key, $icons)
{
	return isset( $icons[$key] ) ? $icons[$key] : '';
}




function responsive_menu( $items = array(), $relativ_url = '', $sort = true  )
{
	foreach( $items as $key => $item )
	{
		$items[$key] = array(
			'href' => $key,
			'label' => __( ( is_array( $item ) ? current( $item ) : $item ) ),
			'css' => ( is_array( $item ) ? key( $item ) : '' ),
		);
	}
	if( $sort )
	{
		usort( $items, function( $a, $b ){
			return strtolower( $a['label'] ) > strtolower( $b['label'] );
		} );
	}

	foreach( $items as $key => $item )
	{
		$items[$key] = '<li><a href="' . $relativ_url . $item['href'] . '" class="button ' . $item['css'] . '">' . $item['label'] . '</a></li>';
	}
	
	return '<ul class="responsive_buttons rwd_buttons_' . count( $items ) . '">' . "\n" . implode( "\n", $items ) . "\n" . '</ul>';
}


function get_snazzy_maps( $url )
{
	$data = curlIPS( $url, array(
		'timeout' => 5,
		'refferer' => 'http://snazzymaps.com'
	));
	
	if( $data != false )
	{
		preg_match_all('/<pre id="style-json">([^<]+)<\/pre>/smD', $data, $matches );
		
		if( isset( $matches[1][0] ) && !empty( $matches[1][0] ) )
		{
			return preg_replace( '/({|,)([a-zA-z0-9]*)\:/smD', '\1"\2":', str_replace( array( "\r", "\n", "\t", "\s", " ", '/**/'), '', $matches[1][0] ) );
		}
	}
	
	return $data;
}

function admin_url_button( $route, $text, $array = array(), $class = 'button' )
{
	foreach( $array as $key => $value )
	{
		$array[$key] = $key . '=' . $value;
	}
	
	return '<a href="' . admin_url( $route, implode( '&', $array ) ) . '" class="' . $class . '">' . __( $text ) . '</a> ';
}

function check_license( $license_number, $plugin_name )
{
	require_once ( IPS_ADMIN_PATH .'/libs/class.PluginManage.php' );
	
	$plugins = new Plugin_Manage();
	$plugins->loadPlugins();
	
	$plugins->plugins[ strtolower( $plugin_name ) ] = array(
		'license_number' => $license_number
	);
					
	$response = $plugins->updatePlugin( $plugin_name );
	
	return ips_json( array( 
		'response' => ( $response !== true ? strip_tags( $response ) : 'installed' )
	) );
}

function admin_msg( array $message )
{
	return '<div class="ips-message msg-' . key( $message ) . '">' . current( $message ) . '</div>';
}
function admin_caption( $caption )
{
	return '<div class="title_caption"><span class="caption">'.__( $caption ).'</span></div>';
}
function get_current( $array )
{
	$current = current( $array );
	
	return is_array( $current ) ? get_current( $current ) : $current;
}
function activ_text_file( $file )
{
	$file = base64_decode( $file );
	$dir = dirname( $file );
	$file = basename( $file );
	
	if( !file_exists( $dir . '/' . $file ) )
	{
		$file = strpos( $file, 'activ__' ) !== false ? str_replace( 'activ__', '', $file ) : 'activ__' . $file;
	}
	
	if( strpos( $file, 'activ__' ) !== false )
	{
		return rename( $dir . '/' . $file, $dir . '/' . str_replace( 'activ__', '', $file ) );
	}
	
	return rename( $dir . '/' . $file, $dir . '/activ__' . $file );
}
/* Get text bg files */
function getTextBgFiles()
{
	$files = glob( ABS_PATH . '/upload/system/upload_text_bg/*' );
	
	if( empty( $files ) )
	{
		return admin_msg( array(
			'alert' => __('upload_files_empty')
		) );
	}
	
	foreach( $files as $key => $file )
	{
		$activ = strpos( basename( $file ), 'activ__' ) !== false;
		
		$files[$key] = '
		<div class="admin-thumb ' . __( $activ ? 'activ' : '' ) . '">
			<div class="actions">
				<a class="button css-lightbox-a" href="#">
				<span>Zobacz</span>
				<div id="img_lightbox_' . $key . '" class="css-lightbox"><img src="' . ABS_URL . 'upload/system/upload_text_bg/' . basename( $file ) . '"></div>
				</a>
				<a class="button admin-thumb-activ" href="' . IPS_ADMIN_URL . '/admin-ajax.php?ajax_action=activ_text_file&file=' . base64_encode( $file ) . '" data-off="' . __(  'option_turn_off' ) . '" data-on="' . __( 'option_turn_on' ) . '">' . __( $activ ? 'option_turn_off' : 'option_turn_on' ) . '</a>
				<a class="button admin-thumb-delete" href="' . IPS_ADMIN_URL . '/admin-ajax.php?ajax_action=delete_file_path&file=' . base64_encode( $file ) . '">' . __( 'remove_system_file' ) . '</a>
			</div>
			<img src="' . ABS_URL . 'upload/system/upload_text_bg/' . basename( $file ) . '" />
		</div>';
	}
	
	return implode( '', $files );
}

function admin_loader( $width = 48 )
{
	return '<div class="admin_loader"><img width="' . $width . '" height="' . $width . '" src="' . ABS_URL .'images/svg/spinner.svg"></div>';
}



function getMarginOption( $type )
{
	$curent = Config::getArray( 'upload_margin', $type );
	
	return array(
		$type => array(
			'option_set_text' => 'upload_margin_title',
			'option_is_array' => array(
				'side' => array(
					'option_set_text' => 'upload_margin_side',
					'option_type' => 'range',
					'option_min' => 0,
					'option_max' => 100
				),
				'top' => array(
					'option_set_text' => 'upload_margin_top',
					'option_type' => 'range',
					'option_max' => 100,
					'option_min' => 0
				),
				'box_color' => array( 
					'option_set_text' => ( $type == 'demotywator' ? 'upload_margin_demotywator_box_color_title' : 'upload_margin_box_color' ),
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_margin_' . $type . '_box_color', $curent['box_color'], 'upload_margin[' . $type . '][box_color]' )
				),
				'border_width' => array(
					'option_set_text' => 'upload_margin_border_width',
					'option_type' => 'range',
					'option_max' => 9,
					'option_min' => 0,
				),
				'border_color' => array(
					'option_set_text' => 'upload_margin_border_color',
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_margin_' . $type . '_border_color', $curent['border_color'], 'upload_margin[' . $type . '][border_color]' ),
					'option_css' => ( $curent['border_width'] == 0  ? 'display_none' : '' )
				)
			)
		)
	);
}

function getOptionsFile()
{
	return require_once( IPS_ADMIN_PATH .'/admin-options.php');
}

function user_font_changes( $option_name )
{
	return array(
		'option_set_text' => 'upload_user_font_changes_title',
		
		'current_value' => Config::getArray( $option_name, 'user_font_changes' ),
		'option_select_values' => array(
			'type' => __( 'user_font_changes_type' ), 
			'size' => __( 'user_font_changes_size' ), 
			'color' => __( 'user_font_changes_color' ), 
			'style' => __( 'user_font_changes_style' ), 
			'align' => __( 'user_font_changes_align' )
		),
		'option_multiple' => true,
		'option_type' => 'input',
		'option_lenght' => 10
	);
}
