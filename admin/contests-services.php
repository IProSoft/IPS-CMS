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

	global $PD;
	
	echo admin_caption( 'contest_title' );

	Config::update( 'contest_option_count', $PD->cnt( 'contests' ) );
	
	$contest_action = isset( $_GET['contest_action'] ) && !empty( $_GET['contest_action'] ) && Config::get( 'contest_option' ) ? $_GET['contest_action'] : ( Config::get( 'contest_option' ) ? 'view' : 'settings' );
	
	if( !empty( $_FILES["file"]["tmp_name"] ) )
	{
		$file_name = isset( $_POST['contest_id'] ) ? md5( $_POST['contest_id'] ) : md5( rand().time() );

		$file_name = uploadAdminImage( $file_name, array(
			'resize' => Config::get('file_max_width')
		), ABS_PATH . '/upload/contest_files' );
	}

	
	echo responsive_menu( array(
		'add' => 'contest_add',
		'view' => 'contest_browse',
		'settings' => 'contest_settings'
	), admin_url( 'contests', 'contest_action=' ), false  );
	
	
	
	
	
	
	if(  $contest_action == 'settings'  )
	{
		echo '
		<form method="post" enctype="multipart/form-data" action="admin-save.php">
			' . displayArrayOptions( array( 
					'contest_option'
				)) . '
			<input type="submit" value="' . __( 'save' ) . '" class="button" />
		</form>
		';
	}
	elseif(  $contest_action == 'delete'  )
	{
		if( $PD->delete( 'contests', array( 'id' => $_GET['contest_id'] ) ) )
		{
			ips_admin_redirect( 'contests', 'contest_action=view',  __('contest_removed'));
		}
	}
	elseif(  $contest_action == 'edit'  )
	{
		if( !empty( $_POST ) )
		{
			if( empty( $_POST['contest_id'] ) || empty($_POST['contest_description']) ||  empty($_POST['contest_title']) )
			{
				echo ips_message( array(
					'alert' =>  __('fill_in_required_fields')
				), true );
			}
			else
			{
				$contest_winner = empty( $_POST['contest_winner'] ) ? '' : Sanitize::cleanSQL( $_POST['contest_winner'] );
				$file_name = empty( $file_name ) ? basename( $_POST['contest_thumb'] ) : $file_name;

				$dane = array(
					'contest_title' => Sanitize::cleanSQL( $_POST['contest_title'] ),
					'contest_description' => Sanitize::cleanXSS( convert_line_breaks( $_POST['contest_description'] ) ),
					'contest_activ' => (int)$_POST['contest_activ'],
					'contest_winner' => $contest_winner,
					'contest_type' => Sanitize::onlyAlphanumeric($_POST['contest_type']), 
					'contest_thumb' => $file_name,
					'contest_category_id' => ( isset( $_POST['contest_category'] ) && !empty( $_POST['contest_category'] ) ? $_POST['contest_category'] : '0' ),
				);
		
				if( $PD->update('contests', $dane,  array( 'id' => $_POST['contest_id'] ) ) )
				{
					ips_message( array(
						'info' =>  __('settings_saved')
					) );
					ips_admin_redirect('contests');
				}
			}
		}
		
		$row = $PD->select( 'contests', array(
			'id' => (int)$_GET['contest_id']
		), 1);
		
		$row = array_merge( $row, $_POST );
		
		if( empty( $row ) )
		{	
			ips_admin_redirect( 'contests', false, __('contest_no_with_id'));
		}
		if( !empty( $row['contest_thumb'] ) )
		{
			$row['contest_thumb'] = ABS_URL . 'upload/contest_files/' . $row['contest_thumb'];
		}
		
		$row['contest_description'] = convert_line_breaks( $row['contest_description'] );
		
		$row['category_list'] = Categories::categorySelectOptions( $row['contest_category_id'] ) ;
		
		
		echo Templates::getInc()->getTpl( '/__admin/contest_form.html', $row );
		
		
	}
	elseif( $contest_action == 'view' )
	{

		$pagin = new Pagin_Tool;
		
		echo $pagin->addSelect( 'sort_by', array(
			'id' => 'ID',
			'contest_start' => 'contest_start',
			'contest_expire' => 'contest_end',
		) )->addSelect( 'sort_by_order', array(
			'DESC' => 'desc',
			'ASC' => 'asc'
		) )->wrapSelects()->wrap()->addJS( 'contests', $PD->cnt("contests") )->addMessage('')->get();
		
	}
	elseif( $contest_action == 'add' )
	{			
		if( !empty( $_POST ) )
		{
			if( isset( $_POST['contest_thumb'] ) && !empty( $_POST['contest_thumb'] ) && ( !isset( $file_name ) || empty( $file_name ) ) )
			{
				$file_name = basename( $_POST['contest_thumb'] );
			}
			
			unset( $_POST['contest_thumb'] );
			
			if( !isset( $file_name ) || empty( $file_name ) )
			{
				echo ips_message( array(
					'alert' =>  __('contest_add_thumbnail')
				), true );
			}
			elseif( empty( $_POST['contest_type'] ) || empty( $_POST['contest_description'] ) ||  empty( $_POST['contest_title'] ) )
			{
				echo ips_message( array(
					'alert' =>  __('fill_in_required_fields')
				), true );
			}
			else
			{
				$contest_start = Sanitize::cleanSQL( $_POST['contest_start'] ) . ':00';
				$contest_expire = Sanitize::cleanSQL($_POST['contest_expire']) . ':00';
				
				if( $contest_expire > $contest_start )
				{
						$dane = array(
							'contest_title' => Sanitize::cleanSQL($_POST['contest_title']), 
							'contest_description' => Sanitize::cleanXSS( nl2br( $_POST['contest_description'] ) ), 
							'contest_activ' => (int)$_POST['contest_activ'], 
							'contest_winner' => '',
							'contest_start' => $contest_start,
							'contest_expire' => $contest_expire,					
							'contest_type' => Sanitize::onlyAlphanumeric( $_POST['contest_type'] ), 
							'contest_thumb' => $file_name,
							'contest_category_id' => ( isset( $_POST['contest_category'] ) && !empty( $_POST['contest_category'] ) ? $_POST['contest_category'] : '0' ),
						);
						
						$contest_add = $PD->insert("contests", $dane);
						
						if( $contest_add )
						{
							$PD->update('contests', array( 
								'contest_thumb' => md5( $contest_add ) . '.jpg'
							), array( 
								'id' => $contest_add
							) );
							
							rename( ABS_PATH . "/upload/contest_files/" . $file_name, ABS_PATH . "/upload/contest_files/" . md5( $contest_add ) . '.jpg' );
							
							ips_admin_redirect( 'contests', false, __('contest_saved') );
						}
				}
				else
				{
					echo ips_message( array(
						'alert' =>  __('contest_selected_date_passed')
					), true );
				}
			}
		} 
		
		$row = array_merge( array(
			'id' => null,
			'contest_thumb' => ( !isset( $file_name ) ? null : ABS_URL . 'upload/contest_files/' . $file_name ),
			'contest_description' => '',
			'category_list' => Categories::categorySelectOptions( false ),
			'contest_title' => '',
			'contest_activ' => 0,
			'contest_type' => 'share',
			'contest_status' => '',
			'contest_start' => ''
		), $_POST );
		
		echo Templates::getInc()->getTpl( '/__admin/contest_form.html', $row );
				
	}
	
	
?>