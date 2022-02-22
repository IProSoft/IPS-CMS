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

	if ( !empty( $_POST ) )
	{

		if ( isset( $_POST['mailing']['subject'] ) && isset( $_POST['mailing']['content'] ) && isset( $_POST['mailing']['footer'] ) )
		{	
			
			$mailing = array(
				'subject' => $_POST['mailing']['subject'],
				'content' => stripslashes( htmlspecialchars_decode( $_POST['mailing']['content'] ) ),
				'footer' => $_POST['mailing']['footer'],
				'only_adult' => $_POST['mailing']['only_adult'],
				'activ_status' => $_POST['mailing']['activ_status'],
				'user_language' => serialize( $_POST['mailing']['user_language'] ),
				'status' => 'draft'
			);
			
			if( !isset( $_POST['mailing']['edit'] ) || !is_numeric( $_POST['mailing']['edit'] ) )
			{
				$insert_id = PD::getInstance()->insert( 'mailing_service', $mailing );
			}
			else
			{
				$insert_id = (int)$_GET['mailing_id'];
				PD::getInstance()->update( 'mailing_service', $mailing, array(
					'mailing_id' => $insert_id
				) );
			}
			
			if( $insert_id )
			{
				ips_admin_redirect( 'mailing', 'action=preview&mailing_id=' . $insert_id, __('mailing_saved') );    
			}                  
		}
		else
		{
			echo admin_msg( array(
				'alert' => __('fill_in_required_fields')
			) );
		}
	} 
	
	$action = isset( $_GET['action'] ) ? $_GET['action'] : 'view';
	
	echo admin_caption( 'mailing_title' );
	echo '
		<div class="tabbed_area" style="padding:10px">
			<a class="button" href="' . admin_url( 'mailing', 'action=add' ) . '">' . __( 'common_add' ) . '</a>
			<a class="button" href="' . admin_url( 'mailing', 'action=view' ) . '">' . __( 'browse' ) . '</a>
			<a class="button" href="' . admin_url( 'mailing', 'action=settings' ) . '">' . __( 'settings' ) . '</a>
		</div>';
		
	if( $action == 'settings' )
	{
		include_once( IPS_ADMIN_PATH .'/cron-functions.php');

		if( Config::getArray( 'mailing_options', 'send_type' ) == 'cron' && Config::getArray( 'mailing_options', 'cron_added' ) == false )
		{
			$cron_key = ips_add_cron( time(), 'quarter', 'cron_Mailing_Admin',  array() );

			Config::update( 'mailing_options', array(
				'cron_added' => $cron_key 
			) );
		}
		elseif( Config::getArray( 'mailing_options', 'send_type' ) == 'normal'  )
		{
			ips_cron_delete_by_func( 'cron_Mailing_Admin' );
			
			Config::update( 'mailing_options', array(
				'cron_added' => false
			) );
		}

		echo '

			<form method="post" enctype="multipart/form-data" action="admin-save.php">
				' . displayArrayOptions( array( 
						'mailing_options' => array(
							
							'option_is_array' => array(
								'send_type' => array( 
									'option_set_text' => false,
									'option_select_values' => array(
										'normal' =>  __('mailing_options_normal') ,
										'cron' =>  __('mailing_options_cron')
									),
								) 
							),
					))) . '
				<input type="submit" value="' . __( 'save' ) . '" class="button" />
			</form>
			<div class="div-info-message">
				' . __('mailing_options_info') . '
			</div>
	
		';
		
		Session::set( 'admin_redirect', admin_url( 'mailing', 'action=settings' ) );
	}
	elseif( $action == 'view' )
	{
		$pagin = new Pagin_Tool;	
				
		echo $pagin->wrap()->addJS( 'mailing_service', $PD->cnt( 'mailing_service' ) )->addMessage('')->get();
	
	}
	elseif( $action == 'send' && isset( $_GET['mailing_id'] ) )
	{
		
		$active = PD::getInstance()->cnt( 'mailing_service', array(
			'status' => 'sending',
			'mailing_id' => array( (int)$_GET['mailing_id'], '!=' )
		));
		
		if( $active == 0 )
		{
			PD::getInstance()->update( 'mailing_service', array(
				'status' => 'sending'
			), array(
				'mailing_id' => (int)$_GET['mailing_id']
			) );
				
			if( Config::getArray( 'mailing_options', 'send_type' ) !== 'cron' )
			{
				
				$mailing = PD::getInstance()->select( 'mailing_service', array(
					'status' => 'sending'
				), 1 );
				
				$page_num = isset( $_GET['page'] ) ? $_GET['page'] : 1;
				
				if( $page_num < $mailing['page_num'] )
				{
					$page_num = $mailing['page_num'];
				}
				
				$mailing = Ips_Registry::get( 'Mailing_Admin' )->send( $page_num );
				
				if( $mailing === true )
				{
					return ips_admin_redirect( 'mailing', false, __('mailing_completed') );
				}
				
				echo admin_msg( array(
					'info' => __s( 'mailing_send_status', $mailing['users_send'], $mailing['users_not_send'] )
				) );
				
				echo '
				<script>
				$(function() {
					setTimeout(function(){
						window.location.href = "' . admin_url( 'mailing', 'action=send&mailing_id=' . $_GET['mailing_id'] . '&page=' . ( $page_num + 1 ) ) . '";
					}, 10000 );
				});
				</script>
				
				';
			}
			else
			{
				ips_admin_redirect( 'mailing', false, __('mailing_sending') );
			}
		}
		else
		{
			ips_admin_redirect( 'mailing', false, __('mailing_send_pending') );
		}
	}
	elseif( $action == 'delete' && isset( $_GET['mailing_id'] ) )
	{
		PD::getInstance()->delete( 'mailing_service', array(
			'mailing_id' => (int)$_GET['mailing_id']
		) );
		ips_admin_redirect( 'mailing', false, __('deleted') );
	}
	elseif( $action == 'stop' )
	{
		PD::getInstance()->update( 'mailing_service', array(
			'status' => 'draft'
		) );
		ips_admin_redirect( 'mailing', false, __('mailing_saved') );
	}
	elseif( $action == 'preview' && isset( $_GET['mailing_id'] ) )
	{
		
		$mailing = PD::getInstance()->select( 'mailing_service', array(
			'mailing_id' => (int)$_GET['mailing_id']
		), 1 );
		
		echo Templates::getInc()->getTpl( 'email.html', array(
			'email_content'	=> $mailing['content'],
			'email_title'	=> $mailing['subject'],
			'email_footer'	=> '<br />' . $mailing['footer']
		) );
		
		echo '
		
		<a class="button" href="' . admin_url( 'mailing', 'action=send&mailing_id=' . $_GET['mailing_id'] . '' ) . '">' . __( 'mailing_start' ) . '</a>
		<a class="button" href="' . admin_url( 'mailing', 'action=view' ) . '">' . __( 'save' ) . '</a>
		<a class="button" href="' . admin_url( 'mailing', 'action=edit&mailing_id=' . $_GET['mailing_id'] . '' ) . '">' . __( 'mailing_edit' ) . '</a>
		<a class="button" href="' . admin_url( 'mailing', 'action=delete&mailing_id=' . $_GET['mailing_id'] . '' ) . '">' . __( 'mailing_delete' ) . '</a>
		
		
		';
	}
	elseif( $action == 'add' || $action == 'edit' )
	{
		if( $action == 'edit' && isset( $_GET['mailing_id'] ))
		{
			
			$mailing = PD::getInstance()->select( 'mailing_service', array(
				'mailing_id' => (int)$_GET['mailing_id']
			), 1 );
			
			if( empty( $mailing ) )
			{
				ips_admin_redirect( 'mailing' );   
			}
			
			$mailing_id = (int)$_GET['mailing_id'];
		}
		
	
		echo '
		
		<script type="text/javascript" src="/libs/tinyeditor/tiny.editor.packed.js"></script>
		<link rel="stylesheet" href="/libs/tinyeditor/tinyeditor.css">


		<form method="post" action="">
			'.displayarrayOptions(array(
				'mailing[subject]' => array(
					'current_value' => ( isset( $mailing['subject'] ) ? $mailing['subject'] : '' ),
					'option_type' => 'input',
					'option_lenght' => false
				),
				'mailing[content]' => array(
					'current_value' => ( isset( $mailing['content'] ) ? $mailing['content'] : '' ),
					'option_type' => 'textarea',
					'option_lenght' => false,
					'option_css' => 'tiny_editor'
				),
				'mailing[footer]' => array(
					'current_value' => ( isset( $mailing['footer'] ) ? $mailing['footer'] : '' ),
					'option_set_text' => htmlspecialchars( __s( 'mailing_footer', ABS_URL . 'edit_profile' ) ),
					'option_type' => 'textarea',
					'option_lenght' => false,
				),
				'test_mail' => array(
					'current_value' => '',
					'option_css' => 'mailing_test',
					'option_type' => 'text',
					'option_value' => '<input type="text" name="test_mail" class="test_mail_input" /><button class="button test_mail">'.__('mailing_send').'</button>
					<span class="test_mail_input_status"><img src="images/icons/update-success.png" /></span>'
				),
				'mailing[only_adult]' => array(
					'current_value' => ( isset( $mailing['only_adult'] ) ? $mailing['only_adult'] : 0 ),
					'option_names' => 'yes_no',
				),
				'mailing[activ_status]' => array(
					'current_value' => ( isset( $mailing['activ_status'] ) ? $mailing['activ_status'] : 'all' ),
					'option_select_values' => array(
						0 => __('mailing_activ_status_title_unactiv'),
						1 => __('mailing_activ_status_title_acitv'),
						'all' =>  __('common_all'),
					)
				),
				'mailing[user_language]' => array(
					'current_value' => ( isset( $mailing['user_language'] ) && is_serialized( $mailing['user_language'] ) ? unserialize( $mailing['user_language'] ) : array() ),
					'option_value_as_key' => true,
					'option_select_values' => Translate::codes(),
					'option_multiple' => true,
				),
			)).'

			<input type="hidden" name="mailing[edit]" value="' . ( isset( $mailing_id ) ? $mailing_id : false ) . '" />
			
				
			<input type="submit" id="mailingsubmit" class="button" value='.__('mailing_preview').'>
			
		</form>
		
		<script type="text/javascript">
		$(document).ready(function(){
			$("#mailingsubmit").on("click", function(e){
				var form = $(this).parents("form");
				if( form.find("input").first().val() == ""  )
				{
					alert("' . __ ('fill_in_required_fields') . '");
					return false;
				}
				else
				{
					editor.post();
				}
			});	
		});						
		</script>
		';
	}
	
?>