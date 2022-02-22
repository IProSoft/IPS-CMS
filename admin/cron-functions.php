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

/**
 * Walidacja zapisywanych danych zaplanowanego zadania cron.      
 *
 * @param array $args Parametry zapisywanego zadania.         
 
 * @return string|bool Zwraca wiadomość w wypadku niepowodzenia    
 */
function ips_save_options( $args, $edit = false ){
	
	$func_args = $args;
	if( empty( $args['timestamp'] ) )
	{
		return 	admin_msg( array(
			'alert' => __('enter_correct_date' )
		) );
	}
	
	if(  !in_array( $args['cron'], array( 'clear-cache', 'backup', 'sitemap' ) ) && ( empty($args['count']) || !is_numeric($args['count']) ) )
	{
		return admin_msg( array(
			'alert' => __('enter_correct_count')
		) );
	}
	
	if( $args['cron'] == 'move-to-main' )
	{
		/* if( (empty($args['votes_count']) || !is_numeric($args['votes_count'])) && (empty($args['share']) || !is_numeric($args['share'])) )
		{
			return admin_msg( array(
				'alert' => __('enter_correct_rate_number' )
            ) );
		} */
	}
	elseif( $args['cron'] == 'import' )
	{
		/* if( empty($args['site']) || !filter_var($args['site'], FILTER_VALIDATE_URL) )
		{
			return admin_msg( array(
				'alert' =>  __('enter_valid_url' )
			) );
		} */
			
		if( empty($args['import_directory']) || !file_exists(ABS_PATH . '/' . trim($args['import_directory'], '/')) )
		{
			return admin_msg( array(
				'alert' => __('cron_import_server_catalog_error' )
			) );
		}
		
		if( empty($args['users']) )
		{
			return admin_msg( array(
				'alert' => __('enter_users_logins' ) 
            ) );
		}
	}
	
	elseif( $args['cron'] == 'archive' ){}
	elseif( $args['cron'] == 'fanpage' ){}	
	
	if( $edit ) 
		return true;
		
	unset($func_args['timestamp'], $func_args['cron'], $func_args['schedule']);
	
	if( $args['cron'] == 'fanpage' && $args['type'] != 'post' )
	{
		if( !isset( $args['album_id'] ) || empty( $args['album_id'] ) )
		{
			return admin_msg( array(
				'alert' =>  __('fanpage_album_id_error' )
			) );
		}
	}
	
	if ( ! ips_add_cron( strtotime( $args['timestamp'] . ':00' ), $args['schedule'], $args['cron'], $func_args ) )
	{
		return admin_msg( array(
	      'alert' =>  __('cron_create_error' ) 
        ) );	
	}

	return true;
}

function ips_cron_change( $args, $timestamp, $key ){

	if ( ips_save_options( $args, true ) === true )
	{
		ips_cron_delete( $timestamp, $key );
		return ips_save_options( $args );
	}
}


function ips_unique_key(){
	return md5( serialize( func_get_args() ) );
}
/**
 * Dodawanie zaplanowanego zadania cron.
 *
 * @param int $timestamp uniksowy znacznik rozpoczęcia zadania.
 * @param string $schedule Częstość wykonywanego zadania.
 * @param strin $func_name Nazwa wywoływanej funkcji.
 * @param array $args Parametry wywoływanej funkcji.
 */
function ips_add_cron( $timestamp, $schedule, $func_name, $args = array()) {
	$crons = ips_cron_array();
	$schedules = ips_return_schedules();
	
	if ( !isset( $schedules[$schedule] ) )
		return false;
		
	$key = ips_unique_key( $timestamp, $schedule, $func_name );

	$crons[$timestamp][$key] = array( 
		'id' => $key, 
		'timestamp' => $timestamp,  
		'function-name' => $func_name, 
		'schedule' => $schedule, 
		'args' => $args, 
		'interval' => $schedules[$schedule]['interval']
	);
	if( isset( $args['last-activity'] ) )
	{
		$crons[$timestamp][$key]['last-activity'] = $args['last-activity'];
		unset($args['last-activity']);
	}
	uksort( $crons, "strnatcasecmp" );
	ips_save_crontabs( $crons );
	
	return $key;
}

