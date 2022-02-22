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
	require_once( dirname(__FILE__) . '/config.php');
	
	if( !defined('USER_ADMIN') || !USER_ADMIN ) die ("Hakier?");
	
	include( IPS_ADMIN_PATH .'/admin-functions.php');
	include( IPS_ADMIN_PATH .'/cron-functions.php'); 
	
	$load_action = $_GET['action'];
	
	$pages = xy( IPS_ACTION_PAGE, 20 );
	
	$limit = ' LIMIT ' . $pages[1] . ',' . $pages[2];

	$sort_by_field = isset( $_GET['sort_by'] ) && !empty( $_GET['sort_by'] ) ? $_GET['sort_by'] : false;

	$order = isset( $_GET['order'] ) && !empty( $_GET['order'] ) ? $_GET['order'] : '';


	$order_by = $sort_by_field ? array( $sort_by_field => $order ) : false;


	echo '<script type="text/javascript" src="js/tableSorter.js"></script> 
	<script type="text/javascript">
	$(document).ready(function(){ 
		if( $( ".features-table" ).length > 0 )
		{
			setVarsForm("ajax_action=' . $load_action . '");
			
			$(".features-table").each(function(){
				var options = {
					selectorHeaders : "thead th",
					selectorHeadersName : "thead"
				};
				if( $(this).find("th").first().find("input").lenght > 0  )
				{
					options.excludeHeader = [0];
				}
				$(this).tablesorter( options );
			});
		}
	}); 
		
	$(function() {
		if( $( ".draggable" ).length > 0 )
		{
			$(".draggable").each(function(){
				
				$(this).sortable({
					revert: true,
					start: function( event, ui ) {
						getHookPriority( event, ui );
					},
					stop: function( event, ui ) {
						updateHookStatus( event, ui );
					}
				});
				$(this).draggable({
					connectToSortable: $(this),
					helper: "clone",
					revert: "invalid"
				});
			});
		}
	});	
	</script>';

