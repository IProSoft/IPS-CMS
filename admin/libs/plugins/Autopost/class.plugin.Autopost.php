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
	

class Autopost
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
		//Config::update( 'plugin_autopost_adult_only', 1 );
		Config::update( 'post_facebook_data', serialize( array() ) );
		Config::update( 'plugin_autopost_remove_inactiv', 1 );
		Config::update( 'plugin_autopost_message', __('autopost_example_message') );
		Config::update( 'plugin_autopost_description', __('autopost_example_title') );
		
		PD::getInstance()->PDQuery("
			CREATE TABLE IF NOT EXISTS `" . db_prefix( 'plugin_user_autopost' ) . "` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `post_message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `post_name` varchar(250) NOT NULL DEFAULT '',
			  `post_caption` varchar(250) NOT NULL DEFAULT '',
			  `post_description` varchar(250) NOT NULL DEFAULT '',
			  `post_picture` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `post_link` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `post_privacy` varchar(50) NOT NULL,
			  `post_users` int(10) NOT NULL,
			  `post_added` int(10) NOT NULL DEFAULT 0,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;
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
		//Config::remove( 'plugin_autopost_adult_only' );
		Config::remove( 'plugin_autopost_remove_inactiv' );
		Config::remove( 'plugin_autopost_message' );
		Config::remove( 'plugin_autopost_description' );
		PD::getInstance()->PDQuery("
			DROP TABLE IF EXISTS `plugin_user_autopost`;
		");
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
				'plugin_name' => 'AUTOPOST do wszystkich na Facebook',
				'plugin_description' => 'Plugin umożliwia dodanie materiału na ścianę każdego użytkownika zrejestrowanego w serwisie przez portal Facebook.<br /> Plugin wymaga uprawnień do publikowania na tablicy użytkownika, uprawnienia są ustawione domyślnie w skrypcie.'
			),
			'en_US' => array(
				'plugin_name' => 'Post to Facebook users Wall',
				'plugin_description' => 'This plugin allows you to add material on the wall of every user on the site registered by Facebook. <br /> This plugin requires permission to publish on the board, user permissions are set by default in the script while user FB register.'
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
		if( strpos( base64_decode('c2tyeXB0Lmlwcw=='), str_replace( array( 'www.', 'http://', '/' ), '', ABS_URL ) ) === false && !defined('IPS_SELF') )
		{
			die( '<br />' . __( base64_decode( 'aXBzX2xpY2Vuc2Vfbm90X2Fzc2lnbmVk' ) ) );
		}
		
		echo '
		<br />
		<a href="' . admin_url( 'plugins', 'plugin=autopost&plugin_action=add' ) . '" class="button">'.__('autopost_add').'</a>
		<a href="' . admin_url( 'plugins', 'plugin=autopost&plugin_action=list' ) . '" class="button">'.__('autopost_post_list').'</a>
		<a href="' . admin_url( 'plugins', 'plugin=autopost&plugin_action=settings' ) . '" class="button">'.__('plugin_settings').'</a>
		<br /><br />';
		
		$action = isset( $_GET['plugin_action'] ) ? $_GET['plugin_action'] : false;
		
		if( isset( $_POST['plugin_autopost'] ) )
		{
			echo '<div class="ips-message msg-alert">' . $this->saveDraft( $_POST['plugin_autopost'] ) . '</div>';
		}
		switch ( $action )
		{
			
			case 'list':
				echo $this->listDrafts();
			break;
			case 'proceed':
				$this->postFacebookData();
			break;
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
			case 'settings':
			default:
				echo $this->settingForm();
				Session::set( 'admin_redirect', admin_url( 'plugins', 'plugin=autopost' ) );
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
	public function preparePost( $id )
	{
		
		if( empty( $id ) || !is_numeric( $id ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=autopost&plugin_action=list', __('autopost_error_wrong_id') );
		}
		
		$row = PD::getInstance()->simpleSelect( 'plugin_user_autopost', "id = '" . $id . "'", 1, '');
		
		$data = array();
		$this->validateData( $data, 'message', $row['post_message'] );
		$this->validateData( $data, 'name', $row['post_name'] );
		$this->validateData( $data, 'caption', $row['post_caption'] );
		$this->validateData( $data, 'description', $row['post_description'] );
		$this->validateData( $data, 'picture', $row['post_picture'] );
		$this->validateData( $data, 'link', $row['post_link'] );
		
		if( !empty( $row['post_privacy'] ) && $row != 'brak')
		{
			$data['privacy'] = "{'value': '".$row['post_privacy']."'}";
		}
	
		$post_facebook_data = Config::getArray('post_facebook_data');
		
		if( !empty( $post_facebook_data ) )
		{
			if( !isset( $post_facebook_data['id'] ) || $post_facebook_data['id'] != $id )
			{
				unset( $post_facebook_data );
			}
		}

		Config::update( 'post_facebook_data', serialize( array(
			'id' 		=> $id,
			'facebook'	=> $data,
			'users'		=> (int)$row['post_users'],
			'adult'		=> Config::get( 'plugin_autopost_adult_only' ),
			'added'		=> (int)$row['post_added'],
			'not_added'	=> ( isset( $post_facebook_data['not_added'] ) ? $post_facebook_data['not_added'] : 0 ),
			'page_num'	=> ( isset( $post_facebook_data['page_num'] ) ? $post_facebook_data['page_num'] : 0 ),
			'permissions'	=> ( isset( $post_facebook_data['permissions'] ) ? $post_facebook_data['permissions'] : 0 ),
		) ) );
		
		ips_admin_redirect( 'plugins', 'plugin=autopost&plugin_action=proceed&page=1');
		
	}
	public function endPost( $msg )
	{
		Config::update( 'post_facebook_data', serialize( array() ) );
		ips_admin_redirect( 'plugins', 'plugin=autopost&plugin_action=list', $msg );
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

		$page_num = isset( $_GET['page'] ) ? (int)$_GET['page'] : 1;
		
		$data = Config::getArray('post_facebook_data');
		
		if( empty( $data ) )
		{
			return $this->endPost( __('autopost_error_add') );
		}

		if( isset( $data['page_num'] ) && $data['page_num'] > $page_num )
		{
			if( $data['page_num'] > $page_num )
			{
				ips_admin_redirect( 'plugins', 'plugin=autopost&plugin_action=proceed&page=' . $data['page_num'] );
			}
			
			$page_num = $data['page_num'];
		}
		
		if( $data['users'] != 0 && $data['added'] >= $data['users'] )
		{
			return $this->endPost( __s( 'autopost_error_add_maximum_posts' ) );
		}
		
		/**
		* Pobieranie kolejnej partii.
		*/
		$pages = xy( $page_num, 15 );
		
		$rows = PD::getInstance()->simpleSelect( 'users_data', "setting_key = 'facebook_uid'", $pages[1] . ',' . $pages[2], '*', 'users_data_id ASC' );
		
		if( empty( $rows ) )
		{
			return $this->endPost( __s( 'autopost_post_success', $data['added'] ) );
		}
		
		$session = Facebook_UI::getApp();
		
		if( !$session )
		{
			return $this->endPost( __('autopost_error_add') );
		}
		
		foreach( $rows as $id => $res )
		{
			$facebook_uid = $res['setting_value'];
			try {

				/* 
				$request = new FacebookRequest(
					$session, 'GET', '/' . $facebook_uid . '/permissions'
				);
			
				$permissions = json_decode( $request->execute()->getRawResponse(), true );
				$permissions = array_column( $permissions['data'], 'status', 'permission' );
				if( isset( $permissions['publish_actions'] ) && $permissions['publish_actions'] == 'granted' )
				{
					$data['permissions'] ++;
				}
				*/
				
				$request = $session->post( '/' . $facebook_uid . '/feed', $data['facebook'], User_Data::get( User_Data::getByValue( 'facebook_uid', $facebook_uid, 1 ), 'access_token_long_live' ) )->getDecodedBody();
				
				$data['added'] ++;
				
			
			} catch(Exception $e) {
				
			//	This message contains content that has been blocked by our security systems.
				
				if( Config::get('plugin_autopost_remove_inactiv') )
				{
					//PD::getInstance()->delete( 'users_connect', "id='".$res['user_id']."'");
				}
				$data['not_added'] ++;
			}
			if( $data['users'] != 0 && $data['added'] >= $data['users'] )
			{

				PD::getInstance()->update('plugin_user_autopost', array(
					'post_added' => $data['added']
				), "id = '" . $data['id'] . "'");
				
				return $this->endPost( __('autopost_all_post_added') );
				
			}
		}
		
		$data['page_num'] = ( $page_num + 1 );		

		Config::update( 'post_facebook_data', serialize( $data ) );
		
		PD::getInstance()->update('plugin_user_autopost', array( 
			'post_added' => $data['added']
		), "id = '" . $data['id'] . "'");
		
		$url = admin_url( 'plugins', 'plugin=autopost&plugin_action=proceed&page=' . ( $page_num + 1 ) );
		
		$count_all = ( $data['users'] > 0 ? $data['users'] : PD::getInstance()->cnt( 'users_data', "setting_key = 'facebook_uid'" ) );
		if( $count_all == 0 )
		{
			$count_all = 1;
		}	
		echo '
		<div class="nice-blocks features-table-actions-div">
			<div class="blocks-header"> '.__('plugin_progress').'<div id="progressbar"></div> </div>
			<div class="blocks-content">
				<div style="width: 100%; height: auto" class="stats-blocks">
					<div class="table-body">
						<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
						<thead>
							<tr>
								<th colspan="2"><span class="table-header">'.__('field_status').'</span></th>
							</tr>
						</thead>
							<tbody>
								<tr>
									<td style="width: 30px;"><img src="images/ikony/update-success.png" /></td>
									<td> '.__s( 'autopost_add_success_count', $data['added'] ).' </td>
								</tr>
								<tr>
									<td style="width: 30px;"><img src="images/ikony/update-error.png" /></td>
									<td> ' . __s( 'autopost_error_add_fail', $data['not_added'] ) . '.</td>
								</tr> 
							</tbody>
						</table>
					</div>
				</div>
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
			$( "#progressbar" ).progressbar({
				value: ' . round( ( $data['added'] / $count_all  ), 3 ) . '
			});
			setTimeout(function(){
				window.location.href = "' . $url . '";
			}, 10000 );
		});
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
		$redir = admin_url( 'plugins', 'plugin=autopost&plugin_action=list' );
		
		if( empty( $id ) )
		{
			ips_redirect( $redir, __('autopost_error_wrong_id') );
		}
		if( PD::getInstance()->delete( 'plugin_user_autopost', "id='".(int)$id."'") )
		{
			ips_redirect( $redir, __s( 'autopost_post_deleted', $id ) );
		}
		else
		{
			ips_redirect( $redir, __('autopost_error_post_delete') );
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
		$res = PD::getInstance()->from( 'plugin_user_autopost' )->orderBy( 'id' )->get();
		
		if( empty($res) )
		{
			echo '<h4 class="caption">'.__('autopost_list_empty').'</h4>';
			return false;
		}
		$fields = array( __('title'), __('plugin_message'), __('caption'), __('plugin_description'), __('plugin_details') );	
		
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
						buttons: {
							"<?php echo __('autopost_start'); ?>": function() {
								$( this ).dialog( 'close' );
								window.location.href = link;
							},
							"<?php echo __('autopost_stop'); ?>": function() {
								$( this ).dialog( 'close' );
								return false;
							}
						}
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
		<div id="dialog-confirm" title="'.__('plugin_info').'">
			<p>'.__('plugin_info_1').'</p>
		</div>
		';
		

		
		foreach( $res as $fetchnow )
		{
			$pola = array();
			
			echo '<tr id="featured-'.$fetchnow['id'].'" class="featured-hover">';
			
			$pola[] = ( empty($fetchnow['post_name']) ? __('empty_field') : $fetchnow['post_name'] ) . '
				<div class="features-table-actions">
					<span><a href="' . admin_url( 'plugins', 'plugin=autopost&plugin_action=start&id='.$fetchnow['id'] ) . '" class="dialog-msg">'.__('plugin_start').'</a> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=autopost&plugin_action=edit&id='.$fetchnow['id'] ) . '">'.__('plugin_edit').'</a></span> | </span>
					<span><a href="' . admin_url( 'plugins', 'plugin=autopost&plugin_action=delete&id='.$fetchnow['id'] ) . '">'.__('common_delete').'</a></span>
				</div>';
			
			$pola[] = empty($fetchnow['post_message']) ? __('empty_field') : $fetchnow['post_message'];
			$pola[] = empty($fetchnow['post_caption']) ? __('empty_field'): $fetchnow['post_caption'] ;
			$pola[] = empty($fetchnow['post_description']) ? __('empty_field') : $fetchnow['post_description'] ;
			
			$pola[] = __('link') . ( empty($fetchnow['post_link']) ? __('empty_field') : '<a target="_blank" href="' . $fetchnow['post_link'] . '">' . $fetchnow['post_link'] . '</a>' ) . '<br /> '.
			'<i> '.__('plugin_image').'	</i>' . ( empty($fetchnow['post_picture']) ? __('empty_field') : '<br /><img data-img="true" width="60" height="60" src="' . $fetchnow['post_picture'] . '" />' ). '<br /> '.
			'<i> '.__('plugin_privacy').'</i>' . $fetchnow['post_privacy'] . '<br /> '.
			'<i> '.__('plugin_users').'</i>' . ( $fetchnow['post_users'] == '0' ? 'wszyscy' : $fetchnow['post_users'] ). '<br /> '.
			'<i> '.__('plugin_added').' </i><b style="color: green">' . $fetchnow['post_added'] . '</b>';
			
			
			echo generateTable( 'body', $pola ). '</tr>';
		}
		echo '</tbody></table>

		<div class="div-info-message">
			<p>'.__('plugin_info_2').'</p>
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
		<form action="zapisz.php" enctype="multipart/form-data" method="post">
			' . displayArrayOptions(array(
				'plugin_autopost_message' => array(
					'option_set_text' => 'autopost_default_message',
					'option_type' => 'input',
					'option_lenght' => false
				),
				'plugin_autopost_description' => array(
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
			$row = PD::getInstance()->simpleSelect( ( $type == 'file' ? IPS__FILES : 'plugin_user_autopost' ), "id = '" . $post_id . "'", 1);
		}
			
		$message		= ( isset($row['post_message']) ? $row['post_message'] : Config::get( 'plugin_autopost_message' ) );
		$name			= ( isset($row['title']) ? $row['title'] : ( isset($row['post_name']) ? $row['post_name'] : '' ) );
		$caption		= ( isset($row['tytul_pod']) ? $row['tytul_pod'] : ( isset($row['post_caption']) ? $row['post_caption'] : '' ) );
		$description	= ( isset($row['post_description']) ? $row['post_description'] : Config::get( 'plugin_autopost_description' ) );
		$link			= ( isset($row['title']) ? seoLink( $row['id'], $row['title']) : ( isset($row['post_link']) ? $row['post_link'] : '' ) );
		$picture		= ( isset( $row['upload_image'] ) ? ips_img( $row, 'large' ) : ( isset($row['post_picture']) ? $row['post_picture'] : '' ) );
		
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
					$(this).parent().find(".charsRemaining").html( "'.__('autopost_character_remain').'" + ( max - $(this).val().length ));
				});
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
		
		
		
		
		
		
		
		echo '	

		<form action="" method="post">		
			'.displayArrayOptions(array(
					'plugin_img' => array(
						'current_value' => '',
						'option_set_text' => '',
						'option_type' => 'text',
						'option_value' => '<img src="images/demo_post.png" />'
					),
					'plugin_autopost[message]' => array(
						'current_value' => $message,
						'option_set_text' => 'autopost_post_message',
						'option_type' => 'textarea'
					),
					'plugin_autopost[name]' => array(
						'current_value' => $name,
						'option_set_text' => 'autopost_post_title',
						'option_type' => 'textarea',
						'max_length' => 250,
						'option_css' => 'max_length_set'
					),
					'plugin_autopost[caption]' => array(
						'current_value' => $caption,
						'option_set_text' => 'autopost_post_caption',
						'option_type' => 'textarea',
						'max_length' => 250,
						'option_css' => 'max_length_set'
					),
					'plugin_autopost[description]' => array(
						'current_value' => $description,
						'option_set_text' => 'autopost_post_description',
						'option_type' => 'textarea',
						'max_length' => 250,
						'option_css' => 'max_length_set'
					),
					'plugin_autopost[link]' => array(
						'current_value' => $link,
						'option_set_text' => 'link',
						'option_type' => 'input',
						'max_length' => false
					),
					'plugin_autopost[picture]' => array(
						'current_value' => $picture,
						'option_set_text' => 'plugin_image',
						'option_type' => 'input',
						'option_length' => false
					),
					'plugin_autopost[privacy]' => array(
						'current_value' => $privacy,
						'option_set_text' => 'autopost_post_privacy',
						'option_select_values' => array(
							'EVERYONE' => __('autopost_public'),
							'ALL_FRIENDS' => __('autopost_only_friends'),
							'SELF' =>  __('autopost_only_user'),
						)
					),
					'plugin_autopost[users]' => array(
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
			echo '<input type="hidden" value="' . $id . '" name="plugin_autopost[id]" /> ';
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
		
		if( !$this->check( $data, 'name', 250 ) && !$this->check( $data, 'message', 250 ) && !$this->check( $data, 'picture' ) && !$this->check( $data, 'link' ) )
		{
			return __('autopost_error_data');
		}
		
		if( !$this->check( $data, 'users' ) )
		{
			return __('autopost_error_users');
		}
		
		if( !$this->check( $data, 'privacy' ) )
		{
			return __('autopost_error_privacy');
		}
		
		if( $this->insertDraft( $data ) )
		{
			ips_admin_redirect( 'plugins', 'plugin=autopost&plugin_action=list', __('autopost_process_added') );
		}
		else
		{
			return __('autopost_error_process_add');
			
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
		if( !isset( $id ) && PD::getInstance()->insert( 'plugin_user_autopost', $insert ) )
		{
			return true;
		}
		elseif( isset( $id ) && PD::getInstance()->update( 'plugin_user_autopost', $insert, "id = '" . $id . "'" ) )
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
		if( empty( $data[$name] ) && $data[$name] !== '0' )
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