/**
 * Usunięcie zaplanowanego zadania cron.
 *
 * @param int $timestamp uniksowy znacznik rozpoczęcia zadania.
 * @param string $schedule Częstość wykonywanego zadania.
 * @param strin $func_name Nazwa wywoływanej funkcji.
 * @param array $args Parametry wywoływanej funkcji.
 */
 
function ips_cron_delete( $timestamp, $key ){
	$crons = ips_cron_array();
	
	unset($crons[$timestamp][$key]);
	if ( empty($crons[$timestamp]) )
		unset( $crons[$timestamp] );
	uksort( $crons, "strnatcasecmp" );
	ips_save_crontabs( $crons );
}


function ips_cron_delete_by_func( $function )
{
	$crons = ips_cron_array();
	foreach ( $crons as $timestamp => $functions )
	{
		foreach ( $functions as $key => $func )
		{
			if( $func['function-name'] == $function )
			{
				unset( $crons[$timestamp][$key] );
				if( empty( $crons[$timestamp] ) )
				{
					unset( $crons[$timestamp] );
				}
				uksort( $crons, "strnatcasecmp" );
				ips_save_crontabs( $crons );
			}
		}
	}
}

/**
 * Zwracanie danych pojedyńczego zadania.
 *
 * @return array
 */
function ips_cron_get( $key, $timestamp )
{
	$crons = ips_cron_array();
	if( isset($crons[$timestamp][$key]) )
	{
		if( !empty($crons[$timestamp][$key]['args']) )
		{
			$crons[$timestamp][$key] = array_merge( $crons[$timestamp][$key], $crons[$timestamp][$key]['args'] );
		}
		return $crons[$timestamp][$key];
	}
}

/**
 * Zmiana daty wywołania zadania.
 *
 * @param int $timestamp uniksowy znacznik rozpoczęcia zadania.
 * @param string $schedule Częstość wykonywanego zadania.
 * @param strin $func_name Nazwa wywoływanej funkcji.
 * @param array $args Parametry wywoływanej funkcji.
 */
 
function ips_cron_change_timestamp( $timestamp, $schedule, $func_name, $args = array())
{
	$crons = ips_cron_array();
	$schedules = ips_return_schedules();
	$key = ips_unique_key( $timestamp, $schedule, $func_name );

	$interval = $schedules[$schedule]['interval'];
	
	$now = time();

	if ( $timestamp >= $now )
		$timestamp = $now + $interval;
	else
		$timestamp = $now + ( $interval - ( ($now - $timestamp) % $interval ) );

	return ips_add_cron( $timestamp, $schedule, $func_name, $args );
}

/**
 * Zwracamy listę dostępnych pozycji harmonogramu
 *
 * @param null
 * 
 * @return array $schedules Lista dostępnych pozycji.
 */
 
function ips_return_schedules() {
	$schedules = array(
		'quarter' => array( 'interval' => 900, 'text' => __('cron_every_quarter')),
		'hourly' => array( 'interval' => 3600, 'text' => __('cron_hourly')),
		'daily' => array( 'interval' => 86400, 'text' => __('cron_daily')),
		'weekly' => array( 'interval' => 604800, 'text' => __('cron_weekly')),
	);
	return $schedules;
}

/**
 * Zwracamy listę dostępnych pozycji funkcji wywoływanych
 * przez CRON
 *
 * @param null
 * 
 * @return array $func Lista dostępnych funkcji.
 */
function ips_cron_available_func() {

	return array(
		'move-to-main' => array( 
			'text' => __('files_moved_to_main_text'), 
			'call-function' => 'cron_MoveToMain', 
			'history' => __('files_moved_to_main')
		),
		'backup' => array( 
			'text' => __('backup_mysql_text'), 
			'call-function' => 'cron_Backup', 
			'history' => __('backup_done')
		),
		'import' => array( 
			'text' => __( 'import_folder_text' ), 
			'call-function' => 'cron_ImportImages' 
		),
		'import_drafts' => array( 
			'text' => __( 'import_from_templates'), 
			'call-function' => 'cron_ImportDrafts' 
		),
		'archive' => array( 
			'text' => __( 'files_moved_to_archive_text' ), 
			'call-function' => 'cron_ArchiveImages', 
			'history' => __('files_moved_to_archive')),
		'archive_wait' => array( 
			'text' => __( 'files_moved_to_wait_text' ), 
			'call-function' => 'cron_ArchiveWaitImages', 
			'history' => __('files_moved_to_wait')),
		'fanpage' => array( 
			'text' => __( 'files_added_to_fanpage_text' ), 
			'call-function' => 'cron_PostToFanpage', 
			'history' => __('files_added_to_fanpage')
		),
		'clear-cache' => array( 
			'text' => __( 'system_cache_cleared_cache_text' ), 
			'call-function' => 'cron_ClearCache', 
			'history' => __('system_cache_cleared_cache')
		),
		'sitemap' => array( 
			'text' => __( 'cron_sitemap' ), 
			'call-function' => 'cron_CreateSitemap'
		),
	);

}