switch( $load_action )
{
	case "mailing_service":
		
		$mailing = PD::getInstance()->select( 'mailing_service' );
		
		if( empty( $mailing ) )
		{
			echo '<h4 class="caption">' . __('nothing_found') . '</h4>';
			return false;
		}
		
		$columns = array( __('mailing_subject_title'), __('mailing_only_adult_title'), __('mailing_activ_status_title'), __('mailing_status'), __('mailing_users') );
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		
		foreach( $mailing as $fetchnow )
		{
			
			echo '<tr id="featured-' . $fetchnow['mailing_id'] . '" class="featured-hover ui-state-default">';
			$fields = array();
			
			
			$data = '<div class="features-table-actions">';
			if( $fetchnow['status'] == 'send' )
			{
			}
			elseif( $fetchnow['status'] != 'sending' )
			{
				$data .= '
				<span>
					<a href="' . admin_url( 'mailing', 'action=send&mailing_id=' . $fetchnow['mailing_id'] ) . '">' . __('mailing_start') . '</a> | 
				</span>
				<span>
					<a href="' . admin_url( 'mailing', 'action=edit&mailing_id=' . $fetchnow['mailing_id'] ) . '">' . __('common_edit') . '</a> | 
				</span>';
			}
			else
			{
				$data .= '
				<span>	
					<a href="' . admin_url( 'mailing', 'action=stop&mailing_id=' . $fetchnow['mailing_id'] ) . '">' . __('field_stop') . '</a>
				</span> |';	
			}
			
			$data .= '
				<span>	
					<a href="' . admin_url( 'mailing', 'action=delete&mailing_id=' . $fetchnow['mailing_id'] ) . '">' . __('common_delete') . '</a>
				</span>
			</div>';
			
			
			$fields[] = '<span>' . $fetchnow['subject'] . '</span>' . $data;
			
			$fields[] = ( $fetchnow['only_adult'] == 1 ? __('yes') : __('no')  );	
			
			$where = array();
	
			switch( $fetchnow['activ_status'] )
			{
				case '0':
					$fields[] = __('mailing_activ_status_title_unactiv');	
					$where[] = 'activ = 0';
				break;
				case '1':
					$fields[] = __('mailing_activ_status_title_acitv');	
					$where[] = 'activ = 1';
				break;
				default:
					$fields[] = __('common_all');	
				break;
			}
			
			$fields[] = __('mailing_status_' . $fetchnow['status'] );	
			
			
		
			if( $fetchnow['only_adult'] == 1 )
			{
				$where[] = "user_birth_date < '" . date( 'Y-m-d', strtotime('-18 years') ) . "'";
			}
			
			$cnt = $PD->cnt( 'users', ( !empty( $where ) ? implode( ' AND ', $where ) : false ) );
			
		
		
			$fields[] = __s('mailing_send_info', $fetchnow['users_send'], $fetchnow['users_not_send'], $cnt );	
			
			echo generateTable( 'body', $fields ) . '</tr>';
			
		}
		
	echo '</tbody></table>';	
	break;
	
	case 'fanpage_urls':
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		if( empty( $fanpages ) )
		{
			echo '<h4 class="caption">' . __('none_bloks_found') . '</h4>';
			return false;
		}
			
		$columns = array( __('apps_fanpage_list_title'), __('fanpage_default'), __('fanpage_api_is_set'), __('fanpage_id')  );


		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		
		$default_fanpage = Config::get( 'apps_fanpage_default' );
		
		foreach( $fanpages as $key => $fetchnow )
		{
			$fields = array();
			
			
			echo '<tr class="featured-hover">';
			
			$fields[] = '<span style="font-size:1.2em;padding:1%"><a href="' . $fetchnow['url'] . '" target="_blank">' .$fetchnow['url'] . '</a></span> 
				<div class="features-table-actions">
					<span><a href="' . ABS_URL.'inc/plugins/api-facebook.php?fanpage_id=' . $fetchnow['fanpage_id'] . '" target="_blank">API Facebook</a> | </span>
					<span><a href="' . admin_url( 'fanpage', 'action=settings&default_key=' . $key . '' ) . '">' . __('fanpage_default') . '</a> | </span>
					<span><a href="' . admin_url( 'fanpage', 'action=settings&delete_key=' . $key . '' ) . '">' . __('common_delete') . '</a></span>
				</div>';
			
			$fields[] = md5( $default_fanpage ) == $key ? __('yes') : __('no');
			
			$fields[] = isset( $fetchnow['api_token'] ) == $key ? __('yes') : __('no');
			
			$fields[] = $fetchnow['fanpage_id'];

			echo generateTable( 'body', $fields ). '</tr>';
		}
		echo '</tbody></table>';

	break;
	
	
	case "hooks":
		$blocks = Config::getArray('hooks_actions_registry');
		
		if( empty( $blocks ) )
		{
			echo '<h4 class="caption">' . __('none_bloks_found') . '</h4>';
			return false;
		}
		
		$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('hooks_block_title'), __('hooks_block_function'), __('hooks_block_priority'), __('hooks_block_visibility') );
		if( isset( $blocks['load'] ) )
		{
			unset( $blocks['load'] );
		}
		foreach( $blocks as $hook )
		{
			echo '
			<div class="caption_small on-table">'. __( 'hook_' . $hook[0]['hook'] ) . '</div>
			<table class="features-table">
			<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
			<tfoot><tr><th colspan="5"></th></tr></tfoot>
			<tbody class="draggable">';

			foreach( $hook as $fetchnow )
			{
				echo '<tr id="featured-' . $fetchnow['key'] . '" class="featured-hover ui-state-default" data-hook="' . $fetchnow['hook'] . '">';
				$fields = array();
				
				$ids_field = '';
				if( isset( $fetchnow['params']['title'] ) )
				{
					$ids_field .= '<input type="checkbox" class="checkoption" name="ids[]" value="' . $fetchnow['key'] . '" data-hook="' . $fetchnow['hook'] . '" />';
				}
				
				$ids_field .= '<input type="hidden" class="hook_option" name="ids[]" value="' . $fetchnow['key'] . '" data-hook="' . $fetchnow['hook'] . '" />';
				
				$fields[] = $ids_field;		
				
				
				//$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="' . $fetchnow['key'] . '" data-hook="' . $fetchnow['hook'] . '" />';		
				
				/* $fields[] = '<span>' . __( 'hook_' . $fetchnow['hook'] ) . '</span> 
					<div class="features-table-actions">
					<span><a href="' . admin_url( 'hooks', 'action=edit&key='.$fetchnow['key'].'&hook=' . $fetchnow['hook'] . '' ) . '">' . __('common_edit') . '</a></span>
					</div>'; */
				
				$fields[] = '<span>' . ( isset( $fetchnow['params']['title'] ) ? $fetchnow['params']['title'] : hookTitle( $fetchnow['function'], $fetchnow['params'] ) ) . '</span> 
					<div class="features-table-actions">
					<span><a href="' . admin_url( 'hooks', 'action=edit&key='.$fetchnow['key'].'&hook=' . $fetchnow['hook'] . '' ) . '">' . __('common_edit') . '</a></span>
					</div>';
				
				$fields[] = $fetchnow['function'];	
				//$fields[] = var_export( $fetchnow['params'], true );
				
				
				
				$fields[] = '<span class="item-priority">' . ceil( $fetchnow['priority'] ) . '</span>';	
				
				$fields[] = ( isset( $fetchnow['params']['visibility'] ) ? ( $fetchnow['params']['visibility'] == 1 ? __('yes') : __('no')  ) : __('yes') );
				echo generateTable( 'body', $fields ) . '</tr>';
			}
		
			echo '</tbody></table>';	
		}
		echo actionsButtons(array(
			'remove_selected' => 'delete_hook',
			'hook_save_selected' => 'save_hook_position'
		));
		
		
	break;
	case "categories":
		
		$count = $PD->from( IPS__FILES . ' up' )->where( 'up.category_id = cat.id_category' )->fields( array(
			'COUNT(*)' 
		) )->getQuery();
			
		$res = $PD->from( 'upload_categories cat' )->fields( array( 
			'*', 
			'( '. $count .' ) AS `added`' )
		)->orderBy( $order_by )->limit( $pages )->get();
		
		if( empty( $res ) )
		{
			echo '<h4 class="caption">' . __('none_categories_added') . '</h4>';
			return false;
		}
		
		$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('field_name'), __('field_adult') );
		if( Config::get('services_premium') && Config::getArray( 'services_premium_options', 'category' ) ) 
		{
			$columns[] = __('field_premium');
		}
		$columns[] = __('field_thumb');
		$columns[] = __('field_added');
		
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		

	foreach( $res as $fetchnow )
	{
		$fields = array();
		
		
		echo '<tr id="featured-'.$fetchnow['id_category'].'" class="featured-hover ' . ( $fetchnow['is_default_category'] ? 'default_category' : '') . '">';
		$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id_category'].'" />';		
		
	
		$fields[] = '<span id="category_name_'.$fetchnow['id_category'].'" class="editText">' .$fetchnow['category_name'] . '</span> 
			<div class="features-table-actions">
				<span><a href="' . admin_url( 'categories', 'category_action=edit&id_category='.$fetchnow['id_category'] ) . '">' . __('common_edit') . '</a> | </span>
				<span><a href="' . admin_url( 'categories', 'category_action=move&id_category='.$fetchnow['id_category'] ) . '">' . __('files_move') . '</a> | </span>
				<span><a href="'.ABS_URL.'category/'.$fetchnow['id_category'].',' . seoLink( false, $fetchnow['category_name'] ) . '" target="_blank">' . __('field_view') . '</a> | </span>
				<span><a href="' . admin_url( 'categories', 'category_action=delete&id_category='.$fetchnow['id_category'] ) . '">' . __('common_delete') . '</a></span>
			</div>';
		
		$fields[] = $fetchnow['only_adult'] == 1 ? __('yes') : __('no');
		
		if( Config::get('services_premium') && Config::getArray( 'services_premium_options', 'category' ) )
		{
			$fields[] =  $fetchnow['only_premium'] == 1 ? __('yes') : __('no'); 
		}
		$fields[] = '<img src="'. ABS_URL . 'upload/category_images/' . $fetchnow['category_image'].'">';
		$fields[] = $PD->cnt( IPS__FILES, array( 
			'category_id' => $fetchnow['id_category']
		));
		echo generateTable( 'body', $fields ). '</tr>';
	}
	echo '</tbody></table>';
	
	echo actionsButtons(array(
		'remove_selected' => 'deletecategory',
		'remove_selected_with_files' => 'deletecategoryall'
	));
	
	echo '
	<div class="div-info-message">
		<p>' . __('load_info_1') . '</p>
	</div>
	';	
		
	break;
	
	
	case "crons":
		$cron_tabs = ips_cron_array();
		
		if( is_array( $cron_tabs ) )
		{
			foreach( $cron_tabs as $timestamp => $func )
			{
				foreach( $func as $key => $fetchnow )
				{
					if( strpos( $fetchnow['function-name'], '_Admin' ) !== false )
					{
						unset( $cron_tabs[$timestamp][$key] );
					}
				}
				if( empty( $cron_tabs[$timestamp] ) )
				{
					unset( $cron_tabs[$timestamp] );
				}
			}
		}
		
		
		if( !$cron_tabs )
		{
			echo '<h4 class="caption">' . __('no_scheduled_tasks') . '</h4>';
			return false;
		}
		
		$columns = array( '', __('field_name'), __('task_scheduled'), __('task_on_schedule'), __('task_recently') ) ;	
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		
	$schedules = ips_return_schedules();
	$functions = ips_cron_available_func();
	$idx = 1;
	foreach( $cron_tabs as $timestamp => $func )
	{
		foreach( $func as $fetchnow )
		{
			
			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = $idx . ')';
			$fields[] = $functions[$fetchnow['function-name']]['text'] . '
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'cron', 'edit='.$fetchnow['id'].'&timestamp='.$timestamp.'&cron='.$fetchnow['function-name'] ) . '">' . __('common_edit') . '</a> | </span>
					<span><a href="' . admin_url( 'cron', 'delete='.$fetchnow['id'].'&timestamp='.$timestamp ) . '">' . __('common_delete') . '</a> | </span>
					<span><a href="'.ABS_URL.'ips-cron.php?cron='.md5(AUTH_KEY).'&key='.$fetchnow['id'].'&timestamp='.$timestamp.'">' . __('task_on_schedule') . '</a> </span>
				</div>';
			$fields[] = date( "Y-m-d H:i:s", $timestamp );
			$fields[] = $schedules[$fetchnow['schedule']]['text'];
			$fields[] = empty($fetchnow['last-activity']) ? __('task_waiting') : date( "Y-m-d H:i:s", $fetchnow['last-activity']);
			echo generateTable( 'body', $fields). '</tr>';
			$idx++;
		}
	}
	echo '</tbody></table>';	
		
	break;
	case "pages-list":
		
		$res = $PD->from( 'posts p' )->orderBy( $order_by )->limit( $pages )->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('no_entries') . '</h4>';
			return false;
		}
			$columns = array( __('field_id'), __('title'), __('field_date'), __('field_status'), __('field_visibility'), __('field_author') );	
			echo '
			<table class="features-table">
			<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
			<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
			<tbody>';

		foreach( $res as $fetchnow )
		{
			
			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = $fetchnow['id'];
			$fields[] = '<span id="post_title_'.$fetchnow['id'].'" class="editText">' . $fetchnow['post_title'] . '</span> 
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'pages', 'action=edit&id='.$fetchnow['id'] ) . '">' . __('common_edit') . '</a> | </span>
					<span><a href="' . admin_url( 'pages', 'action=delete&id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a> | </span>
					<span><a target="_blank" href="'.ABS_URL.'' . $fetchnow['post_type'] . '/'.$fetchnow['post_permalink'].'">' . __('field_view') . '</a></span>
				</div>';
			
			$fields[] = $fetchnow['post_date'];
			

			switch( $fetchnow['post_type'] )
			{
				case 'pages':
					$fields[] = __('field_subpage');
				break;
				case 'news':
					$fields[] = __('news_article');
				break;
				case 'posts':
					$fields[] = __('field_post');
				break;
			}
			
			$fields[] = 
				($fetchnow['post_visibility'] == "public" ? __('for_all') : '') . '
				'.($fetchnow['post_visibility'] == "private" ? __('for_members') : '');
			
			
			$fields[] = $fetchnow['post_author'];

			echo generateTable( 'body', $fields). '</tr>';
		}
		echo '</tbody></table>';
	
	
	break;
	case 'contests':
		
		$res = $PD->from( 'contests c' )->orderBy( $order_by )->limit( $pages )->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('contests_list_empty') . '</h4>';
			return false;
		}
			$columns = array( __('field_id'), __('title'), __('version'), __('start'), __('contests_expires'), __('contests_is_on') );	
			echo '
			<table class="features-table">
			<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
			<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
			<tbody>';

		foreach($res as $fetchnow){
			
			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = $fetchnow['id'];
			$fields[] = '<span id="contest_title_'.$fetchnow['id'].'" class="editText">' . $fetchnow['contest_title'] . '</span> 
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'contests', 'contest_action=edit&contest_id='.$fetchnow['id'] ) . '">' . __('common_edit') . '</a> | </span>
					<span><a href="' . admin_url( 'contests', 'contest_action=delete&contest_id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a> | </span>
					<span><a target="_blank" href="'.ABS_URL.'contest/'.$fetchnow['id'].'">' . __('field_view') . '</a></span>
				</div>';
			$fields[] = 
				($fetchnow['contest_type'] == "demotywator" ? __('contests_image_caption') : '') . '
				'.($fetchnow['contest_type'] == "share" ? __('number_of_facebook_shares') : '') . '
				'.($fetchnow['contest_type'] == "normalny" ? __('contests_only_description') : '') . '
				'.($fetchnow['contest_type'] == "votes_opinion" ? __('common_opinion') : '') . '
				'.($fetchnow['contest_type'] == "comments" ? __('contests_comments_count') : '');
			$fields[] = $fetchnow['contest_start'];
			$fields[] = ($fetchnow['contest_expire'] < date("Y-m-d H:i:s") ? __('completed') : $fetchnow['contest_expire']);
			$fields[] = ( $fetchnow['contest_activ'] == 1 ? __('yes') : __('no') );
			
			echo generateTable( 'body', $fields). '</tr>';
		}
		echo '</tbody></table>';
	
	
	break;
	
	case 'generator':

		$res = $PD->from( 'mem_generator' )->orderBy( $order_by )->limit( $pages )->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('generators_list_empty') . '</h4>';
			return false;
		}
		$columns = array( '<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('title'), __('generators_file_link'), __('category'), __('field_date'), __('generators_used'), ucfirst( __('generators_is_active') ) );	
	
		
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		
		
		foreach($res as $fetchnow)
		{

			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';
			$fields[] = '<span id="mem_title_'.$fetchnow['id'].'" class="editText">' . $fetchnow['mem_title'] . '</span> 
				<div class="features-table-actions">
					
					<span><a href="' . admin_url( 'generator', 'mem-action=mem_activ&id='.$fetchnow['id'].'&mem_activ='.( $fetchnow['mem_activ'] == 1 ? '0' : '1' ) ) . '">'.( $fetchnow['mem_activ'] == 1 ? __('generators_deactivate') : __('generators_active') ).'</a></span> |
					<span><a href="' . admin_url( 'generator', 'mem-action=edit&id='.$fetchnow['id'] ) . '">' . __('common_edit') . '</a></span> | 
					<span><a href="' . admin_url( 'generator', 'mem-action=delete&id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
			$fields[] = '<a target="_blank" href="'. ABS_URL . 'upload/upload_mem/'.$fetchnow['mem_image'].'">'.$fetchnow['mem_image'].'</a>';
			$fields[] = Ips_Registry::get( 'Mem_Admin' )->getCategory( $fetchnow['mem_category'] );
			$fields[] = $fetchnow['mem_date_add'];
			$fields[] = $fetchnow['mem_generated'];
			$fields[] = $fetchnow['mem_activ'] == 1 ? __('yes') : __('no') ;
			
			echo generateTable( 'body', $fields). '</tr>';
		}
		
		echo '</tbody></table>';
		echo actionsButtons(array(
			'remove_selected' => 'deletegenerator',
		));
	break;
	case "generator-cats":
		
		$mem_categories = Ips_Registry::get( 'Mem_Admin' )->getCategories();
		
		if( empty( $mem_categories ) )
		{
			echo '<h4 class="caption">' . __('generators_list_empty_category') . '</h4>';
			return false;
		}
		$columns = array( __('generators_text'), __('category_description'),__('generators_category_img_count') );	
		
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		
		
		foreach( $mem_categories as $fetchnow )
		{

			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = '<span id="category_text_'.$fetchnow['id'].'" class="editText">' . $fetchnow['category_text'] . '</span> 
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'generator', 'mem-action=categories&category-action=edit&id='.$fetchnow['id'] ) . '">' . __('common_edit') . '</a></span> | 
					<span><a href="' . admin_url( 'generator', 'mem-action=categories&category-action=delete&id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
			$fields[] = $fetchnow['category_description'];
			$fields[] = $PD->cnt('mem_generator', array( 
				'mem_category' => $fetchnow['id']
			));

			echo generateTable( 'body', $fields). '</tr>';
		}
		
		echo '</tbody></table>';
		
	break;
	case 'premium_users':
	
		
		
		$res = $PD->from( 'premium_users' )->join( 'users u')->on( 'premium_users.user_id', 'u.id' )->orderBy( $order_by )->limit( $pages )->get();
		
	
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('premium_list_empty') . '</h4>';
			return false;
		}
			
			
		$columns = array( __('field_id'), __('common_username'), __('premium_from'), __('premium_days'), __('premium_expires') );	
			
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';

		foreach( $res as $fetchnow )
		{

			$premium = date("Y-m-d", strtotime($fetchnow['premium_from'] . " +".$fetchnow['days']." days" ));
			if( $premium < date("Y-m-d") )
			{
				$premium = __('premium_expires');
			}
			echo '<tr id="featured-'.$fetchnow['premium_id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = $fetchnow['id'];
			$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['login'].'">'.$fetchnow['login'].'</a>
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'premium', 'action=users&action_users=renew&id='.$fetchnow['id'] ) . '">' . __('premium_extend') . '</a> | </span>
					<span><a href="' . admin_url( 'premium', 'action=users&action_users=delete&id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
			$fields[] = $fetchnow['premium_from'];
			$fields[] = $fetchnow['days'];
			$fields[] = $premium;
			echo generateTable( 'body', $fields). '</tr>';
		}
		echo '</tbody></table>';

	break;
	case 'archive':
		
		$res = $PD->from( IPS__FILES . ' up' )->where( 'upload_status', 'archive' )->orderBy( $order_by )->limit( $pages );

		if( isset($_GET['login']) )
		{
			$res = $res->where( 'user_login', $_GET['login'], 'LIKE' );
		}

		$res = $res->get();

		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('files_list_empty') . '</h4>';
			return false;
		}
	$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('title'), __('field_author'), __('field_date'), __('common_opinion'), __('type'));

	echo '
	<table class="features-table">
	<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
	<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
	<tbody>';
			foreach( $res as $fetchnow )
			{
				if( Session::has( 'admin_thumbs' ) )
				{
					$title = '
					<a class="id-title" target="_blank" href="' . ABS_URL . $fetchnow['id'].'/" title="' . $fetchnow['title'] . '">
						<i style="display:none;">' . $fetchnow['title'] . '</i><img class="thumb" src="' . ips_img( $fetchnow, 'thumb' ) . '" />
					</a>';
				}
				else
				{
					$title = '<span id="title_'.$fetchnow['id'].'" class="editText id-title">' . $fetchnow['title'] . '</span> ';
				}
		
				echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
				$fields = array();
				$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
				$fields[] = $title . '
				<div class="features-table-actions">
				<span><a href="' . admin_url( 'geo', 'id='.$fetchnow['id'] ) . '">' . __('field_ip') . '</a> | </span>
				<span><a target="_blank" href="'.seoLink($fetchnow['id'], $fetchnow['title']).'">' . __('field_view') . '</a> | </span>
				<span><a href="' . admin_url( 'delete_file', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
				$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['user_login'].'">'.$fetchnow['user_login'].'</a>';
				$fields[] = $fetchnow['date_add'];
				$fields[] = $fetchnow['votes_opinion'];
				$fields[] = $fetchnow['upload_type'];
				echo generateTable( 'body', $fields). '</tr>';
			}
	echo '</tbody></table>';

	
	echo actionsButtons(array(
		'move_to_wait' => 'waitarchive',
		'common_delete' => 'deletearchive',
	));
	
	
	break;
	case "tags":
	

		$res = $PD->from( 'upload_tags_post' )->join( 'upload_tags t')->on( 'upload_tags_post.id_tag', 't.id_tag' )->fields( 't.*, COUNT(upload_id) as count_files' )->groupBy( 'id_tag' )->orderBy( $order_by )->limit( $pages );

		if( isset($_GET['tag']) )
		{
			$res = $res->where( 't.tag', $_GET['tag'], 'LIKE' );
		}

		$res = $res->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('files_list_empty') . '</h4>';
			return false;
		}
		
		$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('field_tag'), __('field_tag_count') );
			echo '
			<table class="features-table">
			<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
			<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
			<tbody>';
			
			
			foreach( $res as $fetchnow )
			{
				
				echo '<tr id="featured-' . $fetchnow['id_tag'] . '" class="featured-hover">';
				
				$fields = array();
				$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="' . $fetchnow['id_tag'] . '" />';	
				$fields[] = '<span id="tag_' . $fetchnow['id_tag'] . '" class="editText id-title">' . $fetchnow['tag'] . '</span>
				<div class="features-table-actions">
					<span><a href="'.ABS_URL.'tag/' . Tags::delimiter( $fetchnow['tag'] ) . '">' . __('field_view') . '</a></span>
				</div>';
				$fields[] = $fetchnow['count_files'];
				echo generateTable( 'body', $fields). '</tr>';
			}
			
			
		echo '</tbody></table>';
	
	echo actionsButtons(array(
		'common_delete' => 'deleteTags'
	));
	
	
	break;
	case "pinit_category_files":
		
		if( !isset( $_GET['file_category'] ) )
		{
			echo '<h4 class="caption">' . __('select_category') . '</h4>';
			return false;
		}

		
		$res = $PD->from( IPS__FILES . ' up' )->join( 'users u')->on( 'u.id', 'up.user_id' )->where( 'category_id', $_GET['file_category'] )->orderBy( $order_by )->limit( $pages )->get();

		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('files_list_empty') . '</h4>';
			return false;
		}
		$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('title'), __('field_author'), __('field_date'), '<a class="admin-help-block" href="#"><img src="images/icons/normal-comments.png"><div>' . __('item_comments') . '</div></a>', '<a class="admin-help-block" href="#"><img src="images/icons/fb-comments.png"><div>' . __('item_comments_facebook') . '</div></a>', __('field_pin_likes'));
		
		if( Config::get('apps_facebook_autopost') )
		{
			$columns[] = __('facebook_autopost');
		}
		if( Config::get('apps_social_lock') )
		{
			$columns[] = __('apps_social_lock');
		}
		
	echo '
	<table class="features-table">
	<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
	<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
			
			
			foreach( $res as $fetchnow )
			{
				if( Session::has( 'admin_thumbs' ) )
				{
					$title = '
					<a class="id-title" target="_blank" href="' . ABS_URL . $fetchnow['id'].'/" title="' . $fetchnow['pin_title'] . '">
						<i style="display:none;">' . $fetchnow['pin_title'] . '</i><img class="thumb" src="' . ips_img( $fetchnow, 'thumb' ) . '" />
					</a>';
				}
				else
				{
					$title = '<span id="title_'.$fetchnow['id'].'" class="editText id-title">' . $fetchnow['pin_title'] . '</span> ';
				}
	
				echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
				$fields = array();
				$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
				$fields[] = $title.'
				<div class="features-table-actions">
				<span><a href="'.ABS_URL.'edit/'.$fetchnow['id'].'/">' . __('common_edit') . '</a> | </span>
				<span><a href="' . admin_url( 'geo', 'id='.$fetchnow['id'] ) . '">' . __('field_ip') . '</a> | </span>
				<span><a target="_blank" href="'.seoLink($fetchnow['id'], $fetchnow['pin_title']).'">' . __('field_view') . '</a> | </span>
				<span><a href="' . admin_url( 'delete_file', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
				$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['login'].'">'.$fetchnow['login'].'</a>';
				$fields[] = $fetchnow['date_add'];
				$fields[] = $fetchnow['comments'];
				$fields[] = $fetchnow['comments_facebook'];
				$fields[] = $fetchnow['pin_likes'];
				if( Config::get('apps_facebook_autopost') )
				{
					$fields[] = $fetchnow['up_lock'] == 'autopost' ? __('yes') : __('no') ;
				}
				
				if( Config::get('apps_social_lock') )
				{
					$fields[] = $fetchnow['up_lock'] == 'social_lock' ? __('yes') : __('no') ;
				}
				echo generateTable( 'body', $fields). '</tr>';
			}
		echo '</tbody></table>';
		
		echo actionsButtons(array(
			'change_category' => array( 
				'category_change' => '<select id="category_id" name="category_id">'.Categories::categorySelectOptions( $_GET['file_category'] ).'</select>'
			)
		));
		
	break;
	case "category_files":
	
		if( !isset( $_GET['file_category'] ) )
		{
			echo '<h4 class="caption">' . __('select_category') . '</h4>';
			return false;
		}

		$res = $PD->from( IPS__FILES . ' up' )->where( 'category_id', $_GET['file_category'] )->orderBy( $order_by )->limit( $pages )->get();
		
		
		if( empty( $res) )
		{
			echo '<h4 class="caption">' . __('files_list_empty') . '</h4>';
			return false;
		}
		$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('title'), __('field_author'), __('field_date'), '<a class="admin-help-block" href="#"><img src="images/icons/normal-comments.png"><div>' . __('item_comments') . '</div></a>', '<a class="admin-help-block" href="#"><img src="images/icons/fb-comments.png"><div>' . __('item_comments_facebook') . '</div></a>', __('common_opinion'), '+', '-', __('type') );
		if( Config::get('apps_facebook_autopost') )
		{
			$columns[] = __('facebook_autopost');
		}
		if( Config::get('apps_social_lock') )
		{
			$columns[] = __('apps_social_lock');
		}	
	echo '
	<table class="features-table">
	<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
	<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
			
			
			foreach( $res as $fetchnow )
			{
				if( Session::has( 'admin_thumbs' ) )
				{
					$title = '
					<a class="id-title" target="_blank" href="' . ABS_URL . $fetchnow['id'].'/" title="' . $fetchnow['title'] . '">
						<i style="display:none;">' . $fetchnow['title'] . '</i><img class="thumb" src="' . ips_img( $fetchnow, 'thumb' ) . '" />
					</a>';
				}
				else
				{
					$title = '<span id="title_'.$fetchnow['id'].'" class="editText id-title">' . $fetchnow['title'] . '</span> ';
				}
				
			
				echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
				$fields = array();
				$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
				$fields[] = $title.'
				<div class="features-table-actions">
				<span><a href="'.ABS_URL.'edit/'.$fetchnow['id'].'/">' . __('common_edit') . '</a> | </span>
				<span><a href="' . admin_url( 'geo', 'id='.$fetchnow['id'] ) . '">' . __('field_ip') . '</a> | </span>
				<span><a target="_blank" href="'.seoLink($fetchnow['id'], $fetchnow['title']).'">' . __('field_view') . '</a> | </span>
				<span><a href="' . admin_url( 'delete_file', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
				$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['user_login'].'">'.$fetchnow['user_login'].'</a>';
				$fields[] = $fetchnow['date_add'];
				$fields[] = $fetchnow['comments'];
				$fields[] = $fetchnow['comments_facebook'];
				$fields[] = $fetchnow['votes_opinion'];
				$fields[] = (int)Upload_Meta::get( $fetchnow['id'], 'votes_down' );
				$fields[] = (int)Upload_Meta::get( $fetchnow['id'], 'votes_down' );
				$fields[] = $fetchnow['upload_type'];
				if( Config::get('apps_facebook_autopost') )
				{
					$fields[] = $fetchnow['up_lock'] == 'autopost' ? __('yes') : __('no') ;
				}
				
				if( Config::get('apps_social_lock') )
				{
					$fields[] = $fetchnow['up_lock'] == 'social_lock' ? __('yes') : __('no') ;
				}
				echo generateTable( 'body', $fields). '</tr>';
			}
		echo '</tbody></table>';
		
		echo actionsButtons(array(
			'change_category' => array( 
				'category_change' => '<select id="category_id" name="category_id">'.Categories::categorySelectOptions( $_GET['file_category'] ).'</select>'
			)
		));
	break;
	case 'boards' :
		
		$res = $PD->from( 'pinit_boards b' )->join( 'users u')->on( 'u.id', 'b.user_id' )->orderBy( $order_by )->limit( $pages );
		
		if( isset($_GET['login']) )
		{
			$user_info = getUserInfo( false, false, $_GET['login'] );
			if( isset( $user_info['id'] ) )
			{
				$res = $res->where( 'user_id', $user_info['id'] );
			}
		}
		if( isset( $_GET['privacy'] ) && $_GET['privacy'] != 'all' )
		{
			$res = $res->where( 'board_privacy', $_GET['privacy'] );
		} 
		
		$res = $res->get();
		
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('boards_list_empty') . '</h4>';
			return false;
		}
		$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('title'), __('field_author'), __('field_date'), __('field_board_followers'),  __('field_board_pins'), __('field_privacy'), __( 'item_views' ) );
		
		echo '
	<table class="features-table">
	<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
	<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
			
			
			foreach( $res as $fetchnow )
			{
				
				echo '<tr id="featured-'.$fetchnow['board_id'].'" class="featured-hover">';
				$fields = array();
				$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['board_id'].'" />';	
				$fields[] = '<span id="title_'.$fetchnow['board_id'].'" class="editText id-title">' . $fetchnow['board_title'] . '</span> ' . '
				<div class="features-table-actions">
				<span><a target="_blank" href="'.ABS_URL .'board/' . $fetchnow['board_id'] . '">' . __('field_view') . ' </a> | </span>
				<span><a href="' . admin_url( 'delete_board', 'id='.$fetchnow['board_id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
				$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['login'].'">'.$fetchnow['login'].'</a>';
				$fields[] = $fetchnow['date_add'];
				$fields[] = $fetchnow['board_followers'];
				$fields[] = $fetchnow['board_pins'];
				$fields[] = $fetchnow['board_privacy'] == 'private' ? __( 'field_privacy_hidden' ) : __( 'field_privacy_visible' ) ;
				$fields[] = $fetchnow['board_views'];
				
				echo generateTable( 'body', $fields). '</tr>';
			}
	echo '</tbody></table>';
	
		echo actionsButtons( array(
			'common_delete' => 'deleteboards'
		) );
	
	break;
	
	
	
	
	
	
	case 'pins':

		$res = $PD->from( IPS__FILES . ' up' )->join( 'users u')->on( 'u.id', 'up.user_id' )->orderBy( $order_by )->limit( $pages );
		
		if( isset($_GET['login']) )
		{
			$user_info = getUserInfo( false, false, $_GET['login'] );
			if( isset( $user_info['id'] ) )
			{
				$res = $res->where( 'user_id', $user_info['id'] );
			}
		}
		if( isset( $_GET['privacy'] ) && $_GET['privacy'] != 'all' && !empty( $_GET['privacy'] ) )
		{
			$res = $res->where( 'pin_privacy', $_GET['privacy'] );
		} 
		
		$res = $res->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('files_list_empty') . '</h4>';
			return false;
		}
		
		$columns = array('<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('title'), __('field_author'), __('field_date'), '<a class="admin-help-block" href="#"><img src="images/icons/normal-comments.png"><div>' . __('item_comments') . '</div></a>', '<a class="admin-help-block" href="#"><img src="images/icons/fb-comments.png"><div>' . __('item_comments_facebook') . '</div></a>', __('field_likes'), __('field_privacy') );
		if( Config::get('apps_facebook_autopost') )
		{
			$columns[] = __('facebook_autopost');
		}
		if( Config::get('apps_social_lock') )
		{
			$columns[] = __('apps_social_lock');
		}
		$columns[] = 'Views';
		echo '
	<table class="features-table">
	<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
	<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
			
			
			foreach( $res as $fetchnow )
			{
				if( Session::has( 'admin_thumbs' ) )
				{
					$title = '
					<a class="id-title" target="_blank" href="'.ABS_URL .'pin/' . $fetchnow['id'] . '" title="' . $fetchnow['pin_title'] . '">
						<i style="display:none;">' . $fetchnow['pin_title'] . '</i><img class="thumb" src="' . ips_img( $fetchnow['upload_image'], 'large' ) . '" />
					</a>';
				}
				else
				{
					$title = '<span id="title_'.$fetchnow['id'].'" class="editText id-title">' . $fetchnow['pin_title'] . '</span> ';
				}

				echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover '.( $fetchnow['pin_featured'] ? 'pin_featured' : '' ).'">';
				$fields = array();
				$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
				$fields[] = $title . '
				<div class="features-table-actions">
				<span><a href="' . admin_url( 'geo', 'id='.$fetchnow['id'] ) . '">' . __('field_ip') . '</a> | </span>
				<span><a target="_blank" href="'.ABS_URL .'pin/' . $fetchnow['id'] . '">' . __('field_view') . ' </a> | </span>
				<span><a href="' . admin_url( 'delete_pin', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a> | </span>
				<span><a href="' . admin_url( 'feature_pin', 'id='.$fetchnow['id'] ) . '" '.( $fetchnow['pin_featured'] ? 'style="color: red;"' : '' ).'>' . __('featured_pin') . '</a></span>
				</div>';
				$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['login'].'">'.$fetchnow['login'].'</a>';
				$fields[] = $fetchnow['date_add'];
				$fields[] = $fetchnow['comments'];
				$fields[] = $fetchnow['comments_facebook'];
				$fields[] = $fetchnow['pin_likes'];
				$fields[] = $fetchnow['pin_privacy'] == 'private' ? __( 'field_privacy_hidden' ) : __( 'field_privacy_visible' ) ;
				
				if( Config::get('apps_facebook_autopost') )
				{
					$fields[] = $fetchnow['up_lock'] == 'autopost' ? __('yes') : __('no') ;
				}
				
				if( Config::get('apps_social_lock') )
				{
					$fields[] = $fetchnow['up_lock'] == 'social_lock' ? __('yes') : __('no') ;
				}
			
				$fields[] = $fetchnow['upload_views'];
				
				echo generateTable( 'body', $fields). '</tr>';
			}
	echo '</tbody></table>';

		echo actionsButtons( array(
			'common_delete' => 'deletepin'
		) );
	break;
	
	
	
	case 'wait':
	case 'main':


		$res = $PD->from( IPS__FILES . ' up' )->setWhere( array(
			'up.upload_status' => 'public',
			'up.upload_activ' => ( $load_action == "main" ? 1 : 0 ),
		))->orderBy( $order_by )->limit( $pages );
		
		if( isset( $_GET['login'] ) )
		{
			$res = $res->where( 'up.user_login', $_GET['login'], 'LIKE' );
		}
		
		$res = $res->get();

		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('files_list_empty') . '</h4>';
			return false;
		}
		
		if( Session::get( 'admin_thumbs' ) != 'preview' )
		{
		
			$columns = array(
				'<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', 
				__('title'), 
				__('field_author'), 
				__('field_date'), 
				'<a class="admin-help-block" href="#"><img src="images/icons/normal-comments.png"><div>' . __('item_comments') . '</div></a>', 
				'<a class="admin-help-block" href="#"><img src="images/icons/fb-comments.png"><div>' . strip_tags(__('item_comments_facebook')) . '</div></a>', 
				__('common_opinion'), 
				'+', 
				'-', 
				__('type')
			);
			
			if( Config::get('apps_facebook_autopost') )
			{
				$columns[] = __('facebook_autopost');
			}
			if( Config::get('apps_social_lock') )
			{
				$columns[] = __('apps_social_lock');
			}
		
			$columns[] = __('item_views');
			
			echo '
			<table class="features-table">
			<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
			<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
			<tbody>';
				
				
				foreach( $res as $fetchnow )
				{
					if( Session::has( 'admin_thumbs' ) )
					{
						$title = '
						<a class="thumb-cnt" href="#" data-featherlight="' . ips_img( $fetchnow, 'large' ) . '">
							<img class="thumb large" src="' . ips_img( $fetchnow, 'medium' ) . '" />
							<b class="thumb-p" href="#">kliknij aby powiększyć</b>
						</a>';
					}
					else
					{
						$title = '<span id="title_'.$fetchnow['id'].'" class="editText id-title">' . $fetchnow['title'] . '</span> ';
					}

					echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
					$fields = array();
					$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
					$fields[] = $title . '
					<div class="features-table-actions">
					<span><a href="'.ABS_URL.'edit/'.$fetchnow['id'].'/">' . __('common_edit') . '</a> | </span>
					<span><a href="' . admin_url( 'geo', 'id='.$fetchnow['id'] ) . '">' . __('field_ip') . '</a> | </span>
					<span><a target="_blank" href="'.seoLink($fetchnow['id'], $fetchnow['title']).'">' . __('field_view') . ' </a> | </span>
					<span><a href="' . admin_url( 'delete_file', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
					</div>';
					$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['user_login'].'">'.$fetchnow['user_login'].'</a>';
					$fields[] = $fetchnow['date_add'];
					$fields[] = $fetchnow['comments'];
					$fields[] = $fetchnow['comments_facebook'];
					$fields[] = $fetchnow['votes_opinion'];
					$fields[] = $fetchnow['votes_up'];
					$fields[] = $fetchnow['votes_down'];
					$fields[] = $fetchnow['upload_type'];
					if( Config::get('apps_facebook_autopost') )
					{
						$fields[] = $fetchnow['up_lock'] == 'autopost' ? __('yes') : __('no') ;
					}
					
					if( Config::get('apps_social_lock') )
					{
						$fields[] = $fetchnow['up_lock'] == 'social_lock' ? __('yes') : __('no') ;
					}
					
					$fields[] = $fetchnow['upload_views'];
					
					echo generateTable( 'body', $fields). '</tr>';
				}
		}	
		else
		{
			
			$columns = array(
				'<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', 
				__('field_image'),
				__('field_info'), 
				__('field_actions'), 
			);
			
			
			
			echo '
			<table class="features-table table-with-info">
			<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
			<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
			<tbody>';
			
			
			foreach( $res as $fetchnow )
			{
				
				echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
				
				$fields = array();
				$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
				$fields[] = '
				<a class="thumb-cnt" href="#" data-featherlight="' . ips_img( $fetchnow, 'large' ) . '">
					<img class="thumb large" src="' . ips_img( $fetchnow, 'medium' ) . '" />
					<b class="thumb-p" href="#">kliknij aby powiększyć</b>
				</a>';
				

				$info = '
				<table class="table-info">
					<tr>
						<td>'.__('title').'</td>
						<td><span id="title_'.$fetchnow['id'].'" class="editText id-title">' . $fetchnow['title'] . '</span></td>
					</tr>
					<tr>	
						<td>' . __('field_author') . '</td>
						<td><a href="'. ABS_URL.'profile/'.$fetchnow['user_login'].'">'.$fetchnow['user_login'].'</a></td>
					</tr>
					<tr>	
						<td>' . __('field_date') . '</td>
						<td>' . $fetchnow['date_add'] . '</td>
					</tr>
					<tr>	
						<td>' . __('item_comments') . '</td>
						<td>' . $fetchnow['comments'] . '</td>
					</tr>
					<tr>	
						<td>' . __('item_comments_facebook') . '</td>
						<td>' . $fetchnow['comments_facebook'] . '</td>
					</tr>
					<tr>	
						<td>' . __('common_opinion') . '</td>
						<td>' . $fetchnow['votes_opinion'] . ' ( +: ' . $fetchnow['votes_up'] . ', -: ' . $fetchnow['votes_down'] . ')</td>
					</tr>
						';
						
					if( Config::get('apps_facebook_autopost') )
					{
						$info .= '
						<tr>
							<td>' . __('facebook_autopost') . '</td>
							<td>' . ( $fetchnow['up_lock'] == 'autopost' ? __('yes') : __('no') ) . '</td>
						</tr>';
					}
					
					if( Config::get('apps_social_lock') )
					{
						$info .= '
						<tr>
							<td>' . __('apps_social_lock') . '</td>
							<td>' . ( $fetchnow['up_lock'] == 'social_lock' ? __('yes') : __('no') ) . '</td>
						</tr>';
					}
					
					$info .= '
					<tr>
						<td>' . __('type') . '</td>
						<td>' . $fetchnow['upload_type'] . '</td>
					</tr>
					<tr>
						<td>' . __('item_views') . '</td>
						<td>' . $fetchnow['upload_views'] . '</td>
					</tr>
				</table>
				';
				$fields[] = $info;
						
				$fields[] = '
				<div class="features-actions">
					<div><a class="button" href="'.ABS_URL.'edit/'.$fetchnow['id'].'/">' . __('common_edit') . '</a></div>
					<div><a class="button" href="' . admin_url( 'geo', 'id='.$fetchnow['id'] ) . '">' . __('field_ip') . '</a></div>
					<div><a class="button" target="_blank" href="'.seoLink($fetchnow['id'], $fetchnow['title']).'">' . __('field_view') . ' </a></div>
					<div><a class="button" href="' . admin_url( 'delete_file', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></div>
				</div>';

				echo generateTable( 'body', $fields). '</tr>';
			}
		}		
				
		echo '</tbody></table>';
		
		$load_action = $load_action == 'wait' ? 'main' : 'wait';
		
		$buttons = array(
			'move_to_' . $load_action => $load_action,
			'common_archive' => 'archive',
			'common_delete' => 'delete'
		);

		if( Config::get('apps_facebook_autopost') )
		{
			$buttons['block_autopost_facebook'] = 'autopost';
		}
		
		if( Config::get('apps_social_lock') )
		{
			$buttons['block_social_lock'] = 'social_lock';
		}
		
		echo actionsButtons( $buttons );
		
	break;
	
	
	
	
	
	
	
	
	
	
	
	case "ads":	
		
		if( App::ver( array( 'gag', 'bebzol', 'vines' ) ) )
		{
			$condition = array(
				'unique_name' => array(
					array('left_side_list','right_side_list','pin_side_block_top','pin_side_block_middle','pin_side_block_bottom'), 'NOT IN'
				)
			);
		}
		elseif( App::ver( array( 'pinestic') ) )
		{
			
			$condition = array(
				'unique_name' => array(
					array('between_files','left_side_list','right_side_list','top_of_list','side_block_top','side_block_bottom','gallery_ad'), 'NOT IN'
				)
			);
			
		}
		else
		{
			$condition = array(
				'unique_name' => array(
					array('side_block_top','side_block_bottom','pin_side_block_top','pin_side_block_middle','pin_side_block_bottom'), 'NOT IN'
				)
			);
		}
		
		$res = $PD->select( 'ads', $condition );
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('error_mysql_query') . '</h4>';
			return false;
		}
		
		
		
		
		$columns = array( __('ad_name'), __('ad_unique_name'), __('ad_activ') );

		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
			

		foreach($res as $fetchnow){
			
			echo '<tr id="featured-' . $fetchnow['id'] . '" class="featured-hover">';
			$fields = array();
			$fields[] = __( 'ads_' . $fetchnow['unique_name'] ) . '
				<div class="features-table-actions ' . $fetchnow['unique_name'] . '">
					<span><a data-activ="' . $fetchnow['ad_activ'] . '" class="ads_edit" href="' . admin_url( 'ads', 'edit=' . $fetchnow['id'] ) . '">' . __('common_edit') . '</a> | </span>
					<span><a href="' . admin_url( 'ads', 'status=' . $fetchnow['id'] ) . '">' . __( ( (int)$fetchnow['ad_activ'] == 1 ? 'option_turn_off' : 'option_turn_on' ) ) . '</a></span>
					<textarea class="display_none" name="ads_unique_name">' .  $fetchnow['unique_name'] . '</textarea>
					<textarea class="display_none" name="ads_title">' . __( 'ads_' . $fetchnow['unique_name'] ) . '</textarea>
					<textarea class="display_none" name="ads_sizes">' . __( 'ads_sizes_' . $fetchnow['unique_name'] ) . '</textarea>
					<textarea class="display_none" name="ad_content">' . htmlspecialchars_decode( stripslashes( $fetchnow['ad_content'] ), ENT_QUOTES ) . '</textarea>
				</div>';
				
			$fields[] = $fetchnow['unique_name'];
			$fields[] = (int)$fetchnow['ad_activ'] == 1 ? __('yes') : __('no') ;
			
			echo generateTable( 'body', $fields). '</tr>';
		}

		echo '</tbody></table>';


		
	break;
	
	case "comments":

		$res = $PD->from( 'upload_comments' )->orderBy( $order_by )->limit( $pages );
		
		if( isset( $_GET['login'] ) )
		{
			$res = $res->where( 'user_login', $_GET['login'], 'LIKE' );
		}

		$res = $res->get();
		
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('comments_list_empty') . '</h4>';
			return false;
		}
		
		$columns = array( '<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('contents'), __('field_author'), __('field_date'), __('common_opinion'), __('field_votes') );

		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
			

		foreach($res as $fetchnow){
			
			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
			$fields[] = $fetchnow['content'] . '
				<div class="features-table-actions">
					<span><a href="'. ABS_URL . ( IPS_VERSION == 'pinestic' ? 'pin/' . $fetchnow['upload_id'] : $fetchnow['upload_id']) .'/">' . __('field_file') . '</a> | </span>
					<span><a href="' . admin_url( 'del_com', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
				
			$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['user_login'].'">'.$fetchnow['user_login'].'</a>';
			$fields[] = $fetchnow['date_add'];
			$fields[] = $fetchnow['comment_opinion'];
			$fields[] = $fetchnow['comment_votes'];
			echo generateTable( 'body', $fields). '</tr>';
		}

		echo '</tbody></table>';

		echo actionsButtons( array(
			'common_delete' => 'deletecomment'
		) );
		
	break;
	
	
	case 'users':
		
		
		$res = $PD->from( 'users u' )->fields("( SELECT COALESCE( SUM(comment_opinion), 0) FROM " . db_prefix( 'upload_comments' ) . " WHERE user_id = u.id  ) as comments_opinion,
		".
		( IPS_VERSION != 'pinestic' ?
		'( SELECT COALESCE( SUM(votes_opinion), 0) FROM ' . db_prefix( IPS__FILES ) . ' WHERE user_id = u.id  ) as posts_opinion,' :
		'( SELECT COALESCE( SUM(pin_likes), 0) FROM ' . db_prefix( IPS__FILES ) . ' WHERE user_id = u.id  ) as posts_opinion,' ) 
		. "
		u.*,
		( SELECT setting_value FROM " . db_prefix( 'users_data' ) . " WHERE user_id = u.id AND setting_key = 'is_admin') AS is_admin,
		( SELECT setting_value FROM " . db_prefix( 'users_data' ) . " WHERE user_id = u.id AND setting_key = 'is_moderator') AS is_moderator")->orderBy( $order_by )->limit( $pages );
		
		if( isset( $_GET['login'] ) )
		{
			$res = $res->where( 'u.login', $_GET['login'], 'LIKE' );
		}

		$res = $res->get();
		
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('users_list_empty') . '</h4>';
			return false;
		}
		$columns = array( '<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('common_username'), __('field_materials'), __('field_comments'), 'PZM(<a class="admin-help-block" href="#">?<div>' . __('users_files_opinion') . '</div></a>)', 'PZK(<a class="admin-help-block" href="#">?<div>' . __('users_comments_opinion') . '</div></a>)', __('field_register'), __('field_email') );

		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		
		

		foreach( $res as $fetchnow )
		{

			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
			
			$fields[] = '<a class="overflow ' . ( $fetchnow['user_banned'] ==! 0 ? ' user_banned' : ''  ) .'" href="'. ABS_URL . 'profile/'.$fetchnow['login'].'">' . $fetchnow['login'] . ( $fetchnow['activ'] == 0 ? ' ' . __( 'user_inactive' ) : '' ) . '</a>
				<div class="features-table-actions">
					<span>
					<a class="admin-help-block" '.( $fetchnow['is_admin'] == 0 ? '' : 'style="color: red;"' ) . 'href="' . admin_url( 'admin', 'admin-action=' . ( $fetchnow['is_admin'] == 0 ? 'add' : 'remove') . '&id='.$fetchnow['id'] ) . '"> ' . __( 'field_admin_add' ) . '<div>'.( $fetchnow['is_admin'] == 0 ? __('give_prev') : __('remove_prev') ).' ' . __('administrator_rights') . '</div></a> | 
					</span>
					<span>
					<a class="admin-help-block" ' . ( $fetchnow['is_moderator'] == 0 ? '' : 'style="color: red;"' ) . ' href="' . admin_url( 'moderator', 'admin-action='.( $fetchnow['is_moderator'] == 0 ? 'add' : 'remove' ).'&id='.$fetchnow['id'] ) . '"> ' . __('field_moderator_add') . '<div>'.( $fetchnow['is_moderator'] == 0 ? __('give_prev') : __('remove_prev') ).' ' . __('moderator_rights') . '</div></a> | 
					</span>
					<span>
					<a href="'. ABS_URL.'messages/write/'.$fetchnow['login'].'">' . __('post_pw') . '</a> | 
					</span>
					'.( $fetchnow['activ'] == 0 ? '<span><a href="' . admin_url( 'activate', 'id='.$fetchnow['id'] ) . '">' . __('activate_account') . '</a> | </span>' : '') . '
					<span>
					<a href="' . admin_url( 'delete_user', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a>
					</span>
				</div>';
				
			$fields[] = $fetchnow['user_uploads'];
			$fields[] = $fetchnow['user_comments'];
			$fields[] = $fetchnow['posts_opinion'];
			$fields[] = $fetchnow['comments_opinion'];
			
			$fields[] = $fetchnow['date_add'];
			
			$fields[] = '<a class="overflow_small admin-help-block" href="mailto:'.$fetchnow['email'].'">'.$fetchnow['email'].'<div>'.$fetchnow['email'].'<br />' . __('users_email_user') . '</div></a>';
			echo generateTable( 'body', $fields). '</tr>';
			
			
		}

		echo '</tbody></table>';
		
		echo actionsButtons(array(
			'common_delete' => 'deleteuser',
			'user_ban' => array( 
				'userban' => '<select name="userban" id="userban" style="width: 150px;"><option value="0">' . __('user_ban_week') . '</option><option value="1">' . __('user_ban_month') . '</option><option value="2">' . __('user_ban_alltime') . '</option></select>'
			)
		));


	break;
	
	
	case 'premium_services':
			
		$res = $PD->select( 'premium_services', false, $pages );	
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('services_premium_list_empty') . '</h4>';
			return false;
		}	
			$columns = array( '', 'ID', __('sms_number'), __('sms_contents'), __('sms_price'), __('sms_codes_used'), __('sms_extend_premium' ), __('sms_description'), __('services_premium_codes_verify' ) );	
			
			echo '
			<table class="features-table" style="width: 800px">
			<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
			<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
			<tbody>';
		$idx = 1;
		foreach( $res as $fetchnow )
		{
			
			echo '<tr id="featured-'.$fetchnow['service_id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = $idx . ')';
			$fields[] = $fetchnow['service_id'];
			$fields[] = $fetchnow['sms_number'] . '
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'premium', 'action=change&service_id='.$fetchnow['service_id'] ) . '">'.__('common_edit').'</a> | </span>
					<span><a href="' . admin_url( 'premium', 'action=delete&service_id='.$fetchnow['service_id'] ) . '">'.__('common_delete').'</a> </span>
				</div>';
			$fields[] = $fetchnow['sms_content'];
			$fields[] = $fetchnow['sms_price'];
			$fields[] =  $fetchnow['codes_used'];
			$fields[] = $fetchnow['sms_extend_premium'];
			$fields[] = '<span style="word-wrap: break-word ! important; white-space: normal; display: block; width: 250px ! important;">' . $fetchnow['sms_description'] . '</span>';
			$fields[] = ( $fetchnow['sms_codes_verify'] == 1 ? __('services_premium_verify_imported') : __('services_premium_verify_online'));
			

			echo generateTable( 'body', $fields). '</tr>';
			$idx++;
		}
		echo '</tbody></table>';

	break;
	
	
	
	case 'ban':
	
	
		$res = $PD->from( 'users u' )->join( 'users_data')->on( 'users_data.user_id', 'u.id' )->setWhere( array(
			'users_data.setting_key' => 'user_banned_data'
		))->fields('u.*, users_data.setting_value')->orderBy( 'u.id' )->limit( $pages );
		
		if( isset( $_GET['login'] ) )
		{
			$res = $res->where( 'users.login', $_GET['login'], 'LIKE' );
		}

		$res = $res->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">' . __('users_list_empty') . '</h4>';
			return false;
		}
		
		$columns = array( '<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('common_username'), __('field_email'), __('field_banned_by'), __('ban_for_time') );

		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		

		foreach($res as $fetchnow){

			$fetchnow = array_merge( $fetchnow, unserialize( $fetchnow['setting_value'] ) );
			
			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
			$fields[] = '<a href="'. ABS_URL.'profile/'.$fetchnow['login'].'">'.$fetchnow['login'].'</a>
				<div class="features-table-actions">
					<span><a class="admin-help-block" href="' . admin_url( 'un_ban_user', 'id='.$fetchnow['id'] ) . '">	' . __('user_ban_unban') . '<div>' . __('user_ban_unban') . '</div></a>  | </span>
					<span><a href="' . ABS_URL . 'messages/write/'.$fetchnow['login'].'">' . __('post_pw') . '</a> | </span>
					<span><a href="' . admin_url( 'delete_user', 'id='.$fetchnow['id'] ) . '">' . __('common_delete') . '</a></span>
				</div>';
				
			$fields[] = '<a class="admin-help-block" href="mailto:'.$fetchnow['email'].'">'.$fetchnow['email'].'<div>' . __('users_email_user') . '</div></a>';
			$fields[] = $fetchnow['who_ban'];
			$fields[] = $fetchnow['date_ban'];
			echo generateTable( 'body', $fields). '</tr>';
		}

		echo '</tbody></table>';
	break;
	
	
	case 'facebook':
	
		$res = $PD->from( 'fanpage_posts' )->orderBy( $order_by )->limit( $pages )->get();
			
		if( empty( $res ) )
		{
			echo '<h4 class="caption">' . __('fanpage_list_empty') . '</h4>';
			return false;
		}
		
		$columns = array( '<input type="checkbox" class="checkoption" onclick="checkAll(this);" />', __('field_file_title'), __('fanpage_id_item_or_album'), __('type'),  __('field_date'), __('caption_fanpage') );

		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $columns ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $columns ).'</tr></tfoot>
		<tbody>';
		
		foreach($res as $fetchnow)
		{
			
			
			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			$fields = array();
			$fields[] = '<input type="checkbox" class="checkoption" name="ids[]" value="'.$fetchnow['id'].'" />';	
			
			$fields[] = $fetchnow['post_title'] . '
				<div class="features-table-actions">
					<span><a target="_blank" href="' . $fetchnow['post_url'] . '">' . __('fanpage_see_facebook') . '</a>  | </span>
					<span><a href="' . admin_url( 'fanpage', 'action=delete&post_id=' . $fetchnow['post_id'] . '' ) . '">' . __('common_delete') . '</a></span>
				</div>';
			$fields[] = ( !empty( $fetchnow['album_id'] ) ? 'Album:' . $fetchnow['album_id'] :  '<a target="_blank" href="'. ABS_URL . $fetchnow['upload_id'].'/">'.$fetchnow['upload_id'].'</a>' );
			$fields[] = ucfirst( $fetchnow['post_type'] );
			$fields[] = $fetchnow['post_data'];
			
			$fanpage_adress = Facebook_Fanpage::filter( 'url', 'fanpage_id', $fetchnow['fanpage_id'] );
			
			$fields[] = $fanpage_adress ? $fanpage_adress : 'https://www.facebook.com/pages/page/' . $fetchnow['fanpage_id'];
			
			echo generateTable( 'body', $fields). '</tr>';
			
		}
		
		echo '</tbody></table>';
		
		echo actionsButtons(array(
			'fanpage_remove_from_facebook' => 'fanpage_post_remove'
		));
		

	break;
}

?>