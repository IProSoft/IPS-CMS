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
	if( $action )
	{
		Session::set( 'admin_redirect', admin_url( 'template', 'action=' . $action ) );
	}
	
	echo '
		<div class="title_caption">
			<span class="caption">' . __('template_title') . ( $action ? ' > ' . __('template_' . $action ) : '' ) . '</span>
		</div>
		
		<div class="templates-service">
	';

		if( $action == 'favicon' )
		{

			echo '
			<form action="admin-save.php" enctype="multipart/form-data" method="post">
				<div class="content_tabs tabbed_area">
				'.displayOptionField( 'template_favicon',  array(
					'current_value' => '',
					'option_new_block' => __( 'template_favicon' ),
					'option_set_text' => 'template_favicon_title',
					'option_type' => 'text',
					'option_value' => '<input type="file" name="favicon" />'
				)).'
				</div>
				<button type="submit" class="button"> ' . __('save') . ' </button>
			</form>
			
			<div class="div-info-message">
				' . __('template_info_5') . '
			</div>';
		}
		elseif( $action == 'logo' )
		{
			
			echo '
			
				<form action="admin-save.php" enctype="multipart/form-data" method="post">
					<div class="content_tabs tabbed_area">
					'.displayOptionField( 'template_logo',  array(
						'current_value' => '',
						'option_new_block' => __( 'template_logo_title' ),
						'option_set_text' => 'template_logo',
						'option_type' => 'text',
						'option_value' => '<input type="file" name="logo" />'
					)) . ( IPS_VERSION == 'pinestic' ? displayOptionField( 'template_logo_small',  array(
						'current_value' => '',
						'option_set_text' => 'template_logo_small',
						'option_type' => 'text',
						'option_value' => '<input type="file" name="logo_small" />'
					)) : '' ) . '
					</div>
					<button type="submit" class="button"> ' . __('save') . ' </button>
				</form>
			
			<div class="div-info-message">
				' . __('template_info_2') . '
			</div>';
		}
		elseif( $action == 'version' )
		{
			
			echo '
			<div class="content_tabs tabbed_area">
				<span>' . __('template_type') . '</span>
				<a href="admin-save.php?ips_version=demotywator" class="button" role="button">' . __('template_demotywator') . '</a>
				<a href="admin-save.php?ips_version=kwejk" class="button" role="button">' . __('template_kwejk') . '</a>
				<a href="admin-save.php?ips_version=gag" class="button" role="button">' . __('template_gag') . '</a>
				<a href="admin-save.php?ips_version=bebzol" class="button" role="button">' . __('template_bebzol') . '</a>
				<a href="admin-save.php?ips_version=vines" class="button" role="button">' . __('template_vines') . '</a>
			</div>
			';
		}
		elseif( $action == 'settings' )
		{
			echo '
				<form action="admin-save.php" enctype="multipart/form-data" method="post">	
				' . displayArrayOptions( getOptionsFile()['template_settings'] ) . '
				<button class="button">' . __('save') . ' </button>
				</form>
			';
			if( IPS_VERSION != 'pinestic' )
			{
				echo '
				<div class="tabbed_area layout-columns">
				
					<span>' . __('template_type_layout') . '</span>
					<span>
						<a rel="1" href="admin-save.php?template_layout=one" title="' . __('template_one_column') . '" class="'.( Config::get( 'template_settings', 'layout' ) == 'one' ? 'layout_active' : '') . '"><img src="images/1_column.png"></a>
						<a rel="2" href="admin-save.php?template_layout=two" title="' . __('template_two_column') . '" class="'.( Config::get( 'template_settings', 'layout' ) == 'two' ? 'layout_active' : '') . '"><img src="images/2_column.png"></a>
						<a rel="3" href="admin-save.php?template_layout=three" title="' . __('template_three_column') . '" class="'.( Config::get( 'template_settings', 'layout' ) == 'three' ? 'layout_active' : '') . '"><img src="images/3_column.png"></a>
					</span>
				';
				
				if( Config::get( 'template_settings', 'layout' ) == 'three' )
				{
					echo '
					<span>' . __('template_layout_thumbs') . '</span>
					<span>
						<a href="admin-save.php?template_layout_thumbs=3" class="'.( Config::get('template_settings', 'thumb_count') == 'i-3' ? 'layout_active' : '') . '"><img src="images/3_thumbs.png"></a>
						<a href="admin-save.php?template_layout_thumbs=4" class="'.( Config::get('template_settings', 'thumb_count') == 'i-4' ? 'layout_active' : '') . '"><img src="images/4_thumbs.png"></a>
						<a href="admin-save.php?template_layout_thumbs=5" class="'.( Config::get('template_settings', 'thumb_count') == 'i-5' ? 'layout_active' : '') . '"><img src="images/5_thumbs.png"></a>
					</span>
				';
				}
				echo '	
					<div class="div-info-message with-margin">
						' . __('template_info_3') . '
					</div>
				</div>
				';
			}
		}
		elseif( $action == 'files' )
		{
			include( IPS_ADMIN_PATH .'/libs/class.editor.php' );
			$edit = new fileEdit();

			if( isset( $_POST['edits'] ) )
			{
				if( $edit->saveFile( $_POST['edit_file_name'], $_POST['edits'] ) )
				{
					echo admin_msg( array(
						'info' => __('template_file_saved')
					) );	
				}
				else
				{
					echo admin_msg( array(
						'alert' => __('template_failed_to_write_file')
					) );;	
				}
			}
			
			echo '
			<div class="content_tabs tabbed_area" ><span class="label-lg">' . __('template_edit_template') . '</span>' . $edit->showFiles( ABS_TPL_PATH ) .'</div>
			
			<div class="content_tabs tabbed_area" ><span class="label-lg">' . __('template_edit_css_template') . '</span>' . $edit->showFiles( ABS_TPL_PATH . '/css' ) . '</div>
			
			<div class="content_tabs tabbed_area" ><span class="label-lg">' . __('template_edit_other_css') . '</span>' . $edit->showFiles( ABS_PATH . '/css' ) .'</div>
			';
			if( isset( $_GET['file'] ) )
			{
				echo Templates::getInc()->getTpl( '/__admin/file_edit.html', array(
					'file_content' => $edit->editFile( $_GET['file'] ),
					'file' => $_GET['file']
				) );
				
			}
			echo '
			</div>
			';
		}
		elseif( $action == 'fonts' )
		{
			$fonts = new Admin_Web_Fonts();
		
			if( !empty( $_POST ) )
			{
				$fonts->updateOptions( $_POST );
			}
			echo '
			<div class="tabbed_area">
				<form method="post" id="ips_fonts_options">';
				foreach( $fonts->attrElements as $group => $name  )
				{
					echo '<h2>' . $name['name'] . '</h2>';
					echo $fonts->listAvailableFonts( $group );
				}
			echo '
				<button class="button">' . __('template_save all') . '</button>
				</form>
			</div>
			';
			
			echo $fonts->fontsCodeWrite();
		}
		elseif( $action == 'menu' )
		{
			echo '
			<div class="tabbed_area menu-edit-forms">
				<div id="menu-messages" class="ips-message msg-info" style="display:none;"></div>
			
			';
			
				$menu_array_hierarchy = Config::getArray( 'menu_array_hierarchy' );
				$menu = Config::getArray( 'menu_array' );
				$menu_id = 'main_menu';
				
				switch( IPS_VERSION )
				{
					case 'bebzol':
					case 'vines':
					case 'kwejk':
					case 'demotywator':
						
						$condition = array(
							'menu_id' => 'main_menu'
						);
						if( Config::get('categories_option') )
						{
							$condition['item_id'] = array( 'categories', '!=' );
						}
						$menu_array = PD::getInstance()->select('menus', $condition, null, '*', array( 'item_position' => 'ASC' ));
					break;
					case 'pinestic':
						$menu_array = PD::getInstance()->select('menus', array(
							'menu_id' => 'pinit_menu'
						), null, '*', array( 'item_position' => 'ASC' ));
						$menu_id = 'pinit_menu';
					break;
					case 'gag':
						$menu_array = PD::getInstance()->select('menus',array(
							'menu_id' => 'main_menu',
							'item_id' => array( 'rand', 'NOT IN' ),
						), null, '*', array( 'item_position' => 'ASC' ));
						
						if( Config::getArray( 'template_settings', 'gag_sub_menu' ) == 1 )
						{
							$sub_menu_array = PD::getInstance()->select('menus', array(
								'menu_id' => 'gag_sub_menu'
							), null, '*', array( 'item_position' => 'ASC' ));
						}
					break;
				}
				
				$footer_menu_array = PD::getInstance()->select('menus', array(
					'menu_id' => 'footer_menu'
				), null, '*', array( 'item_position' => 'ASC' ));
				
				echo menuItemsForm( $menu_array, $menu_id, __('template_main_menu') );
				
				if( isset( $sub_menu_array ) )
				{
					echo menuItemsForm( $sub_menu_array, 'gag_sub_menu', 'Sub Menu');
				}
				
				echo menuItemsForm( $footer_menu_array, 'footer_menu', __( 'template_footer_menu' ) );
				
			
				
				echo '
				<form id="form-menu-add" action="admin-save.php" enctype="multipart/form-data" method="post">	
					<ul class="menu ui-sortable ips-menu-edit" id="ips-menu-edit">
						<li id="menu-add-element" class="ui-state-default">
							<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
							<span class="item-title">' . __('template_add_item_to_menu') . '</span>
							<a class="item-edit" id="edit-add-element" title="' . __('template_menu_item_edit') . '" href="#">' . __('template_menu_item_edit') . '</a>
						
							<div class="menu-item-settings settings-show" id="settings-menu-add-element" style="display: block;">
								<p class="field-url description description-wide">
									<label for="menu_id" style="width: 275px; padding: 5px;">
										' . __('template_to_which_add_menu_item') . '<br>
										<select id="menu_id" name="menu_id" style="width: 275px; padding: 5px;">
											<option value="' . $menu_id . '" selected="selected">' . __('template_main_menu') . '</option>
											<option value="footer_menu" selected="selected">' . __( 'template_footer_menu' ) . '</option>
									';
									if( isset( $sub_menu_array ) )
									{
										echo '<option value="gag_sub_menu">' . __('template_sub_menu') . '</option>';
									}
									echo '
										</select>
									</label>
									
									
									
									<label for="item_anchor">
										' . __('template_link') . '<br>
										<input type="text" name="item_anchor" value="Link" id="menu-item-anchor">
									</label>
									<br>
									<label for="item_title">
										' . __('template_title_attribute') . '<br>
										<input type="text" name="item_title" value="" id="menu-item-title">
									</label>
									<br>
									<label for="item_url">
										' . __('template_item_url') . '<br>
										<input type="text" name="item_url" value="' . ABS_URL . '" id="menu-item-url">
									</label>
									<br>
									<label for="item_class">
										' . __('template_item_class') . '<br>
										<input type="text" name="item_class" value="" id="menu-item-class">
									</label>
									
									<label for="item_target">
										' . __('template_menu_item_target') . '<br>
										<select id="item_target" name="item_target" style="width: 175px; padding: 5px;">
											<option value="" selected="selected">' . __('template_target_self') . '</option>
											<option value="_blank">' . __('template_target_blank') . '</option>
										</select>
									</label>
									
								</p>
								
								<input type="hidden" name="item_activ" value="0" id="item-activ-menu-add-element">
								
								<a class="item-edit-activ" id="edit-add-element" title="' . __('template_disable_item') . '" href="#"><span style="color:red">' . __('turn_off') . '</span></a>
							</div>
						</li>
					</ul>
					<input type="submit" value="' . __('template_add_item') . '" class="button">
				</div>
				<div class="tabbed_area">
					<div class="div-info-message with-margin">
						' . __s('template_info_4', '<a href="' . admin_url( 'language', 'action=menu' ) . '">'.__('language_menu').'</a>') . '
					</div>
				</div>
				
				
			';
		}
		
		echo '</div>';
			