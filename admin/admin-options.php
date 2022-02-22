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

	$ips_options = array();
	
	$ips_options['main_options'] = array(
		'site_url_prefix' => array(
			'option_new_block'=> __( 'new_block_other' ),
			'option_select_values' => array(
				0	=> '',
				1	=> 'www'
			)	
		),
		'head_code' => array(
			'option_type' => 'textarea',
			'option_rows' => 6,
			'option_cols' => 140,
		),
		'footer_code' => array(
			'option_type' => 'textarea',
			'option_rows' => 6,
			'option_cols' => 140,
		),
		'seo_links_format',
		'seo_links_format_pct' => array(
			'option_select_values' => array(
					'-'	=> '-',
					'_'	=> '_'
				)
		),
		
		'site_in_maintenance' => array(
			'option_new_block'=> __( 'new_block_maintenance' ),
		),
		'site_in_maintenance_text' => array(
			'option_type' => 'textarea',
			'option_depends' => array( 
				'site_in_maintenance' => 1
			),
			'option_rows' => 3,
			'option_cols' => 55,
		),
		
		//Opcje dostępne dla gości
		'user_guest_option' => array( 
			'option_is_array' => array(
				'view_site', 
				'vote', 
				'comment',
				'upload'
			)
		),
		
		'comments_options' => array(
			'option_is_array' => array(
				'type' => array(
					'option_select_values' => array(
						'off'			=> __( 'comments_options_type_off' ),
						'ajax'			=> __( 'comments_options_type_ajax' ),
						'facebook'		=> __( 'comments_options_type_facebook' ),
						'ajax_facebook' => __( 'comments_options_type_ajax_facebook' )
					)
				),
				'as_image' => array( 
					'opt_not_allowed_templates' => array( 'pinestic' )
				),
				'as_video' => array(
					'option_depends' => in_array( Config::getArray('comments_options', 'type' ), array( 0, 3 ) ),
				),
				'below_vote' => array(
					'option_type' => 'input',
					'option_lenght' => 5,
					'option_depends' => in_array( Config::getArray('comments_options', 'type' ), array( 0, 3 ) )
				),
				'flooding_time' => array(
					'option_type' => 'input',
					'option_lenght' => 5,
					'option_depends' => in_array( Config::getArray('comments_options', 'type' ), array( 0, 3 ) )
				),
				'allways_visible',
				'comments_facebook_color_scheme' => array( 
					'option_value_as_key' => true,  
					'option_select_values' => array(
						'dark', 'light', 
					)
				),
				'emots' => array(
					'option_depends' => in_array( Config::getArray('comments_options', 'type' ), array( 0, 3 ) ),
				),
				'emots_type' => array(
					'option_depends' => Config::getArray('comments_options', 'emots' ) == 1,
					'option_value_as_key' => true,  
					'option_select_values' => array(
						'red', 'light', 'dark', 'dark_flat', 
					)
				),
			)
		),
		'files_on_page' => array(
			'option_new_block' => true,
			'option_type' => 'range',
			'option_min' => 1,
			'option_max' => 100,
			'validation' => array(
				'match' => '^[0-9]{1,3}$',
				'set_value' => ''
			),
		),
		'file_max_width' => array(
			'option_type' => 'range',
			'option_min' => 100,
			'option_max' => 700,
			'option_depends' => IPS_VERSION != 'gag' && IPS_VERSION != 'pinestic',
			'validation' => array(
				'match' => '^[0-9]{1,3}$',
				'set_value' => ''
			),
		),

		
		
		
		'user_account' => array(
			'option_is_array' => array(
				'comment_wait_time' => array(
					'option_type' => 'range',
					'option_min' => 0,
					'option_max' => 24,
				),
				'upload_wait_time' => array(
					'option_type' => 'range',
					'option_min' => 0,
					'option_max' => 24,
				),
				'email_activation',
				'register_captcha',
				'password_length' => array(
					'option_type' => 'range_slider',
					'option_min' => 1,
					'option_max' => 150,
				),
				'login_length' => array(
					'option_type' => 'range_slider',
					'option_min' => 1,
					'option_max' => 50,
				),
				'login_cookie_time' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 24,
				),
				'login_cookie_remember_time' => array(
					'option_type' => 'range',
					'option_min' => 2,
					'option_max' => 31,
				)
			)
		),

		'favourites_add' => array(
			'option_new_block' => true,
		),
		
		'scroll_long_files' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_new_block' => true,
		),
		'scroll_long_files_height' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_type' => 'input',
			'option_lenght' => 5,
			'option_depends' => array( 
				'scroll_long_files' => 1
			),
		),
		'rss_off' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_new_block' => __( 'new_block_other' ),
		), 
		'pw_messages_admin',
		
		
		'ajax_notify' => array( 
			'option_select_values' => array(
				'0' => __( 'ajax_notify_off' ),
				'top' => __( 'ajax_notify_top' ),
				'facebook' => __( 'ajax_notify_facebook' )
			)
		),
		
		'vines_main_as_file' => array(
			'opt_allowed_templates' => array( 'vines' ),
		)
	);
	
	$ips_options['template_settings'] = array(

		'template_settings' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_is_array' => array(
				'side_block_position' => array(
					'opt_allowed_templates' => array( 'gag', 'bebzol', 'vines' ),
					'option_select_values' => array(
						'right' => __('template_settings_side_block_position_right'),
						'left' => __('template_settings_side_block_position_left')
					)
				),
				'item_title',
				'upload_source',
				'tips',
				'gag_sub_menu',
				'side_stats' => array(
					'opt_allowed_templates' => array( 'gag' ),
				),
				'wait_counter' => array(
					'opt_not_allowed_templates' => array( 'pinestic' ),
				),
				'sorting_top' => array(
					'option_select_values' => array(
						'alltime' => __('common_all'),
						'day' => __('top_time_day'),
						'week' => __('top_time_week'),
						'month' => __('top_time_month'),
						'year' => __('top_time_year'),
					)
				)
			)
		),
		'vote_file_menu' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_type' => 'text', 
			'option_css' => 'normal-inputs',
			'option_value' => voteMenus()
		),
		'pagin_css' => array( 
			'option_select_values' => array(
				'red' => __( 'pagin_css_red' ),
				'red_modern' => __( 'pagin_css_red_modern' ),
				'demot' => __( 'pagin_css_demot' ),
				'demot_modern' => __( 'pagin_css_demot_modern' ),
				'blue' => __( 'pagin_css_blue' ),
				'block' => __( 'pagin_css_block' ),
				'mem' => __( 'pagin_css_mem' ),
				'kwejk' => __( 'pagin_css_kwejk' ),
				'infinity' => __( 'pagin_css_infinity' ),
				'none' => __( 'pagin_css_none' ),
			)
		),
		'pagin_css_pages' => array(
			'option_depends' => !in_array( Config::get('pagin_css'), array('kwejk', 'block', 'infinity' ) ),
			'option_type' => 'range',
			'option_min' => 2,
			'option_max' => 18
		),
		'infinity_scroll_onclick' => array(
			'option_depends' => Config::get('pagin_css') == 'infinity',
			'validation' => array(
				'function' => 'infinity_scroll_onclick',
				'set_value' => 0
			),
		),
		
		'infinity_scroll_pages' => array(
			'option_type' => 'range',
			'option_min' => 1,
			'option_max' => 20,
			'option_depends' => Config::get('pagin_css') == 'infinity',
			'validation' => array(
				'function' => 'infinity_scroll_pages',
				'set_value' => 0
			)
		),
		'js_dialog' => array( 
			'option_is_array' => array(
				'style' => array(
					'option_select_values' => jqueryDialogs()
				),
				'in' => array(
					'option_select_values' => jqueryDialogAnimations()
				),
				'out' => array(
					'option_select_values' => jqueryDialogAnimations()
				)
			)
		),
		'ips_icheck_skin' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_is_array' => array(
				'skin' => array(
					'option_select_values' => array(
						'flat' => 'flat',
						'futurico' => 'futurico',
						'line' => 'line',
						'minimal' => 'minimal',
						'polaris' => 'polaris',
						'square' => 'square',
					)
				),
				'color' => array(
					'option_depends' => Config::getArray( 'ips_icheck_skin', 'skin' ) != 'futurico' && Config::getArray( 'ips_icheck_skin', 'skin' ) != 'polaris',
					'option_select_values' => array(
						'info' => 'blue',
						'success' => 'green',
						'grey' => 'grey',
						'orange' => 'orange',
						'pink' => 'pink',
						'purple' => 'purple',
						'alert' => 'red',
						'yellow' => 'yellow',
					)
				)
			)
		)
	);
	
	$ips_options['options_fanpage'] = array(
		'apps_fanpage_posting' => array(
			'option_is_array' => array(
				'on_upload',
				'on_upload_count' => array(
					'option_type' => 'input',
					'option_lenght' => 2,
					'option_depends' => Config::getArray( 'apps_fanpage_posting', 'on_upload' ) == 1,
				),
				'on_upload_fanpages' => array(
					'current_value' => Config::getArray( 'apps_fanpage_posting', 'on_upload_fanpages' ),
					'option_select_values' => array_column( array_filter( Config::getArray( 'apps_fanpage_array' ), function( $value ){
						return isset( $value['api_token'] );
					}), 'url', 'fanpage_id' ),
					'option_multiple' => true,
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_depends' => Config::getArray( 'apps_fanpage_posting', 'on_upload' ) == 1,
				),
				'move_main',
				'move_main_count' => array(
					'option_type' => 'input',
					'option_lenght' => 2,
					'option_depends' => Config::getArray( 'apps_fanpage_posting', 'on_upload' ) == 1,
				),
				'move_main_fanpages' => array(
					'current_value' => Config::getArray( 'apps_fanpage_posting', 'move_main_fanpages' ),
					'option_select_values' => array_column( array_filter( Config::getArray( 'apps_fanpage_array' ), function( $value ){
						return isset( $value['api_token'] );
					}), 'url', 'fanpage_id' ),
					'option_multiple' => true,
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_depends' => Config::getArray( 'apps_fanpage_posting', 'move_main' ) == 1,
				)
			)
		),
		'apps_fanpage_auto' => array(
			'option_is_array' => array(
				'image_info' => array(
					'default_value' => 'orginal',
					'option_set_text' => __('apps_fanpage_auto_image'),
					'option_select_values' => array(
						'orginal'	=> __('apps_fanpage_auto_orginal'),
						'user'		=>  __('apps_fanpage_auto_user'),
						'off'		=>  __('turn_off')
					),
					'option_css' => 'apps_fanpage_auto_check close_all',
				),
				'image' => array(
					'default_value' => '',
					'option_set_text' => __s('fanpage_image', ABS_URL . 'upload/images/large/image.jpg'),
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'display_none apps_fanpage_auto_input',
				),
				
				
				'title_info' => array(
					'default_value' => 'orginal',
					'option_set_text' =>  __('title'),
					'option_select_values' => array(
						'orginal'	=> __('apps_fanpage_auto_orginal'),
						'user'		=>  __('apps_fanpage_auto_user')
					),
					'option_css' => 'apps_fanpage_auto_check apps_fanpage_auto_select',
				),
				'title' => array(
					'default_value' => '',
					'option_set_text' => __('apps_fanpage_auto_title_add'),
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'display_none apps_fanpage_auto_input',
				),
				
				'caption_info' => array(
					'default_value' => 'orginal',
					'option_set_text' => 'Caption',
					'option_select_values' => array(
						'orginal'	=> __('apps_fanpage_auto_orginal'),
						'user'		=>  __('apps_fanpage_auto_user'),
						'off'		=>  __('turn_off')
					),
					'option_css' => 'apps_fanpage_auto_check apps_fanpage_auto_select',
				),
				'caption' => array(
					'default_value' => '',
					'option_set_text' => __('apps_fanpage_auto_caption'),
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'display_none apps_fanpage_auto_input',
				),
				
				
				'description_info' => array(
					'default_value' => 'orginal',
					'option_set_text' => __('fanpage_data_description_title'),
					'option_select_values' => array(
						'orginal'	=> __('apps_fanpage_auto_orginal'),
						'user'		=>  __('apps_fanpage_auto_user'),
						'off'		=>  __('turn_off')
					),
					'option_css' => 'apps_fanpage_auto_check apps_fanpage_auto_select',
				),
				'description' => array(
					'default_value' => '',
					'option_set_text' => __('apps_fanpage_auto_more_details'),
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'display_none apps_fanpage_auto_input',
				),
				'message' => array(
					'default_value' => '',
					'option_set_text' => __('fanpage_data_message_title'),
					'option_type' => 'textarea',
				),
			)
		)
	);
	
	$ips_options['options_apps'] = array(
		'apps_login_enabled' => array(
			'option_new_block' => true,
			'option_is_array' => array(
				'facebook',
				'nk',
				'twitter' => array(
					'option_depends' => strpos( ABS_URL, 'iprosoft.') === false,
				)
			)
		),
		
		'apps_auto_login_enabled' => array(
			'option_new_block' => true,
			'option_is_array' => array(
				'facebook',
				'nk',
				'twitter' =>array(
					'option_depends' => strpos( ABS_URL, 'iprosoft.') === false,
				)
			)
		),
		
		'apps_facebook_app' => array(
			'option_is_array' => array(
				'app_id' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
					'validation' => array(
						'match' => '[0-9]{10,}',
						'set_value' => 'false'
					),
				),
				'app_secret' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
					'validation' => array(
						'match' => '[0-9a-z]{27,37}',
						'set_value' => 'false'
					),
				),
				'app_version' => array(
					'option_value_as_key' => true,
					'option_select_values' => array(
						'v2.0',
						'v2.1',
						'v2.2',
						'v2.3',
						'v2.4',
					)
				),
				'admin_id' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
					'validation' => array(
						'match' => '[0-9]{5,25}',
						'set_value' => 'false'
					),
				),
				'previliges' => array(
					'current_value' => Config::getArray( 'apps_facebook_app', 'previliges' ),
					'option_select_values' => array( 
						'email' => 'email', 
						'user_birthday' => 'user_birthday', 
						'publish_actions' => 'publish_actions'
					),
					'option_multiple' => true,
					'option_depends' => 'demo_disabled',
				),
				'require_previliges' => array(
					'option_depends' => in_array( 'publish_actions', Config::getArray( 'apps_facebook_app', 'previliges' ) ),
				),
				'save_token',
				'auto_user_name',
				'exclude_adult'
			)
		),
		'apps_fanpage_default' => array(
			'option_type' => 'text',
			'option_value' => '<div class="input_with_button"><input disabled="disabled" type="text" value="' . Config::get('apps_fanpage_default') . '" size="40" name="apps_fanpage_default" style="width: 40%;"><input disabled="disabled" type="text" value="' . Config::get('apps_fanpage_default_id') . '" size="40" name="apps_fanpage_default_id" style="width: 25%;"><a class="button" href="' . admin_url( 'fanpage', 'action=settings' ) . '">' . __('field_change_add') . '</a></div>',
			'option_depends' => 'demo_disabled',
			'validation' => array(
				'match' => '^https?://(?:www\.)?facebook.com/(.+)?$',
				'set_value' => 'false'
			),
		),
		'opt_html_1' => array(
			'content' => '<div class="option-cnt"><a class="button ips-confirm" title="' . __( 'delete_connected_acounts_info') . '" href="' . admin_url( 'delete_connection', 'uid=facebook_uid' ) . '">' . __( 'delete_connected_acounts') . '</a></div>'
		),
		
		
		
		'opt_html_fanpage_settings' => array(
			'option_new_block' => __( 'apps_fanpage_info' ),
			'content' => '<div class="option-cnt"><a class="button" href="' . admin_url( 'fanpage', 'action=settings' ) . '">' . __( 'fanpage_settings') . '</a></div>'
		),
		'apps_nk_app' => array(
			'option_is_array' => array(
				'app_key' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
				),
				'app_secret' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
				)
			)
		),
		
		'opt_html_nk_app' => array(
			'content' => '<div class="option-cnt"><a class="button ips-confirm" title="' . __( 'delete_connected_acounts_info') . '" href="' . admin_url( 'delete_connection', 'uid=nk_uid' ) . '">' . __( 'delete_connected_acounts') . '</a></div>'
		),

		'apps_twitter_app' => array(
			'option_depends' => strpos( ABS_URL, 'iprosoft.') === false,
			'option_is_array' => array(
				'consumer_key' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
				),
				'consumer_secret' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
				),
				'username' => array(
					'option_type' => 'input',
					'option_lenght' => 40,
					'option_depends' => 'demo_disabled',
				)
			)
		),
		'opt_html_twitter_app' => array(
			'content' => '<div class="option-cnt"><a class="button ips-confirm" title="' . __( 'delete_connected_acounts_info') . '" href="' . admin_url( 'delete_connection', 'uid=twitter_uid' ) . '">' . __( 'delete_connected_acounts') . '</a></div>',
			'option_depends' => strpos( ABS_URL, 'iprosoft.') === false,
		),
		'apps_get_thumbnail' => array(
			'option_new_block'	=> __( 'new_block_thumbnail' ),
		),
		
		
		
		'apps_facebook_autopost' => array(
			'option_new_block'	=> __( 'apps_facebook_autopost_block_title' ),
		),
		'apps_facebook_autopost_options' => array(
			'option_display_name' => false,
			'option_depends' => array( 
				'apps_facebook_autopost' => 1
			),
			'option_is_array' => array(	
				'count' => array(
					'option_type' => 'input',
					'option_lenght' => 2,
				),
				'wait_time' => array(
					'option_type' => 'input',
					'option_lenght' => 2,
				),
				'auto_block',
				'only_logged',
				'image',
			)
		),
		
		'apps_social_lock' => array(
			'option_new_block'	=> __( 'apps_social_lock_block' ),
		),
		'apps_social_lock_options' => array(
			'option_display_name' => false,
			'option_depends' => array( 
				'apps_social_lock' => 1
			),
			'option_is_array' => array(	
				'count' => array(
					'option_type' => 'input',
					'option_lenght' => 2,
				),
				'wait_time' => array(
					'option_type' => 'input',
					'option_lenght' => 2,
				),
				'auto_block',
				'image',
			)
		),
		
		'apps_google_app' => array(
			'option_is_array' => array(
				'maps_api_key' => array(
					'option_type' => 'input',
					'option_lenght' => 50,
					'option_depends' => strpos( ABS_URL, 'iprosoft.') === false ,
				),
				'profile_url' => array(
					'option_type' => 'input',
					'option_lenght' => 80,
					'option_depends' => 'demo_disabled',
				)
			)
		),
		'opt_html_apps_google_app' => array(
			'opt_allowed_templates' => array( 'pinestic'),
			'content' => '<div class="option-cnt"><a href="https://developers.google.com/maps/documentation/javascript/tutorial?hl=pl#api_key">https://developers.google.com/maps/documentation/javascript/tutorial?hl=pl#api_key</a></div>',
		),
	
		
		'google_maps_advanced' => array(
			'opt_allowed_templates' => array( 'pinestic'),
			'option_type' => 'value',
			'current_value' => '<a class="button" href="' . admin_url( 'map' ) . '">' . __( 'common_go' ) . '</a>'
		)
	);
	
	$ips_options['options_map'] = array(
		'apps_google_maps_customize' => array(
			'option_is_array' => array(
				'customized_styles' => array(
					'option_css' => 'maps_customize',
					'option_type' => 'textarea',
				), 
				'default_position' => array(
					'option_select_values' => array(
						'canada' => 'Canada',
						'france' => 'France',
						'germany' => 'Germany',
						'poland' => 'Poland',
						'usa' => 'United States',
					)
				),
				'disallow_multiple_markers' => array()
			)
		)
	);
	
	$ips_options['options_fast'] = array( 
		'page_fast',
		'page_fast_options' => array(
			'option_depends' => array( 
				'page_fast' => 1
			),
			'option_is_array' => array(
				'widget',
				'share',
				'like',
				'google'
			)
		)
	);
	

	$ips_options['options_articles'] = array(
		'article_options' => array(
			'option_is_array' => array(
		
				'img_intro', 
				'allow_video',
				'view_type' => array( 
					'option_select_values' => array(
						1 => __( 'article_options_view_type_1' ),
						2 => __( 'article_options_view_type_2' ),
						3 => __( 'article_options_view_type_3' ),
						4 => __( 'article_options_view_type_4' ),
						5 => __( 'article_options_view_type_5' )
					)
				),
				'intro_length' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 250
				),
			)
		)
	);

	$ips_options['options_galleries'] = array(
		'gallery_options' => array(
			'option_is_array' => array(
				'local' => array(
					'opt_not_allowed_templates' => array( 'pinestic' ),
				),
				'watermark',
				'load' => array( 
					'option_select_values' => array(
						'simple' => __( 'gallery_load_simple' ),
						'pretty_photo' => __( 'gallery_load_pretty_photo' ),
						'lightbox' => __( 'gallery_load_lightbox' ),
						'pirobox' => __( 'gallery_load_pirobox' ),
						'kwejk' => __( 'gallery_load_kwejk' ),
						'demot' => __( 'gallery_load_demot' )
					)
				),
				'load_img_before',
				'add_description',
				'description_length' => array(
					'opt_not_allowed_templates' => array( 'pinestic' ),
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 10000
				),
				'items_range' => array(
					'option_type' => 'range_slider',
					'option_min' => 1,
					'option_max' => 50
				)
			)
		)
	);
	if( IPS_VERSION == 'pinestic' )
	{
		$ips_options['options_galleries']['gallery_options']['load']['option_select_values'] = array(
			'pinit'	=> __( 'gallery_load_pinit' )
		);
	}
	
	$ips_options['options_animation'] = array(
		'animation_options' => array(
			'option_is_array' => array(
				'const_size',
				'width' => array(
					'option_depends' => Config::getArray( 'animation_options', 'const_size' ) == 1,
					'option_type' => 'range',
					'option_min' => 300,
					'option_max' => 700
				),
				'height' => array(
					'option_depends' => Config::getArray( 'animation_options', 'const_size' ) == 1,
					'option_type' => 'range',
					'option_min' => 300,
					'option_max' => 700
				)
			)
		)
	);
	
	$ips_options['options_ranking'] = array(	
		'ranking_options' => array(
			'option_is_array' => array(
				'local',
				'watermark',
				'create_image',
				'bg_color' => array(
					'option_depends' => Config::getArray( 'ranking_options', 'create_image' ) == 1,
					'option_type' => 'text',
					'option_value' => colorPicker( 'ranking_options_bg_color', Config::getArray( 'ranking_options', 'bg_color' ), 'ranking_options[bg_color]' )
				),
				'add_description',
				'description_length' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 5000
				),
				'items_range' => array(
					'option_type' => 'range_slider',
					'option_min' => 1,
					'option_max' => 50
				)
			)
		)
	);
	
	$ips_options['options_email'] = array(
		'email_admin_user' => array(
			'option_type' => 'input',
			'option_lenght' => 60
		),
		'email_smtp',
		'email_smtp_options' => array(
			'option_is_array' => array(
				'login' => array(
					'option_type' => 'input',
					'option_lenght' => 50,
					'option_depends' => array( 
						'email_smtp' => 1
					)
				),
				'password' => array(
					'option_type' => 'input',
					'option_lenght' => 50,
					'option_depends' => array( 
						'email_smtp' => 1
					)
				),
				'host' => array(
					'option_type' => 'input',
					'option_lenght' => 50,
					'option_depends' => array( 
						'email_smtp' => 1
					)
				),
				'port' => array(
					'option_type' => 'input',
					'option_lenght' => 5,
					'option_depends' => array( 
						'email_smtp' => 1
					)
				),
				'auth_on' => array( 
					'option_depends' => array( 
						'email_smtp' => 1
					)
				)
				,
				'ssl' => array( 
					'option_depends' => array( 
						'email_smtp' => 1
					)
				),
			)
		),
		'opt_html_footer' => array(
			'content' => '<div class="option-cnt"><a class="button" href="' . admin_url( 'language', 'action=edit&code=' . Config::getArray( 'language_settings', 'default_language') . '&edit-action=email' ) . '">' . __( 'email_edit_footer' ) . '</a></div>',
		),
		'opt_html_tpl' => array(
			'content' => '<div class="option-cnt"><a class="button" href="' . admin_url( 'template', 'action=files&file=email.html' ) . '">' . __( 'email_edit_tpl' ) . '</a></div>',
		),
	);
	

	$ips_options['options_pinit'] = array(
		'upload_type' => array(
			'option_is_array' => array(
				'mp4',
				'video'
			)
		),
		
		'pinit_upload_normal',
		'pinit_upload_website',
		'add_max_file_size' => array(
			'option_type' => 'input',
			'option_lenght' => 3,
			'option_sprintf' => convertBytes( file_upload_max_size() ) / 1048576,
			'validation' => array(
				'match' => '^[0-9]{1,3}$',
				'set_value' => ''
			),
		),
		'images_compress' => array(
			'option_is_array' => array(
				'png' => array(
					'option_type' => 'range',
					'option_max' => 6,
					'option_min' => 1
				),
				'jpg' => array(
					'option_type' => 'range',
					'option_max' => 100,
					'option_min' => 50
				),
			),
		),
	);

	$ips_options['options_add_types'] = array(
		

		'upload_type' => array(
			'option_is_array' => array(
				'article', 'gallery', 'animation', 'demotywator', 'video', 'text', 'image', 'mem', 'ranking'
			)
		),
		
		'upload_demotywator_type' => array(
			'option_is_array' => array(
				'image', 'text', 'video', 
			),
			'option_depends' => Config::getArray( 'upload_type', 'demotywator' ) == 1
		),
		'upload_video_type' => array(
			'option_is_array' => array(
				'video', 'mp4', 'swf'
			),
			'option_depends' => Config::getArray( 'upload_type', 'video' ) == 1
		),
		'upload_allowed_html_tags' => array(
			'option_new_block' => __( 'new_block_other' ),
			'current_value' => Config::getArray( 'upload_allowed_html_tags' ),
			'option_value_as_key' => true, 
			'option_select_values' => array(
				'img','div','span','ul','li','strong','em','u','b','i','p','a','h1','h2','h3','h4','h5','h6','em','blockquote','sup','sub','q'
			),
			'option_multiple' => true,
			'option_type' => 'input',
			'option_lenght' => 10,
		),
		
		
		
		
		'add_filename_date',
		'add_extra_description' => array( 
			'option_select_values' => array(
				0			=> __( 'add_extra_description_0' ),
				'all_users'	=> __( 'add_extra_description_all_users' ),
				'only_admin'=> __( 'add_extra_description_only_admin' )
			)
		),
		'add_require_rules',
		'add_source',
		'add_captcha' => array( 
			'option_select_values' => array(
				0 => __( 'add_captcha_1' ),
				1 => __( 'add_captcha_2' ),
				2 => __( 'add_captcha_3' )
			)
		),
		'image_captcha_size' => array( 
			'option_display_name' => false,
			'option_depends' =>  Config::get( 'add_captcha' ) != 0,
			'option_is_array' => array(
				'font' => array(
					'option_select_values' => getFonts(),
				),
				'min' => array(
					'option_type' => 'range',
					'option_max' => 24,
					'option_min' => 8
				),
				'max' => array(
					'option_type' => 'range',
					'option_max' => 24,
					'option_min' => 8
				),
			)
		),
		'add_max_file_size' => array(
			'option_new_block' => true,
			'option_type' => 'range',
			'option_max' => convertBytes( file_upload_max_size() ) / 1048576,
			'option_min' => 1,
			'option_sprintf' => convertBytes( file_upload_max_size() ) / 1048576,
			'validation' => array(
				'match' => '^[0-9]{1,3}$',
				'set_value' => ''
			),
		),
		
		'upload_tags' => array(
			'option_new_block' => __( 'caption_tags' ),
		),
		'upload_tags_options' => array(
			'option_display_name' => false,
			'option_is_array' => array(
				'extract',
				'length' => array(
					'option_type' => 'range',
					'option_max' => 100,
					'option_min' => 1
				),
				'blocked' => array(
					'option_css' => 'tags_fancy_input',
					'option_type' => 'textarea',
					'option_rows' => 3,
					'option_cols' => 55,
				)
			),
			'option_depends' => array( 
				'upload_tags' => 1
			)
		),
		
		
		
		
		
		
		'images_compress' => array(
			'option_is_array' => array(
				'png' => array(
					'option_type' => 'range',
					'option_max' => 6,
					'option_min' => 1
				),
				'jpg' => array(
					'option_type' => 'range',
					'option_max' => 100,
					'option_min' => 50
				),
			),
		),
		'add_private_files',
		'add_thumb_size' => array(
			'option_new_block' => 'text',
			'option_type' => 'text',
			'option_is_array' => array(
				'width' => array(
					'option_type' => 'range',
					'option_max' => 300,
					'option_min' => 50
				), 
				'height' => array(
					'option_type' => 'range',
					'option_max' => 300,
					'option_min' => 50
				), 
				'box' => array( 
					'type' => 'radio'
				), 
				'box_color' => array( 
					'option_type' => 'text',
					'option_value' => colorPicker( 'add_thumb_size_box_color', Config::getArray( 'add_thumb_size', 'box_color' ), 'add_thumb_size[box_color]' )
				),
				
			)
		),
		'upload_margin' => array(
			'option_set_text' => 'upload_margin_default_title',
			'option_is_array' => getMarginOption( 'default' )
		),
		
		

		
		
		
		
		
		
		
		'system_fonts' => array( 
			'option_is_array' => getSystemFonts()
		),
	/* 	'system_fonts_add' => array(
			'option_type' => 'text',
			'option_value' => '<button class="button" onclick="importFont();return false;">' . __( 'system_fonts_add_input' ) . '</button>'
		), */
		'system_fonts_add_web' => array(
			'option_type' => 'text',
			'option_value' => '<a class="button" href="' . admin_url( 'options', 'action=upload&sub_action=fonts' ) . '">' . __( 'system_fonts_add_web_input' ) . '</a>'
		),
	);
	//Opcje Video
	$ips_options['options_video'] = array(	
		
	
		'upload_video_options' => array(
			'option_is_array' => array(
				'add_video_layer',
				'add_video_resolution' => array( 
					'opt_not_allowed_templates' => array( 'pinestic' ), 
					'option_select_values' => array(
						'16:9'	=> __( 'upload_video_options_resolution_16_9' ),
						'4:3'	=> __( 'upload_video_options_resolution_4_3' ),
						'0'		=> __( 'upload_video_options_resolution_off' )
					)
				),
			),
		),
		
		'video_player' => array(
			'option_is_array' => array(
				'type' => array(
					'option_select_values' => array(
						'ajax'		=> __( 'video_player_ajax' ),
						'standard'	=> __( 'video_player_standard' )
					)
				),
				'autoplay',
				'loop'
			)
		),
		
		'upload_mp4_options' => array(
			'option_is_array' => array(
				'max_duration' => array(
					'option_type' => 'range',
					'option_max' => 3600,
					'option_min' => 1,
					'validation' => array(
						'match' => '^[0-9]{1,}$',
						'set_value' => 60
					),
				), 
				'download_always', 
				'default_cover' => array(
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_mp4_options_default_cover', Config::getArray( 'upload_mp4_options', 'default_cover' ), 'upload_mp4_options[default_cover]' )
				), 
				'cover_add', 
				'cover_file' => array(
					'option_depends' => array( 
						'cover_add' => 1
					),
					'option_type' => 'text',
					'option_value' => '<input type="file"  name="mp4_cover_file_upload" />'
				),
				'extract_image' => array(
					'option_type' => 'checkbox',
					'validation' => array(
						'function' => 'shellExecAllowed',
						'set_value' => 0
					),
				),
			),
		),
		
		
		'mp4_autoplay',
		
		'mp4_player' => array( 
			'option_depends' => defined('IPS_SELF'),
			'option_new_block' => 'MP4 player',
			'option_select_values' => array(
				'mediaelement'	=> 'MediaElement',
				'video.js'	=> 'Video.js',
			)
		),
		
		'upload_margin' => array(
			'option_is_array' => getMarginOption( 'video' )
		),
	);	
	
						
						
	$ips_options['options_demotywator'] = array(
		'upload_demotywator_text' => array(
			'option_is_array' => array(
				'font' => array(
					'option_select_values' => getFonts(),
				),
				'user_font_changes' => user_font_changes( 'upload_demotywator_text' ),
				'font_color_top' => array(
					'option_type' => 'text',
					'option_set_text' => 'upload_font_color_top_title',
					'option_value' => colorPicker( 'upload_demotywator_text_font_color_top', Config::getArray( 'upload_demotywator_text', 'font_color_top' ), 'upload_demotywator_text[font_color_top]' )
				),
				'font_color_bottom' => array(
					'option_type' => 'text',
					'option_set_text' => 'upload_font_color_bottom_title',
					'option_value' => colorPicker( 'upload_demotywator_text_font_color_bottom', Config::getArray( 'upload_demotywator_text', 'font_color_bottom' ), 'upload_demotywator_text[font_color_bottom]' )
				),
				'font_size_top' => array(
					'option_type' => 'range',
					'option_set_text' => 'upload_font_size_top_title',
					'option_min' => 2,
					'option_max' => 48
				),
				'font_size_bottom' => array(
					'option_type' => 'range',
					'option_set_text' => 'upload_font_size_bottom_title',
					'option_min' => 2,
					'option_max' => 48
				),
				'vertical_align' => array(
					'option_select_values' => array(
						'top'		=> __( 'upload_demotywator_text_vertical_align_top' ),
						'bottom'	=> __( 'upload_demotywator_text_vertical_align_bottom' ),
						'division'	=> __( 'upload_demotywator_text_vertical_align_division' )
					)
				),
				'hide_title'
			)
		),
		
		'upload_demot_signature' => array(
			'option_is_array' => array(
				'type' => array( 
					'option_select_values' => array(
						'off' => __( 'upload_demot_signature_off' ),
						'text_bottom' => __( 'upload_demot_signature_text_bottom' ),
						'text' => __( 'upload_demot_signature_text' ),
						'image' => __( 'upload_demot_signature_image' )
					)
				),
				'position' => array(
					'option_depends' => Config::getArray( 'upload_demot_signature', 'type' ) == 'text_bottom',
					'option_select_values' => array(
						'left'   => __( 'x_position_left' ),
						'center' => __( 'x_position_center' ),
						'right'  => __( 'x_position_right' ),
					)
				),
				'file' => array(
					'option_depends' => Config::getArray( 'upload_demot_signature', 'type' ) == 'image',
					'option_type' => 'text',
					'option_value' => '<input type="file" name="upload_signature" />',
					'option_sprintf' => adminUploadedFile( array( 'upload_demot_signature' => 'file' ), 'upload/system/watermark' ),
				),
				'font' => array(
					'option_select_values' => getFonts(),
					'option_depends' => in_array( Config::getArray( 'upload_demot_signature', 'type' ), array( 'text', 'text_bottom' ) ),
				),
				'font_size' => array(
					'option_depends' =>  in_array( Config::getArray( 'upload_demot_signature', 'type' ), array( 'text', 'text_bottom' ) ),
					'option_type' => 'range',
					'option_min' => 2,
					'option_max' => 16,
					'validation' => array(
						'msg' => 'upload_font_size_error',
						'match' => '^[0-9]{1,3}$',
						'set_value' => ''
					),
				),
				'color' => array(
					'option_depends' =>  in_array( Config::getArray( 'upload_demot_signature', 'type' ), array( 'text', 'text_bottom' ) ),
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_demot_signature_color', Config::getArray( 'upload_demot_signature', 'color' ), 'upload_demot_signature[color]' )
				),
				'upper' => array(
					'option_depends' =>  in_array( Config::getArray( 'upload_demot_signature', 'type' ), array( 'text', 'text_bottom' ) ),
					'option_select_values' => array(
						'low' => __( 'upload_demot_signature_upper_low' ),
						'up' => __( 'upload_demot_signature_upper_up' ),
					)
				),
			)
		),

		'upload_margin' => array(
			'option_is_array' => getMarginOption( 'demotywator' )
		),
	);
	$ips_options['options_mem'] = array(

		
		'upload_mem_text' => array(
			'option_is_array' => array(
				'font' => array(
					'option_select_values' => getFonts()
				),
				'font_color_top' => array(
					'option_type' => 'text',
					'option_set_text' => 'upload_font_color_top_title',
					'option_value' => colorPicker( 'upload_demotywator_text_font_color_top', Config::getArray( 'upload_mem_text', 'font_color_top' ), 'upload_mem_text[font_color_top]' )
				),
				'font_size_top' => array(
					'option_type' => 'range',
					'option_set_text' => 'upload_font_size_top_title',
					'option_min' => 2,
					'option_max' => 48
				),
				'font_color_bottom' => array(
					'option_type' => 'text',
					'option_set_text' => 'upload_font_color_bottom_title',
					'option_value' => colorPicker( 'upload_demotywator_text_font_color_bottom', Config::getArray( 'upload_mem_text', 'font_color_bottom' ), 'upload_mem_text[font_color_bottom]' )
				),
				'font_size_bottom' => array(
					'option_type' => 'range',
					'option_set_text' => 'upload_font_size_bottom_title',
					'option_min' => 2,
					'option_max' => 48
				),
				'user_font_changes' => user_font_changes( 'upload_mem_text' ),

				'add_shadow' => array(
					'option_new_block' => __( 'upload_mem_text_title' )
				),
				'shadow_color' => array(
					'option_type' => 'text',
					'option_depends' => Config::getArray( 'upload_mem_text', 'add_shadow' ) == 1,
					'option_value' => colorPicker( 'upload_mem_text_color', Config::getArray( 'upload_mem_text', 'shadow_color' ), 'upload_mem_text[shadow_color]' )
				),
				'shadow_blur' => array(
					'option_depends' => Config::getArray( 'upload_mem_text', 'add_shadow' ) == 1,
					'option_type' => 'range',
					'option_min' => 0,
					'option_max' => 100
				),
				
				'add_border' => array(
					'option_new_block' => __('upload_mem_text_border_title')
				),
				'border_color' => array(
					'option_type' => 'text',
					'option_depends' => Config::getArray( 'upload_mem_text', 'add_border' ) == 1,
					'option_value' => colorPicker( 'upload_mem_text_color', Config::getArray( 'upload_mem_text', 'border_color' ), 'upload_mem_text[border_color]' )
				),
				'border_opacity' => array(
					'option_depends' => Config::getArray( 'upload_mem_text', 'add_border' ) == 1,
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 100
				),
				
				'add_background' => array(
					'option_new_block' => __('upload_mem_text_background_title')
				),
				'background_color' => array(
					'option_type' => 'text',
					'option_depends' => Config::getArray( 'upload_mem_text', 'add_background' ) == 1,
					'option_value' => colorPicker( 'upload_mem_text_color', Config::getArray( 'upload_mem_text', 'background_color' ), 'upload_mem_text[background_color]' )
				),
				'background_opacity' => array(
					'option_depends' => Config::getArray( 'upload_mem_text', 'add_background' ) == 1,
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 100
				),
			)
		),

	

		'upload_margin' => array(
			'option_is_array' => getMarginOption( 'mem' )
		),
	);

	$ips_options['options_add_text'] = array(
		
		
		'upload_text_display_type' => array( 
			'option_new_block' => __( 'upload_text_options_title' ),
			'option_select_values' => array(
				'display_as_text'	=> __( 'upload_text_display_type_1' ),
				'display_as_image'	=> __( 'upload_text_display_type_2' )
			)
		),
		'upload_text_display_cut_intro' => array(
			'option_depends' => array( 
				'upload_text_display_type' => 'display_as_text'
			)
		),
		
		
		
		
		'upload_text_options' => array(
			'option_display_name' => false,
			'option_is_array' => array(
				'font' => array(
					'option_depends' => array( 
						'upload_text_display_type' => 'display_as_image'
					), 
					'option_select_values' => getFonts()
				),
				'font_size' => array(
					'option_type' => 'range',
					'option_min' => 8,
					'option_max' => 64,
					'validation' => array(
						'msg' => 'upload_font_size_error',
						'match' => '^[0-9]{1,3}$',
						'set_value' => ''
					),
				),
				'font_color' => array(
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_color', Config::getArray( 'upload_text_options', 'font_color' ), 'upload_text_options[font_color]' ),
					'validation' => array(
						'msg' => 'upload_text_color_error',
						'match' => '^[a-zA-Z0-9\#]{1,7}$',
						'set_value' => ''
					),
				),

				'user_font_changes' => array_merge( user_font_changes( 'upload_text_options' ), array(
					'option_depends' => array( 
						'upload_text_display_type' => 'display_as_image'
					)
				)),
				'position' => array( 
					'option_select_values' => array(
						'left'	=> __( 'x_position_left' ),
						'center'=> __( 'x_position_center' ),
						'right'	=> __( 'x_position_right' )
					)
				),
				'letters' => array( 
					'option_depends' => array( 
						'upload_text_display_type' => 'display_as_image'
					),
					'option_select_values' => array(
						'upper'	=> __( 'upload_text_options_letters_upper' ),
						'lower'=> __( 'upload_text_options_letters_lower' ),
						'none'	=> __( 'upload_text_options_letters_none' )
					)
				),
				'bg' => array( 
					'option_depends' => array( 
						'upload_text_display_type' => 'display_as_image'
					),
					'option_new_block' => __( 'upload_text_options_image_title' ),
					'option_select_values' => array(
						'color'	=> __( 'upload_text_options_bg_1' ),
						'gradient'=> __( 'upload_text_options_bg_2' ),
						'image'	=> __( 'upload_text_options_bg_3' )
					)
				),
				'bg_color' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'color',
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_bg_color', Config::getArray( 'upload_text_options', 'bg_color' ), 'upload_text_options[bg_color]' )
				),
				'bg_gradient_1' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'gradient',
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_bg_gradient_1', Config::getArray( 'upload_text_options', 'bg_gradient_1' ), 'upload_text_options[bg_gradient_1]' )
				),
				'bg_gradient_2' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'gradient',
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_bg_gradient_2', Config::getArray( 'upload_text_options', 'bg_gradient_2' ), 'upload_text_options[bg_gradient_2]' )
				),
				'bg_gradient_3' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'gradient',
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_bg_gradient_3', Config::getArray( 'upload_text_options', 'bg_gradient_3' ), 'upload_text_options[bg_gradient_3]' )
				),
				'bg_gradient_4' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'gradient',
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_bg_gradient_4', Config::getArray( 'upload_text_options', 'bg_gradient_4' ), 'upload_text_options[bg_gradient_4]' )
				),
				'bg_image' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'image',
					'option_type' => 'text',
					'option_value' => '<input type="file" name="upload_text_bg_image" />',
				),
				'opt_html_bg_files' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'image' && Config::getArray( 'upload_text_display_type' ) == 'display_as_image',
					'content' => '<div class="option-cnt admin-thumbs-middle">' . getTextBgFiles() . '</div>',
				),
				'user_bg' => array(
					'option_depends' => array( 
						'upload_text_display_type' => 'display_as_image'
					)
				),
				'user_bg_fit' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'image' && Config::getArray( 'upload_text_display_type' ) == 'display_as_image', 
					'option_select_values' => array(
						'full'		 => __( 'upload_text_options_user_bg_fit_full' ),
						'fit_text' => __( 'upload_text_options_user_bg_fit_fit_text' ),
						'fill_color' => __( 'upload_text_options_user_bg_fit_fill_color' )
					)
				),
				'user_bg_fit_fill_color' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'user_bg_fit' ) == 'fill_color' && Config::getArray( 'upload_text_display_type' ) == 'display_as_image',
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_user_bg_fit_fill_color', Config::getArray( 'upload_text_options', 'user_bg_fit_fill_color' ), 'upload_text_options[user_bg_fit_fill_color]' )
				),
				'image_shadow' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'image',
					'option_type' => 'range',
					'option_min' => 0,
					'option_max' => 100
				),
				'shadow' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'bg' ) == 'image',
				),
				'shadow_color' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'shadow' ) == 1,
					'option_type' => 'text',
					'option_value' => colorPicker( 'upload_text_shadow_color', Config::getArray( 'upload_text_options', 'shadow_color' ), 'upload_text_options[shadow_color]' )
				),
				'shadow_blur' => array(
					'option_depends' => Config::getArray( 'upload_text_options', 'shadow' ) && Config::getArray( 'upload_text_display_type' ) == 'display_as_image',
					'option_type' => 'range',
					'option_min' => 0,
					'option_max' => 100
				),
				'padding' => array(
					'option_type' => 'input',
					'option_lenght' => 5,
					'option_depends' => Config::getArray( 'upload_text_display_type' ) == 'display_as_text', 
				),
				'length' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 2000
				),
				'set_min_height' => array(
					'option_type' => 'input',
					'option_lenght' => 5
				)
			)
		),
		'upload_margin' => array(
			'option_is_array' => getMarginOption( 'text' )
		)
	);
	

	
	$ips_options['options_watermark'] = array( 
		'watermark' => array( 
			'option_new_block' => __( 'watermark_title' ),
			'option_select_values' => array(
				'off'		=> __( 'watermark_off' ),
				'as_image'	=> __( 'watermark_as_image' ),
				'as_text'	=> __( 'watermark_as_text' )
			)
		),
		
		

			

		'watermark_options' => array(
			'option_depends' => Config::get('watermark') != 'off',
			'option_is_array' => array(
				'text' => array(
					'option_depends' => array( 
						'watermark' => 'as_text'
					),
					'option_type' => 'input',
					'option_lenght' => 40,
					'validation' => array(
						'match' => '^[\w\d\W]{1,40}$',
						'set_value' => ''
					),
				),
				'text_size' => array(
					'option_depends' => array( 
						'watermark' => 'as_text'
					),
					'option_type' => 'input',
					'option_lenght' => 2,
					'validation' => array(
						'match' => '^[0-9]{1,3}$',
						'set_value' => '24'
					),
				),
				'text_font' => array(
					'option_depends' => array( 
						'watermark' => 'as_text'
					), 
					'option_select_values' => getFonts()
				),
				'text_font_color' => array(
					'option_depends' => array( 
						'watermark' => 'as_text'
					),
					'option_type' => 'text',
					'option_value' => colorPicker( 'watermark_options_text_font_color', Config::getArray( 'watermark_options', 'text_font_color' ), 'watermark_options[text_font_color]' )
				),
				'opacity' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 100,
					'validation' => array(
						'match' => '^[0-9]{1,3}$',
						'set_value' => 40
					),
				),
				'file' => array(
					'option_depends' => array( 
						'watermark' => 'as_image'
					),
					'option_type' => 'text',
					'option_value' => '<input type="file"  name="watermark_file" />',
					'option_sprintf' => adminUploadedFile( array( 'watermark_options' => 'file' ), 'upload/system/watermark' ),
				),
				'position' => array(
					'option_select_values' => array(
						'top-left'		=> __( 'watermark_options_position_top_left' ),
						'top'			=> __( 'watermark_options_position_top_center' ),
						'top-right'		=> __( 'watermark_options_position_top_right' ),
						
						'left'			=> __( 'watermark_options_position_center_left' ),
						'center'		=> __( 'watermark_options_position_center' ),
						'right'			=> __( 'watermark_options_position_center_right' ),
						
						'bottom-left'	=> __( 'watermark_options_position_bottom_left' ),
						'bottom'		=> __( 'watermark_options_position_bottom_center' ),
						'bottom-right'	=> __( 'watermark_options_position_bottom_right' )
					)
				),
				'position_x' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 100,
					'validation' => array(
						'match' => '^[0-9\-]{1,3}$',
						'set_value' => '1'
					),
				),
				'position_y' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 100,
					'validation' => array(
						'match' => '^[0-9\-]{1,3}$',
						'set_value' => '1'
					),
				),
				'position_absolute'
			)
		),
		
		
		
		
		
		
		'watermark_transparent' => array(
			'option_new_block' => true,
			'option_is_array' => array(
				'activ',
				'opacity' => array(
					'option_type' => 'range',
					'option_min' => 1,
					'option_max' => 100,
					'option_depends' => Config::getArray( 'watermark_transparent', 'activ' ) == 1,
				),
				'file' => array(
					'option_type' => 'text',
					'option_value' => '<input type="file" name="watermark_transparent" />',
					'option_sprintf' => adminUploadedFile( array( 'watermark_transparent' => 'file' ), 'upload/system/watermark' ),
					'option_depends' => Config::getArray( 'watermark_transparent', 'activ' ) == 1,
				),
				'angle' => array(
					'option_type' => 'range',
					'option_min' => -50,
					'option_max' => +50,
					'option_depends' => Config::getArray( 'watermark_transparent', 'activ' ) == 1,
				),
			)
		),
		
	);

	
	$ips_options['options_social'] = array(
		'social_plugins' => array(
			'option_is_array' => array(
				'share_img' => array(
					'option_type' => 'checkbox'
				),
				'share' => array(
					'option_new_block' => __( 'social_plugins_share_block' ),
					'option_set_text' => 'social_plugins_show_on_list'
				),
				'share_page' => array(
					'option_set_text' => 'social_plugins_show_on_page'
				),
				
				'like_page' => array(
					'option_new_block' => __( 'social_plugins_like_block' ),
					'option_set_text' => 'social_plugins_show_on_page'
				), 
				'like' => array(
					'option_set_text' => 'social_plugins_show_on_list'
				),
				'like_page_big' => array(
					'opt_allowed_templates' => array( 'vines' ),
				),
				'like_add_share',
				'like_template' =>  array( 
					'option_select_values' => array(
						'box_count' => __( 'social_plugins_like_template_box_count' ),
						'button_count' => __( 'social_plugins_like_template_button_count' ),
						'button' => __( 'social_plugins_like_template_button' )
					)
				),
				
				
				
				'google_page' => array(
					'option_new_block' => __( 'social_plugins_google_block' ),
					'option_set_text' => 'social_plugins_show_on_page'
				),
				'google' => array(
					'option_set_text' => 'social_plugins_show_on_list'
				), 
				'size_google' =>  array( 
					'option_value_as_key' => true,  
					'option_select_values' => array(
						'small', 'medium', 'tall'
					)
				),
				
				'nk_page' => array(
					'option_new_block' => __( 'social_plugins_nk_block' ),
					'option_set_text' => 'social_plugins_show_on_page'
				), 
				'nk' => array(
					'option_set_text' => 'social_plugins_show_on_list'
				), 
				'nk_type' => array( 
					'option_ddslick' => true,  
					'option_select_values' => array(
						2 => array( 
							'img' => 'http://cdn.iprosoft.pro/ips-cms/admin/nk_type_2.png'
						), 
						3 => array( 
							'img' => 'http://cdn.iprosoft.pro/ips-cms/admin/nk_type_3.png'
						), 
						4 => array( 
							'img' => 'http://cdn.iprosoft.pro/ips-cms/admin/nk_type_4.png'
						), 
						5 => array( 
							'img' => 'http://cdn.iprosoft.pro/ips-cms/admin/nk_type_5.png'
						)
					)
				),
				'nk_scheme' => array(  
					'option_select_values' => array(
						0 => __( 'social_plugins_nk_scheme_light' ),
						1 => __( 'social_plugins_nk_scheme_dark' )
					)
				),
				
				
				'twitter_page' => array(
					'option_new_block' => __( 'social_plugins_twitter_block' ),
					'option_set_text' => 'social_plugins_show_on_page'
				),
				'twitter' => array(
					'option_set_text' => 'social_plugins_show_on_list'
				), 
				'twitter_page_big' => array(
					'opt_allowed_templates' => array( 'vines' ),
				),
				'scheme_font' => array(
					'option_new_block' => __( 'social_plugins_scheme_block' ),
					'option_value_as_key' => true,  
					'option_select_values' => array(
						'arial', 'lucida grande', 'segoe ui', 'tahoma', 'trebuchet ms', 'verdana'
					)
				),
				'colorscheme' => array( 
					'option_value_as_key' => true,  
					'option_select_values' => array(
						'dark', 'light'
					)
				),
				
				
				'language' => array( 
					'option_value_as_key' => true,  
					'option_select_values' => array_combine( languagesList(), languagesList() )
				)
				
			)
		),
		
		
	);
	
	
	$ips_options['options_categories'] = array(
		'categories_option', 
		
		'categories_options' => array(
			'option_display_name' => false,
			'option_is_array' => array(
				'sorting' => array( 
					'option_depends' => array( 
						'categories_option' => 1
					),
					'option_select_values' => array(
						'category_name'	=> __( 'categories_of_files_sorting_normal' ),
						'id_category'	=> __( 'categories_of_files_sorting_id' ),
					),
				),
				'thumb_size' => array(
					'option_depends' => array( 
						'categories_option' => 1
					),
					'option_type' => 'range',
					'option_min' => 10,
					'option_max' => 250
				),
			)
		)
	);
	$ips_options['options_widgets'] = array(
		'widget_confirm_email_reminder' => array(
			'opt_allowed_templates' => array( 'pinestic'),
			'option_depends' => Config::get( 'user_account', 'email_activation' ),
		),
		/* 
		'widget_app_friends' => array(
			'opt_title' => 'Widget Znajomi na Facebook pod menu',
			'opt_descript' => 'Widget Znajomi na Facebook pod menu'
		), 
		*/
		'widget_pinit_related_boards' => array(
			'opt_allowed_templates' => array( 'pinestic'),
		),
		'widget_pinit_related_pins' => array(
			'opt_allowed_templates' => array( 'pinestic'),
		),
		'widget_go_to_top' => array(),
		'module_history' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'opt_suboptions' => array(
				'module_history_actions' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'register', 'login', 'connect', 'view', 'comment', 'add', 'favorites', 'vote' 
					)
				),
			)
		),
		'widget_history_users_activity' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_depends' => array( 
				'module_history' => 1
			),
		),
		'widget_fan_box' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'widget_popular_tags' => array( 
			'opt_suboptions' => array(
				'widget_popular_tags_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'count' => array(
							'option_type' => 'range',
							'option_min' => 1,
							'option_max' => 100
						),
						'header'
					)
				),
			)
		),
		'widget_float_box' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'widget_social_share' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'widget_go_to' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'widget_search_bar' => array(),
		'widget_navigation_bottom' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'widget_navigation_bottom_box' => array( 
			'widget_depends' => array( 
				'pagin_css' => 7
			),
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'widget_url_copy' => array(),
		'widget_smilar_url' => array(),
		'widget_navigation_on_page' => array(),
		'widget_navigation_on_page_box' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'widget_top_comments' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'opt_suboptions' => array(
				'widget_top_comments_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'count' => array(
							'option_type' => 'range',
							'option_min' => 1,
							'option_max' => 50
						),
						'sort' => array(
							'option_select_values' => array(
								'comment_votes' => __( 'sort_votes' ),
								'comment_opinion' => __( 'sort_opinion' ),
							),
						),
					)
				)
			)
		),
		
		'widget_user_idle' => array(
			'opt_suboptions' => array(
				'widget_user_idle_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'time' => array(
							'option_type' => 'range',
							'option_min' => 1,
							'option_max' => 50
						),
						'files',
						'files_sort' => array(
							'option_select_values' => array(
								'votes_count' => __( 'sort_votes' ),
								'votes_opinion' => __( 'sort_opinion' ),
								'comments' => __( 'sort_comments' ),
							),
						),
						'ad',
						'ad_content' => array(
							'option_type' => 'textarea',
							'option_rows' => 3,
							'option_cols' => 55,
						),
					)
				),
			)
			
		),
		
		'widget_top_files_right' => array(
			'opt_allowed_templates' => array( 'kwejk', 'demotywator' ),
			'opt_suboptions' => array(
				'widget_top_files_right_wait' => array(),
				'widget_top_files_right_main' => array(),
			)
			
		),
		'widget_popular_tags_wait' => array(),
		'widget_file_tags' => array(),
		'widget_sort_box' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'opt_suboptions' => array(
				'widget_sort_box_social' => array()
			)
		),
		'widget_category_panel' => array(
			'widget_depends' => array( 
				'categories_option' => 1
			),
			'opt_suboptions' => array(
				'widget_category_panel_view' => array(
					'option_select_values' => array(
						'images' => __( 'option_image' ),
						'links' => __( 'option_links' ),
					),
				),
				'widget_category_panel_view_header' => array()
			)
		),

		
		'widget_float_slides' => array(
			'opt_suboptions' => array(
				'widget_float_slides_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'slider_class' => array( 
							'option_select_values' => array(
								'3d'		=> '3D',
								'modern'	=> 'Modern',
								'shine'		=> 'Shine',
								'metal'		=> 'Metal',
							),
						),
						'facebook', 
						'google', 
						'twitter', 
						'twitter_widget_id' => array( 
							'option_type' => 'input',
							'option_lenght' => 60
						), 
					),
				),
			),
		),
		
		'widget_cookie_info' => array(
			'opt_suboptions' => array(
				'widget_cookie_info_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'template' => array( 
							'option_select_values' => array(
								'fixed'		=> 'Fixed',
								'bottom'	=> 'Bottom'
							),
						)
					)
				)
			)
		),
		'widget_popup' => array(
			'opt_suboptions' => array(
				'widget_popup_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'close' => array(),
						'close_timer' => array(
							'option_type' => 'input',
							'option_lenght' => 10,
						),
						'box_like' => array(),
						'timeout' => array(
							'option_type' => 'input',
							'option_lenght' => 10,
						),
						'delay' => array(
							'option_type' => 'input',
							'option_lenght' => 10,
						),
						'title' => array(
							'option_type' => 'input',
							'option_lenght' => 10,
						),
						'message' => array(
							'option_type' => 'textarea',
							'option_lenght' => 10,
							'option_rows' => 3,
							'option_cols' => 55,
						),
						'theme' => array(
							'option_select_values' => array(
								'normal' => __( 'option_normal' ),
								'gradient' => __( 'option_gradient' ),
								'info' => __( 'option_blue' ),
								'light' => __( 'option_light' ),
							),
							
						),
					)
				)
			)
		),
		'widget_see_more' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'opt_suboptions' => array(
				'widget_see_more_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'layout' => array(
							'opt_allowed_templates' => array( 'kwejk', 'demotywator', 'vines', 'gag' ),
							'option_select_values' => array(
								'one' => __( 'option_one' ),
								'two' => __( 'option_two' ),
								'three' => __( 'option_three' ),
							),
						),
						'source' => array(
							'option_select_values' => array(
								'waiting' => __( 'common_waiting' ),
								'main' => __( 'common_main' ),
								'top' => __( 'option_top' ),
								'tags' => __( 'option_tags' ),
								'rand' => __( 'option_rand' )
							),
							
						),
						'limit' => array(
							'option_type' => 'input',
							'option_lenght' => 10,
						),
						'image' => array(
							'option_depends' => defined('IPS_SELF'),
						)
					)
				)
			)
		),
		'widget_best_files' => array(
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'opt_suboptions' => array(
				'widget_best_files_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'source' => array(
							'option_select_values' => array(
								'wait' => __( 'common_waiting' ),
								'main' => __( 'common_main' ),
								'new' => __( 'option_new' ),
								'rand' => __( 'option_rand' )
							)
						),
						'type' => array(
							'option_select_values' => array(
								'all' => __( 'common_all' ),
								'image' => __( 'option_image' ),
								'video' => __( 'option_video' ),
								'gallery' => __( 'option_gallery' ),
								'article' => __( 'option_article' )
							)
						),
						'limit' =>  array(
							'option_type' => 'input',
							'option_lenght' => 5,
						),
						'button' => array(
							'option_select_values' => array(
								'like'	=> __( 'option_like' ),
								'share' => __( 'option_share' )
							),
							
						),
						'limit' => array(
							'option_type' => 'input',
							'option_lenght' => 5,
						),
						'interval' => array(
							'option_type' => 'input',
							'option_lenght' => 5,
						),
						'close' => array(
							'option_type' => 'input',
							'option_lenght' => 5,
						),
					)
				)
			)
		),
		'widget_only_adult' => array(),
		'widget_small_posts' => array(
			'opt_allowed_templates' => array( 'gag', 'bebzol', 'vines' ),
		),
		'widget_popular_posts' => array(
			'opt_allowed_templates' => array( 'gag', 'bebzol', 'vines' ),
			'opt_suboptions' => array(
				'widget_popular_posts_options' => array(
					'option_display_name' => false,
					'option_is_array' => array(
						'source' => array(
							'option_select_values' => array(
								'wait' => __( 'common_waiting' ),
								'main' => __( 'common_main' ),
								'new' => __( 'option_new' ),
								'rand' => __( 'option_rand' )
							)
						),
						'type' => array(
							'option_select_values' => array(
								'all' => __( 'common_all' ),
								'image' => __( 'option_image' ),
								'video' => __( 'option_video' ),
								'gallery' => __( 'option_gallery' ),
								'article' => __( 'option_article' )
							)
						),
						'limit' =>  array(
							'option_type' => 'input',
							'option_lenght' => 5,
						)
					)
				)
			)
		),
		
		'widget_personalize' => array(
			'opt_allowed_templates' => array( 'gag', 'vines', 'bebzol' )
		),
		'widget_side_action_button' => array(
			'opt_allowed_templates' => array( 'gag', 'vines', 'bebzol' )
		)
	);
	$ips_options['options_privileges'] = array(
		'mod_privileges' => array(
			'option_type' => 'checkbox',
			'option_is_array' => array(
				'main', 'waiting', 'archive', 'archive-wait', 'contest_delete_caption', 'delete_comment', 'delete', 'adult', 'category_change', 'private-wait', 'facebook', 'autopost', 'social_lock'
			)
		),
	);
	
	$ips_options['cache_options'] = array(
		'system_cache' => array(
			'option_is_array' => array(
				'config',
				'config_expiry' => array(
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_set_text' => 'system_cache_expiry',
					'validation' => array(
						'match' => '^[0-9]{1,}$',
						'set_value' => 3600
					),
				),
				'files' => array(
					'option_new_block' => __( 'descript_block_file' ),
				),
				'files_expiry' => array(
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_set_text' => 'system_cache_expiry',
					'validation' => array(
						'match' => '^[0-9]{1,}$',
						'set_value' => 3600
					),
				),
				'comments' => array(
					'option_new_block' => __( 'descript_block_comments' ),
				),
				'comments_expiry' => array(
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_set_text' => 'system_cache_expiry',
					'validation' => array(
						'match' => '^[0-9]{1,}$',
						'set_value' => 3600
					),
				),
				'css_js' => array(
					'option_new_block' => __( 'descript_block_js_css' ),
				),
				'css_js_expiry' => array(
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_set_text' => 'system_cache_expiry',
					'validation' => array(
						'match' => '^[0-9]{1,}$',
						'set_value' => 3600
					),
				),
				'templates' => array(
					'option_new_block' => __( 'descript_block_html' ),
				),
				'templates_expiry' => array(
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_set_text' => 'system_cache_expiry',
					'validation' => array(
						'match' => '^[0-9]{1,}$',
						'set_value' => 3600
					),
				),
			)
		),
		
		'img_preloader' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' ),
			'option_new_block' => __( 'descript_block_other' ),
		),
		'gif_auto_play' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
		'online_stats' => array( 
			'opt_not_allowed_templates' => array( 'pinestic' )
		),
	);
	
	
	if( defined( 'IPS_SELF' ) )
	{
		$ips_options['options_widgets']['widget_popup']['opt_suboptions']['widget_popup_options']['option_is_array']['theme']['option_select_values']['simple'] = 'Simple';
	}
	
	return $ips_options;
