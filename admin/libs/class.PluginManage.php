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
	
class Plugin_Manage
{

    /**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public $plugins = array();

    /**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function __construct()
	{
		$this->pluginDirs = array(
			ABS_PLUGINS_PATH,
			IPS_ADMIN_PATH .'/libs/plugins'
		);
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function loadPlugins()
    {
		$response = ips_api_post( '__cache/cache_plugins_list.json' );
		
		if( !$response || !isset( $response['plugins'] ) )
		{
			$response = ips_api_post( 'plugins/all' );
		}
		
		$this->ips_plugins = $response ? $response['plugins'] : array();
		
        foreach( $this->pluginDirs as $path )
		{
			$this->getPlugins( $path );
		}
		
		return empty( $this->plugins ) ? false : true ;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
    private function getPlugins( $path )
    {
        if( !file_exists( $path ) || !is_readable( $path ) )
		{
			return;
		}
		
		$plugins = $this->get();
		
		$ignore = array( 'cgi-bin', '.', '..' );
        $handle = opendir( $path );
       
        while( false !== ( $file = readdir( $handle ) ) )
        {
            if( !in_array( $file, $ignore ) )
            {
                if( is_dir( $path . '/' . $file ) )
                {
                    $this->getPlugins( $path . '/' . $file );
                }
                elseif ( strstr( $file, 'class.plugin.' ) )
				{
					$name = str_replace ( array( 'class.plugin.', '.php' ), '', $file );
					
					$update = ( isset( $this->ips_plugins[ strtolower( $name ) ]['modified'] ) ? $this->ips_plugins[ strtolower( $name ) ]['modified'] : false );
					$this->plugins[ strtolower( $name ) ] = array(
						'path' => $path . '/' . $file,
						'class'	 =>  $name,
						'update' => ( $update && $update > filemtime( $path . '/' . $file ) ? true : false ),
						'license_number' => ( isset( $plugins[ strtolower( $name ) ] ) ? $plugins[ strtolower( $name ) ]['license_number'] : false ),
					);
				}
            }
        }
		
        closedir( $handle );
    }
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function initPlugin( $plugin_name )
	{
		$plugin_name = strtolower( $plugin_name );
		
		if( !isset( $this->plugins[ $plugin_name ]['path'] ) )
		{
			if( IPS_ADMIN_PANEL )
			{
				return ips_admin_redirect( 'plugins', false, array(
					'alert' => __('plugin_not_found')
				));
			}
			return false;
		}
		
		require_once( $this->plugins[ $plugin_name ]['path'] );
       
		$plugin = new $this->plugins[ $plugin_name ]['class'];
		
		$this->language( dirname( $this->plugins[ $plugin_name ]['path'] ) );
		
		return $plugin->init();
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function initAllPlugins()
    {
		foreach ( $this->plugins as $plugin )
        {
            $this->pluginInfo( $plugin );
        }
    }
	
	public function pluginInfo( $plugin )
	{
		
		require_once( $plugin['path'] );
       
		$plugin_info = new $plugin['class'];
		
		$plugin_info = $plugin_info->info();
		$plugin_info = $plugin_info[Config::get('admin_lang_locale')];
		
		$plugin_name = strtolower( $plugin['class'] );
		
		if( !$this->is_activ( $plugin_name ) )
		{
			$links = admin_url_button( 'plugins', 'plugin_turn_on', array( 'activate' => $plugin_name ), '' );
		}
		else
		{
			$links = admin_url_button( 'plugins', 'plugin_settings', array( 'plugin' => $plugin_name ), '' )
			 . admin_url_button( 'plugins', 'plugin_turn_off', array( 'deactivate' => $plugin_name ), '' )
			 . admin_url_button( 'plugins', 'plugin_update', array( 'update' => $plugin_name ), '' );
		}
			
		echo  displayArrayOptions( array(
			'option' . rand() => array(
				'current_value' => '',
				'option_set_text' => ( isset( $plugin_info['plugin_display_name'] ) ? $plugin_info['plugin_display_name'] : $plugin_info['plugin_name'] ).'<span class="plugin-actions">' . $links . '</span></span>',
				'option_type' => 'text',
				'option_css' => ( $this->is_activ( $plugin_name ) ? 'active' : '' ) . ' ' . ( $plugin['update'] ? 'update' : '' ),
				'option_value' => '<div class="widget-descript">' . $plugin_info['plugin_description'] . '</div>'
			)
		));
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function is_activ( $plugin_name )
	{
		$is_activ = Config::getArray( 'ips_plugins', $plugin_name );
		
		return !empty( $is_activ );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function activatePlugin( $plugin_name )
	{
		$plugin_name = strtolower( $plugin_name );
		
		if( isset( $this->plugins[ $plugin_name ] ) )
		{
			$this->switchPlugin( $plugin_name, 'install' );
		
			$plugins = $this->get();
			
			require_once( $this->plugins[ $plugin_name ]['path'] );
			$object = new $plugin_name;
			
			$plugins[$plugin_name] = array(
				'license_number' => $object->license,
				'init' => method_exists( $object, 'init' )
			);
			
			$this->switchPluginHooks( $plugin_name, 'add' );
			
			Config::update( 'ips_plugins', $plugins );
			
			return ips_admin_redirect( 'plugins', false, __s( 'plugin_turned_on', ucfirst($plugin_name) ) );
		}
	}
	
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function deactivatePlugin( $plugin_name )
	{
		$plugin_name = strtolower( $plugin_name );
		
		if( isset( $this->plugins[ $plugin_name ] ) )
		{
			$this->switchPlugin( $plugin_name, 'uninstall' );
			
			$plugins = $this->get();
			
			if( isset( $plugins[$plugin_name] ) )
			{
				unset( $plugins[$plugin_name] );
			}
			
			$this->switchPluginHooks( $plugin_name, 'remove' );
			
			Config::update( 'ips_plugins', serialize( $plugins ) );
			
			return ips_admin_redirect( 'plugins', false, __s( 'plugin_turned_off', ucfirst($plugin_name) ) );
		}
	}
	
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function switchPluginHooks( $plugin_name, $set )
	{
		$plugin_name = strtolower( $plugin_name );
		
		
		require_once( $this->plugins[ $plugin_name ]['path'] );
        $object = new $plugin_name;
		
		if( $action = $object->hasHooks() )
		{
			$hooks = Ips_Registry::get( 'Hooks' );
				
			if( $set == 'add' )
			{
				$hooks->register_action( $action['hook'], 'callable_plugin', $action['args'], $action['priority'], $action['key'] );
			}
			elseif( $set == 'remove' )
			{
				$hooks->delete_action_by_key( $action['key'], $action['hook'] );
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
	public function switchPlugin( $plugin_name, $action )
	{
		require_once( $this->plugins[ $plugin_name ]['path'] );
        $object = new $plugin_name;
		
		if( method_exists( $object, $action ) )
		{
			$object->{$action}();
		}
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function get()
	{
		$plugins = Config::getArray( 'ips_plugins' );
		
		if( is_array( $plugins ) )
		{
			return $plugins;
		}
		
		return array();
	}

	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function updatePlugin( $plugin_name )
	{
		$plugin_name = strtolower( $plugin_name );
		
		require_once( IPS_ADMIN_PATH .'/update-functions.php' );
		
		if( isset( $this->plugins[ $plugin_name ] ) )
		{
			if( !isset( $this->plugins[ $plugin_name ]['license_number'] ) )
			{
				return admin_msg( array(
					'alert' =>  __('license_auth_wrong_data_url')
				) );
			}

			$response = ips_api_post( 'user/hash', array (
				'user_license' => $this->plugins[ $plugin_name ]['license_number'],
				'user_email'   => Config::get('license_email'),
				'user_domain'  => ABS_URL
			)); 
			
			if( $response && !isset( $response['error'] ) )
			{
				$response = ips_api_post( 'download/plugin', array (
					'plugin_hash' => $plugin_name,
					'user_hash'	  => $response['user_hash']
				));
			}
				
			if( isset( $response['error'] ) )
			{
				return 	admin_msg( array(
				'alert' => $response['error'] 
			) );
			}

			if( $response )
			{
				try{
					
					$plugins_dir = IPS_ADMIN_PATH .'/libs/plugins';
					$plugin_folder = IPS_ADMIN_PATH .'/libs/plugins/' . ucfirst( strtolower( $plugin_name ) );
					
					File::deleteDir( $plugin_folder );
		
					if ( !file_exists( $plugin_folder ) )
					{
						mkdir( $plugin_folder, 0777, true );
					}
					
					File::create( $plugin_folder . '/install.zip', $response );
					
					$zip = new ZipArchive;
					$res = $zip->open( $plugin_folder . '/install.zip' );
					
					if( $res === true )
					{
						$zip->extractTo( $plugin_folder . '/' );
						$zip->close();
						
						@unlink( $plugin_folder . '/install.zip' );
						
						return true;
					}
					else
					{
						return admin_msg( array(
					'alert' =>  __('error_unpacking_zip_file')
						) );
					}
					
				} catch ( Exception $e ) {
										
					return admin_msg( array(
						'alert' => __('error_saving_zip_file') 
					) );

				}
			}
			else
			{
				return 	admin_msg( array(
					'alert' => __('plugin_update_error') 
				) );
			}
		}
	}

	public function availablePlugins()
	{
		foreach( $this->ips_plugins as $plugin_name => $plugin )
		{
			echo Templates::getInc()->getTpl( '/__admin/plugin_item.html', array_merge( $plugin, array(
				'installed' => isset( $this->plugins[ strtolower( $plugin_name ) ] ),
				'last_modified' => date("Y-m-d H:i", $plugin['modified'] )
			) ) ); 
		}
	}
	/**
	 * Loads plugin language files
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function language( $path )
	{
		$file = $path . '/language.php';
		
		if( file_exists( $file ) )
		{
			$phrases = include_once( $file );
			
			global ${IPS_LNG}; 
			${IPS_LNG} = array_merge( ${IPS_LNG}, $phrases[ Config::get('admin_lang_locale') ] );
		}
	}

}