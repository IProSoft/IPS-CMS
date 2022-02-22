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
	
class Sticked implements InterfacePluggable
{
	public $license = '[license_number]';
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function install()
	{
		Config::update( 'plugin_sticked', serialize( array( 
			'height' => 40, 
			'color' => '#ffffff',
			'file' => '',
			'align' => 'right',
			'add_to' => array(
				'image',
				'mem',
				'demotywator'
			)
		) ), true );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/			
	public function uninstall()
	{
		Config::remove( 'plugin_sticked' );
	}
	
	/**
	* Get plugin info
	*
	* @param 
	* 
	* @return 
	*/
	public function info()
	{
		return array(
			'pl_PL' => array(
				'plugin_name' => 'Sticked',
				'plugin_description' => 'Plugin umożliwia dodawanie znaku wodnego pod obrazkiem jak ma to miejsce w serwisie kwejk.pl lub demotywatory.pl<br />Znak jest dodawany tylko do materiałów typu kwejk/demotywator/mem',
				'plugin_display_name' => 'Sticked',
			),
			'en_US' => array(
				'plugin_name' => 'Sticked',
				'plugin_description' => 'Plugin allows admin to chose watermark sticked to bottom of image like 9GAG. Added only for file types: kwejk, demotywator, mem',
				'plugin_display_name' => 'Sticked',
			)
		);
	}
	

	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function init()
	{
		$files = Config::getArray( 'plugin_sticked', 'add_to' );
		
		$file = Config::getArray( 'plugin_sticked', 'file' );
		
		echo '
		<form method="post" enctype="multipart/form-data" action="admin-save.php">	
		' . displayArrayOptions( array(
			'plugin_sticked' => array(
				'option_display_name' => false,
				'option_is_array' => array(
					'add_to' => array(
						'current_value' => Config::getArray( 'plugin_sticked', 'add_to' ),
						'option_select_values' => [
							'image' => 'Image',
							'mem' => 'Mem',
							'demotywator' => 'Demotywator'
						],
						'option_multiple' => true,
						'option_type'	=> 'input',
						'option_lenght' => 10
					),
					'height' => array(
						'option_type' => 'input',
						'option_lenght' => 10,
						'option_css'	=> 'display_none' 
					),
					'file' => array(
						'option_type'	=> 'text',
						'option_value'	=> '<input type="file" name="plugin_sticked_file" /><input type="hidden" name="plugin_sticked[file]" value="' .  Config::getArray( 'plugin_sticked', 'file' ) . '" />',
						'option_sprintf' => adminUploadedFile( array( 'plugin_sticked' => 'file' ), 'upload/system/watermark' ),
					),
					'align' => array(
						'option_select_values' => array(
							'right' => __( 'plugin_sticked_align_right' ),
							'left'	=> __( 'plugin_sticked_align_left' )
						),
					),
					'color' => array(
						'option_type' => 'text',
						'option_value' => colorPickerArr( 'plugin_sticked', 'color' )
					),
				)
			)
		)) . '
		<a href="/admin/route-plugins" class="button ">' . __('back') . '</a>
		<input type="submit" value="' . __('save') . '" class="button">
		</form>
		';

		Session::set( 'admin_redirect', admin_url( 'plugins', 'plugin=sticked' ) );
	}
	/**
	* Register hooks/filters while page load
	*
	* @param 
	* 
	* @return 
	*/
	public function hooks()
	{
		add_filter( 'init_css_files', function( $array ){
			return add_static_file( $array, array(
				plugin_url( __FILE__, 'css/core.css' )
			) );
		}, 10 );
		
		if( IPS_ACTION == 'uploading' )
		{
			require_once( dirname( __FILE__ ) . '/lib/StickyImage.php' );
			
			$img = new StickyImage;
			
			add_filter( 'up_create_config', array( 
				$img, 'initImageConfig'
			) );
			
			add_filter( 'up_actions_finish', array( 
				$img, 'addLayer'
			) );
			
			add_filter( 'upload_db_insert', array( 
				$img, 'beforeInsert'
			) );
		}
	}
	
	/**
	* Register plugin language phrases
	*
	* @param 
	* 
	* @return 
	*/
	public function translate()
	{
		
	}
	
	/**
	* Define if the plugin have to be called while script load
	*
	* @param 
	* 
	* @return bool
	*/
	public function hasHooks()
	{
		return [
			'key' => 'plugin_sticked_hook',
			'hook' => 'load',
			'args' => array(
				'plugin' => 'Sticked',
				'actions' => [ 'uploading' ]
			),
			'priority' => 10,
		];
	}
}


?>