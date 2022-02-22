 <?php

define( "OUTPUT_SQL", IPS_ADMIN_PATH .'/backup' );

class Database_Backup
{
    
    /**
     * Database to backup
     */
    public $dbName = DB_NAME;
    
    public $status = '';
    
    public function __construct()
	{
		$file_name = OUTPUT_SQL . '/' . 'db-backup-' . $this->dbName . '-' . date( "Y-m-d_H", time() );
		
		$this->db_file = $file_name . '.sql';
		
		if( file_exists( $this->db_file ) && filesize( $this->db_file ) > 5242880 )
		{
			$files = glob( $file_name . '*', GLOB_NOSORT );
			
			$this->db_file = $file_name . '_part_' . count( $files ) . '.sql';
		}
	}
    /**
     * Pobieranie listy tabel i danych z bazy
     * Retrieve a list of tables and data from the database
     *
     * @param null
     * 
     * @return void
     */
    public function backupTables( $cron = false )
    {
        $this->status = 0;
        
		try
        {
            $this->tables = PD::getInstance()->query( 'SHOW TABLES' );
            ksort( $this->tables );
			
			try
			{
				$sql = File::read( ABS_PATH . '/templates/__admin/db-template.sql' );
            }
			catch ( Exception $e )
			{
				$sql = '';
			}
			
            /**
             * Iterate tables
             */
            foreach ( $this->tables as $id => $table )
            {
                if ( is_array( $table ) )
                {
                    $table = reset( $table );
                }
                
				$this->tables[$id] = str_replace( '`', '', $table );
			}
			
			$action = get_input('dump');
			
			if( $action == 'data' )
			{
				$current_table = get_input('current_table');
				
				$this->saved = array();
				
				foreach ( $this->tables as $key => $table )
				{
					if( $table == $current_table )
					{
						break;
					}
					$this->saved[] = $table;
				}
				
				if( $current_table === false )
				{
					die( 'Empoty table name' );
				}

				return $this->backupData( $current_table );
			}
			else
			{
				/**
				 * Iterate tables - create table
				 */
				foreach ( $this->tables as $id => $table )
				{
					$create = PD::getInstance()->query( 'SHOW CREATE TABLE `' . $table . '`' );
					
					$sql .= "--\n-- Table structure `" . $table . "`\n--\n\n";
					$sql .= 'DROP TABLE IF EXISTS `' . $table . '`;';
					$sql .= "\n\n" . $create[0]['Create Table'] . ";\n\n";
					$this->status++;
				}	
				
				$this->saveFile( $sql );
				
				if( $cron )
				{
					/**
					 * Iterate tables - export data
					 */
					foreach ( $this->tables as $id => $table )
					{
						$this->backupData( $table, 'cron' );
					}	
					
					return true;
				}
				
				return $this->reload( $this->tables[0] );
			}

        }
        catch ( Exception $e )
        {
            var_dump( $e->getMessage() );
            return false;
        }
    }
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function backupData( $table, $cron = false )
    {
		$this->status = get_input('tables');
		//LOCK_EX FILE_APPEND
		
		$limit = ( !$cron ? xy( get_input('page'), 5000 ) : false );

		$result = PD::getInstance()->from( $table )->limit( $limit )->get();
		$count = count( $result );
		
		$sql = "--\n-- Table data `" . $table . "`\n--\n\n";
		
		$fields = array();
		if ( !empty( $result ) )
		{
			$fields = '(' . implode( ",", array_map( function( $field_name ){
				return "`" . $field_name . "`";
			}, array_keys(current( $result ) )) ) . ') VALUES';
			
			
			
			$fields_data = array();
			foreach ( $result as $i => $row )
			{
				
				$row = array_map( function( $data ){
					return "'" . addslashes( $data ) . "'";
				}, $row );
				
				$result[$i] = '';
				
				if( $i%100 == 0 )
				{
					if( $i > 0 )
					{
						$result[$i-1] = substr( $result[$i-1], 0, -1 ) . ";\n";
					}
					$result[$i] = 'INSERT INTO ' . $table . ' ' . $fields . ' ';
				}
				
				$result[$i] .= '(' . implode( ",", $row ) . '),';
			}
			
			$result = substr( implode( '', $result ), 0, -1 ) . ";";
			
			
			$sql .= $result. "\n\n\n";
			
			
		}
		if ( empty( $result ) || $count < 5000 )
		{
			foreach ( $this->tables as $key => $t )
			{
				if( $table == $t )
				{
					$table = isset( $this->tables[$key+1] ) ? $this->tables[$key+1] : null;
					break;
				}
			}
			return $this->reload( $table, 0 );
		}
		
		$this->saveFile( $sql, null, LOCK_EX | FILE_APPEND );
		
		
		
		if( !$cron )
		{
			return $this->reload( $table, get_input('page') );
		}
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function reload( $current_table, $page = 0 )
    {
		if( $current_table === null )
		{
			return true;
		}

		echo ips_redirect_js( admin_url( '/', 'backup=mysql&dump=data&tables=' . $this->status . '&current_table=' . $current_table . '&page=' . ( $page + 1 ) ), 0.5 );
	}
    
    /**
     * Storing SQL database dump file
     *
     * @param string $sql - database dump file
     * 
     * @return bool
     */
    protected function saveFile( $sql, $db_file = null, $flag = LOCK_EX )
    {
        if ( !$sql )
        {
            return false;
        }
		
        if ( empty( $db_file ) )
        {
            $db_file = $this->db_file;
        }
		
        try
        {
			file_put_contents( $db_file, $sql, $flag );
        }
        catch ( Exception $e )
        {
            //var_dump( $e->getMessage() );
            return false;
        }
        
        return true;
    }
	
	
	/****SELF FUNCTIONS****/
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function backupInstall()
	{
		$this->dumpStructure();
		$this->updateSettings();
		$this->updateTranslations();
		$this->updateTranslations( true );
		$this->sortLang( 'pl' );
		$this->sortLang( 'en' );
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
    public function dumpStructure()
    {
        try
        {
            
            $tables = array();
            $tables = PD::getInstance()->query( 'SHOW TABLES' );
            
            $sql = "<?php\n\nreturn array(\n\t";
            
            /**
             * Iterate tables
             */
            foreach ( $tables as $id => $table )
            {
                if ( is_array( $table ) )
                {
                    $table = reset( $table );
                }
                
                $table = str_replace( DB_PREFIX, '', $table );
                
                $row2 = PD::getInstance()->query( 'SHOW CREATE TABLE ' . $table );
                if ( strpos( $table, 'plugin' ) === false && strpos( $table, 'autopost' ) === false )
                {
                    $sql .= "'" . $table . "' => \"\n\t\t" 
					. implode( "\n\t\t", explode( "\n", str_replace( 'CREATE TABLE `', 'CREATE TABLE `" . DB_PREFIX . "', str_replace( 'CREATE TABLE IF NOT EXISTS', 'CREATE TABLE', preg_replace( '/ AUTO_INCREMENT=([0-9]*)/', '', $row2[0]['Create Table'] ) ) ) ) ) 
					. ";";
					
                    $sql .= "\n\t\",\n\t";
                }
                
            }
            $sql .= "\n);";
        }
        catch ( Exception $e )
        {
            return false;
        }
		
		$this->saveFile( $sql, ABS_PATH . '/install/import-tables.php' );
    }
    
    
	
	public function updateSettings()
    {
		if( file_exists( ABS_PATH . '/install/import-options.php' ) )
		{
			$options = include( ABS_PATH . '/install/import-options.php');
			$options_diff = $this->settingsDiff( $options['system_settings'] );
			
			$count_options = call_user_func_array('array_merge', require_once( IPS_ADMIN_PATH .'/admin-options.php' ) );
	
			foreach( $options_diff as $k => $option )
			{
				if( isset( $count_options[ $option['settings_name'] ] ) && isset( $count_options[ $option['settings_name'] ]['option_is_array'] ) && is_array( $option['settings_value'] ) )
				{
					if( count( $count_options[ $option['settings_name'] ]['option_is_array'] ) != count( $option['settings_value'] )  )
					{
						echo "Settings values count problem ";
						var_dump($option['settings_name']);
						var_dump($count_options[ $option['settings_name'] ]['option_is_array']);
						var_dump($option['settings_value']);
						die();
					}
				}
				if( is_serialized( $option['settings_value'] ) )
				{
					$options_diff[$k]['settings_value'] = unserialize( $option['settings_value'] );
				}
			}
			
			$system_settings = $this->prityExport( $options_diff );
			
			file_put_contents( ABS_PATH . '/install/import-options.php', '<?php' . "\nreturn array(" . $system_settings . "\n);" );
			
		}
		
		
		
	}
	public function prityExport( $settings )
	{
		$string = "\n\t'system_settings' => array(\n\t\t";

		foreach( $settings as $setting )
		{
			$string .= str_replace( array( 
				"\t\t)\n",
				'  )',
				'  	\'',
				"=> \n\t\t  "
			), array( 
				"\t\t),\n", 
				"\t)", 
				"\t\t'", 
				"=> "
			), str_replace( array( 
				'  \'', 
				"> \n    ", 
				"\n     ", 
				"\n    ", 
				"\n"
			), array(
				"\t'", 
				"> ", 
				"\n\t\t\t", 
				"\n\t\t", 
				"\n\t\t"
			), var_export( $setting, true ) . "\n") );
		}
		
		return $string . "\n\t),";
	}
	public function settingsDiff( $install_settings )
	{
			$db_settings = PD::getInstance()->select( 'system_settings' );
			
			$install_settings_column = array_column( $db_settings, 'settings_name' );
			$db_settings_column = array_column( $install_settings, 'settings_name' );
			
			$diff = array_diff( $db_settings_column, $install_settings_column );
			
			if( !empty( $diff ) )
			{
				var_dump( $diff );
				die('Diff database - install problem');
			}

			foreach( $db_settings as $key => $v )
			{
				if( isset( $v['settings_id'] ) )
				{
					unset( $v['settings_id'] );
				}
				
				$db_settings[ $v['settings_name'] ] = $v;
				
				unset( $db_settings[$key] );
			}

			$diff = array();
			
			$set_value = array(
				'apps_fanpage_array' => array(),
				'apps_fanpage_default_token' => '',
				'apps_fanpage_default_id' => '',
				'apps_fanpage_default' => '',

				'apps_facebook_app' => array(
					'app_id' => '',
					'app_secret' => '',
					'app_version' => 'v2.4',
					'admin_id' => '',
					'previliges' => array( 'email', 'user_birthday', 'publish_actions' ),
					'require_previliges' => 0,
					'save_token' => 1,
					'auto_user_name' => 1,
					'exclude_adult' => 1
				),
				'apps_nk_app' => array(
					'app_key' => '',
					'app_secret' => ''
				),
				'apps_twitter_app' => array(
					'consumer_key' => '',
					'consumer_secret' => '',
					'username' => '' 
				),
				'apps_fanpage_posting' =>  array (
					'on_upload' => 0,
					'on_upload_count' => 2,
					'on_upload_fanpages' => array (),
					'move_main' => 0,
					'move_main_count' => 2,
					'move_main_fanpages' => array ()
				),
				

				'apps_login_enabled' => array(
					'facebook' => 0, 
					'twitter' => 0,
					'nk' => 0
				),
				'system_cache' => array(
					'config' => false,
					'config_expiry' => 3600,
					'comments' => false,
					'comments_expiry' => 3600,
					'css_js' => false,
					'css_js_expiry' => 3600,
					'files' => false,
					'files_expiry' => 3600,
					'templates' => false,
					'templates_expiry' => 3600,
				),
				'analytics_enabled' => 0,
				'widget_user_idle_options' => array(
					'ad_content' => ''
				),
				'admin_alerts' => array(),
				'web_fonts_config' => array(),
				'upload_tags' => 1, 
				'upload_tags_options' => array(
					'extract' => 0,
					'length' => 1,
					'blocked' => array(
						'lub',
						'tak',
						'nie',
						'nad',
						'pod',
						'www',
						'był',
						'się'
					)
				),
				'footer_code' => '',
				'head_code' => '',
				'email_admin_user' => '',
				'site_in_maintenance_text' => 'Maintence',
				'language_settings' => array (
					'default_language' => 'PL',
					'ips_multilanguage' => 1,
					'allow_change_languages' => 1,
					'language_locales' => array (
						'PL' => 'pl_PL',
					),
					'languages' => array (
						0 => 'PL'
					),
				),
				
				'email_smtp' => 0,
				'email_smtp_options' => array(
					'login' =>  '',
					'password' =>  '',
					'host' =>  '',
					'port' =>  '',
					'auth_on' => 1,
					'ssl' => 1
				)
			);
		

			foreach( $install_settings as $key => $value )
			{
				$settings_name = $value['settings_name'];
				
				if( strpos( $settings_name, 'cache_data_' ) === false && !in_array( $settings_name, array(
					'admin_last_visit_stats',
					'license_number',
					'license_email',
					'update_alert_last_time',
					'app_css_files',
					'app_javascript_files',
					'jquery_array',
					'hooks_actions_registry',
					'ips_randomity',
					'install_date',
					'ips_system_version',
					'post_facebook_data',
					'web_fonts_list'
				) ) )
				{
					if( !isset( $db_settings[$settings_name] ) )
					{
						if( isset( $value['settings_id'] ) )
						{
							unset( $value['settings_id'] );
						}
						
						if( is_serialized( $value['settings_value'] ) )
						{
							$value['settings_value'] = unserialize( $value['settings_value'] );
						}
						
						$diff[ $settings_name ] = $value;
					}
				}
				else
				{
					unset( $db_settings[$settings_name] );
				}
				
				/** Set default values */
				if( isset( $set_value[ $settings_name ] ) )
				{
					$db_settings[ $settings_name ]['settings_value'] = $set_value[ $settings_name ];
				}
			}

			$install_settings = null;

			if( !empty( $diff ) )
			{
				ips_log( array( 
					$diff
				), 'logs/config-diff.log', true );
				
				$db_settings = array_merge_recursive( $db_settings, $diff );
			}
			
			usort( $db_settings, function($x, $y) {
				return strcasecmp( $x['settings_name'], $y['settings_name'] );
			});
			
		return $db_settings;
	}
	
    public function updateTranslations( $pinit = false )
    {
        
        $ready = array(
            'other' => array ()
        );
        
        
        $install_language = array();
        
        $db_language = PD::getInstance()->select( 'translations', array(
			'translation_name' => array( '^pinit', ( $pinit ? "REGEXP" : "NOT REGEXP" ) )
		));
       
		if( count( $db_language ) < 10 )
		{
			return false;
		}
		
        foreach ( $db_language as $key => $phrase )
        {
            $install_language[$phrase['translation_name']] = $phrase['translation_value'];
        }
        
        
        
        foreach ( $install_language as $phrase_name => $content )
        {
            if ( strpos( $phrase_name, '_' ) !== false )
            {
                $first = substr( $phrase_name, 0, strpos( $phrase_name, '_' ) );
                if ( !isset( $ready[$first] ) )
                {
                    $ready[$first] = array();
                }
                $ready[$first][] = $phrase_name;
            }
            else
            {
                $ready['other'][] = $phrase_name;
            }
            
        }
        
        asort( $ready );
        ksort( $ready );
        
        
        foreach ( $ready as $name => $tranlations )
        {
            if ( count( $tranlations ) == 1 )
            {
                
                $ready['other'] = array_merge( $ready['other'], $ready[$name] );
                unset( $ready[$name] );
            }
            elseif ( $name == 'other' )
            {
                $tmp = $ready['other'];
                unset( $ready['other'] );
                $ready['other'] = $tmp;
            }
        }
        

        
        $final_file = '<?php' . "\n" . 'return array(' . "\n";
        foreach ( $ready as $key => $trans_keys )
        {
            asort( $trans_keys );
			foreach ( $trans_keys as $final_key )
            {
                if( strpos( $final_key, 'category_text_' ) === false )
				{
					$final_file .= "\t" . "'" . $final_key . "' => '" . $install_language[$final_key] . "'," . "\n";
				}
            }
            $final_file .= "\t" . "\n";
        }
        
        
        $final_file .= "\n);\n?>";
        
        file_put_contents( ABS_PATH . '/install/import-language-' . ( $pinit ? 'pin-' : '' ) . 'pl.php', $final_file, LOCK_EX );
    }
	
	public function sortLang( $lang_code )
	{
		if( !file_exists( IPS_ADMIN_PATH .'/language/lang-' . $lang_code . '.php' ) )
		{
			return false;
		}
		
		$lang = include( IPS_ADMIN_PATH .'/language/lang-' . $lang_code . '.php' );
		
		ksort( $lang );

		$sorted = array(
			'no_sort' => array()
		);
		foreach( $lang as $name => $value )
		{
			if( strpos( $name, '_' ) !== false )
			{
				$n = strstr($name, '_', true );
				$sorted[$n][$name] = $value;
			}
			else
			{
				$sorted['no_sort'][$name] = $value;
			}
		}
		$file = "<?php\nreturn array(\n";

		$i = 0;
		foreach( $sorted as $n => $v )
		{
			foreach( $v as $name => $value )
			{
				$i++;
				$file .= "\t'" . $name ."' => '" . str_replace( "'", "\\'", $value ) . "',\n";
			}
			$file .= "\n";
		}
		$file .= ");";
		
		if( $i == count( $lang ) )
		{
			file_put_contents( IPS_ADMIN_PATH .'/language/lang-' . $lang_code . '.php', $file );
		}
	}

} 