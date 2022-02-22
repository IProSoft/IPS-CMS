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

class System_Tools 
{

	public $copied = 0;
	/**
	* Wywoływanie poszczególnych funkcji lub zwracanie pliku cache
	*
	* @param $function - nazwa funkcji
	* 
	* @return string
	*/
	public function count_stats()
	{
		$admin_stats = Config::getArray('admin_last_visit_stats');
		
		if( empty( $admin_stats ) )
		{
			$admin_stats = array(
				'count_stats_shares' => 0,
				'current_date' => date("Y-m-d H:i:s")
			);
		}
		else
		{
			if( Session::has('current_admin_login') )
			{
				//return $admin_stats;
			}
			
			$admin_stats['count_stats_shares'] = array_sum( array_values( $this->checkShares( true ) ) ) - (int)$admin_stats['count_stats_shares'];
			
			if( $admin_stats['count_stats_shares'] < 0 )
			{
				$admin_stats['count_stats_shares'] = 0;
			}
		}

		$admin_stats = array_merge( $admin_stats, array(
			'count_stats_register' => PD::getInstance()->cnt( 'users', "date_add >= '" . $admin_stats['current_date'] . "'"),
			'count_stats_files' => PD::getInstance()->cnt( IPS__FILES, "date_add > '" . $admin_stats['current_date'] . "'"),
			'count_stats_comments' => PD::getInstance()->cnt( 'upload_comments', "date_add > '" . $admin_stats['current_date'] . "'"),
			
			'current_date' => date("Y-m-d H:i:s"),
			
			'count_all_comments' => PD::getInstance()->cnt( 'upload_comments' ),
			'count_all_shares' => PD::getInstance()->cnt( 'shares' ),
			'count_all_users' => PD::getInstance()->cnt( 'users' ),
			'count_all_files' => PD::getInstance()->cnt( IPS__FILES ),
		));
		
		
		Config::update( 'admin_last_visit_stats', $admin_stats );
		
		Session::set( 'current_admin_login', true );
		
		return $admin_stats;
		
	}
	public function call( $function )
	{
		
		return call_user_func( array( $this, $function ) );
		
		$cache_name = 'statistics-' . md5( $function ) ;
		
		Ips_Cache::setCacheLifetime( ( isset( $_POST['optimize'] ) ? 0 : 300 ) );
		
		if( !$cache = Ips_Cache::get( $cache_name ) )
		{
			$cache = call_user_func( array( $this, $function ) );
	
			Ips_Cache::write( $cache, $cache_name );
		}
		
		return $cache;
	}
	/**
	* Optymalizacja tabel MySQL
	*
	* @param 
	* 
	* @return 
	*/
    function optimize()
	{
        $result = PD::getInstance()->query( sprintf( 'SHOW TABLE STATUS FROM `%s`', DB_NAME ) );

		if ( count($result) > 0)
		{
            foreach( $result as $table_info )
			{
                PD::getInstance()->query( sprintf('OPTIMIZE TABLE `%s`', $table_info['Name'] ) );
			}
        }

		PD::getInstance()->truncate( 'temporary' );
		PD::getInstance()->truncate( 'users_online' );
		Ips_Cache::clear( 'statistics-' . md5( 'checkTables' ) );
		Ips_Cache::clear( 'statistics-' . md5( 'checkShares' ) );
		Ips_Cache::clear( 'statistics-' . md5( 'checkAddedFiles' ) );

    }