function ips_cron_func_text( $func ) {

	$functions = ips_cron_available_func();
	
	if( isset( $functions[$func] ) )
	{
		return $functions[$func]['text'];
	}
	
	return false;
}
/**
 * Pobieramy listę zadań z bazy.
 * Pobieranie prosto z bazy aby uniknąć cachowania opcji
 *
 * @return array $cron lista zadań CRON.
 */
function ips_cron_array()  {
	/**
	* Zapytanie przez PD aby uzyskać aktualną wartość
	*/ 
	$cron = Config::noCache( 'cron_tasks' );
	
	if ( !$cron )
		return array();
		
	if( is_serialized( $cron ) )
	{
		$cron = unserialize( $cron );
	}
	
	if ( ! is_array( $cron ) || empty( $cron ) )
		return array();

	return $cron;
}
/**
 * Aktualizacja listy zadań CRON.
 *
 * @param array $cron tablica zadań.
 */
function ips_save_crontabs( $cron ) {
	$crons = empty($cron) ? '' : serialize($cron);
	PD::getInstance()->update('`system_settings`', array( 
		"settings_value" => $crons
	), "`settings_name` = 'cron_tasks'");
}









function cron_MoveToMain( ) {
	
	$args = func_get_arg(0);
	
	$sort_by = isset( $args['import_sorting'] ) ? $args['import_sorting'] : 'rand';
	switch( $sort_by )
	{
		case 'asc':
			$sort_by = array( 'up.date_add' => 'ASC' );
		break;
		case 'desc':
			$sort_by = array( 'up.date_add' => 'DESC' );
		break;
		default:
			$sort_by = 'RAND()';
		break;
	}
	
	$conditions = array(
		'upload_activ' => 0,
		'upload_status' => 'public'
	);
	
	if( (int)$args['share'] > 0 )
	{
		$conditions['s.share'] = array( $args['share'] , '>=' );
	}
	
	if( (int)$args['votes_count'] > 0 )
	{
		$conditions['up.votes_opinion'] = array( $args['votes_count'] , '>=' );
	}	
	
	$conditions['up.id'] = 's.upload_id';
	
	$to_move = PD::getInstance()->from( array( 
		IPS__FILES => 'up', 
		'shares' => 's'
	) )->setWhere( $conditions )->orderBy( $sort_by )->fields( 'up.*, s.share' )->get( $args['count'] );
			
			
	if( !empty( $to_move ) )
	{
		$ids = array();
		if( $args['count'] > 1 )
		{
			foreach( $to_move as $key => $file )
			{
				$ids[] = $file['id'];
			}
		}
		else
		{
			$ids[] = $to_move['id'];
		}
		$moved = 0;
		foreach( $ids as $key => $id )
		{
			
			$operations = new Operations;
			if( $operations->move( $id, 'main' ) )
			{
				$moved++;
			}
			
		}

		ips_save_crontabs_history( func_get_arg(1),  count( $moved ) );
	}
}
function cron_ArchiveWaitImages(  ) {
	$args = func_get_arg(0);


	$to_move = PD::getInstance()->select( IPS__FILES, array(
		'upload_status' => 'archive'
	), $args['count'] );

	if( !empty( $to_move ) )
	{
		$ids = 0;
		if( isset( $to_move['id'] ) )
		{
			$to_move = array( 
				0 => $to_move
			);
		}
		foreach( $to_move as $file )
		{
			$operations = new Operations;
			if( $operations->move( $file['id'], 'archive-wait' ) )
			{
				$ids++;
			}
		}

		waitCounterUpdate();
		
		ips_save_crontabs_history( func_get_arg(1),  $ids );
	}
}


