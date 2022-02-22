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
	
	$action = isset( $_GET['action'] ) ? $_GET['action'] : false;
	
	$blocks = Config::getArray('hooks_actions_registry');
	
	if( $blocks == false || !is_array( $blocks ) )
	{
		$blocks = array();
		Config::update('hooks_actions_registry', serialize( array() ) );
	}

	echo admin_caption( 'hooks_list' );
	
	echo '
	<div>
	<a href="' . admin_url( 'hooks', 'action=add' ) . '" class="button">' . __( 'hooks_add' ) . '</a> ';
	
	if( !empty( $blocks ) )
	{
		echo '<a href="' . admin_url( 'hooks', 'action=view' ) . '" class="button">' . __( 'hooks_show' ) . '</a>';
	}
	
	echo '
	<a href="' . admin_url( 'hooks', 'action=reset' ) . '" class="button dialog-msg">' . __( 'hooks_restore' ) . '</a>
	
	</div><br />
	
	<div class="hooks-service">';
	if( $action == 'reset' )
	{
		realoadDefaultHooks();
		ips_admin_redirect( 'hooks', 'action=view', __( 'hooks_restore_success' ) );
	}
	elseif( $action == 'add' || $action == 'edit' )
	{
		$hooks = Ips_Registry::get( 'Hooks' );
		if( $action == 'edit' && !empty( $_GET['key'] ) )
		{
			$hook_action = $hooks->find_action( $_GET['key'], ( isset( $_GET['hook'] ) ? $_GET['hook'] : false ) );
			$hook_action['hook'] = str_replace( $hook_action['hook'] . '_', '', $hook_action['key'] );
		}
		
		if( !isset( $hook_action['hook'] ) )
		{
			$hook_action = array();
			$hook_action['hook'] = isset( $_GET['hook'] ) ? $_GET['hook'] : 'before_content';
		}
		
		if( isset( $_GET['force_hook'] ) )
		{
			$hook_action['hook'] = $_GET['force_hook'];
		}

		//$priority_list = array_diff( range(1,10), reservedPriority( $hook_action['hook'] ) );

		$priority_list = range(1,10);
		$hooks->fill_priority( $hook_action['hook'], $priority_list );
	
		
		$list = array();
		foreach( $priority_list as $priority )
		{
			$list[ $priority ] = $priority;
		}
		
		$allowed_hooks = getAllowedPositions( $hook_action['hook'] );
	
	
		echo '
		<script type="text/javascript" src="/libs/tinyeditor/tiny.editor.packed.js"></script>
		<link rel="stylesheet" href="/libs/tinyeditor/tinyeditor.css">
		<form action="admin-save.php" enctype="multipart/form-data" method="post">
			';
				
			$options = array(
				'add_hook[hook]' => array(
					'current_value' => $hook_action['hook'],
					'option_set_text' => 'hooks_hook_position',
					'option_select_values' => $allowed_hooks
				),
				'add_hook[priority]' => array(
					'current_value' => ( isset( $hook_action['priority'] ) ? $hook_action['priority'] : 10 ),
					'option_set_text' => 'hooks_block_priority',
					'option_select_values' => $list
				)
			);
			
			if( !isset( $hook_action['function'] ) || isset( $hook_action['params']['title'] ) )
			{		
				
				
				$options = array_merge( $options, array(
					'add_hook[title]' => array(
						'current_value' => ( isset( $hook_action['params']['title'] ) ? $hook_action['params']['title'] : '' ),
						'option_set_text' => 'hooks_set_name',
						'option_type' => 'input',
						'option_lenght' => 10
					),
					'add_hook[content]' => array(
						'current_value' => ( isset( $hook_action['params']['title'] ) ? $hook_action['params']['title'] : '' ),
						'option_set_text' => 'hooks_set_content',
						'option_type' => 'textarea',
						'option_css' => 'tiny_editor',
					),
					'add_hook[visibility]' => array(
						'current_value' => ( isset( $hook_action['params']['visibility'] ) ? $hook_action['params']['visibility'] : 1 ),
						'option_set_text' => 'hooks_is_visible'
					),
				)); 
			

				echo '<input type="hidden" name="add_hook[user_action]" value="1" />';
			}
				
				echo displayArrayOptions( $options );
				
				if( isset( $hook_action['key'] ) )
				{
					echo '<input type="hidden" name="add_hook[key]" value="' . $hook_action['key'] . '" />';
				}	
					
			echo '	

			<button class="tiny_editor_save button" type="submit"> ' . __('save') . ' </button>
				
			</form>
			<div class="div-info-message">
				<p>' . __( 'hooks_info_priority' ) . '</p>
			</div>
			';
			
			
	
			
			
	}
	else
	{
		
		$pagin = new Pagin_Tool;
		
		echo $pagin->wrap()->addJS( 'hooks', 1 )->addMessage('hooks_info_1')->get();
		
	}
	echo '
	<div id="dialog-confirm" style="display:none;">' . __( 'hooks_info_2' ) . '</div>
	<script>	
		$(".dialog-msg").on("click", function( e ){
			e.preventDefault();
			link = $(this).attr("href");
			$( "#dialog-confirm" ).dialog({
				resizable: false,
				height:240,
				width: 500,
				modal: true,
				buttons: {
					"' . __( 'hooks_info_submit' ) . '": function() {
						$( this ).dialog( "close" );
						window.location.href = link;
					},
					"' . __( 'hooks_info_cancel' ) . '": function() {
						$( this ).dialog( "close" );
						return false;
					}
				}
			});
		});
	</script>
	';
	Session::set( 'admin_redirect', admin_url( 'hooks', 'action=view' ) );
	
	echo '</div>';	
?>