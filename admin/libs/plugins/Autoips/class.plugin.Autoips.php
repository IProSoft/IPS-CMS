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
	
define( 'IPS_AUTOPOST', true );
class Autoips
{
	public $license = '[license_number]';
	
	public $inserts = array();
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function install()
	{
		//Config::update( 'plugin_autoips_adult_only', 1 );
		Config::update( 'plugin_autoips_remove_inactiv', 1 );
		Config::update( 'plugin_autoips_message', 'Twój obrazek na dziś:' );
		Config::update( 'plugin_autoips_description', 'Subskrybuj najlepsze materiały!' );
		
		PD::getInstance()->query("
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_user_autopost_pro' ) . "` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `post_message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `post_name` varchar(250) NOT NULL DEFAULT '',
			  `post_caption` varchar(250) NOT NULL DEFAULT '',
			  `post_type` varchar(50) NOT NULL DEFAULT 'image',
			  `post_description` varchar(250) NOT NULL DEFAULT '',
			  `post_picture` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `post_link` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `post_privacy` varchar(50) NOT NULL,
			  `post_users` int(10) NOT NULL,
			  `post_added` int(10) NOT NULL DEFAULT '0',
			  `not_added` int(10) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_user_autopost_pro_added' ) . "` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `user_autopost_id` int(10) NOT NULL,
			  `user_facebook_uid` VARCHAR(30) NOT NULL,
			  `facebook_status` int(10) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `unique_users` (`user_autopost_id`,`user_facebook_uid`),
			  KEY `user_facebook_uid` (`user_facebook_uid`),
			  KEY `user_autopost_id` (`user_autopost_id`)
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
		Config::remove( 'plugin_autoips_settings' );
		Config::remove( 'plugin_autoips_remove_inactiv' );
		Config::remove( 'plugin_autoips_message' );
		Config::remove( 'plugin_autoips_description' );
		PD::getInstance()->query('
			DROP TABLE IF EXISTS `' . db_prefix( 'plugin_user_autopost_pro' ) . '`;
			DROP TABLE IF EXISTS `' . db_prefix( 'plugin_user_autopost_pro_added' ) . '`;
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
				'plugin_name' => 'Autoips do wszystkich na Facebook',
				'plugin_description' => 'Plugin umożliwia dodanie materiału na ścianę każdego użytkownika zrejestrowanego w serwisie przez portal Facebook.<br /> Plugin wymaga uprawnień do publikowania na tablicy użytkownika, uprawnienia są ustawione domyślnie w skrypcie.<br />Do wykonania postu wykorzystane są zadania CRON'
			),
			'en_US' => array(
				'plugin_name' => 'Autoips: Facebook users Wall with CRON',
				'plugin_description' => 'This plugin allows you to add material on the wall of every user on the site registered by Facebook. <br /> This plugin requires permission to publish on the board, user permissions are set by default in the script while user FB register.<br />Posting is done by CRON Tasks',
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
			die( '<br />' . __( base64_decode( 'aXBzX2xpY2Vuc2Vfbm90X2Fzc2lnbmVk' ) ) );
		}

		echo '
		<br />
		'. ( defined( 'IPS_SELF') ? '<a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=permissions' ) . '" class="button">Permissions</a>' : '' ) .'
		<a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=add' ) . '" class="button">Dodaj nowy</a>
		<a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=list' ) . '" class="button">Lista postów</a>
		<a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=settings' ) . '" class="button">Ustawienia wtyczki</a>
		<br /><br />';
		$action = isset( $_GET['plugin_action'] ) ? $_GET['plugin_action'] : false;
		if( isset($_POST['plugin_autoips']) )
		{
			echo admin_msg( array(
				'alert' => $this->saveDraft( $_POST['plugin_autoips'] )
			) );
		}
	
		switch ( $action )
		{
			
			case 'list':
				echo $this->listDrafts();
			break;
			case 'proceed':
				//$this->postFacebookData();
			break;
			case 'start':
				$this->preparePost( (isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? $_GET['id'] : false ), 'start' );
			break;
			case 'permissions':
				$this->permissions();
			break;
			case 'stop':
				$this->stopPost( (isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? $_GET['id'] : false ) );
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
			case 'settings':
			default:
				echo $this->settingForm();
				Session::set( 'admin_redirect', admin_url( 'plugins', 'plugin=autoips' ) );
			break;
		}
		
		echo '
		<div class="div-info-message">
			' . str_replace( array( '1h', '/60' ), array( '5min', '/5' ), __s('cron_tasks_info_5', IPS_ADMIN_URL . '/plugin/Autoips', IPS_ADMIN_URL . '/plugin/Autoips' ) ). '
		</div>';
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function permissions()
	{
		
		$rows = PD::getInstance()->select( 'users_data', array(
			'setting_key' => 'facebook_uid'
		), xy( IPS_ACTION_PAGE, 30 ), '*', array( 
			'users_data_id' => 'ASC'
		) );
		
		
		require_once( LIBS_PATH . '/connect/facebook/facebook.php');
	
		$facebook = new Facebook(array(
			'appId'  => Config::get('apps_facebook_app', 'app_id'),
			'secret' => Config::get('apps_facebook_app', 'app_secret'),
			'cookie' => true,
		));
		
		if( IPS_ACTION_PAGE == 1 )
		{
			Session::set( 'permissions', array(
				'post_added' => 0,
				'not_added' => 0
			));
		}
		$perms = Session::set( 'permissions' );
		foreach( $rows as $id => $res )
		{

			try {
				
				$permissions = $facebook->api('/' . $res['setting_value'] . '/permissions');
				$post_added = false;
				
				foreach( $permissions['data'] as $permission )
				{
					if( $permission['permission'] == 'publish_actions' && $permission['status'] == 'granted' )
					{
						$post_added = true;
					}
				}
				
				if( $post_added )
				{
					$perms ['post_added'] ++;
				}
				else
				{
					$perms ['not_added'] ++;
				}
				
			} catch (Exception $e) {
				
				$perms ['not_added'] ++;
				/* 
				preg_match( '~\(\#([0-9]*)\)~imu', $e->getMessage(), $matches );
				if( !isset( $matches[1] ) )
				{
					$matches[1] = 0;
				}
				
				$data['not_added'] ++;
				*/
			}

		}
		var_dump($perms );
		if( empty( $rows ) )
		{
			die();
		}
		Session::set( 'permissions', $perms  );
		
		echo '
		
		<script type="text/JavaScript">
			setTimeout(function(){
				document.location.href = "'. ABS_URL . 'admin/admin.php?route=plugins&plugin=autoips&autopost_action=permissions&page='. ( IPS_ACTION_PAGE + 1 ) .'";
			}, 10000 );
		</script>
			';
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function stopPost( $id )
	{
		if( empty( $id ) || !is_numeric( $id ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=autoips&plugin_action=list', 'Wystąpił błąd. Brak poprawnego ID' );
		}
		
		$data = $this->dataSettings( 'get' );
		$data['stopped'] = true;
		
		$this->dataSettings( 'save', $data );
		
		ips_admin_redirect( 'plugins', 'plugin=autoips&plugin_action=list', 'Post został zatrzymany' );
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function preparePost( $id, $action = false )
	{
		
		if( empty( $id ) || !is_numeric( $id ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=autoips&plugin_action=list', 'Wystąpił błąd. Brak poprawnego ID' );
		}
		$data = $this->dataSettings( 'get' );
		
		if( empty( $data ) || isset( $data['stopped'] ) || $data['id'] != $id )
		{
		
			$row = PD::getInstance()->select( 'plugin_user_autopost_pro', array( 'id' => $id ), 1, '');
			
			$data = array();
			
			if( !empty( $row['post_message'] ) )
			{
				$this->validateData( $data, 'message', $row['post_message'] );
			}
			
			if( $row['post_type'] != 'text' )
			{
				$this->validateData( $data, 'name', $row['post_name'] );
				$this->validateData( $data, 'caption', $row['post_caption'] );
				$this->validateData( $data, 'description', $row['post_description'] );
				$this->validateData( $data, 'picture', $row['post_picture'] );
				$this->validateData( $data, 'link', $row['post_link'] );
			}

			
			if( !empty( $row['post_privacy'] ) && $row != 'brak')
			{
				$data['privacy'] = "{'value': '".$row['post_privacy']."'}";
			}

			$data = array(
				'id' 			=> $id,
				'facebook'		=> $data,
				'users'			=> $row['post_users'],
				'adult'			=> Config::get( 'plugin_autoips_adult_only' ),
				'post_added'	=> $row['post_added'],
				'not_added'		=> $row['not_added']
			);
			
		}
		
		if( $action == 'start' && isset( $data['stopped'] ) )
		{
			unset( $data['stopped'] );
		}
		
		$this->dataSettings( 'save', $data );
		
		ips_admin_redirect( 'plugins', 'plugin=autoips&plugin_action=list', 'Post został dodany jako aktywny' );
		
	}
	public function dataSettings( $action = 'save', $data = false )
	{
		if( $action == 'save' )
		{
			Config::update( 'plugin_autoips_settings', serialize( $data ) );
		}
		elseif( $action == 'delete' )
		{
			Config::update( 'plugin_autoips_settings', '0' );
		}
		else
		{
			$data = Config::get( 'plugin_autoips_settings');
			if( empty( $data ) )
			{
				return false;
			}
			return unserialize( $data );
		}
	}
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function postFacebookData()
	{
		
		set_time_limit(0);
		
		$data = $this->dataSettings( 'get' );
		
		if( isset( $data['stopped'] ) && $data['stopped'] === true )
		{
			return;
		}
		
		if( empty( $data ) )
		{
			die( 'Wystąpił błąd podczas procesu dodawania.' );
		}
		
		if( $data['users'] != 0 && $data['post_added'] >= $data['users'] )
		{
			die( 'Dodano już maksymalną ilość postów.' );
		}
		/**
		* Pobieranie kolejnej partii.
		*/
		
		$rows = PD::getInstance()->select( 'users_data', array(
			'setting_key' => 'facebook_uid',
			'setting_value' => array( 'SELECT user_facebook_uid FROM plugin_user_autopost_pro_added WHERE user_autopost_id = ' . $data['id'], 'NOT IN')
		), base64_decode('MjU='), '*', array( 
			'setting_key' => 'ASC'
		) );
		
		if( empty( $rows ) )
		{
			die( 'Post został dodany na ścianie ' .$data['post_added'] .' użytkowników' );
		}
		
		require_once( ABS_PATH . '/connect/facebook/facebook.php');
	
		$facebook = new Facebook(array(
			'appId'  => Config::get('apps_facebook_app', 'app_id'),
			'secret' => Config::get('apps_facebook_app', 'app_secret'),
			'cookie' => true,
		));
		

		foreach( $rows as $id => $res )
		{
			try {
				$facebook_uid = $res['setting_value'];
				//$facebook_uid = 100001851532200;
				//$user_info = $facebook->api('/'.$res['facebook_uid'].'/');
				//$facebook->api('/'.$res['facebook_uid']);
				/*
				$permissions = $facebook->api('/'.$facebook_uid.'/permissions');
				if( isset( $permissions['data'][0]['publish_actions'] ) && $permissions['data'][0]['publish_actions'] == 1 )
				{
					$data['post_added'] ++;$this->insertUserStatus( $data['id'], $facebook_uid, true );
				}
				else
				{
					$data['not_added'] ++;$this->insertUserStatus( $data['id'], $facebook_uid, $e );
				} */

				$rest = $facebook->api("/" . $facebook_uid . "/feed", 'post', $data['facebook'] );

				$this->insertUserStatus( $data['id'], $facebook_uid, true );
				$data['post_added']++;
			
			} catch (Exception $e) {

				ips_log( $e );
				preg_match( '~\(\#([0-9]*)\)~imu', $e->getMessage(), $matches );
				if( !isset( $matches[1] ) )
				{
					$matches[1] = 0;
				}
				
				$this->insertUserStatus( $data['id'], $facebook_uid, $matches[1] );
				
				if( strpos( $e->getMessage(), "The user hasn't authorized the application to perform this action" ) !== false )
				{
					User_Data::delete( $res['user_id'], 'facebook_uid' );
				}
				$data['not_added']++;
			}
		
			if( $data['users'] != 0 && $data['post_added'] >= $data['users'] )
			{
				PD::getInstance()->update('plugin_user_autopost_pro', array( 
					'post_added' => $data['post_added'], 
					'not_added' => $data['not_added']
				), array( 'id' => $data['id'] ));
				
				$this->dataSettings( 'delete' );
				
				$this->insertUserStatus( 'save' );
				
				die( 'Wszystkie wpisy dodane.' );
			}
		}
		
		$this->dataSettings( 'save', $data );
		$this->insertUserStatus( 'save' );
		
		PD::getInstance()->update('plugin_user_autopost_pro', array( 
			'post_added' => $data['post_added'], 
			'not_added' => $data['not_added']
		), array( 'id' => $data['id'] ));
	
	}
	
	
	
	public function insertUserStatus( $post_id, $user_facebook_uid = false, $message = 1 )
	{
		if( $post_id == 'save' )
		{
			PD::getInstance()->query( implode( "\n", $this->inserts ) );
		}
		else
		{
			$this->inserts[] = "INSERT INTO `" . db_prefix( 'plugin_user_autopost_pro_added' ) . "` (`id`, `user_autopost_id`, `user_facebook_uid`, `facebook_status`) VALUES ( NULL, '" . $post_id . "', '" . $user_facebook_uid . "', '" . $message . "');";
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
			$data[ $name ] = $value;
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
		$redir = admin_url( 'plugins', 'plugin=autoips&plugin_action=list' );
		
		if( empty( $id ) )
		{
			ips_redirect( $redir, 'ID jest niepoprawne' );
		}
		
		if( PD::getInstance()->delete( 'plugin_user_autopost_pro', array( 'id' => (int)$id )) )
		{
			PD::getInstance()->delete( 'plugin_user_autopost_pro_added', array( 'user_autopost_id' => (int)$id ));
			ips_redirect( $redir, 'Poprawnie usunięto post o ID ' . $id );
		}
		else
		{
			ips_redirect( $redir, 'Wystąpił błąd podczas usuwania postu.' );
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
		$res = PD::getInstance()->form( 'plugin_user_autopost_pro')->orderBy( 'id' )->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">Brak zdefiniowanych postów</h4>';
			return false;
		}
		
		$fields = array('Tytuł', 'Wiadomość', 'Caption', 'Opis', 'Szczegóły');	
		
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
		.featured-hover i {display: inline-block; width: 80px;}
		#dialog-confirm{ display: none;}
		</style>
<?php		
		echo '
		<table class="features-table">
		<thead><tr>'.generateTable( 'head', $fields ).'</tr></thead>
		<tfoot><tr>'.generateTable( 'head', $fields ).'</tr></tfoot>
		<tbody>
		<div id="dialog-confirm" title="Informacja">
			<p>Dodawanie materiałów może potrwać nawet kilkadziesiąt minut ponieważ jest wykonane w sposób zabezpieczający Aplikację przez uznaniem za SPAM. Okno przeglądarki będzie odświeżać się automatycznie.</p>
		</div>
		';
		

		$data = Config::getArray( 'plugin_autoips_settings');
		
		foreach( $res as $fetchnow )
		{
			$pola = array();
			
			$is_activ = ( $data['id'] == $fetchnow['id'] && !isset( $data['stopped'] ) ? true : false );
			
			if( $fetchnow['post_type'] == 'text' )
			{
				$fetchnow['post_name'] = $fetchnow['post_description'] = $fetchnow['post_caption'] = 'Nie dotyczy';
			}
			
			echo '<tr id="featured-'.$fetchnow['id'].'" '.( $is_activ ? 'style="color:red"' : '' ).' class="featured-hover">';
			
			$pola[] = '<span class="id-title">' . ( empty($fetchnow['post_name']) ? 'brak' : $fetchnow['post_name'] ) . '</span>
				<div class="features-table-actions">
					'.( $is_activ  ? 
					'<span><a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=stop&id='.$fetchnow['id'] ) . '">Zatrzymaj</a>' : 
					'<span><a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=start&id='.$fetchnow['id'] ) . '" class="dialog-msg">Rozpocznij</a>' ).'
					 | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=edit&id='.$fetchnow['id'] ) . '">Edytuj</a></span> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=autoips&plugin_action=delete&id='.$fetchnow['id'] ) . '">Usuń</a></span>
				</div>';
			
			
			$pola[] = empty($fetchnow['post_message']) ? 'brak' : $fetchnow['post_message'];
			$pola[] = empty($fetchnow['post_caption']) ? 'brak' : $fetchnow['post_caption'] ;
			$pola[] = empty($fetchnow['post_description']) ? 'brak' : $fetchnow['post_description'] ;
			
			$pola[] = ( $fetchnow['post_type'] != 'text' ? 
			'Link: ' . ( empty($fetchnow['post_link']) ? 'brak' : '<a target="_blank" href="' . $fetchnow['post_link'] . '">' . $fetchnow['post_link'] . '</a>' ) . '<br /> 
			<i>Obrazek: </i>' . ( empty($fetchnow['post_picture']) ? 'brak' : '<br /><img data-img="true" width="60" height="60" src="' . $fetchnow['post_picture'] . '" />' ). '<br /> '
			: '' )
			.
			'<i>Prywatność: </i>' . $fetchnow['post_privacy'] . '<br /> '.
			'<i>Użytkownicy: </i>' . ( $fetchnow['post_users'] == '0' ? 'wszyscy' : $fetchnow['post_users'] ). '<br /> '.
			'<i>Dodano : </i><b style="color: green">' . $fetchnow['post_added'] . '</b>'. '<br /> '.
			'<i>Nie dodano : </i><b style="color: green">' . $fetchnow['not_added'] . '</b>';
			
			
			echo generateTable( 'body', $pola ). '</tr>';
		}
		echo '</tbody></table>

		<div class="div-info-message">
			<p><strong>Uwaga:</strong> dodawanie wpisu nie zawsze wykona się w 100% choćby z powodu braku userów w bazie lub zablokowanie aplikacji przez użytkownika.</strong></p>
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
			' . displayArrayOptions(array(

				'plugin_autoips_message' => array(
					'option_set_text' => 'autopost_default_message',
					'option_type' => 'input',
					'option_lenght' => false
				),
				'plugin_autoips_description' => array(
					'option_set_text' => 'autopost_default_description',
					'option_type' => 'input',
					'option_lenght' => false
				),
			)) . '	
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
	public function posts( $post_id, $type = 'file' )
	{

		if( !empty( $post_id ) && is_numeric( $post_id ) )
		{
			$id =  $type == 'file' ? false : $post_id;
			
			if( $type !== 'file' )
			{
				$row = PD::getInstance()->select( 'plugin_user_autopost_pro', array( 'id' => $post_id ), 1);
			}
			else
			{
				$row = PD::getInstance()->select( 'upload_post', array( 'id' => $post_id ), 1);
				$row_2 = PD::getInstance()->select( 'upload_text', array( 'upload_id' => $post_id ), 1);
				
				if( !empty( $row_2 ) )
				{
					unset( $row_2['id']);
					$row = array_merge( $row, $row_2 );
				}
			}
		
		}
			
		$message		= ( isset( $row['post_message'] ) ? $row['post_message'] : ( isset( $row['long_text'] ) ? str_replace( '<br />', "\n", $row['long_text'] ) :  Config::get( 'plugin_autoips_message' ) ) );
		$name			= ( isset($row['title']) ? $row['title'] : ( isset($row['post_name']) ? $row['post_name'] : '' ) );
		$caption		= ( isset($row['top_line']) ? $row['top_line'] : ( isset($row['post_caption']) ? $row['post_caption'] : '' ) );
		$description	= ( isset($row['post_description']) ? $row['post_description'] : Config::get( 'plugin_autoips_description' ) );
		$link			= ( isset($row['title']) ? seoLink( intval( isset( $row['upload_id'] ) ? $row['upload_id'] : $row['id'] ), $row['title']) : ( isset($row['post_link']) ? $row['post_link'] : '' ) );
		$picture		= ( isset($row['upload_image']) ? ips_img( $row, 'thumb' ) : ( isset($row['post_picture']) ? $row['post_picture'] : '' ) );
		$post_type			= ( isset($row['post_type']) ? $row['post_type'] : 'image' );
		$users			= ( isset($row['post_users']) ? $row['post_users'] : 0 );
		$privacy		= ( isset($row['post_privacy']) ? $row['post_privacy'] : 'EVERYONE' );	
			
			echo '
		<script type="text/javascript">
			$(document).ready(function(){
				$("textarea[maxlength]").each( function(){			
					$(this).before("<span class=\"charsRemaining\"></span>");
					$(this).keyup(function(){
						var max = parseInt($(this).attr("maxlength"));
						if( $(this).val().length > max )
						{
							$(this).val($(this).val().substr(0, $(this).attr("maxlength")));
						}
						$(this).parent().find(".charsRemaining").html("Zostało " + (max - $(this).val().length) + " znaków");
					});
				});
				$("#post_text").on("click", function(){
					$(".post_text").fadeOut();
					$("#post_type").val("text");
				});
				$("#post_image").on("click", function(){
					$(".post_text").fadeIn();
					$("#post_type").val("image");
				});
				
			});
		</script>
		';
		
		
		if( $type == 'file' )
		{
			echo '
			<form action="" method="post">
				' . displayArrayOptions(array(
					'id' => array(
						'option_set_text' => 'autopost_fill_with_id',
						'option_type' => 'input',
						'option_lenght' => 5
					)
				)) . '
			<input type="submit" value="'.__('plugin_fill').'" class="button">
			</form>';
		}
		
		if( defined( 'IPS_SELF' ) )
		{
			echo '
			<script type="text/javascript">
				$(document).ready(function(){
				$("#'.( $post_type == 'image' ? 'post_image' : 'post_text' ).'").trigger("click");
				});
			</script>
			<div style="vertical-align: top;">
					<span>Typ materiału</span>
					<button id="post_text" class="button">Tekst</button>
					<button id="post_image" class="button">Obrazek</button>
			</div>
			
			';
		}
		
		
		
		
		
		echo '	

		<form action="" method="post">		
			'.displayArrayOptions(array(
					'plugin_img' => array(
						'current_value' => '',
						'option_set_text' => '',
						'option_type' => 'text',
						'option_value' => '<img src="images/demo_post.png" />'
					),
					'plugin_autoips[message]' => array(
						'current_value' => $message,
						'option_set_text' => 'autopost_post_message',
						'option_type' => 'textarea'
					),
					'plugin_autoips[name]' => array(
						'current_value' => $name,
						'option_set_text' => 'autopost_post_title',
						'option_type' => 'textarea',
						'max_length' => 250,
						'option_css' => 'max_length_set post_text'
					),
					'plugin_autoips[caption]' => array(
						'current_value' => $caption,
						'option_set_text' => 'autopost_post_caption',
						'option_type' => 'textarea',
						'max_length' => 250,
						'option_css' => 'max_length_set post_text'
					),
					'plugin_autoips[description]' => array(
						'current_value' => $description,
						'option_set_text' => 'autopost_post_description',
						'option_type' => 'textarea',
						'max_length' => 250,
						'option_css' => 'max_length_set post_text'
					),
					'plugin_autoips[link]' => array(
						'current_value' => $link,
						'option_set_text' => 'link',
						'option_type' => 'input',
						'max_length' => false,
						'option_css' => 'post_text'
					),
					'plugin_autoips[picture]' => array(
						'current_value' => $picture,
						'option_set_text' => 'plugin_image',
						'option_type' => 'input',
						'option_length' => false,
						'option_css' => 'post_text'
					),
					'plugin_autoips[privacy]' => array(
						'current_value' => $privacy,
						'option_set_text' => 'autopost_post_privacy',
						'option_select_values' => array(
							'EVERYONE' => __('autopost_public'),
							'ALL_FRIENDS' => __('autopost_only_friends'),
							'SELF' =>  __('autopost_only_user'),
						)
					),
					'plugin_autoips[users]' => array(
						'current_value' => $users,
						'option_set_text' => 'autopost_post_counter',
						'option_type' => 'input',
						'option_length' => false
					),
				)).'
	
