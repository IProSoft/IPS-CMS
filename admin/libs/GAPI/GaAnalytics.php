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

class Ga_Analytics {
	
	private $instance = false;
	
	public function __construct()
	{
		set_include_path( get_include_path() . PATH_SEPARATOR . LIBS_PATH . '/google-api-php-client/src');
		$this->profileID = Config::GET('analytics_profile');
	}
	/**
	* Hasło i email Analytycs, profil analytycs
	*
	* @param 
	* 
	* @return 
	*/
	public function checkSettings()
	{
		
		//echo Config::GET('analytics_email'),Config::GET('analytics_password');
		//Config::update( 'analytics_profile', '69964312' );
		
		$this->gaEmail		= Config::GET('analytics_email');
		$this->gaPassword	= Config::GET('analytics_password');
		
		if( isset( $_GET['ga_reset'] ) )
		{
			Config::update( 'analytics_email', '' );
			Config::update( 'analytics_password', '' );
			Config::update( 'analytics_profile', '' );
			ips_message( array(
				'info' => __('analytics_services_reset')
			) );
			ips_admin_redirect('/', 'ga_change=true');
		}
		
		if( !empty( $this->gaEmail ) && (bool)filter_var( $this->gaEmail, FILTER_VALIDATE_EMAIL) && !empty($this->gaPassword) )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* Czy wybrany został profil
	*
	* @param 
	* 
	* @return 
	*/
	public function checkProfile()
	{
		if( !empty( $this->profileID ) || !is_numeric( $this->profileID ) )
		{
			return true;
		}
		return false;
	}
	
	public function init( $profile = false )
	{
		if( $profile )
		{
			try{
				require_once (IPS_ADMIN_PATH .'/libs/GAPI/gapi.class.php' );
			
				$class =  new gapi( $this->gaEmail, $this->gaPassword );
				
			} catch ( Exception $e ) {
				return false;
				return $e->getMessage();
			}
			return $class;
		}
		if( !$this->instance )
		{
			try{
				
				require_once ( IPS_ADMIN_PATH .'/libs/GAPI/class.analytics.php' );
				
				$this->instance = new fetchAnalytics( $this->gaEmail, $this->gaPassword, $this->profileID );
				
			} catch ( Exception $e ) {
				
				ips_message( array(
					'info' => __('analytics_services_not_correct')
				) );
				ips_admin_redirect('/', 'ga_change=true');
			}
		}
		return $this->instance;
	}
	/**
	* Lista profili GA
	*
	* @param 
	* 
	* @return 
	*/
	public function listProfiles()
	{
		if( !$this->checkSettings( true ) )
		{
			return false;
		}
		$init = $this->init( true );
		
		if( !is_object( $init ) && empty( $_GET['ga_change']  ) )
		{
			ips_message( array(
				'info' => __('analytics_login_error')
			) );
			ips_admin_redirect('/', 'ga_change=true');
		}
		
		$init->requestAccountData();
		
		$opts = array();
		foreach( $init->getResults() as $result )
		{
			$opts[$result->getProfileId()] = $result;
		}
		return $opts;
		
	}
	/**
	* Zapisywanie ustawień GA
	*
	* @param 
	* 
	* @return 
	*/
	public function settingsForm()
	{
		$init = $this->init( true );

		if( $this->checkSettings( true ) && !is_object( $init ) )
		{
			echo  		admin_msg( array(
				'alert' => __('analytics_login_error')
			) );
		}
		

		$token = Config::getArray( 'admin_data_analytics' );
		
		if( empty( $token  ) )
		{
			echo '
			<strong>Google Authentication Code </strong>
			<p>Musisz zaakceptować aplikację aby wyświetlać dane z usługi Google Analitics</p>
			<a target="_blank" href="https://accounts.google.com/o/oauth2/auth?'.$url.'">tutaj</a>
			';
		}
		$form = '<form action="" enctype="multipart/form-data" method="post">';
			
			$options = array(
				'ga_data[email]' => array(
					'option_new_block' => __('analytics_login_data'),
					'current_value' => $this->gaEmail,
					'option_set_text' => 'analytics_email',
					'option_type' => 'input',
					'option_lenght' => false
				),
				'ga_data[password]' => array(
					'current_value' => $this->gaPassword,
					'option_set_text' => 'analytics_password',
					'option_type' => 'password',
					'option_lenght' => false
				)
			);
			
			try{
			
				$profiles = $this->listProfiles();
			
			} catch ( Exception $e ) {
				$profiles = array();
			}
			
			if( $profiles )
			{

				$options = array_merge( $options, array(
					'ga_data[profile]' => array(
						'current_value' => $this->profileID,
						'option_set_text' => 'analytics_profiles',
						'option_select_values' => $profiles
					)
				));
				
			};
		
		$form .= displayArrayOptions( $options ) . '

		<input type="submit" name="ga_form" class="button" value="' . __('save') . '" />
		</form>';
		return $form;
	}
	/**
	* Zapisywanie ustawień GA
	*
	* @param 
	* 
	* @return 
	*/
	public function saveSettings( $data )
	{
		ips_log( $data['email'] );
		ips_log( $data['password'] );
		
		if( !empty( $data['password'] ) && !empty( $data['email'] ) )
		{
			$this->gaEmail = $data['email'];
			$this->gaPassword = $data['password'];
			$init = $this->init( true );
		
			if( !is_object( $init ) )
			{
				ips_message( array(
					'alert' => __('analytics_login_error')
				) );
				ips_admin_redirect('/', 'ga_change=true');
			}
			
			Config::update( 'analytics_password', $data['password'] );
			Config::update( 'analytics_email', $data['email'] );
			if( !empty( $data['profile'] ) )
			{
				Config::update( 'analytics_profile', $data['profile'] );
			}
			ips_message( array(
				'normal' =>  __('settings_saved')
			) );
		}
		else
		{
			ips_message( array(
				'normal' =>  __('fill_in_required_fields')
			) );
		}

		ips_admin_redirect('/');
	}
	public function report()
	{
		
		$ga_init = $this->init();
		
		if( $ga_init == false )
		{
			return __('analytics_login_error');
		}
		
		//$this->instance->requestReportData( $this->profileID, array('browser','browserVersion'), array('pageviews','visits','UniquePageviews') );
		//$this->instance->requestReportData( $this->profileID, array('eventLabel'),array('uniqueEvents','pageViews','visits'), 'uniqueEvents');
		
		foreach( $this->instance->getResults() as $result )
		{
			//echo $result->getPageviews();
			//echo $result->getVisits();
		}
			
			$traffic = $this->instance->trafficCount();
			$referral = $this->instance->referralCount();

		
			echo '
			<div class="nice-blocks features-table-actions-div">
				<div class="blocks-header">'.__('analytics_statistics_views').'</div>
				<div class="blocks-content">
					<div class="stats-blocks" style="width: 300px">
						<span class="table-header">'.__('analytics_views').'</span>
						<div class="table-body">
							<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
							<thead></thead>
								<tbody>
									<tr>
										<td>'.__('analytics_visit').' </td>
										<td class="count-rows">'.$traffic['pageView'].'</td>
									</tr>
									<tr>
										<td>'.__('analytics_hits').' </td>
										<td class="count-rows">'.$traffic['visits'].'</td>
									</tr>
									<tr>
										<td> '.__('analytics_unique_visitors').'</td>
										<td class="count-rows">'.$traffic['unique'].'</td>
									</tr>
									<tr>
										<td>'.__('analytics_average_time_visit').' </td>
										<td class="count-rows">'.round($traffic['timeOnSite'], 1).' min</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>';
				if( !empty($referral) )
				{
					
						echo '
					<div class="stats-blocks" style="width: 300px">
						<span class="table-header">'.__('analytics_traffic_sources').'</span>
						<div class="table-body">
							<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
							<thead></thead>
								<tbody>';
								if( count($referral) > 5 )
								{
									$referral = array_slice($referral, 0, 5);
								}
								foreach( $referral as $name => $ref )
								{
									echo '
									<tr>
										<td>' . $name . ': </td>
										<td class="count-rows">' . $ref . '</td>
									</tr>';
									
								}
								echo '	
								</tbody>
							</table>
						</div>
					</div>';
					
				}				
					
					echo '
					<div class="stats-blocks" style="width: 50%; height: auto;">
						<span class="table-header">'.__('analytics_traffic_sources').'</span>
						<div class="table-body">
							<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
							<thead></thead>
								<tbody>
									<tr>
										<td>'. $this->instance->graphSourceCount() . '</td>
									</tr>	
								</tbody>
							</table>
						</div>
					</div>';
					echo '
					<div class="stats-blocks" style="width: 49%; height: auto;">
						<span class="table-header">'.__('analytics_new_returning').' </span>
						<div class="table-body">
							<table width="100%" cellspacing="0" cellpadding="0" class="blocks-table">
							<thead></thead>
								<tbody>
									<tr>
										<td>
										'. $this->instance->graphVisitorType() . '
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>';
			
					
					
				echo '	
					<div class="clear"></div>
				</div>
			</div>';
		
		
	}
}
?>