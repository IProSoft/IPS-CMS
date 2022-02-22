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
Name: IMPORT VINES
Description: Plugin IMPORT VINES.
Data: 2012-01-16
*/

class Vines
{
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function install()
	{

		PD::getInstance()->query("
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_import_vines' ) . "` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `cron_urls` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `cron_category` int(10) NOT NULL DEFAULT '0',
			  `cron_interval` varchar(50) NOT NULL DEFAULT '',
			  `cron_interval_time` varchar(50) NOT NULL DEFAULT '',
			  `cron_watermark_add` varchar(250) NOT NULL DEFAULT '',
			  `cron_count` int(10) NOT NULL DEFAULT '10',
			  `cron_main_or_wait` varchar(50) NOT NULL DEFAULT 'waiting',
			  `cron_tags` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `cron_users` varchar(250) NOT NULL DEFAULT '',
			  `cron_check_time` varchar(50) NOT NULL DEFAULT 'everytime',
			  `cron_next_check_time` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			
			
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_import_vines_files' ) . "` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `cron_id` int(10) NOT NULL,
			  `cron_url` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `cron_serialized` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  FULLTEXT KEY `cron_url` (`cron_url`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		");
		$contents = @file_get_contents( IPS_ADMIN_PATH .'/.htaccess' );
		if( !empty( $contents ) && strpos( $contents, 'call-$1.php' ) === false )
		{
			@file_put_contents( IPS_ADMIN_PATH .'/.htaccess', "\nRewriteRule ^plugin/([A-Za-z0-9_]+)$ libs/plugins/$1/call-$1.php [L]", FILE_APPEND );
		}
		
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
		//Config::remove( 'plugin_import_vines_adult_only' );
		//Config::remove( 'plugin_import_vines_remove_inactiv' );
		//Config::remove( 'plugin_import_vines_message' );
		//Config::remove( 'plugin_import_vines_description' );
		PD::getInstance()->query('
			DROP TABLE IF EXISTS `' . db_prefix( 'plugin_import_vines' ) . '`;
			DROP TABLE IF EXISTS `' . db_prefix( 'plugin_import_vines_files' ) . '`;
		');
	}
	
	public function info()
	{
		return array(
			'pl_PL' => array(
				'plugin_name' => 'IMPORT VINES',
				'plugin_description' => 'Plugin IMPORT VINES'
			),
			'en_US' => array(
				'plugin_name' => 'IMPORT VINES',
				'plugin_description' => 'Plugin IMPORT VINES'
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

		echo '
		<br />
		<a href="' . admin_url( 'plugins', 'plugin=vines&plugin_action=add' ) . '" class="button">Dodaj zadanie importu</a>
		<a href="' . admin_url( 'plugins', 'plugin=vines&plugin_action=list' ) . '" class="button">Lista zadań</a>
		<br /><br />';
		$action = isset( $_GET['plugin_action'] ) ? $_GET['plugin_action'] : false;
		
		if( isset( $_POST['plugin_import_vines'] ) )
		{
			echo admin_msg( array(
				'alert' => $this->saveDraft( $_POST['plugin_import_vines'] )
			) );
		}
		
		
		switch ( $action )
		{
			
			
			case 'start':
				$this->preparePost( (isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? $_GET['id'] : false ) );
			break;
			case 'edit':
				echo $this->posts( (isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? $_GET['id'] : false ), 'post' );
			break;
			case 'add':
				echo $this->posts( ( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ? $_POST['id'] : false  ) );
			break;
			case 'delete':
				echo $this->delete( (isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? $_GET['id'] : false ) );
			break;
			
			case 'list':
			default:
				echo $this->listDrafts();
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
	public function saveDraft( $data )
	{
		
		
		if( Config::get('categories_option') == 1 && !$this->check( $data, 'category' ) )
		{
			return 'Wybierz kategorię';
		}
		
		if( !$this->check( $data, 'users' ) )
		{
			return 'Podaj login przynajmniej jednego użytkownika';
		}
		
		if( !$this->check( $data, 'count' ) )
		{
			return 'Wpisz liczbę materiałów do importu';
		}
		
		if( isset( $data['watermark_add'] ) )
		{
			$data['watermark_add'] = $data['watermark_add'];
		}
		else
		{
			$data['watermark_add'] = '0';
		}
	
	
		$data['extract_videos'] = isset( $data['extract_videos'] ) ? '1' : '0' ;
		$data['extract_images'] = isset( $data['extract_images'] ) ? '1' : '0' ;
	
		if( $this->insertDraft( $data ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=vines&plugin_action=list', 'Zadanie zostało dodane/zmienione' );
		}
		else
		{
			return 'Wystąpił błąd podczas dodawania/edycji wpisu.';
			
		}
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function insertDraft( $data )
	{
		
		$insert = array();
		if( isset( $data['id'] ) )
		{
			$id = $data['id'];
			unset( $data['id'] );
		}
		elseif( isset( $_GET['id'] ) && !empty( $_GET['id'] ) )
		{
			$id = $_GET['id'];
		}
		foreach( $data as $field => $value )
		{
			if( $value != '' )
			{
				$insert['cron_' . $field] = $value;
			}
		}
		
		if( !isset( $id ) && PD::getInstance()->insert( 'plugin_import_vines', $insert ) )
		{
			return true;
		}
		elseif( isset( $id ) )
		{
			PD::getInstance()->update( 'plugin_import_vines', $insert, array( 'id' => $id ));
			return true;
		}
		
		return false;
	}
	
	public function inputField( $key, $array, $array_2, $default = '' )
	{
		if( isset( $array[ $key ] ) )
		{
			return $array[ $key ];
		}
		if( isset( $array_2[ 'cron_' . $key ] ) )
		{
			return $array_2[ 'cron_' . $key ];
		}
		return $default;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function posts( $import_id )
	{
		$row = array();
		
		if( !empty( $import_id ) && is_numeric( $import_id ) )
		{
			$row = PD::getInstance()->select( 'plugin_import_vines', array( 
				'id' => $import_id
			), 1);
		}
		
		$plugin_import_vines = isset( $_POST['plugin_import_vines'] ) ? $_POST['plugin_import_vines'] : array() ;
		
		$category					= $this->inputField( 'category', $plugin_import_vines, $row );
		$interval					= $this->inputField( 'interval', $plugin_import_vines, $row, 'daily' );
		$count						= $this->inputField( 'count', $plugin_import_vines, $row, 10 );
		$main_or_wait				= $this->inputField( 'main_or_wait', $plugin_import_vines, $row, 'waiting' );
		$tags						= $this->inputField( 'tags', $plugin_import_vines, $row );
		$users						= $this->inputField( 'users', $plugin_import_vines, $row, USER_LOGIN );

	
		$watermark_add			= $this->inputField( 'watermark_add', $plugin_import_vines, $row, 0 ) != 0 ? 1 : 0;
		
		$check_time	= $this->inputField( 'check_time', $plugin_import_vines, $row, 'everytime' );
		
			echo '
			
			
		<form action="" method="post">	
		' . displayArrayOptions(array(

				'category' => array(
					'current_value' => '',
					'option_set_text' => 'Kategoria do której zostaną dodane pliki',
					'option_type' => 'text',
					'option_depends' => 'categories_option',
					'option_value' => '<select style="width: 200px;" id="file_category" name="plugin_import_vines[category]">'.Categories::categorySelectOptions( $category ).'</select>'
				),
				'plugin_import_vines[users]' => array(
					'current_value' => $users,
					'option_set_text' => 'Loginy userów, którym zostaną losowo przypisane obrazki(oddziel przecinkiem)',
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'tags_fancy_input'
				),
				'plugin_import_vines[main_or_wait]' => array(
					'current_value' => $main_or_wait,
					'option_set_text' => 'Gdzie dodać importowane materiały',
					'option_select_values' => array(
						'main' => 'Na główną',
						'wait' => 'Do poczekalni',
					)
				),
				'plugin_import_vines[tags]' => array(
					'current_value' => $tags,
					'option_set_text' => 'Dodatkowe słowa kluczowe(oddziel przecinkiem)',
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'tags_fancy_input'
				),
				'plugin_import_vines[interval]' => array(
					'current_value' => $interval,
					'option_set_text' => 'Jak często importować materiały',
					'option_select_values' => array(
						'hourly' => 'Co godzinę',
						'hourly_two' => 'Co 2 godziny',
						'hourly_twelve' => 'Co 12 godzin',
						'daily' => 'Co 24h',
						'weekly' => 'Co tydzień'
					)
				),
				'plugin_import_vines[count]' => array(
					'current_value' => $count,
					'option_set_text' => 'Ile materiałów importować',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'plugin_import_vines[watermark_add]' => array(
					'current_value' => $watermark_add,
					'option_set_text' => 'Dodać znak wodny',
					'option_names' => 'yes_no',
				),
				
				
				'plugin_import_vines[check_time]' => array(
					'current_value' => $check_time,
					'option_set_text' => 'Kiedy sprawdzać czy dodano nowe pliki',
					'option_select_values' => array(
						'everytime' => 'Przy każdym uruchomieniu',
						'daily' => 'Raz dziennie',
						'weekly' => 'Raz w tygodniu'
					)
				),
			)) ;
		if( isset( $id ) && $id )
		{
			echo '<input type="hidden" value="' . $id . '" name="plugin_import_vines[id]" /> ';
		}
		echo '
		<input type="submit" value="' . __('save') . '" class="button">
		</form>
		';
	}
	
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function preparePost( $cron_id, $call = false )
	{
		
		if( empty( $cron_id ) || !is_numeric( $cron_id ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=vines&plugin_action=list', 'Wystąpił błąd. Brak poprawnego ID' );
		}
		
		require_once( IPS_ADMIN_PATH .'/import-functions.php');
		require_once( IPS_ADMIN_PATH .'/libs/class.FastImage.php');
		
		$row = PD::getInstance()->select( 'plugin_import_vines', array( 
			'id' => $cron_id
		), 1, '');
		
		if( time() > $row['cron_next_check_time'] || $call == false )
		{
			
			$files = glob( ABS_PATH . '/_vines_extract/uploads/*.txt', GLOB_NOSORT );
			
			$images = $videos = array();
			
			if( $files )
			{
				
				foreach( $files as $file )
				{
					$data = json_decode( file_get_contents( $file ) ) ;
					
					$unique_filename = basename( $file, '.txt' );
					
					$videos[] = array(
						'unique_filename' => $unique_filename,
						'link' => $data->source,
						'title' => $data->upload_title,
						'img' => $data->image_src,
						'site' => parse_url( $data->get_from, PHP_URL_HOST ),
					);
				}
			}
			$files_ready = array();
			
		
	
			/**    VIDEO    **/
			foreach( $videos as $id => $video_id )
			{
				if( isset( $video_id['title'] ) )
				{
									
					if( !empty( $video_id['title'] ) )
					{

						$keywords = !empty( $row['cron_tags'] ) ? str_replace('|', ',', $row['cron_tags'] ) : extractCommonWords( $video_id['title'] );
						
						if( is_array( $keywords ) )
						{
							$keywords = implode( ',', array_values( $keywords ) );
						}
						$files_ready[$id] = $video_id;
					
						$files_ready[$id]['upload_video'] = $video_id['link'];
						$files_ready[$id]['title'] = trim( ucfirst( $video_id['title'] ) );
						$files_ready[$id]['upload_tags'] = $keywords;
						$files_ready[$id]['file_category'] = $row['cron_category'];
						$files_ready[$id]['upload_type'] = 'video';
						$files_ready[$id]['upload_subtype'] = 'mp4';
						$files_ready[$id]['size-checked'] = true;
					
					}
					if( empty( $video_id['title'] ) )
					{
						error_log( 'import_vines-Error:' . $video_id['link'] .' -- '. $video_id['title'] );
					}
				}
				
			}
			

			$kolejka = 0;
			foreach( $files_ready as $file )
			{
				if( !empty( $file['title'] ) )
				{
					$exists = PD::getInstance()->cnt('plugin_import_vines_files',  array( 
						'cron_url' => $file['link']
					));

					if( empty( $exists ) )
					{
						$exists = PD::getInstance()->cnt( 'upload_imported',  array( 
							'link' => ( isset( $file['upload_video'] ) ? $file['upload_video'] : $file['link'] )
						));

						if( empty( $exists ) )
						{
							
							PD::getInstance()->insert( 'plugin_import_vines_files', array( 
								'cron_id' => $cron_id,
								'cron_url' => $file['link'],
								'cron_serialized' => serialize( $file )
							) );
							$kolejka++;
						}
					}
					
				}
			}

			switch( $row['cron_check_time'] )
			{
				case 'everytime':
					$time = time();
				break;
				case 'daily':
					$time = strtotime( date("Y-m-d H", strtotime("next Day") ) . ':00:00' );
				break;
				case 'weekly':
					$time = strtotime( date("Y-m-d H", strtotime("next Week") ) . ':00:00' );
				break;
				
			}
			
			PD::getInstance()->update( 'plugin_import_vines', array( 
				'cron_next_check_time' => $time
			), array( 'id' => $cron_id ));

		}
		
		/**
		* Wykonanie 
		*/
		if( isset( $_GET['call'] ) || $call )
		{

			$rows_import = PD::getInstance()->select( 'plugin_import_vines', array( 
				'id' => $cron_id
			));
			
			foreach( $rows_import as $row_import )
			{
				/* if( time() > $row_import['cron_interval_time'] )
				{ */
					$rows_files = PD::getInstance()->select( 'plugin_import_vines_files', array(
						'cron_id' => $row_import['id']
					), $row_import['cron_count'] );
					
					if( $row_import['cron_count'] == 1 )
					{
						$rows_files = array( $rows_files );
					}
					
					foreach( $rows_files as $row )
					{
						cronIPSCall( IPS_ADMIN_URL . '/plugin/Vines?id=' . $row['id'], 2 );
					}
					
				/* 	switch( $row_import['cron_interval'] )
					{
						case 'hourly':
							$time = strtotime( date("Y-m-d H", strtotime("+1 hour") ) . ':00:00' );
						break;
						case 'hourly_two':
							$time = strtotime( date("Y-m-d H", strtotime("+2 hour") ) . ':00:00' );
						break;
						case 'hourly_twelve':
							$time = strtotime( date("Y-m-d H", strtotime("+12 hour") ) . ':00:00' );
						break;
						case 'daily':
							$time = strtotime( date("Y-m-d H", strtotime("next Day") ) . ':00:00' );
						break;
						case 'weekly':
							$time = strtotime( date("Y-m-d H", strtotime("next Week") ) . ':00:00' );
						break;
					}
					PD::getInstance()->update( 'plugin_import_vines', array( 
						'cron_interval_time' => $time
					), array( 'id' => $row_import['id'] ));
				} */
			}
			
		}
		if( !$call )
		{
			ips_admin_redirect( 'plugins', 'plugin=vines&plugin_action=list', 'Akcja wykonana poprawnie, dodano ' . $kolejka . ' plików do kolejki.'  );
		}
	}
	
	
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function delete( $id )
	{
		$redir = admin_url( 'plugins', 'plugin=vines&plugin_action=list' );
		
		if( empty( $id ) )
		{
			ips_redirect( $redir, 'ID jest niepoprawne' );
		}
		if( PD::getInstance()->delete( 'plugin_import_vines', array( 'id' => (int)$id )) )
		{
			ips_redirect( $redir, 'Poprawnie usunięto zadanie o ID ' . $id );
		}
		else
		{
			ips_redirect( $redir, 'Wystąpił błąd podczas usuwania zadania.' );
		}
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function listDrafts()
	{
		$res = PD::getInstance()->from( 'plugin_import_vines' )->orderBy('id')->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">Brak zdefiniowanych zadań</h4>';
			return false;
		}
		
		$fields = array('Akcja', 'Loginy', 'Gdzie dodać', 'Częstotliwość', 'Szczegóły zadania');	
		
		?>
		<script type="text/javascript" src="js/tableSorter.js"></script> 
		<script type="text/javascript">
			$(document).ready(function(){ 
				$(".features-table").tablesorter({selectorHeadersName : 'tfoot', selectorHeaders : 'tfoot th', excludeHeader: [0]});
				$(".features-table").tablesorter({selectorHeadersName : 'thead', selectorHeaders : 'thead th', excludeHeader: [0]});
			}); 

			$(function() {
				$('.dialog-msg').on('click', function( e ){
					e.preventDefault();
					link = $(this).attr('href');
					$( "#dialog-confirm" ).dialog({
						resizable: false,
						height:240,
						width: 500,
						modal: true,
						buttons: [
							{
								text: "Rozpocznij",
								click: function() {
									$( this ).dialog( 'close' );
									window.location.href = link;
								}
							},
							{
								text: ips_i18n.__( 'js_common_cancel' ),
								click: function() {
									$( this ).dialog( 'close' );
									return false;
								}
							}
						]
					});
				});
				
				$( document ).tooltip({
					items: "[data-img]",
					content: function() {
						var element = $( this );
					
						var img = element.attr('src');
						return '<img src="'+ img + '">';
						
					}
				});
			});
		</script>
		<style>
		.features-table td, .features-table th{ white-space: normal !important }
		.features-table-actions {float: left; width: 150px;}
		.featured-hover i {display: inline-block; width: 80px;}
		#dialog-confirm{ display: none;}
		</style>
<?php		
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $fields ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $fields ).'</tr></tfoot>
		<tbody>
		
		';
		

		
		foreach( $res as $fetchnow )
		{
			$pola = array();
			
			echo '<tr id="featured-' . $fetchnow['id'] . '" class="featured-hover">';
			
			$pola[] = '
				<div class="features-table-actions" style="display:block">
					<span><a href="' . admin_url( 'plugins', 'plugin=vines&plugin_action=start&call=start&id='.$fetchnow['id'] ) . '">Wykonaj</a> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=vines&plugin_action=start&id='.$fetchnow['id'] ) . '">Kolejkuj</a> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=vines&plugin_action=edit&id='.$fetchnow['id'] ) . '">Edytuj</a></span> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=vines&plugin_action=delete&id='.$fetchnow['id'] ) . '">Usuń</a></span>
				</div>';
			
			$pola[] = $fetchnow['cron_users'];
			$pola[] = $fetchnow['cron_main_or_wait'];
			switch( $fetchnow['cron_interval'] )
			{
				case 'hourly':
					$pola[] = 'Co godzinę';
				break;
				case 'hourly_two':
					$pola[] = 'Co dwie godziny';
				break;
				case 'hourly_twelve':
					$pola[] = 'Co 12 godzin';
				break;
				case 'daily':
					$pola[] = 'Codziennie';
				break;
				case 'weekly':
					$pola[] = 'Co tydzień';
				break;
			}
			$cron_category = '';
			if( Config::get('categories_option') == 1 )
			{
				$cats = Categories::getCategories( $fetchnow['cron_category']);
				if( isset( $cats['category_name'] ) )
				{
					$cron_category = 'Kategoria: ' . $cats['category_name'];
				}
			}
			
			$pola[] = 
			'Tagi: ' . str_replace( '|', ', ', $fetchnow['cron_tags'] ) . '<br /> '.
			'<i>Ile importować: </i>' . $fetchnow['cron_count'] . '<br /> '.
			'<i>Dodac znak wodny: </i>' . ( $fetchnow['cron_watermark_add'] == 1 ? 'Tak' : 'Nie' ) . '<br /> ' 
			. $cron_category;
			
			
			echo generateTable( 'body', $pola ). '</tr>';
		}
		echo '</tbody></table>

		<div class="div-info-message">
			<p><strong>Uwaga:</strong> dodawanie plików może nie wykonać się zawsze w 100% ponieważ może wystąpić błąd po stronie serwisu, błąd pobierania obrazka itp czyli np z 10 zostanie dodanych przy jednej kolejce 6 materiałów do serwisu.</p>
			<p><strong>Opcje: Wykonaj:</strong> Dodanie plików do kolejki i do serwisu.</p>
			<p><strong>Opcje: Kolejkuj:</strong> Dodanie plików tylko do kolejki. Dodanie do serwisu nastąpi podczas wywołania CRON</p>
		</div>
		<div class="div-info-message">
			' . __s('cron_tasks_info_5', IPS_ADMIN_URL . '/plugin/Vines', IPS_ADMIN_URL . '/plugin/Vines' ) . '
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
				
			</div>
			<input type="submit" class="button" value="' . __('save') . '" />
		</form>
		';
	}
	
	
	
	
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function check( $data, $name, $strlen = false )
	{
		if( !isset( $data[$name] ) )
		{
			return false;
		}
		if( empty( $data[$name] ) )
		{
			return false;
		}
		if( $strlen && strlen( $data[$name] ) > $strlen )
		{
			return false;
		}
		
		return true;
	}
}
?>