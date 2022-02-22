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
	
	include_once ( IPS_ADMIN_PATH .'/fanpage-functions.php' );
	
	/** ADD post to Fanpage **/
	if( isset( $_POST['fanpage_data'] ) )
	{
		$data = $_POST['fanpage_data'];
		
		$redir = admin_url( 'fanpage', ( isset($data['id']) ? 'id=' . $data['id'] : false ) );
		
		if( empty( $data['message'] ) && ( $data['type'] == 'post' && empty( $data['link'] ) ) )
		{
			$data = null;
		}
		
		if( empty( $data['image'] ) && empty( $_FILES["file"]["tmp_name"] ) )
		{
			$data = null;
		}
		
		if( $data['type'] == 'upload' && empty( $data['album_id'] ) )
		{
			$data = null;
		}

		if( empty( $data ) )
		{
			return ips_redirect( $redir . '&action=add', array(
				'alert' => 'fill_in_required_fields'
			) );
		}
		
		$fanpage_post = Facebook_Fanpage::post( false, $data );
		
		if( $fanpage_post === true || is_numeric( $fanpage_post ) )
		{
			$redir =  admin_url( 'fanpage' );
			
			if( $redirect = Session::get( 'ips-redirect' ) )
			{
				$redir = str_replace( ABS_URL, '', $redirect );
			}
			
			return ips_redirect( $redir, array(
				'info' => 'fanpage_add_success'
			) );
		}
		else
		{
			if( is_string( $fanpage_post ) )
			{
				ips_message( array(
					'alert' =>  $fanpage_post
				) );
			}
			
			return ips_redirect( $redir, array(
				'alert' => 'fanpage_add_error'
			) );
		}
	}
	
	echo admin_caption( 'caption_fanpage' );
	echo responsive_menu( array(
		'browse' => 'fanpage_browse_posts',
		'add' => 'add_post_to_fanpage',
		'settings' => 'fanpage_settings'
	), admin_url( 'fanpage', 'action=' ) ) . '
	<br />
	';
	
	$action = isset( $_GET['action'] ) ? $_GET['action'] : false;

	if( IPS_ACTION_GET_ID || $action == 'add'  )
	{
		$token = Config::get('apps_fanpage_default_token');
		
		if( !Facebook_UI::isAppValid(false) )
		{
			echo '
			<div style="padding: 10px" class="tabbed_area">
				' . __('fanpage_api_set') . '<br /><br />
				<a href="' . admin_url( 'fanpage', 'action=settings' ) . '" class="button" role="button">' . __('configuration') . '</a>
			</div><br />
			';
		}
		else
		{
			$row = array(
				'caption' => __( 'meta_site_title' ),
				'description' => __( 'meta_site_description' )
			);
			
			if( IPS_ACTION_GET_ID )
			{
				$file = PD::getInstance()->select( IPS__FILES, array(
					'id' => IPS_ACTION_GET_ID
				), 1);
				
				if( !empty( $file ) )
				{
					$row = array_merge( $file, $row, array(
						'image' => ips_img( $file, 'medium' ),
						'link' => seoLink( $file['id'], $file['title'] ),
						'message' => $file['title'],
					));
				}
			}
			
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

			echo '
			<form action="" enctype="multipart/form-data" method="post">
				<input type="hidden" id="fanpage_album_input" value="fanpage_data[album_id]" />				
				' . displayArrayOptions( array(
					'fanpage_data' => array(
						'option_is_array' => array(
							'type' => array(
								'current_value' => 'post',
								'option_select_values' => array(
									'post' => __('fanpage_post_type_post'),
									'upload' =>  __('fanpage_post_type_upload')
								),
								'option_css' => 'fanpage_post_type',
							),
							'fanpage_id' => array(
								'current_value' => Config::get( 'apps_fanpage_default_id' ),
								'option_set_text' => __( 'fanpage_add_id' ),
								'option_css' => 'fanpage_id_change',
								'option_select_values' => array_column( $api_tokens, 'url', 'fanpage_id' ),
								'option_multiple' => true
							),
							'title' => array(
								'current_value' => ( isset( $row['title'] ) ? $row['title'] : '' ),
								'option_type' => 'input',
								'option_lenght' => 10,
								'option_css' => 'fanpage_type_post',
							),
							'message' => array(
								'current_value' => trim( isset( $row['top_line'] ) ? $row['top_line'] : '' ),
								'option_type' => 'textarea',
							),
							'caption' => array(
								'current_value' => trim( isset($row['caption']) ? $row['caption'] : '' ),
								'option_type' => 'input',
								'option_lenght' => 10,
								'option_css' => 'fanpage_type_post',
							),
							'description' => array(
								'current_value' => trim( isset($row['description']) ? $row['description'] : '' ),
								'option_type' => 'input',
								'option_lenght' => 10,
								'option_css' => 'fanpage_type_post',
							),
							'link' => array(
								'current_value' => trim( isset($row['link']) ? $row['link'] : '' ),
								'option_type' => 'input',
								'option_css' => 'fanpage_type_post'
							),
							'album_id' => array(
								'current_value' => '',
								'option_type' => 'text',
								'option_css' => 'fanpage_type_upload display_none album_id_container',
								'option_value' => '<img width="22" height="22" src="/images/svg/spinner.svg">'
							),
							'image' => array(
								'current_value' => trim( isset( $row['image'] ) ? $row['image'] : '' ),
								'option_set_text' => 'file_from_link',
								'option_type' => 'input',
								'option_css' => 'change_input_link'
							),
							'image_upload' => array(
								'current_value' => '',
								'option_set_text' => 'file_from_disk',
								'option_type' => 'text',
								'option_value' => '<input type="file" name="file" />',
								'option_css' => 'change_input_file display_none'
							),
						)
					),
					'fanpage_image' => array(
						'current_value' => '',
						'option_set_text' => __s( 'fanpage_data_image_title', ABS_URL . 'upload/...' ),
						'option_type' => 'text',
						'option_value' => '<div id="upload_fanpage_buttons">
							<button value="file" class="button change_input">' . __('file_from_disk') . '</button>
							<button value="link" class="button change_input">' . __('file_from_link') . '</button>
						</div>',
					),
					
				)) 
				. ( isset( $row['id'] ) ? '<input type="hidden" name="fanpage_data[id]" value="'.$row['id'].'" />' : '' ) 
				. '
				<input type="hidden" id="image_type" name="fanpage_data[image_type]" value="link" />
				<input type="submit" name="fanpage_data[form]" class="button" value="' . __('fanpage_data_title') . '" />
				</form>';
				
		}
	}
	elseif( $action == 'settings' )
	{
		$fanpages = Config::getArray( 'apps_fanpage_array' );
		
		if( !is_array( $fanpages ) )
		{
			$fanpages = array();
		}
		
		/** Add new Fanpage */
		if( isset( $_POST['apps_fanpage_add'] ) )
		{
			return ips_admin_redirect( 'fanpage', 'action=settings', Facebook_Fanpage::addFanpage( $_POST['apps_fanpage_add'] ) );
		}
		
		/** SET default Fanpage */
		if( isset( $_GET['default_key'] ) )
		{
			return ips_admin_redirect( 'fanpage', 'action=settings', Facebook_Fanpage::setDefaultFanpage( $_GET['default_key'] ) );
		}
		
		/** DELETE Fanpage */
		if( isset( $_GET['delete_key'] ) )
		{
			return ips_admin_redirect( 'fanpage', 'action=settings', Facebook_Fanpage::deleteFanpage( $_GET['delete_key'] ) );
		}
		
		echo '
		<form action="" enctype="multipart/form-data" method="post">
			' . displayArrayOptions( array(	
				'apps_fanpage_array' => array(
					'option_new_block' => __('apps_fanpage_array_block'),
					'option_type' => 'text',
					'option_value' => '<div class="input_with_button"><input type="text" value="" size="40" name="apps_fanpage_add"><input type="submit" class="button" value="' . __('common_add') . '" /></div>',
					'option_depends' => 'demo_disabled',
				)
			)) . '
		</form>';
		
		if( !empty( $fanpages ) )
		{
			$pagin = new Pagin_Tool;
			
			echo $pagin->wrap()->addJS( 'fanpage_urls', 1 )->addMessage('')->get();
		}
		
		echo '
		<form action="admin-save.php" enctype="multipart/form-data" method="post">		
			' . displayArrayOptions( getOptionsFile()['options_fanpage'] ) . ' 
			<input type="submit" class="button" value="' . __('save') . '" />
		</form>';
		
		Session::set( 'admin_redirect', admin_url( 'fanpage', 'action=settings' ) );		
	}
	elseif( $action == 'delete' )
	{
		if( isset( $_GET['post_id'] ) && !empty( $_GET['post_id'] ) )
		{	
			if( fanpageDelete( Sanitize::cleanSQL( $_GET['post_id'] ) ) )
			{
				ips_message( array(
					'info' =>  __('fanpage_post_deleted')
				) );
			}
			else
			{
				ips_message( array(
					'alert' =>  __('fanpage_post_delete_error')
				) );
			}
		}
		
		ips_admin_redirect('fanpage');
	}
	else
	{

		$pagin = new Pagin_Tool;	
					
		echo $pagin->addSelect( 'sort_by', array(
			'post_data' => 'date'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->wrapSelects()->wrap()->addJS( 'facebook', $PD->cnt( 'fanpage_posts') )->addMessage('')->get();	

	}
	echo '
		<div class="div-info-message">
			<p>' . __( 'fanpage_upload_info' ) . '</p>
		</div>
	';