    /**
     * Wyświetlanie stanu tabel i możliwej ilości uzyskanej przestrzeni
     *
     * @return string $oversized
     */
    function checkTables() {
       
        $tot_data       = 0;
        $oversized      = 0;
        $result         = PD::getInstance()->query( sprintf( 'SHOW TABLE STATUS FROM `%s`', DB_NAME ) );
		$count			= !empty($result) ? count($result) : 0;
        $index_count    = 0;
		$to_clear		= 0;

        if ( $count > 0 )
		{
			foreach( $result as $table_info )
			{
				$total_data = $table_info['Data_length'];
                $total_index = $table_info['Index_length'];
                $total = round( ( $total_data + $total_index ) / 1024, 3 );
                $to_clear = round( floatval($table_info['Data_free']) / 1024, 2);
                $oversized += $to_clear;

				if( $to_clear > 0 )
				{
					$index_count++;
                }
			}
			
			$data = __s( 'system_optimize_tables', $index_count, $count ) . '<br /><br />';	
            $size = PD::getInstance()->query( sprintf( "SELECT sum( data_length + index_length ) / 1024 / 1024 AS mb FROM information_schema.TABLES WHERE table_schema = '%s'", DB_NAME ) );
			
			$data .= __s( 'system_optimize_table_size', round( $size[0]['mb'], 2 ) ) .'<br /><br />';
			
			$table_temporary = PD::getInstance()->cnt( 'temporary' );
			$table_online = PD::getInstance()->cnt( 'users_online' );
			
			if( $oversized > 0 || ( $table_temporary + $table_online ) > 0 )
			{

				$data .= __s( 'system_optimize_gain', round ( $oversized, 3 ) ) . '<br /><br />';
				$data .= __s( 'system_optimize_remove_records', $table_temporary + $table_online ) . '<br /><br />';
				$data .= ' <form method="post" action=""><input type="hidden" name="optimize"><button class="button">'.__('system_optimize_start').'</button></form>';
				
				return $data;
             
            }
			else 
			{
                return $data . __('system_optimize_not_required');
            }
        }
    }
	public function checkAddedFiles()
	{
		$db = PD::getInstance();

		
		
		if( IPS_VERSION == 'pinestic' )
		{
			$return = '
				<tr>
					<td>'.__('system_all').'</td>
					<td class="count-rows">' . $db->cnt( IPS__FILES ) . '</td>
				</tr>
				<tr>
					<td>'.__('system_public').'</td>
					<td class="count-rows">' . $db->cnt( IPS__FILES, array( 'pin_privacy' => 'public' ) ) . '</td>
				</tr>
				<tr>
					<td>'.__('system_private').' </td>
					<td class="count-rows">' . $db->cnt( IPS__FILES, array( 'pin_privacy' => 'private' ) ) . '</td>
				</tr>
			';
		}
		else
		{
			$return = '
				<tr>
					<td>'.__('system_main').'</td>
					<td class="count-rows">' . $db->cnt( IPS__FILES, array( 'upload_status' => 'public', 'upload_activ' => 1 ) ) . '</td>
				</tr>
				<tr>
					<td>'.__('system_waiting_room').'</td>
					<td class="count-rows">' . $db->cnt( IPS__FILES, array( 'upload_status' => 'public', 'upload_activ' => 0 ) ) . '</td>
				</tr>
				<tr>
					<td> '.__('system_archive').'</td>
					<td class="count-rows">' . $db->cnt( IPS__FILES, array( 'upload_status' => 'archive' ) ) . '</td>
				</tr>
				<tr>
					<td>'.__('system_private').' </td>
					<td class="count-rows">' . $db->cnt( IPS__FILES, array( 'upload_status' => 'private' ) ) . '</td>
				</tr>
			';
		}
		
		return '
				
			
			<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
			<thead></thead>
				<tbody>
					' . $return . '
				</tbody>
			</table>
			
			
			';
		
	}
	