function cron_ArchiveImages(  ) {
	$args = func_get_arg(0);

	$where = array(
		'upload_status' => 'public',
		'date_add' => array( date("Y-m-d H:i:s", strtotime( "now - " . $args['count'] . " days" ) ), '<=')
	);
	if( isset( $args['onlywait'] ) && $args['onlywait'] == 1 )
	{
		$where['upload_activ'] = 0;
	}
	
	$to_move = PD::getInstance()->select( IPS__FILES, $where );
	
	if( !empty( $to_move ) )
	{
		$ids = 0;
		
		foreach( $to_move as $file )
		{
			$operations = new Operations;
			if( $operations->move( $file['id'], 'archive' ) )
			{
				$ids++;
			}
		}
		waitCounterUpdate();
		ips_save_crontabs_history( func_get_arg(1),  $ids );
	}
}
function cron_CreateSitemap(  ) {
	$args = func_get_arg(0);
	
	$sitemap = new Sitemap;
	
	ips_save_crontabs_history( func_get_arg(1),  $sitemap->createSitemap() );                    
}

function cron_PostToFanpageRand( $conditions, $args )
{
	$rand = PD::getInstance()->optRand( db_prefix( IPS__FILES ), $conditions, $args['count'] );
	
	if( PD::getInstance()->cnt( 'fanpage_posts', "upload_id = '" . $rand['id'] . "'" ) > 0 )
	{
		return cron_PostToFanpageRand( $conditions, $args );
	}
	
	return $rand;
}

function cron_PostToFanpage()
{
	$args = func_get_arg(0);

	if( Facebook_UI::isAppValid() )
	{
		$conditions = array(
			'upload_status' => 'public',
			'id' => array( 'SELECT upload_id FROM fanpage_posts', 'NOT IN' )
		);
		
		if( $args['pick_from'] != 'all' )
		{
			$conditions['upload_activ'] = $args['pick_from'] == 'main' ? 1 : 0;
		}
		
		if( Config::get('apps_facebook_app', 'exclude_adult') )
		{
			$conditions['category_id'] = array(  'SELECT id_category FROM ' . db_prefix( 'upload_categories' ) . ' WHERE `only_adult` = 1', 'NOT IN' );
			$conditions['upload_adult'] = 0;
		}
		
		$to_move = cron_PostToFanpageRand( $conditions, $args );
	
		if( !empty( $to_move ) )
		{
			if( isset( $to_move['id'] ) )
			{
				$to_move = array( 
					0 => $to_move
				);
			}
			
			foreach( $to_move as $file )
			{
				Facebook_Fanpage::postFromId( $file['id'], ( $args['type'] != 'upload' ? 'post' : 'upload' ), $args );
			}
			
			ips_save_crontabs_history( func_get_arg(1),  count( $to_move ) );
		}
	}
	else
	{
		ips_save_crontabs_history( func_get_arg(1),  __('fanpage_error_api_facebook') );                    
	}
}
function cron_ClearCache(  ) {
	
	$args = func_get_arg(0);
	$info = '';
	if( isset( $args['tpl'] ) && $args['tpl'] )
	{
		$info .= clearCache( 'tpl' ) . '<br />';
	}
	if( isset( $args['js'] ) && $args['js'] )
	{
		$info .= clearCache( 'jscss' ) . '<br />';
	}
	if( isset( $args['file'] ) && $args['file'] )
	{
		$info .= clearCache( 'ipscache' ) . '<br />';
	}
	if( isset( $args['tmp'] ) && $args['tmp'] )
	{
		$info .= clearCache( 'img' ) . '<br />';
	}
	
	if( isset( $args['mysql'] ) && $args['mysql'] )
	{
		$info .= clearCache( 'mysql' ) . '<br />';
	}
	
	if( !empty($info) )
	{
		ips_save_crontabs_history( func_get_arg(1),  '<br />' . $info );
	}
}
function getDrafts ( $count = false )
{

	$files = glob( ABS_PATH . '/upload/import/drafts/import_draft_*', GLOB_NOSORT );
	if( $count )
	{
		return count( $files );
	}
}
function cron_ImportDrafts() {
	
	$args = func_get_arg(0);

	require_once( ABS_PATH . '/functions-upload.php' );
	require_once( IPS_ADMIN_PATH .'/import-functions.php' );
	
	$drafts = array();
	$dir = ABS_PATH . '/upload/import/drafts/';
	
	if ( is_dir( $dir ) )
	{
		if ( $dh = opendir( $dir ))
		{
			while (( $file = readdir( $dh ) ) !== false && $args['count'] > count( $drafts ) ) {
				if( strpos( $file, 'import_draft_' ) !== false )
				{
					$drafts[] = unserialize( file_get_contents( $dir . $file ) );
					unlink( $dir . $file );
				}
			}
			closedir( $dh );
		}
	}
	$success = 0;
	
	shuffle( $drafts );
	
	foreach( $drafts as $key => $import_urls )
	{
		try{		
			
			$_POST['title'] = $import_urls['title'];
			
			if( $import_urls['upload_type'] == 'image' )
			{
				$_POST['upload_url'] = $import_urls['image-link'];
			}
			
			addImportedFile( array_merge( $args, $import_urls, array( 'import_add_to' => $args['import_add_to'] ) )  );
			
			$success++;
			
		} catch (Exception $e) {
			cron_ImportDrafts( $args );
		}
		unset( $_POST['upload_url'] );
	}

	Session::clear( 'inc' );
	
	ips_save_crontabs_history( $args['count'],  __s( 'added_count_files', $success ));
}