			<input id="post_type" type="hidden" value="image" name="plugin_autoips[type]" />
		';
		if( isset( $id ) && $id )
		{
			echo '<input type="hidden" value="' . $id . '" name="plugin_autoips[id]" /> ';
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
	public function saveDraft( $data )
	{
		
		if( $data['type'] !== 'text' )
		{
			if( !$this->check( $data, 'name', 250 ) || !$this->check( $data, 'message', 250 ) || !$this->check( $data, 'picture' ) || !$this->check( $data, 'link' ) )
			{
				//return 'Musisz wpisać poprawne: tytuł / wiadomość / link / obrazek';
			}
		}
		else
		{
			if( !$this->check( $data, 'message' ) )
			{
				return 'Wpisz treśc wiadomości';
			}
		}
		if( !$this->check( $data, 'users' ) )
		{
			return 'Wpisz liczbę użytkowników, którym dodać post';
		}
		
		if( !$this->check( $data, 'privacy' ) )
		{
			return 'Podaj parametr Prywatność';
		}
		
		if( $this->insertDraft( $data ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=autoips&plugin_action=list', 'Post został dodany do listy, kliknij "Rozpocznij" aby zacząć proces dodawania' );
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
		foreach( $data as $field => $value )
		{
			if( $value != "" )
			{
				$insert['post_' . $field] = $value;
			}
		}
		if( !isset( $id ) && PD::getInstance()->insert( 'plugin_user_autopost_pro', $insert ) )
		{
			return true;
		}
		elseif( isset( $id ) && PD::getInstance()->update( 'plugin_user_autopost_pro', $insert, array( 'id' => $id )) )
		{
			return true;
		}
		return false;
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
		if( empty( $data[$name] ) && $data[$name] != '0' )
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