	public function countAddedFiles()
	{
		$query = 'SELECT';
		$query .= "(SELECT COUNT(*) FROM " . db_prefix( IPS__FILES ) . "  WHERE date_add >= CONCAT( CURDATE( ) , ' 00:00:00' )) AS `today`,";
		$query .= "(SELECT COUNT(*) FROM " . db_prefix( IPS__FILES ) . "  WHERE date_add >= CONCAT( DATE_SUB(CURDATE(), INTERVAL 1 day ) , ' 00:00:00' ) AND date_add < CURDATE( ) ) AS `yesterday`,";
		$query .= "(SELECT COUNT(*) FROM " . db_prefix( IPS__FILES ) . "  WHERE date_add > ADDDATE(CURDATE( ), INTERVAL 1-DAYOFWEEK(CURDATE( )) DAY)) AS `week`,";
		$query .= "(SELECT COUNT(*) FROM " . db_prefix( IPS__FILES ) . "  WHERE date_add > ADDDATE(CURDATE( ), INTERVAL 1-DAYOFMONTH(CURDATE( )) DAY)) AS `month`,";
		$query .= "(SELECT COUNT(*) FROM " . db_prefix( IPS__FILES ) . " ) AS `all`";
		$query .= ';';

		$result = PD::getInstance()->query( $query );
		
		
		$file_size = $this->checkFileSize() . ' MB';
		
		$return = '
			
		<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
		<thead></thead>
			<tbody>
				<tr>
					<td>'.__('system_today').' </td>
					<td class="count-rows">' . $result[0]['today'] . '</td>
				</tr>
				<tr>
					<td>'.__('system_yesterday').' </td>
					<td class="count-rows">' . $result[0]['yesterday'] . '</td>
				</tr>
				<tr>
					<td>'.__('system_this_week').'</td>
					<td class="count-rows">' . $result[0]['week'] . '</td>
				</tr>
				<tr>
					<td>'.__('system_this_month').'</td>
					<td class="count-rows">' . $result[0]['month'] . '</td>
				</tr>
				<tr>
					<td>'.__('from_beginning').'</td>
					<td class="count-rows">' . $result[0]['all'] . '</td>
				</tr>
				<tr>
					<td>'.__('system_file_size').' </td>
					<td class="count-rows">' . $file_size . '</td>
				</tr>
			</tbody>
		</table>
		
		
		';
		
		
		return $return;
		
	}
	public function checkShares( $only_result = false )
	{
		$query = 'SELECT';
		$query .= '(SELECT SUM(share) FROM ' . db_prefix( 'shares' ) . ' ) AS `facebook`,';
		$query .= '(SELECT SUM(nk) FROM ' . db_prefix( 'shares' ) . ' ) AS `nk`,';
		$query .= '(SELECT SUM(google) FROM ' . db_prefix( 'shares' ) . ' ) AS `google`,';
		$query .= '(SELECT COUNT(*) FROM ' . db_prefix( 'fanpage_posts' ) . ' ) AS `fanpage`';
		$query .= ';';
		
		if( !isset( $this->checkShares ) )
		{
			$this->checkShares = PD::getInstance()->query( $query );
		}

		$result = $this->checkShares[0];
		
		if( $only_result )
		{
			return $result;
		}
		
		return '
			
		<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
		<thead></thead>
			<tbody>
				<tr>
					<td>'.__('system_facebook').'</td>
					<td class="count-rows">' . ( empty( $result['facebook'] ) ? 0 : $result['facebook'] ) . '</td>
				</tr>
				<tr>
					<td>'.__('system_nasza_klasa').'</td>
					<td class="count-rows">' . ( empty( $result['nk'] ) ? 0 : $result['nk'] ) . '</td>
				</tr>
				<tr>
					<td> '.__('system_google_plus').'</td>
					<td class="count-rows">' . ( empty( $result['google'] ) ? 0 : $result['google'] ) . '</td>
				</tr>
				<tr>
					<td>'.__('system_fanpage').'</td>
					<td class="count-rows">' . ( empty( $result['fanpage'] ) ? 0 : $result['fanpage'] ) . '</td>
				</tr>
			</tbody>
		</table>
		
		';

		
	}
	
