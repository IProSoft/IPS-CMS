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
	

class Importer
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
		
		PD::getInstance()->query("
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_cron_import' ) . "` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `cron_urls` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `cron_watermark_remove` varchar(250) NOT NULL DEFAULT '',
			  `cron_watermark_remove_cut_direction` varchar(250) NOT NULL DEFAULT '',
			  `cron_watermark_remove_cut_height` varchar(250) NOT NULL DEFAULT '',
			  `cron_category` int(10) NOT NULL DEFAULT '0',
			  `cron_interval` varchar(50) NOT NULL DEFAULT '',
			  `cron_interval_time` varchar(50) NOT NULL DEFAULT '',
			  `cron_count` int(10) NOT NULL DEFAULT '10',
			  `cron_main_or_wait` varchar(50) NOT NULL DEFAULT 'wait',
			  `cron_tags` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `cron_users` varchar(250) NOT NULL DEFAULT '',
			  `cron_check_time` varchar(50) NOT NULL DEFAULT 'everytime',
			  `cron_next_check_time` varchar(50) NOT NULL DEFAULT '',
			  `cron_extract_videos` tinyint(1) NOT NULL DEFAULT '0',
			  `cron_extract_images` tinyint(1) NOT NULL DEFAULT '1',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			
			
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_cron_import_files' ) . "` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `cron_id` int(10) NOT NULL,
			  `cron_url` varchar(255) NOT NULL,
			  `cron_serialized` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `cron_url` (`cron_url`)
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
		PD::getInstance()->query('
			DROP TABLE IF EXISTS `' . db_prefix( 'plugin_cron_import' ) . '`;
			DROP TABLE IF EXISTS `' . db_prefix( 'plugin_cron_import_files' ) . '`;
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
				'plugin_name' => 'IMPORT materiałów poprzez CRON',
				'plugin_description' => 'Plugin umożliwia import materiałów za pomocą zadań CRON z wybranych stron.',
			),
			'en_US' => array(
				'plugin_name' => 'CRON file IMPORT',
				'plugin_description' => 'Plugin allows admin import images/videos from other sites with CRON'
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
		if( strpos( base64_decode('[license_code]'), str_replace( array( 'www.', 'http://', '/' ), '', ABS_URL ) ) === false && !defined('IPS_SELF') )
		{
			die( '<br />' . base64_decode( '[license_code_alert]' ) );
		}
		
		echo '
		<br />
		<a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=add' ) . '" class="button">Dodaj zadanie importu</a>
		<a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=list' ) . '" class="button">Lista zadań</a>
		<a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=files' ) . '" class="button">Kolejka plików</a>
		<br /><br />';
		$action = isset( $_GET['plugin_action'] ) ? $_GET['plugin_action'] : false;
		
		if( isset( $_POST['plugin_importer'] ) )
		{
			echo admin_msg( array(
				'alert' => $this->saveDraft( $_POST['plugin_importer'] )  
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
			case 'files':
				echo $this->listFiles();
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
		
		if( !$this->check( $data, 'urls' ) )
		{
			return 'Podaj przynajmniej jeden poprawny adres strony';
		}
		else
		{
			$urls = ( strpos( $data['urls'], '|' ) !== false ? explode( '|', $data['urls'] ) : array( $data['urls'] ) );
			if( count( $urls ) < 1 )
			{
				return 'Podaj przynajmniej jeden poprawny adres strony';
			}
			foreach( $urls as $url )
			{
				if( filter_var( $url, FILTER_VALIDATE_URL ) == false )
				{
					return 'Podaj tylko poprawne adresy stron';
				}
			}
		}
		
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
		
		if( isset( $data['watermark_remove'] ) && $data['watermark_remove'] == 1 )
		{
			if( !$this->check( $data, 'watermark_remove_cut_height' ) )
			{
				return 'Podaj wysokośc na jakiej uciąć znak wodny';
			}
			$data['watermark_remove'] = '1';
		}
		else
		{
			$data['watermark_remove'] = '0';
		}
	
	
		$data['extract_videos'] = isset( $data['extract_videos'] ) ? $data['extract_videos'] : '0' ;
		$data['extract_images'] = isset( $data['extract_images'] ) ? $data['extract_images'] : '0' ;
	
		if( $this->insertDraft( $data ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=importer&plugin_action=list', 'Zadanie zostało dodane/zmienione' );
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
		
		if( !isset( $id ) && PD::getInstance()->insert( 'plugin_cron_import', $insert ) )
		{
			return true;
		}
		elseif( isset( $id ) )
		{
			PD::getInstance()->update( 'plugin_cron_import', $insert, array( 'id' => $id ));
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
			$row = PD::getInstance()->select( 'plugin_cron_import', array( 'id' => $import_id ), 1);
		}
		
		$plugin_importer = isset( $_POST['plugin_importer'] ) ? $_POST['plugin_importer'] : array() ;
		
		$urls						= $this->inputField( 'urls', $plugin_importer, $row );
		$category					= $this->inputField( 'category', $plugin_importer, $row );
		$interval					= $this->inputField( 'interval', $plugin_importer, $row, 'daily' );
		$count						= $this->inputField( 'count', $plugin_importer, $row, 10 );
		$main_or_wait				= $this->inputField( 'main_or_wait', $plugin_importer, $row, 'wait' );
		$tags						= $this->inputField( 'tags', $plugin_importer, $row );
		$users						= $this->inputField( 'users', $plugin_importer, $row, USER_LOGIN );

		$extract_images				= $this->inputField( 'extract_images', $plugin_importer, $row, 0 ) != 0 ? 1 : 0;
		$extract_videos				= $this->inputField( 'extract_videos', $plugin_importer, $row, 0 ) != 0 ? 1 : 0;

		$watermark_remove			= $this->inputField( 'watermark_remove', $plugin_importer, $row, 0 ) != 0 ? 1 : 0;
		
		$watermark_remove			= $this->inputField( 'watermark_remove', $plugin_importer, $row, 0 ) != 0 ? 1 : 0;
		$watermark_remove_cut_direction	= $this->inputField( 'watermark_remove_cut_direction', $plugin_importer, $row, 'dol' );
		$watermark_remove_cut_height	= $this->inputField( 'watermark_remove_cut_height', $plugin_importer, $row, 40 );
		$check_time	= $this->inputField( 'check_time', $plugin_importer, $row, 'everytime' );
		
			echo '
			
			
		<form action="" method="post">	
		' . displayArrayOptions(array(
				'plugin_importer[urls]' => array(
					'current_value' => $urls,
					'option_set_text' => 'Wpisz adresy stron skopiowane z paska przeglądarki(oddziel przecinkiem) np http://www.strona.pl/',
					'option_type' => 'textarea',
					'option_css' => 'tags_fancy_input'
				),
				'category' => array(
					'current_value' => '',
					'option_set_text' => 'Kategoria do której zostaną dodane pliki',
					'option_type' => 'text',
					'option_depends' => 'categories_option',
					'option_value' => '<select style="width: 200px;" id="file_category" name="plugin_importer[category]">'.Categories::categorySelectOptions( $category ).'</select>'
				),
				'plugin_importer[users]' => array(
					'current_value' => $users,
					'option_set_text' => 'Loginy userów, którym zostaną losowo przypisane obrazki(oddziel przecinkiem)',
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'tags_fancy_input'
				),
				'plugin_importer[main_or_wait]' => array(
					'current_value' => $main_or_wait,
					'option_set_text' => 'Gdzie dodać importowane materiały',
					'option_select_values' => array(
						'main' => 'Na główną',
						'wait' => 'Do poczekalni',
					)
				),
				'plugin_importer[tags]' => array(
					'current_value' => $tags,
					'option_set_text' => 'Dodatkowe słowa kluczowe(oddziel przecinkiem)',
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'tags_fancy_input'
				),
				'plugin_importer[interval]' => array(
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
				'plugin_importer[count]' => array(
					'current_value' => $count,
					'option_set_text' => 'Ile materiałów importować',
					'option_type' => 'input',
					'option_lenght' => 10
				),
				'plugin_importer[extract_videos]' => array(
					'current_value' => $extract_videos,
					'option_set_text' => 'Wyszukuj video na stronie',
					'option_names' => 'yes_no'
				),
				'plugin_importer[extract_images]' => array(
					'current_value' => $extract_images,
					'option_set_text' => 'Wyszukuj obrazki na stronie',
					'option_names' => 'yes_no'
				),
				'plugin_importer[watermark_remove]' => array(
					'current_value' => $watermark_remove,
					'option_set_text' => 'Usunąć znak wodny?',
					'option_names' => 'yes_no',
					'option_css' => 'watermark_inputs'
				),
				'plugin_importer[watermark_remove_cut_direction]' => array(
					'current_value' => $watermark_remove_cut_direction,
					'option_set_text' => 'Z której strony uciąć image',
					'option_select_values' => array(
						'gora' => 'Od góry',
						'dol' => 'Od dołu'
					),
					'option_css' => 'watermark_input ' .( $watermark_remove == 1 ? '' : 'display_none' )
				),
				'plugin_importer[watermark_remove_cut_height]' => array(
					'current_value' => $watermark_remove_cut_height,
					'option_set_text' => 'Ile PX uciąć, standard to 40px',
					'option_type' => 'input',
					'option_lenght' => 10,
					'option_css' => 'watermark_input ' .( $watermark_remove == 1 ? '' : 'display_none' )
				),
				'plugin_importer[check_time]' => array(
					'current_value' => $check_time,
					'option_set_text' => 'Kiedy sprawdzać czy w serwisach pojawiły się nowe materiały',
					'option_select_values' => array(
						'daily' => 'Raz dziennie',
						'weekly' => 'Raz w tygodniu'
					)
				),
			))
				;
		if( isset( $id ) && $id )
		{
			echo '<input type="hidden" value="' . $id . '" name="plugin_importer[id]" /> ';
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
		$db = PD::getInstance();
		
		if( empty( $cron_id ) || !is_numeric( $cron_id ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=importer&plugin_action=list', 'Wystąpił błąd. Brak poprawnego ID' );
		}
		require_once( ABS_PATH . '/functions-upload.php');
		require_once( IPS_ADMIN_PATH .'/import-functions.php');
		require_once( IPS_ADMIN_PATH .'/libs/class.FastImage.php');
		
		$row = $db->select( 'plugin_cron_import', array( 'id' => $cron_id ), 1, '');
		
		if( time() > $row['cron_next_check_time'] || $call == false )
		{
			$urls = ( strpos( $row['cron_urls'], '|' ) !== false ? explode( '|', $row['cron_urls'] ) : array( $row['cron_urls'] ) );
			
			$html = get_link_content( $urls );
			
			$images = $videos = array();
			
			if( in_array( 'http://www.filmiki.jeja.pl/', $urls ) )
			{
				preg_match_all('@<div class="contentBox contentBoxWide filmBoxS"><div class="left"><a href="([^"]+)"><img src="([^"]+)" alt="([^"]+)" class="thumbFilm"([^"]+)>@', $html, $matches );

				$videos = array();
				foreach( $matches[2] as $id => $link )
				{
					$vid = extract_youtube_links( get_link_content( array( $link ) ) );
					
					if( !empty( $vid[0] ) )
					{
						$videos[] = array( 
							'link' => $vid[0], 
							'title' => $matches[4][$id]
						);
					}
				}
			}
			elseif( $html )
			{
				if( $row['cron_extract_videos'] == 1 )
				{
					$videos = extract_youtube_links( $html );
					if( defined('IPS_SELF' ) )
					{
						$videos = extract_youtube_links_ips_self( $html, $videos );
					}
				}
				
				if( $row['cron_extract_images'] == 1 )
				{
					$images = extract_html_tags( $html, 'img' );
					if( defined('IPS_SELF') )
					{
						preg_match_all('@<div class="file-container" data-target="([^"]+)">([^"]+)<div id="(video_small|video_male)" style="([^"]+)">([^"]+)<div id="video_pozycja_male" style="([^"]+)">([^"]+)<iframe class="youtube-player"([^"]+)type="text/html"([^"]+)width="([^"]+)"([^"]+)height="([^"]+)"([^"]+)src="([^"]+)"([^"]+)frameborder="0">([^"]+)</iframe>([^"]+)</div>([^"]+)</div>([^"]+)</div>@', $html, $matches );
						foreach( $matches[1] as $key => $link )
						{

							preg_match("/<title>(.*)<\/title>/i", file_get_contents( $link ), $match_title);
							
							if( !empty( $match_title[1] ) )
							{
								preg_match_all( '~(?:v/|watch\?v=|embed/|youtu\.be/)([\w\-\_]{11})~ix', $matches[13][$key], $matched );
								$videos[] = array(
									'link' => $matched[1][0],
									'title' => $match_title[1]
								);
							}
						}
						
					}
					
				}
			}
			$files_ready = array();
			if ( !empty( $images ) )
			{
				$i = 0;
				foreach ( $images as $img )
				{
					
					$site = pasuje( html_entity_decode( $img->getAttribute('src') ), $img->getAttribute('class') );
					$title = $img->getAttribute('alt');
					if( $site && strpos( $img->getAttribute('src'), '_miniatura' ) === false )
					{
						/**
						* Duplikaty zdjęć nie są dodawane.
						*/					
						//var_dump( $title .' : '. $img->getAttribute('src') );
						if( !empty( $title ) )
						{
							if( empty( $title ) )
							{
								$title = '';
							}
							
							$keywords = !empty( $row['cron_tags'] ) ? str_replace('|', ',', $row['cron_tags'] ) : extractCommonWords( $title );
						
							if( is_array( $keywords ) )
							{
								$keywords = implode( ',', array_values( $keywords ) );
							}
							$image = new FastImage( $site['img'] );
							
							list( $width, $height ) = $image->getSize();
							
							if( empty( $width ) || ( $width > 200 && $height > 200 ) )
							{
								$files_ready[] = array
								(
									'img' => $site['img'],
									'title' => ucfirst( $title ),
									'site' => $site['site'],
									'upload_tags' => $keywords,
									'file_category' => $row['cron_category'],
									'upload_type' => 'image',
									'size-checked' => ( empty( $width ) ? false : true )
								);
							}
						}
						if( empty( $title ) )
						{
							error_log( 'Importer-Error:' . $img->getAttribute('src') .' -- '.$img->getAttribute('class') );
						}
					} 
					$i++;
				}
			}
			
	
			/**    VIDEO    **/
			foreach( $videos as $id => $video_id )
			{
		
				if( is_array( $video_id ) && isset( $video_id['link'] ) )
				{
					$title = $video_id['title'];
					$video_id = $video_id['link'];
				}
				else
				{
					$sxml = @simplexml_load_file( 'http://gdata.youtube.com/feeds/api/videos/' . $video_id );
					$title = (string)$sxml->title;
				}
			
				if( isset( $title ) )
				{
					
					$url = 'http://www.youtube.com/watch?v=' . $video_id;
					
					if( !empty( $title ) )
					{
						//var_dump( $title .' : '. $url );
						if( empty( $title ) )
						{
							$title = '';
						}

						$keywords = !empty( $row['cron_tags'] ) ? str_replace('|', ',', $row['cron_tags'] ) : extractCommonWords( $title );
						
						if( is_array( $keywords ) )
						{
							$keywords = implode( ',', array_values( $keywords ) );
						}
						
						$files_ready[] = array
						(
							'img' => 'http://img.youtube.com/vi/' . $video_id . '/0.jpg',
							'link' => $url,
							'upload_video' => $url,
							'title' => trim( ucfirst( $title ) ),
							'site' => 'Youtube',
							'upload_tags' => $keywords,
							'file_category' => $row['cron_category'],
							'upload_type' => 'video',
							'size-checked' => true
						);
					}
					if( empty( $title ) )
					{
						error_log( 'Importer-Error:' . $url .' -- '. $title );
					}
				}
				
			}
			$filed_added = 0;
			foreach( $files_ready as $file )
			{
				if( !empty( $file['title'] ) )
				{
					$exists = $db->cnt('plugin_cron_import_files',  array( 
						'cron_url' => $file['img']
					));

					if( empty( $exists ) )
					{
						$exists = $db->cnt( 'upload_imported',  array( 
							'link' => ( isset( $file['upload_video'] ) ? $file['upload_video'] : $file['img'] )
						));
						
						if( empty( $exists ) )
						{
							if( strlen( $file['title'] ) > 20 )
							{
								$exists = $db->select( IPS__FILES, array( 
									'title' => array( $file['title'], 'LIKE' )
								), 1 );
							}
						}
					}
					
					if( empty( $exists ) )
					{
						$db->insert( 'plugin_cron_import_files', array( 
							'cron_id' => $cron_id,
							'cron_url' => $file['img'],
							'cron_serialized' => serialize( $file )
						) );
						$filed_added++;
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
			
			$db->update( 'plugin_cron_import', array( 'cron_next_check_time' => $time ), array( 'id' => $cron_id ));

		}
		
		/**
		* Wykonanie 
		*/
		if( isset( $_GET['call'] ) || $call )
		{

			$rows_import = $db->select( 'plugin_cron_import', array( 'id' => $cron_id ));
			
			foreach( $rows_import as $row_import )
			{
				if( time() > $row_import['cron_interval_time'] )
				{
					$rows_files = $db->select( 'plugin_cron_import_files', array( 'cron_id' => $row_import['id'] ), $row_import['cron_count'] );
					
					foreach( $rows_files as $row )
					{
						cronIPSCall( IPS_ADMIN_URL . '/plugin/Importer?id=' . $row['id'], 5 );
					}
					
					switch( $row_import['cron_interval'] )
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
					
					$db->update( 'plugin_cron_import', array( 
						'cron_interval_time' => $time
					), array( 'id' => $row_import['id'] ));
				}
			}
			
		}
		if( !$call )
		{
			ips_admin_redirect( 'plugins', 'plugin=importer&plugin_action=list', 'Akcja wykonana poprawnie, dodano ' . $filed_added . ' plików do kolejki.'  );
		}
	}
	
	
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function validateData( &$data, $name, $value )
	{
		if( !empty( $value ) && $value != 'brak')
		{
			$data[ $name ] = stripslashes( $value );
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
		$redir = admin_url( 'plugins', 'plugin=importer&plugin_action=list' );
		
		if( empty( $id ) )
		{
			ips_redirect( $redir, 'ID jest niepoprawne' );
		}
		if( PD::getInstance()->delete( 'plugin_cron_import', array( 'id' => (int)$id )) )
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
	public function listFiles()
	{
		$db = PD::getInstance();
		
		if( isset( $_GET['action'] ) && $_GET['action'] == 'delete'  )
		{ 
			$row = $db->select( 'plugin_cron_import_files', array( 'id' => $_GET['id'] ), 1 );
		
			if( !empty( $row ) )
			{
				
				$db->delete( 'plugin_cron_import_files', array( 'id' => $_GET['id'] ));
				
				$db->insert( "upload_imported", array(
					'title' => 'importer-skipped', 
					'link' => $row['cron_url'], 
					'source_url' => 'Inne', 
					'upload_id' => 0
				) );
				
				ips_admin_redirect( 'plugins', 'plugin=importer&plugin_action=files', 'Poprawnie usunięto plik' );
			}
		}
		
		$res = $db->from( 'plugin_cron_import_files files' )->join( 'plugin_cron_import pl' )->on( 'files.cron_id', 'pl.id' )->fields( 'pl.cron_urls, files.*' )->orderBy('id')->get();
		
		
		if( empty( $res ) )
		{
			echo '<h4 class="caption">Brak plików w kolejce</h4>';
			return false;
		}
		
		$fields = array('Adresy strony', 'Tytuł', 'Plik');	
		
		?>
		<script type="text/javascript" src="js/tableSorter.js"></script> 
		<script type="text/javascript">
			$(document).ready(function(){ 
				$(".features-table").tablesorter({selectorHeadersName : 'tfoot', selectorHeaders : 'tfoot th', excludeHeader: [0]});
				$(".features-table").tablesorter({selectorHeadersName : 'thead', selectorHeaders : 'thead th', excludeHeader: [0]});
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
			
			$data = unserialize( $fetchnow['cron_serialized'] );
			if( isset( $data['upload_video'] ) )
			{
				$file = Ips_Registry::get( 'Video' )->get( $data['upload_video'], array(
					'width' 	=> 236,
					'height' 	=> 132,
					'embed'		=> true
				));
			}
			else
			{
				$file = '<a href="' . $fetchnow['cron_url'] . '" target="_blank"><img src="' . $fetchnow['cron_url'] . '" style="max-width: 235px;max-height: 132px" /></a>';
			}
			
			$pola[] = '<div>' . str_replace( '|', ', ', $fetchnow['cron_urls'] ) . '</div>
			<div class="features-table-actions">
					<span><a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=files&action=delete&id=' . $fetchnow['id'] . '' ) . '">Usuń</a></span>
				</div>';
			
			$pola[] = $file;
			
			
			$pola[] = $data['title'];
			
			
			echo generateTable( 'body', $pola ). '</tr>';
		}
		echo '</tbody></table>';	
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
		$res = PD::getInstance()->form( 'plugin_cron_import')->orderBy( 'id' )->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">Brak zdefiniowanych zadań</h4>';
			return false;
		}
		$fields = array('Adresy stron', 'Loginy', 'Gdzie dodać', 'Częstotliwość', 'Szczegóły zadania');	
		
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
			
			$pola[] = '<div>'. str_replace( '|', ', ', $fetchnow['cron_urls'] ) . '</div>
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=start&call=start&id='.$fetchnow['id'] ) . '">Wykonaj</a> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=start&id='.$fetchnow['id'] ) . '">Kolejkuj</a> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=edit&id='.$fetchnow['id'] ) . '">Edytuj</a> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=importer&plugin_action=delete&id='.$fetchnow['id'] ) . '">Usuń</a></span>
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
			'<i>Ucinać znak wodny: </i>' . ( $fetchnow['cron_watermark_remove'] == 1 ? 'Tak' : 'Nie' ) . '<br /> ' 
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
			' . __s('cron_tasks_info_5', IPS_ADMIN_URL . '/plugin/Importer', IPS_ADMIN_URL . '/plugin/Importer' ) . '
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