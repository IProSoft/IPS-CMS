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
	
/*
Name: Optymalizacja obrazów
Description: Plugin umozliwia dodatkową optymalizację obrazów.<br /> Obrazy są wysyłane do zewnętrznego serwisu smushit.com.
Data: 2012-03-07
*/

class Optimizer
{
	public $license = '[license_number]';
	
	public $files = array();
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function install()
	{
		Config::update( 'optimized_images_part_count', 10 );
		
		
		PD::getInstance()->query("
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_optimized_images' ) . "` (
			  `optimized_id` int(20) NOT NULL AUTO_INCREMENT,
			  `optimized_image` varchar(250) NOT NULL DEFAULT '',
			  `optimized_reduced_size` int(10) NOT NULL DEFAULT '0',
			  `optimized_status` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`optimized_id`),
			  UNIQUE KEY `optimized_image` (`optimized_image`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		");
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
		Config::remove( 'optimized_images_part_count' );
		PD::getInstance()->query('
			DROP TABLE IF EXISTS `' . db_prefix( 'plugin_optimized_images' ) . '`;
		');
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
				'plugin_name' => 'Optymalizacja obrazów',
				'plugin_description' => 'Plugin umozliwia dodatkową optymalizację obrazów.<br /> Obrazy są wysyłane do zewnętrznego serwisu smushit.com.'
			),
			'en_US' => array(
				'plugin_name' => 'Image Optimizer',
				'plugin_description' => 'Plugin allow admin optimize images with smushit.com'
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
		if( strpos( base64_decode('aXN0b3RuYS5wbA=='), str_replace( array( 'www.', 'http://', '/' ), '', ABS_URL ) ) === false && !defined('IPS_SELF') )
		{
			die( '<br />' . __( base64_decode( 'aXBzX2xpY2Vuc2Vfbm90X2Fzc2lnbmVk' ) ) );
		}
		
		echo '
		<br />
		<a href="' . admin_url( 'plugins', 'plugin=optimizer&optimizer_action=stats' ) . '" class="button">Statystyki</a>
		<a href="' . admin_url( 'plugins', 'plugin=optimizer&optimizer_action=all' ) . '" class="button">Optymalizuj wszystko</a>
		<a href="' . admin_url( 'plugins', 'plugin=optimizer&optimizer_action=settings' ) . '" class="button">Ustawienia</a>
		<br /><br />';
		$action = isset( $_GET['optimizer_action'] ) ? $_GET['optimizer_action'] : false ;

		switch ( $action )
		{
			case 'stats':
				$this->stats();
			break;
			case 'all':
				$this->optimize( 'images/', true );
				
				$page = $this->optimizeFiles();
				
				echo '
				<img width="48" height="48" src="/images/svg/spinner.svg">
				<script>
				$(function() {
					setTimeout(function(){
						window.location.href = "' . admin_url( 'plugins', 'plugin=optimizer&optimizer_action=progress&page=1' . '' ) . '";
					}, 1000 );
				});
				</script>
				';
		
		
			break;
			
			case 'clear':
				$this->uninstall();
				$this->install();
				ips_admin_redirect( 'plugins', 'plugin=optimizer', 'Dane zostały wyczyszczone.' );
			break;
			case 'progress':
				$this->runOptimization();
			break;
			case 'settings':
			default:
				echo $this->settingForm();
				Session::set( 'admin_redirect', admin_url( 'plugins', 'plugin=optimizer' ) );
			break;
		}	
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function stats()
	{
		$optimized = PD::getInstance()->cnt( '`plugin_optimized_images`', array(
			"optimized_status" => 'optimized'
		) );
		
		$not_optimized = PD::getInstance()->cnt( '`plugin_optimized_images`', array(
			"optimized_status" => 'waiting'
		) );
		
		$optimized_size = PD::getInstance()->query( 'SELECT SUM(optimized_reduced_size) as size FROM ' . db_prefix( 'plugin_optimized_images' ) );
		
		echo '
		<div class="features-table-actions-div">
			<div class="table-body">
				<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
				<thead>
					<tr>
						<th><span class="table-header">Typ</span></th>
						<th><span class="table-header">Info</span></th>
					</tr>
				</thead>
				<tbody>
				<tr>
					<td> Zoptymalizowanych </td>
					<td> ' . $optimized . '</td>
				</tr>
				<tr>
					<td> Do optymalizacji </td>
					<td> ' . $not_optimized . '</td>
				</tr>
				<tr>
					<td> Zyskano MB </td>
					<td> ' . ( $optimized_size[0]['size'] > 0 ? round( convertBytes( $optimized_size[0]['size'] ) / 1048576, 1 ) : $optimized_size[0]['size'] ). '</td>
				</tr>
				
				</tbody>
				</table>
			</div>
		</div>
		';
	
	}
		/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function settingForm()
	{
		
		
		return '
		<form action="admin-save.php" enctype="multipart/form-data" method="post">
			<div class="content_tabs tabbed_area">
				' . displayOptionField( 'optimized_images_part_count',  array(
					'option_set_text' => 'Ilośc obrazów optymalizowanych w jednym przejściu petli ( gdyby występował timeout serwera proszę zmniejszyć tą opcję)',
					'option_type' => 'input'
				)) . '
			</div>
			<input type="submit" class="button" value="' . __('save') . '" /> <a href="' . admin_url( 'plugins', 'plugin=optimizer&optimizer_action=clear' ) . '" class="button">Wyczyść dane optymalizacji</a>
		</form>
		';
	}
	
	
	public function optimizeFiles( $page = 1 )
	{
		$pages = xy( $page, 100 );
		
		$files = PD::getInstance()->select( IPS__FILES, false, $pages[1], '*', array( 'id' => 'DESC' ) );
		
		if( empty( $files ) )
		{
			return true;
		}
		
		foreach( $files as $file )
		{
			if( PD::getInstance()->cnt( '`plugin_optimized_images`', "optimized_image = 'id||" . $file['id'] . "||" . $file['upload_image'] . "'" ) == 0  )
			{
				PD::getInstance()->insert( '`plugin_optimized_images`', array(
					'optimized_image' => 'id||' . $file['id'] . '||' . $file['upload_image'],
					'optimized_status' => 'waiting'
				));
			}
		}
		
		return $this->optimizeFiles( $page + 1 );
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function optimize( $folders, $force = false)
	{
		
		if( is_array( $folders ) )
		{
			foreach( $folders as $folder)
			{
				$this->getFiles( $folder, $force );
			}
		}
		else
		{
			$this->getFiles( $folders, $force );
		}
		
		
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function getFiles( $folder, $force )
	{
		
		/** Pobiera tylko pliki z katalogów images itp, nie obrazy materiałów */
		$iterator = new RecursiveDirectoryIterator( ABS_PATH . '/' . $folder );
		$files = array();
		foreach ( $iterator as $file )
		{
			if ( $file->isFile() && $file->getFilename() != '.htaccess' )
			{
				$files[] = $folder . $file->getFilename();
			}
		}
		
		$in_database = PD::getInstance()->select('`plugin_optimized_images`', array(
			'optimized_status' => 'optimized'
		), null, 'optimized_image');
		
		if( !empty( $in_database ) && !$force )
		{
			$merged = array();
			foreach( $in_database as $images )
			{
				$merged = array_merge( $merged, array_values($images) );
			}
			$files = array_diff( $files, $merged );
		}

		$this->files = array_merge( $this->files, $files );
		
		foreach ( $files as $file )
		{
			PD::getInstance()->insert( '`plugin_optimized_images`', array(
				'optimized_image' => $file,
				'optimized_status' => 'waiting'
			));
		}
		
		unset( $files );

	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function runOptimization()
	{
		$page_num = isset( $_GET['page'] ) ? $_GET['page'] : 1;
		
		$pages = xy( $page_num, Config::get( 'optimized_images_part_count' ) );
		
		$to_optimize = PD::getInstance()->select('`plugin_optimized_images`', array(
			'optimized_status' => 'waiting'
		), $pages );
		
		if( empty( $to_optimize ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=optimizer', 'Pliki zoptymalizowane.' );
		}
		
		require_once( dirname( __FILE__ ) .'/smush.php' );
		
		echo '
		<div class="nice-blocks features-table-actions-div">
			<div class="table-body">
						<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
						<thead>
							<tr>
								<th><span class="table-header">Status</span></th>
								<th><span class="table-header">Plik</span></th>
								<th><span class="table-header">Info</span></th>
							</tr>
						</thead>
							<tbody>
								';
							
		foreach( $to_optimize as $key => $image )
		{
			if( strpos( $image['optimized_image'], 'id||' ) !== false )
			{
				$sumarize = array( 
					'reduced_size' => 0,
					'percent' => 0
				);
				
				$folders = glob( IMG_PATH . '/*', GLOB_ONLYDIR | GLOB_NOSORT );
				
				$image['optimized_image'] = explode( '||', $image['optimized_image'] );
				$image['optimized_image'] = end( $image['optimized_image'] );
				
				echo '<tr>
				<td style="width: 30px;"><img src="images/icons/update-success.png" /></td>
				<td> ' . $image['optimized_image'] . '</td>';
				
				foreach( $folders as $folder )
				{
					if( file_exists( $folder .'/' . $image['optimized_image'] ) )
					{
						$data = smush_url( ABS_URL . IMG_UP . '/'. basename($folder) . '/' .$image['optimized_image'] , $folder .'/' . $image['optimized_image'] );
						
						if( isset( $data['saving'] ) )
						{
							$sumarize['reduced_size'] = $sumarize['reduced_size'] + $data['saving'];
							$sumarize['percent'] = $sumarize['percent'] + $data['percent'];
						}
					}
				} 
				
				if( $sumarize['reduced_size'] != 0  )
				{
					echo '
						<td> Optymalizacja: ' . $sumarize['percent'] . '%</td>
					';
				}
				else
				{
					echo '
						<td> Plik nie wymaga optymalizacji </td>
					';
				}
				
				PD::getInstance()->update('`plugin_optimized_images`', array(
					"optimized_status" => 'optimized', 
					'optimized_reduced_size' => $sumarize['reduced_size']
				), array( 'optimized_id' => $image['optimized_id'] ));
				
				echo '</tr>';
			}
			else
			{
				$data = smush_url( ABS_URL . $image['optimized_image'] , ABS_PATH . '/' . $image['optimized_image'] );
	 
				echo '<tr>
				<td style="width: 30px;"><img src="images/icons/update-success.png" /></td>
				<td> ' . $image['optimized_image'] . '</td>';
				
				if( isset( $data['saving'] ) )
				{
					echo '
						<td> Optymalizacja: ' . $data['percent'] . '%</td>
					';
					PD::getInstance()->update('`plugin_optimized_images`', array(
						"optimized_status" => 'optimized', 
						'optimized_reduced_size' => $data['saving']
					), array( 'optimized_id' => $image['optimized_id'] ));
				}
				else
				{
					echo '
						<td> Plik nie wymaga optymalizacji </td>
					';
				}
				echo '</tr>';
			}
		}
		
		$url = admin_url( 'plugins', 'plugin=optimizer&optimizer_action=progress&page=' . ( $page_num + 1 ) );
		
		echo '
							</tbody>
						</table>
					
				<div class="clear"></div>
			</div>
		</div>
		<style>
		 .ui-progressbar .ui-progressbar-value { background-image: url(http://jqueryui.com/resources/demos/progressbar/images/pbar-ani.gif); }
		 .ui-progressbar {height: 1.2em !important}
		 ..ui-widget-content( background: none;)
		</style>
		<script>
		$(function() {
			setTimeout(function(){
				window.location.href = "' . $url . '";
			}, 4000 );
		});
		</script>
		';
	}
}
?>