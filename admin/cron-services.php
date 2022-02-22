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

	require_once( IPS_ADMIN_PATH .'/cron-functions.php');
	require_once( IPS_ADMIN_PATH .'/cron-functions.php');
	
	echo admin_caption( 'action_cron' );
		
	if( !isset( $_GET['add'] ) && !isset( $_GET['edit'] ) && !isset( $_GET['history'] ) )
	{
		$pagin = new Pagin_Tool;	
		echo $pagin->wrap()->addJS( 'crons', count( ips_cron_array() ) )->addMessage('')->get();	
	}
	
	if( isset( $_GET['clear_history'] ) )
	{
		ips_cron_history_clear();
		ips_admin_redirect('cron');
	}
	
	if( isset( $_GET['delete'] ) )
	{
		$msg = ips_cron_delete( $_GET['timestamp'], $_GET['delete'] );
		
		ips_admin_redirect('cron', false, array(
			'info' => 'cron_task_removed'
		));

	}
	
	if( isset( $_GET['edit'] ) && !empty( $_POST )  )
	{
		$msg = ips_cron_change( $_POST, $_GET['timestamp'], $_GET['edit'] );
		
		if( $msg === true )
		{
			ips_admin_redirect('cron', false, array(
				'info' => 'cron_task_saved'
			));
		}
		else
		{
			echo $msg;
		}
	}
	elseif( isset( $_POST['timestamp'] ) )
	{
		$msg = ips_save_options( $_POST );
		
		if( $msg === true )
		{
			ips_admin_redirect('cron', false, array(
				'info' => 'cron_task_saved'
			));
		}
		else
		{
			echo $msg;
		}	
	}
	

	if( ( isset( $_GET['edit'] ) || isset( $_GET['add'] ) ) && ( isset( $_POST['cron'] ) || isset( $_GET['cron'] ) ) )
	{
		
		$row = $_POST;
		if( isset( $_GET['edit'] ) && isset( $_GET['timestamp'] ) )
		{
			$row = array_merge( ips_cron_get( $_GET['edit'], $_GET['timestamp'] ), $_POST );
		}
	
		$cron_type = isset( $_POST['cron'] ) ? $_POST['cron'] : $_GET['cron'];
		
		if( ! in_array( $cron_type, array_keys(ips_cron_available_func()) ) )
		{
			ips_admin_redirect( 'cron', 'add=true', array(
				'info' => 'cron_task_type'
			));
		}
		

		echo '
		<link rel="stylesheet" href="css/smoothness/datepicker.css" type="text/css" />
		<script src="js/jquery.ui.datepicker-pl.js" type="text/javascript"></script>
		<script src="js/jquery.ui.datepicker-godziny.js" type="text/javascript"></script>
		<script type="text/javascript">
		$(function() {
			$.datepicker.setDefaults( $.datepicker.regional[ "pl" ] );
			$( ".timestamp_input input" ).datetimepicker({
				timeFormat: "hh:mm",
				stepSecond: 60,
				minDate: 0,
				dateFormat: "yy-mm-dd",
				defaultDate: "'.( isset( $row['timestamp'] ) ? ( is_numeric( $row['timestamp'] ) ? date( "Y-m-d H:i", $row['timestamp'] ) : $row['timestamp']  ): date( "Y-m-d H", strtotime("now + 1 hour") ) . ':00' ).'"
			});
		});
		</script>
		<form enctype="multipart/form-data" action="" method="post">
		';
		
		
		$options = array(
			'timestamp' => array(
				'current_value' => ( isset( $row['timestamp'] ) ? ( is_numeric( $row['timestamp'] ) ? date( "Y-m-d H:i", $row['timestamp'] ) : $row['timestamp']  ) : date("Y-m-d H", strtotime("now + 1 hour")).':00' ),
				'option_set_text' => 'cron_start_timestamp',
				'option_type' => 'input',
				'option_lenght' => 10,
				'option_css' => 'timestamp_input'
			),
			'schedule' => array(
				'current_value' => ( isset( $row['schedule'] )  ? $row['schedule'] : '' ),
				'option_select_values' => array(
					'hourly' => __('cron_hourly'),
					'daily' => __('cron_daily'),
					'weekly' => __('cron_weekly')
				),
				'option_set_text' => 'cron_schedule'
			)
		);
		
		
		if( $cron_type == 'move-to-main' )
		{
			$options = array_merge( $options, array(
				'count' => array(
					'current_value' => ( isset( $row['count'] ) ? $row['count'] : 1 ),
					'option_set_text' => 'count_move_count',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'votes_count' => array(
					'current_value' => ( isset($row['votes_count']) ? $row['votes_count'] : 1 ),
					'option_set_text' => 'count_min_opinion',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'share' => array(
					'current_value' => ( isset( $row['share'] ) ? $row['share'] : 1 ),
					'option_set_text' => 'count_min_shares',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'import_sorting' => array(
					'current_value' => ( isset( $row['import_sorting'] )  ? $row['import_sorting'] : 'desc' ),
					'option_select_values' => array(
						'desc' => __('import_sorting_desc'),
						'asc' => __('import_sorting_asc'),
						'rand' => __('import_sorting_rand')
					)
				)
			));
			
			$info_message = __('cron_tasks_info_1') ;
		}
		elseif( $cron_type == 'import' )
		{
			
			
			$options = array_merge( $options, array(
				'count' => array(
					'current_value' => ( isset($row['count']) ? $row['count'] : 1 ),
					'option_set_text' => 'cron_import_files_count',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'import_directory' => array(
					'current_value' => ( isset($row['import_directory']) ? $row['import_directory'] : '' ),
					'option_set_text' => 'cron_import_server_catalog',
					'option_type' => 'input',
					'option_lenght' => 20
				),
				'users' => array(
					'current_value' => ( isset($row['users']) ? $row['users'] : '' ),
					'option_set_text' => 'import_usernames',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'import_sorting' => array(
					'current_value' => ( isset( $row['import_sorting'] )  ? $row['import_sorting'] : 'desc' ),
					'option_select_values' => array(
						'desc' => __('import_sorting_desc'),
						'asc' => __('import_sorting_asc'),
						'rand' => __('import_sorting_rand')
					)
				),
				'import_category' => array(
					'current_value' => '',
					'option_set_text' => 'category',
					'option_type' => 'text',
					'option_value' => '<select name="import_category"><option value="0" selected="selected">-----</option>'.Categories::categorySelectOptions( ( isset($row['import_category']) ? $row['import_category'] : false ) ).'</select>'
				),
				'import_default_name' => array(
					'current_value' => ( isset($row['import_default_name']) ? $row['import_default_name'] : '' ),
					'option_set_text' => 'import_folders_default_name',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'import_add_to' => array(
					'current_value' => ( isset( $row['import_add_to'] ) ? $row['import_add_to'] : 'wait' ),
					'option_set_text' => 'import_destination',
					'option_select_values' => array(
						'wait' => __('common_waiting'),
						'main' => __('common_main')
					)
				),
				'upload_tags' => array(
					'current_value' => ( isset( $row['upload_tags'] ) ? $row['upload_tags'] : '' ),
					'option_set_text' => 'import_additional_tags',
					'option_type' => 'input',
					'option_lenght' => 10
				),
			));
			
			

			
			$info_message = __('cron_tasks_info_2') ;
		}
		elseif( $cron_type == 'import_drafts' )
		{
			
			$options = array_merge( $options, array(
				'count' => array(
					'current_value' => ( isset($row['count']) ? $row['count'] : 1 ),
					'option_set_text' => 'cron_import_files_count',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'import_add_to' => array(
					'current_value' => ( isset( $row['import_add_to'] ) ? $row['import_add_to'] : 'wait' ),
					'option_set_text' => 'import_destination',
					'option_select_values' => array(
						'wait' => __('common_waiting'),
						'main' => __('common_main')
					)
				)
			));

			$info_message = __s( 'cron_tasks_info_3', getDrafts( 'count' ) ) ;
		}
		elseif( $cron_type == 'archive' )
		{
			
			
			$options = array_merge( $options, array(
				'count' => array(
					'current_value' => ( isset($row['count']) ? $row['count'] : 1 ),
					'option_set_text' => 'cron_archive_period',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'onlywait' => array(
					'current_value' => ( isset($row['onlywait']) ? $row['onlywait'] : 1 ),
					'option_set_text' => 'only_waiting_room'
				)
			));
			

			$info_message = __('cron_tasks_info_4');
		}
		elseif( $cron_type == 'archive_wait' )
		{
			
			$options = array_merge( $options, array(
				'count' => array(
					'current_value' => ( isset($row['count']) ? $row['count'] : 1 ),
					'option_set_text' => 'count_move_count',
					'option_type' => 'input',
					'option_lenght' => 10
				)
			));
			
			$info_message = __('cron_tasks_info_4');
		}
		elseif( $cron_type == 'fanpage' )
		{
			$fanpages = Config::getArray( 'apps_fanpage_array' );
			
			$api_tokens = array_filter( $fanpages, function( $value ){
				return isset( $value['api_token'] );
			});
			
			if( !is_array( $fanpages ) || empty( $api_tokens ) )
			{
				ips_admin_redirect( 'fanpage', 'action=settings', array(
					'alert' => __( 'fanpage_configuration_problems' )
				));
			}
			
			$options = array_merge( $options, array(
				'type' => array(
					'current_value' => ( isset( $row['type'] ) ? $row['type'] : 'post' ),
					'option_set_text' => 'fanpage_post_type',
					'option_css' => 'fanpage_post_type',
					'option_select_values' => array(
						'post' => __('fanpage_post_type_post'),
						'upload' => __('fanpage_post_type_upload')
					)
				),
				'fanpage_id' => array(
					'current_value' => ( isset( $row['fanpage_id'] ) ? $row['fanpage_id'] : Config::get( 'apps_fanpage_default_id' ) ),
					'option_set_text' => 'select_an_fanpage',
					'option_type' => 'text',
					'option_css' => 'fanpage_id_change',
					'option_select_values' => array_column( $api_tokens, 'url', 'fanpage_id' ),
					'option_multiple' => true
				),
				'album_id' => array(
					'current_value' => '',
					'option_set_text' => 'fanpage_data_album_id_title',
					'option_type' => 'text',
					'option_css' => 'fanpage_type_upload album_id_container' . (  isset( $row['type'] ) && $row['type'] == 'upload' ? '' : ' display_none'),
					'option_value' => '<img width="22" height="22" src="/images/svg/spinner.svg"><script>var album_ids_edit = '.( isset( $row['album_id'] ) ? json_encode( $row['album_id'] ) : false ).'</script>'
				),
				'pick_from' => array(
					'current_value' => ( isset( $row['pick_from'] ) ? $row['pick_from'] : 'all' ),
					'option_set_text' => 'fanpage_pick_from',
					'option_select_values' => array(
						'all' => __('common_all'),
						'main' => __('common_main'),
						'wait' => __('common_waiting')
					)
				),
				'count' => array(
					'current_value' => ( isset($row['count']) ? $row['count'] : 1 ),
					'option_set_text' => 'cron_publish_facebook_count',
					'option_type' => 'input',
					'option_lenght' => 10
				)
			));

			
			$info_message = __s( 'cron_tasks_info_6', admin_url( 'fanpage', 'action=settings' ) );
		}
		elseif( $cron_type == 'clear-cache' )
		{

			
			$options = array_merge( $options, array(
				'tpl' => array(
					'option_new_block' => __('system_cache_clear_cache'),
					'current_value' => ( isset($row['tpl']) ? $row['tpl'] : 0 ),
					'option_set_text' => 'templates',
					'option_names' => 'yes_no'
				),
				'js' => array(
					'current_value' => ( isset($row['js']) ? $row['js'] : 0 ),
					'option_set_text' => 'js_and_css',
					'option_names' => 'yes_no'
				),
				'file' => array(
					'current_value' => ( isset($row['file']) ? $row['file'] : 0 ),
					'option_set_text' => 'materials',
					'option_names' => 'yes_no'
				),
				'tmp' => array(
					'current_value' => ( isset($row['tmp']) ? $row['tmp'] : 0 ),
					'option_set_text' => 'temporary_files',
					'option_names' => 'yes_no'
				),
				'mysql' => array(
					'current_value' => ( isset($row['mysql']) ? $row['mysql'] : 0 ),
					'option_set_text' => 'system_cache_clear_mysql',
					'option_names' => 'yes_no'
				)
			));

		}
		
		if( $caption = ips_cron_func_text( $cron_type ) )
		{
			echo admin_caption( $caption );
		}
		
		echo displayArrayOptions( $options );
		
		if( isset( $info_message ) )
		{
			echo '<div class="div-info-message">
				<p>' . $info_message . '</p>
			</div>';
		}
		
		
		echo '
			<input type="hidden" name="cron" value="'.$cron_type.'" />
			<input type="submit" class="button" value="'.( isset( $_GET['edit'] ) ? __('add_task') : __('save_changes') ).'" />
			
		</form>
		';
		
	}
	elseif( isset( $_GET['edit'] ) || isset( $_GET['add'] ) )
	{

		echo admin_caption( 'cron_tasks_type' );
		
		$functions = ips_cron_available_func();
		
		foreach( $functions as $function => $data )
		{
			$functions[$function] = $data['text'];
		}
		
		echo responsive_menu( $functions, admin_url( 'cron', 'add=true&cron=' ) );
	}
	elseif( isset( $_GET['history'] ) )
	{
		$cron = ips_cron_history();
		if( empty($cron) )
		{
			echo __('empty_task_history');
		}
		else
		{
			foreach( $cron as $zadanie )
			{
				echo date( "Y-m-d H:i:s", $zadanie['time'] ) . ' -- ' . $zadanie['info'] . '<br />';
			}
		}
		echo '
		<br /><br />
		<a class="button" href="' . admin_url( 'cron' ) . '">'.__('common_back').'</a>
		<a class="button" href="' . admin_url( 'cron', 'histroy=true&clear_history' ) . '">'.__('clear_cron_history').'</a>';
	}
	else
	{
		echo '
		<a class="button" href="' . admin_url( 'cron', 'add=true' ) . '">'.__('add_task').'</a>
		<a class="button" href="' . admin_url( 'cron', 'history=true' ) . '">'.__('history').'</a>
		';
	}
	$key = md5(AUTH_KEY);
	echo '
	
	<div class="div-info-message">
		' . __s('cron_tasks_info_5', ABS_URL . 'ips-cron.php?cron=' . $key, ABS_URL . 'ips-cron.php?cron=' . $key ) . '
	</div>';
?>