function cron_ImportImages(  ) {
	
	$args = func_get_arg(0);

	require_once( ABS_PATH . '/functions-upload.php' );
	require_once( IPS_ADMIN_PATH .'/import-functions.php' );
	
	Session::setChild( 'inc', 'import_add_to', $args['import_add_to'] );
	
	if( strpos( $args['import_directory'], "," ) !== false)
	{
		$args['import_directory']	= explode( ',', $args['import_directory'] );
	}
	
	$msg = importFolderImages( $args['import_directory'], $args['import_default_name'], $args['upload_tags'], $args['count'], ( isset( $args['import_category'] ) ? $args['import_category'] : 0 ), explode(',', $args['users'] ) );
	
	Session::setChild( 'inc', 'import_add_to', false );

	if( isset( $msg['error'] ) )
	{
		return ips_save_crontabs_history( func_get_arg(1),  '<br />' . str_replace( '-', '<br />-', strip_tags( $msg['error'] ) ) );;
	}
	
	if( !empty( $msg['files'] ) )
	{
		ips_save_crontabs_history( func_get_arg(1), __s( 'added_count_files', count( $msg['files'] ) ) );
	}
}
function cron_Mailing_Admin()
{
	if( Config::getArray( 'mailing_options', 'send_type' ) == 'cron' )
	{
		Ips_Registry::get( 'Mailing_Admin' )->send( false, true );
	}
}
function cron_Backup(  ) {
	
	$backupDatabase = new Database_Backup();
	$backupDatabase->backupTables( 'cron' );

	if( !empty($backupDatabase->status) )
	{
		ips_save_crontabs_history( func_get_arg(1),  '<br />' . __('statistic_mysql_backup') . $backupDatabase->status );
	}
}

function ips_save_crontabs_history( $func_name, $info ){
	$functions = ips_cron_available_func();
	$cron = ips_cron_history();
	if( is_numeric($info) )
	{
		if( isset($functions[$func_name]['history']) )
		{
			$info = sprintf($functions[$func_name]['history'], $info);
		}
	}
	if( !empty( $info ) )
	{
		if( count( $cron ) > 20 )
		{
			array_shift( $cron );
		}
		array_push( $cron, array( 'time' => time(), 'info' => $info ) );
		
		$crons = empty($cron) ? '' : serialize($cron);
		Config::update( 'cron_tasks_history', $crons  );
	}
}

function ips_cron_history_clear(){
	Config::update( 'cron_tasks_history', '' );
}

function ips_cron_history(){
	
	$cron = Config::noCache( 'cron_tasks_history' );
	
	if( is_serialized( $cron ) )
	{
		$cron = unserialize( $cron );
	}
	
	if ( ! is_array($cron) || empty($cron) )
		return array();

	return $cron;
}

function ips_cron_system()
{
	$system_cache = Config::getArray( 'system_cache' );

	/**
	 * Caching enabled, delete files from the cache
	 */
	if ( $system_cache['css_js'] != 0 )
	{
		$files = File::search( ABS_CSS_JS_CACHE_PATH );
		foreach( $files as $file )
		{
			if ( time() - filemtime( $file ) > $system_cache['css_js_expiry'] )
			{
				File::deleteFile( $file );
			}
		}
	}
}
?>