	/**
	* Reset whole instalation
	*
	* @param null
	* 
	* @return null
	*/
	public function resetSystem()
	{
		
		$db = PD::getInstance();
		$erase_tables = array_diff( array_values( array_column( PD::getInstance()->query( "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = '" . DB_NAME . "'" ), 'TABLE_NAME' ) ), [
			'ads',
			'menus',
			'posts',
			'system_settings',
			'translations',
			'updates_table',
			'users',
			'users_data',
		] );
		
		foreach( $erase_tables as $table )
		{
			$db->truncate( $table );
		}
		
		$users = User_Data::getByValue( 'is_admin', 1, false );

		$db->from( 'users' )->where( 'id',  array_column( $users, 'user_id' ), 'NOT IN' )->remove();
		$db->from( 'users_data' )->where( 'user_id', array_column( $users, 'user_id' ), 'NOT IN' )->remove();
		
		$db->update( 'users', array(
			'avatar' => 'anonymus.png'
		));
		/* Reset AI column */
	
		$max_users = $db->select( 'users', null, 1, 'MAX(id) as max');
		$db->query( "ALTER TABLE " . db_prefix( 'users' ) . " AUTO_INCREMENT = " . $max_users['max'] . ";" );
		
		$erase_folders = array_merge( glob( ABS_PATH . '/cache/*', GLOB_ONLYDIR | GLOB_NOSORT ), glob( ABS_PATH . '/upload/*', GLOB_ONLYDIR | GLOB_NOSORT ) );
		
		foreach( $erase_folders as $folder )
		{
			removeEmptySubfolders( $folder );
			File::deleteDir( $folder );
		}
		
		

		Config::update( 'watermark_options', [
			'text' => '',
			'text_size' => 18,
			'text_font' => 'PT-Sans',
			'text_font_color' => '#a6a7ba',
			'text_opacity' => 50,
			'file' => '',
			'position' => 'top_left',
			'position_x' => 10,
			'position_y' => 10,
			'position_absolute' => '1',
			'opacity' => 100,
		] );
		
		Config::update( 'watermark', [
			'activ' => 0,
			'opacity' => 80,
			'angle' => 38,
			'file' => '',
		] );
		
		Config::update( 'upload_demot_signature', [
			'file' => '',
		] );
		
		clearCache( 'all' );
		
		Config::update( 'wait_counter', 0 );
		
		removeEmptySubfolders( ABS_PATH . '/upload/' );
		
		self::checkSystemDirs();
		
		Categories::defaultCategory();
	}
	
	public static function checkSystemDirs()
	{
		
		$errors = self::createSystemDirs();
		if( !empty( $errors ) )
		{
			$msg = '';
			foreach( $errors as $dir )
			{
				$msg .= __s( 'admin_create_dir', str_replace( ABS_PATH, '', $dir ) ) . '<br/>';
			}
			ips_message( $msg );
		}
	}
	public static function createSystemDirs( $chmod = 0755 )
	{
		$system_dirs = array(
			'cache' => array(
				'ips_cache', 
				'minify', 
				'img_cache', 
				'tpl_cache', 
				'cache_fonts'
			),
			'upload' => array(
				'category_images',
				'contest_files',
				
				'images',
				'images/large',
				'images/medium',
				'images/square',
				'images/thumb',
				'images/thumb-small',
				'images/thumb-mini',
				
				'img_avatar',
				'img_backup',  
				
				'import', 
				'import/drafts', 
				'tmp',
				
				'update', 
				'upload_mem', 
				'upload_ranking', 
				'upload_gallery', 
				'upload_video', 
				'system', 
				'system/watermark'
			),
		); 
		
	
	
	
		if( IPS_VERSION == 'pinestic' )
		{
			$system_dirs['cache'][] = 'ips_cache/pinit_cache';
			$system_dirs['cache'][] = 'ips_cache/pinit_cache/upload_images';
		}
		
		$errors = array();
		foreach( $system_dirs as $main_dir => $sub_dirs )
		{
			if( !file_exists( ABS_PATH . '/' . $main_dir ) )
			{
				if( File::createDir( ABS_PATH . '/' . $main_dir, $chmod ) )
				{
					$errors[] = ABS_PATH . '/' . $main_dir;
				}
			}
			
			if( file_exists( ABS_PATH . '/' . $main_dir ) )
			{
				foreach( $sub_dirs as $dir )
				{
					$dir = ABS_PATH . '/' . $main_dir. '/' . $dir;
					if( !file_exists( $dir ) && !File::createDir( $dir, $chmod ) )
					{
						$errors[] = $dir;
					}
				}
			}
		}
		
		/** Chmod 755 is not enaugh */
		if( $chmod == 0755 && !empty( $errors ) )
		{
			$errors = self::createSystemDirs( 0777 );
		}
		
		if( !file_exists( ABS_PATH . '/upload/img_avatar/anonymus.png' ) )
		{
			copy( ABS_PATH . '/images/anonymus.png', ABS_PATH . '/upload/img_avatar/anonymus.png' );
		}
		
		return $errors;
	}
	
