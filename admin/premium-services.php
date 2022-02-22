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

	echo admin_caption( 'action_premium' );
	
	if( !Config::get('services_premium') )
	{
		$_GET['action'] = 'options';
	}	
	
	echo responsive_menu( array(
		'services' => 'services_premium_browse',
		'add' => 'services_premium_add',
		'addpremium' => 'services_premium_add_user',
		'users' => 'premium_users',
		'options' => 'settings',
	), admin_url( 'premium', 'action=' ) );
	
	$action = isset( $_GET['action'] ) ? $_GET['action'] : 'options';
	
	if( $action == 'add' ||  $action == 'change' )
	{
		if( isset( $_POST['services_premium'] ) )
		{
			$settings = $_POST['services_premium'];
			$messages = array();
			
			if( $settings['sms_codes_verify'] == 0 && empty( $settings['provider_id'] ) )
			{
				$messages[] = __('services_premium_enter_provider_id');
			}
			elseif( $settings['sms_codes_verify'] == 0 && empty( $settings['sms_service_name'] ) )
			{
				$messages[] = __('services_premium_enter_sms_service_name' );
			}
			elseif( !is_numeric( $settings['sms_number'] ) )
			{
				$messages[] = __('services_premium_enter_correct_number');
			}
			elseif( empty( $settings['sms_content'] ) )
			{
				$messages[] = __('services_premium_enter_content_sms');
			}
			elseif( empty( $settings['sms_price'] ) )
			{
				$messages[] = __('services_premium_enter_price_of_sms');
			}
			elseif( empty( $settings['sms_extend_premium'] ) )
			{
				$messages[] = __('services_premium_days_extend');
			}
			else
			{
				$premium = new PremiumPay();
				$service_id = $premium->addService( $settings );
				
				if( $service_id )
				{
					if( !empty( $settings['codes_list'] ) || !empty( $_FILES['codes_file']["tmp_name"] ) )
					{
						$import_codes_list = ( isset( $settings['codes_list'] ) ? $settings['codes_list'] : null );
						$import_codes_file = ( isset( $_FILES['import_codes_file']["tmp_name"] ) ? $_FILES['import_codes_file']["tmp_name"] : null );
						
						if( !empty( $import_codes_list ) || !empty( $import_codes_file ) )
						{
							addCodes( $import_codes_list, $import_codes_file, $service_id );
						}
					}
					
					$messages[] = __('services_premium_added');
				}
				
				if( PD::getInstance()->cnt('premium_codes') == 0 && $settings['sms_codes_verify'] == 1 )
				{
					$messages[] = __('settings_sms_code_empty');
				}
			}
			
			ips_admin_redirect( 'premium', 'action=' 
			. ( !isset( $service_id ) || !$service_id ? 'add' 
			. ( isset( $_GET['service_id'] ) ? '&service_id=' . (int)$_GET['service_id'] : '' ) : 'services' ), implode( '<br />', $messages ) );
		}
		
		Config::tmp( 'services_premium', array( 
			'sms_codes_verify' => 0
		));
		
		echo PremiumPay::form( ( isset( $_GET['service_id'] ) ? (int)$_GET['service_id'] : false ) );	
	}
	elseif( $action == 'delete' && isset( $_GET['service_id'] ) )
	{
		if( isset( $_GET['confirm'] ) )
		{
			
			$premium = new PremiumPay();
			$premium->deleteService( (int)$_GET['service_id'] );
			
			ips_admin_redirect( 'premium', 'action=services', 'services_premium_removed' );
		}
		
		echo admin_msg( array(
			'alert' => __s('services_premium_service_warning', $_GET['service_id'] )
		) );
		
		
		
	}
	elseif( $action == 'addpremium' )
	{
		echo '
		<form action="admin-save.php" enctype="multipart/form-data" method="post">
			' . displayArrayOptions(array(
				'add_premium' => array(
					'current_value' => '',
					'option_set_text' => 'services_premium_add_users_list',
					'option_type' => 'input'
				),
				'add_premium_time' => array(
					'current_value' => '',
					'option_set_text' => 'services_premium_add_users_days',
					'option_type' => 'input'
				)
			)) . '
		<input name="sms_services_form" type="submit" class="button" value="' . __('save') . '" />
		</form>';		
	}
	elseif( $action == 'users' )
	{
		if( isset( $_POST['action_users_time'] ) )
		{
			$user = $PD->from( 'premium_users')->where( 'user_id', (int)$_POST['action_users_id'] )->getOne();
			
			if( $PD->update( 'premium_users', array( 'days' => $user['days'] + (int)$_POST['action_users_time'] ), array(
				'user_id' => (int)$_POST['action_users_id']
			) ) )
			{
				echo admin_msg( array(
					'info' => __s( 'services_premium_extend_users_id_success', $_POST['action_users_id'], (int)$_POST['action_users_time'] )
				) )	;
			}
		}
	
		if( isset($_GET['action_users']) && $_GET['action_users'] == 'delete' )
		{
			if( $PD->delete('premium_users', array(
				'user_id' => IPS_ACTION_GET_ID
			)))
			{
				echo admin_msg( array(
					'info' => __s('services_premium_add_users_id', IPS_ACTION_GET_ID ) 
				) );
			}
		}	
	
		if( isset( $_GET['action_users'] ) && $_GET['action_users'] == 'renew' )
		{
			echo '
			<form action="' . admin_url( 'premium', 'action=users' ) . '" method="post">
				' . displayArrayOptions(array(
					'action_users_time' => array(
						'current_value' => '',
						'option_set_text' => 'services_premium_extend_users_id',
						'option_type' => 'input'
					)
				)) . '
				<input type="hidden" name="action_users_id" value="'.IPS_ACTION_GET_ID.'" />
				<input name="action_users" type="submit" class="button" value= '.__('extend').' />
			</form>
			
			';
		}
		else
		{
		
			$pagin = new Pagin_Tool;	
			
			echo $pagin->addSelect( 'sort_by', array(
				'premium_users.days' => 'services_premium_days',
				'premium_users.premium_from' => 'services_premium_date'
			) )->addSelect( 'sort_by_order', array(
				'DESC' => 'desc',
				'ASC' => 'asc'
			) )->wrapSelects()->wrap()->addJS( 'premium_users', $PD->cnt( 'premium_users' ) )->addMessage('')->get();	
		}

	}
	elseif( $action == 'services' )
	{
		$pagin = new Pagin_Tool;	
		
		echo $pagin->addSelect( 'sort_by', array(
			'premium_services.sms_price' => 'sms_price',
			'premium_services.sms_extend_premium' => 'sms_extend_premium'
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->wrapSelects()->wrap()->addJS( 'premium_services', $PD->cnt( 'premium_services' ) )->addMessage('')->get();

	}
	else
	{
		echo '
		<form action="admin-save.php" enctype="multipart/form-data" method="post">
			' . displayArrayOptions(array(
				'services_premium',
				'services_premium_options' => array(
					'option_depends' => 'services_premium',
					'option_new_block' => __('services_premium_service_list'),
					'option_is_array' => array(
						'category' => array(
							'option_display_name' => false,
							'option_set_text' => 'services_premium__categories'
						),
						'add' => array(
							'option_set_text' => 'services_premium__upload',
						),
						'comments' => array(
							'option_set_text' => 'services_premium__comment'
						)
					)
				)
			)) . '
			<div class="div-info-message">
				<p>'.__('action_premium_info').'</p>
			</div>
			<input name="sms_services_form" type="submit" class="button" value="' . __('save') . '" />
		</form>';

	}
	
	Session::set( 'admin_redirect', admin_url( 'premium', 'action=' . $action ) );
	
	echo '<a style="color: red; margin: 20px 0; display: block; width: 270px;" href="http://dotpay.pl/files/dotpay_instrukcja_techniczna_uslug_premium.pdf" target="_blank">'.__('services_premium_dotpay_info').'</a>';








?>