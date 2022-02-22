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
	
	echo admin_caption( 'caption_admin' );
	
	require_once ( IPS_ADMIN_PATH .'/libs/class.SystemTools.php' );
	
	$system = new System_Tools();
	
	if( isset( $_POST['optimize'] ) )
	{
		$system->optimize();
		echo admin_msg( array(
			'info' => __('system_optimize_success') 
			) );
	}
	
	if( isset( $_GET['clear-system'] ) )
	{
		$system->resetSystem();
		echo admin_msg( array(
	     'info' =>  __('clear_system_done') 
         ) );
	}
	
	if( isset( $_GET['backup'] ) )
	{
		$backupDatabase = new Database_Backup();
		switch( $_GET['backup'] )
		{
			case 'files':
				if( isset( $_GET['start'] ) )
				{
					if( file_exists( OUTPUT_SQL . '/backup_files/' ) || mkdir( OUTPUT_SQL . '/backup_files/', 0777 ) )
					{
						if( isset( $_GET['zip'] ) )
						{
							$status = $system->backupZip( OUTPUT_SQL . '/backup_files' );
						}
						elseif( isset( $_GET['dir'] ) )
						{
							$status = $system->backupFiles();
						}
						else
						{
							$status = $system->backup( ABS_PATH, OUTPUT_SQL . '/backup_files' );
						}
						
						echo Templates::getInc()->getTpl( '/__admin/update_status.html', array(
							'table_name' => __( 'common_operations' ),
							'row_first' => __( 'common_status' ),
							'row_second' => __( 'common_folder' ),
							'data' => $status
						));
					}
					else
					{
						echo __( 'backup_create_directory' );
					}
				}
				else
				{
					$size = $system->checkFileSize( ABS_PATH, 1000 );
					
					echo ips_message( array(
						'info' => __s('backup_will_take_about', $size . ' MB' )
					), true ) .'<br />' 
					. __s('backup_safe_size' )
					.'<br /><a href="' . admin_url( '/', 'backup=files&start=true' ) . '" class="button">'.__('statistic_start').'</a><br />';
				}
			break;
			case 'mysql':
	

				$status = $backupDatabase->backupTables();
				
			
				if( $status === true )
				{
					
					$status = array( array( 
						'status' => true,
						'msg' => __s('statistic_saved_directory_backup', basename( $backupDatabase->db_file ), 'backup/' . basename( $backupDatabase->db_file ) )
					));
					
					if ( strpos( ABS_URL, 'skrypt.ips' ) !== false )
					{
						$backupDatabase->backupInstall();
					}
				}
				else
				{
					$status = array( array( 
						'status' => true,
						'msg' =>  __s( 'backup_tables_count', $backupDatabase->status )
					));
						
					if( isset( $backupDatabase->saved ) && !empty( $backupDatabase->saved ) )
					{
						foreach( $backupDatabase->saved as $table )
						{
							$status[] = array( 
								'status' => true,
								'msg' => __s( 'backup_tables_content', $table )
							);
						}
					}
					
					$status[] = array( 
						'status' => 'loader',
						'msg' => __s( 'backup_tables_content_save', ( get_input( 'current_table' ) ? '(' . $_GET['current_table'] . ')' : '' ) )
					);
				}
				
				echo Templates::getInc()->getTpl( '/__admin/update_status.html', array(
					'table_name' => __( 'common_operations' ),
					'row_first' => __( 'common_status' ),
					'row_second' => __( 'common_file' ),
					'data' => $status
				));
				
			break;
		}
		echo '<br /><br /><a href="'. admin_url( '/' ) . '" class="button">'.__('statistic_return').'</a><br />';
	}
	else
	{
		
		$admin_stats = $system->count_stats();
			
		if( !empty( $admin_stats ) )
		{
			echo '
			<div class="admin_stats">
				<ul>
					<li><div class="fancy-square"><a title="" class="count_stats" href="#">' . $admin_stats['count_all_shares'] . ' udostępnień</a><span>+' . $admin_stats['count_stats_shares'] . ' ' . __( 'stats_shares' ) . '</span></div></li>
					<li><div class="fancy-square"><a title="" class="count_stats" href="#">' . $admin_stats['count_all_users'] . ' użytkowników</a><span>+' . $admin_stats['count_stats_register'] . ' ' . __( 'stats_register' ) . '</span></div></li>
					<li><div class="fancy-square"><a title="" class="count_stats" href="#">' . $admin_stats['count_all_files'] . ' plików</a><span>+' . $admin_stats['count_stats_files'] . ' ' . __( 'stats_files' ) . '</span></div></li>
					<li><div class="fancy-square"><a title="" class="count_stats" href="#">' . $admin_stats['count_all_comments'] . ' komentarzy</a><span>+' . $admin_stats['count_stats_files'] . ' ' . __( 'stats_comments' ) . '</span></div></li>
				</ul>
			</div>';
		}
		
		if( file_exists( ABS_PATH . '/error_log.log' ) && filesize( ABS_PATH . '/error_log.log' ) > 41943040 )
		{
			copy( ABS_PATH . '/error_log.log', ABS_PATH . '/logs/all_logs/error_log.log');
			unlink( ABS_PATH . '/error_log.log' );
		}
		if( file_exists( ABS_PATH . '/php.log' ) && filesize( ABS_PATH . '/php.log' ) > 41943040 )
		{
			copy( ABS_PATH . '/php.log', ABS_PATH . '/logs/all_logs/php.log');
			unlink( ABS_PATH . '/php.log' );
		}
		if( file_exists( ABS_PATH . '/logs/error-history.log' ) && filesize( ABS_PATH . '/logs/error-history.log' ) > 41943040 )
		{
			copy( ABS_PATH . '/logs/error-history.log', ABS_PATH . '/logs/all_logs/error-history.log');
			unlink( ABS_PATH . '/logs/error-history.log' );
		}
		
		
		echo Templates::getInc()->getTpl( '/__admin/system_stats.html', array(
			'statistic_mysql' => $system->call('checkTables'),
			'statistic_shares' => $system->call('checkShares'),
			'statistic_counters' => $system->call('checkAddedFiles')
		));

		if( isset( $_GET['ga_enabled'] ) )
		{
			Config::update( 'analytics_enabled', $_GET['ga_enabled'] == 'true' );
			ips_redirect();
		}
		
		
		/* 
		if( Config::get('analytics_enabled') )
		{
			require ( IPS_ADMIN_PATH .'/libs/GAPI/gapi.class.php' );
			require ( IPS_ADMIN_PATH .'/libs/GAPI/GaAnalytics.php' );
			
			$ga = new Ga_Analytics();

			if( isset( $_POST['ga_data'] ) )
			{
				$ga->saveSettings( $_POST['ga_data'] );
			}
			if( !$ga->checkSettings() || !$ga->checkProfile() || isset( $_GET['ga_change'] )  )
			{
				echo $ga->settingsForm();
			}
			else
			{
				echo '
				<script type="text/javascript">
					loadStats( "load-stats", "div-ga-stats-container" );
				</script>
				<div id="div-ga-stats-container">
					<div class="nice-blocks features-table-actions-div">
						<div class="blocks-header" style="text-align: center;">
							'.__('statistic_loading').' <img src="images/system-loading.gif" style="vertical-align: middle;"> 
						</div>
					</div>
				</div>
				';
			}

			if( strpos(ABS_URL, 'iprosoft.pl' ) == false )
			{	
				echo '<br />
				<a href="' . IPS_ADMIN_URL . '/admin.php?ga_change=true" class="button">'.__('change_google_analytics').'</a> 
				<a href="' . IPS_ADMIN_URL . '/admin.php?ga_enabled=false" class="button">'.__('disable_google_analytics').'</a><br />';
			}
		}
		else
		{
			echo '<br /><a href="' . IPS_ADMIN_URL . '/admin.php?ga_enabled=true" class="button">'.__('enable_google_analytics').'</a><br />';
				
				
		} 
		
		
		
		echo '<br />
		<div class="div-info-message">
			<p><strong>Info</strong>
			'.__('statistic_info_1').'<br />
			<br />
			'.__('statistics_updated_every_10_minutes').'
			<br /><br />
			</p>
		</div>'; */
	
	}	
?>