	public function checkFileSize( $path = IMG_PATH, $max = false )
	{
		if( file_exists( $path ) )
		{
			$dir_iterator = new RecursiveDirectoryIterator( $path );
			$iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
			$size = 0;
			
			foreach ( $iterator as $file )
			{
				if ( $file->isFile() && $file->getFilename() != '.htaccess' && $file->getFilename() != '.'&& $file->getFilename() != '..'  && strpos( realpathDir( '', $file->getPathname() ), 'backup' ) === false )
				{
					$size += $file->getSize();
					if( $max )
					{
						if( $size > ( $max * 1024* 1024 ) )
						{
							return ' > ' . round( convertBytes( $size ) / 1048576, 1 );
						}
					}
				}
				
			}
			return round( convertBytes( $size ) / 1048576, 1 );
		}
		return 0;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function backup( $from_path, $to_path )
	{
		
		$iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $from_path, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD
		);

		$paths = array();
		
		foreach ( $iter as $path => $dir )
		{
			if ( $dir->isDir() && strpos( $path, 'backup' ) === false && strpos( $path, DIRECTORY_SEPARATOR . 'cache' ) === false )
			{
				$paths[] = $path;
			}
		}
		
		$paths = array_map( function( $path ){
			return str_replace( ABS_PATH, '', $path );
		}, $paths );
		
		array_unshift( $paths, DIRECTORY_SEPARATOR );
		
		Config::update( 'cache_data_backup', array( 
			'destination' => implode( DIRECTORY_SEPARATOR, explode( '/', $to_path ) ),
			'paths' => $paths,
			'activ' => 1,
			'copied' => 0
		), false );
		
		$this->reload();
		
		return array(array(
			'status' => 'loader',
			'msg' => __( 'backup_waiting' )
		));
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function reload()
	{
		echo ips_redirect_js( admin_url( '/', 'backup=files&start=true&dir' ), 2 );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function backupFiles()
	{
		$time_start = time();
		
		$paths = Config::noCache( 'cache_data_backup' );

		
		if( empty( $paths['paths'] ) )
		{
			Config::remove( 'cache_data_backup' );
			
			return array(array(
				'status' => true,
				'msg' => __s( 'backup_was_made', $paths['copied'] )
			));
		}
		
		foreach ( $paths['paths'] as $key => $path )
		{
			if( !empty( $path ) && is_dir( ABS_PATH  . $path ) )
			{
				$folder = $paths['destination'] .  $path;
				
				if( !file_exists( $folder ) )
				{
					$paths['copied']++;
					@mkdir( $folder, 0755, true );
				}
				
				$files = array_filter( glob( rtrim(ABS_PATH . $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . '*', GLOB_NOSORT ), 'is_file' );
				
				foreach ( $files as $file )
				{
					if ( is_file( $file ) )
					{
						$file_path = $paths['destination'] . str_replace( ABS_PATH, '', $file );
						
						if( !file_exists( $file_path ) || !files_are_equal( $file , $file_path ) )
						{
							if( !copy( $file, $file_path ) )
							{
								var_dump( $file_path );
							}
						}
						
						$paths['copied']++;
					}
				}
			}
			
			unset( $paths['paths'][$key] );
			
			Config::update( 'cache_data_backup', array( 
				'paths' => array_values( $paths['paths'] ),
				'copied' => $paths['copied']
			), false );
			
			if( time() - $time_start > 10 || !isset( $paths['paths'][$key+1] ) )
			{
				$this->reload();
				
				return array(array(
					'status' => true,
					'msg' => __s( 'backup_partial', $paths['copied'] )
				),array(
					'status' => 'loader',
					'msg' => __( 'backup_waiting' )
				));
				
			}
		}
	}
	
	
	
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function backupZip( $path )
	{
		$file = OUTPUT_SQL . '/' . 'backup_' . date("Y-m-d_H");
		try
		{
			$a = new PharData( $file. '.tar' );
			if( is_file( $path ) )
			{
				$a->addFile( $path );
			}
			else
			{
				$a->buildFromDirectory( $path );
			}
			$a->compress(Phar::GZ);

			@chmod($file. '.tar', 0777 );
			@unlink( $file. '.tar' );
		} 
		catch (Exception $e) 
		{
			return array(array(
				'status' => false,
				'msg' => __s( 'backup_zipped_error')
			));
		}
		
		return array(array(
			'status' => true,
			'msg' => __s( 'backup_zipped', IPS_ADMIN_URL . '/backup/' . basename( $file. '.tar.gz'  ) )
		));
	}
}
?>